@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Data Siswa</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.reports.students.excel', request()->only('search', 'classroom_id')) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="{{ route('admin.reports.students.pdf', request()->only('search', 'classroom_id')) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a href="{{ route('admin.students.import') }}" class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Import</a>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Siswa</a>
    </div>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama / NIS..."></div>
        <div class="col-md-4"><select name="classroom_id" class="form-select"><option value="">Semua Kelas</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" @selected(request('classroom_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Siswa</th><th>NIS</th><th>Kelas</th><th>JK</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($students as $s)
            <tr>
                <td><div class="d-flex align-items-center gap-2"><img src="{{ $s->photo_url }}" class="avatar-sm">{{ $s->name }}</div></td>
                <td><span class="badge bg-light text-dark">{{ $s->nis }}</span></td>
                <td>{{ $s->classroom->name ?? '-' }}</td>
                <td>{{ $s->gender ?? '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.students.edit',$s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.students.destroy',$s) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Data siswa ini beserta akunnya akan dihapus permanen.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Belum ada siswa.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $students->links() }}
</div></div>
@endsection
