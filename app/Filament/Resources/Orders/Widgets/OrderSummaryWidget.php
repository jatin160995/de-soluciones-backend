<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class OrderSummaryWidget extends Widget
{
    protected string $view = 'filament.resources.orders.widgets.order-summary';

    public ?Order $record = null;

    protected int|string|array $columnSpan = 'full';
}
