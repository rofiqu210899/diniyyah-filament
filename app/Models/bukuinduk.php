<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bukuinduk extends Model
{
    use HasFactory;
    protected $table = 'bukuinduk';
    protected $guarded = [];

    public function madin()
    {
        return $this->belongsTo(Madin::class, 'tkt', 'id');
    }
    public function unitSekolah()
    {
        return $this->belongsTo(unit::class, 'unit', 'id');
    }
    public function Funkelurahan()
    {
        return $this->belongsTo(kelurahan::class, 'des', 'id_kel');
    }
    public function Funkecamatan()
    {
        return $this->belongsTo(kecamatan::class, 'kec', 'id_kec');
    }
    public function Funkabupaten()
    {
        return $this->belongsTo(kabupaten::class, 'kab', 'id_kabkot');
    }
    public function Funprovinsi()
    {
        return $this->belongsTo(provinsi::class, 'prov', 'id_prov');
    }
}
