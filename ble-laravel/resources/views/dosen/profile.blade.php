<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Dosen – BLE Absen</title>
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
        gap: 15px;
    }
    .header-title { font-size: 16px; font-weight: bold; flex: 1; }
    .btn-back {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Alert ── */
    .alert {
        margin: 15px 15px 0;
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 14px;
    }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    /* ── Container ── */
    .container { padding: 15px; max-width: 640px; margin: 0 auto; }

    /* ── Card ── */
    .card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    
    .profile-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    .profile-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .profile-label { font-size: 12px; color: #6b7280; margin-bottom: 5px; }
    .profile-value { font-size: 16px; font-weight: bold; color: #111; }

    /* ── Form ganti password ── */
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        color: #374151;
        margin-bottom: 6px;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 14px;
    }
    .form-group input:focus {
        outline: none;
        border-color: #007bff;
    }
    .btn-submit {
        width: 100%;
        padding: 14px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }
    .btn-submit:hover {
        background: #0056b3;
    }
    .card-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #111;
    }
</style>
</head>
<body>

<div class="header">
    <button onclick="window.location.href='{{ route('dosen.rekap') }}'" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
    </button>
    <div class="header-title">Profile Dosen</div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul style="padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container">
    <div class="card">
        <div class="profile-item">
            <div class="profile-label">NIDN</div>
            <div class="profile-value">{{ $dosen->nidn }}</div>
        </div>
        <div class="profile-item">
            <div class="profile-label">Nama</div>
            <div class="profile-value">{{ $dosen->nama }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Ganti Password</div>
        <form method="POST" action="{{ route('dosen.profile.password') }}">
            @csrf
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="password_lama" required placeholder="Masukkan password lama">
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" required placeholder="Masukkan password baru">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_baru_confirmation" required placeholder="Konfirmasi password baru">
            </div>
            <button type="submit" class="btn-submit">Simpan Password</button>
        </form>
    </div>
</div>

</body>
</html>
