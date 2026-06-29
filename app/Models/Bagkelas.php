<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagkelas extends Model
{
    use HasFactory;
    protected $primaryKey = 'idbag';

    public function mustahiqs()
    {
        return $this->hasMany(Mustahiq::class);
    }
}
