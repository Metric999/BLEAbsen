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
        $allJadwals = Jadwal::with(['mataKuliah', 'ruangan'])
            ->where('nidn', $dosen->nidn)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        // Daftar kelas unik dari jadwal dosen
        $kelasList = $allJadwals->pluck('kelas')->unique()->filter()->values();

        // Kelas yang dipilih (default: kelas pertama)
        $selectedKelas = $request->query('kelas', $kelasList->first());

        // Jadwal yang sesuai dengan kelas yang dipilih
        $jadwals = $allJadwals->where('kelas', $selectedKelas)->values();

        // Jadwal yang sedang dipilih
        $selectedId = $request->query('jadwal_id', $jadwals->first()?->id_jadwal);
        
        // Pastikan jadwal yang dipilih valid untuk kelas tersebut
        $selectedJadwal = $jadwals->firstWhere('id_jadwal', $selectedId);
        if (!$selectedJadwal && $jadwals->isNotEmpty()) {
            $selectedJadwal = $jadwals->first();
            $selectedId = $selectedJadwal->id_jadwal;
        }

        // Daftar mahasiswa + status absensi hari ini untuk jadwal terpilih
        $mahasiswas = [];
        $summary    = ['hadir' => 0, 'izin' => 0, 'alpha' => 0];

        if ($selectedKelas && $selectedJadwal) {
            // Ambil semua mahasiswa yang kelasnya sesuai
            $mahasiswas = \App\Models\Mahasiswa::where('kelas', $selectedKelas)
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

            // Hitung summary keseluruhan sesi (bukan hanya sesi yang dipilih)
            $jadwalsHariIni = Jadwal::where('id_matkul', $selectedJadwal->id_matkul)
                ->where('kelas', $selectedKelas)
                ->where('hari', $selectedJadwal->hari)
                ->pluck('id_jadwal');

            $allAbsensiHariIni = Absensi::whereIn('id_jadwal', $jadwalsHariIni)
                ->whereDate('tanggal', today())
                ->get()
                ->groupBy('nim');

            foreach ($mahasiswas as $mhs) {
                $absensiGroup = $allAbsensiHariIni->get($mhs['nim']);
                
                $overallStatus = 'alpha';
                if ($absensiGroup) {
                    $statuses = $absensiGroup->pluck('status');
                    if ($statuses->contains('hadir')) {
                        $overallStatus = 'hadir';
                    } elseif ($statuses->contains('izin')) {
                        $overallStatus = 'izin';
                    }
                }
                
                $summary[$overallStatus] = ($summary[$overallStatus] ?? 0) + 1;
            }
        }

        $isConfirmed = false;
        if ($selectedKelas && $selectedJadwal) {
            $isConfirmed = Absensi::whereIn('id_jadwal', $jadwalsHariIni)
                ->whereDate('tanggal', today())
                ->where('is_confirmed', true)
                ->exists();
        }

        return view('dosen.rekap', [
            'dosen'          => $dosen,
            'kelasList'      => $kelasList,
            'selectedKelas'  => $selectedKelas,
            'jadwals'        => $jadwals,
            'selectedJadwal' => $selectedJadwal,
            'jadwalsHariIni' => $jadwalsHariIni ?? collect(), // pass schedules list
            'allAbsensiHariIni' => $allAbsensiHariIni ?? collect(), // pass detailed attendance
            'mahasiswas'     => $mahasiswas,
            'summary'        => $summary,
            'isConfirmed'    => $isConfirmed,
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

        // Cek apakah sudah dikonfirmasi
        $isConfirmed = Absensi::where('id_jadwal', $jadwal->id_jadwal)
            ->whereDate('tanggal', today())
            ->where('is_confirmed', true)
            ->exists();
            
        if ($isConfirmed) {
            return back()->withErrors(['Ubah Status' => 'Absensi untuk jadwal ini sudah dikonfirmasi dan tidak bisa diubah lagi.']);
        }

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

        // Auto-update untuk sesi berikutnya di hari yang sama untuk matkul dan kelas yang sama
        $jadwalBerikutnya = Jadwal::where('id_matkul', $jadwal->id_matkul)
            ->where('kelas', $jadwal->kelas)
            ->where('hari', $jadwal->hari)
            ->where('jam_mulai', '>', $jadwal->jam_mulai)
            ->get();

        foreach ($jadwalBerikutnya as $jb) {
            Absensi::updateOrCreate(
                [
                    'nim'       => $request->nim,
                    'id_jadwal' => $jb->id_jadwal,
                    'tanggal'   => today(),
                ],
                [
                    'status'     => $request->status,
                    'keterangan' => $request->keterangan,
                ]
            );
        }

        return redirect()
            ->route('dosen.rekap', [
                'kelas'     => $jadwal->kelas,
                'jadwal_id' => $jadwal->id_jadwal
            ])
            ->with('success', 'Status absensi berhasil diperbarui.');
    }

    /**
     * Dosen melakukan konfirmasi absensi untuk hari dan kelas yang dipilih.
     * Semua mahasiswa yang belum diabsen akan di-set menjadi 'alpha',
     * lalu seluruh data absensi untuk jadwal tersebut ditandai is_confirmed = true.
     */
    public function konfirmasiAbsensi(Request $request)
    {
        $request->validate([
            'kelas'     => 'required|string',
            'jadwal_id' => 'required|integer',
        ]);

        $dosen = Auth::guard('dosen')->user();
        $selectedJadwal = Jadwal::where('id_jadwal', $request->jadwal_id)
            ->where('nidn', $dosen->nidn)
            ->firstOrFail();

        // Ambil semua jadwal dosen untuk kelas dan matkul yang sama hari ini
        $jadwalsHariIni = Jadwal::where('id_matkul', $selectedJadwal->id_matkul)
            ->where('kelas', $request->kelas)
            ->where('hari', $selectedJadwal->hari)
            ->get();
            
        $jadwalIds = $jadwalsHariIni->pluck('id_jadwal');

        // Pastikan semua mahasiswa di kelas tersebut memiliki record absensi
        $mahasiswas = \App\Models\Mahasiswa::where('kelas', $request->kelas)->get();

        foreach ($mahasiswas as $mhs) {
            foreach ($jadwalsHariIni as $j) {
                $absensi = Absensi::where('nim', $mhs->nim)
                    ->where('id_jadwal', $j->id_jadwal)
                    ->whereDate('tanggal', today())
                    ->first();

                if (!$absensi) {
                    Absensi::create([
                        'nim'          => $mhs->nim,
                        'id_jadwal'    => $j->id_jadwal,
                        'tanggal'      => today(),
                        'status'       => 'alpha',
                        'is_confirmed' => true,
                    ]);
                } else {
                    $absensi->update(['is_confirmed' => true]);
                }
            }
        }

        return redirect()
            ->route('dosen.rekap', [
                'kelas'     => $request->kelas,
                'jadwal_id' => $selectedJadwal->id_jadwal
            ])
            ->with('success', 'Absensi berhasil dikonfirmasi dan diselesaikan.');
    }

    /**
     * Tampilkan halaman profile dosen
     */
    public function profile()
    {
        $dosen = Auth::guard('dosen')->user();
        return view('dosen.profile', compact('dosen'));
    }

    /**
     * Update password dosen
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:6|confirmed',
        ], [
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
        ]);

        $dosen = Auth::guard('dosen')->user();

        if (!\Hash::check($request->password_lama, $dosen->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $dosen->password = \Hash::make($request->password_baru);
        // Save using Eloquent instance
        $dosen->save();

        return back()->with('success', 'Password berhasil diubah.');
    }
}