@extends('layouts.app')
@section('title', 'Manajemen Sekolah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Sekolah</h1>
    <a href="{{ route('superadmin.schools.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Sekolah</a>
</div>

<div class="card" data-aos="fade-up">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama / NPSN..."></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" @selected(request('status')==='1')>Aktif</option>
                    <option value="0" @selected(request('status')==='0')>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('superadmin.schools.index') }}" class="btn btn-light"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Sekolah</th><th>NPSN</th><th>Guru</th><th>Siswa</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($school->logo)<img src="{{ $school->logo_url }}" class="avatar-sm">@else<span class="stat-icon bg-soft-primary" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-building"></i></span>@endif
                                <div><div class="fw-semibold">{{ $school->name }}</div><small class="text-muted">{{ $school->level ?? '-' }}</small></div>
                            </div>
                        </td>
                        <td>{{ $school->npsn ?? '-' }}</td>
                        <td>{{ $school->teachers_count }}</td>
                        <td>{{ $school->students_count }}</td>
                        <td>
                            <span class="badge bg-soft-{{ $school->is_active ? 'success':'danger' }}">{{ $school->is_active ? 'Aktif':'Nonaktif' }}</span>
                            @if($school->isFrozen())<span class="badge bg-soft-info" title="Sisa masa langganan dibekukan"><i class="bi bi-snow me-1"></i>Beku</span>@endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('superadmin.schools.edit', $school) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('superadmin.schools.destroy', $school) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Semua data terkait sekolah ini akan ikut terhapus dan tidak dapat dikembalikan.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada sekolah.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $schools->links() }}
    </div>
</div>
@endsection
