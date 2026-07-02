@extends('layouts.app')
@section('title', 'Data Guru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Data Guru</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.reports.teachers.excel', request()->only('search')) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="{{ route('admin.reports.teachers.pdf', request()->only('search')) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Guru</a>
    </div>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama / NIP..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Guru</th><th>NIP</th><th>JK</th><th>Email</th><th>Telepon</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($teachers as $t)
            <tr>
                <td><div class="d-flex align-items-center gap-2"><img src="{{ $t->photo_url }}" class="avatar-sm">{{ $t->name }}</div></td>
                <td>{{ $t->nip ?? '-' }}</td>
                <td>{{ $t->gender ?? '-' }}</td>
                <td>{{ $t->user->email ?? '-' }}</td>
                <td>{{ $t->phone ?? '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.teachers.edit',$t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.teachers.destroy',$t) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Data guru ini beserta akunnya akan dihapus permanen.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada guru.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $teachers->links() }}
</div></div>
@endsection
