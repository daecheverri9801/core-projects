<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanPagosCIExport implements FromArray, WithHeadings, WithStyles
{
    protected $encabezados;
    protected $filas;
    protected $totales;

    public function __construct($encabezados, $filas, $totales)
    {
        $this->encabezados = $encabezados;
        $this->filas       = $filas;
        $this->totales     = $totales;
    }

    public function headings(): array
    {
        return array_merge(
            ['Proyecto', 'Inmueble', 'Cliente'],
            $this->encabezados
        );
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->filas as $f) {
            $row = [
                $f['proyecto'],
                $f['inmueble'],
                $f['cliente'],
            ];

            foreach ($this->encabezados as $m) {
                $row[] = $f['meses'][$m] ?? 0;
            }

            $data[] = $row;
        }

        // Fila TOTAL
        $totalRow = ['TOTAL', '', ''];
        foreach ($this->encabezados as $m) {
            $totalRow[] = $this->totales[$m] ?? 0;
        }

        $data[] = $totalRow;

        return $data;
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
