@extends('layouts.app')
@section('title', 'Paket Soal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Paket Soal</h1>
    <a href="{{ route('guru.packages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Paket</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari paket..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Nama Paket</th><th>Mapel</th><th>Jml Soal</th><th>Acak</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($packages as $p)
            <tr>
                <td class="fw-semibold"><i class="bi bi-box-seam me-1 text-primary"></i>{{ $p->name }}</td>
                <td>{{ $p->subject->name ?? '-' }}</td>
                <td><span class="badge bg-soft-info">{{ $p->questions_count }} soal</span></td>
                <td>@if($p->randomize_questions)<span class="badge bg-soft-success">Soal</span>@endif @if($p->randomize_options)<span class="badge bg-soft-success">Opsi</span>@endif</td>
                <td class="text-end">
                    <a href="{{ route('guru.packages.edit',$p) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('guru.packages.destroy',$p) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Paket soal ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">Belum ada paket soal.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $packages->links() }}
</div></div>
@endsection
