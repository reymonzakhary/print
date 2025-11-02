<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection , WithStartRow , WithHeadingRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function headings(): array
	{
		return $this->collection()->first()->keys();
	}
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        return $rows();
    }
}
