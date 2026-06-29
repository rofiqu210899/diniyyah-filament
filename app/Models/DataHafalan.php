<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataHafalan extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relasi ke Jenjang Madin
     */
    public function madin()
    {
        return $this->belongsTo(Madin::class, 'tkt', 'id');
    }
}
