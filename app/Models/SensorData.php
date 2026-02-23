<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
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
        'timeDevice',
        'temp1_threshold',
        'temp2_threshold',
        'hysteresis',
    ];

    protected $casts = [
        'battery_a' => 'float',
        'battery_b' => 'float',
        'battery_c' => 'float',
        'battery_d' => 'float',
        'temperature_1' => 'float',
        'temperature_2' => 'float',
        'pln_volt' => 'float',
        'pln_current' => 'float',
        'pln_power' => 'float',
        'relay_1' => 'integer',
        'relay_2' => 'integer',
        'timeDevice' => 'string',
        'temp1_threshold' => 'float',  
        'temp2_threshold' => 'float',   
        'hysteresis' => 'float',        
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
    public function deviceSetting(){
        return $this->hasMany(DeviceSettings::class, 'device_id');
    }

}
