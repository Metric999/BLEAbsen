<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function store(Request $request)
{
    $absen = Absensi::create([
        'mahasiswa_id' => $request->mahasiswa_id,
        'mata_kuliah_id' => $request->mata_kuliah_id,
        'uuid_beacon' => $request->uuid,
        'rssi' => $request->rssi,
        'waktu' => now()
    ]);

    return response()->json([
        'message' => 'Absensi berhasil',
        'data' => $absen
    ]);
}
}
