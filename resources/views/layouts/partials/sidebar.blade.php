@php $role = auth()->user()->role; @endphp

<nav class="nav flex-column pb-4">
    @if ($role === 'super_admin')
        <div class="nav-section">Utama</div>
        <a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <div class="nav-section">Manajemen</div>
        <a class="nav-link {{ request()->routeIs('superadmin.schools.*') ? 'active' : '' }}" href="{{ route('superadmin.schools.index') }}"><i class="bi bi-building"></i> Sekolah</a>
        <a class="nav-link {{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}" href="{{ route('superadmin.admins.index') }}"><i class="bi bi-person-badge"></i> Admin Sekolah</a>
        <a class="nav-link {{ request()->routeIs('superadmin.subscriptions.*') ? 'active' : '' }}" href="{{ route('superadmin.subscriptions.index') }}"><i class="bi bi-wallet2"></i> Langganan</a>
        <a class="nav-link {{ request()->routeIs('superadmin.activity-logs') ? 'active' : '' }}" href="{{ route('superadmin.activity-logs') }}"><i class="bi bi-clock-history"></i> Log Aktivitas</a>

    @elseif ($role === 'admin')
        <div class="nav-section">Utama</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <div class="nav-section">Data Pengguna</div>
        <a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" href="{{ route('admin.teachers.index') }}"><i class="bi bi-person-workspace"></i> Guru</a>
        <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people"></i> Siswa</a>
        <div class="nav-section">Akademik</div>
        <a class="nav-link {{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}" href="{{ route('admin.classrooms.index') }}"><i class="bi bi-door-open"></i> Kelas</a>
        <a class="nav-link {{ request()->routeIs('admin.majors.*') ? 'active' : '' }}" href="{{ route('admin.majors.index') }}"><i class="bi bi-diagram-3"></i> Jurusan</a>
        <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-journal-bookmark"></i> Mata Pelajaran</a>
        <a class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}" href="{{ route('admin.academic-years.index') }}"><i class="bi bi-calendar3"></i> Tahun Ajaran</a>
        <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}"><i class="bi bi-building-gear"></i> Ruang Ujian</a>
        <div class="nav-section">Lainnya</div>
        <a class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}"><i class="bi bi-megaphone"></i> Pengumuman</a>
        <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.scores') }}"><i class="bi bi-bar-chart"></i> Laporan Nilai</a>
        <a class="nav-link {{ request()->routeIs('admin.subscription.*') ? 'active' : '' }}" href="{{ route('admin.subscription.index') }}"><i class="bi bi-wallet2"></i> Langganan</a>

    @elseif ($role === 'guru')
        <div class="nav-section">Utama</div>
        <a class="nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}" href="{{ route('guru.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <div class="nav-section">Soal & Ujian</div>
        <a class="nav-link {{ request()->routeIs('guru.question-banks.*') || request()->routeIs('guru.questions.*') ? 'active' : '' }}" href="{{ route('guru.question-banks.index') }}"><i class="bi bi-collection"></i> Bank Soal</a>
        <a class="nav-link {{ request()->routeIs('guru.packages.*') ? 'active' : '' }}" href="{{ route('guru.packages.index') }}"><i class="bi bi-box-seam"></i> Paket Soal</a>
        <a class="nav-link {{ request()->routeIs('guru.exams.*') ? 'active' : '' }}" href="{{ route('guru.exams.index') }}"><i class="bi bi-pencil-square"></i> Ujian</a>
        <a class="nav-link {{ request()->routeIs('guru.schedules.*') ? 'active' : '' }}" href="{{ route('guru.schedules.index') }}"><i class="bi bi-calendar-week"></i> Jadwal Ujian</a>
        <div class="nav-section">Penilaian</div>
        <a class="nav-link {{ request()->routeIs('guru.results.*') ? 'active' : '' }}" href="{{ route('guru.results.index') }}"><i class="bi bi-clipboard-check"></i> Hasil & Koreksi</a>

    @elseif ($role === 'siswa')
        <div class="nav-section">Utama</div>
        <a class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" href="{{ route('siswa.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <div class="nav-section">Ujian</div>
        <a class="nav-link {{ request()->routeIs('siswa.exams.*') ? 'active' : '' }}" href="{{ route('siswa.exams.index') }}"><i class="bi bi-pencil-square"></i> Jadwal Ujian</a>
        <a class="nav-link {{ request()->routeIs('siswa.results.*') ? 'active' : '' }}" href="{{ route('siswa.results.index') }}"><i class="bi bi-award"></i> Nilai Saya</a>
    @endif

    <div class="nav-section">Akun</div>
    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear"></i> Profil</a>
</nav>
