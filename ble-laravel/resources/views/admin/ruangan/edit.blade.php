@extends('layouts.admin')
@section('title', 'Edit Ruangan')

@section('content')
<div class="form-card" style="max-width:680px">
    <div class="form-title">Edit Ruangan</div>

    <form method="POST" action="{{ route('admin.ruangan.update', $ruangan->id_ruangan) }}">
        @csrf @method('PUT')
        <div class="form-grid">

            {{-- ID Ruangan (tidak bisa diubah) --}}
            <div class="form-group">
                <label>ID Ruangan</label>
                <input type="text" value="{{ $ruangan->id_ruangan }}" disabled
                       style="background:#F3F4F6;color:#6B7280;cursor:not-allowed">
                <small style="color:#9CA3AF;font-size:11.5px;margin-top:3px;display:block">
                    ID ruangan tidak dapat diubah setelah dibuat.
                </small>
            </div>

            {{-- Nama Ruangan --}}
            <div class="form-group">
                <label>Nama Ruangan <span class="req">*</span></label>
                <input type="text" name="nama_ruangan"
                       value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}" required>
                @error('nama_ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Beacon Name --}}
            <div class="form-group">
                <label>Beacon Name</label>
                <input type="text" name="beacon_name"
                       value="{{ old('beacon_name', $ruangan->beacon_name) }}"
                       placeholder="cth: BLE-GU805">
                <small style="color:#6B7280;font-size:11.5px;margin-top:3px;display:block">
                    Nama broadcast ESP32. Format: <strong>BLE-</strong> diikuti ID tanpa strip.
                </small>
                @error('beacon_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Beacon UUID --}}
            <div class="form-group">
                <label>Beacon UUID <span class="req">*</span></label>
                <input type="text" name="beacon_uuid"
                       value="{{ old('beacon_uuid', $ruangan->beacon_uuid) }}" required>
                <small style="color:#6B7280;font-size:11.5px;margin-top:3px;display:block">
                    UUID harus sesuai dengan yang ada di kode ESP32 beacon.
                </small>
                @error('beacon_uuid')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Perbarui</button>
            <a href="{{ route('admin.ruangan.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection