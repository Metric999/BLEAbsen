<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $jadwals = Jadwal::with(['mataKuliah', 'ruangan', 'dosen'])
            ->when($request->search, fn($q) =>
                $q->where('kelas', 'like', "%{$request->search}%"))
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->paginate(20);

        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $matkuls   = MataKuliah::orderBy('nama_matkul')->get();
        $ruangans  = Ruangan::orderBy('id_ruangan')->get();
        $dosens    = Dosen::orderBy('nama')->get();
        $kelasList = Mahasiswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.jadwal.create', compact('matkuls', 'ruangans', 'dosens', 'kelasList'));
    }

    /**
     * Store batch jadwal dari grid mingguan.
     * Request structure:
     *   kelas    = "IF4C-Pagi"
     *   semester = 4
     *   jadwals  = [
     *     0 => [hari, jam_mulai, jam_selesai, id_matkul, nidn, id_ruangan],
     *     1 => [...],
     *     ...
     *   ]
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas'                    => 'required|string|max:20',
            'semester'                 => 'required|integer|min:1|max:8',
            'jadwals'                  => 'required|array|min:1',
            'jadwals.*.hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jadwals.*.jam_mulai'      => 'required|date_format:H:i:s',
            'jadwals.*.jam_selesai'    => 'required|date_format:H:i:s',
            'jadwals.*.id_matkul'      => 'required|exists:mata_kuliahs,id_matkul',
            'jadwals.*.nidn'           => 'required|exists:dosens,nidn',
            'jadwals.*.id_ruangan'     => 'required|exists:ruangans,id_ruangan',
        ], [
            'jadwals.required'         => 'Belum ada jadwal yang diisi pada grid.',
            'jadwals.*.hari.required'  => 'Hari tidak valid.',
        ]);

        $kelas    = $request->kelas;
        $semester = $request->semester;
        $inserted = 0;

        foreach ($request->jadwals as $item) {
            // Skip duplikat (kelas + hari + jam_mulai sudah ada)
            $exists = Jadwal::where('kelas', $kelas)
                ->where('hari', $item['hari'])
                ->where('jam_mulai', $item['jam_mulai'])
                ->exists();

            if ($exists) continue;

            Jadwal::create([
                'kelas'       => $kelas,
                'hari'        => $item['hari'],
                'jam_mulai'   => $item['jam_mulai'],
                'jam_selesai' => $item['jam_selesai'],
                'id_matkul'   => $item['id_matkul'],
                'nidn'        => $item['nidn'],
                'id_ruangan'  => $item['id_ruangan'],
            ]);
            $inserted++;
        }

        // Sync mahasiswa dengan kelas yang sama ke jadwal baru
        $jadwalIds = Jadwal::where('kelas', $kelas)->pluck('id_jadwal');
        $mahasiswas = Mahasiswa::where('kelas', $kelas)->get();
        foreach ($mahasiswas as $mhs) {
            $mhs->jadwals()->syncWithoutDetaching($jadwalIds->toArray());
        }

        return redirect()->route('admin.jadwal.index')
            ->with('success', "Berhasil menyimpan {$inserted} jadwal untuk kelas {$kelas}.");
    }

    public function edit(Jadwal $jadwal)
    {
        $matkuls   = MataKuliah::orderBy('nama_matkul')->get();
        $ruangans  = Ruangan::orderBy('id_ruangan')->get();
        $dosens    = Dosen::orderBy('nama')->get();
        $kelasList = Mahasiswa::distinct()->orderBy('kelas')->pluck('kelas');

        return view('admin.jadwal.edit', compact('jadwal', 'matkuls', 'ruangans', 'dosens', 'kelasList'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'kelas'       => 'required|string|max:20',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'id_matkul'   => 'required|exists:mata_kuliahs,id_matkul',
            'id_ruangan'  => 'required|exists:ruangans,id_ruangan',
            'nidn'        => 'required|exists:dosens,nidn',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);

        $jadwal->update([
            'kelas'       => $validated['kelas'],
            'hari'        => $validated['hari'],
            'jam_mulai'   => $validated['jam_mulai'] . ':00',
            'jam_selesai' => $validated['jam_selesai'] . ':00',
            'id_matkul'   => $validated['id_matkul'],
            'id_ruangan'  => $validated['id_ruangan'],
            'nidn'        => $validated['nidn'],
        ]);

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }

    private function parseWaktu(string $waktu): array
    {
        $parts = explode('-', $waktu);
        return [
            str_replace('.', ':', trim($parts[0])) . ':00',
            str_replace('.', ':', trim($parts[1] ?? '00:00')) . ':00',
        ];
    }
}