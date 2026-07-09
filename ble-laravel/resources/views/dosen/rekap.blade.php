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
    .header-user  { font-size: 12px; opacity: .85; }
    .btn-logout {
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3);
        color: #fff;
        padding: 6px 12px;
        border-radius: 7px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-logout:hover { background: rgba(255,255,255,.25); }

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
    <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="btn-logout">Keluar</button>
    </form>
</div>

{{-- ── ALERT ── --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="container">

    {{-- ── PILIH JADWAL ── --}}
    <div class="card">
        <div class="card-title">Pilih Jadwal</div>

        @if($jadwals->isEmpty())
            <p style="color:#9ca3af;font-size:14px">Anda belum memiliki jadwal mengajar.</p>
        @else
            {{-- Form GET: reload halaman dengan jadwal_id yang dipilih --}}
            <form method="GET" action="{{ route('dosen.rekap') }}" id="formJadwal">
                <select name="jadwal_id" onchange="document.getElementById('formJadwal').submit()">
                    @foreach($jadwals as $j)
                        <option value="{{ $j->id_jadwal }}"
                            {{ $selectedJadwal?->id_jadwal == $j->id_jadwal ? 'selected' : '' }}>
                            {{ $j->kelas ?? '' }} – {{ $j->hari }}
                            {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}
                            · {{ $j->mataKuliah->nama_matkul }}
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedJadwal)
                <div class="jadwal-info">
                    Ruangan: <strong>{{ $selectedJadwal->id_ruangan }}</strong>
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
                          onclick="toggleEdit('{{ $mhs['nim'] }}')" title="Klik untuk ubah">
                        @if($mhs['status'] === 'hadir') Hadir
                        @elseif($mhs['status'] === 'izin') Izin
                        @else Alpha
                        @endif
                    </span>

                    {{-- Form ubah status (tersembunyi, muncul saat badge diklik) --}}
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
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- ── TOMBOL REKAP ── --}}
    <button class="btn-rekap" onclick="openModal()">Lihat Rekap Absensi</button>
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