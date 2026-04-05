<?php

namespace App\Console\Commands;

use App\Exports\SensorDataExport;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportDailySensorLog extends Command
{
    protected $signature = 'sensor:export-daily
                            {--date= : Tanggal yang diekspor (Y-m-d). Default: kemarin}
                            {--device= : Device code tertentu. Default: semua device}';

    protected $description = 'Export log sensor harian ke file Excel dan simpan ke storage';

    public function handle(): int
    {
        $dateInput = $this->option('date');
        $deviceCode = $this->option('device');

        $date = $dateInput ? Carbon::parse($dateInput) : Carbon::yesterday();
        $dateStr = $date->format('Y-m-d');

        $this->info("Export log sensor tanggal: {$dateStr}");

        $query = Device::query();
        if ($deviceCode) {
            $query->where('device_code', $deviceCode);
        }

        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn('Tidak ada device ditemukan.');
            return 1;
        }

        $exported = 0;

        foreach ($devices as $device) {
            $filename = "sensor_export_{$device->device_code}_{$dateStr}.xlsx";
            $path = "exports/daily/{$filename}";

            $rowCount = \App\Models\SensorData::where('device_id', $device->id)
                ->whereDate('created_at', $date)
                ->count();

            if ($rowCount === 0) {
                $this->line("  [{$device->device_code}] Tidak ada data pada {$dateStr}, skip.");
                continue;
            }

            Excel::store(
                new SensorDataExport($device->id, $dateStr, $dateStr),
                $path,
                'local'
            );

            $this->info("  [{$device->device_code}] {$rowCount} baris → storage/{$path}");
            $exported++;
        }

        $this->info("Selesai. {$exported} dari {$devices->count()} device diekspor.");
        return 0;
    }
}
