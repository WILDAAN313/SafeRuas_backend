<?php

namespace App\Models;

use App\Support\IndramayuBoundary;
use Database\Factories\LaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
    /** @use HasFactory<LaporanFactory> */
    use HasFactory;

    protected $table = 'laporan';

    protected $fillable = [
        'user_id',
        'instansi_id',
        'judul',
        'deskripsi',
        'kategori',
        'foto',
        'latitude',
        'longitude',
        'alamat_lokasi',
        'status',
    ];

    /** @param Builder<static> $query */
    #[Scope]
    protected function withinIndramayu(Builder $query): void
    {
        app(IndramayuBoundary::class)->constrain($query);
    }

    public function isWithinIndramayu(): bool
    {
        return app(IndramayuBoundary::class)->contains($this->latitude, $this->longitude);
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'laporan_id');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusLaporan::class, 'laporan_id');
    }
}
