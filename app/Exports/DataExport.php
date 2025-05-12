<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class DataExport implements FromCollection, WithHeadings
{
    private $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            return collect($row)->values()->toArray();
        });
    }

    public function headings(): array
    {
        if ($this->data->isEmpty()) {
            return [];
        }
        return array_keys($this->data->first());
    }
}
