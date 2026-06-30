@extends('layouts.app')
@section('title', 'Jadwal Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Jadwal Ujian</h1>
    <a href="{{ route('guru.schedules.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Jadwal</a>
</div>
<div class="row g-3">
    @forelse($schedules as $s)
        <div class="col-md-6 col-xl-4" data-aos="fade-up">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-soft-{{ $s->isOngoing()?'success':($s->isUpcoming()?'info':'secondary') }}">{{ $s->statusLabel() }}</span>
                    <span class="badge bg-light text-dark"><i class="bi bi-key me-1"></i>{{ $s->token }}</span>
                </div>
                <h6 class="fw-bold mb-1">{{ $s->exam->title }}</h6>
                <p class="text-muted small mb-2">{{ $s->exam->subject->name ?? '' }} · {{ $s->classroom->name ?? '' }}</p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="bi bi-clock me-1"></i>{{ $s->start_time->format('d M Y H:i') }} — {{ $s->end_time->format('H:i') }}</li>
                    <li><i class="bi bi-building me-1"></i>{{ $s->room->name ?? 'Tanpa ruang' }}</li>
                    <li><i class="bi bi-people me-1"></i>{{ $s->results_count }} peserta mengerjakan</li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.results.index', ['schedule'=>$s->id]) }}" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-clipboard-check me-1"></i>Hasil</a>
                    <a href="{{ route('guru.schedules.edit',$s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('guru.schedules.destroy',$s) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Jadwal ujian ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">Belum ada jadwal ujian.</div></div></div>
    @endforelse
</div>
<div class="mt-3">{{ $schedules->links() }}</div>
@endsection
