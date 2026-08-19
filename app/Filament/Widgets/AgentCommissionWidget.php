<?php

namespace App\Filament\Widgets;

use App\Models\AgentCommissionStatement;
use App\Services\CommissionCalculator;
use Filament\Widgets\Widget;

class AgentCommissionWidget extends Widget
{
    protected string $view = 'filament.widgets.agent-commission-widget';

    protected int|string|array $columnSpan = 'full';

    public array $currentEstimate = [];

    public ?AgentCommissionStatement $lastStatement = null;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('sales_agent') ?? false;
    }

    public function mount(): void
    {
        $agent = auth()->user();
        $calculator = app(CommissionCalculator::class);

        $this->currentEstimate = $calculator->calculate($agent, now());

        $this->lastStatement = $agent->commissionStatements()
            ->whereDate('period_month', now()->subMonthNoOverflow()->startOfMonth())
            ->first();
    }
}
