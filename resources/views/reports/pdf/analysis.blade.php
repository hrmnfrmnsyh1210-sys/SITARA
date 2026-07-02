@extends('reports.pdf.layout')

@section('report')
    <table class="summary">
        <tr>
            <td><div class="val">{{ $participants }}</div><div class="lbl">Peserta</div></td>
            <td><div class="val">{{ $avg }}</div><div class="lbl">Rata-rata</div></td>
            <td><div class="val">{{ $passed }}</div><div class="lbl">Lulus</div></td>
            <td><div class="val">{{ $highest }}</div><div class="lbl">Tertinggi</div></td>
            <td><div class="val">{{ $lowest }}</div><div class="lbl">Terendah</div></td>
        </tr>
    </table>

    <h3 style="font-size:11px;margin:14px 0 4px">Peringkat Siswa</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:60px">Peringkat</th>
                <th style="text-align:left">Nama Siswa</th>
                <th>Nilai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r->student->name ?? '-' }}</td>
                    <td class="text-center fw-bold {{ $r->is_passed ? 'pass' : 'fail' }}">{{ $r->total_score }}</td>
                    <td class="text-center">{{ $r->is_passed ? 'Lulus' : 'Tidak Lulus' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center muted" style="padding:18px">Belum ada peserta.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="font-size:11px;margin:16px 0 4px">Analisis Butir Soal</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th style="text-align:left">Butir Soal</th>
                <th>Tipe</th>
                <th>% Benar</th>
                <th>Tingkat Kesukaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemStats as $i => $stat)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $stat['text'] }}</td>
                    <td class="text-center">{{ $stat['type'] }}</td>
                    <td class="text-center">{{ $stat['correct_pct'] }}%</td>
                    <td class="text-center">{{ $stat['level'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center muted" style="padding:18px">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('signature')
    {{ now()->translatedFormat('d F Y') }}<br>
    Guru Mata Pelajaran<br>
    <div class="space"></div>
    <span class="name">{{ $exam->teacher->name ?? auth()->user()->name }}</span><br>
    <span class="muted">{{ $exam->teacher?->nip ? 'NIP. ' . $exam->teacher->nip : '' }}</span>
@endsection
