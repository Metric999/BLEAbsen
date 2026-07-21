<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Validasi jadwal sebelum absen (dipanggil setelah BLE terdeteksi).
     * Cek: mahasiswa terdaftar + hari + jam + ruangan cocok.
     */
    public function checkJadwal(Request $request): JsonResponse
    {
        $request->validate([
            'id_ruangan' => 'required|string|exists:ruangans,id_ruangan',
        ]);

        $mahasiswa = $request->user();
        $now       = Carbon::now();
        $hariIni   = $this->getHariIndonesia($now->dayOfWeek);

        $jadwal = Jadwal::with(['mataKuliah', 'ruangan', 'dosen'])
            ->where('kelas', $mahasiswa->kelas)
            ->where('hari', $hariIni)
            ->where('id_ruangan', $request->id_ruangan)
            ->whereTime('jam_mulai', '<=', $now->format('H:i:s'))
            ->whereTime('jam_selesai', '>=', $now->format('H:i:s'))
            ->first();

        if (! $jadwal) {
            return response()->json([
                'status'  => 'invalid',
                'message' => 'Tidak ada jadwal perkuliahan di ruangan atau waktu ini.',
            ], 422);
        }

        $sudahAbsen = Absensi::where('nim', $mahasiswa->nim)
            ->where('id_jadwal', $jadwal->id_jadwal)
            ->whereDate('tanggal', today())
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status'  => 'already',
                'message' => 'Anda sudah melakukan absensi hari ini.',
                'jadwal'  => $jadwal,
            ]);
        }

        return response()->json([
            'status'  => 'valid',
            'message' => 'Silakan Absen (Waktu Sesuai)',
            'jadwal'  => $jadwal,
        ]);
    }

    /**
     * Simpan absensi mahasiswa.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_jadwal' => 'required|integer|exists:jadwals,id_jadwal',
        ]);

        $mahasiswa = $request->user();

        // Cegah duplikat
        $existing = Absensi::where('nim', $mahasiswa->nim)
            ->where('id_jadwal', $request->id_jadwal)
            ->whereDate('tanggal', today())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Absensi sudah tercatat.',
                'data'    => $existing,
            ]);
        }

        $absensi = Absensi::create([
            'nim'       => $mahasiswa->nim,
            'id_jadwal' => $request->id_jadwal,
            'tanggal'   => today(),
            'status'    => 'hadir',
        ]);

        // Auto-absen untuk sesi berikutnya di hari yang sama untuk matkul dan kelas yang sama
        $jadwalSekarang = Jadwal::find($request->id_jadwal);
        if ($jadwalSekarang) {
            $jadwalBerikutnya = Jadwal::where('id_matkul', $jadwalSekarang->id_matkul)
                ->where('kelas', $jadwalSekarang->kelas)
                ->where('hari', $jadwalSekarang->hari)
                ->where('jam_mulai', '>', $jadwalSekarang->jam_mulai)
                ->get();

            foreach ($jadwalBerikutnya as $jb) {
                Absensi::firstOrCreate(
                    [
                        'nim'       => $mahasiswa->nim,
                        'id_jadwal' => $jb->id_jadwal,
                        'tanggal'   => today(),
                    ],
                    [
                        'status'    => 'hadir',
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Absensi berhasil dicatat.',
            'data'    => $absensi->load('jadwal.mataKuliah', 'jadwal.ruangan'),
        ], 201);
    }

    /**
     * Riwayat absensi mahasiswa yang sedang login.
     */
    public function riwayat(Request $request): JsonResponse
    {
        $data = Absensi::with(['jadwal.mataKuliah', 'jadwal.ruangan', 'jadwal.dosen'])
            ->where('nim', $request->user()->nim)
            ->orderByDesc('tanggal')
            ->paginate(20);

        return response()->json($data);
    }

    // ----------------------------------------------------------------

    private function getHariIndonesia(int $dayOfWeek): string
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ][$dayOfWeek] ?? '';
    }
}