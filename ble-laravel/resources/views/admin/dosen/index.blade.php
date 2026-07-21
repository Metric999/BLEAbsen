@extends('layouts.admin')
@section('title', 'Dosen')

@section('content')

<div class="action-bar">
    <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Dosen
    </a>
</div>

<div class="card">
    <form method="GET">
        <div class="filter-bar">
            <input type="text" name="search" placeholder="Cari Nama / NIDN..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline" style="padding:8px 20px">Cari</button>
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline" style="padding:8px 16px">Reset</a>
        </div>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>NIDN/NIK</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosens as $d)
                <tr>
                    <td>{{ $d->nidn }}</td>
                    <td>{{ $d->nama }}</td>
                    <td>{{ $d->username }}</td>
                    <td style="display:flex;gap:6px;align-items:center">
                        <a href="{{ route('admin.dosen.edit', $d->nidn) }}"
                           class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('admin.dosen.destroy', $d->nidn) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus dosen {{ $d->nama }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:32px;color:#9CA3AF">
                        Belum ada data dosen.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $dosens->withQueryString()->links() }}
</div>
@endsection