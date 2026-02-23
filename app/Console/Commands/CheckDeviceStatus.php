<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class CheckDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-device-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cek status device';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       $devices = Device::all();
       $offlineCount = 0;

       foreach ($devices as $device) {
            $oldStatus = $device->status;
            $device->checkOnlineStatus();

            if($device->status ==='offline' &&$oldStatus ==='online')
            {
                $this->warn("device {$device->device_code} Offline");
                $offlineCount++;
            }
        # code...
       }
       $this->info("checked ". $devices->count(). " device. {$offlineCount} Offline.");
       return 0;
    }
}
