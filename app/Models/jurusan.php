<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jurusan extends Model
{
    protected $table = 'jurusan';
    use HasFactory;

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'jur', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(unit::class, 'unit', 'id');
    }
}
