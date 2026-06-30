@extends('layouts.app')
@section('title', 'Nilai Saya')

@section('content')
<h1 class="page-title mb-3">Nilai & Riwayat Ujian</h1>
<div class="row g-3">
    @forelse($results as $r)
        @php $show = $r->examSchedule->exam->show_result; @endphp
        <div class="col-md-6 col-xl-4" data-aos="fade-up">
            <div class="card h-100"><div class="card-body text-center">
                <span class="badge bg-soft-{{ $r->status==='graded'?'success':'secondary' }} mb-2">{{ $r->status==='graded'?'Dinilai':'Menunggu Koreksi' }}</span>
                <h6 class="fw-bold">{{ $r->examSchedule->exam->title }}</h6>
                <p class="text-muted small">{{ $r->examSchedule->exam->subject->name ?? '' }}</p>
                @if($show && $r->status==='graded')
                    <div class="display-5 fw-bold {{ $r->is_passed?'text-success':'text-danger' }}">{{ $r->total_score }}</div>
                    <span class="badge bg-soft-{{ $r->is_passed?'success':'danger' }}">{{ $r->is_passed?'LULUS':'TIDAK LULUS' }}</span>
                @else
                    <div class="display-6 text-muted">—</div>
                    <small class="text-muted">Nilai belum dipublikasikan</small>
                @endif
                <div class="mt-3"><a href="{{ route('siswa.results.show',$r) }}" class="btn btn-light btn-sm w-100">Detail</a></div>
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">Belum ada riwayat ujian.</div></div></div>
    @endforelse
</div>
<div class="mt-3">{{ $results->links() }}</div>
@endsection
