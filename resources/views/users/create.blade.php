{{-- resources/views/users/create.blade.php --}}
@extends('layouts.admin')

@section('main-content')

<div style="padding-top:5.5rem;">
<div class="container-fluid" style="max-width:1060px;">

    {{-- ══ Page Header ══════════════════════════════════════════ --}}
    <div class="d-flex align-items-center mb-4" style="gap:14px;">
        <a href="{{ route('users.index') }}" class="uf-back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 style="font-size:1.3rem;font-weight:700;color:#1c1c1e;margin:0 0 2px;">
                <i class="fas fa-user-plus" style="color:var(--ios-blue);margin-right:8px;"></i>
                Add New User
            </h4>
            <p style="color:var(--ios-gray);font-size:.82rem;margin:0;">Create a new staff account</p>
        </div>
    </div>

    {{-- ══ Validation errors ════════════════════════════════════ --}}
    @if($errors->any())
        <div class="uf-alert-error mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="uf-grid">

            {{-- ══ LEFT COLUMN — Avatar + Status ═══════════════ --}}
            <div class="uf-col-left">

                {{-- Photo card --}}
                <div class="card mb-3">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-camera"></i> Profile Photo</p>

                        <div class="uf-avatar-wrap" id="avatarPreviewWrap">
                            <div class="uf-avatar-placeholder" id="avatarPlaceholder">
                                <i class="fas fa-user" style="font-size:2rem;opacity:.25;"></i>
                                <span style="font-size:.72rem;color:var(--ios-gray);margin-top:4px;">Upload photo</span>
                            </div>
                            <img src="" id="avatarPreview" class="uf-avatar-preview" style="display:none;" alt="Preview">
                            <label for="avatarInput" class="uf-avatar-overlay">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                        </div>
                        <p style="text-align:center;font-size:.72rem;color:var(--ios-gray);margin:8px 0 0;">
                            JPG, PNG — max 2MB
                        </p>
                    </div>
                </div>

                {{-- Status card --}}
                <div class="card">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-circle-dot"></i> Account Status</p>
                        <div class="uf-radio-stack">
                            @foreach(['active'=>['Active','var(--ios-green)','#34C759'],'inactive'=>['Inactive','var(--ios-gray)','#8E8E93'],'suspended'=>['Suspended','var(--ios-red)','#FF3B30']] as $val=>[$lbl,$clr,$hex])
                                <label class="uf-radio-row" data-color="{{ $hex }}">
                                    <input type="radio" name="status" value="{{ $val }}"
                                           {{ old('status','active') === $val ? 'checked' : '' }}>
                                    <span class="uf-radio-dot" style="border-color:{{ $hex }};"></span>
                                    <span class="uf-radio-label">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>{{-- /col-left --}}

            {{-- ══ RIGHT COLUMN — All fields ═══════════════════ --}}
            <div class="uf-col-right">

                {{-- Identity card --}}
                <div class="card mb-3">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-id-card"></i> Identity</p>
                        <div class="uf-row-2">
                            <div class="uf-field">
                                <label class="uf-label">First Name <span class="uf-req">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       class="uf-input {{ $errors->has('name') ? 'uf-input-error' : '' }}"
                                       placeholder="Ahmad" required>
                            </div>
                            <div class="uf-field">
                                <label class="uf-label">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                       class="uf-input {{ $errors->has('middle_name') ? 'uf-input-error' : '' }}"
                                       placeholder="bin">
                            </div>
                        </div>
                        <div class="uf-row-2">
                            <div class="uf-field">
                                <label class="uf-label">Last Name <span class="uf-req">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                       class="uf-input {{ $errors->has('last_name') ? 'uf-input-error' : '' }}"
                                       placeholder="Abdullah" required>
                            </div>
                            <div class="uf-field" style="margin-bottom:0;">
                                <label class="uf-label">
                                    <i class="fas fa-id-badge" style="color:var(--ios-blue);margin-right:4px;"></i>
                                    Employee Identity Number (EIN)
                                </label>
                            <input type="text" name="ein" value="{{ old('ein') }}"
                                   class="uf-input uf-mono {{ $errors->has('ein') ? 'uf-input-error' : '' }}"
                                   placeholder="HTPN-2026-001">
                            <span class="uf-hint">Unique hospital staff ID. Leave blank to assign later.</span>
                        </div>
                    </div>
                </div>

                {{-- Contact card --}}
                <div class="card mb-3">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-address-book"></i> Contact</p>
                        <div class="uf-field">
                            <label class="uf-label">Email Address <span class="uf-req">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="uf-input {{ $errors->has('email') ? 'uf-input-error' : '' }}"
                                   placeholder="ahmad@htpn.gov.my" required>
                        </div>
                        <div class="uf-row-2" style="margin-bottom:0;">
                            <div class="uf-field" style="margin-bottom:0;">
                                <label class="uf-label">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="uf-input" placeholder="+60 12-345 6789">
                            </div>
                            <div class="uf-field" style="margin-bottom:0;">
                                <label class="uf-label">Extension</label>
                                <input type="text" name="phone_extension" value="{{ old('phone_extension') }}"
                                       class="uf-input" placeholder="1234">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password card --}}
                <div class="card mb-3">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-lock"></i> Password</p>
                        <div class="uf-row-2" style="margin-bottom:0;">
                            <div class="uf-field" style="margin-bottom:0;">
                                <label class="uf-label">Password <span class="uf-req">*</span></label>
                                <div class="uf-pw-wrap">
                                    <input type="password" name="password" id="pw1"
                                           class="uf-input {{ $errors->has('password') ? 'uf-input-error' : '' }}"
                                           placeholder="Min. 8 characters" required>
                                    <button type="button" class="uf-pw-eye" onclick="togglePw('pw1',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="uf-field" style="margin-bottom:0;">
                                <label class="uf-label">Confirm Password <span class="uf-req">*</span></label>
                                <div class="uf-pw-wrap">
                                    <input type="password" name="password_confirmation" id="pw2"
                                           class="uf-input" placeholder="Repeat password" required>
                                    <button type="button" class="uf-pw-eye" onclick="togglePw('pw2',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Role & Department card --}}
                <div class="card mb-3">
                    <div class="card-body" style="padding:1.25rem;">
                        <p class="uf-section-label"><i class="fas fa-user-shield"></i> Role & Department</p>

                        <div class="uf-field">
                            <label class="uf-label">Role <span class="uf-req">*</span></label>
                            <select name="role_id"
                                    class="uf-input uf-select {{ $errors->has('role_id') ? 'uf-input-error' : '' }}"
                                    required>
                                <option value="">— Select role —</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                            {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }} · Level {{ $role->level }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="uf-hint">Only roles below your own level are listed.</span>
                        </div>

                        <div class="uf-field" style="margin-bottom:0;">
                            <label class="uf-label">
                                Departments
                                <span class="uf-hint-inline">Tap a selected dept to mark as primary ★</span>
                            </label>
                            <div class="uf-dept-list">
                                @php
                                    $deptGroups = $departments->groupBy('type');
                                    $typeLabels = ['management'=>'⚙ Management','clinical'=>'🏥 Clinical','support'=>'🛠 Support'];
                                    $typeColors = ['management'=>'#007AFF','clinical'=>'#34C759','support'=>'#FF9500'];
                                @endphp
                                @foreach(['management','clinical','support'] as $type)
                                    @if($deptGroups->has($type))
                                        <div class="uf-dept-group" style="color:{{ $typeColors[$type] }}">
                                            {{ $typeLabels[$type] }}
                                        </div>
                                        @foreach($deptGroups[$type] as $dept)
                                            <label class="uf-dept-item" id="deptLabel{{ $dept->id }}">
                                                <input type="checkbox"
                                                       name="department_ids[]"
                                                       value="{{ $dept->id }}"
                                                       class="dept-checkbox"
                                                       id="dept{{ $dept->id }}"
                                                       {{ in_array($dept->id, old('department_ids',[])) ? 'checked' : '' }}>
                                                <span class="uf-dept-check"><i class="fas fa-check"></i></span>
                                                <span class="uf-dept-name">{{ $dept->name }}</span>
                                                <span class="uf-primary-star" id="primary{{ $dept->id }}">★</span>
                                            </label>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                            <input type="hidden" name="primary_dept" id="primaryDeptInput" value="{{ old('primary_dept') }}">
                            <span class="uf-hint">First selected is primary by default.</span>
                        </div>
                    </div>
                </div>

                {{-- Submit row --}}
                <div class="d-flex justify-content-end" style="gap:10px;">
                    <a href="{{ route('users.index') }}" class="uf-btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="uf-btn-submit">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                </div>

            </div>{{-- /col-right --}}
        </div>
    </form>

</div>
</div>

{{-- ══ SHARED STYLES ════════════════════════════════════════════ --}}
<style>
/* Layout */
.uf-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 16px;
    align-items: start;
}
@media(max-width:720px){ .uf-grid { grid-template-columns:1fr; } }
.uf-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:480px){ .uf-row-2 { grid-template-columns:1fr; } }

