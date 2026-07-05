<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPertingkatanExport implements WithMultipleSheets
{
    protected $tahunAjaranId;

    public function __construct($tahunAjaranId)
    {
        $this->tahunAjaranId = $tahunAjaranId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new RekapPertingkatanSheet($this->tahunAjaranId, 1, 'ULA'),
            new RekapPertingkatanSheet($this->tahunAjaranId, 2, 'WUSTHO'),
            new RekapPertingkatanSheet($this->tahunAjaranId, 3, 'ULYA'),
        ];
    }
}
