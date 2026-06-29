<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madin extends Model
{

    use HasFactory;
    protected $table = 'madin';

    public function mustahiqs()
    {
        return $this->hasMany(Mustahiq::class, 'tkt', 'id');
    }
    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'tkt', 'id');
    }
}
