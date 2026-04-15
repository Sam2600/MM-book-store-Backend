<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\AuthorEarning;
use App\Models\ChapterRead;
use App\Models\Payout;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    use ApiResponse;

    /**
     * Calculate and store monthly author earnings from chapter reads (ad revenue).
     *
     * POST /admin/earnings/calculate
     * Body: { month: "2026-04", ad_revenue: 1500000.00 }
     *
     * Formula:
     *   author_amount = (author_chapter_reads / total_platform_reads) × ad_revenue × (rate / 100)
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'ad_revenue' => 'required|numeric|min:0',
        ]);

        $month     = $request->input('month');
        $adRevenue = (float) $request->input('ad_revenue');

        [$year, $mon] = explode('-', $month);

        $rate = Rate::where('type', 'chapter_read')->first();

        if (!$rate) {
            return $this->error('No rate configured for type "chapter_read". Add one in the rates table first.');
        }

        $totalReads = ChapterRead::whereYear('created_at', $year)
            ->whereMonth('created_at', $mon)
            ->count();

        if ($totalReads === 0) {
            return $this->error("No chapter reads found for {$month}.");
        }

        $authorReads = ChapterRead::whereYear('chapter_reads.created_at', $year)
            ->whereMonth('chapter_reads.created_at', $mon)
            ->join('novels', 'chapter_reads.novel_id', '=', 'novels.id')
            ->select(
                'novels.translator_id',
                'chapter_reads.novel_id',
                DB::raw('COUNT(*) as read_count')
            )
            ->groupBy('novels.translator_id', 'chapter_reads.novel_id')
            ->get();

        DB::beginTransaction();

        try {
            $results = [];

            foreach ($authorReads as $row) {
                $amount = round(($row->read_count / $totalReads) * $adRevenue * ($rate->rate / 100), 2);

                AuthorEarning::updateOrCreate(
                    [
                        'translator_id' => $row->translator_id,
                        'novel_id'      => $row->novel_id,
                        'source'        => 'ad_revenue',
                        'period'        => $month,
                    ],
                    [
                        'rate_id'      => $rate->id,
                        'amount'       => $amount,
                        'coins_earned' => 0,
                        'earned_at'    => now(),
                    ]
                );

                $results[] = [
                    'translator_id' => $row->translator_id,
                    'novel_id'      => $row->novel_id,
                    'read_count'    => $row->read_count,
                    'amount'        => $amount,
                ];
            }

            DB::commit();

            return $this->success("Earnings calculated for {$month}. Total reads: {$totalReads}.", [
                'month'       => $month,
                'total_reads' => $totalReads,
                'ad_revenue'  => $adRevenue,
                'rate'        => $rate->rate,
                'breakdown'   => $results,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }

    /**
     * Calculate earnings for a month then immediately create pending payout records
     * for all eligible authors in a single call.
     *
     * POST /admin/earnings/calculate-and-create-payouts
     * Body: { month: "2026-03", ad_revenue: 1500000.00 }
     */
    public function calculateAndCreatePayouts(Request $request): JsonResponse
    {
        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'ad_revenue' => 'required|numeric|min:0',
        ]);

        $month     = $request->input('month');
        $adRevenue = (float) $request->input('ad_revenue');
        $threshold = (float) env('MINIMUM_PAYOUT_THRESHOLD', 0);

        [$year, $mon] = explode('-', $month);

        $rate = Rate::where('type', 'chapter_read')->first();

        if (!$rate) {
            return $this->error('No rate configured for type "chapter_read". Add one in the rates table first.');
        }

        $totalReads = ChapterRead::whereYear('created_at', $year)
            ->whereMonth('created_at', $mon)
            ->count();

        if ($totalReads === 0) {
            return $this->error("No chapter reads found for {$month}.");
        }

        $authorReads = ChapterRead::whereYear('chapter_reads.created_at', $year)
            ->whereMonth('chapter_reads.created_at', $mon)
            ->join('novels', 'chapter_reads.novel_id', '=', 'novels.id')
            ->select(
                'novels.translator_id',
                'chapter_reads.novel_id',
                DB::raw('COUNT(*) as read_count')
            )
            ->groupBy('novels.translator_id', 'chapter_reads.novel_id')
            ->get();

        DB::beginTransaction();

        try {
            // Step 1: Calculate earnings per novel per author
            $earningsBreakdown = [];

            foreach ($authorReads as $row) {
                $amount = round(($row->read_count / $totalReads) * $adRevenue * ($rate->rate / 100), 2);

                AuthorEarning::updateOrCreate(
                    [
                        'translator_id' => $row->translator_id,
                        'novel_id'      => $row->novel_id,
                        'source'        => 'ad_revenue',
                        'period'        => $month,
                    ],
                    [
                        'rate_id'      => $rate->id,
                        'amount'       => $amount,
                        'coins_earned' => 0,
                        'earned_at'    => now(),
                    ]
                );

                $earningsBreakdown[] = [
                    'translator_id' => $row->translator_id,
                    'novel_id'      => $row->novel_id,
                    'read_count'    => $row->read_count,
                    'amount'        => $amount,
                ];
            }

            // Step 2: Aggregate per author and create payout records
            $authorTotals = AuthorEarning::where('period', $month)
                ->where('source', 'ad_revenue')
                ->select('translator_id', DB::raw('SUM(amount) as total'))
                ->groupBy('translator_id')
                ->having('total', '>=', $threshold)
                ->get();

            $payoutsCreated = 0;
            $payoutsSkipped = 0;
            $payoutErrors   = [];

            foreach ($authorTotals as $earning) {
                if (Payout::where('translator_id', $earning->translator_id)->where('period', $month)->exists()) {
                    $payoutsSkipped++;
                    continue;
                }

                $author = User::with('paymentMethod')->find($earning->translator_id);

                if (!$author || !$author->paymentMethod || !$author->payment_account) {
                    $authorName = $author?->name ?? 'unknown';
                    $payoutErrors[] = "Author #{$earning->translator_id} ({$authorName}) has no payment info — skipped.";
                    continue;
                }

                Payout::create([
                    'translator_id'   => $earning->translator_id,
                    'period'          => $month,
                    'total_amount'    => $earning->total,
                    'payment_method'  => $author->paymentMethod->code,
                    'payment_account' => $author->payment_account,
                    'status'          => 'pending',
                ]);

                $payoutsCreated++;
            }

            DB::commit();

            return $this->success("Earnings calculated and payouts generated for {$month}.", [
                'month'       => $month,
                'total_reads' => $totalReads,
                'ad_revenue'  => $adRevenue,
                'rate'        => $rate->rate,
                'threshold'   => $threshold,
                'earnings'    => [
                    'breakdown' => $earningsBreakdown,
                ],
                'payouts' => [
                    'created' => $payoutsCreated,
                    'skipped' => $payoutsSkipped,
                    'errors'  => $payoutErrors,
                ],
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }

    /**
     * Get chapter read stats for a specific author (all their novels).
     *
     * GET /author/earnings/stats?month=2026-04
     */
    public function authorStats(Request $request): JsonResponse
    {
        $user  = $request->user();
        $month = $request->query('month');

        $query = ChapterRead::whereIn('chapter_reads.novel_id', function ($q) use ($user) {
                $q->select('id')->from('novels')->where('translator_id', $user->id);
            })
            ->join('novels', 'chapter_reads.novel_id', '=', 'novels.id')
            ->select(
                'chapter_reads.novel_id',
                'novels.title as novel_title',
                DB::raw('COUNT(*) as read_count')
            )
            ->groupBy('chapter_reads.novel_id', 'novels.title');

        if ($month) {
            [$year, $mon] = explode('-', $month);
            $query->whereYear('chapter_reads.created_at', $year)
                  ->whereMonth('chapter_reads.created_at', $mon);
        }

        $stats = $query->get();

        $earnings = AuthorEarning::where('translator_id', $user->id)
            ->where('source', 'ad_revenue')
            ->when($month, function ($q) use ($month) {
                [$year, $mon] = explode('-', $month);
                $q->whereYear('earned_at', $year)->whereMonth('earned_at', $mon);
            })
            ->select('novel_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('novel_id')
            ->pluck('total_amount', 'novel_id');

        $data = $stats->map(function ($row) use ($earnings) {
            return [
                'novel_id'    => $row->novel_id,
                'novel_title' => $row->novel_title,
                'read_count'  => $row->read_count,
                'amount'      => (float) ($earnings[$row->novel_id] ?? 0),
            ];
        });

        return $this->success('Author earnings stats.', $data);
    }
}
