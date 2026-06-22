@extends('layouts.admin')
@section('title', 'Edit Mata Kuliah')

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Edit Mata Kuliah</div>

    <form method="POST" action="{{ route('admin.matakuliah.update', $matakuliah->id_matkul) }}">
        @csrf @method('PUT')
        <div class="form-grid">

            <div class="form-group form-full">
                <label>Nama Mata Kuliah <span class="req">*</span></label>
                <input type="text" name="nama_matkul"
                       value="{{ old('nama_matkul', $matakuliah->nama_matkul) }}" required>
                @error('nama_matkul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>SKS <span class="req">*</span></label>
                <input type="number" name="sks"
                       value="{{ old('sks', $matakuliah->sks) }}" min="1" max="6" required>
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Semester <span class="req">*</span></label>
                <select name="semester" required>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('semester', $matakuliah->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Perbarui</button>
            <a href="{{ route('admin.matakuliah.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection
