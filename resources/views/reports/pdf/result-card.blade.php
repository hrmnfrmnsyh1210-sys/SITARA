@extends('reports.pdf.layout')

@section('report')
    <table class="meta" style="margin-bottom:14px">
        <tr><td class="k">Nama Siswa</td><td class="s">:</td><td class="fw-bold">{{ $student->name }}</td></tr>
        <tr><td class="k">NIS / NISN</td><td class="s">:</td><td>{{ $student->nis }}{{ $student->nisn ? ' / ' . $student->nisn : '' }}</td></tr>
        <tr><td class="k">Kelas</td><td class="s">:</td><td>{{ $student->classroom->name ?? '-' }}</td></tr>
        <tr><td class="k">Ujian</td><td class="s">:</td><td>{{ $exam->title }}</td></tr>
        <tr><td class="k">Mata Pelajaran</td><td class="s">:</td><td>{{ $exam->subject->name ?? '-' }}</td></tr>
        <tr><td class="k">Dikumpulkan</td><td class="s">:</td><td>{{ $result->submitted_at?->translatedFormat('d F Y, H:i') ?? '-' }}</td></tr>
    </table>

    @php
        $accent = $result->is_passed ? '#059669' : '#dc2626';
        $tint = $result->is_passed ? '#e6fbf6' : '#fdecec';
    @endphp
    <table style="width:100%;border-collapse:collapse;margin-bottom:14px">
        <tr>
            <td style="width:40%;border:1px solid {{ $accent }};border-top:4px solid {{ $accent }};border-radius:10px;background:{{ $tint }};text-align:center;padding:16px 14px">
                <div style="font-size:9px;color:#64748b;letter-spacing:1px;text-transform:uppercase">Nilai Akhir</div>
                <div style="font-size:42px;font-weight:bold;color:{{ $accent }};line-height:1.1">{{ $result->total_score }}</div>
                <div style="font-size:11px;font-weight:bold;letter-spacing:.5px;color:{{ $accent }}">
                    {{ $result->is_passed ? 'LULUS' : 'TIDAK LULUS' }}
                </div>
            </td>
            <td style="width:4%"></td>
            <td style="vertical-align:middle">
                <table class="data" style="margin:0">
                    <tr><th style="text-align:left">Jawaban Benar</th><td class="text-center pass fw-bold">{{ $result->correct_count }}</td></tr>
                    <tr><th style="text-align:left">Jawaban Salah</th><td class="text-center fail fw-bold">{{ $result->wrong_count }}</td></tr>
                    <tr><th style="text-align:left">Tidak Dijawab</th><td class="text-center fw-bold">{{ $result->empty_count }}</td></tr>
                    <tr><th style="text-align:left">KKM / Nilai Lulus</th><td class="text-center">{{ $exam->passing_score }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="font-size:9px;color:#6b7280;text-align:center;margin-top:6px">
        Dokumen ini dihasilkan secara otomatis oleh sistem SITARA sebagai bukti hasil ujian.
    </p>
@endsection

@section('signature')
    {{ now()->translatedFormat('d F Y') }}<br>
    Kepala Sekolah<br>
    <div class="space"></div>
    <span class="name">{{ $school?->principal_name ?? '(...............................)' }}</span>
@endsection
