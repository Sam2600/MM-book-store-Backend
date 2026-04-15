<?php

namespace App\Console\Commands;

use App\Models\AuthorEarning;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BulkCreatePayoutsCommand extends Command
{
    protected $signature = 'payouts:bulk-create
                            {--period= : Period in YYYY-MM format (defaults to previous month)}';

    protected $description = 'Auto-create pending payout records for all eligible authors for a given period.';

    public function handle(): int
    {
        $period    = $this->option('period') ?? now()->subMonth()->format('Y-m');
        $threshold = (float) env('MINIMUM_PAYOUT_THRESHOLD', 5000);

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $this->error("Invalid period format: {$period}. Use YYYY-MM.");
            return self::FAILURE;
        }

        $this->info("Creating payouts for period: {$period}");
        $this->info("Minimum threshold: {$threshold} MMK");

        $earnings = AuthorEarning::where('period', $period)
            ->where('source', 'ad_revenue')
            ->select('translator_id', DB::raw('SUM(amount) as total'))
            ->groupBy('translator_id')
            ->having('total', '>=', $threshold)
            ->get();

        if ($earnings->isEmpty()) {
            $this->warn("No earnings above threshold found for {$period}. Run POST /admin/earnings/calculate first.");
            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $errors  = [];

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

        $this->info("Done. Created: {$created} | Skipped (already exist): {$skipped}");

        foreach ($errors as $err) {
            $this->warn($err);
        }

        return self::SUCCESS;
    }
}
