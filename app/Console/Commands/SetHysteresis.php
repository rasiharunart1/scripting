<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\DeviceSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetHysteresis extends Command
{
    protected $signature = 'sensor:set-hysteresis
                            {--step= : Langsung set ke step tertentu (1-10)}
                            {--start : Mulai eksperimen dari hari ini (step 1)}
                            {--status : Tampilkan status eksperimen saat ini}
                            {--reset : reset nilai step hysteresis}
                            {--prev : Kembali ke step sebelumnya (mundur 1 hari)}
                            {--device= : Device code tertentu. Default: semua device}';

    protected $description = 'Update nilai hysteresis device sesuai jadwal eksperimen harian';

    // const HYSTERESIS_STEPS = [0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0];
    const HYSTERESIS_STEPS = [0,1,2,3,4,5,6,7,8,9,10];
    
    const STATE_FILE = 'experiment/hysteresis_state.json';

    public function handle(): int
    {
        if ($this->option('status')) {
            return $this->showStatus();
        }

        if ($this->option('start')) {
            return $this->startExperiment();
        }

        if ($this->option('step')) {
            return $this->setStep((int) $this->option('step'));
        }

        if ($this->option('prev')) {
            return $this->previousStep();
        }

        if($this->option('reset')){
            return $this->resetStep();
        }

        return $this->runScheduledStep();
    }

    private function startExperiment(): int
    {
        $state = [
            'start_date' => Carbon::today()->toDateString(),
            'current_step' => 1,
            'history' => [],
        ];

        Storage::put(self::STATE_FILE, json_encode($state, JSON_PRETTY_PRINT));

        $this->info('Eksperimen dimulai: ' . $state['start_date']);
        return $this->applyHysteresis(1, $state);
    }

    private function runScheduledStep(): int
    {
        if (!Storage::exists(self::STATE_FILE)) {
            $this->error('Eksperimen belum dimulai. Jalankan: php artisan sensor:set-hysteresis --start');
            return 1;
        }

        $state = json_decode(Storage::get(self::STATE_FILE), true);
        $startDate = Carbon::parse($state['start_date']);
        $today = Carbon::today();

        $dayNumber = (int) ($startDate->diffInDays($today) + 1);

        if ($dayNumber > count(self::HYSTERESIS_STEPS)) {
            $this->warn("Eksperimen sudah selesai (hari ke-{$dayNumber} dari " . count(self::HYSTERESIS_STEPS) . ").");
            return 0;
        }

        $state['current_step'] = $dayNumber;
        return $this->applyHysteresis($dayNumber, $state);
    }

    private function setStep(int $step): int
    {
        $maxStep = count(self::HYSTERESIS_STEPS);
        if ($step < 1 || $step > $maxStep) {
            $this->error("Step harus antara 1 dan {$maxStep}");
            return 1;
        }

        $state = Storage::exists(self::STATE_FILE)
            ? json_decode(Storage::get(self::STATE_FILE), true)
            : ['start_date' => Carbon::today()->toDateString(), 'history' => []];

        // Sesuaikan start_date agar jadwal harian besok tetap sinkron
        $state['start_date'] = Carbon::today()->copy()->subDays($step - 1)->toDateString();
        $state['current_step'] = $step;
        return $this->applyHysteresis($step, $state);
    }

    private function previousStep(): int
    {
        if (!Storage::exists(self::STATE_FILE)) {
            $this->error('Eksperimen belum dimulai.');
            return 1;
        }

        $state = json_decode(Storage::get(self::STATE_FILE), true);
        $currentStep = $state['current_step'] ?? 1;

        if ($currentStep <= 1) {
            $this->warn('Sudah berada di step pertama (1). Tidak bisa mundur lagi.');
            return 0;
        }

        $prevStep = $currentStep - 1;

        // Majukan start_date 1 hari agar scheduler besok tidak loncat ke depan
        $state['start_date'] = Carbon::parse($state['start_date'])->addDay()->toDateString();
        $state['current_step'] = $prevStep;

        $this->info("Mundur ke step sebelumnya: {$prevStep}");
        return $this->applyHysteresis($prevStep, $state);
    }

    private function applyHysteresis(int $step, array $state): int
    {
        $value = self::HYSTERESIS_STEPS[$step - 1];
        $deviceCode = $this->option('device');

        $query = Device::with('deviceSettings');
        if ($deviceCode) {
            $query->where('device_code', $deviceCode);
        }

        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn('Tidak ada device ditemukan.');
            return 1;
        }

        $updated = 0;
        foreach ($devices as $device) {
            if (!$device->deviceSettings) {
                $this->line("  [{$device->device_code}] Tidak ada settings, skip.");
                continue;
            }

            $device->deviceSettings->update(['hysteresis' => $value]);
            $this->info("  [{$device->device_code}] Hysteresis → {$value} °C");
            $updated++;
        }

        // Simpan history
        $state['history'][] = [
            'date'  => Carbon::today()->toDateString(),
            'step'  => $step,
            'value' => $value,
        ];
        Storage::put(self::STATE_FILE, json_encode($state, JSON_PRETTY_PRINT));

        $this->info("Hari ke-{$step}: hysteresis = {$value} °C ({$updated} device diupdate)");
        return 0;
    }

    private function showStatus(): int
    {
        if (!Storage::exists(self::STATE_FILE)) {
            $this->warn('Eksperimen belum dimulai.');
            return 0;
        }

        $state = json_decode(Storage::get(self::STATE_FILE), true);
        $startDate = Carbon::parse($state['start_date']);
        $today = Carbon::today();
        $dayNumber = (int) ($startDate->diffInDays($today) + 1);

        $this->line('');
        $this->info('=== Status Eksperimen Hysteresis ===');
        $this->line("Tanggal mulai : {$state['start_date']}");
        $this->line("Hari ke-      : {$dayNumber} / " . count(self::HYSTERESIS_STEPS));
        $this->line("Step saat ini : {$state['current_step']}");

        $currentVal = self::HYSTERESIS_STEPS[($state['current_step'] - 1)] ?? '-';
        $this->line("Hysteresis    : {$currentVal} °C");

        $this->line('');
        $this->line('Jadwal nilai:');
        $headers = ['Step', 'Hari ke-', 'Tanggal', 'Hysteresis', 'Status'];
        $rows = [];
        foreach (self::HYSTERESIS_STEPS as $i => $val) {
            $stepNum = $i + 1;
            $stepDate = $startDate->copy()->addDays($i)->toDateString();
            $status = $dayNumber > $stepNum ? '✓ Selesai' : ($dayNumber === $stepNum ? '→ Hari ini' : '  Belum');
            $rows[] = [$stepNum, $i + 1, $stepDate, "{$val} °C", $status];
        }
        $this->table($headers, $rows);

        if (!empty($state['history'])) {
            $this->line('');
            $this->line('Riwayat perubahan:');
            $this->table(['Tanggal', 'Step', 'Nilai'], array_map(fn($h) => [
                $h['date'], $h['step'], "{$h['value']} °C"
            ], $state['history']));
        }

        return 0;
    }
    
    private function resetStep():int
    {
        if (Storage::exists(self::STATE_FILE)){
            Storage::delete(self::STATE_FILE);
            $this->info('Riwayat step experiment berhasil dihapus. status kembali awal');
        }else{
            $this->info('durung mulai cok...');
        }
        
       $updated = DeviceSettings::query()->update(['hysteresis' => 0]);
       $this->info("Info: Nilai hysteresis semua device telah di-reset ke 0 °C ({$updated} device).");

        return 0;
    }
}
