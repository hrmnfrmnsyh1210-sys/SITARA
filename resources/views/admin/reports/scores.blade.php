@extends('layouts.app')
@section('title', 'Laporan Nilai')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="page-title mb-0">Laporan Nilai Siswa</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.scores.excel', request()->query()) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="{{ route('admin.reports.scores.pdf', request()->query()) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    </div>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-4"><select name="classroom_id" class="form-select"><option value="">Semua Kelas</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" @selected(request('classroom_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><select name="exam_id" class="form-select"><option value="">Semua Ujian</option>@foreach($exams as $e)<option value="{{ $e->id }}" @selected(request('exam_id')==$e->id)>{{ $e->title }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Siswa</th><th>Kelas</th><th>Ujian</th><th>Mapel</th><th class="text-center">Nilai</th><th class="text-center">Status</th></tr></thead>
        <tbody>
        @forelse($results as $r)
            <tr>
                <td class="fw-semibold">{{ $r->student->name ?? '-' }}</td>
                <td>{{ $r->student->classroom->name ?? '-' }}</td>
                <td>{{ $r->examSchedule->exam->title ?? '-' }}</td>
                <td>{{ $r->examSchedule->exam->subject->name ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $r->total_score }}</td>
                <td class="text-center"><span class="badge bg-soft-{{ $r->is_passed?'success':'danger' }}">{{ $r->is_passed?'Lulus':'Tidak Lulus' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data nilai.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $results->links() }}
</div></div>
@endsection
