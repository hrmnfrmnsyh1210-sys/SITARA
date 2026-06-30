@extends('layouts.app')
@section('title', 'Mata Pelajaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Mata Pelajaran</h1>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari mapel..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Kode</th><th>Nama</th><th>Warna</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($subjects as $s)
            <tr>
                <td><span class="badge bg-light text-dark">{{ $s->code ?? '-' }}</span></td>
                <td class="fw-semibold">{{ $s->name }}</td>
                <td><span class="d-inline-block rounded" style="width:24px;height:24px;background:{{ $s->color }}"></span></td>
                <td class="text-end">
                    <a href="{{ route('admin.subjects.edit',$s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.subjects.destroy',$s) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Mata pelajaran ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Belum ada mata pelajaran.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $subjects->links() }}
</div></div>
@endsection
