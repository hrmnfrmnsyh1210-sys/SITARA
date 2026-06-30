<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $students = Student::where('school_id', $schoolId)
            ->with(['user', 'classroom'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('nis', 'like', "%$s%"))
            ->when($request->classroom_id, fn ($q, $id) => $q->where('classroom_id', $id))
            ->orderBy('name')->paginate(12)->withQueryString();

        $classrooms = Classroom::where('school_id', $schoolId)->orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classrooms'));
    }

    public function create()
    {
        return view('admin.students.form', $this->formData(new Student));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', Rule::unique('students')->where('school_id', $schoolId)],
            'nisn' => ['nullable', 'string', 'max:30'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'min:6'],
        ]);

        DB::transaction(function () use ($data, $schoolId) {
            $user = User::create([
                'school_id' => $schoolId,
                'role' => User::ROLE_SISWA,
                'name' => $data['name'],
                'username' => $data['nis'],
                'email' => $data['nis'] . '@siswa.sitara.local',
                'password' => Hash::make($data['password']),
            ]);

            Student::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'classroom_id' => $data['classroom_id'] ?? null,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan. Login menggunakan NIS.');
    }

    public function edit(Student $student)
    {
        abort_unless($student->school_id === auth()->user()->school_id, 403);

        return view('admin.students.form', $this->formData($student));
    }

    public function update(Request $request, Student $student)
    {
        abort_unless($student->school_id === auth()->user()->school_id, 403);
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', Rule::unique('students')->where('school_id', $schoolId)->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:30'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'min:6'],
        ]);

        DB::transaction(function () use ($data, $student) {
            $student->user?->update(array_filter([
                'name' => $data['name'],
                'username' => $data['nis'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : null,
            ], fn ($v) => $v !== null));

            unset($data['password']);
            $student->update($data);
        });

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        abort_unless($student->school_id === auth()->user()->school_id, 403);
        $student->user?->delete();
        $student->delete();

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    private function formData(Student $student): array
    {
        return [
            'student' => $student,
            'classrooms' => Classroom::where('school_id', auth()->user()->school_id)->orderBy('name')->get(),
        ];
    }

    // ----------------------------------------------------------------
    // Excel import
    // ----------------------------------------------------------------

    /** Header aliases -> canonical key. */
    private const COLUMN_ALIASES = [
        'nis' => 'nis', 'no_induk' => 'nis',
        'nisn' => 'nisn',
        'nama' => 'name', 'name' => 'name', 'nama_siswa' => 'name', 'nama_lengkap' => 'name',
        'jenis_kelamin' => 'gender', 'jk' => 'gender', 'gender' => 'gender', 'l/p' => 'gender',
        'tempat_lahir' => 'birth_place',
        'tanggal_lahir' => 'birth_date', 'tgl_lahir' => 'birth_date',
        'no_hp' => 'phone', 'hp' => 'phone', 'telepon' => 'phone', 'phone' => 'phone', 'no_telepon' => 'phone',
        'alamat' => 'address', 'address' => 'address',
        'kelas' => 'classroom', 'classroom' => 'classroom', 'rombel' => 'classroom',
    ];

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [], ['file' => 'berkas Excel']);

        $schoolId = auth()->user()->school_id;

        $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet();

        // Map normalised header names -> column letters from row 1.
        $colMax = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $map = [];
        for ($i = 1; $i <= $colMax; $i++) {
            $letter = Coordinate::stringFromColumnIndex($i);
            $raw = strtolower(trim((string) $sheet->getCell($letter . '1')->getValue()));
            $key = self::COLUMN_ALIASES[str_replace([' ', '.'], '_', $raw)] ?? null;
            if ($key && ! isset($map[$key])) {
                $map[$key] = $letter;
            }
        }

        if (! isset($map['nis'], $map['name'], $map['birth_date'])) {
            return back()->with('error', 'Format kolom tidak dikenali. Pastikan ada kolom: nis, nama, dan tanggal_lahir. Gunakan template yang disediakan.');
        }

        // Pre-load classrooms (matched by name, case-insensitive).
        $classrooms = Classroom::where('school_id', $schoolId)->get()
            ->keyBy(fn ($c) => strtolower(trim($c->name)));

        $created = 0;
        $skipped = 0;
        $errors = [];
        $seenNis = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $val = fn (string $key) => isset($map[$key])
                ? trim((string) $sheet->getCell($map[$key] . $row)->getValue())
                : '';

            $nis = $val('nis');
            $name = $val('name');

            // Skip fully empty rows.
            if ($nis === '' && $name === '') {
                continue;
            }

            if ($nis === '' || $name === '') {
                $errors[] = "Baris $row: NIS dan Nama wajib diisi.";
                continue;
            }

            // Parse birth date (needed for the default password).
            $birthDate = $this->parseDate($sheet->getCell($map['birth_date'] . $row));
            if (! $birthDate) {
                $errors[] = "Baris $row: Tanggal lahir kosong / tidak valid (dibutuhkan untuk password default).";
                continue;
            }

            // Duplicate guards.
            if (isset($seenNis[$nis])) {
                $errors[] = "Baris $row: NIS $nis duplikat di dalam file.";
                continue;
            }
            $seenNis[$nis] = true;

            if (Student::where('school_id', $schoolId)->where('nis', $nis)->exists()) {
                $skipped++;
                continue;
            }

            // Resolve optional classroom by name.
            $classroomId = null;
            if ($className = $val('classroom')) {
                $classroom = $classrooms->get(strtolower($className));
                if (! $classroom) {
                    $errors[] = "Baris $row: Kelas \"$className\" tidak ditemukan.";
                    continue;
                }
                $classroomId = $classroom->id;
            }

            try {
                DB::transaction(function () use ($val, $nis, $name, $birthDate, $classroomId, $schoolId) {
                    $user = User::create([
                        'school_id' => $schoolId,
                        'role' => User::ROLE_SISWA,
                        'name' => $name,
                        'username' => $nis,
                        'email' => $nis . '@siswa.sitara.local',
                        // Default password = birth date as ddmmyyyy. Student must change on first login.
                        'password' => Hash::make($birthDate->format('dmY')),
                        'must_change_password' => true,
                    ]);

                    Student::create([
                        'user_id' => $user->id,
                        'school_id' => $schoolId,
                        'classroom_id' => $classroomId,
                        'nis' => $nis,
                        'nisn' => $val('nisn') ?: null,
                        'name' => $name,
                        'gender' => $this->normaliseGender($val('gender')),
                        'birth_place' => $val('birth_place') ?: null,
                        'birth_date' => $birthDate->toDateString(),
                        'phone' => $val('phone') ?: null,
                        'address' => $val('address') ?: null,
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Baris $row: gagal disimpan ({$e->getMessage()}).";
            }
        }

        $summary = "$created siswa berhasil diimpor.";
        if ($skipped > 0) {
            $summary .= " $skipped dilewati (NIS sudah terdaftar).";
        }
        $summary .= ' Password default setiap siswa adalah tanggal lahir (format ddmmyyyy), dan wajib diganti saat login pertama.';

        return back()
            ->with($created > 0 ? 'success' : 'error', $summary)
            ->with('import_errors', $errors);
    }

    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $headers = ['nis', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'no_hp', 'alamat', 'kelas'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray([
            ['2024001', '0098765432', 'Budi Santoso', 'L', 'Bandung', '2008-05-17', '081234567890', 'Jl. Merdeka No. 1', 'X IPA 1'],
            ['2024002', '0098765433', 'Siti Aminah', 'P', 'Surabaya', '2008-11-03', '081298765432', 'Jl. Diponegoro No. 5', 'X IPA 1'],
        ], null, 'A2');

        // Keep the date column as text so dd-mm-yyyy / yyyy-mm-dd values stay intact.
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('@');

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastCol}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Normalise free-text gender values to the L/P enum. */
    private function normaliseGender(string $value): ?string
    {
        $v = strtolower(trim($value));

        return match (true) {
            in_array($v, ['l', 'laki-laki', 'laki', 'pria', 'male', 'm'], true) => 'L',
            in_array($v, ['p', 'perempuan', 'wanita', 'female', 'f'], true) => 'P',
            default => null,
        };
    }

    /** Parse a spreadsheet cell into a Carbon date, accepting Excel serials and common string formats. */
    private function parseDate($cell): ?Carbon
    {
        if (ExcelDate::isDateTime($cell) && is_numeric($cell->getValue())) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $cell->getValue()));
        }

        $raw = trim((string) $cell->getValue());
        if ($raw === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'd-m-y', 'd/m/y', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
            } catch (\Throwable) {
                continue;
            }
            if ($date && $date->format($format) === $raw) {
                return $date->startOfDay();
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
