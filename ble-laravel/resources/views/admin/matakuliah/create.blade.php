@extends('layouts.admin')
@section('title', 'Tambah Mata Kuliah')

@push('styles')
<style>
/* ── Ruangan list ── */
.room-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }

.room-row {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px;
    background: #F9FAFB;
    border: 1.5px solid #E5E7EB;
    border-radius: 9px;
    animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

.room-row select {
    flex: 1;
    padding: 8px 10px;
    border: 1.5px solid #D1D5DB;
    border-radius: 7px;
    font-size: 13.5px;
    color: #111827;
    background: #fff;
    cursor: pointer;
    outline: none;
}
.room-row select:focus { border-color: #1B5FE0; box-shadow: 0 0 0 3px rgba(27,95,224,.1); }

.room-badge {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: #1D4ED8;
    background: #EFF6FF; border: 1px solid #BFDBFE;
    padding: 4px 10px; border-radius: 6px;
    white-space: nowrap;
}

.btn-remove-room {
    display: flex; align-items: center; justify-content: center;
    width: 28px; height: 28px;
    background: #FEE2E2; color: #DC2626;
    border: none; border-radius: 7px;
    cursor: pointer; font-size: 14px; flex-shrink: 0;
    transition: background .15s;
}
.btn-remove-room:hover { background: #FECACA; }

.btn-add-room {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: #fff; color: #1B5FE0;
    border: 1.5px dashed #93C5FD;
    border-radius: 9px; font-size: 13px;
    font-weight: 500; cursor: pointer;
    transition: all .15s;
}
.btn-add-room:hover { background: #EFF6FF; border-color: #1B5FE0; }
.btn-add-room:disabled { opacity: .4; cursor: not-allowed; }

.empty-room-msg {
    padding: 14px;
    text-align: center;
    color: #9CA3AF;
    font-size: 13px;
    border: 1.5px dashed #E5E7EB;
    border-radius: 9px;
    margin-bottom: 8px;
}
</style>
@endpush

@section('content')
<div class="form-card" style="max-width:780px">
    <div class="form-title">Form Tambah Mata Kuliah</div>

    <form method="POST" action="{{ route('admin.matakuliah.store') }}" id="formMatkul">
        @csrf
        <div class="form-grid">

            {{-- Nama Mata Kuliah --}}
            <div class="form-group form-full">
                <label>Nama Mata Kuliah <span class="req">*</span></label>
                <input type="text" name="nama_matkul" value="{{ old('nama_matkul') }}"
                       placeholder="cth: Pemrograman Web" required>
                @error('nama_matkul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- SKS --}}
            <div class="form-group">
                <label>SKS <span class="req">*</span></label>
                <input type="number" name="sks" value="{{ old('sks', 2) }}"
                       min="1" max="6" required>
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Semester --}}
            <div class="form-group">
                <label>Semester <span class="req">*</span></label>
                <select name="semester" required>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        {{-- ── RUANGAN DINAMIS ── --}}
        <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #E5E7EB;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div>
                    <span style="font-size:13.5px; font-weight:600; color:#111827">Ruangan</span>
                    <span style="font-size:12px; color:#6B7280; margin-left:6px">
                        (pilih satu atau lebih ruangan untuk mata kuliah ini)
                    </span>
                </div>
                {{-- Counter ruangan terpilih --}}
                <span id="roomCounter" style="font-size:12px; color:#6B7280; display:none">
                    <span id="roomCount">0</span> ruangan dipilih
                </span>
            </div>

            @error('ruangans')
                <div class="invalid-feedback" style="margin-bottom:8px;">{{ $message }}</div>
            @enderror

            @if($ruangans->isEmpty())
                {{-- Tidak ada ruangan sama sekali di database --}}
                <div style="padding:16px; background:#FEF3C7; border-radius:9px;
                            border:1px solid #FDE68A; font-size:13px; color:#92400E;">
                    ⚠️ Belum ada ruangan terdaftar di sistem.
                    <a href="{{ route('admin.ruangan.create') }}" style="color:#1B5FE0; font-weight:500;">
                        Tambah ruangan terlebih dahulu
                    </a>
                </div>
            @else
                {{-- List ruangan yang sudah ditambahkan --}}
                <div id="roomList" class="room-list">
                    {{-- Baris akan ditambahkan oleh JS --}}
                </div>

                {{-- Pesan kosong --}}
                <div id="emptyMsg" class="empty-room-msg">
                    Belum ada ruangan dipilih. Klik tombol di bawah untuk menambahkan.
                </div>

                {{-- Tombol tambah ruangan --}}
                <button type="button" class="btn-add-room" id="btnAddRoom" onclick="addRoomRow()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Ruangan
                </button>
            @endif
        </div>

        <div class="form-actions" style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.matakuliah.index') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

{{-- Data ruangan untuk JS (semua opsi yang tersedia) --}}
<script>
const AVAILABLE_ROOMS = @json($ruangans->map(fn($r) => [
    'id'   => $r->id_ruangan,
    'nama' => $r->nama_ruangan,
])->values());

const OLD_SELECTED = @json(old('ruangans', []));

let rowIndex = 0;

// ── Tambah satu baris dropdown ruangan ──────────────────────────────
function addRoomRow(selectedId = '') {
    // Cek apakah semua ruangan sudah dipilih
    const usedIds = getSelectedIds();
    const remaining = AVAILABLE_ROOMS.filter(r => !usedIds.includes(r.id) || r.id === selectedId);

    if (remaining.length === 0) {
        alert('Semua ruangan sudah ditambahkan.');
        return;
    }

    rowIndex++;
    const idx  = rowIndex;
    const list = document.getElementById('roomList');

    const row = document.createElement('div');
    row.className  = 'room-row';
    row.id         = 'room-row-' + idx;

    // Ikon ruangan
    row.innerHTML = `
        <svg width="16" height="16" fill="none" stroke="#6B7280" viewBox="0 0 24 24" style="flex-shrink:0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5
                     M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>

        <select name="ruangans[]" onchange="onRoomChange(this, ${idx})" required>
            <option value="">-- Pilih Ruangan --</option>
        </select>

        <div class="room-badge" id="room-badge-${idx}" style="display:none"></div>

        <button type="button" class="btn-remove-room" onclick="removeRow(${idx})" title="Hapus">
            ✕
        </button>
    `;

    list.appendChild(row);

    // Isi opsi dropdown (hanya yang belum dipilih di baris lain)
    refreshDropdownOptions(idx, selectedId);

    updateUI();
}

// ── Saat dropdown berubah: update badge info ruangan ────────────────
function onRoomChange(select, idx) {
    const badge = document.getElementById('room-badge-' + idx);
    const room  = AVAILABLE_ROOMS.find(r => r.id === select.value);

    if (room) {
        badge.textContent = room.nama;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }

    // Refresh semua dropdown agar opsi yang sudah dipilih tidak muncul di baris lain
    refreshAllDropdowns();
    updateUI();
}

// ── Hapus satu baris ────────────────────────────────────────────────
function removeRow(idx) {
    const row = document.getElementById('room-row-' + idx);
    if (row) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(10px)';
        row.style.transition = 'all .15s ease';
        setTimeout(() => {
            row.remove();
            refreshAllDropdowns();
            updateUI();
        }, 150);
    }
}

// ── Refresh opsi satu dropdown (exclude yang sudah dipilih di baris lain) ──
function refreshDropdownOptions(idx, keepSelected = '') {
    const usedIds = getSelectedIds(idx);
    const select  = document.querySelector(`#room-row-${idx} select`);
    if (!select) return;

    const current = keepSelected || select.value;
    select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';

    AVAILABLE_ROOMS.forEach(r => {
        if (!usedIds.includes(r.id) || r.id === current) {
            const opt      = document.createElement('option');
            opt.value      = r.id;
            opt.textContent = r.id + ' — ' + r.nama;
            if (r.id === current) opt.selected = true;
            select.appendChild(opt);
        }
    });
}

// ── Refresh semua dropdown setelah perubahan ────────────────────────
function refreshAllDropdowns() {
    document.querySelectorAll('.room-row').forEach(row => {
        const idx    = row.id.replace('room-row-', '');
        const select = row.querySelector('select');
        refreshDropdownOptions(idx, select?.value || '');
    });
}

// ── Ambil semua ID yang sudah dipilih (kecuali baris tertentu) ─────
function getSelectedIds(excludeIdx = null) {
    const ids = [];
    document.querySelectorAll('.room-row').forEach(row => {
        const idx    = row.id.replace('room-row-', '');
        const select = row.querySelector('select');
        if (idx != excludeIdx && select && select.value) {
            ids.push(select.value);
        }
    });
    return ids;
}

// ── Update tampilan counter & empty state ──────────────────────────
function updateUI() {
    const rows     = document.querySelectorAll('.room-row');
    const emptyMsg = document.getElementById('emptyMsg');
    const counter  = document.getElementById('roomCounter');
    const countEl  = document.getElementById('roomCount');
    const btnAdd   = document.getElementById('btnAddRoom');

    if (rows.length === 0) {
        emptyMsg.style.display = 'block';
        counter.style.display  = 'none';
    } else {
        emptyMsg.style.display = 'none';
        counter.style.display  = 'inline';
        countEl.textContent    = rows.length;
    }

    // Nonaktifkan tombol jika semua ruangan sudah dipilih
    const usedCount = getSelectedIds().length;
    if (btnAdd) {
        btnAdd.disabled = (usedCount >= AVAILABLE_ROOMS.length);
    }
}

// ── Validasi sebelum submit ─────────────────────────────────────────
document.getElementById('formMatkul').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.room-row select');
    for (const select of rows) {
        if (!select.value) {
            e.preventDefault();
            alert('Ada baris ruangan yang belum dipilih. Pilih ruangan atau hapus baris tersebut.');
            select.focus();
            return;
        }
    }
});

// ── Init: isi ulang dari old() jika ada validasi error ─────────────
window.addEventListener('DOMContentLoaded', () => {
    if (OLD_SELECTED.length > 0) {
        OLD_SELECTED.forEach(id => addRoomRow(id));
    } else {
        updateUI();
    }
});
</script>
@endsection