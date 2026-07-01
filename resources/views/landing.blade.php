<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SITARA · Sistem Tes Akademik Terpadu</title>
    <meta name="description" content="Platform ujian sekolah berbasis web yang modern, aman, dan mudah digunakan.">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/sitara.css') }}" rel="stylesheet">
    <style>
        :root { --hero-blue: #1763c9; --hero-blue-dark: #0f4ea3; --hero-mint: #2fe3a8; }

        /* ---- Navbar (transparent over hero, solid on scroll) ---- */
        .navbar-hero { transition: background .3s, box-shadow .3s, padding .3s; padding-top: 1rem; padding-bottom: 1rem; }
        .navbar-hero .navbar-brand, .navbar-hero .nav-link, .navbar-hero .social-ic { color: #fff; }
        .navbar-hero .nav-link { position: relative; font-weight: 600; letter-spacing: .03em; }
        .navbar-hero .nav-link::after { content:''; position:absolute; left:.75rem; right:.75rem; bottom:.15rem; height:2px; background:var(--hero-mint); transform:scaleX(0); transform-origin:left; transition:transform .25s; }
        .navbar-hero .nav-link:hover::after, .navbar-hero .nav-link.text-mint::after { transform:scaleX(1); }
        .navbar-hero .social-ic { font-size: 1.15rem; transition: transform .2s, color .2s; }
        .navbar-hero .social-ic:hover { color: var(--hero-mint); transform: translateY(-3px); }
        .navbar-hero.scrolled { background: var(--hero-blue); box-shadow: 0 8px 30px rgba(8,30,70,.25); padding-top: .6rem; padding-bottom: .6rem; }

        /* ---- Hero ---- */
        .hero { position: relative; overflow: hidden; background: var(--hero-blue); color: #fff; padding: 6rem 0 6rem; }
        .hero .eyebrow { text-transform: uppercase; letter-spacing: .12em; font-weight: 700; font-size: .85rem; color: rgba(255,255,255,.85); }
        .hero .eyebrow b { color: var(--hero-mint); }
        .hero h1 { font-weight: 800; font-size: clamp(2.4rem, 5vw, 3.6rem); line-height: 1.05; letter-spacing: -.02em; }
        .hero h1 .mint { color: var(--hero-mint); }
        .hero .lead-text { color: rgba(255,255,255,.9); max-width: 30rem; }
        .hero .lead-text b { color: #fff; }
        .hero .how-line { color: var(--hero-mint); font-weight: 700; }
        .btn-pill-mint { background: var(--hero-mint); color: #0f4ea3; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; border: none; border-radius: 50px; padding: 1rem 3rem; box-shadow: 0 14px 30px rgba(47,227,168,.4); transition: transform .2s, box-shadow .2s, background .2s; }
        .btn-pill-mint:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(47,227,168,.55); background: #38f0b4; color: #0f4ea3; }

        /* decorative mint swirl behind illustration */
        .hero-blob { position: absolute; right: 2%; top: 50%; transform: translateY(-50%); width: 560px; max-width: 48vw; opacity: .9; z-index: 0; pointer-events: none; }
        .hero-art { position: relative; z-index: 2; animation: sitara-float 6s ease-in-out infinite; }
        .hero-art .mockup { background: #fff; border-radius: 20px; box-shadow: 0 40px 80px rgba(8,30,70,.45); overflow: hidden; }

        /* ---- General sections ---- */
        .navbar-glass .nav-link { position: relative; }
        .feature-icon { width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center; font-size: 1.5rem; transition: transform .25s ease; }
        .card:hover .feature-icon { transform: scale(1.12) rotate(-5deg); }
        .step-num { width: 46px; height: 46px; border-radius: 50%; background: var(--sitara-accent-grad); color: #fff; display: grid; place-items: center; font-weight: 700; box-shadow: 0 6px 16px rgba(20,184,166,.35); }
        .section { padding: 5rem 0; }
        .gradient-text { background: linear-gradient(90deg,#2563EB,#14b8a6); -webkit-background-clip: text; background-clip: text; color: transparent; }

        /* ---- Pricing ---- */
        .price-hero { background: linear-gradient(135deg,#0f4ea3 0%,#1763c9 55%,#0d9488 100%); }
        .price-card { background:#fff; border-radius: 22px; box-shadow: 0 30px 60px rgba(8,30,70,.18); overflow:hidden; }
        .price-card .price-head { background: linear-gradient(135deg,#2563EB,#0d9488); color:#fff; padding: 2rem; }
        .price-amount { font-size: clamp(2.4rem,5vw,3.2rem); font-weight: 800; line-height: 1; }
        .price-badge { background: var(--hero-mint); color:#0f4ea3; font-weight:700; letter-spacing:.06em; text-transform:uppercase; font-size:.7rem; border-radius:50px; padding:.35rem .9rem; }
        .price-feature { display:flex; align-items:flex-start; gap:.65rem; padding:.5rem 0; }
        .price-feature i { color:#0d9488; font-size:1.1rem; margin-top:.1rem; }
        .price-pill { border:1.5px solid #d7e3f4; border-radius:50px; padding:.4rem 1.1rem; font-weight:600; font-size:.9rem; color:#1763c9; background:#fff; }
        .price-pill b { color:#0d9488; }
        footer a { color: #cbd5e1; text-decoration: none; transition: color .2s, padding-left .2s; }
        footer a:hover { color: #5eead4; padding-left: 4px; }
        .btn-light:hover { transform: translateY(-2px); }
        /* ---- Mobile navbar toggler ---- */
        .navbar-hero .navbar-toggler { padding: .3rem .55rem; border-radius: 10px; background: rgba(255,255,255,.12); }
        .navbar-hero .navbar-toggler:focus { box-shadow: none; }
        .navbar-hero .navbar-toggler i { line-height: 1; }

        @media (max-width: 991.98px) {
            .hero { padding: 5rem 0 4rem; text-align: center; }
            .hero .lead-text { margin-inline: auto; }
            .hero-blob { display: none; }

            /* Solid dropdown panel so the menu never overlaps the hero */
            .navbar-hero .navbar-collapse {
                margin-top: .85rem;
                padding: .75rem 1rem 1rem;
                background: var(--hero-blue-dark);
                border: 1px solid rgba(255,255,255,.12);
                border-radius: 18px;
                box-shadow: 0 24px 48px rgba(8,30,70,.45);
            }
            .navbar-hero .navbar-nav { gap: .15rem; }
            .navbar-hero .nav-link { padding: .65rem .35rem; border-radius: 10px; }
            .navbar-hero .nav-link:hover { background: rgba(255,255,255,.08); }
            .navbar-hero .nav-link::after { display: none; }
            .navbar-hero .navbar-collapse > .d-flex {
                justify-content: center;
                margin-top: .85rem; padding-top: .85rem;
                border-top: 1px solid rgba(255,255,255,.15);
            }
            .navbar-hero .btn-pill-mint { padding: .65rem 1.5rem; }
        }
    </style>
</head>
<body>
{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-hero fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
            <img src="{{ asset('assets/maskot5.png') }}" alt="SITARA" style="height:46px;width:auto;filter:drop-shadow(0 4px 8px rgba(0,0,0,.25))">
            <span style="font-size:1.4rem;letter-spacing:.02em">SITARA</span>
        </a>
        <button class="navbar-toggler border-0 text-white" data-bs-toggle="collapse" data-bs-target="#nav"><i class="bi bi-list fs-2"></i></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav mx-auto gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#keunggulan">Keunggulan</a></li>
                <li class="nav-item"><a class="nav-link" href="#cara-kerja">Cara Kerja</a></li>
                <li class="nav-item"><a class="nav-link" href="#harga">Harga</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="https://wa.me/6285705041136" target="_blank" class="social-ic" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="https://www.instagram.com/uneeddeveloper/" target="_blank" class="social-ic" title="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="{{ route('login') }}" class="btn btn-pill-mint px-4 py-2" style="font-size:.8rem"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
            </div>
        </div>
    </div>
</nav>

{{-- Hero --}}
<header class="hero">
    {{-- decorative mint swirl --}}
    <svg class="hero-blob" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M300 70c80 0 150 30 195 95 50 72 55 168 5 240-46 66-128 95-208 92-92-3-178-55-205-143-24-78 4-168 70-222 41-34 92-62 143-62z" stroke="url(#mg)" stroke-width="42" opacity=".55"/>
        <path d="M315 150c70-5 138 35 165 100 28 68 6 152-52 196-58 44-146 44-205-2-52-40-72-115-48-176 25-66 80-113 140-118z" stroke="url(#mg)" stroke-width="26" opacity=".8"/>
        <defs><linearGradient id="mg" x1="80" y1="80" x2="520" y2="520" gradientUnits="userSpaceOnUse"><stop stop-color="#2fe3a8"/><stop offset="1" stop-color="#5eead4"/></linearGradient></defs>
    </svg>

    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <p class="eyebrow mb-3">Platform ujian sekolah <b>terpadu</b></p>
                <h1 class="mb-3">Bukan ujian biasa,<br><span class="mint">lebih dari sekadar tes.</span></h1>
                <p class="lead-text mb-4">Dari penyusunan soal hingga analisis nilai. Kami menjadikan ujian <b>sekolah dan madrasah</b> Anda lebih mudah, aman, dan modern.</p>
                <p class="how-line mb-4">Bagaimana? Berbasis DIFERENSIASI dan KEAMANAN</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('login') }}" class="btn btn-pill-mint">Mulai Sekarang</a>
                    <a href="#harga" class="btn btn-pill-mint" style="background:rgba(255,255,255,.14);color:#fff;box-shadow:none">Lihat Paket</a>
                </div>
            </div>
            <div class="col-lg-6 d-flex justify-content-center" data-aos="fade-left">
                <div class="hero-art text-center" style="max-width:460px;width:100%">
                    <img src="{{ asset('assets/maskot1.png') }}" alt="Maskot SITARA" class="img-fluid" style="max-height:480px;filter:drop-shadow(0 30px 50px rgba(8,30,70,.45))">
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Statistik --}}
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row text-center g-4">
            @php $st = [['building','Sekolah',$stats['schools']],['person-workspace','Guru',$stats['teachers']],['people','Siswa',$stats['students']],['pencil-square','Ujian',$stats['exams']]]; @endphp
            @foreach($st as $i => $s)
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="{{ $i*100 }}">
                <i class="bi bi-{{ $s[0] }} fs-2 text-primary"></i>
                <div class="display-6 fw-bold">{{ number_format($s[2]) }}+</div>
                <div class="text-muted">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Tentang --}}
<section class="section" id="tentang">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('assets/maskot3.png') }}" alt="" class="animate-float" style="height:96px;width:auto">
                    <h2 class="fw-bold mb-0">Tentang <span class="gradient-text">SITARA</span></h2>
                </div>
                <p class="text-muted">SITARA (Sistem Tes Akademik Terpadu) adalah platform ujian sekolah berbasis web yang dirancang untuk menggantikan ujian kertas dengan proses digital yang efisien. Mendukung banyak sekolah (multi-tenant), berbagai jenis soal, penilaian otomatis, dan analisis hasil yang mendalam.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Multi sekolah dalam satu sistem</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Penilaian otomatis & analisis butir soal</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i>Aman dengan kontrol akses berbasis peran</li>
                </ul>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-3">
                    @php $roles=[['shield-lock','Super Admin','Kelola seluruh sekolah & data sistem'],['person-badge','Admin Sekolah','Kelola guru, siswa, kelas & jadwal'],['easel','Guru','Buat bank soal, paket, & nilai siswa'],['mortarboard','Siswa','Ikuti ujian & lihat hasil']]; @endphp
                    @foreach($roles as $r)
                    <div class="col-sm-6">
                        <div class="card h-100"><div class="card-body">
                            <i class="bi bi-{{ $r[0] }} fs-3 text-primary"></i>
                            <h6 class="fw-bold mt-2">{{ $r[1] }}</h6>
                            <small class="text-muted">{{ $r[2] }}</small>
                        </div></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Keunggulan --}}
<section class="section bg-white" id="keunggulan">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">Keunggulan Utama</h2>
            <p class="text-muted">Semua yang Anda butuhkan untuk ujian online yang lancar</p>
        </div>
        <div class="row g-4">
            @php $feats=[
                ['bi-clock','soft-primary','Timer Otomatis','Hitung mundur dengan auto-submit saat waktu habis.'],
                ['bi-save','soft-success','Auto Save','Jawaban tersimpan otomatis, aman dari koneksi terputus.'],
                ['bi-shuffle','soft-warning','Random Soal','Acak soal & pilihan jawaban untuk mencegah kecurangan.'],
                ['bi-graph-up','soft-info','Analisis Nilai','Statistik, ranking, dan analisis tingkat kesulitan soal.'],
                ['bi-collection','soft-purple','Bank Soal','Kelola ribuan soal dengan gambar, audio, & video.'],
                ['bi-shield-check','soft-danger','Aman','Fullscreen, blokir tombol kembali, & audit log.'],
            ]; @endphp
            @foreach($feats as $i => $f)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($i%3)*100 }}">
                <div class="card h-100"><div class="card-body p-4">
                    <div class="feature-icon bg-{{ $f[1] }} mb-3"><i class="bi {{ $f[0] }}"></i></div>
                    <h5 class="fw-bold">{{ $f[2] }}</h5>
                    <p class="text-muted mb-0">{{ $f[3] }}</p>
                </div></div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Cara Kerja --}}
<section class="section" id="cara-kerja">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up"><h2 class="fw-bold">Cara Kerja</h2><p class="text-muted">Empat langkah sederhana</p></div>
        <div class="row g-4">
            @php $steps=[['Siapkan Soal','Guru membuat bank soal & menyusunnya menjadi paket.'],['Jadwalkan Ujian','Admin/guru mengatur jadwal, kelas, & ruang ujian.'],['Siswa Ujian','Siswa login dengan NIS dan mengerjakan ujian CBT.'],['Lihat Hasil','Nilai otomatis dihitung & dianalisis secara instan.']]; @endphp
            @foreach($steps as $i => $s)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i*100 }}">
                <div class="d-flex align-items-center gap-3 mb-3"><span class="step-num">{{ $i+1 }}</span><h6 class="fw-bold mb-0">{{ $s[0] }}</h6></div>
                <p class="text-muted">{{ $s[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Harga / Paket Langganan --}}
<section class="section price-hero text-white" id="harga">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="price-badge mb-3 d-inline-block">Paket Langganan</span>
            <h2 class="fw-bold">Satu Harga, Semua Fitur</h2>
            <p class="opacity-75 mb-0">Berlangganan per sekolah. Tanpa biaya per siswa, tanpa biaya tersembunyi.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8" data-aos="zoom-in">
                @php $fmt = fn ($n) => $currency . ' ' . number_format($n, 0, ',', '.'); @endphp
                <div class="price-card">
                    <div class="row g-0">
                        {{-- Kolom harga --}}
                        <div class="col-md-5 price-head d-flex flex-column justify-content-center">
                            <span class="price-badge align-self-start mb-3">Paling Populer</span>
                            <h3 class="fw-bold mb-1">Paket Sekolah</h3>
                            <p class="opacity-75 small mb-4">Akses penuh untuk admin, guru &amp; siswa tanpa batas.</p>
                            <div class="d-flex align-items-end gap-2">
                                <span class="price-amount">{{ $fmt($price) }}</span>
                                <span class="opacity-75 mb-1">/ bulan</span>
                            </div>
                            <div class="mt-4 d-flex flex-wrap gap-2">
                                <span class="price-pill text-white border-0" style="background:rgba(255,255,255,.15)">1 Bulan</span>
                                <span class="price-pill text-white border-0" style="background:rgba(255,255,255,.15)">3 Bulan</span>
                                <span class="price-pill text-white border-0" style="background:rgba(255,255,255,.15)">6 Bulan</span>
                                <span class="price-pill text-white border-0" style="background:rgba(255,255,255,.15)">1 Tahun</span>
                            </div>
                        </div>

                        {{-- Kolom fitur --}}
                        <div class="col-md-7">
                            <div class="p-4 p-md-5 text-dark">
                                <h6 class="fw-bold text-uppercase text-muted mb-3" style="letter-spacing:.08em;font-size:.78rem">Semua sudah termasuk</h6>
                                <div class="row">
                                    @php $included = [
                                        'Jumlah guru & siswa tanpa batas',
                                        'Bank soal & paket soal tak terbatas',
                                        'Semua tipe soal (PG, essay, isian, dll)',
                                        'Penilaian otomatis & koreksi essay',
                                        'Analisis butir soal & ranking nilai',
                                        'Mode ujian aman (anti-curang)',
                                        'Auto-save & timer otomatis',
                                        'Impor data siswa & soal (Excel/Word)',
                                        'Dukungan teknis & pembaruan rutin',
                                    ]; @endphp
                                    @foreach($included as $inc)
                                    <div class="col-12">
                                        <div class="price-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $inc }}</span></div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="d-grid gap-2 d-sm-flex mt-4">
                                    <a href="https://wa.me/6285705041136?text={{ urlencode('Halo, saya tertarik berlangganan SITARA untuk sekolah kami.') }}" target="_blank" class="btn btn-success btn-lg px-4 fw-semibold"><i class="bi bi-whatsapp me-1"></i>Mulai Langganan</a>
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg px-4 fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i>Sudah Berlangganan? Masuk</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center opacity-75 small mt-4 mb-0">
                    <i class="bi bi-shield-check me-1"></i>Pembayaran dikonfirmasi manual oleh tim kami. Hubungi kami untuk uji coba atau penawaran khusus banyak sekolah.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="section bg-white" id="faq">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up"><h2 class="fw-bold">Pertanyaan Umum</h2></div>
        <div class="row justify-content-center"><div class="col-lg-8">
            <div class="accordion" id="faqAcc" data-aos="fade-up">
                @php $faqs=[
                    ['Bagaimana siswa masuk ke sistem?','Siswa login menggunakan NIS dan kata sandi yang diberikan oleh sekolah.'],
                    ['Apakah jawaban hilang jika internet terputus?','Tidak. Setiap jawaban disimpan otomatis sehingga siswa dapat melanjutkan ujian.'],
                    ['Apakah mendukung soal essay?','Ya. SITARA mendukung pilihan ganda, benar/salah, menjodohkan, isian singkat, essay, dan unggah file.'],
                    ['Apakah bisa untuk banyak sekolah?','Ya, SITARA dirancang multi-tenant sehingga satu sistem dapat melayani banyak sekolah.'],
                    ['Bagaimana sistem langganannya?','Langganan dihitung per sekolah per bulan dengan semua fitur termasuk, tanpa biaya per siswa. Sekolah dapat memperpanjang kapan saja dari dashboard admin, dan pembayaran dikonfirmasi oleh tim kami.'],
                    ['Apa yang terjadi jika langganan berakhir?','Data sekolah tetap aman. Akses ujian untuk guru & siswa dijeda sementara hingga langganan diperpanjang, sementara admin tetap bisa masuk untuk memperpanjang.'],
                ]; @endphp
                @foreach($faqs as $i => $q)
                <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i? 'collapsed':'' }} rounded fw-semibold" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">{{ $q[0] }}</button>
                    </h2>
                    <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i? '':'show' }}" data-bs-parent="#faqAcc">
                        <div class="accordion-body text-muted">{{ $q[1] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div></div>
    </div>
</section>

{{-- Testimoni --}}
<section class="section" id="testimoni">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up"><h2 class="fw-bold">Apa Kata Mereka</h2></div>
        <div class="row g-4">
            @php $tes=[['Pak Budi','Kepala Sekolah','SITARA membuat pelaksanaan ujian jauh lebih efisien dan hemat kertas.'],['Bu Sari','Guru Matematika','Analisis butir soal sangat membantu saya memperbaiki kualitas soal.'],['Andi','Siswa Kelas XII','Tampilannya mudah dipakai dan timer-nya jelas. Tidak bingung saat ujian.']]; @endphp
            @foreach($tes as $i => $t)
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $i*100 }}">
                <div class="card h-100"><div class="card-body p-4">
                    <div class="text-warning mb-2">@for($s=0;$s<5;$s++)<i class="bi bi-star-fill"></i>@endfor</div>
                    <p class="text-muted">"{{ $t[2] }}"</p>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <img src="https://ui-avatars.com/api/?background=2563EB&color=fff&name={{ urlencode($t[0]) }}" class="avatar-sm">
                        <div><div class="fw-semibold">{{ $t[0] }}</div><small class="text-muted">{{ $t[1] }}</small></div>
                    </div>
                </div></div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Kontak / CTA --}}
<section class="section" id="kontak">
    <div class="container">
        <div class="card border-0 overflow-hidden" style="background:linear-gradient(135deg,#2563EB 0%,#1e3a8a 55%,#0d9488 100%)" data-aos="zoom-in">
            <div class="card-body text-white p-5">
                <div class="row align-items-center">
                    <div class="col-md-8 text-center text-md-start">
                        <h2 class="fw-bold mb-2">Siap Memodernkan Ujian Sekolah Anda?</h2>
                        <p class="opacity-75 mb-4">Hubungi kami di <i class="bi bi-envelope"></i> info@sitara.sch.id atau mulai sekarang.</p>
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 fw-semibold">Masuk ke SITARA</a>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <img src="{{ asset('assets/maskot5.png') }}" alt="" class="img-fluid animate-float" style="max-height:230px">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-dark text-light pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold text-white d-flex align-items-center gap-2"><i class="bi bi-mortarboard-fill text-primary"></i> SITARA</h5>
                <p class="text-secondary small">Sistem Tes Akademik Terpadu — platform ujian sekolah berbasis web yang modern, aman, dan mudah dikembangkan.</p>
            </div>
            <div class="col-6 col-lg-2"><h6 class="text-white">Produk</h6><ul class="list-unstyled small"><li class="mb-1"><a href="#keunggulan">Keunggulan</a></li><li class="mb-1"><a href="#cara-kerja">Cara Kerja</a></li><li class="mb-1"><a href="#harga">Harga</a></li><li><a href="#faq">FAQ</a></li></ul></div>
            <div class="col-6 col-lg-2"><h6 class="text-white">Perusahaan</h6><ul class="list-unstyled small"><li class="mb-1"><a href="#tentang">Tentang</a></li><li class="mb-1"><a href="#testimoni">Testimoni</a></li><li><a href="#kontak">Kontak</a></li></ul></div>
            <div class="col-lg-4"><h6 class="text-white">Kontak</h6><p class="text-secondary small mb-1"><i class="bi bi-geo-alt me-2"></i>Indonesia</p><p class="text-secondary small mb-1"><i class="bi bi-envelope me-2"></i>info@sitara.sch.id</p><p class="text-secondary small"><i class="bi bi-telephone me-2"></i>(021) 1234-5678</p></div>
        </div>
        <hr class="border-secondary">
        <div class="text-center text-secondary small">
            © {{ date('Y') }} SITARA. Seluruh hak cipta dilindungi.
            &middot; by <a href="https://www.instagram.com/uneeddeveloper/" target="_blank" class="fw-semibold text-decoration-none" style="color:#5eead4">UneedDeveloper</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, easing: 'ease-out-cubic' });
    const nav = document.querySelector('.navbar-hero');
    window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 30));

    // Auto-close the mobile menu after tapping a link
    const navCollapse = document.getElementById('nav');
    if (navCollapse) {
        navCollapse.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (navCollapse.classList.contains('show')) {
                    bootstrap.Collapse.getOrCreateInstance(navCollapse).hide();
                }
            });
        });
    }
</script>
</body>
</html>
