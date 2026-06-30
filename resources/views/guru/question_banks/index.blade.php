@extends('layouts.app')
@section('title', 'Bank Soal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Bank Soal</h1>
    <a href="{{ route('guru.question-banks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Bank Soal</a>
</div>
<div class="card" data-aos="fade-up"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari bank soal..."></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div></form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Nama</th><th>Mapel</th><th>Jml Soal</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse($banks as $b)
            <tr>
                <td class="fw-semibold"><i class="bi bi-collection me-1 text-primary"></i>{{ $b->name }}</td>
                <td>{{ $b->subject->name ?? '-' }}</td>
                <td><span class="badge bg-soft-info">{{ $b->questions_count }} soal</span></td>
                <td class="text-end">
                    <a href="{{ route('guru.question-banks.questions.index',$b) }}" class="btn btn-sm btn-primary"><i class="bi bi-list-ul me-1"></i>Kelola Soal</a>
                    <a href="{{ route('guru.question-banks.edit',$b) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('guru.question-banks.destroy',$b) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Bank soal beserta seluruh soal di dalamnya akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Belum ada bank soal.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $banks->links() }}
</div></div>
@endsection
