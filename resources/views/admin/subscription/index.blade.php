@extends('layouts.app')
@section('title', 'Langganan')

@php
    $fmt = fn ($n) => $currency . ' ' . number_format($n, 0, ',', '.');
    $covering = $active !== null;
    $pending = $school?->hasPendingSubscription();
    $endsAt = $active?->ends_at;
    $daysLeft = $endsAt ? now()->startOfDay()->diffInDays($endsAt, false) : null;
@endphp

@section('content')
<h1 class="page-title mb-3">Langganan Sekolah</h1>

<div class="row g-3">
    {{-- Status --}}
    <div class="col-lg-5">
        <div class="card h-100" data-aos="fade-up">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="stat-icon bg-soft-{{ $covering ? 'success' : 'danger' }}" style="width:52px;height:52px;font-size:1.4rem">
                        <i class="bi bi-{{ $covering ? 'patch-check' : 'exclamation-octagon' }}"></i>
                    </span>
                    <div>
                        <div class="text-muted small">Status Langganan</div>
                        <div class="h5 mb-0">{{ $covering ? 'Aktif' : 'Tidak Aktif' }}</div>
                    </div>
                </div>

                @if ($covering)
                    <p class="mb-1">Berlaku sampai <strong>{{ $endsAt->format('d F Y') }}</strong>.</p>
                    @if ($daysLeft !== null && $daysLeft <= config('sitara.subscription.warning_days'))
                        <div class="alert alert-warning py-2 px-3 small mb-0">
                            <i class="bi bi-clock-history me-1"></i>Sisa {{ (int) $daysLeft }} hari lagi. Segera perpanjang agar tidak terputus.
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">Sekolah belum memiliki langganan aktif. Guru dan siswa belum bisa mengakses fitur ujian sampai langganan diaktifkan.</p>
                @endif

                @if ($pending)
                    <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                        <i class="bi bi-hourglass-split me-1"></i>Pengajuan langganan Anda sedang menunggu konfirmasi operator.
                    </div>
                @endif

                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Harga per bulan</span>
                    <strong>{{ $fmt($price) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Ajukan perpanjangan --}}
    <div class="col-lg-7">
        <div class="card h-100" data-aos="fade-up">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-arrow-repeat me-1"></i>Ajukan Perpanjangan</h2>

                @if ($pending)
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                        Sudah ada pengajuan yang menunggu konfirmasi. Anda bisa mengajukan lagi setelah pengajuan ini diproses.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.subscription.store') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Lama Langganan</label>
                            <select name="months" id="months" class="form-select" data-price="{{ $price }}">
                                @foreach ([1, 3, 6, 12] as $m)
                                    <option value="{{ $m }}">{{ $m }} bulan</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Tagihan</label>
                            <input type="text" id="total" class="form-control fw-semibold" value="{{ $fmt($price) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metode Pembayaran</label>
                            <input name="payment_method" class="form-control" placeholder="Transfer BCA / Mandiri ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bukti Transfer <span class="text-muted">(opsional)</span></label>
                            <input type="file" name="payment_proof" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan <span class="text-muted">(opsional)</span></label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Catatan untuk operator...">{{ old('note') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Kirim Pengajuan</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Riwayat --}}
<div class="card mt-3" data-aos="fade-up">
    <div class="card-body">
        <h2 class="h6 mb-3"><i class="bi bi-clock-history me-1"></i>Riwayat Langganan</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal Ajuan</th><th>Durasi</th><th>Total</th><th>Periode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($history as $sub)
                    <tr>
                        <td><small>{{ $sub->created_at->format('d M Y H:i') }}</small></td>
                        <td>{{ $sub->months }} bln</td>
                        <td>{{ $fmt($sub->price) }}</td>
                        <td><small class="text-muted">{{ $sub->starts_at ? $sub->starts_at->format('d M Y') . ' — ' . $sub->ends_at->format('d M Y') : '-' }}</small></td>
                        <td><span class="badge bg-soft-{{ $sub->statusColor() }}">{{ $sub->statusLabel() }}</span></td>
                        <td class="text-end">
                            @if ($sub->status === \App\Models\Subscription::STATUS_PENDING)
                                <form action="{{ route('admin.subscription.cancel', $sub) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Batalkan pengajuan langganan ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="bi bi-x-lg"></i> Batal</button>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada riwayat langganan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($school)
            {{ $history->links() }}
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const sel = document.getElementById('months');
        const total = document.getElementById('total');
        if (!sel || !total) return;
        const price = Number(sel.dataset.price || 0);
        const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
        sel.addEventListener('change', () => total.value = fmt(price * Number(sel.value)));
    })();
</script>
@endpush
