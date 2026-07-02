@extends('reports.pdf.layout')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>NIS</th>
                <th>NISN</th>
                <th style="text-align:left">Nama</th>
                <th>JK</th>
                <th style="text-align:left">Tempat, Tgl Lahir</th>
                <th>Kelas</th>
                <th>No. HP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $i => $s)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $s->nis }}</td>
                    <td class="text-center">{{ $s->nisn ?? '-' }}</td>
                    <td>{{ $s->name }}</td>
                    <td class="text-center">{{ $s->gender ?? '-' }}</td>
                    <td>{{ $s->birth_place ?? '-' }}{{ $s->birth_date ? ', ' . $s->birth_date->format('d-m-Y') : '' }}</td>
                    <td class="text-center">{{ $s->classroom->name ?? '-' }}</td>
                    <td class="text-center">{{ $s->phone ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center muted" style="padding:18px">Belum ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('signature')
    {{ $school?->address ? \Illuminate\Support\Str::before($school->address, ',') : '' }}, {{ now()->translatedFormat('d F Y') }}<br>
    Kepala Sekolah<br>
    <div class="space"></div>
    <span class="name">{{ $school?->principal_name ?? '(...............................)' }}</span>
@endsection
