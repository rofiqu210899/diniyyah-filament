<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kelas extends Model
{
    use HasFactory;

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'kls', 'idkls');
    }
}
