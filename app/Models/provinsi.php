<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class provinsi extends Model
{
    use HasFactory;
    protected $table = 'provinsi';

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'prov', 'id_prov');
    }
}
