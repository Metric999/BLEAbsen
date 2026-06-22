@extends('layouts.admin')
@section('title', 'Tambah Mata Kuliah')

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Form Tambah Mata Kuliah</div>

    <form method="POST" action="{{ route('admin.matakuliah.store') }}">
        @csrf
        <div class="form-grid">

            <div class="form-group form-full">
                <label>Nama Mata Kuliah <span class="req">*</span></label>
                <input type="text" name="nama_matkul" value="{{ old('nama_matkul') }}" required
                       placeholder="cth: Pemrograman Web">
                @error('nama_matkul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>SKS <span class="req">*</span></label>
                <input type="number" name="sks" value="{{ old('sks', 2) }}" min="1" max="6" required>
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Semester <span class="req">*</span></label>
                <select name="semester" required>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.matakuliah.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection
