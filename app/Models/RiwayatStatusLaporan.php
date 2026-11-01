<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusLaporan extends Model
{
    protected $table = 'riwayat_status_laporan';

    protected $fillable = [
        'laporan_id',
        'user_id',
        'status_sebelumnya',
        'status_baru',
        'catatan',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
