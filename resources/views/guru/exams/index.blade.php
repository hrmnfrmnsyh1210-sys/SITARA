@extends('layouts.app')
@section('title', 'Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Ujian</h1>
    <a href="{{ route('guru.exams.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Ujian</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari ujian..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Judul</th><th>Mapel</th><th>Durasi</th><th>KKM</th><th>Jadwal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($exams as $e)
            <tr>
                <td class="fw-semibold">{{ $e->title }}</td>
                <td>{{ $e->subject->name ?? '-' }}</td>
                <td>{{ $e->duration_minutes }} mnt</td>
                <td>{{ $e->passing_score }}</td>
                <td><span class="badge bg-soft-info">{{ $e->schedules_count }}</span></td>
                <td><span class="badge bg-soft-{{ ['draft'=>'secondary','published'=>'success','closed'=>'danger'][$e->status] }}">{{ ucfirst($e->status) }}</span></td>
                <td class="text-end">
                    <a href="{{ route('guru.schedules.create', ['exam_id'=>$e->id]) }}" class="btn btn-sm btn-light" title="Jadwalkan"><i class="bi bi-calendar-plus"></i></a>
                    <a href="{{ route('guru.exams.edit',$e) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('guru.exams.destroy',$e) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Ujian ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Belum ada ujian.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $exams->links() }}
</div></div>
@endsection
