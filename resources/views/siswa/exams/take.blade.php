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
    <style>
        body{background:#eef2f7}
        html,body{overflow-x:hidden;max-width:100%}
        .exam-topbar{background:#fff;border-bottom:1px solid #e5e9f0;position:sticky;top:0;z-index:50}
        .exam-title{min-width:0;flex:1}
        .exam-title .fw-bold{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .exam-actions{flex-shrink:0}
        /* tabular numerals keep the countdown from jumping width digit-to-digit */
        .cbt-timer{font-variant-numeric:tabular-nums;white-space:nowrap}

        @media (max-width: 575.98px){
            .exam-topbar .container-fluid{padding-left:.85rem!important;padding-right:.85rem!important;gap:.5rem}
            .exam-title .fw-bold{font-size:.92rem}
            .exam-title small{font-size:.7rem}
            .exam-actions{gap:.4rem!important}
            .exam-actions .badge{font-size:.9rem!important;padding:.4rem .55rem!important}
            .exam-actions .btn{padding:.4rem .55rem}
            .exam-actions .btn-primary .btn-label{display:none} /* hide "Selesai" label, keep icon */
            .exam-actions .btn-primary .bi{margin:0!important}
            .container-fluid.px-4{padding-left:.85rem!important;padding-right:.85rem!important}
            .card-body.p-4{padding:1rem!important}
            .question-pane .fs-5{font-size:1.05rem!important}
            .option-card{padding:.75rem!important}
        }

        /* ---------- Enjoy: animations & polish ---------- */
        /* soft slide-in each time a question is shown */
        @keyframes sitara-q-in{ from{opacity:0; transform:translateY(16px) scale(.99);} to{opacity:1; transform:translateY(0) scale(1);} }
        .question-pane.q-anim{ animation: sitara-q-in .4s cubic-bezier(.22,.61,.36,1) both; }

        /* option cards: springy hover + pop when picked */
        .option-card{ transition: border-color .18s, background .18s, box-shadow .18s, transform .12s ease; }
        .option-card:active{ transform: scale(.99); }
        @keyframes sitara-opt-pop{ 0%{transform:scale(1);} 45%{transform:scale(1.02);} 100%{transform:scale(1);} }
        .option-card.just-picked{ animation: sitara-opt-pop .34s ease; }

        /* nav buttons bounce when a question becomes answered */
        @keyframes sitara-nav-pop{ 0%{transform:scale(1);} 50%{transform:scale(1.22);} 100%{transform:scale(1);} }
        .cbt-question-nav button.just-answered{ animation: sitara-nav-pop .35s ease; }
        .cbt-question-nav button{ transition: transform .15s, background .2s, border-color .2s, color .2s; }

        /* progress bar */
        .exam-progress{ height:9px; border-radius:999px; background:#e5e9f0; overflow:hidden; }
        .exam-progress > span{ display:block; height:100%; width:0; border-radius:999px;
            background:linear-gradient(90deg,#2563EB,#14b8a6); background-size:200% 100%;
            animation: sitara-gradient-shift 3s ease infinite; transition:width .55s cubic-bezier(.22,.61,.36,1); }

        /* timer turns urgent & blinks in the final minute */
        .exam-actions .badge.time-low{ animation: sitara-time-blink 1s steps(1,end) infinite; }
        @keyframes sitara-time-blink{ 50%{ filter:brightness(1.2); box-shadow:0 0 0 5px rgba(239,68,68,.18);} }

        /* encouraging mascot in the sidebar */
        .exam-cheer{ background:linear-gradient(135deg,#eef5ff,#e8fbf4); border:1px solid #e4eefb; border-radius:16px; }
        .exam-cheer img{ width:52px; height:auto; animation: sitara-float 4s ease-in-out infinite; }

        /* red flash overlay when a student leaves the exam */
        .cheat-flash{ position:fixed; inset:0; background:rgba(239,68,68,.35); z-index:3000; pointer-events:none; opacity:0; }
        .cheat-flash.show{ animation: sitara-cheat-flash .6s ease; }
        @keyframes sitara-cheat-flash{ 0%,100%{opacity:0;} 30%{opacity:1;} }

        @media (prefers-reduced-motion: reduce){
            .question-pane.q-anim,.option-card.just-picked,.cbt-question-nav button.just-answered,
            .exam-progress > span,.exam-cheer img,.cheat-flash.show{ animation:none!important; }
        }
    </style>
</head>
<body>
<div class="exam-topbar py-2">
    <div class="container-fluid d-flex justify-content-between align-items-center gap-3 px-4">
        <div class="exam-title"><div class="fw-bold">{{ $exam->title }}</div><small class="text-muted">{{ auth()->user()->name }} · {{ $exam->subject->name ?? '' }}</small></div>
        <div class="exam-actions d-flex align-items-center gap-3">
            <div class="badge bg-soft-danger fs-6 px-3 py-2"><i class="bi bi-clock me-1"></i><span class="cbt-timer" id="timer">--:--</span></div>
            <button class="btn btn-light" onclick="toggleFullscreen()" title="Layar Penuh"><i class="bi bi-arrows-fullscreen"></i></button>
            <button class="btn btn-primary" onclick="confirmSubmit()"><i class="bi bi-send me-1"></i><span class="btn-label">Selesai</span></button>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        <div class="col-lg-8 col-xl-9">
            <div class="card"><div class="card-body p-4">
                {{-- Progress: how many answered --}}
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="fw-semibold text-muted"><i class="bi bi-check2-circle me-1 text-success"></i><span id="progressLabel">0 dari {{ count($ordered) }} terjawab</span></span>
                    <span class="fw-bold" style="color:#14b8a6" id="progressPct">0%</span>
                </div>
                <div class="exam-progress mb-4"><span id="progressBar"></span></div>

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
                        @if($q->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($q->image) }}" class="img-fluid rounded mb-3" style="max-height:280px">@endif
                        @if($q->audio)<audio controls class="mb-3 w-100"><source src="{{ \Illuminate\Support\Facades\Storage::url($q->audio) }}"></audio>@endif

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
                <div class="exam-cheer d-flex align-items-center gap-2 p-2 mt-3">
                    <img src="{{ asset('assets/maskot2.png') }}" alt="Maskot SITARA">
                    <small class="text-muted">Tetap fokus & <b>jujur</b> ya! Kamu pasti bisa 💪</small>
                </div>
                <hr>
                <button class="btn btn-primary w-100" onclick="confirmSubmit()"><i class="bi bi-send me-1"></i>Kumpulkan Ujian</button>
            </div></div>
        </div>
    </div>
</div>

<div class="cheat-flash" id="cheatFlash"></div>

<form id="submitForm" method="POST" action="{{ route('siswa.exams.submit',$schedule) }}" class="d-none">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const EXAM_MASCOT = "{{ asset('assets/maskot2.png') }}";
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const SAVE_URL = "{{ route('siswa.exams.answer', $schedule) }}";
const VIOLATION_URL = "{{ route('siswa.exams.violation', $schedule) }}";
const TOTAL = {{ count($ordered) }};
const STUDENT = @json(auth()->user()->name);
let current = 0;
let remaining = Math.floor({{ $remaining }}); // floor: avoid fractional seconds in the timer

// ---- Timer ----
const timerEl = document.getElementById('timer');
function tick() {
    if (remaining <= 0) { autoSubmit(); return; }
    remaining--;
    const h = String(Math.floor(remaining/3600)).padStart(2,'0');
    const m = String(Math.floor((remaining%3600)/60)).padStart(2,'0');
    const s = String(remaining%60).padStart(2,'0');
    timerEl.textContent = (h!=='00'? h+':' : '') + m + ':' + s;
    // urgent styling in the final minute
    if (remaining <= 60) timerEl.closest('.badge')?.classList.add('time-low');
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
    const pane = document.querySelector(`.question-pane[data-index="${i}"]`);
    pane.style.display = 'block';
    // re-trigger the slide-in animation
    pane.classList.remove('q-anim'); void pane.offsetWidth; pane.classList.add('q-anim');
    document.querySelectorAll('.cbt-question-nav button').forEach(b => b.classList.remove('current'));
    document.getElementById('nav-'+i).classList.add('current');
    current = i;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ---- Progress bar ----
function updateProgress() {
    const answered = document.querySelectorAll('.cbt-question-nav button.answered').length;
    const pct = TOTAL ? Math.round(answered / TOTAL * 100) : 0;
    document.getElementById('progressBar').style.width = pct + '%';
    document.getElementById('progressLabel').textContent = answered + ' dari ' + TOTAL + ' terjawab';
    document.getElementById('progressPct').textContent = pct + '%';
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
    if (idx !== undefined) {
        const btn = document.getElementById('nav-'+idx);
        btn.classList.toggle('answered', hasVal);
        if (hasVal) { btn.classList.remove('just-answered'); void btn.offsetWidth; btn.classList.add('just-answered'); }
        updateProgress();
    }
}
function saveAnswer(qid, value, el) {
    if (el && el.closest('.option-card')) {
        const card = el.closest('.option-card');
        card.closest('.question-pane').querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.classList.remove('just-picked'); void card.offsetWidth; card.classList.add('just-picked');
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

/* ============================================================
   Anti-cheat: detect leaving the exam page + sound warning
   ============================================================ */
// --- Warning sound: the school's own audio file, looped while away ---
// Routed through the Web Audio API so we can (a) amplify above the system
// volume and (b) improve the odds of playing through iOS's silent switch,
// which plain <audio> elements normally respect.
const warnAudio = new Audio("{{ asset('assets/warning.mp3') }}");
warnAudio.loop = true;      // keep playing until the student comes back
warnAudio.preload = 'auto';
warnAudio.volume = 1;

// Escalating loudness: gentle at first (a brief accidental switch shouldn't
// disturb the room), then rises the longer the student stays away.
// NOTE: gain only helps when the phone volume is LOW, not zero — 0 × gain = 0.
// A browser cannot raise the device's hardware/media volume, so we pair the
// sound with vibration + persistent screen flashing (both volume-independent).
const WARN_GAIN_START = 1.0;   // opening level
const WARN_GAIN_MAX   = 4.0;   // ceiling — pushes hard so a low (not muted) volume is still audible
const WARN_RAMP_SEC   = 6;     // seconds to climb from start to max
let actx = null, warnGain = null, audioPrimed = false;

// Browsers block audio until a user gesture — unlock & wire the graph on first interaction.
function primeAudio() {
    if (audioPrimed) return;
    try {
        actx = new (window.AudioContext || window.webkitAudioContext)();
        const src = actx.createMediaElementSource(warnAudio);
        warnGain = actx.createGain();
        warnGain.gain.value = WARN_GAIN_START;
        src.connect(warnGain); warnGain.connect(actx.destination);
        audioPrimed = true;
    } catch (e) { audioPrimed = false; }
    if (actx && actx.state === 'suspended') actx.resume();
}
document.addEventListener('click', primeAudio);
document.addEventListener('keydown', primeAudio);

// Volume-independent alarms: vibration + repeating screen flash. These keep
// alerting the student even when the phone's media volume is turned all the way
// down, which no audio API can override.
let alertTimer = null;
function startVolumeProofAlarm() {
    stopVolumeProofAlarm();
    const pulse = () => {
        flashScreen();
        // Android fires vibration regardless of volume; iOS Safari ignores it (no-op).
        try { if (navigator.vibrate) navigator.vibrate([400, 200, 400]); } catch (e) {}
    };
    pulse();
    alertTimer = setInterval(pulse, 1200);   // keep pulsing the whole time they're away
}
function stopVolumeProofAlarm() {
    if (alertTimer) { clearInterval(alertTimer); alertTimer = null; }
    try { if (navigator.vibrate) navigator.vibrate(0); } catch (e) {}
}

function playWarning() {
    if (actx && actx.state === 'suspended') actx.resume();   // resume in case it was suspended
    if (warnGain && actx) {
        const t = actx.currentTime;
        warnGain.gain.cancelScheduledValues(t);
        warnGain.gain.setValueAtTime(WARN_GAIN_START, t);
        warnGain.gain.linearRampToValueAtTime(WARN_GAIN_MAX, t + WARN_RAMP_SEC);
    }
    try { warnAudio.currentTime = 0; warnAudio.play().catch(() => {}); } catch (e) {}
    startVolumeProofAlarm();
}
function stopWarning() {
    try {
        if (warnGain && actx) { warnGain.gain.cancelScheduledValues(actx.currentTime); warnGain.gain.value = WARN_GAIN_START; }
        warnAudio.pause(); warnAudio.currentTime = 0;
    } catch (e) {}
    stopVolumeProofAlarm();
}

function flashScreen() {
    const f = document.getElementById('cheatFlash');
    f.classList.remove('show'); void f.offsetWidth; f.classList.add('show');
}

let violations = {{ (int) ($result->violation_count ?? 0) }};   // resume the count across refreshes
let isAway = false;
function leftExam() {
    if (isAway) return;              // debounce blur + visibilitychange firing together
    isAway = true;
    playWarning(); flashScreen();
}
function returnedToExam() {
    if (!isAway) return;
    isAway = false;
    stopWarning();
    // record the violation on the server (authoritative count), then react
    fetch(VIOLATION_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: '{}'
    }).then(r => r.json())
      .then(d => { violations = (d && typeof d.violations === 'number') ? d.violations : violations + 1; showViolationModal(); })
      .catch(() => { violations++; showViolationModal(); });
}
function showViolationModal() {
    // We record & warn, but never auto-terminate — a network drop or phone call
    // shouldn't end the exam. The teacher reviews the count and decides.
    Swal.fire({
        title: 'Kamu keluar dari halaman ujian!',
        html: '<p class="sitara-swal-text">Aktivitas ini <b>tercatat</b> oleh sistem (<b>pelanggaran ke-' + violations + '</b>) dan akan <b>dilaporkan ke guru</b>.<br>' +
              'Jika ini karena kendala jaringan, segera lanjutkan ujianmu. Tetaplah <b>jujur</b> — <b>jangan mencontek</b> ya! 🙏</p>',
        imageUrl: EXAM_MASCOT, imageWidth: 130, imageAlt: 'SITARA',
        allowOutsideClick: false, allowEscapeKey: false, buttonsStyling: false,
        confirmButtonText: 'Saya mengerti, lanjut',
        customClass: {
            popup: 'sitara-swal', title: 'sitara-swal-title', image: 'sitara-swal-img',
            actions: 'sitara-swal-actions',
            confirmButton: 'sitara-swal-btn sitara-swal-btn-danger'
        }
    });
}
// tab switch / minimize
document.addEventListener('visibilitychange', () => { document.hidden ? leftExam() : returnedToExam(); });
// switching to another app / window while tab still "visible"
window.addEventListener('blur', () => { if (!document.hidden) leftExam(); });
window.addEventListener('focus', () => { if (!document.hidden) returnedToExam(); });

/* ============================================================
   Friendly reminder to stay honest (with mascot) on start
   ============================================================ */
window.addEventListener('load', () => {
    updateProgress();
    Swal.fire({
        title: 'Semangat, ' + STUDENT + '! 🦉',
        html: '<p class="sitara-swal-text">Kerjakan dengan tenang, teliti, dan <b>jujur</b>.<br>' +
              'Jangan berpindah tab atau keluar dari halaman ini — sistem <b>memantau, mencatat, &amp; melaporkan</b> ' +
              'aktivitasmu ke guru, dan akan <b>berbunyi peringatan</b> bila kamu keluar.<br><br>' +
              '<b>Jangan mencontek ya, kamu pasti bisa!</b> 💪</p>',
        imageUrl: EXAM_MASCOT, imageWidth: 150, imageAlt: 'SITARA',
        allowOutsideClick: false, buttonsStyling: false,
        confirmButtonText: 'Siap, mulai ujian!',
        customClass: {
            popup: 'sitara-swal', title: 'sitara-swal-title', image: 'sitara-swal-img',
            actions: 'sitara-swal-actions',
            confirmButton: 'sitara-swal-btn sitara-swal-btn-primary'
        }
    }).then(() => primeAudio());
});
</script>
</body>
</html>
