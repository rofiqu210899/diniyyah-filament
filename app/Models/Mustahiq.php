<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mustahiq extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke Jenjang Madin
     */
    public function madin()
    {
        return $this->belongsTo(Madin::class, 'tkt', 'id');
    }

    /**
     * Relasi ke Tahun Ajaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Scope: filter mustahiq berdasarkan tahun ajaran aktif
     */
    public function scopeAktif($query)
    {
        $tahunAjaran = TahunAjaran::getAktif();

        if ($tahunAjaran) {
            return $query->where('tahun_ajaran_id', $tahunAjaran->id);
        }

        return $query->whereRaw('1 = 0'); // kosongkan jika tidak ada tahun ajaran aktif
    }

    /**
     * Accessor: Label jenis kelamin
     */
    public function getJkLabelAttribute(): string
    {
        return match ($this->jk) {
            1 => 'Putra',
            2 => 'Putri',
            default => '-',
        };
    }
}
