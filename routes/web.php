<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Siswa;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Sitemap XML untuk Google (halaman publik saja).
Route::get('/sitemap.xml', function () {
    $url = rtrim(config('app.url'), '/');
    $today = now()->toDateString();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        . "<url><loc>{$url}/</loc><lastmod>{$today}</lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>"
        . "<url><loc>{$url}/login</loc><lastmod>{$today}</lastmod><changefreq>monthly</changefreq><priority>0.5</priority></url>"
        . '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated - shared (profile)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Forced password change on first login (generated default password).
    Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change');
    Route::put('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::post('/toggle-dark', function () {
        session(['dark_mode' => ! session('dark_mode', false)]);
        return back();
    })->name('toggle-dark');

    // Halaman info ketika langganan sekolah berakhir (untuk guru & siswa yang diblokir).
    Route::get('/langganan-ditangguhkan', fn () => View::make('subscription.suspended'))
        ->name('subscription.suspended');
});

/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('schools', SuperAdmin\SchoolController::class);
    Route::resource('admins', SuperAdmin\AdminController::class);
    Route::get('/activity-logs', [SuperAdmin\DashboardController::class, 'activityLogs'])->name('activity-logs');

    // Langganan
    Route::get('/subscriptions', [SuperAdmin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [SuperAdmin\SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::put('/subscriptions/price', [SuperAdmin\SubscriptionController::class, 'updatePrice'])->name('subscriptions.price');
    Route::post('/subscriptions/{subscription}/approve', [SuperAdmin\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscription}/reject', [SuperAdmin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');
});

/*
|--------------------------------------------------------------------------
| Admin Sekolah
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('teachers', Admin\TeacherController::class);

    // Excel import for students (registered before the resource so /students/import
    // isn't captured by the students/{student} show route).
    Route::get('students/import', [Admin\StudentController::class, 'importForm'])->name('students.import');
    Route::post('students/import', [Admin\StudentController::class, 'import'])->name('students.import.store');
    Route::get('students/import/template', [Admin\StudentController::class, 'importTemplate'])->name('students.import.template');
    Route::resource('students', Admin\StudentController::class);
    Route::resource('classrooms', Admin\ClassroomController::class);
    Route::resource('majors', Admin\MajorController::class);
    Route::resource('subjects', Admin\SubjectController::class);
    Route::resource('rooms', Admin\RoomController::class);
    Route::resource('academic-years', Admin\AcademicYearController::class);
    Route::resource('announcements', Admin\AnnouncementController::class);

    // Laporan (rekap nilai, data siswa, data guru) — tampilan + ekspor Excel/PDF.
    Route::get('/reports/scores', [Admin\ReportController::class, 'scores'])->name('reports.scores');
    Route::get('/reports/scores/excel', [Admin\ReportController::class, 'scoresExcel'])->name('reports.scores.excel');
    Route::get('/reports/scores/pdf', [Admin\ReportController::class, 'scoresPdf'])->name('reports.scores.pdf');
    Route::get('/reports/students/excel', [Admin\ReportController::class, 'studentsExcel'])->name('reports.students.excel');
    Route::get('/reports/students/pdf', [Admin\ReportController::class, 'studentsPdf'])->name('reports.students.pdf');
    Route::get('/reports/teachers/excel', [Admin\ReportController::class, 'teachersExcel'])->name('reports.teachers.excel');
    Route::get('/reports/teachers/pdf', [Admin\ReportController::class, 'teachersPdf'])->name('reports.teachers.pdf');

    // Langganan sekolah
    Route::get('/subscription', [Admin\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [Admin\SubscriptionController::class, 'store'])->name('subscription.store');
    Route::delete('/subscription/{subscription}', [Admin\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

/*
|--------------------------------------------------------------------------
| Guru
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:guru', 'subscribed'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [Guru\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('question-banks', Guru\QuestionBankController::class);

    // Word (.docx) import for questions within a bank.
    Route::get('question-banks/{questionBank}/questions/import', [Guru\QuestionController::class, 'importForm'])->name('question-banks.questions.import');
    Route::post('question-banks/{questionBank}/questions/import', [Guru\QuestionController::class, 'import'])->name('question-banks.questions.import.store');
    Route::get('questions/import/template', [Guru\QuestionController::class, 'importTemplate'])->name('questions.import.template');
    Route::resource('question-banks.questions', Guru\QuestionController::class)->shallow();
    Route::resource('packages', Guru\ExamPackageController::class);
    Route::resource('exams', Guru\ExamController::class);
    Route::resource('schedules', Guru\ExamScheduleController::class);

    // Hasil & penilaian
    Route::get('/results', [Guru\ResultController::class, 'index'])->name('results.index');
    Route::get('/results/export/excel', [Guru\ResultController::class, 'scoresExcel'])->name('results.excel');
    Route::get('/results/export/pdf', [Guru\ResultController::class, 'scoresPdf'])->name('results.pdf');
    Route::get('/results/{result}', [Guru\ResultController::class, 'show'])->name('results.show');
    Route::post('/results/{result}/grade', [Guru\ResultController::class, 'grade'])->name('results.grade');
    Route::get('/analysis/{exam}', [Guru\ResultController::class, 'analysis'])->name('analysis');
    Route::get('/analysis/{exam}/excel', [Guru\ResultController::class, 'analysisExcel'])->name('analysis.excel');
    Route::get('/analysis/{exam}/pdf', [Guru\ResultController::class, 'analysisPdf'])->name('analysis.pdf');
    Route::get('/schedules/{schedule}/attendance', [Guru\ResultController::class, 'attendancePdf'])->name('schedules.attendance');
});

/*
|--------------------------------------------------------------------------
| Siswa - CBT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:siswa', 'subscribed'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [Siswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/exams', [Siswa\ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{schedule}/confirm', [Siswa\ExamController::class, 'confirm'])->name('exams.confirm');
    Route::post('/exams/{schedule}/start', [Siswa\ExamController::class, 'start'])->name('exams.start');
    Route::get('/exams/{schedule}/take', [Siswa\ExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/{schedule}/answer', [Siswa\ExamController::class, 'saveAnswer'])->name('exams.answer');
    Route::post('/exams/{schedule}/violation', [Siswa\ExamController::class, 'recordViolation'])->name('exams.violation');
    Route::post('/exams/{schedule}/submit', [Siswa\ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/results', [Siswa\ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{result}/pdf', [Siswa\ResultController::class, 'pdf'])->name('results.pdf');
    Route::get('/results/{result}', [Siswa\ResultController::class, 'show'])->name('results.show');
});
