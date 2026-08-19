<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $statusLabels = [
                'pending' => 'Pendiente',
                'phone_verified' => 'Teléfono verificado',
                'confirmed' => 'Confirmado',
                'shipped' => 'Enviado',
                'delivered' => 'Entregado',
                'cancelled' => 'Cancelado',
                'returned' => 'Devuelto',
            ];
            $statusColors = [
                'pending' => 'gray',
                'phone_verified' => 'info',
                'confirmed' => 'warning',
                'shipped' => 'primary',
                'delivered' => 'success',
                'cancelled' => 'danger',
                'returned' => 'danger',
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1.5rem;">
            <div>
                <p style="font-size:0.8rem;opacity:0.6;margin-bottom:0.25rem;">Fecha del pedido</p>
                <p style="font-weight:600;">{{ $record->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem;opacity:0.6;margin-bottom:0.25rem;">Hace</p>
                <p style="font-weight:600;">{{ $record->created_at?->diffForHumans() }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem;opacity:0.6;margin-bottom:0.25rem;">Tienda</p>
                <p style="font-weight:600;">{{ $record->store?->name ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem;opacity:0.6;margin-bottom:0.25rem;">Cliente</p>
                <p style="font-weight:600;">{{ $record->customer_name ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:0.8rem;opacity:0.6;margin-bottom:0.25rem;">Estado actual</p>
                <x-filament::badge :color="$statusColors[$record->status] ?? 'gray'">
                    {{ $statusLabels[$record->status] ?? $record->status }}
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>