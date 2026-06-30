@extends('layouts.app')
@section('title', 'Pengumuman')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Pengumuman</h1>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Pengumuman</a>
</div>
<div class="row g-3">
    @forelse($announcements as $a)
        <div class="col-md-6" data-aos="fade-up">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="badge bg-soft-primary mb-2">{{ ['all'=>'Semua','teachers'=>'Guru','students'=>'Siswa'][$a->target] }}</span>
                    <span class="badge bg-soft-{{ $a->is_published?'success':'secondary' }}">{{ $a->is_published?'Tayang':'Draft' }}</span>
                </div>
                <h6 class="fw-bold">{{ $a->title }}</h6>
                <p class="text-muted small">{{ Str::limit($a->content, 150) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">{{ $a->created_at->diffForHumans() }}</small>
                    <div>
                        <a href="{{ route('admin.announcements.edit',$a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.announcements.destroy',$a) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Pengumuman ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                    </div>
                </div>
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">Belum ada pengumuman.</div></div></div>
    @endforelse
</div>
<div class="mt-3">{{ $announcements->links() }}</div>
@endsection
