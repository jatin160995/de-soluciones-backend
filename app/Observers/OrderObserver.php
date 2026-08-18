<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'note' => filled($order->statusChangeNote) ? $order->statusChangeNote : $this->buildNote($order),
            'changed_by' => Auth::id(),
        ]);
    }

    public function created(Order $order): void
    {
        // Log the initial status too, so every order has at least one history row from creation.
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'note' => 'Pedido creado',
            'changed_by' => Auth::id(),
        ]);
    }

    protected function buildNote(Order $order): ?string
    {
        $previous = $order->getOriginal('status');

        return "Cambiado de \"{$previous}\" a \"{$order->status}\"";
    }
}
