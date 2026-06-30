<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('npsn', 'like', "%$s%"))
            ->with(['subscriptions' => fn ($q) => $q->latest()])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $pending = Subscription::with(['school', 'requester'])
            ->where('status', Subscription::STATUS_PENDING)
            ->latest()
            ->get();

        $price = Subscription::monthlyPrice();
        $currency = config('sitara.subscription.currency');

        return view('superadmin.subscriptions.index', compact('schools', 'pending', 'price', 'currency'));
    }

    /**
     * Atur harga langganan per bulan (berlaku global untuk semua sekolah).
     */
    public function updatePrice(Request $request)
    {
        $data = $request->validate([
            'monthly_price' => ['required', 'integer', 'min:0', 'max:100000000'],
        ]);

        Subscription::setMonthlyPrice($data['monthly_price']);

        return back()->with('success', 'Harga langganan per bulan berhasil diperbarui.');
    }

    /**
     * Konfirmasi pengajuan langganan dari admin sekolah.
     */
    public function approve(Subscription $subscription)
    {
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 422, 'Pengajuan ini sudah diproses.');

        $this->activate($subscription, $subscription->months);

        return back()->with('success', "Langganan {$subscription->school->name} diaktifkan sampai {$subscription->ends_at->format('d M Y')}.");
    }

    public function reject(Request $request, Subscription $subscription)
    {
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 422, 'Pengajuan ini sudah diproses.');

        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $subscription->update([
            'status' => Subscription::STATUS_REJECTED,
            'note' => $data['note'] ?? $subscription->note,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan langganan ditolak.');
    }

    /**
     * Super admin mengaktifkan langganan secara manual untuk sebuah sekolah.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'months' => ['required', 'integer', 'min:1', 'max:24'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $monthlyPrice = Subscription::monthlyPrice();

        $subscription = new Subscription([
            'plan_name' => 'Bulanan',
            'months' => $data['months'],
            'price' => $monthlyPrice * $data['months'],
            'status' => Subscription::STATUS_PENDING,
            'payment_method' => $data['payment_method'] ?? 'Manual (operator)',
            'note' => $data['note'] ?? null,
        ]);
        $subscription->school_id = $data['school_id'];
        $subscription->save();

        $this->activate($subscription, $data['months']);

        $school = School::find($data['school_id']);

        return back()->with('success', "Langganan {$school->name} aktif sampai {$subscription->ends_at->format('d M Y')}.");
    }

    /**
     * Set sebuah subscription menjadi aktif. Jika sekolah masih punya langganan
     * yang berjalan, perpanjangan ditumpuk dari tanggal berakhir terakhir.
     */
    private function activate(Subscription $subscription, int $months): void
    {
        $current = $subscription->school->activeSubscription();

        $startsAt = $current && $current->ends_at->isFuture()
            ? $current->ends_at->copy()->addDay()
            : Carbon::today();

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonthsNoOverflow($months),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }
}
