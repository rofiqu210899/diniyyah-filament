<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kabupaten extends Model
{
    use HasFactory;
    protected $table = 'kabupaten';

    public function bukuinduk()
    {
        return $this->hasMany(bukuinduk::class, 'kab', 'id_kabkot');
    }
}