/* Back button */
.uf-back-btn {
    width:38px; height:38px; border-radius:12px; flex-shrink:0;
    background:rgba(0,0,0,.06); border:1px solid rgba(0,0,0,.08);
    display:flex; align-items:center; justify-content:center;
    color:#555; text-decoration:none; transition:all .2s var(--ease-ios);
}
.uf-back-btn:hover { background:rgba(0,0,0,.1); color:#1c1c1e; text-decoration:none; }

/* Section labels */
.uf-section-label {
    font-size:.75rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:var(--ios-gray);
    margin:0 0 14px; display:flex; align-items:center; gap:6px;
}
.uf-section-label i { color:var(--ios-blue); }

/* Error alert */
.uf-alert-error {
    display:flex; gap:12px; align-items:flex-start;
    padding:14px 18px; border-radius:var(--radius-md);
    background:rgba(255,59,48,.08); border:1px solid rgba(255,59,48,.2);
    color:#c0392b; font-size:.85rem;
}
.uf-alert-error i { flex-shrink:0; margin-top:2px; }

/* Fields */
.uf-field { margin-bottom:14px; }
.uf-label {
    display:block; font-size:.75rem; font-weight:600;
    color:#1c1c1e; margin-bottom:6px;
    text-transform:uppercase; letter-spacing:.4px;
}
.uf-req { color:var(--ios-red); }
.uf-hint { display:block; font-size:.72rem; color:var(--ios-gray); margin-top:5px; }
.uf-hint-inline { font-size:.71rem; color:var(--ios-gray); margin-left:6px; font-weight:400; text-transform:none; letter-spacing:0; }

/* Inputs */
.uf-input {
    width:100%;
    background:rgba(120,120,128,.08);
    border:1px solid rgba(0,0,0,.09);
    border-radius:var(--radius-md);
    padding:10px 14px;
    font-size:.875rem; color:#1c1c1e; outline:none;
    transition:all .2s; appearance:none; -webkit-appearance:none;
}
.uf-input:focus {
    background:#fff; border-color:var(--ios-blue);
    box-shadow:0 0 0 3px rgba(0,122,255,.12);
}
.uf-input::placeholder { color:var(--ios-gray2); }
.uf-input-error { border-color:var(--ios-red) !important; background:rgba(255,59,48,.04) !important; }
.uf-select { cursor:pointer; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238E8E93' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; padding-right:36px; }
.uf-mono { font-family:'SF Mono','Courier New',monospace; letter-spacing:.4px; }

/* Password eye */
.uf-pw-wrap { position:relative; }
.uf-pw-wrap .uf-input { padding-right:42px; }
.uf-pw-eye {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:var(--ios-gray); cursor:pointer;
    font-size:.85rem; padding:2px; transition:color .15s;
}
.uf-pw-eye:hover { color:var(--ios-blue); }

/* Avatar upload */
.uf-avatar-wrap {
    position:relative; width:110px; height:110px;
    border-radius:50%; margin:0 auto 10px;
    border:2px dashed rgba(0,0,0,.15); overflow:hidden; cursor:pointer;
    transition:border-color .2s;
}
.uf-avatar-wrap:hover { border-color:var(--ios-blue); }
.uf-avatar-placeholder {
    height:100%; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
}
.uf-avatar-preview { width:100%; height:100%; object-fit:cover; display:block; }
.uf-avatar-overlay {
    position:absolute; inset:0; border-radius:50%;
    background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:1.2rem; opacity:0; cursor:pointer; margin:0;
    transition:opacity .2s;
}
.uf-avatar-wrap:hover .uf-avatar-overlay { opacity:1; }

/* Remove avatar checkbox */
.uf-remove-check {
    display:flex; align-items:center; gap:7px; justify-content:center;
    font-size:.75rem; color:var(--ios-red); cursor:pointer; margin:6px 0 0;
}
.uf-remove-check input { cursor:pointer; accent-color:var(--ios-red); }

/* Status radios */
.uf-radio-stack { display:flex; flex-direction:column; gap:8px; }
.uf-radio-row {
    display:flex; align-items:center; gap:10px;
    padding:9px 13px; border-radius:var(--radius-md);
    border:1px solid rgba(0,0,0,.08);
    background:rgba(120,120,128,.06);
    cursor:pointer; transition:all .15s; user-select:none;
}
.uf-radio-row input[type=radio] { display:none; }
.uf-radio-dot {
    width:16px; height:16px; border-radius:50%; flex-shrink:0;
    border:2px solid #ccc; position:relative; transition:all .15s;
}
.uf-radio-dot::after {
    content:''; position:absolute; inset:3px; border-radius:50%;
    background:transparent; transition:all .15s;
}
.uf-radio-row:has(input:checked) {
    background:rgba(0,122,255,.06); border-color:rgba(0,122,255,.2);
}
.uf-radio-row:has(input[value=active]:checked)    { background:rgba(52,199,89,.07); border-color:rgba(52,199,89,.3); }
.uf-radio-row:has(input[value=suspended]:checked) { background:rgba(255,59,48,.07); border-color:rgba(255,59,48,.3); }
.uf-radio-row:has(input:checked) .uf-radio-dot { border-color:currentColor; }
.uf-radio-row:has(input:checked) .uf-radio-dot::after { background:currentColor; }
.uf-radio-row:has(input[value=active]:checked)    { color:#1a7a35; }
.uf-radio-row:has(input[value=inactive]:checked)  { color:#555; }
.uf-radio-row:has(input[value=suspended]:checked) { color:#c0392b; }
.uf-radio-label { font-size:.85rem; font-weight:500; color:#1c1c1e; }
.uf-radio-row:has(input:checked) .uf-radio-label { color:inherit; font-weight:600; }

/* Self-edit status note */
.uf-status-note {
    font-size:.75rem; color:#b45309;
    background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25);
    border-radius:var(--radius-sm); padding:7px 10px; margin-bottom:10px;
    display:flex; align-items:center; gap:6px;
}

/* Stat mini (edit only) */
.uf-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.uf-stat-box {
    background:rgba(120,120,128,.07); border-radius:var(--radius-sm);
    padding:10px 12px; text-align:center;
}
.uf-stat-box small { display:block; font-size:.65rem; color:var(--ios-gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; }
.uf-stat-box span  { font-size:.82rem; font-weight:600; color:#1c1c1e; }

/* Department list */
.uf-dept-list {
    border:1px solid rgba(0,0,0,.09); border-radius:var(--radius-md);
    max-height:260px; overflow-y:auto;
    scrollbar-width:thin; scrollbar-color:rgba(0,0,0,.1) transparent;
}
.uf-dept-group {
    font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
    padding:9px 14px 4px; background:rgba(0,0,0,.02);
    border-bottom:1px solid rgba(0,0,0,.05); position:sticky; top:0;
}
.uf-dept-item {
    display:flex; align-items:center; gap:9px;
    padding:8px 14px; cursor:pointer; margin:0;
    font-size:.83rem; color:#555; transition:background .12s;
    border-bottom:1px solid rgba(0,0,0,.04);
}
.uf-dept-item:last-child { border-bottom:none; }
.uf-dept-item:hover { background:rgba(0,122,255,.04); }
.uf-dept-item:has(input:checked) { background:rgba(0,122,255,.07); color:#1c1c1e; }
.uf-dept-item input[type=checkbox] { display:none; }
.uf-dept-check {
    width:18px; height:18px; border-radius:6px; flex-shrink:0;
    border:1.5px solid rgba(0,0,0,.2); display:flex; align-items:center;
    justify-content:center; font-size:.6rem; color:transparent;
    transition:all .15s; background:#fff;
}
.uf-dept-item:has(input:checked) .uf-dept-check {
    background:var(--ios-blue); border-color:var(--ios-blue); color:#fff;
}
.uf-dept-name { flex:1; line-height:1.3; }
.uf-primary-star {
    font-size:.85rem; color:transparent; flex-shrink:0;
    transition:color .15s; line-height:1;
}
.uf-primary-star.visible { color:#FF9500; }

/* Submit buttons */
.uf-btn-cancel {
    display:inline-flex; align-items:center; gap:7px;
    padding:10px 22px; border-radius:var(--radius-pill);
    background:rgba(0,0,0,.06); border:1px solid rgba(0,0,0,.08);
    color:#555; font-size:.875rem; font-weight:500;
    text-decoration:none; cursor:pointer; transition:all .2s;
}
.uf-btn-cancel:hover { background:rgba(0,0,0,.1); color:#1c1c1e; text-decoration:none; }
.uf-btn-submit {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 28px; border-radius:var(--radius-pill);
    background:var(--ios-blue); border:none; color:#fff;
    font-size:.875rem; font-weight:600; cursor:pointer;
    transition:all .2s var(--ease-ios);
}
.uf-btn-submit:hover {
    background:#0062cc; transform:translateY(-1px);
    box-shadow:0 6px 20px rgba(0,122,255,.35);
}
.uf-btn-submit-green {
    background:var(--ios-green);
}
.uf-btn-submit-green:hover {
    background:#28a745;
    box-shadow:0 6px 20px rgba(52,199,89,.35);
}
</style>

{{-- ══ SCRIPTS ═══════════════════════════════════════════════════ --}}
<script>
/* Avatar preview */
document.getElementById('avatarInput').addEventListener('change', function() {
    if (!this.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('avatarPlaceholder').style.display = 'none';
        var p = document.getElementById('avatarPreview');
        p.src = e.target.result;
        p.style.display = 'block';
    };
    reader.readAsDataURL(this.files[0]);
});

/* Password toggle */
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    var ico = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

/* Department primary logic */
var primaryDeptId = document.getElementById('primaryDeptInput').value || null;

function syncPrimary() {
    var checked = Array.from(document.querySelectorAll('.dept-checkbox:checked')).map(function(c){return c.value;});

    if (primaryDeptId && checked.indexOf(String(primaryDeptId)) === -1) primaryDeptId = null;
    if (!primaryDeptId && checked.length) primaryDeptId = checked[0];

    document.querySelectorAll('.uf-primary-star').forEach(function(s){ s.classList.remove('visible'); });
    if (primaryDeptId) {
        var star = document.getElementById('primary' + primaryDeptId);
        var cb   = document.getElementById('dept'    + primaryDeptId);
        if (star && cb && cb.checked) star.classList.add('visible');
    }
    document.getElementById('primaryDeptInput').value = primaryDeptId || '';
}

/* Click on label of already-checked dept → promote to primary */
document.querySelectorAll('.uf-dept-item').forEach(function(lbl) {
    lbl.addEventListener('click', function(e) {
        var cb = this.querySelector('.dept-checkbox');
        if (cb && cb.checked && String(cb.value) !== String(primaryDeptId)) {
            e.preventDefault();
            primaryDeptId = cb.value;
            syncPrimary();
        }
    });
});

document.querySelectorAll('.dept-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() { syncPrimary(); });
});

syncPrimary();
</script>

@endsection