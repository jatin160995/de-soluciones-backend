<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CommissionCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAgentCommissionStatements extends Command
{
    protected $signature = 'commissions:generate {--month= : Period to generate for, format YYYY-MM. Defaults to last month.}';

    protected $description = 'Generate/refresh monthly commission statements for all active sales agents.';

    public function handle(CommissionCalculator $calculator): int
    {
        $periodMonth = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : now()->subMonthNoOverflow()->startOfMonth();

        $agents = User::role('sales_agent')
            ->whereHas('commissionPlan', fn($q) => $q->where('is_active', true))
            ->get();

        if ($agents->isEmpty()) {
            $this->info('No active sales agents with a commission plan found.');

            return self::SUCCESS;
        }

        $this->info("Generating commission statements for {$periodMonth->format('F Y')}...");

        foreach ($agents as $agent) {
            $statement = $calculator->generateStatement($agent, $periodMonth);

            $this->line(sprintf(
                '- %s: L. %s delivered -> %s %s (%s)',
                $agent->name,
                number_format($statement->delivered_sales_total, 2),
                $statement->commission_currency,
                number_format($statement->commission_amount, 2),
                $statement->status,
            ));
        }

        $this->info("Done. {$agents->count()} statement(s) processed.");

        return self::SUCCESS;
    }
}
