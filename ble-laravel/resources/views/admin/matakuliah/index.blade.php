@extends('layouts.admin')
@section('title', 'Mata Kuliah')

@section('content')

<div class="action-bar">
    <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Mata Kuliah
    </a>
</div>

<div class="card">
    <form method="GET">
        <div class="filter-bar">
            <input type="text" name="search" placeholder="Cari Nama Mata Kuliah..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline" style="padding:8px 20px">Cari</button>
            <a href="{{ route('admin.matakuliah.index') }}" class="btn btn-outline"
               style="padding:8px 16px">Reset</a>
        </div>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Ruangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matakuliahs as $mk)
                <tr>
                    <td>{{ $loop->iteration + ($matakuliahs->currentPage() - 1) * $matakuliahs->perPage() }}</td>
                    <td style="font-weight:500">{{ $mk->nama_matkul }}</td>
                    <td>{{ $mk->sks }} SKS</td>
                    <td>Semester {{ $mk->semester }}</td>

                    {{-- Kolom Ruangan --}}
                    <td>
                        @if($mk->ruangans->isEmpty())
                            <span style="font-size:12px;color:#9CA3AF">—</span>
                        @else
                            <div style="display:flex;flex-wrap:wrap;gap:4px">
                                @foreach($mk->ruangans as $r)
                                    <span style="
                                        display:inline-block;
                                        font-size:11px;
                                        padding:3px 8px;
                                        background:#EFF6FF;
                                        color:#1D4ED8;
                                        border-radius:5px;
                                        font-weight:500;
                                        border:1px solid #BFDBFE;
                                    ">{{ $r->id_ruangan }}</span>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <td style="display:flex;gap:6px;align-items:center">
                        <a href="{{ route('admin.matakuliah.edit', $mk->id_matkul) }}"
                           class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('admin.matakuliah.destroy', $mk->id_matkul) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus mata kuliah {{ $mk->nama_matkul }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:#9CA3AF">
                        Belum ada data mata kuliah.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $matakuliahs->withQueryString()->links() }}
</div>
@endsection