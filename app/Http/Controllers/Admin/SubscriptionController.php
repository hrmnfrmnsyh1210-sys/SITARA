<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        $active = $school?->activeSubscription();
        $history = $school
            ? $school->subscriptions()->latest()->paginate(10)
            : collect();

        $price = Subscription::monthlyPrice();
        $currency = config('sitara.subscription.currency');

        return view('admin.subscription.index', compact('school', 'active', 'history', 'price', 'currency'));
    }

    /**
     * Admin sekolah mengajukan perpanjangan (status pending → menunggu konfirmasi super admin).
     */
    public function store(Request $request)
    {
        $school = auth()->user()->school;
        abort_unless($school, 403, 'Akun Anda tidak terhubung ke sekolah.');

        if ($school->hasPendingSubscription()) {
            return back()->with('error', 'Masih ada pengajuan langganan yang menunggu konfirmasi. Mohon tunggu konfirmasi dari operator.');
        }

        $data = $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:24'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => ['nullable', 'image', 'max:2048'],
        ]);

        $monthlyPrice = Subscription::monthlyPrice();

        $subscription = new Subscription([
            'plan_name' => 'Bulanan',
            'months' => $data['months'],
            'price' => $monthlyPrice * $data['months'],
            'status' => Subscription::STATUS_PENDING,
            'payment_method' => $data['payment_method'] ?? null,
            'note' => $data['note'] ?? null,
            'requested_by' => auth()->id(),
        ]);
        $subscription->school_id = $school->id;

        if ($request->hasFile('payment_proof')) {
            $subscription->payment_proof = $request->file('payment_proof')->store('subscription-proofs', 'public');
        }

        $subscription->save();

        return redirect()->route('admin.subscription.index')
            ->with('success', 'Pengajuan langganan terkirim. Akses penuh akan aktif setelah dikonfirmasi operator.');
    }

    public function cancel(Subscription $subscription)
    {
        abort_unless($subscription->school_id === auth()->user()->school_id, 403);
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 422);

        if ($subscription->payment_proof) {
            Storage::disk('public')->delete($subscription->payment_proof);
        }
        $subscription->delete();

        return back()->with('success', 'Pengajuan langganan dibatalkan.');
    }
}
