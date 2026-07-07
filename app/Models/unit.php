<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class unit extends Model
{
    protected $table = 'unit';
    use HasFactory;

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'unit', 'id');
    }

    public function jurusan()
    {
        return $this->hasMany(jurusan::class, 'unit', 'id');
    }
}
