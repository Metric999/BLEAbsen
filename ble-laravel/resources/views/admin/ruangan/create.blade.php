@extends('layouts.admin')
@section('title', 'Tambah Ruangan')

@section('content')
<div class="form-card" style="max-width:680px">
    <div class="form-title">Form Tambah Ruangan</div>

    <form method="POST" action="{{ route('admin.ruangan.store') }}">
        @csrf
        <div class="form-grid">

            {{-- ID Ruangan --}}
            <div class="form-group">
                <label>ID Ruangan <span class="req">*</span></label>
                <input type="text" name="id_ruangan" value="{{ old('id_ruangan') }}"
                       placeholder="cth: GU-805" required style="text-transform:uppercase">
                <small style="color:#6B7280;font-size:11.5px;margin-top:3px;display:block">
                    Harus unik. Gunakan format gedung-nomor, cth: GU-805, TA-11A
                </small>
                @error('id_ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Nama Ruangan --}}
            <div class="form-group">
                <label>Nama Ruangan <span class="req">*</span></label>
                <input type="text" name="nama_ruangan" value="{{ old('nama_ruangan') }}"
                       placeholder="cth: Gedung Utama Lantai 8" required>
                @error('nama_ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Beacon Name --}}
            <div class="form-group">
                <label>Beacon Name</label>
                <input type="text" name="beacon_name" value="{{ old('beacon_name') }}"
                       placeholder="cth: BLE-GU805">
                <small style="color:#6B7280;font-size:11.5px;margin-top:3px;display:block">
                    Nama broadcast ESP32. Format: <strong>BLE-</strong> diikuti ID tanpa strip.
                    Kosongkan jika belum ada beacon.
                </small>
                @error('beacon_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Beacon UUID --}}
            <div class="form-group">
                <label>Beacon UUID <span class="req">*</span></label>
                <input type="text" name="beacon_uuid" value="{{ old('beacon_uuid') }}"
                       placeholder="cth: 4fafc201-1fb5-459e-8fcc-c5c9c331914b" required>
                <small style="color:#6B7280;font-size:11.5px;margin-top:3px;display:block">
                    UUID unik dari ESP32 beacon. Generate di
                    <a href="https://www.uuidgenerator.net/" target="_blank"
                       style="color:#1B5FE0">uuidgenerator.net</a>
                </small>
                @error('beacon_uuid')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.ruangan.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection