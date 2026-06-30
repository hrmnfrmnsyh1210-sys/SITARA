@extends('layouts.app')
@section('title', 'Admin Sekolah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Admin Sekolah</h1>
    <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Admin</a>
</div>

<div class="card" data-aos="fade-up">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama / email..."></div>
            <div class="col-md-4"><select name="school_id" class="form-select"><option value="">Semua Sekolah</option>@foreach($schools as $s)<option value="{{ $s->id }}" @selected(request('school_id')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nama</th><th>Email</th><th>Sekolah</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($admins as $a)
                    <tr>
                        <td><div class="d-flex align-items-center gap-2"><img src="{{ $a->avatar_url }}" class="avatar-sm">{{ $a->name }}</div></td>
                        <td>{{ $a->email }}</td>
                        <td>{{ $a->school->name ?? '-' }}</td>
                        <td><span class="badge bg-soft-{{ $a->is_active?'success':'danger' }}">{{ $a->is_active?'Aktif':'Nonaktif' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('superadmin.admins.edit',$a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('superadmin.admins.destroy',$a) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Akun admin ini akan dihapus permanen.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">Belum ada admin.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $admins->links() }}
    </div>
</div>
@endsection
