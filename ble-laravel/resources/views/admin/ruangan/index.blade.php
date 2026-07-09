@extends('layouts.admin')
@section('title', 'Ruangan')

@section('content')

<div class="action-bar">
    <a href="{{ route('admin.ruangan.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Ruangan
    </a>
</div>

<div class="card">
    <form method="GET">
        <div class="filter-bar">
            <input type="text" name="search" placeholder="Cari ID atau nama ruangan..."
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline" style="padding:8px 20px">Cari</button>
            <a href="{{ route('admin.ruangan.index') }}" class="btn btn-outline"
               style="padding:8px 16px">Reset</a>
        </div>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Ruangan</th>
                    <th>Nama Ruangan</th>
                    <th>Beacon Name</th>
                    <th>Beacon UUID</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ruangans as $r)
                <tr>
                    <td>{{ $loop->iteration + ($ruangans->currentPage() - 1) * $ruangans->perPage() }}</td>
                    <td>
                        <span style="font-weight:600;color:#1D4ED8;background:#EFF6FF;
                                     padding:3px 8px;border-radius:5px;font-size:13px">
                            {{ $r->id_ruangan }}
                        </span>
                    </td>
                    <td>{{ $r->nama_ruangan }}</td>
                    <td>
                        @if($r->beacon_name)
                            <code style="background:#F3F4F6;padding:2px 7px;border-radius:5px;
                                         font-size:12px;color:#374151">
                                {{ $r->beacon_name }}
                            </code>
                        @else
                            <span style="color:#9CA3AF;font-size:12px">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:12px;color:#6B7280;font-family:monospace">
                            {{ Str::limit($r->beacon_uuid, 30) }}
                        </span>
                    </td>
                    <td style="display:flex;gap:6px;align-items:center">
                        <a href="{{ route('admin.ruangan.edit', $r->id_ruangan) }}"
                           class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('admin.ruangan.destroy', $r->id_ruangan) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus ruangan {{ $r->id_ruangan }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:#9CA3AF">
                        Belum ada data ruangan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $ruangans->withQueryString()->links() }}
</div>
@endsection