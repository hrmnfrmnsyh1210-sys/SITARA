# SITARA — Sistem Tes Akademik Terpadu

Platform ujian sekolah berbasis web (CBT / Computer Based Test) untuk sekolah, guru, dan siswa.
Dibangun dengan **Laravel 12**, **Blade**, **Bootstrap 5**, **MySQL**, dan arsitektur **MVC** yang rapi.

---

## ✨ Fitur Utama

- **4 Peran pengguna** dengan dashboard masing-masing: Super Admin, Admin Sekolah, Guru, Siswa
- **Multi-tenant** — satu sistem melayani banyak sekolah (data tiap sekolah terisolasi via `school_id`)
- **Master data lengkap** (CRUD + search + filter + pagination): Sekolah, Admin, Guru, Siswa, Kelas, Jurusan, Mata Pelajaran, Tahun Ajaran, Semester, Ruangan, Bank Soal, Paket Soal, Ujian, Jadwal, Pengumuman
- **Bank soal** dengan 6 jenis soal: Pilihan Ganda, Benar/Salah, Menjodohkan, Isian Singkat, Essay, Upload File; mendukung gambar & audio
- **Mesin CBT**: timer hitung mundur, auto-submit, auto-save jawaban, acak soal & pilihan, flag soal, navigasi nomor, fullscreen, blokir tombol back, resume saat koneksi terputus
- **Penilaian otomatis** untuk soal objektif + koreksi manual essay oleh guru
- **Analisis**: ranking siswa, rata-rata nilai, analisis butir soal & tingkat kesulitan, persentase jawaban benar
- **UI modern**: tema biru (#2563EB), sidebar responsif, navbar, dark mode, animasi AOS, Bootstrap Icons
- **Landing page** modern (hero, tentang, keunggulan, cara kerja, statistik, FAQ, testimoni, kontak)
- **Keamanan**: CSRF, hash password, middleware role, validasi form, login history, activity log, session

---

## 🚀 Cara Menjalankan

Proyek sudah dikonfigurasi untuk Laragon (MySQL `root` tanpa password, database `sitara`).

```bash
# 1. Install dependency (jika belum)
composer install

# 2. Konfigurasi .env sudah diset ke MySQL database "sitara".
#    Pastikan MySQL berjalan, lalu:

# 3. Migrasi + data demo
php artisan migrate:fresh --seed

# 4. Symlink storage (untuk upload gambar/foto)
php artisan storage:link

# 5. Jalankan
php artisan serve
```

Buka **http://127.0.0.1:8000**

---

## 🔑 Akun Demo (password semua: `password`)

| Peran         | Login                    | Kata Sandi |
|---------------|--------------------------|------------|
| Super Admin   | `superadmin@sitara.test` | `password` |
| Admin Sekolah | `admin@sitara.test`      | `password` |
| Guru          | `guru@sitara.test`       | `password` |
| Siswa         | `2401001` (NIS)          | `password` |

> Siswa login menggunakan **NIS**. Akun staff login dengan **email/username**.
> NIS siswa demo: `2401001` s/d `2401006`.

Ada **1 ujian aktif** (UAS Matematika) yang langsung bisa dikerjakan oleh siswa kelas XII IPA 1.

---

## 🗂️ Struktur Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── SuperAdmin/   (Dashboard, School, Admin)
│   │   ├── Admin/        (Dashboard, Teacher, Student, Classroom, Major,
│   │   │                  Subject, Room, AcademicYear, Announcement, Report)
│   │   ├── Guru/         (Dashboard, QuestionBank, Question, ExamPackage,
│   │   │                  Exam, ExamSchedule, Result)
│   │   ├── Siswa/        (Dashboard, Exam [CBT], Result)
│   │   ├── LandingController.php
│   │   └── ProfileController.php
│   └── Middleware/RoleMiddleware.php
├── Models/               (16 model dengan Eloquent relationship)
└── Services/GradingService.php   (mesin penilaian otomatis)

database/migrations/      (skema lengkap + foreign key)
database/seeders/         (data demo)

resources/views/
├── layouts/app.blade.php + partials/sidebar
├── components/stat-card.blade.php
├── auth/login, landing, profile
├── superadmin/, admin/, guru/, siswa/   (index + form per modul)
└── siswa/exams/take.blade.php           (antarmuka CBT)

routes/web.php            (rute terkelompok per peran + middleware role)
public/css/sitara.css     (tema kustom + dark mode + CBT)
```

---

## 🧱 Model & Relasi Database

`users` (role: super_admin/admin/guru/siswa) → `schools` → `teachers`, `students`,
`classrooms`, `majors`, `subjects`, `rooms`, `academic_years` → `semesters`,
`question_banks` → `questions`, `exam_packages` ⇄ `questions` (pivot),
`exams` → `exam_schedules` → `exam_results` → `answers`,
plus `announcements`, `login_histories`, `activity_logs`, `settings`.

Semua relasi memakai foreign key dengan `cascadeOnDelete` / `nullOnDelete` yang sesuai.

---

## 📌 Catatan Pengembangan Lanjutan

Fondasi sudah berjalan penuh. Beberapa fitur dari spesifikasi yang dapat ditambahkan
sebagai langkah berikutnya (struktur & data sudah disiapkan):

- **Export PDF / Excel & Import Excel** — tambahkan `maatwebsite/excel` & `barryvdh/laravel-dompdf`,
  lalu hubungkan ke tombol export di tiap tabel index.
- **Cetak Kartu Peserta + QR Code** — tambahkan `simplesoftwareio/simple-qrcode`.
- **Backup database & pengaturan website** — tabel `settings` sudah tersedia.
- **Notifikasi realtime** — broadcasting sudah dikonfigurasi (driver log).

Semua titik integrasi sudah disiapkan di model & controller terkait.
