@extends('reports.pdf.layout')

@section('report')
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th>NIP</th>
                <th style="text-align:left">Nama</th>
                <th>JK</th>
                <th style="text-align:left">Email</th>
                <th>No. HP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teachers as $i => $t)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $t->nip ?? '-' }}</td>
                    <td>{{ $t->name }}</td>
                    <td class="text-center">{{ $t->gender ?? '-' }}</td>
                    <td>{{ $t->user->email ?? '-' }}</td>
                    <td class="text-center">{{ $t->phone ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center muted" style="padding:18px">Belum ada data guru.</td></tr>
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
