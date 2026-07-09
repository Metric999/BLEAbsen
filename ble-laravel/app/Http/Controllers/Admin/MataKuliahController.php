<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $matakuliahs = MataKuliah::with('ruangans')           // eager-load ruangan
            ->when($request->search, fn($q) =>
                $q->where('nama_matkul', 'like', "%{$request->search}%"))
            ->orderBy('semester')
            ->orderBy('nama_matkul')
            ->paginate(20);

        return view('admin.matakuliah.index', compact('matakuliahs'));
    }

    public function create()
    {
        $ruangans = Ruangan::orderBy('id_ruangan')->get();    // semua ruangan untuk checkbox

        return view('admin.matakuliah.create', compact('ruangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'sks'         => 'required|integer|min:1|max:6',
            'semester'    => 'required|integer|min:1|max:8',
            'ruangans'    => 'nullable|array',
            'ruangans.*'  => 'exists:ruangans,id_ruangan',
        ]);

        // Simpan mata kuliah
        $matakuliah = MataKuliah::create([
            'nama_matkul' => $validated['nama_matkul'],
            'sks'         => $validated['sks'],
            'semester'    => $validated['semester'],
        ]);

        // Kaitkan ruangan yang dipilih ke pivot table
        if (! empty($validated['ruangans'])) {
            $matakuliah->ruangans()->sync($validated['ruangans']);
        }

        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $matakuliah)
    {
        $ruangans        = Ruangan::orderBy('id_ruangan')->get();
        $ruanganTerpilih = $matakuliah->ruangans->pluck('id_ruangan')->toArray();

        return view('admin.matakuliah.edit', compact('matakuliah', 'ruangans', 'ruanganTerpilih'));
    }

    public function update(Request $request, MataKuliah $matakuliah)
    {
        $validated = $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'sks'         => 'required|integer|min:1|max:6',
            'semester'    => 'required|integer|min:1|max:8',
            'ruangans'    => 'nullable|array',
            'ruangans.*'  => 'exists:ruangans,id_ruangan',
        ]);

        $matakuliah->update([
            'nama_matkul' => $validated['nama_matkul'],
            'sks'         => $validated['sks'],
            'semester'    => $validated['semester'],
        ]);

        // sync: hapus yang tidak dipilih, tambah yang baru dipilih
        $matakuliah->ruangans()->sync($validated['ruangans'] ?? []);

        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $matakuliah)
    {
        // sync([]) otomatis hapus pivot sebelum delete (karena cascadeOnDelete)
        $matakuliah->delete();

        return back()->with('success', 'Mata kuliah berhasil dihapus.');
    }
}