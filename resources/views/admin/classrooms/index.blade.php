@extends('layouts.app')
@section('title', 'Kelas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Kelas</h1>
    <a href="{{ route('admin.classrooms.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kelas..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Wali Kelas</th><th>Siswa</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($classrooms as $c)
            <tr>
                <td class="fw-semibold"><i class="bi bi-door-open me-1 text-primary"></i>{{ $c->name }}</td>
                <td>{{ $c->grade_level ?? '-' }}</td>
                <td>{{ $c->major->name ?? '-' }}</td>
                <td>{{ $c->homeroomTeacher->name ?? '-' }}</td>
                <td>{{ $c->students_count }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.classrooms.edit',$c) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.classrooms.destroy',$c) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Kelas ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada kelas.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $classrooms->links() }}
</div></div>
@endsection
