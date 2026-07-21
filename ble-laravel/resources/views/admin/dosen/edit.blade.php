@extends('layouts.admin')
@section('title', 'Edit Dosen')

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Form Edit Dosen</div>

    <form method="POST" action="{{ route('admin.dosen.update', $dosen->nidn) }}">
        @csrf @method('PUT')
        <div class="form-grid">

            <div class="form-group">
                <label>NIDN/NIK</label>
                <input type="text" value="{{ $dosen->nidn }}" disabled
                       style="background:#F3F4F6;color:#6B7280;cursor:not-allowed">
            </div>

            <div class="form-group">
                <label>Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama',$dosen->nama) }}" required>
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Username <span class="req">*</span></label>
                <input type="text" name="username"
                       value="{{ old('username',$dosen->username) }}" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Password Baru <small style="color:#9CA3AF">(kosong = tidak berubah)</small></label>
                <input type="password" name="password">
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.dosen.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection