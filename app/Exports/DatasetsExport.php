<?php

namespace App\Exports;

use App\Models\Dataset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DatasetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Dataset::with('respondent')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Responden',
            'NIM',
            'Jurusan', 
            'Semester',
            'Durasi Penggunaan',
            'Frekuensi Akses',
            'Perhatian Konten',
            'Penghayatan',
            'Label Intensitas',
            'Training Data',
            'Tanggal Input'
        ];
    }

    /**
     * @param mixed $dataset
     * @return array
     */
    public function map($dataset): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $dataset->respondent->nama,
            $dataset->respondent->nim,
            $dataset->respondent->jurusan,
            $dataset->respondent->semester,
            $dataset->durasi_penggunaan,
            $dataset->frekuensi_akses,
            $dataset->perhatian_konten,
            $dataset->penghayatan,
            strtoupper($dataset->label_intensitas),
            $dataset->is_training_data ? 'Ya' : 'Tidak',
            $dataset->created_at->format('d/m/Y H:i')
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        return [
            // Header style
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '2E7D32'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            
            // All data rows
            "A2:L{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'DDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Center align for specific columns
            "A:A" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "E:E" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "H:L" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}