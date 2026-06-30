<?php

namespace App\Support;

use ZipArchive;

/**
 * Parses a .docx file into question payloads ready for Question::create().
 *
 * Document format — one question per block, blocks separated by a blank line:
 *
 *   [PG]                       <- optional type tag (PG/BS/ISIAN/ESSAY/UPLOAD/JODOH)
 *   1. Ibu kota Indonesia?     <- question text (leading number optional)
 *   A. Jakarta
 *   B. Bandung
 *   C. Surabaya
 *   JAWABAN: A
 *   SKOR: 2                    <- optional (default 1)
 *   TINGKAT: mudah             <- optional (mudah/sedang/sulit, default sedang)
 *   PEMBAHASAN: ...            <- optional
 *
 * Type-specific answer lines:
 *   [BS]     -> JAWABAN: Benar | Salah
 *   [ISIAN]  -> JAWABAN: jakarta | dki jakarta   (pipe = jawaban alternatif)
 *   [JODOH]  -> baris pasangan "kiri = kanan" (atau tabel 2 kolom)
 *   [ESSAY]/[UPLOAD] -> tanpa jawaban
 */
class WordQuestionParser
{
    private const TYPE_TAGS = [
        'pg' => 'multiple_choice', 'pilihan ganda' => 'multiple_choice',
        'bs' => 'true_false', 'benar salah' => 'true_false', 'benar/salah' => 'true_false',
        'isian' => 'short_answer', 'isian singkat' => 'short_answer',
        'essay' => 'essay', 'esai' => 'essay', 'uraian' => 'essay',
        'upload' => 'file_upload', 'unggah' => 'file_upload',
        'jodoh' => 'matching', 'menjodohkan' => 'matching',
    ];

    private const DIFFICULTY = [
        'mudah' => 'easy', 'easy' => 'easy', 'rendah' => 'easy',
        'sedang' => 'medium', 'medium' => 'medium',
        'sulit' => 'hard', 'susah' => 'hard', 'hard' => 'hard', 'tinggi' => 'hard',
    ];

    /** @var list<string> */
    public array $errors = [];

    /**
     * @return list<array<string,mixed>> Question payloads (without question_bank_id).
     */
    public function parse(string $path): array
    {
        $blocks = $this->splitIntoBlocks($this->extractParagraphs($path));

        $questions = [];
        foreach ($blocks as $i => $lines) {
            $payload = $this->parseBlock($lines, $i + 1);
            if ($payload) {
                $questions[] = $payload;
            }
        }

        return $questions;
    }

