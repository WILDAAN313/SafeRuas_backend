<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penugasan extends Model
{
    protected $table = 'penugasan';

    protected $fillable = [
        'laporan_id',
        'petugas_id',
        'admin_id',
        'catatan',
        'tanggal_penugasan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penugasan' => 'datetime',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
