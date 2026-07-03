<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

/**
 * Pencatat aktivitas otomatis.
 *
 * Mendengarkan event Eloquent (created/updated/deleted) untuk model-model
 * penting lalu menuliskannya ke tabel `activity_logs` sehingga halaman
 * Log Aktivitas super admin selalu terisi tanpa perlu memanggil manual
 * di tiap controller.
 */
class ActivityLogger
{
    /** Kelas model => label Bahasa Indonesia untuk deskripsi log. */
    protected static array $labels = [
        \App\Models\School::class => 'Sekolah',
        \App\Models\User::class => 'Pengguna',
        \App\Models\Teacher::class => 'Guru',
        \App\Models\Student::class => 'Siswa',
        \App\Models\Subject::class => 'Mata Pelajaran',
        \App\Models\Classroom::class => 'Kelas',
        \App\Models\Major::class => 'Jurusan',
        \App\Models\Room::class => 'Ruang',
        \App\Models\AcademicYear::class => 'Tahun Ajaran',
        \App\Models\Semester::class => 'Semester',
        \App\Models\QuestionBank::class => 'Bank Soal',
        \App\Models\Question::class => 'Soal',
        \App\Models\ExamPackage::class => 'Paket Soal',
        \App\Models\Exam::class => 'Ujian',
        \App\Models\ExamSchedule::class => 'Jadwal Ujian',
        \App\Models\Announcement::class => 'Pengumuman',
        \App\Models\Subscription::class => 'Langganan',
        \App\Models\Setting::class => 'Pengaturan',
    ];

    /** Event Eloquent => kata kerja aksi. */
    protected static array $verbs = [
        'created' => 'Tambah',
        'updated' => 'Ubah',
        'deleted' => 'Hapus',
    ];

    /** Atribut yang perubahannya tidak perlu dicatat (mengurangi noise). */
    protected static array $ignoredChanges = [
        'updated_at', 'last_login_at', 'remember_token', 'current_question_index',
    ];

    /**
     * Daftarkan listener global. Dipanggil dari AppServiceProvider::boot().
     */
    public static function register(): void
    {
        foreach (array_keys(static::$verbs) as $event) {
            Event::listen("eloquent.{$event}: *", function ($eventName, array $payload) use ($event) {
                static::handle($event, $payload[0] ?? null);
            });
        }
    }

    /**
     * Tulis satu baris log bila model termasuk yang dipantau.
     */
    protected static function handle(string $event, mixed $model): void
    {
        if (! $model instanceof Model) {
            return;
        }

        // Abaikan proses konsol (seeder, migrasi, test) agar log tidak terisi data palsu.
        if (app()->runningInConsole()) {
            return;
        }

        $class = get_class($model);
        if (! array_key_exists($class, static::$labels)) {
            return;
        }

        // Lewati update yang hanya menyentuh atribut internal (mis. last_login_at).
        if ($event === 'updated') {
            $changed = array_diff(array_keys($model->getChanges()), static::$ignoredChanges);
            if (empty($changed)) {
                return;
            }
        }

        $label = static::$labels[$class];

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => static::$verbs[$event] . ' ' . $label,
            'subject_type' => $class,
            'subject_id' => $model->getKey(),
            'description' => static::nameFor($model),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Ambil label pengenal model (nama/judul) untuk kolom deskripsi.
     */
    protected static function nameFor(Model $model): string
    {
        foreach (['name', 'title', 'year', 'key'] as $attr) {
            $value = $model->getAttribute($attr);
            if (! empty($value)) {
                return (string) $value;
            }
        }

        return '#' . $model->getKey();
    }
}