    /**
     * Extract the document into a flat list of paragraph strings (blank paragraphs kept).
     * Reads word/document.xml directly from the .docx zip — robust against PhpWord reader quirks.
     *
     * @return list<string>
     */
    private function extractParagraphs(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->errors[] = 'Berkas tidak dapat dibuka (bukan dokumen .docx yang valid).';

            return [];
        }
        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return [];
        }

        $paragraphs = [];
        // Each paragraph is one line. Match both empty self-closing <w:p/> (blank lines)
        // and full <w:p>...</w:p> paragraphs.
        if (preg_match_all('/<w:p\b[^>]*\/>|<w:p\b[^>]*>.*?<\/w:p>/s', $xml, $matches)) {
            foreach ($matches[0] as $paragraphXml) {
                // Line breaks / tabs inside a paragraph become spaces.
                $paragraphXml = preg_replace('/<w:(?:br|tab)\b[^>]*\/?>/', ' ', $paragraphXml);
                // Collect every <w:t> run.
                preg_match_all('/<w:t\b[^>]*>(.*?)<\/w:t>/s', $paragraphXml, $runs);
                $text = html_entity_decode(implode('', $runs[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
                $paragraphs[] = trim($text);
            }
        }

        return $paragraphs;
    }

    /**
     * Group paragraphs into blocks separated by one or more blank lines.
     *
     * @param  list<string>  $paragraphs
     * @return list<list<string>>
     */
    private function splitIntoBlocks(array $paragraphs): array
    {
        $blocks = [];
        $current = [];

        foreach ($paragraphs as $line) {
            if (trim($line) === '') {
                if ($current) {
                    $blocks[] = $current;
                    $current = [];
                }

                continue;
            }
            $current[] = trim($line);
        }

        if ($current) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    /**
     * @param  list<string>  $lines
     * @return array<string,mixed>|null
     */
    private function parseBlock(array $lines, int $number): ?array
    {
        $type = null;

        // Leading [TAG]?
        if (preg_match('/^\[(.+?)\]\s*(.*)$/u', $lines[0], $m)) {
            $type = self::TYPE_TAGS[strtolower(trim($m[1]))] ?? null;
            $lines[0] = trim($m[2]);
            if ($lines[0] === '') {
                array_shift($lines);
            }
        }

        $questionLines = [];
        $options = [];      // A. .. style
        $pairs = [];        // jodoh
        $answer = null;
        $score = null;
        $difficulty = null;
        $explanation = null;

        foreach ($lines as $line) {
            if (preg_match('/^([A-Ea-e])[.)]\s*(.+)$/u', $line, $m)) {
                $options[strtoupper($m[1])] = trim($m[2]);
            } elseif (preg_match('/^(JAWABAN|KUNCI|ANSWER)\s*[:.]?\s*(.*)$/ui', $line, $m)) {
                $answer = trim($m[2]);
            } elseif (preg_match('/^(SKOR|POIN|SCORE|NILAI)\s*[:.]?\s*(.*)$/ui', $line, $m)) {
                $score = is_numeric(trim($m[2])) ? (float) trim($m[2]) : null;
            } elseif (preg_match('/^(TINGKAT|KESULITAN|LEVEL)\s*[:.]?\s*(.*)$/ui', $line, $m)) {
                $difficulty = self::DIFFICULTY[strtolower(trim($m[2]))] ?? null;
            } elseif (preg_match('/^(PEMBAHASAN|PENJELASAN|EXPLANATION)\s*[:.]?\s*(.*)$/ui', $line, $m)) {
                $explanation = trim($m[2]) ?: null;
            } elseif ($type === 'matching' && preg_match('/^(.+?)\s*=\s*(.+)$/u', $line, $m)) {
                $pairs[] = ['left' => trim($m[1]), 'right' => trim($m[2])];
            } else {
                $questionLines[] = $line;
            }
        }

        // Strip a leading "1." / "1)" numbering from the first question line.
        if ($questionLines) {
            $questionLines[0] = preg_replace('/^\s*\d+[.)]\s*/u', '', $questionLines[0]);
        }
        $questionText = trim(implode("\n", $questionLines));

        if ($questionText === '') {
            $this->errors[] = "Soal #$number dilewati: teks pertanyaan kosong.";

            return null;
        }

        // Infer type when no tag was given.
        $type ??= $this->inferType($options, $answer, $pairs);

        $payload = [
            'type' => $type,
            'question_text' => $questionText,
            'score' => $score ?? 1,
            'difficulty' => $difficulty ?? 'medium',
            'explanation' => $explanation,
            'options' => null,
            'correct_answer' => null,
        ];

        return match ($type) {
            'multiple_choice' => $this->buildMultipleChoice($payload, $options, $answer, $number),
            'true_false' => $this->buildTrueFalse($payload, $answer, $number),
            'short_answer' => $this->buildShortAnswer($payload, $answer, $number),
            'matching' => $this->buildMatching($payload, $pairs, $number),
            'essay', 'file_upload' => $payload,
            default => $this->fail("Soal #$number dilewati: tipe soal tidak dikenali."),
        };
    }

    /** @param array<string,string> $options */
    private function inferType(array $options, ?string $answer, array $pairs): string
    {
        if ($pairs) {
            return 'matching';
        }
        if ($options) {
            return 'multiple_choice';
        }
        if ($answer !== null && $answer !== '') {
            $a = strtolower($answer);
            if (in_array($a, ['benar', 'salah', 'b', 's', 'true', 'false'], true)) {
                return 'true_false';
            }

            return 'short_answer';
        }

        return 'essay';
    }

    private function buildMultipleChoice(array $payload, array $options, ?string $answer, int $number): ?array
    {
        if (count($options) < 2) {
            return $this->fail("Soal #$number dilewati: pilihan ganda butuh minimal 2 opsi (A, B, ...).");
        }
        if (! $answer) {
            return $this->fail("Soal #$number dilewati: tidak ada baris JAWABAN.");
        }

        ksort($options);
        $payload['options'] = [];
        foreach ($options as $key => $text) {
            $payload['options'][] = ['key' => $key, 'text' => $text];
        }

        // Answer may list several letters (e.g. "A, C") for multi-answer.
        $letters = preg_split('/[\s,;]+/', strtoupper(trim($answer)), -1, PREG_SPLIT_NO_EMPTY);
        $letters = array_values(array_filter($letters, fn ($l) => isset($options[$l])));

        if (! $letters) {
            return $this->fail("Soal #$number dilewati: JAWABAN \"$answer\" tidak cocok dengan opsi manapun.");
        }
        $payload['correct_answer'] = $letters;

        return $payload;
    }

    private function buildTrueFalse(array $payload, ?string $answer, int $number): ?array
    {
        $a = strtolower(trim((string) $answer));
        $value = match (true) {
            in_array($a, ['benar', 'b', 'true', 't', 'ya'], true) => 'true',
            in_array($a, ['salah', 's', 'false', 'f', 'tidak'], true) => 'false',
            default => null,
        };

        if ($value === null) {
            return $this->fail("Soal #$number dilewati: JAWABAN benar/salah tidak valid (gunakan Benar atau Salah).");
        }

        $payload['options'] = [['key' => 'true', 'text' => 'Benar'], ['key' => 'false', 'text' => 'Salah']];
        $payload['correct_answer'] = [$value];

        return $payload;
    }

    private function buildShortAnswer(array $payload, ?string $answer, int $number): ?array
    {
        if (! $answer) {
            return $this->fail("Soal #$number dilewati: isian singkat butuh baris JAWABAN.");
        }
        $payload['correct_answer'] = array_values(array_filter(array_map('trim', explode('|', $answer)), fn ($a) => $a !== ''));

        return $payload;
    }

    private function buildMatching(array $payload, array $pairs, int $number): ?array
    {
        if (count($pairs) < 2) {
            return $this->fail("Soal #$number dilewati: menjodohkan butuh minimal 2 pasangan (format \"kiri = kanan\").");
        }
        $payload['options'] = $pairs;
        $payload['correct_answer'] = array_column($pairs, 'right');

        return $payload;
    }

    private function fail(string $message): null
    {
        $this->errors[] = $message;

        return null;
    }
}
