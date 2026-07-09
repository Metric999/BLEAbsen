<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – BLE Absen</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1B5FE0 0%, #0F3FA8 50%, #0A2D7A 100%);
        }

        .wrapper { width: 100%; max-width: 420px; padding: 16px; }

        /* ── Logo ── */
        .logo-box { text-align: center; margin-bottom: 28px; }
        .logo-circle {
            width: 80px; height: 80px;
            background: rgba(255,255,255,.15);
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
        }
        .logo-circle svg { width: 42px; height: 42px; }
        .logo-box h1 { color: #fff; font-size: 22px; font-weight: 700; letter-spacing: .5px; }
        .logo-box p  { color: rgba(255,255,255,.7); font-size: 12px; margin-top: 4px;
                        text-transform: uppercase; letter-spacing: 1px; }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 32px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .card h2 { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 4px; }
        .card .subtitle { font-size: 13px; color: #6B7280; margin-bottom: 24px; }

        /* ── Form ── */
        .form-group { margin-bottom: 16px; }
        label {
            display: block; font-size: 13px; font-weight: 500;
            color: #374151; margin-bottom: 5px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #9CA3AF;
        }
        .input-icon svg { width: 16px; height: 16px; display: block; }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1.5px solid #D1D5DB;
            border-radius: 9px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
            appearance: none;
        }
        select { cursor: pointer; }
        input:focus, select:focus {
            border-color: #1B5FE0;
            box-shadow: 0 0 0 3px rgba(27,95,224,.15);
        }
        input.is-invalid, select.is-invalid { border-color: #DC2626; }

        /* toggle password */
        .toggle-pw {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9CA3AF; padding: 0;
        }
        .toggle-pw:hover { color: #374151; }
        .toggle-pw svg { width: 16px; height: 16px; display: block; }

        .invalid-feedback { color: #DC2626; font-size: 12px; margin-top: 4px; }

        /* Alert */
        .alert {
            padding: 11px 14px; border-radius: 8px;
            font-size: 13px; margin-bottom: 18px;
        }
        .alert-danger  { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }

        /* Role badge — warna berubah sesuai pilihan */
        .role-indicator {
            display: inline-block;
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 12px;
            margin-left: 6px;
            font-weight: 500;
            vertical-align: middle;
            transition: all .2s;
        }
        .role-admin { background: #DBEAFE; color: #1E40AF; }
        .role-dosen { background: #D1FAE5; color: #065F46; }

        /* Submit */
        .btn-login {
            width: 100%; padding: 12px;
            background: #1B5FE0; color: #fff;
            border: none; border-radius: 9px;
            font-size: 14.5px; font-weight: 600;
            cursor: pointer; letter-spacing: .3px;
            transition: background .2s, transform .1s;
            margin-top: 6px;
        }
        .btn-login:hover    { background: #1347B4; }
        .btn-login:active   { transform: scale(.99); }
        .btn-login:disabled { background: #93C5FD; cursor: not-allowed; }

        /* footer */
        .footer {
            text-align: center; margin-top: 20px;
            font-size: 12px; color: rgba(255,255,255,.6);
        }
    </style>
</head>
<body>

<div class="wrapper">
    {{-- Logo --}}
    <div class="logo-box">
        <div class="logo-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6.5 6.5 17.5 17.5 12 23 12 1 17.5 6.5 6.5 17.5"/>
            </svg>
        </div>
        <h1>BLE-ABSEN</h1>
        <p>Sistem Informasi Absensi IoT</p>
    </div>

    {{-- Card --}}
    <div class="card">
        <h2>
            Masuk ke Sistem
            <span class="role-indicator role-admin" id="roleIndicator">Admin</span>
        </h2>
        <p class="subtitle">Pilih role lalu masukkan kredensial Anda</p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            {{-- ROLE SELECT --}}
            <div class="form-group">
                <label for="role">Login Sebagai</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                                     c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0
                                     015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                                     m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <select name="role" id="role"
                            onchange="updateRoleStyle(this.value)"
                            class="{{ $errors->has('role') ? 'is-invalid' : '' }}">
                        <option value="admin" {{ old('role','admin')==='admin' ? 'selected':'' }}>
                            Admin
                        </option>
                        <option value="dosen" {{ old('role')==='dosen' ? 'selected':'' }}>
                            Dosen
                        </option>
                    </select>
                </div>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- USERNAME --}}
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        autofocus
                        class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                    >
                </div>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6
                                     a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword()">
                        <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                     9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-login" id="submitBtn">Masuk</button>
        </form>
    </div>

    <div class="footer">&copy; {{ date('Y') }} BLE Absen – Sistem Absensi Berbasis IoT</div>
</div>

<script>
    function updateRoleStyle(role) {
        const el = document.getElementById('roleIndicator');
        if (role === 'admin') {
            el.textContent = 'Admin';
            el.className = 'role-indicator role-admin';
        } else {
            el.textContent = 'Dosen';
            el.className = 'role-indicator role-dosen';
        }
    }

    // Set initial style dari old() jika ada
    updateRoleStyle(document.getElementById('role').value);

    function togglePassword() {
        const input = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        eyeIcon.innerHTML = isHidden
            ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                        a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878
                        l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59
                        m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
                        a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
            : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                        -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Memproses...';
    });
</script>
</body>
</html>