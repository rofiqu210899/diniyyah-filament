<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kelurahan extends Model
{
    use HasFactory;
    protected $table = 'kelurahan';

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'des', 'id_kel');
    }
}
