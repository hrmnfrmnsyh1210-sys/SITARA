<?php

namespace App\Http\Controllers\Concerns;

use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Helpers untuk membuat laporan Excel (.xlsx) dan PDF dengan gaya seragam
 * (kop sekolah + judul + tabel). Dipakai oleh controller laporan di semua peran.
 */
trait ExportsReports
{
    /** Warna aksen header tabel (biru SITARA). */
    protected string $reportAccent = '2563EB';

    /**
     * Buat spreadsheet baru dengan metadata dasar.
     */
    protected function newSpreadsheet(string $sheetTitle): Spreadsheet
    {
        $ss = new Spreadsheet;
        $ss->getProperties()
            ->setCreator('SITARA')
            ->setTitle($sheetTitle);
        // Judul sheet maksimal 31 karakter & tanpa karakter terlarang.
        $ss->getActiveSheet()->setTitle($this->safeSheetTitle($sheetTitle));

        return $ss;
    }

    /**
     * Tulis blok kop laporan (nama sekolah, judul, baris meta) di bagian atas
     * sheet, lalu kembalikan nomor baris tempat tabel data harus dimulai.
     *
     * @param  list<string>  $metaLines  baris keterangan (mis. "Kelas: X IPA 1")
     */
    protected function writeSheetHeading(Worksheet $sheet, ?School $school, string $title, array $metaLines, int $columnCount): int
    {
        $lastCol = Coordinate::stringFromColumnIndex(max(1, $columnCount));
        $row = 1;

        if ($school) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", strtoupper($school->name));
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            $sub = collect([$school->level, $school->address])->filter()->implode(' — ');
            if ($sub !== '') {
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", $sub);
                $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
            $row++; // baris kosong
        }

        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        foreach ($metaLines as $line) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", $line);
            $sheet->getStyle("A{$row}")->getFont()->setSize(9)->getColor()->setRGB('555555');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        return $row + 1; // sisakan satu baris kosong sebelum tabel
    }

    /**
     * Beri gaya baris header tabel (teks putih di atas latar warna aksen).
     */
    protected function styleHeaderRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->reportAccent);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Beri border tipis pada seluruh sel tabel.
     */
    protected function borderRange(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D5DD');
    }

    /**
     * Auto-size kolom A..N berdasarkan jumlah kolom.
     */
    protected function autoSizeColumns(Worksheet $sheet, int $columnCount): void
    {
        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    /**
     * Kirim spreadsheet sebagai unduhan .xlsx.
     */
    protected function xlsxDownload(Spreadsheet $ss, string $filename): StreamedResponse
    {
        $writer = new Xlsx($ss);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $this->safeFilename($filename, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Render sebuah view Blade menjadi PDF dan kirim sebagai unduhan.
     */
    protected function pdfDownload(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($this->safeFilename($filename, 'pdf'));
    }

    /**
     * Path lokal absolut logo sekolah untuk disematkan di PDF (null bila tak ada).
     */
    protected function schoolLogoPath(?School $school): ?string
    {
        if (! $school || ! $school->logo) {
            return null;
        }
        $path = storage_path('app/public/' . $school->logo);

        return is_file($path) ? $path : null;
    }

    private function safeSheetTitle(string $title): string
    {
        $clean = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $title);

        return mb_substr(trim($clean), 0, 31) ?: 'Laporan';
    }

    private function safeFilename(string $filename, string $ext): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', pathinfo($filename, PATHINFO_FILENAME));
        $base = trim($base, '_') ?: 'laporan';

        return "{$base}.{$ext}";
    }
}
