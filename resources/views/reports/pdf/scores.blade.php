@extends('reports.pdf.layout')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>NIS</th>
                <th style="text-align:left">Nama Siswa</th>
                <th>Kelas</th>
                <th style="text-align:left">Ujian</th>
                <th style="text-align:left">Mata Pelajaran</th>
                <th>Nilai</th>
                <th>B</th>
                <th>S</th>
                <th>K</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $r->student->nis ?? '-' }}</td>
                    <td>{{ $r->student->name ?? '-' }}</td>
                    <td class="text-center">{{ $r->student->classroom->name ?? '-' }}</td>
                    <td>{{ $r->examSchedule->exam->title ?? '-' }}</td>
                    <td>{{ $r->examSchedule->exam->subject->name ?? '-' }}</td>
                    <td class="text-center fw-bold">{{ $r->total_score }}</td>
                    <td class="text-center">{{ $r->correct_count }}</td>
                    <td class="text-center">{{ $r->wrong_count }}</td>
                    <td class="text-center">{{ $r->empty_count }}</td>
                    <td class="text-center {{ $r->is_passed ? 'pass' : 'fail' }}">{{ $r->is_passed ? 'Lulus' : 'Tidak Lulus' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center muted" style="padding:18px">Belum ada data nilai.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($results->isNotEmpty())
        <p style="font-size:9px;margin-top:8px" class="muted">
            Keterangan: B = jumlah benar, S = jumlah salah, K = kosong. Total {{ $results->count() }} data ·
            Lulus {{ $results->where('is_passed', true)->count() }} · Tidak Lulus {{ $results->where('is_passed', false)->count() }}.
        </p>
    @endif
@endsection

@section('signature')
    {{ $school?->address ? \Illuminate\Support\Str::before($school->address, ',') : '' }}, {{ now()->translatedFormat('d F Y') }}<br>
    Kepala Sekolah<br>
    <div class="space"></div>
    <span class="name">{{ $school?->principal_name ?? '(...............................)' }}</span>
@endsection
