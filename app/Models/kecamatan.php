<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kecamatan extends Model
{
    use HasFactory;
    protected $table = 'kecamatan';

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'kec', 'id_kec');
    }
}
