<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagmadin extends Model
{
    use HasFactory;
    protected $primaryKey = 'idbm';

    public function mustahiqs()
    {
        return $this->hasMany(Mustahiq::class);
    }
}
