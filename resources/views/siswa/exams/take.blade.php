<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} · Ujian</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/sitara.css') }}" rel="stylesheet">
    <style>body{background:#eef2f7}.exam-topbar{background:#fff;border-bottom:1px solid #e5e9f0;position:sticky;top:0;z-index:50}</style>
</head>
<body>
<div class="exam-topbar py-2">
    <div class="container-fluid d-flex justify-content-between align-items-center px-4">
        <div><div class="fw-bold">{{ $exam->title }}</div><small class="text-muted">{{ auth()->user()->name }} · {{ $exam->subject->name ?? '' }}</small></div>
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-soft-danger fs-6 px-3 py-2"><i class="bi bi-clock me-1"></i><span class="cbt-timer" id="timer">--:--</span></div>
            <button class="btn btn-light" onclick="toggleFullscreen()" title="Layar Penuh"><i class="bi bi-arrows-fullscreen"></i></button>
            <button class="btn btn-primary" onclick="confirmSubmit()"><i class="bi bi-send me-1"></i>Selesai</button>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        <div class="col-lg-8 col-xl-9">
            <div class="card"><div class="card-body p-4">
                @foreach($ordered as $i => $q)
                    @php $saved = $answers[$q->id] ?? null; $sval = $saved?->answer[0] ?? null; @endphp
                    <div class="question-pane" data-index="{{ $i }}" style="display:{{ $i===0?'block':'none' }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-soft-primary">Soal {{ $i+1 }} dari {{ count($ordered) }}</span>
                            <button class="btn btn-sm btn-outline-warning flag-btn" data-qid="{{ $q->id }}" onclick="toggleFlag({{ $q->id }}, this)">
                                <i class="bi bi-flag{{ $saved?->is_flagged ? '-fill' : '' }}"></i> Ragu-ragu
                            </button>
                        </div>

                        <div class="fs-5 mb-3">{!! nl2br(e($q->question_text)) !!}</div>
                        @if($q->image)<img src="{{ asset('storage/'.$q->image) }}" class="img-fluid rounded mb-3" style="max-height:280px">@endif
                        @if($q->audio)<audio controls class="mb-3 w-100"><source src="{{ asset('storage/'.$q->audio) }}"></audio>@endif

                        @if(in_array($q->type,['multiple_choice','true_false']))
                            <div class="d-grid gap-2">
                                @foreach($q->options as $opt)
                                    <label class="option-card p-3 d-flex align-items-center gap-2 {{ $sval===$opt['key']?'selected':'' }}">
                                        <input type="radio" class="form-check-input mt-0" name="q{{ $q->id }}" value="{{ $opt['key'] }}" {{ $sval===$opt['key']?'checked':'' }}
                                            onchange="saveAnswer({{ $q->id }}, this.value, this)">
                                        <span><strong>{{ strtoupper($opt['key']==='true'?'B':($opt['key']==='false'?'S':$opt['key'])) }}.</strong> {{ $opt['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($q->type==='short_answer')
                            <input type="text" class="form-control form-control-lg" value="{{ $sval }}" placeholder="Ketik jawaban..."
                                   onchange="saveAnswer({{ $q->id }}, this.value, this)">
                        @elseif($q->type==='matching')
                            @php $rights = collect($q->options)->pluck('right')->shuffle(); $savedMatch = $saved?->answer ?? []; @endphp
                            @foreach($q->options as $mi => $pair)
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-5">{{ $pair['left'] }}</div>
                                    <div class="col-1 text-center"><i class="bi bi-arrow-right"></i></div>
                                    <div class="col-6">
                                        <select class="form-select match-select" data-qid="{{ $q->id }}" data-idx="{{ $mi }}" onchange="saveMatch({{ $q->id }})">
                                            <option value="">— pilih —</option>
                                            @foreach($rights as $r)<option value="{{ $r }}" {{ ($savedMatch[$mi] ?? null)===$r?'selected':'' }}>{{ $r }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($q->type==='essay')
                            <textarea class="form-control" rows="6" placeholder="Tulis jawaban essay Anda..."
                                      onchange="saveAnswer({{ $q->id }}, this.value, this)">{{ $sval }}</textarea>
                            <small class="text-muted">Jawaban essay akan dikoreksi oleh guru.</small>
                        @elseif($q->type==='file_upload')
                            <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Soal ini memerlukan unggah file. Silakan ikuti instruksi pengawas.</div>
                        @endif

                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-light" onclick="goTo({{ $i-1 }})" {{ $i===0?'disabled':'' }}><i class="bi bi-arrow-left me-1"></i>Sebelumnya</button>
                            @if($i < count($ordered)-1)
                                <button class="btn btn-primary" onclick="goTo({{ $i+1 }})">Selanjutnya<i class="bi bi-arrow-right ms-1"></i></button>
                            @else
                                <button class="btn btn-success" onclick="confirmSubmit()"><i class="bi bi-send me-1"></i>Selesai & Kumpulkan</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div></div>
        </div>

        <div class="col-lg-4 col-xl-3">
            <div class="card"><div class="card-body">
                <h6 class="fw-bold mb-3">Navigasi Soal</h6>
                <div class="cbt-question-nav d-flex flex-wrap gap-2 mb-3">
                    @foreach($ordered as $i => $q)
                        @php $saved = $answers[$q->id] ?? null; @endphp
                        <button id="nav-{{ $i }}" class="{{ $i===0?'current':'' }} {{ $saved && $saved->answer ? 'answered':'' }} {{ $saved && $saved->is_flagged ? 'flagged':'' }}" onclick="goTo({{ $i }})">{{ $i+1 }}</button>
                    @endforeach
                </div>
                <div class="small text-muted d-flex flex-column gap-1">
                    <span><span class="d-inline-block rounded me-1" style="width:14px;height:14px;background:#12b76a"></span> Sudah dijawab</span>
                    <span><span class="d-inline-block rounded me-1" style="width:14px;height:14px;background:#f59e0b"></span> Ragu-ragu</span>
                    <span><span class="d-inline-block rounded me-1 border" style="width:14px;height:14px;background:#fff"></span> Belum dijawab</span>
                </div>
                <hr>
                <button class="btn btn-primary w-100" onclick="confirmSubmit()"><i class="bi bi-send me-1"></i>Kumpulkan Ujian</button>
            </div></div>
        </div>
    </div>
</div>

<form id="submitForm" method="POST" action="{{ route('siswa.exams.submit',$schedule) }}" class="d-none">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const EXAM_MASCOT = "{{ asset('assets/maskot2.png') }}";
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const SAVE_URL = "{{ route('siswa.exams.answer', $schedule) }}";
const TOTAL = {{ count($ordered) }};
let current = 0;
let remaining = {{ $remaining }};

// ---- Timer ----
const timerEl = document.getElementById('timer');
function tick() {
    if (remaining <= 0) { autoSubmit(); return; }
    remaining--;
    const h = String(Math.floor(remaining/3600)).padStart(2,'0');
    const m = String(Math.floor((remaining%3600)/60)).padStart(2,'0');
    const s = String(remaining%60).padStart(2,'0');
    timerEl.textContent = (h!=='00'? h+':' : '') + m + ':' + s;
    if (remaining === 60) Swal.fire({
        toast: true, position: 'top-end', icon: 'warning',
        title: 'Waktu tersisa 1 menit!', showConfirmButton: false, timer: 5000, timerProgressBar: true
    });
}
tick(); setInterval(tick, 1000);

// ---- Navigation ----
function goTo(i) {
    if (i < 0 || i >= TOTAL) return;
    document.querySelectorAll('.question-pane').forEach(p => p.style.display = 'none');
    document.querySelector(`.question-pane[data-index="${i}"]`).style.display = 'block';
    document.querySelectorAll('.cbt-question-nav button').forEach(b => b.classList.remove('current'));
    document.getElementById('nav-'+i).classList.add('current');
    current = i;
    window.scrollTo(0,0);
}

// ---- Save answer ----
function post(payload) {
    payload.remaining_seconds = remaining;
    return fetch(SAVE_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json());
}
function markAnswered(qid, hasVal) {
    const idx = [...document.querySelectorAll('.question-pane')].find(p => p.querySelector(`[name="q${qid}"],[data-qid="${qid}"]`))?.dataset.index;
    if (idx !== undefined) document.getElementById('nav-'+idx).classList.toggle('answered', hasVal);
}
function saveAnswer(qid, value, el) {
    if (el && el.closest('.option-card')) {
        el.closest('.question-pane').querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
        el.closest('.option-card').classList.add('selected');
    }
    post({question_id: qid, answer: value}).then(() => markAnswered(qid, value !== ''));
}
function saveMatch(qid) {
    const selects = document.querySelectorAll(`.match-select[data-qid="${qid}"]`);
    const ans = [...selects].map(s => s.value);
    post({question_id: qid, answer: ans}).then(() => markAnswered(qid, ans.some(a => a !== '')));
}
function toggleFlag(qid, btn) {
    const flagged = !btn.classList.contains('active');
    btn.classList.toggle('active', flagged);
    btn.querySelector('i').className = 'bi bi-flag' + (flagged ? '-fill' : '');
    const idx = btn.closest('.question-pane').dataset.index;
    document.getElementById('nav-'+idx).classList.toggle('flagged', flagged);
    post({question_id: qid, is_flagged: flagged});
}

// ---- Submit ----
function doSubmit() {
    window.removeEventListener('beforeunload', warnLeave);
    document.getElementById('submitForm').submit();
}
function confirmSubmit() {
    Swal.fire({
        title: 'Kumpulkan ujian?',
        html: '<p class="sitara-swal-text">Jawaban tidak dapat diubah lagi setelah dikumpulkan.</p>',
        imageUrl: EXAM_MASCOT, imageWidth: 120, imageAlt: 'SITARA',
        showCancelButton: true, reverseButtons: true, buttonsStyling: false, focusCancel: true,
        confirmButtonText: 'Ya, kumpulkan', cancelButtonText: 'Periksa lagi',
        customClass: {
            popup: 'sitara-swal', title: 'sitara-swal-title', image: 'sitara-swal-img',
            actions: 'sitara-swal-actions',
            confirmButton: 'sitara-swal-btn sitara-swal-btn-primary',
            cancelButton: 'sitara-swal-btn sitara-swal-btn-cancel'
        }
    }).then((r) => { if (r.isConfirmed) doSubmit(); });
}
function autoSubmit() {
    Swal.fire({
        title: 'Waktu habis!',
        html: '<p class="sitara-swal-text">Ujian dikumpulkan secara otomatis.</p>',
        imageUrl: EXAM_MASCOT, imageWidth: 120, imageAlt: 'SITARA',
        allowOutsideClick: false, allowEscapeKey: false, buttonsStyling: false,
        confirmButtonText: 'Mengerti',
        customClass: {
            popup: 'sitara-swal', title: 'sitara-swal-title', image: 'sitara-swal-img',
            actions: 'sitara-swal-actions',
            confirmButton: 'sitara-swal-btn sitara-swal-btn-primary'
        }
    }).then(doSubmit);
    setTimeout(doSubmit, 4000);
}

// ---- Anti-cheat: warn on leave, block back button ----
function warnLeave(e) { e.preventDefault(); e.returnValue = ''; }
window.addEventListener('beforeunload', warnLeave);
history.pushState(null, '', location.href);
window.addEventListener('popstate', () => { history.pushState(null, '', location.href); });

function toggleFullscreen() {
    if (!document.fullscreenElement) document.documentElement.requestFullscreen();
    else document.exitFullscreen();
}
</script>
</body>
</html>
