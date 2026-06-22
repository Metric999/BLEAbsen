<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLE Absen – @yield('title','Admin')</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --blue:#1B5FE0;--blue-d:#1347B4;
            --green:#16A34A;--red:#DC2626;
            --light:#F0F2F5;--white:#FFFFFF;
            --border:#E5E7EB;--text:#1F2937;--text2:#6B7280;
            --sw:162px;
        }
        body{font-family:'Segoe UI',sans-serif;font-size:14px;background:var(--light);color:var(--text);display:flex;min-height:100vh}

        /* ══ Sidebar ══ */
        aside{
            width:var(--sw);background:var(--blue);color:#fff;
            display:flex;flex-direction:column;
            position:fixed;height:100vh;top:0;left:0;z-index:100;
        }
        .brand{padding:16px 14px 12px;border-bottom:1px solid rgba(255,255,255,.15)}
        .brand-name{font-size:14.5px;font-weight:700;letter-spacing:.3px}
        .brand-sub{font-size:10px;opacity:.68;margin-top:2px;line-height:1.35}
        .nav-label{font-size:10px;text-transform:uppercase;letter-spacing:.8px;opacity:.55;padding:14px 14px 4px}
        nav a{
            display:flex;align-items:center;gap:9px;padding:10px 14px;
            color:rgba(255,255,255,.82);text-decoration:none;font-size:13px;
            transition:background .15s;
        }
        nav a:hover,nav a.active{background:rgba(255,255,255,.18);color:#fff}
        nav a svg{width:16px;height:16px;flex-shrink:0}
        .sidebar-foot{margin-top:auto;padding:14px}
        .btn-keluar{
            display:block;width:100%;padding:9px;text-align:center;
            background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);
            color:#fff;border-radius:7px;font-size:13px;cursor:pointer;
            text-decoration:none;transition:background .15s;
        }
        .btn-keluar:hover{background:rgba(255,255,255,.22)}

        /* ══ Main ══ */
        main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{
            background:var(--white);border-bottom:1px solid var(--border);
            padding:0 24px;height:56px;display:flex;align-items:center;
            justify-content:space-between;position:sticky;top:0;z-index:10;
        }
        .topbar-title{font-size:15px;font-weight:600;color:#111827}
        .topbar-date{font-size:13px;color:var(--text2)}
        .content{padding:22px 24px;flex:1}

        /* ══ Alerts ══ */
        .alert{padding:11px 15px;border-radius:8px;margin-bottom:16px;font-size:13px}
        .alert-success{background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0}
        .alert-danger {background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5}

        /* ══ Dashboard cards ══ */
        .dash-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .dash-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
        .dash-card-img{
            height:150px;background:#EEF2FF;
            display:flex;align-items:center;justify-content:center;
        }
        .dash-card-img svg{width:52px;height:52px;color:#93C5FD}
        .dash-card-label{background:var(--blue);color:#fff;padding:12px 16px;font-size:13.5px;font-weight:500}

        /* ══ Content card ══ */
        .card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px 22px}

        /* ══ Action bar ══ */
        .action-bar{display:flex;justify-content:flex-end;margin-bottom:14px}

        /* ══ Filter bar ══ */
        .filter-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px}
        .filter-bar input[type=text]{flex:1;min-width:160px}

        /* ══ Table ══ */
        .tbl-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:13.5px}
        th{
            background:#F9FAFB;text-align:left;padding:10px 16px;
            font-weight:600;color:#374151;border-bottom:1px solid var(--border);
            font-size:13px;
        }
        td{padding:10px 16px;border-bottom:1px solid #F3F4F6;color:#374151;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tbody tr:hover td{background:#FAFAFA}

        /* ══ Buttons ══ */
        .btn{
            display:inline-flex;align-items:center;gap:5px;
            padding:8px 18px;border-radius:7px;font-size:13px;
            font-weight:500;text-decoration:none;border:none;cursor:pointer;
            transition:background .15s;white-space:nowrap;
        }
        .btn-primary{background:var(--blue);color:#fff}
        .btn-primary:hover{background:var(--blue-d);color:#fff}
        .btn-danger{background:var(--red);color:#fff}
        .btn-danger:hover{background:#B91C1C}
        .btn-outline{background:#fff;border:1px solid var(--border);color:#374151}
        .btn-outline:hover{background:#F3F4F6}
        .btn-sm{padding:4px 11px;font-size:12px}

        /* ══ Form ══ */
        .form-card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:28px 28px}
        .form-title{font-size:15px;font-weight:600;color:#111827;margin-bottom:22px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px 28px}
        .form-full{grid-column:1/-1}
        .form-group{display:flex;flex-direction:column;gap:5px}
        label{font-size:13px;font-weight:500;color:#374151}
        .req{color:var(--red);margin-left:2px}
        input[type=text],input[type=password],input[type=number],
        input[type=date],input[type=time],select,textarea{
            padding:9px 11px;border:1.5px solid var(--border);border-radius:7px;
            font-size:13.5px;color:#111827;background:#fff;outline:none;
            transition:border-color .2s,box-shadow .2s;width:100%;
        }
        select{
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236B7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 12px center;
            padding-right:32px;cursor:pointer;
        }
        input:focus,select:focus,textarea:focus{
            border-color:var(--blue);box-shadow:0 0 0 3px rgba(27,95,224,.1)
        }
        .invalid-feedback{color:var(--red);font-size:12px;margin-top:1px}
        .form-actions{display:flex;align-items:center;gap:14px;margin-top:26px;padding-top:4px}
        .btn-cancel{background:none;border:none;color:var(--text2);font-size:13px;cursor:pointer;padding:4px 0}
        .btn-cancel:hover{color:var(--text)}

        /* ══ Pagination ══ */
        .pagination{display:flex;gap:4px;margin-top:16px;flex-wrap:wrap}
        .pagination a,.pagination span{
            padding:5px 10px;border-radius:6px;font-size:13px;
            border:1px solid var(--border);color:#374151;text-decoration:none;
        }
        .pagination .active{background:var(--blue);color:#fff;border-color:var(--blue)}
        .pagination a:hover:not(.active){background:#F3F4F6}
    </style>
    @stack('styles')
</head>
<body>

{{-- ── Sidebar ── --}}
<aside>
    <div class="brand">
        <div class="brand-name">BLE-ABSEN</div>
        <div class="brand-sub">Sistem Informasi Absensi IoT</div>
    </div>
    <nav>
        <div class="nav-label">Menu Utama</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active':'' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
            Dashboard
        </a>
        <div class="nav-label">Kelola Data</div>
        <a href="{{ route('admin.mahasiswa.index') }}" class="{{ request()->routeIs('admin.mahasiswa.*') ? 'active':'' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Mahasiswa
        </a>
        <a href="{{ route('admin.dosen.index') }}" class="{{ request()->routeIs('admin.dosen.*') ? 'active':'' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Dosen
        </a>
        <a href="{{ route('admin.matakuliah.index') }}" class="{{ request()->routeIs('admin.matakuliah.*') ? 'active':'' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Mata Kuliah
        </a>
        <a href="{{ route('admin.jadwal.index') }}" class="{{ request()->routeIs('admin.jadwal.*') ? 'active':'' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Jadwal
        </a>
    </nav>
    <div class="sidebar-foot">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-keluar">Keluar</button>
        </form>
    </div>
</aside>

{{-- ── Main ── --}}
<main>
    <div class="topbar">
        <span class="topbar-title">Dashboard</span>
        <span class="topbar-date">{{ now()->locale('en')->isoFormat('dddd, D MMMM YYYY') }}</span>
    </div>
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>