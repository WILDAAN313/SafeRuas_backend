<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LogicException;
use Pdo\Sqlite;
use RuntimeException;
use WeakMap;

/**
 * @phpstan-type Coordinate array{float, float}
 * @phpstan-type Edge array{float, float, float, float, float}
 * @phpstan-type Ring array{bounds: array{float, float, float, float}, edges: list<Edge>, buckets: array<int, list<int>>}
 * @phpstan-type Prepared array{rings: list<list<Ring>>, polygons: list<list<list<Coordinate>>>, extent: array{float, float, float, float}}
 */
class IndramayuBoundary
{
    /** @var int */
    private const BUCKET_COUNT = 128;

    /** @var float */
    private const EPSILON = 1.0e-10;

    /** @var list<list<Ring>> */
    private array $rings = [];

    /** @var list<list<list<Coordinate>>> */
    private array $polygons;

    /** @var array{float, float, float, float} */
    private array $extent;

    /** @var WeakMap<Sqlite, bool> */
    private WeakMap $sqliteConnections;

    private ?string $wellKnownText = null;

    public static function fromGeoJsonFile(string $path): self
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Data batas wilayah Indramayu tidak tersedia.');
        }

        $sourceHash = hash_file('sha256', $path);
        $codeHash = hash_file('sha256', __FILE__);

        if ($sourceHash === false || $codeHash === false) {
            throw new RuntimeException('Versi data batas wilayah Indramayu gagal dibaca.');
        }

        $version = $codeHash.':'.$sourceHash;
        $create = static function () use ($path, $version): array {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new RuntimeException('Data batas wilayah Indramayu gagal dibaca.');
            }

            $feature = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            $boundary = new self($feature['geometry'] ?? []);

            return ['version' => $version, 'prepared' => [
                'rings' => $boundary->rings,
                'polygons' => $boundary->polygons,
                'extent' => $boundary->extent,
            ]];
        };

        $cached = Cache::remember('saferuas.indramayu.boundary', 86400, $create);

        // Keep one compiled version and replace it immediately when source or algorithm changes.
        if (! is_array($cached) || ($cached['version'] ?? null) !== $version
            || ! is_array($cached['prepared'] ?? null)) {
            $cached = $create();
            Cache::put('saferuas.indramayu.boundary', $cached, 86400);
        }

        return new self([], $cached['prepared']);
    }

    /**
     * @param  array{type?: string, coordinates?: array<mixed>}  $geometry
     * @param  Prepared|null  $prepared
     */
    public function __construct(array $geometry, ?array $prepared = null)
    {
        $this->sqliteConnections = new WeakMap;

        if ($prepared !== null) {
            $this->rings = $prepared['rings'];
            $this->polygons = $prepared['polygons'];
            $this->extent = $prepared['extent'];

            return;
        }

        $coordinates = $geometry['coordinates'] ?? [];

        if (! is_array($coordinates)) {
            throw new InvalidArgumentException('Koordinat batas Indramayu harus berupa array.');
        }

        $polygons = match ($geometry['type'] ?? null) {
            'Polygon' => [$coordinates],
            'MultiPolygon' => $coordinates,
            default => throw new InvalidArgumentException('Batas Indramayu harus berupa Polygon atau MultiPolygon.'),
        };

        if ($polygons === []) {
            throw new InvalidArgumentException('Data batas Indramayu tidak boleh kosong.');
        }

        $this->extent = [INF, INF, -INF, -INF];
        $this->polygons = [];

        foreach ($polygons as $polygon) {
            if (! is_array($polygon) || $polygon === []) {
                throw new InvalidArgumentException('Poligon batas Indramayu tidak valid.');
            }

            $rings = [];
            $normalizedPolygon = [];

            foreach ($polygon as $ring) {
                $points = $this->normalizeRing($ring);
                $prepared = $this->prepareRing($points);
                $rings[] = $prepared;
                $normalizedPolygon[] = $points;
                [$west, $south, $east, $north] = $prepared['bounds'];
                $this->extent = [
                    min($this->extent[0], $west), min($this->extent[1], $south),
                    max($this->extent[2], $east), max($this->extent[3], $north),
                ];
            }

            $this->rings[] = $rings;
            $this->polygons[] = $normalizedPolygon;
        }
    }

    /** @return array{array{float, float}, array{float, float}} */
    public function bounds(): array
    {
        return [[$this->extent[1], $this->extent[0]], [$this->extent[3], $this->extent[2]]];
    }

    public function contains(mixed $latitude, mixed $longitude): bool
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return false;
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if (! is_finite($latitude) || ! is_finite($longitude)) {
            return false;
        }

        foreach ($this->rings as $polygon) {
            $outer = $this->locateInRing($longitude, $latitude, $polygon[0]);

            if ($outer === 0) {
                return true;
            }

            if ($outer === -1) {
                continue;
            }

            foreach (array_slice($polygon, 1) as $hole) {
                $location = $this->locateInRing($longitude, $latitude, $hole);

                if ($location === 0) {
                    return true;
                }

                if ($location === 1) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    /** @param Builder<Model> $query */
    public function constrain(Builder $query): void
    {
        /** @var Connection $connection */
        $connection = $query->getConnection();
        $latitude = $query->getModel()->qualifyColumn('latitude');
        $longitude = $query->getModel()->qualifyColumn('longitude');
        $query->whereBetween($latitude, [$this->extent[1], $this->extent[3]])
            ->whereBetween($longitude, [$this->extent[0], $this->extent[2]]);
        $grammar = $connection->getQueryGrammar();
        $latitudeSql = $grammar->wrap($latitude);
        $longitudeSql = $grammar->wrap($longitude);

        if ($connection->getDriverName() === 'sqlite') {
            $pdo = $connection->getReadPdo();

            if (! $pdo instanceof Sqlite) {
                throw new LogicException('Pemeriksaan wilayah SQLite memerlukan PHP 8.4 atau yang lebih baru.');
            }

            if (! isset($this->sqliteConnections[$pdo])) {
                $registered = $pdo->createFunction(
                    'saferuas_within_indramayu',
                    fn (mixed $lat, mixed $lng): int => (int) $this->contains($lat, $lng),
                    2,
                    Sqlite::DETERMINISTIC,
                );

                if (! $registered) {
                    throw new LogicException('Fungsi pemeriksaan wilayah Indramayu gagal didaftarkan.');
                }

                $this->sqliteConnections[$pdo] = true;
            }

            $query->whereRaw("saferuas_within_indramayu({$latitudeSql}, {$longitudeSql}) = 1");

            return;
        }

        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $query->whereRaw(
                "ST_Intersects(ST_GeomFromText(?, 0), POINT({$longitudeSql}, {$latitudeSql})) = 1",
                [$this->toWellKnownText()],
            );

            return;
        }

        throw new LogicException('Pemeriksaan wilayah Indramayu mendukung SQLite, MySQL, atau MariaDB.');
    }

    /** @return list<Coordinate> */
    private function normalizeRing(mixed $ring): array
    {
        if (! is_array($ring) || count($ring) < 4) {
            throw new InvalidArgumentException('Ring batas Indramayu tidak valid.');
        }

        $points = [];

        foreach ($ring as $point) {
            if (! is_array($point) || ! isset($point[0], $point[1])
                || ! is_numeric($point[0]) || ! is_numeric($point[1])
                || ! is_finite((float) $point[0]) || ! is_finite((float) $point[1])
                || abs((float) $point[0]) > 180 || abs((float) $point[1]) > 90) {
                throw new InvalidArgumentException('Koordinat batas Indramayu tidak valid.');
            }

            $points[] = [(float) $point[0], (float) $point[1]];
        }

        if ($points[0] !== $points[array_key_last($points)]) {
            throw new InvalidArgumentException('Ring batas Indramayu harus tertutup.');
        }

        return $points;
    }

    /**
     * @param  list<Coordinate>  $points
     * @return Ring
     */
    private function prepareRing(array $points): array
    {
        $xs = array_column($points, 0);
        $ys = array_column($points, 1);
        $bounds = [min($xs), min($ys), max($xs), max($ys)];

        if ($bounds[0] === $bounds[2] || $bounds[1] === $bounds[3]) {
            throw new InvalidArgumentException('Ring batas Indramayu tidak memiliki luas.');
        }

        $edges = [];
        $buckets = [];

        // Latitude buckets limit each ray test to nearby edges instead of the entire coastline.
        for ($index = 1, $count = count($points); $index < $count; $index++) {
            [$x1, $y1] = $points[$index - 1];
            [$x2, $y2] = $points[$index];
            $length = hypot($x2 - $x1, $y2 - $y1);

            if ($length === 0.0) {
                continue;
            }

            $edgeIndex = count($edges);
            $edges[] = [$x1, $y1, $x2, $y2, $length];
            $first = $this->bucket(min($y1, $y2) - self::EPSILON, $bounds[1], $bounds[3]);
            $last = $this->bucket(max($y1, $y2) + self::EPSILON, $bounds[1], $bounds[3]);

            for ($bucket = $first; $bucket <= $last; $bucket++) {
                $buckets[$bucket][] = $edgeIndex;
            }
        }

        return compact('bounds', 'edges', 'buckets');
    }

    /** @param Ring $ring */
    private function locateInRing(float $x, float $y, array $ring): int
    {
        [$west, $south, $east, $north] = $ring['bounds'];

        if ($x < $west - self::EPSILON || $x > $east + self::EPSILON
            || $y < $south - self::EPSILON || $y > $north + self::EPSILON) {
            return -1;
        }

        $inside = false;
        $bucket = $this->bucket($y, $south, $north);

        foreach ($ring['buckets'][$bucket] ?? [] as $edgeIndex) {
            [$x1, $y1, $x2, $y2, $length] = $ring['edges'][$edgeIndex];
            $cross = ($x - $x1) * ($y2 - $y1) - ($y - $y1) * ($x2 - $x1);

            if (abs($cross) <= self::EPSILON * $length
                && $x >= min($x1, $x2) - self::EPSILON && $x <= max($x1, $x2) + self::EPSILON
                && $y >= min($y1, $y2) - self::EPSILON && $y <= max($y1, $y2) + self::EPSILON) {
                return 0;
            }

            if (($y1 > $y) !== ($y2 > $y) && $x < ($x2 - $x1) * ($y - $y1) / ($y2 - $y1) + $x1) {
                $inside = ! $inside;
            }
        }

        return $inside ? 1 : -1;
    }

    private function bucket(float $latitude, float $south, float $north): int
    {
        return max(0, min(self::BUCKET_COUNT - 1, (int) floor(($latitude - $south) / ($north - $south) * self::BUCKET_COUNT)));
    }

    private function toWellKnownText(): string
    {
        if ($this->wellKnownText === null) {
            $polygons = [];

            foreach ($this->polygons as $polygon) {
                $rings = [];

                foreach ($polygon as $ring) {
                    $rings[] = '('.implode(',', array_map(fn (array $point): string => $point[0].' '.$point[1], $ring)).')';
                }

                $polygons[] = '('.implode(',', $rings).')';
            }

            $this->wellKnownText = 'MULTIPOLYGON('.implode(',', $polygons).')';
        }

        return $this->wellKnownText;
    }
}
