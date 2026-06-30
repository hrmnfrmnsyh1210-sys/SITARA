@extends('layouts.app')
@section('title', 'Jurusan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Jurusan</h1>
    <a href="{{ route('admin.majors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari jurusan..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Kode</th><th>Nama</th><th>Jml Kelas</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($majors as $m)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $m->code ?? '-' }}</span></td>
                <td class="fw-semibold">{{ $m->name }}</td>
                <td>{{ $m->classrooms_count }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.majors.edit',$m) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.majors.destroy',$m) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Jurusan ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Belum ada jurusan.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $majors->links() }}
</div></div>
@endsection
