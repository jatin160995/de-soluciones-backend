<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Historial de Estado
        </x-slot>

        @php
            $statusLabels = [
                'pending' => 'Pendiente',
                'phone_verified' => 'Teléfono verificado',
                'confirmed' => 'Confirmado',
                'shipped' => 'Enviado',
                'delivered' => 'Entregado',
                'cancelled' => 'Cancelado',
            ];
            $statusColors = [
                'pending' => 'gray',
                'phone_verified' => 'info',
                'confirmed' => 'warning',
                'shipped' => 'primary',
                'delivered' => 'success',
                'cancelled' => 'danger',
            ];
            $history = $this->getHistory();
        @endphp

        @if ($history->isEmpty())
            <p style="opacity:0.6;">Sin historial todavía.</p>
        @else
            <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach ($history as $entry)
                    <div style="display:flex;gap:1rem;align-items:flex-start;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:1rem;">
                        <x-filament::badge :color="$statusColors[$entry->status] ?? 'gray'">
                            {{ $statusLabels[$entry->status] ?? $entry->status }}
                        </x-filament::badge>
                        <div style="flex:1;">
                            <p style="font-size:0.85rem;">{{ $entry->note ?? 'Sin nota' }}</p>
                            <p style="font-size:0.75rem;opacity:0.6;margin-top:0.25rem;">
                                {{ $entry->changedBy?->name ?? 'Sistema / Cliente' }}
                                &middot;
                                {{ $entry->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>