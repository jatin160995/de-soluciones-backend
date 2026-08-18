<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class OrderStatusTimelineWidget extends Widget
{
    protected string $view = 'filament.resources.orders.widgets.order-status-timeline';

    public ?Order $record = null;

    protected int|string|array $columnSpan = 'full';

    protected function getHistory()
    {
        return $this->record->statusHistory()
            ->with('changedBy')
            ->orderByDesc('created_at')
            ->get();
    }
}
