<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class DataHafalan extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    /**
     * Relasi ke Jenjang Madin
     */
    public function madin()
    {
        return $this->belongsTo(Madin::class, 'tkt', 'id');
    }
}
