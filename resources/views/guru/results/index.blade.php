@extends('layouts.app')
@section('title', 'Hasil & Koreksi')

@section('content')
<h1 class="page-title mb-3">Hasil Ujian & Koreksi</h1>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><select name="schedule" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Jadwal</option>
            @foreach($schedules as $s)<option value="{{ $s->id }}" @selected(request('schedule')==$s->id)>{{ $s->exam->title }} — {{ $s->start_time->format('d/m/Y') }}</option>@endforeach
        </select></div>
        @if(request('schedule'))
        <div class="col-md-3"><a href="{{ route('guru.analysis', $schedules->firstWhere('id', request('schedule'))->exam_id) }}" class="btn btn-outline-primary w-100"><i class="bi bi-graph-up me-1"></i>Analisis</a></div>
        @endif
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Siswa</th><th>Ujian</th><th>Dikumpulkan</th><th class="text-center">Nilai</th><th class="text-center">Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($results as $r)
            <tr>
                <td class="fw-semibold">{{ $r->student->name ?? '-' }}</td>
                <td>{{ $r->examSchedule->exam->title ?? '-' }}</td>
                <td><small>{{ $r->submitted_at?->format('d M H:i') ?? '-' }}</small></td>
                <td class="text-center fw-bold">{{ $r->total_score }}</td>
                <td class="text-center">
                    @if($r->status==='graded')<span class="badge bg-soft-success">Selesai</span>
                    @else<span class="badge bg-soft-warning">Perlu Koreksi</span>@endif
                </td>
                <td class="text-end"><a href="{{ route('guru.results.show',$r) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye me-1"></i>Periksa</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada hasil ujian.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $results->links() }}
</div></div>
@endsection
