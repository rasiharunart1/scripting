<?php

namespace App\Exports;

use App\Models\SensorData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SensorDataExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting
{
    public function __construct(
        protected int $deviceId,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function query()
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

        return SensorData::query()
            ->where('device_id', $this->deviceId)
            ->when($start, fn($q) => $q->where('created_at', '>=', $start))
            ->when($end, fn($q) => $q->where('created_at', '<=', $end))
            ->orderBy('created_at', 'desc')
            ->select([
                'created_at',
                'timeDevice',
                'battery_a',
                'battery_b',
                'battery_c',
                'battery_d',
                'temperature_1',
                'temperature_2',
                'pln_volt',
                'pln_current',
                'pln_power',
                'relay_1',
                'relay_2',
            ]);
    }

    public function headings(): array
    {
        return [
            'Waktu Stored DB',
            'Waktu Device Send',
            'Delay (detik)',
            'Battery A (V)',
            'Battery B (V)',
            'Battery C (V)',
            'Battery D (V)',
            'Temperature 1 (°C)',
            'Temperature 2 (°C)',
            'PLN Volt (V)',
            'PLN Current (A)',
            'PLN Power (W)',
            'Relay 1',
            'Relay 2',
        ];
    }

    public function map($row): array
    {
        $timeStore = Carbon::parse($row->created_at);
        $timeDevice = Carbon::parse($row->timeDevice);
        $delay = abs($timeStore->diffInSeconds($timeDevice));
        
        return [
            $timeStore->format('Y-m-d H:i:s'),
            $timeDevice->format('Y-m-d H:i:s'),
            $delay,
            (float) ($row->battery_a ?? 0),
            (float) ($row->battery_b ?? 0),
            (float) ($row->battery_c ?? 0),
            (float) ($row->battery_d ?? 0),
            (float) ($row->temperature_1 ?? 0),
            (float) ($row->temperature_2 ?? 0),
            (float) ($row->pln_volt ?? 0),
            (float) ($row->pln_current ?? 0),
            (float) ($row->pln_power ?? 0),
            (int) ($row->relay_1 ?? 0),
            (int) ($row->relay_2 ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DATETIME,
            'B' => NumberFormat::FORMAT_DATE_DATETIME,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
            'L' => NumberFormat::FORMAT_NUMBER_00,
            'M' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}