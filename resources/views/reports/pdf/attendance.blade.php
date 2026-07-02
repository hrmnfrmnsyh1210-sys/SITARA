@extends('reports.pdf.layout')

@php
    $present = 0;
    foreach ($students as $s) {
        if ($resultsByStudent->get($s->id)?->started_at) { $present++; }
    }
    $total = $students->count();
@endphp

@section('report')
    <p style="font-size:10px;margin:0 0 10px;text-align:justify">
        Pada hari ini telah dilaksanakan ujian <strong>{{ $schedule->exam->title ?? '-' }}</strong>
        untuk kelas <strong>{{ $schedule->classroom->name ?? '-' }}</strong>
        dengan jumlah peserta terdaftar <strong>{{ $total }}</strong> siswa,
        hadir <strong>{{ $present }}</strong> siswa dan tidak hadir <strong>{{ $total - $present }}</strong> siswa.
    </p>

    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>NIS</th>
                <th style="text-align:left">Nama Siswa</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Kehadiran</th>
                <th style="width:110px">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $i => $s)
                @php $res = $resultsByStudent->get($s->id); $hadir = (bool) $res?->started_at; @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $s->nis }}</td>
                    <td>{{ $s->name }}</td>
                    <td class="text-center">{{ $res?->started_at?->format('H:i') ?? '-' }}</td>
                    <td class="text-center">{{ $res?->submitted_at?->format('H:i') ?? '-' }}</td>
                    <td class="text-center {{ $hadir ? 'pass' : 'fail' }}">{{ $hadir ? 'Hadir' : 'Tidak Hadir' }}</td>
                    <td class="text-center">{{ $i % 2 === 0 ? $i + 1 . '.' : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center muted" style="padding:18px">Belum ada siswa pada kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width:100%;margin-top:30px;font-size:10px" class="sign-block">
        <tr>
            <td style="width:50%;text-align:center;vertical-align:top">
                Mengetahui,<br>Kepala Sekolah
                <div style="height:58px"></div>
                <span class="name">{{ $school?->principal_name ?? '(...............................)' }}</span>
            </td>
            <td style="width:50%;text-align:center;vertical-align:top">
                {{ now()->translatedFormat('d F Y') }}<br>Pengawas / Guru
                <div style="height:58px"></div>
                <span class="name">{{ $schedule->exam->teacher->name ?? auth()->user()->name }}</span><br>
                <span class="muted">{{ $schedule->exam->teacher?->nip ? 'NIP. ' . $schedule->exam->teacher->nip : '' }}</span>
            </td>
        </tr>
    </table>
@endsection
