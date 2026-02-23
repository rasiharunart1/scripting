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
    ];

    protected $casts = [
        'temp1_threshold' => 'float',
        'temp2_threshold' => 'float',
        'hysteresis' => 'float',
    ];


    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
