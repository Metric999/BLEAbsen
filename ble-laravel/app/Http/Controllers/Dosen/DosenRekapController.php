<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenRekapController extends Controller
{
    /**
     * Tampilkan halaman rekap live absensi dosen.
     *
     * Query string opsional:
     *  ?jadwal_id=5  → tampilkan daftar hadir untuk jadwal tersebut
     */
    public function index(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();

        // Ambil semua jadwal milik dosen ini
        $jadwals = Jadwal::with(['mataKuliah', 'ruangan'])
            ->where('nidn', $dosen->nidn)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        // Jadwal yang sedang dipilih (default: jadwal pertama)
        $selectedId = $request->query('jadwal_id', $jadwals->first()?->id_jadwal);
        $selectedJadwal = $jadwals->firstWhere('id_jadwal', $selectedId);

        // Daftar mahasiswa + status absensi hari ini untuk jadwal terpilih
        $mahasiswas = [];
        $summary    = ['hadir' => 0, 'izin' => 0, 'alpha' => 0];

        if ($selectedJadwal) {
            $mahasiswas = $selectedJadwal->mahasiswas()
                ->orderBy('nama')
                ->get()
                ->map(function ($mhs) use ($selectedJadwal) {
                    $absensi = Absensi::where('nim', $mhs->nim)
                        ->where('id_jadwal', $selectedJadwal->id_jadwal)
                        ->whereDate('tanggal', today())
                        ->first();

                    return [
                        'nim'        => $mhs->nim,
                        'nama'       => $mhs->nama,
                        'kelas'      => $mhs->kelas,
                        'status'     => $absensi?->status ?? 'alpha',
                        'id_absensi' => $absensi?->id_absensi,
                    ];
                });

            // Hitung summary
            foreach ($mahasiswas as $mhs) {
                $summary[$mhs['status']] = ($summary[$mhs['status']] ?? 0) + 1;
            }
        }

        return view('dosen.rekap', [
            'dosen'          => $dosen,
            'jadwals'        => $jadwals,
            'selectedJadwal' => $selectedJadwal,
            'mahasiswas'     => $mahasiswas,
            'summary'        => $summary,
        ]);
    }

    /**
     * Dosen mengubah status absensi mahasiswa.
     * Dipanggil via form POST dari halaman rekap.
     * Jika absensi belum ada (mahasiswa belum scan BLE), buat baru.
     */
    public function ubahStatus(Request $request, int $idAbsensi)
    {
        $request->validate([
            'status'     => 'required|in:hadir,izin,alpha',
            'jadwal_id'  => 'required|integer',
            'nim'        => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $dosen = Auth::guard('dosen')->user();

        // Pastikan jadwal ini milik dosen yang login
        $jadwal = Jadwal::where('id_jadwal', $request->jadwal_id)
            ->where('nidn', $dosen->nidn)
            ->firstOrFail();

        if ($idAbsensi === 0) {
            // Absensi belum ada → buat baru (dosen yang input manual)
            Absensi::create([
                'nim'        => $request->nim,
                'id_jadwal'  => $jadwal->id_jadwal,
                'tanggal'    => today(),
                'status'     => $request->status,
                'keterangan' => $request->keterangan,
            ]);
        } else {
            Absensi::where('id_absensi', $idAbsensi)
                ->where('id_jadwal', $jadwal->id_jadwal)
                ->update([
                    'status'     => $request->status,
                    'keterangan' => $request->keterangan,
                ]);
        }

        return redirect()
            ->route('dosen.rekap', ['jadwal_id' => $jadwal->id_jadwal])
            ->with('success', 'Status absensi berhasil diperbarui.');
    }
}