<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Support\WordQuestionParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class QuestionController extends Controller
{
    public function index(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);
        $questions = $questionBank->questions()->latest()->paginate(15);

        return view('guru.questions.index', compact('questionBank', 'questions'));
    }

    public function create(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        return view('guru.questions.form', ['questionBank' => $questionBank, 'question' => new Question]);
    }

    public function store(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);
        $data = $this->parse($request);
        $data['question_bank_id'] = $questionBank->id;
        Question::create($data);

        return redirect()->route('guru.question-banks.questions.index', $questionBank)->with('success', 'Soal ditambahkan.');
    }

    public function edit(Question $question)
    {
        $this->authorizeBank($question->questionBank);

        return view('guru.questions.form', ['questionBank' => $question->questionBank, 'question' => $question]);
    }

    public function update(Request $request, Question $question)
    {
        $this->authorizeBank($question->questionBank);
        $question->update($this->parse($request, $question));

        return redirect()->route('guru.question-banks.questions.index', $question->question_bank_id)->with('success', 'Soal diperbarui.');
    }

    public function destroy(Question $question)
    {
        $this->authorizeBank($question->questionBank);
        $bankId = $question->question_bank_id;
        $question->delete();

        return redirect()->route('guru.question-banks.questions.index', $bankId)->with('success', 'Soal dihapus.');
    }

    // ----------------------------------------------------------------
    // Word (.docx) import
    // ----------------------------------------------------------------

    public function importForm(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        return view('guru.questions.import', compact('questionBank'));
    }

    public function import(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $request->validate([
            'file' => ['required', 'file', 'mimes:docx', 'max:5120'],
        ], [], ['file' => 'berkas Word']);

        $parser = new WordQuestionParser;
        $questions = $parser->parse($request->file('file')->getRealPath());

        $created = 0;
        foreach ($questions as $payload) {
            $payload['question_bank_id'] = $questionBank->id;
            Question::create($payload);
            $created++;
        }

        $summary = "$created soal berhasil diimpor.";
        if ($created === 0 && empty($parser->errors)) {
            $summary = 'Tidak ada soal yang terbaca. Pastikan format dokumen sesuai template.';
        }

        return redirect()
            ->route('guru.question-banks.questions.index', $questionBank)
            ->with($created > 0 ? 'success' : 'error', $summary)
            ->with('import_errors', $parser->errors);
    }

    public function importTemplate()
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        $heading = ['bold' => true, 'size' => 13];
        $hint = ['italic' => true, 'color' => '777777', 'size' => 9];

        $section->addText('TEMPLATE IMPORT SOAL — SITARA', ['bold' => true, 'size' => 15]);
        $section->addText('Pisahkan setiap soal dengan satu baris kosong. Tag tipe [..] opsional (otomatis terdeteksi). Baris SKOR / TINGKAT / PEMBAHASAN opsional.', $hint);
        $section->addTextBreak();

        $examples = [
            ['[PG] — Pilihan Ganda', [
                '[PG]', '1. Ibu kota Indonesia adalah?', 'A. Jakarta', 'B. Bandung', 'C. Surabaya', 'D. Medan',
                'JAWABAN: A', 'SKOR: 2', 'TINGKAT: mudah', 'PEMBAHASAN: Jakarta adalah ibu kota Indonesia.',
            ]],
            ['[BS] — Benar / Salah', [
                '[BS]', '2. Matahari terbit dari arah timur.', 'JAWABAN: Benar',
            ]],
            ['[ISIAN] — Isian Singkat (pakai | untuk jawaban alternatif)', [
                '[ISIAN]', '3. Hasil dari 5 x 4 adalah ...', 'JAWABAN: 20 | dua puluh',
            ]],
            ['[JODOH] — Menjodohkan (format "kiri = kanan")', [
                '[JODOH]', '4. Jodohkan negara dengan ibu kotanya.', 'Indonesia = Jakarta', 'Jepang = Tokyo', 'Mesir = Kairo',
            ]],
            ['[ESSAY] — Uraian (tanpa jawaban)', [
                '[ESSAY]', '5. Jelaskan proses terjadinya hujan.', 'SKOR: 10',
            ]],
            ['[UPLOAD] — Upload File (tanpa jawaban)', [
                '[UPLOAD]', '6. Unggah hasil laporan praktikum dalam format PDF.',
            ]],
        ];

        foreach ($examples as [$label, $lines]) {
            $section->addText($label, $heading);
            foreach ($lines as $line) {
                $section->addText($line);
            }
            $section->addTextBreak();
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_soal.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Normalise the request into a persistable question payload.
     */
    private function parse(Request $request, ?Question $question = null): array
    {
        $validated = $request->validate([
            'type' => ['required', 'in:multiple_choice,true_false,matching,short_answer,essay,file_upload'],
            'question_text' => ['required', 'string'],
            'score' => ['required', 'numeric', 'min:0'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'explanation' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
        ]);

        $options = null;
        $correct = null;

        switch ($validated['type']) {
            case 'multiple_choice':
                $texts = array_values(array_filter($request->input('option_text', []), fn ($t) => $t !== null && $t !== ''));
                $options = [];
                foreach ($texts as $i => $t) {
                    $options[] = ['key' => chr(65 + $i), 'text' => $t];
                }
                $correctIndex = (int) $request->input('correct_option', 0);
                $correct = [chr(65 + $correctIndex)];
                break;

            case 'true_false':
                $options = [['key' => 'true', 'text' => 'Benar'], ['key' => 'false', 'text' => 'Salah']];
                $correct = [$request->input('correct_tf', 'true')];
                break;

            case 'short_answer':
                // Accept multiple acceptable answers separated by |
                $correct = array_map('trim', explode('|', (string) $request->input('correct_text', '')));
                break;

            case 'matching':
                $lefts = $request->input('match_left', []);
                $rights = $request->input('match_right', []);
                $pairs = [];
                foreach ($lefts as $i => $l) {
                    if ($l !== null && $l !== '' && isset($rights[$i]) && $rights[$i] !== '') {
                        $pairs[] = ['left' => $l, 'right' => $rights[$i]];
                    }
                }
                $options = $pairs;
                $correct = array_column($pairs, 'right'); // correct order
                break;

            // essay, file_upload: no options/correct answer
        }

        $payload = [
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'score' => $validated['score'],
            'difficulty' => $validated['difficulty'],
            'explanation' => $validated['explanation'] ?? null,
            'options' => $options,
            'correct_answer' => $correct,
        ];

        if ($request->hasFile('image')) {
            if ($question?->image) {
                Storage::disk('public')->delete($question->image);
            }
            $payload['image'] = $request->file('image')->store('questions', 'public');
        }
        if ($request->hasFile('audio')) {
            if ($question?->audio) {
                Storage::disk('public')->delete($question->audio);
            }
            $payload['audio'] = $request->file('audio')->store('questions', 'public');
        }

        return $payload;
    }

    private function authorizeBank(QuestionBank $bank): void
    {
        abort_unless($bank->teacher_id === auth()->user()->teacher?->id, 403);
    }
}
