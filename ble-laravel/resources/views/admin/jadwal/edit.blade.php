@extends('layouts.admin')
@section('title', 'Edit Jadwal')

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Form Edit Jadwal</div>

    <form method="POST" action="{{ route('admin.jadwal.update', $jadwal->id_jadwal) }}">
        @csrf @method('PUT')
        <div class="form-grid">

            <div class="form-group">
                <label>Kelas <span class="req">*</span></label>
                <select name="kelas" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($kelasList as $kls)
                        <option value="{{ $kls }}"
                            {{ old('kelas',$jadwal->kelas)==$kls ? 'selected':'' }}>
                            {{ $kls }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Hari <span class="req">*</span></label>
                <select name="hari" required>
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                        <option value="{{ $h }}"
                            {{ old('hari',$jadwal->hari)==$h ? 'selected':'' }}>{{ $h }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Jam Mulai <span class="req">*</span></label>
                <input type="time" name="jam_mulai"
                       value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}"
                       required>
                @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Jam Selesai <span class="req">*</span></label>
                <input type="time" name="jam_selesai"
                       value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}"
                       required>
                @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Ruangan <span class="req">*</span></label>
                <select name="id_ruangan" required>
                    @foreach($ruangans as $r)
                        <option value="{{ $r->id_ruangan }}"
                            {{ old('id_ruangan',$jadwal->id_ruangan)==$r->id_ruangan ? 'selected':'' }}>
                            {{ $r->id_ruangan }} – {{ $r->nama_ruangan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group form-full">
                <label>Mata Kuliah <span class="req">*</span></label>
                <select name="id_matkul" required>
                    @foreach($matkuls as $mk)
                        <option value="{{ $mk->id_matkul }}"
                            {{ old('id_matkul',$jadwal->id_matkul)==$mk->id_matkul ? 'selected':'' }}>
                            {{ $mk->nama_matkul }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group form-full">
                <label>Dosen <span class="req">*</span></label>
                <select name="nidn" required>
                    @foreach($dosens as $d)
                        <option value="{{ $d->nidn }}"
                            {{ old('nidn',$jadwal->nidn)==$d->nidn ? 'selected':'' }}>
                            {{ $d->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.jadwal.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection