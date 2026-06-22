<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $matakuliahs = MataKuliah::query()
            ->when($request->search, fn($q) =>
                $q->where('nama_matkul', 'like', "%{$request->search}%"))
            ->orderBy('semester')
            ->orderBy('nama_matkul')
            ->paginate(20);

        return view('admin.matakuliah.index', compact('matakuliahs'));
    }

    public function create()
    {
        return view('admin.matakuliah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'sks'         => 'required|integer|min:1|max:6',
            'semester'    => 'required|integer|min:1|max:8',
        ]);

        MataKuliah::create($validated);

        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $matakuliah)
    {
        return view('admin.matakuliah.edit', compact('matakuliah'));
    }

    public function update(Request $request, MataKuliah $matakuliah)
    {
        $validated = $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'sks'         => 'required|integer|min:1|max:6',
            'semester'    => 'required|integer|min:1|max:8',
        ]);

        $matakuliah->update($validated);

        return redirect()->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $matakuliah)
    {
        $matakuliah->delete();
        return back()->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
