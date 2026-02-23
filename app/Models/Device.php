<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_code',
        'status',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributs = [
        'status' => 'offline',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $device){
            if(empty($device->device_code)){
                $device->device_code = self::generateDeviceCode();
            }
        });
    }
    public static function generateDeviceCode()
    {
        $prefix ="MDUnit-";
        do{
            $hash = strtoupper(md5(random_bytes(32)));
            $code = $prefix . substr($hash, 0, 8);
            // $codeHash = hash('sha256', $code);
        }while(self::where('device_code', $code)->exists());
        return $code;
    }

    public function updateLastSeen()
    {
        $this->update(['last_seen'=>now(), 'status'=>'online']);
    }
    public function checkOnlineStatus(){
        if($this->last_seen){
            $minutesOffline = $this->last_seen->diffInMinutes(now());
            $this->status = $minutesOffline >5 ? 'offline' : 'online';
            $this->save();
        }
    }

    public function getLatestSensorData()
    {
        return $this->sensorDataStill()->latest()->first();
    }
    public function getLatestServerSensorData()
    {
        return $this->serverSensorData()->latest()->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deviceSettings()
    {
        return $this->hasOne(DeviceSettings::class, 'device_id');
    }

    public function sensorData()
    {
        return $this->hasMany(SensorData::class, 'device_id');
    }
    public function sensorDataStill()
    {
        return $this->hasMany(SensorDataStill::class, 'device_id');
    }
    public function serverSensorData(){
        return $this->hasMany(ServerSensorData::class, 'device_id');
    }
    public function sensorRanges(){
        return $this->hasMany(SensorRange::class, 'device_id');
    }

    public function scopeOnline($query)
    {
        return $query->where('status','online');
    }

    public function scopeOffline($query){
        return $query->where('status', 'offline');
    }

    }
