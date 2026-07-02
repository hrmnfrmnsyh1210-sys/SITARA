@extends('reports.pdf.layout')

@php
    $passed = $results->where('is_passed', true)->count();
    $avg = $results->count() ? round($results->avg('total_score'), 1) : 0;
@endphp

@section('report')
    @if($results->isNotEmpty())
        <table class="summary">
            <tr>
                <td><div class="val">{{ $results->count() }}</div><div class="lbl">Peserta</div></td>
                <td><div class="val">{{ $avg }}</div><div class="lbl">Rata-rata</div></td>
                <td><div class="val">{{ $results->max('total_score') }}</div><div class="lbl">Tertinggi</div></td>
                <td><div class="val">{{ $results->min('total_score') }}</div><div class="lbl">Terendah</div></td>
                <td><div class="val">{{ $passed }}</div><div class="lbl">Lulus</div></td>
            </tr>
        </table>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>NIS</th>
                <th style="text-align:left">Nama Siswa</th>
                <th style="text-align:left">Ujian</th>
                <th>Dikumpulkan</th>
                <th>Nilai</th>
                <th>B</th>
                <th>S</th>
                <th>K</th>
                <th>Pelanggaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $r->student->nis ?? '-' }}</td>
                    <td>{{ $r->student->name ?? '-' }}</td>
                    <td>{{ $r->examSchedule->exam->title ?? '-' }}</td>
                    <td class="text-center">{{ $r->submitted_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    <td class="text-center fw-bold {{ $r->is_passed ? 'pass' : 'fail' }}">{{ $r->total_score }}</td>
                    <td class="text-center">{{ $r->correct_count }}</td>
                    <td class="text-center">{{ $r->wrong_count }}</td>
                    <td class="text-center">{{ $r->empty_count }}</td>
                    <td class="text-center">{{ ($r->violation_count ?? 0) > 0 ? $r->violation_count . '×' : '—' }}</td>
                    <td class="text-center">{{ $r->status === 'graded' ? 'Selesai' : 'Perlu Koreksi' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center muted" style="padding:18px">Belum ada hasil ujian.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('signature')
    {{ now()->translatedFormat('d F Y') }}<br>
    Guru Mata Pelajaran<br>
    <div class="space"></div>
    <span class="name">{{ $schedule?->exam?->teacher?->name ?? auth()->user()->name }}</span><br>
    <span class="muted">{{ $schedule?->exam?->teacher?->nip ? 'NIP. ' . $schedule->exam->teacher->nip : '' }}</span>
@endsection
