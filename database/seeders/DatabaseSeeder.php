<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamPackage;
use App\Models\ExamSchedule;
use App\Models\Major;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Room;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // Super Admin
        // ---------------------------------------------------------------
        User::create([
            'role' => User::ROLE_SUPER_ADMIN,
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@sitara.test',
            'password' => Hash::make('password'),
        ]);

        // ---------------------------------------------------------------
        // School + Admin
        // ---------------------------------------------------------------
        $school = School::create([
            'name' => 'SMA Negeri 1 Nusantara',
            'npsn' => '20100001',
            'level' => 'SMA',
            'address' => 'Jl. Pendidikan No. 1, Jakarta',
            'phone' => '021-1234567',
            'email' => 'info@sman1nusantara.sch.id',
            'principal_name' => 'Dr. Bambang Sutrisno, M.Pd',
            'primary_color' => '#2563EB',
        ]);

        $admin = User::create([
            'school_id' => $school->id,
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin Sekolah',
            'email' => 'admin@sitara.test',
            'password' => Hash::make('password'),
        ]);

        // Langganan aktif untuk sekolah contoh (berlaku 1 bulan ke depan).
        Subscription::create([
            'school_id' => $school->id,
            'plan_name' => 'Bulanan',
            'months' => 1,
            'price' => Subscription::monthlyPrice(),
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->startOfDay()->addMonthNoOverflow(),
            'payment_method' => 'Seeder',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // ---------------------------------------------------------------
        // Academic structure
        // ---------------------------------------------------------------
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);
        $year->semesters()->createMany([
            ['name' => 'Ganjil', 'is_active' => true],
            ['name' => 'Genap', 'is_active' => false],
        ]);
        $semester = $year->semesters()->first();

        $ipa = Major::create(['school_id' => $school->id, 'code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam']);
        Major::create(['school_id' => $school->id, 'code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial']);

        $subjects = collect([
            ['Matematika', 'MTK', '#2563EB'],
            ['Bahasa Indonesia', 'BIN', '#12b76a'],
            ['Bahasa Inggris', 'BIG', '#f59e0b'],
            ['Fisika', 'FIS', '#7c3aed'],
        ])->map(fn ($s) => Subject::create([
            'school_id' => $school->id, 'name' => $s[0], 'code' => $s[1], 'color' => $s[2],
        ]));
        $mathSubject = $subjects->first();

        foreach (['Ruang A', 'Ruang B', 'Lab Komputer'] as $r) {
            Room::create(['school_id' => $school->id, 'name' => $r, 'capacity' => 36, 'location' => 'Gedung Utama']);
        }

        // ---------------------------------------------------------------
        // Teacher
        // ---------------------------------------------------------------
        $teacherUser = User::create([
            'school_id' => $school->id,
            'role' => User::ROLE_GURU,
            'name' => 'Siti Rahmawati, S.Pd',
            'email' => 'guru@sitara.test',
            'password' => Hash::make('password'),
        ]);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'school_id' => $school->id,
            'nip' => '198501012010012001',
            'name' => 'Siti Rahmawati, S.Pd',
            'gender' => 'P',
            'phone' => '081234567890',
        ]);

        $classroom = Classroom::create([
            'school_id' => $school->id,
            'major_id' => $ipa->id,
            'homeroom_teacher_id' => $teacher->id,
            'name' => 'XII IPA 1',
            'grade_level' => 'XII',
        ]);

        // ---------------------------------------------------------------
        // Students
        // ---------------------------------------------------------------
        $names = ['Andi Pratama', 'Budi Santoso', 'Citra Dewi', 'Dian Permata', 'Eka Putri', 'Fajar Nugroho'];
        foreach ($names as $i => $name) {
            $nis = '24010' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $u = User::create([
                'school_id' => $school->id,
                'role' => User::ROLE_SISWA,
                'name' => $name,
                'username' => $nis,
                'email' => $nis . '@siswa.sitara.local',
                'password' => Hash::make('password'),
            ]);
            Student::create([
                'user_id' => $u->id,
                'school_id' => $school->id,
                'classroom_id' => $classroom->id,
                'nis' => $nis,
                'nisn' => '00' . $nis,
                'name' => $name,
                'gender' => $i % 2 ? 'P' : 'L',
            ]);
        }

        // ---------------------------------------------------------------
        // Question bank + questions
        // ---------------------------------------------------------------
        $bank = QuestionBank::create([
            'school_id' => $school->id,
            'subject_id' => $mathSubject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Bank Soal Matematika Kelas XII',
            'description' => 'Kumpulan soal ujian akhir semester ganjil.',
        ]);

        $questions = [
            [
                'type' => 'multiple_choice',
                'question_text' => 'Hasil dari 15 + 27 × 2 adalah ...',
                'options' => [['key' => 'A', 'text' => '69'], ['key' => 'B', 'text' => '84'], ['key' => 'C', 'text' => '54'], ['key' => 'D', 'text' => '42']],
                'correct_answer' => ['A'],
                'score' => 10, 'difficulty' => 'easy',
                'explanation' => 'Operasi perkalian didahulukan: 27×2=54, lalu 15+54=69.',
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'Turunan pertama dari f(x) = 3x² + 2x adalah ...',
                'options' => [['key' => 'A', 'text' => '6x + 2'], ['key' => 'B', 'text' => '3x + 2'], ['key' => 'C', 'text' => '6x'], ['key' => 'D', 'text' => '5x']],
                'correct_answer' => ['A'],
                'score' => 10, 'difficulty' => 'medium',
            ],
            [
                'type' => 'true_false',
                'question_text' => 'Bilangan 17 adalah bilangan prima.',
                'options' => [['key' => 'true', 'text' => 'Benar'], ['key' => 'false', 'text' => 'Salah']],
                'correct_answer' => ['true'],
                'score' => 5, 'difficulty' => 'easy',
            ],
            [
                'type' => 'short_answer',
                'question_text' => 'Berapakah nilai dari akar kuadrat 144?',
                'correct_answer' => ['12'],
                'score' => 10, 'difficulty' => 'easy',
            ],
            [
                'type' => 'essay',
                'question_text' => 'Jelaskan langkah-langkah menyelesaikan persamaan kuadrat dengan rumus abc!',
                'score' => 20, 'difficulty' => 'hard',
            ],
        ];
        foreach ($questions as $q) {
            $bank->questions()->create($q);
        }

        // ---------------------------------------------------------------
        // Exam package + exam + schedule
        // ---------------------------------------------------------------
        $package = ExamPackage::create([
            'school_id' => $school->id,
            'subject_id' => $mathSubject->id,
            'teacher_id' => $teacher->id,
            'name' => 'Paket UAS Matematika Ganjil',
            'randomize_questions' => true,
            'randomize_options' => true,
        ]);
        $package->questions()->sync(
            $bank->questions->mapWithKeys(fn ($q, $i) => [$q->id => ['order' => $i]])->all()
        );

        $exam = Exam::create([
            'school_id' => $school->id,
            'exam_package_id' => $package->id,
            'subject_id' => $mathSubject->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'title' => 'UAS Matematika Semester Ganjil',
            'description' => "Kerjakan dengan jujur. Tidak diperbolehkan membuka catatan.",
            'duration_minutes' => 60,
            'passing_score' => 70,
            'randomize_questions' => true,
            'randomize_options' => true,
            'show_result' => true,
            'status' => 'published',
        ]);

        ExamSchedule::create([
            'exam_id' => $exam->id,
            'classroom_id' => $classroom->id,
            'room_id' => Room::where('school_id', $school->id)->first()->id,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addDays(7),
            'token' => strtoupper(Str::random(6)),
            'is_active' => true,
        ]);

        Announcement::create([
            'school_id' => $school->id,
            'user_id' => null,
            'title' => 'Jadwal Ujian Akhir Semester Ganjil',
            'content' => 'Ujian Akhir Semester akan dilaksanakan mulai minggu depan. Harap siswa mempersiapkan diri dan memastikan perangkat dalam kondisi baik.',
            'target' => 'all',
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
