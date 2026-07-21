<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Absensi – BLE Absen</title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Arial, sans-serif;
        background: #f5f7fb;
        min-height: 100vh;
    }

    /* ── Header ── */
    .header {
        background: #007bff;
        color: white;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .header-title { font-size: 16px; font-weight: bold; }
    .header-user  { font-size: 12px; opacity: .85; cursor: pointer; }
    
    /* ── Dropdown ── */
    .dropdown { position: relative; display: inline-block; }
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 150px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 10px;
    }
    .dropdown-content.show { display: block; }
    .dropdown-content a, .dropdown-content button {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 13px;
        width: 100%;
        text-align: left;
        border: none;
        background: none;
        cursor: pointer;
    }
    .dropdown-content a:hover, .dropdown-content button:hover { background-color: #f1f1f1; }
    .dropdown-content .text-danger { color: #dc3545; }

    /* ── Alert ── */
    .alert {
        margin: 12px 15px 0;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
    }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    /* ── Container ── */
    .container { padding: 15px; max-width: 640px; margin: 0 auto; }

    /* ── Card ── */
    .card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .card-title { font-size: 15px; font-weight: bold; margin-bottom: 10px; }

    /* ── Jadwal selector ── */
    select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #ddd;
        font-size: 14px;
        background: #fff;
        color: #111;
        cursor: pointer;
        outline: none;
    }
    select:focus { border-color: #007bff; }
    .jadwal-info {
        margin-top: 8px;
        font-size: 12px;
        color: #6b7280;
    }

    /* ── Mahasiswa list ── */
    .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        gap: 8px;
    }
    .item:last-child { border-bottom: none; }
    .item-nama { font-size: 14px; color: #1f2937; flex: 1; }
    .item-kelas { font-size: 11px; color: #9ca3af; }

    /* Status badge */
    .status {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 8px;
        font-weight: bold;
        white-space: nowrap;
        cursor: pointer;
        border: none;
        background: none;
    }
    .status-hadir { background: #e6ffed; color: #1a7f37; }
    .status-izin  { background: #fff4e5; color: #b26a00; }
    .status-alpha { background: #ffe5e5; color: #c62828; }

    /* ── Ubah status form (inline) ── */
    .status-select {
        font-size: 11px;
        padding: 4px 6px;
        border-radius: 8px;
        border: 1px solid #ddd;
        background: #fff;
        color: #111;
        cursor: pointer;
    }
    .btn-simpan-status {
        font-size: 11px;
        padding: 4px 10px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 7px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-simpan-status:hover { background: #0056b3; }
    .btn-ubah {
        font-size: 11px;
        padding: 4px 8px;
        background: none;
        border: 1px solid #ddd;
        border-radius: 7px;
        cursor: pointer;
        color: #374151;
    }
    .btn-ubah:hover { background: #f3f4f6; }
    .edit-form { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .hidden { display: none !important; }

    /* ── Rekap button ── */
    .btn-rekap {
        width: 100%;
        padding: 13px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
        margin-bottom: 20px;
    }
    .btn-rekap:hover { background: #0056b3; }

    /* ── Modal rekap ── */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: flex-end;
        z-index: 999;
    }
    .modal.open { display: flex; }
    .modal-content {
        background: white;
        width: 100%;
        max-width: 640px;
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .modal-header h3 { font-size: 16px; font-weight: bold; }
    .close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #6b7280; }
    .boxes { display: flex; gap: 8px; margin-top: 4px; }
    .box {
        flex: 1;
        padding: 14px 10px;
        border-radius: 10px;
        text-align: center;
        background: #e0f0ff;
    }
    .box-hadir { background: #d1fae5; }
    .box-izin  { background: #fff4e5; }
    .box-alpha { background: #fee2e2; }
    .box b { font-size: 22px; display: block; font-weight: 700; }
    .box p { font-size: 12px; margin-top: 4px; color: #374151; }

    /* ── Empty state ── */
    .empty { text-align: center; padding: 30px 0; color: #9ca3af; font-size: 14px; }
</style>
</head>
<body>

{{-- ── HEADER ── --}}
<div class="header">
    <div>
        <div class="header-title">Live Monitoring Absensi</div>
        <div class="header-user">{{ $dosen->nama }}</div>
    </div>
    
    <div class="dropdown">
        <div onclick="toggleDropdown()" style="cursor: pointer; background: rgba(255,255,255,0.2); padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div id="userDropdown" class="dropdown-content">
            <a href="{{ route('dosen.profile') }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="text-danger">Log Out</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        document.getElementById("userDropdown").classList.toggle("show");
    }
    
    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.closest('.dropdown')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>

{{-- ── ALERT ── --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="container">

    {{-- ── PILIH KELAS & JADWAL ── --}}
    <div class="card">
        <div class="card-title">Pilih Kelas & Jadwal</div>

        @if($kelasList->isEmpty())
            <p style="color:#9ca3af;font-size:14px">Anda belum memiliki jadwal mengajar.</p>
        @else
            {{-- Form GET: reload halaman dengan filter kelas & jadwal_id --}}
            <form method="GET" action="{{ route('dosen.rekap') }}" id="formFilter">
                
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 13px; color: #555; font-weight: bold; margin-bottom: 5px; display: block;">Kelas</label>
                    <select name="kelas" onchange="document.getElementById('formFilter').submit()">
                        @foreach($kelasList as $k)
                            <option value="{{ $k }}" {{ $selectedKelas == $k ? 'selected' : '' }}>
                                {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($jadwals->isNotEmpty())
                <div>
                    <label style="font-size: 13px; color: #555; font-weight: bold; margin-bottom: 5px; display: block;">Jadwal</label>
                    <select name="jadwal_id" onchange="document.getElementById('formFilter').submit()">
                        @foreach($jadwals as $j)
                            <option value="{{ $j->id_jadwal }}"
                                {{ $selectedJadwal?->id_jadwal == $j->id_jadwal ? 'selected' : '' }}>
                                {{ $j->hari }}
                                {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}
                                · {{ $j->mataKuliah->nama_matkul }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </form>

            @if($selectedJadwal)
                <div class="jadwal-info">
                    Ruangan: <strong>{{ $selectedJadwal->id_ruangan ?? '-' }}</strong>
                    · Tanggal hari ini: <strong>{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
                </div>
            @endif
        @endif
    </div>

    {{-- ── DAFTAR MAHASISWA ── --}}
    @if($selectedJadwal)
    <div class="card">
        <div class="card-title">Daftar Mahasiswa</div>

        @if(count($mahasiswas) === 0)
            <div class="empty">Belum ada mahasiswa di kelas ini.</div>
        @else
            @foreach($mahasiswas as $mhs)
            <div class="item" id="row-{{ $mhs['nim'] }}">
                {{-- Nama & Kelas --}}
                <div>
                    <div class="item-nama">{{ $mhs['nama'] }}</div>
                    <div class="item-kelas">{{ $mhs['nim'] }} · {{ $mhs['kelas'] }}</div>
                </div>

                {{-- Status badge (klik untuk ubah) --}}
                <div>
                    {{-- Tampilan status saat ini --}}
                    <span class="status status-{{ $mhs['status'] }}" id="badge-{{ $mhs['nim'] }}"
                          @if(!$isConfirmed) onclick="toggleEdit('{{ $mhs['nim'] }}')" title="Klik untuk ubah" @else title="Absensi sudah dikonfirmasi" style="cursor: default; opacity: 0.8;" @endif>
                        @if($mhs['status'] === 'hadir') Hadir
                        @elseif($mhs['status'] === 'izin') Izin
                        @else Alpha
                        @endif
                    </span>

                    {{-- Form ubah status (tersembunyi, muncul saat badge diklik) --}}
                    @if(!$isConfirmed)
                    <form method="POST"
                          action="{{ route('dosen.absensi.ubah', $mhs['id_absensi'] ?? 0) }}"
                          id="editForm-{{ $mhs['nim'] }}"
                          class="edit-form hidden">
                        @csrf
                        <input type="hidden" name="jadwal_id" value="{{ $selectedJadwal->id_jadwal }}">
                        <input type="hidden" name="nim"       value="{{ $mhs['nim'] }}">
                        <select name="status" class="status-select">
                            <option value="hadir" {{ $mhs['status']==='hadir' ? 'selected':'' }}>Hadir</option>
                            <option value="izin"  {{ $mhs['status']==='izin'  ? 'selected':'' }}>Izin</option>
                            <option value="alpha" {{ $mhs['status']==='alpha' ? 'selected':'' }}>Alpha</option>
                        </select>
                        <button type="submit" class="btn-simpan-status">Simpan</button>
                        <button type="button" class="btn-ubah" onclick="toggleEdit('{{ $mhs['nim'] }}')">Batal</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- ── TOMBOL KONFIRMASI / REKAP ── --}}
    @if($isConfirmed)
        <button class="btn-rekap" style="background: #10b981;" onclick="openModal()">Lihat Hasil Rekapitulasi</button>
    @else
        <form method="POST" action="{{ route('dosen.rekap.konfirmasi') }}" onsubmit="return confirm('Yakin ingin konfirmasi absensi? Data yang belum diabsen akan dianggap Alpha dan seluruh data tidak bisa diubah lagi.');">
            @csrf
            <input type="hidden" name="kelas" value="{{ $selectedKelas }}">
            <input type="hidden" name="jadwal_id" value="{{ $selectedJadwal->id_jadwal }}">
            <button type="submit" class="btn-rekap">Konfirmasi & Selesaikan Absensi</button>
        </form>
        <div style="text-align: center; margin-bottom: 20px;">
            <button type="button" class="btn-ubah" style="border: none; color: #007bff; text-decoration: underline;" onclick="openModal()">Lihat rekap sementara</button>
        </div>
    @endif
    
    @endif

</div>

{{-- ── MODAL REKAP ── --}}
<div class="modal" id="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Rekap Absensi Hari Ini</h3>
            <button class="close-btn" onclick="closeModal()">✖</button>
        </div>

        <div class="boxes">
            <div class="box box-hadir">
                <b>{{ $summary['hadir'] }}</b>
                <p>Hadir</p>
            </div>
            <div class="box box-izin">
                <b>{{ $summary['izin'] }}</b>
                <p>Izin</p>
            </div>
            <div class="box box-alpha">
                <b>{{ $summary['alpha'] }}</b>
                <p>Alpha</p>
            </div>
        </div>

        <p style="font-size:12px;color:#9ca3af;margin-top:14px;text-align:center">
            Total {{ ($summary['hadir'] + $summary['izin'] + $summary['alpha']) }} mahasiswa
        </p>

        @if(isset($allAbsensiHariIni) && count($mahasiswas) > 0)
        <div style="margin-top: 20px; max-height: 250px; overflow-y: auto; border-top: 1px solid #eee; padding-top: 15px;">
            <h4 style="font-size: 14px; margin-bottom: 10px; color: #374151;">Detail Per Sesi</h4>
            @foreach($mahasiswas as $mhs)
                @php
                    $absensiGroup = $allAbsensiHariIni->get($mhs['nim']);
                    // Tentukan status keseluruhan
                    $overall = 'Alpha';
                    if ($absensiGroup) {
                        $statuses = $absensiGroup->pluck('status');
                        if ($statuses->contains('hadir')) { $overall = 'Hadir'; }
                        elseif ($statuses->contains('izin')) { $overall = 'Izin'; }
                    }
                @endphp
                <div style="margin-bottom: 10px; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 13px; font-weight: bold; color: #1f2937;">{{ $mhs['nama'] }}</span>
                        <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: {{ $overall=='Hadir' ? '#d1fae5' : ($overall=='Izin' ? '#fff4e5' : '#fee2e2') }}; color: {{ $overall=='Hadir' ? '#065f46' : ($overall=='Izin' ? '#b26a00' : '#991b1b') }};">
                            {{ $overall }}
                        </span>
                    </div>
                    <div style="font-size: 11px; color: #6b7280; display: flex; flex-wrap: wrap; gap: 6px;">
                        Riwayat:
                        @foreach($jadwalsHariIni as $index => $j)
                            @php
                                $statusSesi = 'Alpha';
                                if ($absensiGroup) {
                                    $absSesi = $absensiGroup->firstWhere('id_jadwal', $j);
                                    if ($absSesi) {
                                        $statusSesi = ucfirst($absSesi->status);
                                    }
                                }
                            @endphp
                            <span>Sesi {{ $index + 1 }} ({{ $statusSesi }})</span>
                            @if(!$loop->last) ➔ @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<script>
    function openModal()  { document.getElementById('modal').classList.add('open'); }
    function closeModal() { document.getElementById('modal').classList.remove('open'); }

    // Tutup modal saat klik overlay
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Toggle form ubah status
    function toggleEdit(nim) {
        const badge = document.getElementById('badge-' + nim);
        const form  = document.getElementById('editForm-' + nim);
        badge.classList.toggle('hidden');
        form.classList.toggle('hidden');
    }
</script>

</body>
</html>