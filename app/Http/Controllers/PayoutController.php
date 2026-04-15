<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Mail\PayoutConfirmedMail;
use App\Models\AuthorEarning;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayoutController extends Controller
{
    use ApiResponse;

    /**
     * Admin: overview of payouts for a period.
     *
     * GET /admin/payouts/summary?period=YYYY-MM
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate(['period' => 'required|date_format:Y-m']);

        $period = $request->period;

        $all          = Payout::where('period', $period);
        $totalAmount  = (clone $all)->sum('total_amount');
        $pendingCount = (clone $all)->where('status', 'pending')->count();
        $paidCount    = (clone $all)->where('status', 'paid')->count();
        $pendingAmt   = (clone $all)->where('status', 'pending')->sum('total_amount');
        $paidAmt      = (clone $all)->where('status', 'paid')->sum('total_amount');

        return $this->success("Payout summary for {$period}.", [
            'period'        => $period,
            'total_authors' => $pendingCount + $paidCount,
            'total_amount'  => (float) $totalAmount,
            'pending'       => ['count' => $pendingCount, 'amount' => (float) $pendingAmt],
            'paid'          => ['count' => $paidCount,    'amount' => (float) $paidAmt],
        ]);
    }

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

        $method  = $request->payment_method  ?? $translator->paymentMethod?->code;
        $account = $request->payment_account ?? $translator->payment_account;

        if (!$method || !$account) {
            return $this->error('No payment method on file. Ask the author to update their payment info, or provide payment_method and payment_account in this request.');
        }

        if (Payout::where('translator_id', $request->translator_id)->where('period', $request->period)->exists()) {
            return $this->error("A payout for {$translator->name} in period {$request->period} already exists.");
        }

        $payout = Payout::create([
            'translator_id'   => $request->translator_id,
            'period'          => $request->period,
            'total_amount'    => $total,
            'payment_method'  => $method,
            'payment_account' => $account,
            'status'          => 'pending',
            'note'            => $request->note,
        ]);

        return $this->success("Payout created for {$translator->name} — {$request->period}.", $payout);
    }

    /**
     * Admin: auto-create pending payout records for all eligible authors for a period.
     * Skips authors already having a payout or below the minimum threshold.
     *
     * POST /admin/payouts/bulk-create
     * Body: { period: "YYYY-MM" }
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $request->validate(['period' => 'required|date_format:Y-m']);

        $period    = $request->period;
        $threshold = (float) env('MINIMUM_PAYOUT_THRESHOLD', 5000);

        $earnings = AuthorEarning::where('period', $period)
            ->where('source', 'ad_revenue')
            ->select('translator_id', DB::raw('SUM(amount) as total'))
            ->groupBy('translator_id')
            ->having('total', '>=', $threshold)
            ->get();

        if ($earnings->isEmpty()) {
            return $this->error("No earnings above threshold ({$threshold} MMK) found for {$period}. Run POST /admin/earnings/calculate first.");
        }

        $created = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();

        try {
            foreach ($earnings as $earning) {
                if (Payout::where('translator_id', $earning->translator_id)->where('period', $period)->exists()) {
                    $skipped++;
                    continue;
                }

                $author = User::with('paymentMethod')->find($earning->translator_id);

                if (!$author || !$author->paymentMethod || !$author->payment_account) {
                    $authorName = $author?->name ?? 'unknown';
                    $errors[] = "Author #{$earning->translator_id} ({$authorName}) has no payment info — skipped.";
                    continue;
                }

                Payout::create([
                    'translator_id'   => $earning->translator_id,
                    'period'          => $period,
                    'total_amount'    => $earning->total,
                    'payment_method'  => $author->paymentMethod->code,
                    'payment_account' => $author->payment_account,
                    'status'          => 'pending',
                ]);

                $created++;
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage());
        }

        return $this->success("Bulk payouts created for {$period}.", [
            'period'    => $period,
            'threshold' => $threshold,
            'created'   => $created,
            'skipped'   => $skipped,
            'errors'    => $errors,
        ]);
    }

    /**
     * Admin: batch-confirm multiple payouts as paid.
     * Sends PayoutConfirmedMail to each author.
     *
     * POST /admin/payouts/bulk-mark-paid
     * Body: { payouts: [{id, reference_number?, note?}] }
     */
    public function bulkMarkPaid(Request $request): JsonResponse
    {
        $request->validate([
            'payouts'                    => 'required|array|min:1',
            'payouts.*.id'               => 'required|exists:payouts,id',
            'payouts.*.reference_number' => 'nullable|string|max:255',
            'payouts.*.note'             => 'nullable|string',
        ]);

        $adminId    = $request->user()->id;
        $now        = now();
        $markedPaid = 0;
        $alreadyPaid = [];

        DB::beginTransaction();

        try {
            foreach ($request->payouts as $item) {
                $payout = Payout::with('author')->find($item['id']);

                if ($payout->status === 'paid') {
                    $alreadyPaid[] = $item['id'];
                    continue;
                }

                $payout->update([
                    'status'           => 'paid',
                    'reference_number' => $item['reference_number'] ?? null,
                    'note'             => $item['note'] ?? $payout->note,
                    'paid_at'          => $now,
                    'processed_by'     => $adminId,
                ]);

                if ($payout->author?->email) {
                    Mail::to($payout->author->email)->send(new PayoutConfirmedMail($payout));
                }

                $markedPaid++;
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage());
        }

        return $this->success("{$markedPaid} payout(s) marked as paid.", [
            'marked_paid'  => $markedPaid,
            'already_paid' => $alreadyPaid,
        ]);
    }

    /**
     * Admin: mark a single payout as paid after transferring the money.
     * Sends PayoutConfirmedMail to the author.
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
            'processed_by'     => $request->user()->id,
        ]);

        $payout->load('author');

        if ($payout->author?->email) {
            Mail::to($payout->author->email)->send(new PayoutConfirmedMail($payout));
        }

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
     * Admin: export payouts for a period as a CSV file.
     * Use this as a checklist when doing manual transfers via KBZ Pay / Wave.
     *
     * GET /admin/payouts/export?period=YYYY-MM
     */
    public function export(Request $request)
    {
        $request->validate(['period' => 'required|date_format:Y-m']);

        $period  = $request->period;
        $payouts = Payout::with('author:id,name,email')
            ->where('period', $period)
            ->orderBy('status')
            ->orderByDesc('total_amount')
            ->get();

        $filename = "payouts-{$period}.csv";

        $callback = function () use ($payouts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Author Name',
                'Email',
                'Payment Method',
                'Account Number',
                'Amount (MMK)',
                'Status',
                'Reference Number',
                'Note',
            ]);

            foreach ($payouts as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->author->name  ?? '—',
                    $p->author->email ?? '—',
                    strtoupper($p->payment_method),
                    $p->payment_account,
                    number_format($p->total_amount, 2, '.', ''),
                    $p->status,
                    $p->reference_number ?? '',
                    $p->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Author: self-request a payout for a period.
     * Creates a pending payout if earnings >= threshold and no payout exists yet.
     *
     * POST /author/payouts/request
     * Body: { period: "YYYY-MM" }
     */
    public function authorRequestPayout(Request $request): JsonResponse
    {
        $request->validate(['period' => 'required|date_format:Y-m']);

        $user      = $request->user();
        $period    = $request->period;
        $threshold = (float) env('MINIMUM_PAYOUT_THRESHOLD', 5000);

        if (!$user->payment_method_id || !$user->payment_account) {
            return $this->error('Please set up your payment method before requesting a payout. Go to your profile and add your KBZ Pay / Wave Pay account.');
        }

        if (Payout::where('translator_id', $user->id)->where('period', $period)->exists()) {
            return $this->error("A payout for {$period} already exists. Check your payout history.");
        }

        $total = AuthorEarning::where('translator_id', $user->id)
            ->where('source', 'ad_revenue')
            ->where('period', $period)
            ->sum('amount');

        if ($total <= 0) {
            return $this->error("No earnings found for {$period}. Earnings are calculated at the end of each month.");
        }

        if ($total < $threshold) {
            return $this->error("Your earnings for {$period} (" . number_format($total, 2) . " MMK) are below the minimum payout threshold (" . number_format($threshold, 0) . " MMK).");
        }

        $user->load('paymentMethod');

        $payout = Payout::create([
            'translator_id'   => $user->id,
            'period'          => $period,
            'total_amount'    => $total,
            'payment_method'  => $user->paymentMethod->code,
            'payment_account' => $user->payment_account,
            'status'          => 'pending',
        ]);

        return $this->success("Payout request submitted for {$period}. You will receive an email once the payment is confirmed.", $payout);
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
