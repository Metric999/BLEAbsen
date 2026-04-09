<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'pertemuan_id', 'mahasiswa_id', 'waktu_absensi',
        'rssi', 'jarak_estimasi', 'device_id', 'status'
    ];
    protected $casts = ['waktu_absensi' => 'datetime'];
 
    public function pertemuan()  { return $this->belongsTo(Pertemuan::class); }
    public function mahasiswa()  { return $this->belongsTo(Mahasiswa::class); }
}