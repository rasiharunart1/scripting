<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'temp1_threshold',
        'temp2_threshold',
        'hysteresis',
        'interval_record',
        'charger_mode',
        'charger_threshold_min',
        'charger_threshold_max',
    ];

    protected $casts = [
        'temp1_threshold' => 'float',
        'temp2_threshold' => 'float',
        'hysteresis' => 'float',
        'interval_record'=> 'int',
        'charger_mode' => 'string',
        'charger_threshold_min' => 'float',
        'charger_threshold_max' => 'float',
    ];


    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
