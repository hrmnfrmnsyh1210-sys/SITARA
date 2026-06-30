@extends('layouts.app')
@section('title', 'Log Aktivitas')

@section('content')
<h1 class="page-title mb-3">Log Aktivitas</h1>
<div class="card" data-aos="fade-up"><div class="card-body">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Deskripsi</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td><small>{{ $log->created_at->format('d M Y H:i') }}</small></td>
                    <td>{{ $log->user->name ?? 'Sistem' }}</td>
                    <td><span class="badge bg-soft-primary">{{ $log->action }}</span></td>
                    <td>{{ $log->description }}</td>
                    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada aktivitas tercatat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div></div>
@endsection
