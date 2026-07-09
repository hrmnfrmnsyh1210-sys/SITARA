<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamPackage;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamLocationTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(bool $requiresLocation): array
    {
        $school = School::create(['name' => 'SMA Test', 'is_active' => true]);

        Subscription::create([
            'school_id' => $school->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
        ]);

        $classroom = Classroom::create(['school_id' => $school->id, 'name' => 'X IPA 1']);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Matematika']);

        $user = User::create([
            'school_id' => $school->id, 'role' => 'siswa', 'name' => 'Budi',
            'email' => 'budi@test.local', 'password' => bcrypt('secret'), 'is_active' => true,
        ]);
        Student::create([
            'user_id' => $user->id, 'school_id' => $school->id,
            'classroom_id' => $classroom->id, 'nis' => '001', 'name' => 'Budi',
        ]);

        $bank = QuestionBank::create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Bank']);
        $question = Question::create([
            'question_bank_id' => $bank->id, 'type' => 'multiple_choice',
            'question_text' => '1+1?', 'options' => ['1', '2'], 'correct_answer' => ['2'], 'score' => 1,
        ]);

        $package = ExamPackage::create(['school_id' => $school->id, 'subject_id' => $subject->id, 'name' => 'Paket']);
        $package->questions()->attach($question->id);

        $exam = Exam::create([
            'school_id' => $school->id, 'exam_package_id' => $package->id, 'subject_id' => $subject->id,
            'title' => 'UTS', 'duration_minutes' => 60, 'status' => 'published',
        ]);

        $schedule = ExamSchedule::create([
            'exam_id' => $exam->id, 'classroom_id' => $classroom->id,
            'start_time' => now()->subMinute(), 'end_time' => now()->addHour(),
            'is_active' => true, 'requires_location' => $requiresLocation,
        ]);

        return [$user, $schedule];
    }

    public function test_start_is_blocked_without_location_when_required(): void
    {
        [$user, $schedule] = $this->scenario(true);

        $response = $this->actingAs($user)->post(route('siswa.exams.start', $schedule));

        $response->assertRedirect(route('siswa.exams.confirm', $schedule));
        $response->assertSessionHas('swal');
        $this->assertDatabaseCount('exam_results', 0);
    }

    public function test_start_is_blocked_with_invalid_coordinates(): void
    {
        [$user, $schedule] = $this->scenario(true);

        $response = $this->actingAs($user)->post(route('siswa.exams.start', $schedule), [
            'latitude' => 999, 'longitude' => 'abc',
        ]);

        $response->assertRedirect(route('siswa.exams.confirm', $schedule));
        $this->assertDatabaseCount('exam_results', 0);
    }

    public function test_start_succeeds_and_records_location(): void
    {
        [$user, $schedule] = $this->scenario(true);

        $response = $this->actingAs($user)->post(route('siswa.exams.start', $schedule), [
            'latitude' => -6.2088, 'longitude' => 106.8456, 'location_accuracy' => 12.7,
        ]);

        $response->assertRedirect(route('siswa.exams.take', $schedule));

        $result = ExamResult::first();
        $this->assertEquals('-6.2088000', $result->latitude);
        $this->assertEquals('106.8456000', $result->longitude);
        $this->assertEquals(13, $result->location_accuracy);
        $this->assertNotNull($result->location_captured_at);
    }

    public function test_start_does_not_require_location_when_toggle_is_off(): void
    {
        [$user, $schedule] = $this->scenario(false);

        $response = $this->actingAs($user)->post(route('siswa.exams.start', $schedule));

        $response->assertRedirect(route('siswa.exams.take', $schedule));
        $this->assertNull(ExamResult::first()->latitude);
    }

    public function test_resuming_an_attempt_that_already_has_location_does_not_ask_again(): void
    {
        [$user, $schedule] = $this->scenario(true);

        $this->actingAs($user)->post(route('siswa.exams.start', $schedule), [
            'latitude' => -6.2088, 'longitude' => 106.8456,
        ]);

        // Kembali ke ujian tanpa mengirim lokasi lagi.
        $response = $this->actingAs($user)->post(route('siswa.exams.start', $schedule));

        $response->assertRedirect(route('siswa.exams.take', $schedule));
        $this->assertEquals(1, ExamResult::count());
    }

    public function test_take_redirects_to_confirm_when_legacy_attempt_lacks_location(): void
    {
        [$user, $schedule] = $this->scenario(false);

        // Attempt dibuat sebelum syarat lokasi diaktifkan.
        $this->actingAs($user)->post(route('siswa.exams.start', $schedule));
        $schedule->update(['requires_location' => true]);

        $response = $this->actingAs($user)->get(route('siswa.exams.take', $schedule));

        $response->assertRedirect(route('siswa.exams.confirm', $schedule));
    }
}
