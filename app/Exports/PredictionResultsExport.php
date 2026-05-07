<?php

namespace App\Exports;

use App\Models\PredictionResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PredictionResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = PredictionResult::with('trainingSession')
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if (isset($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }
        if (isset($this->filters['prediction_filter'])) {
            $query->where('predicted_label', $this->filters['prediction_filter']);
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tanggal Prediksi',
            'Nama Responden',
            'Durasi Penggunaan',
            'Frekuensi Akses',
            'Perhatian Konten',
            'Penghayatan',
            'Hasil Prediksi',
            'Confidence (%)',
            'Model Algorithm',
            'Model Accuracy (%)',
            'Training Session ID',
            'Catatan',
            'IP Address',
            'User Agent'
        ];
    }

    /**
     * @param mixed $predictionResult
     */
    public function map($predictionResult): array
    {
        return [
            $predictionResult->id,
            $predictionResult->created_at->format('d/m/Y H:i:s'),
            $predictionResult->nama ?? 'Anonim',
            $predictionResult->durasi_penggunaan,
            $predictionResult->frekuensi_akses,
            $predictionResult->perhatian_konten,
            $predictionResult->penghayatan,
            $predictionResult->predicted_label,
            $predictionResult->confidence_percentage,
            $predictionResult->model_type,
            $predictionResult->model_accuracy,
            $predictionResult->training_session_id,
            $predictionResult->notes,
            $predictionResult->ip_address,
            $predictionResult->user_agent
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:O1' => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ]
        ];
    }
}
