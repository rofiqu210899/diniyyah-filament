<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HafalanSantri extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function santri()
    {
        return $this->belongsTo(bukuinduk::class, 'santri_id', 'id');
    }

    public function dataHafalan()
    {
        return $this->belongsTo(DataHafalan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
