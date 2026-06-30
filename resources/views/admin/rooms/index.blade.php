@extends('layouts.app')
@section('title', 'Ruang Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Ruang Ujian</h1>
    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari ruangan..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Nama</th><th>Kapasitas</th><th>Lokasi</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($rooms as $r)
            <tr>
                <td class="fw-semibold"><i class="bi bi-building me-1 text-primary"></i>{{ $r->name }}</td>
                <td>{{ $r->capacity }} orang</td>
                <td>{{ $r->location ?? '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.rooms.edit',$r) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.rooms.destroy',$r) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Ruangan ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Belum ada ruangan.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $rooms->links() }}
</div></div>
@endsection
