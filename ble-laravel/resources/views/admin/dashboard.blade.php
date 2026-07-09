@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="dash-cards">

    {{-- Mahasiswa --}}
    <a href="{{ route('admin.mahasiswa.index') }}" style="text-decoration:none">
        <div class="dash-card">
            <div class="dash-card-img">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                             c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0
                             015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                             m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="dash-card-label">Mahasiswa</div>
        </div>
    </a>

    {{-- Dosen --}}
    <a href="{{ route('admin.dosen.index') }}" style="text-decoration:none">
        <div class="dash-card">
            <div class="dash-card-img">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="dash-card-label">Dosen</div>
        </div>
    </a>

    {{-- MataKuliah --}}
    <a href="{{ route('admin.matakuliah.index') }}" style="text-decoration:none">
        <div class="dash-card">
            <div class="dash-card-img">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="dash-card-label">Mata Kuliah</div>
        </div>
    </a>

    {{-- Jadwal --}}
    <a href="{{ route('admin.jadwal.index') }}" style="text-decoration:none">
        <div class="dash-card">
            <div class="dash-card-img">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7
                             a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="dash-card-label">Jadwal</div>
        </div>
    </a>

</div>
@endsection