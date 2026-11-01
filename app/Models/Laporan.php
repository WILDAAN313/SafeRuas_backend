<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
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
