<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $ruangans = Ruangan::query()
            ->when($request->search, fn($q) =>
                $q->where('id_ruangan', 'like', "%{$request->search}%")
                  ->orWhere('nama_ruangan', 'like', "%{$request->search}%"))
            ->orderBy('id_ruangan')
            ->paginate(20);

        return view('admin.ruangan.index', compact('ruangans'));
    }

    public function create()
    {
        return view('admin.ruangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ruangan'   => 'required|string|max:20|unique:ruangans,id_ruangan',
            'nama_ruangan' => 'required|string|max:100',
            'beacon_uuid'  => 'required|string|max:100|unique:ruangans,beacon_uuid',
            'beacon_name'  => 'nullable|string|max:50',
        ], [
            'id_ruangan.unique'  => 'ID ruangan sudah digunakan.',
            'beacon_uuid.unique' => 'UUID beacon sudah digunakan oleh ruangan lain.',
        ]);

        Ruangan::create($request->only(['id_ruangan', 'nama_ruangan', 'beacon_uuid', 'beacon_name']));

        return redirect()->route('admin.ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Simpan ruangan baru via AJAX (dari modal di halaman lain, mis. form Mata Kuliah).
     */
    public function ajaxStore(Request $request)
    {
        $validated = $request->validate([
            'id_ruangan'   => 'required|string|max:20|unique:ruangans,id_ruangan',
            'nama_ruangan' => 'required|string|max:100',
            'beacon_uuid'  => 'required|string|max:100|unique:ruangans,beacon_uuid',
            'beacon_name'  => 'nullable|string|max:50',
        ], [
            'id_ruangan.unique'  => 'ID ruangan sudah digunakan.',
            'beacon_uuid.unique' => 'UUID beacon sudah digunakan oleh ruangan lain.',
        ]);

        $ruangan = Ruangan::create($validated);

        return response()->json([
            'success'  => true,
            'ruangan'  => [
                'id'   => $ruangan->id_ruangan,
                'nama' => $ruangan->nama_ruangan,
            ],
        ]);
    }

    public function edit(Ruangan $ruangan)
    {
        return view('admin.ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'beacon_uuid'  => "required|string|max:100|unique:ruangans,beacon_uuid,{$ruangan->id_ruangan},id_ruangan",
            'beacon_name'  => 'nullable|string|max:50',
        ], [
            'beacon_uuid.unique' => 'UUID beacon sudah digunakan oleh ruangan lain.',
        ]);

        $ruangan->update($request->only(['nama_ruangan', 'beacon_uuid', 'beacon_name']));

        return redirect()->route('admin.ruangan.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan)
    {
        $ruangan->delete();
        return back()->with('success', 'Ruangan berhasil dihapus.');
    }
}