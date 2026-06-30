@extends('layouts.app')
@section('title', 'Langganan Sekolah')

@php
    $fmt = fn ($n) => $currency . ' ' . number_format($n, 0, ',', '.');
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title">Langganan Sekolah</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activateModal">
        <i class="bi bi-plus-lg me-1"></i>Aktifkan Manual
    </button>
</div>

{{-- Pengaturan harga --}}
<div class="card mb-3" data-aos="fade-up">
    <div class="card-body">
        <h2 class="h6 mb-3"><i class="bi bi-tag me-1 text-primary"></i>Harga Langganan</h2>
        <form action="{{ route('superadmin.subscriptions.price') }}" method="POST" class="row g-2 align-items-end">
            @csrf @method('PUT')
            <div class="col-md-5">
                <label class="form-label">Harga per Bulan (per sekolah)</label>
                <div class="input-group">
                    <span class="input-group-text">{{ $currency }}</span>
                    <input type="number" name="monthly_price" value="{{ $price }}" min="0" step="1000" class="form-control" required>
                    <span class="input-group-text">/ bulan</span>
                </div>
                @error('monthly_price')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Simpan Harga</button>
            </div>
            <div class="col-12">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Harga ini dipakai di halaman depan, pengajuan perpanjangan, dan aktivasi manual.</small>
            </div>
        </form>
    </div>
</div>

{{-- Pengajuan menunggu konfirmasi --}}
<div class="card mb-3" data-aos="fade-up">
    <div class="card-body">
        <h2 class="h6 mb-3"><i class="bi bi-hourglass-split me-1 text-warning"></i>Menunggu Konfirmasi
            <span class="badge bg-soft-warning ms-1">{{ $pending->count() }}</span>
        </h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Sekolah</th><th>Diajukan</th><th>Durasi</th><th>Total</th><th>Pembayaran</th><th>Bukti</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($pending as $sub)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $sub->school->name }}</div>
                            <small class="text-muted">oleh {{ $sub->requester->name ?? 'Operator' }}</small>
                        </td>
                        <td><small>{{ $sub->created_at->format('d M Y H:i') }}</small></td>
                        <td>{{ $sub->months }} bln</td>
                        <td>{{ $fmt($sub->price) }}</td>
                        <td><small>{{ $sub->payment_method ?? '-' }}</small>@if($sub->note)<br><small class="text-muted">{{ $sub->note }}</small>@endif</td>
                        <td>
                            @if ($sub->payment_proof)
                                <a href="{{ asset('storage/' . $sub->payment_proof) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-image"></i></a>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('superadmin.subscriptions.approve', $sub) }}" method="POST" class="d-inline" data-confirm="default" data-confirm-text="Aktifkan langganan {{ $sub->school->name }} selama {{ $sub->months }} bulan?">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Setujui</button>
                            </form>
                            <button class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $sub->id }}"><i class="bi bi-x-lg"></i></button>

                            <div class="modal fade" id="rejectModal{{ $sub->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('superadmin.subscriptions.reject', $sub) }}" method="POST" class="modal-content text-start">
                                        @csrf
                                        <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <p class="text-muted small">Tolak pengajuan langganan dari <strong>{{ $sub->school->name }}</strong>.</p>
                                            <label class="form-label">Alasan (opsional)</label>
                                            <textarea name="note" rows="2" class="form-control" placeholder="Mis. bukti transfer tidak valid..."></textarea>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Tolak</button></div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengajuan menunggu konfirmasi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Status langganan tiap sekolah --}}
<div class="card" data-aos="fade-up">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama / NPSN..."></div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-light"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Sekolah</th><th>Status</th><th>Berlaku Sampai</th><th>Langganan Terakhir</th></tr></thead>
                <tbody>
                @forelse($schools as $school)
                    @php
                        $sub = $school->activeSubscription();
                        $last = $school->subscriptions->first();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $school->name }}</div>
                            <small class="text-muted">{{ $school->npsn ?? '-' }}</small>
                        </td>
                        <td>
                            @if ($sub)
                                <span class="badge bg-soft-success">Aktif</span>
                            @else
                                <span class="badge bg-soft-danger">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>{{ $sub ? $sub->ends_at->format('d M Y') : '-' }}</td>
                        <td>
                            @if ($last)
                                <span class="badge bg-soft-{{ $last->statusColor() }}">{{ $last->statusLabel() }}</span>
                                <small class="text-muted">{{ $last->created_at->format('d M Y') }}</small>
                            @else
                                <span class="text-muted">Belum pernah</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-5">Belum ada sekolah.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $schools->links() }}
    </div>
</div>

{{-- Modal aktivasi manual --}}
<div class="modal fade" id="activateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.subscriptions.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Aktifkan Langganan Manual</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="text-muted small">Aktifkan langganan langsung tanpa menunggu pengajuan (mis. pembayaran offline). Jika sekolah masih aktif, durasi ditumpuk dari tanggal berakhir.</p>
                <div class="mb-3">
                    <label class="form-label">Sekolah</label>
                    <select name="school_id" class="form-select" required>
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lama Langganan</label>
                    <select name="months" class="form-select">
                        @foreach ([1, 3, 6, 12] as $m)<option value="{{ $m }}">{{ $m }} bulan ({{ $fmt($price * $m) }})</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-muted">(opsional)</span></label>
                    <input name="payment_method" class="form-control" placeholder="Transfer / Tunai ...">
                </div>
                <div class="mb-0">
                    <label class="form-label">Catatan <span class="text-muted">(opsional)</span></label>
                    <textarea name="note" rows="2" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Aktifkan</button></div>
        </form>
    </div>
</div>
@endsection
