<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instansi extends Model
{
    protected $table = 'instansi';

    protected $fillable = [
        'nama_instansi',
        'alamat',
        'telepon',
        'email',
    ];

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'instansi_id');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class, 'instansi_id');
    }
}
