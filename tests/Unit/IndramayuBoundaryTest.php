<?php

namespace Tests\Unit;

use App\Support\IndramayuBoundary;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class IndramayuBoundaryTest extends TestCase
{
    #[TestWith([1, 2, true])]
    #[TestWith([5, 5, false])]
    #[TestWith([5, 3, true])]
    #[TestWith([0, 5, true])]
    #[TestWith([0, 0, true])]
    #[TestWith([10.000001, 5, false])]
    #[TestWith([30.5, 21, true])]
    #[TestWith([21, 30.5, false])]
    #[TestWith(['1.0', '2.0', true])]
    #[TestWith([null, 2, false])]
    #[TestWith([1, 'invalid', false])]
    #[TestWith([INF, 2, false])]
    #[TestWith([NAN, 2, false])]
    public function test_polygon_membership_handles_holes_edges_islands_and_invalid_coordinates(mixed $latitude, mixed $longitude, bool $expected): void
    {
        $boundary = new IndramayuBoundary([
            'type' => 'MultiPolygon',
            'coordinates' => [
                [
                    [[0, 0], [10, 0], [10, 10], [0, 10], [0, 0]],
                    [[3, 3], [7, 3], [7, 7], [3, 7], [3, 3]],
                ],
                [[[20, 30], [22, 30], [22, 31], [20, 31], [20, 30]]],
            ],
        ]);

        $this->assertSame($expected, $boundary->contains($latitude, $longitude));
    }

    public function test_concave_polygon_excludes_points_inside_its_rectangle_but_outside_its_shape(): void
    {
        $boundary = new IndramayuBoundary([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [10, 0], [10, 2], [2, 2], [2, 10], [0, 10], [0, 0]]],
        ]);

        $this->assertFalse($boundary->contains(8, 8));
        $this->assertTrue($boundary->contains(8, 1));
        $this->assertTrue($boundary->contains(1, 8));
    }

    public function test_bounds_include_every_island_and_use_latitude_longitude_order_for_leaflet(): void
    {
        $boundary = new IndramayuBoundary([
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[[108, -7], [109, -7], [109, -6], [108, -6], [108, -7]]],
                [[[108.5, -5], [108.6, -5], [108.6, -4.9], [108.5, -4.9], [108.5, -5]]],
            ],
        ]);

        $this->assertSame([[-7.0, 108.0], [-4.9, 109.0]], $boundary->bounds());
    }

    #[TestWith([])]
    #[TestWith([['type' => 'Point', 'coordinates' => [108, -6]]])]
    #[TestWith([['type' => 'MultiPolygon', 'coordinates' => []]])]
    #[TestWith([['type' => 'MultiPolygon', 'coordinates' => 'invalid']])]
    #[TestWith([['type' => 'Polygon', 'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 1]]]]])]
    #[TestWith([['type' => 'Polygon', 'coordinates' => [[[181, 0], [182, 0], [182, 1], [181, 0]]]]])]
    public function test_invalid_boundary_data_is_rejected(array $geometry = []): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IndramayuBoundary($geometry);
    }
}
