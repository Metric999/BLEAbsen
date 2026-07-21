@extends('layouts.admin')
@section('title', 'Tambah Dosen')

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Form Tambah Dosen</div>

    <form method="POST" action="{{ route('admin.dosen.store') }}">
        @csrf
        <div class="form-grid">

            <div class="form-group">
                <label>NIDN/NIK <span class="req">*</span></label>
                <input type="text" name="nidn" value="{{ old('nidn') }}" required>
                @error('nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required>
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Username <span class="req">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" placeholder="Kosong = NIDN sebagai password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.dosen.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection