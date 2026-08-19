<?php

namespace App\Services;

use App\Models\AgentCommissionStatement;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CommissionCalculator
{
    /**
     * Sum of delivered order totals for an agent within a calendar month.
     * "Delivered within the month" is based on when the order last transitioned
     * to status = 'delivered' (via order_status_history), not when it was created.
     */
    public function deliveredSalesTotal(User $agent, CarbonInterface $periodMonth): float
    {
        $start = $periodMonth->copy()->startOfMonth();
        $end = $periodMonth->copy()->endOfMonth();

        $deliveredAtSubquery = DB::table('order_status_history')
            ->select('order_id', DB::raw('MAX(created_at) as delivered_at'))
            ->where('status', 'delivered')
            ->groupBy('order_id');

        return (float) Order::query()
            ->where('sales_agent_id', $agent->id)
            ->where('status', 'delivered')
            ->joinSub($deliveredAtSubquery, 'delivered_events', function ($join) {
                $join->on('orders.id', '=', 'delivered_events.order_id');
            })
            ->whereBetween('delivered_events.delivered_at', [$start, $end])
            ->sum('orders.total');
    }

    /**
     * Compute the commission amount + currency for an agent for a given month,
     * based on their active commission plan.
     *
     * @return array{delivered_sales_total: float, commission_amount: float, commission_currency: string}
     */
    public function calculate(User $agent, CarbonInterface $periodMonth): array
    {
        $deliveredTotal = $this->deliveredSalesTotal($agent, $periodMonth);
        $plan = $agent->commissionPlan;

        if (! $plan || ! $plan->is_active) {
            return [
                'delivered_sales_total' => $deliveredTotal,
                'commission_amount' => 0.0,
                'commission_currency' => config('store.currency', 'HNL'),
            ];
        }

        [$amount, $currency] = match ($plan->type) {
            'flat_percent' => $this->calculateFlatPercent($deliveredTotal, $plan->config),
            'volume_bonus' => $this->calculateVolumeBonus($deliveredTotal, $plan->config),
            'tiered_percent' => $this->calculateTieredPercent($deliveredTotal, $plan->config),
            default => [0.0, config('store.currency', 'HNL')],
        };

        return [
            'delivered_sales_total' => $deliveredTotal,
            'commission_amount' => $amount,
            'commission_currency' => $currency,
        ];
    }

    /**
     * Create or update the agent's commission statement for a given month.
     * Never overwrites a statement that has already been marked as paid.
     */
    public function generateStatement(User $agent, CarbonInterface $periodMonth): AgentCommissionStatement
    {
        $existing = AgentCommissionStatement::query()
            ->where('user_id', $agent->id)
            ->whereDate('period_month', $periodMonth->copy()->startOfMonth())
            ->first();

        if ($existing && $existing->status === 'paid') {
            return $existing;
        }

        $result = $this->calculate($agent, $periodMonth);

        return AgentCommissionStatement::updateOrCreate(
            [
                'user_id' => $agent->id,
                'period_month' => $periodMonth->copy()->startOfMonth()->toDateString(),
            ],
            [
                'delivered_sales_total' => $result['delivered_sales_total'],
                'commission_amount' => $result['commission_amount'],
                'commission_currency' => $result['commission_currency'],
            ]
        );
    }

    /**
     * Type (a): flat percentage of total delivered order value. Currency: HNL.
     * config: {"percent": 8}
     */
    protected function calculateFlatPercent(float $total, array $config): array
    {
        $percent = (float) ($config['percent'] ?? 0);

        return [round($total * ($percent / 100), 2), config('store.currency', 'HNL')];
    }

    /**
     * Type (b): fixed-$ bonus for the highest sales-volume tier reached that month.
     * Tiers do NOT stack - only the highest threshold met applies. Currency: USD.
     * config: {"tiers": [{"threshold_lps": 250000, "bonus_usd": 50}, ...]}
     */
    protected function calculateVolumeBonus(float $total, array $config): array
    {
        $tiers = collect($config['tiers'] ?? [])
            ->sortByDesc('threshold_lps')
            ->values();

        $bonus = 0.0;

        foreach ($tiers as $tier) {
            if ($total >= (float) ($tier['threshold_lps'] ?? 0)) {
                $bonus = (float) ($tier['bonus_usd'] ?? 0);
                break;
            }
        }

        return [round($bonus, 2), 'USD'];
    }

    /**
     * Type (c): subtract a deduction % from the total first, then apply
     * a lower or higher rate depending on which side of the threshold
     * the month's total sales volume falls. Currency: HNL.
     * config: {"deduction_percent": 15, "threshold_lps": 200000, "below_percent": 4, "above_percent": 5}
     */
    protected function calculateTieredPercent(float $total, array $config): array
    {
        $deductionPercent = (float) ($config['deduction_percent'] ?? 0);
        $threshold = (float) ($config['threshold_lps'] ?? 0);
        $belowPercent = (float) ($config['below_percent'] ?? 0);
        $abovePercent = (float) ($config['above_percent'] ?? 0);

        $net = $total * (1 - ($deductionPercent / 100));
        $rate = $total > $threshold ? $abovePercent : $belowPercent;

        return [round($net * ($rate / 100), 2), config('store.currency', 'HNL')];
    }
}
