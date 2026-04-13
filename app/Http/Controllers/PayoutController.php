<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\AuthorEarning;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    use ApiResponse;

    /**
     * Admin: create a payout record for one author for a given month.
     * Automatically sums their ad_revenue earnings for that period.
     *
     * POST /admin/payouts
     * Body: { translator_id, period, payment_method?, payment_account?, note? }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'translator_id'   => 'required|exists:users,id',
            'period'          => 'required|date_format:Y-m',
            'payment_method'  => 'nullable|string|max:100',
            'payment_account' => 'nullable|string|max:100',
            'note'            => 'nullable|string',
        ]);

        $translator = User::with('paymentMethod')->findOrFail($request->translator_id);

        $total = AuthorEarning::where('translator_id', $request->translator_id)
            ->where('source', 'ad_revenue')
            ->where('period', $request->period)
            ->sum('amount');

        if ($total <= 0) {
            return $this->error("No ad_revenue earnings found for {$request->period}. Run /admin/earnings/calculate first.");
        }

        // Use author's saved payment info as default; admin can override per payout.
        $method  = $request->payment_method  ?? $translator->paymentMethod?->code;
        $account = $request->payment_account ?? $translator->payment_account;

        if (!$method || !$account) {
            return $this->error('No payment method on file. Ask the author to update their payment info, or provide payment_method and payment_account in this request.');
        }

        $payout = Payout::create([
            'translator_id'  => $request->translator_id,
            'period'         => $request->period,
            'total_amount'   => $total,
            'payment_method' => $method,
            'payment_account'=> $account,
            'status'         => 'pending',
            'note'           => $request->note,
        ]);

        return $this->success("Payout created for {$translator->name} — {$request->period}.", $payout);
    }

    /**
     * Admin: mark a payout as paid after transferring the money.
     *
     * PATCH /admin/payouts/{payout}/mark-paid
     * Body: { reference_number?, note? }
     */
    public function markPaid(Request $request, Payout $payout): JsonResponse
    {
        if ($payout->status === 'paid') {
            return $this->error("Payout #{$payout->id} is already marked as paid.");
        }

        $request->validate([
            'reference_number' => 'nullable|string|max:255',
            'note'             => 'nullable|string',
        ]);

        $payout->update([
            'status'           => 'paid',
            'reference_number' => $request->reference_number,
            'note'             => $request->note ?? $payout->note,
            'paid_at'          => now(),
        ]);

        return $this->success("Payout #{$payout->id} marked as paid.", $payout);
    }

    /**
     * Admin: list all payouts.
     * Query params: ?status=pending|paid  ?period=YYYY-MM
     *
     * GET /admin/payouts
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $payouts = Payout::with('author:id,name,email')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->period, fn ($q) => $q->where('period', $request->period))
            ->orderByDesc('period')
            ->orderBy('status')
            ->paginate(20);

        return $this->success('Payouts list.', $payouts);
    }

    /**
     * Author: view own payout history.
     *
     * GET /author/payouts
     */
    public function authorIndex(Request $request): JsonResponse
    {
        $payouts = Payout::where('translator_id', $request->user()->id)
            ->orderByDesc('period')
            ->get();

        return $this->success('Your payout history.', $payouts);
    }
}
