@extends('layouts.app')
@section('title', 'Tahun Ajaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Tahun Ajaran & Semester</h1>
    <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Tahun Ajaran</th><th>Periode</th><th>Semester</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($years as $y)
            <tr>
                <td class="fw-semibold"><i class="bi bi-calendar3 me-1 text-primary"></i>{{ $y->name }}</td>
                <td><small class="text-muted">{{ $y->start_date?->format('d M Y') ?? '-' }} — {{ $y->end_date?->format('d M Y') ?? '-' }}</small></td>
                <td>@foreach($y->semesters as $sem)<span class="badge bg-soft-{{ $sem->is_active?'success':'secondary' }} me-1">{{ $sem->name }}</span>@endforeach</td>
                <td><span class="badge bg-soft-{{ $y->is_active?'success':'secondary' }}">{{ $y->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td class="text-end">
                    <a href="{{ route('admin.academic-years.edit',$y) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.academic-years.destroy',$y) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Tahun ajaran ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Belum ada tahun ajaran.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $years->links() }}
</div></div>
@endsection
