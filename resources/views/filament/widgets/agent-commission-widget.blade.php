<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Mi comisión
        </x-slot>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div style="padding: 1rem; border-radius: 0.5rem; background-color: rgba(255,255,255,0.04);">
                <div style="font-size: 0.75rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.25rem;">
                    {{ ucfirst(now()->translatedFormat('F Y')) }} (estimado, en curso)
                </div>
                <div style="font-size: 1.5rem; font-weight: 700;">
                    {{ number_format($currentEstimate['commission_amount'] ?? 0, 2) }}
                    {{ $currentEstimate['commission_currency'] ?? config('store.currency', 'HNL') }}
                </div>
                <div style="font-size: 0.8rem; opacity: 0.6; margin-top: 0.25rem;">
                    Ventas entregadas hasta hoy: L. {{ number_format($currentEstimate['delivered_sales_total'] ?? 0, 2) }}
                </div>
            </div>

            <div style="padding: 1rem; border-radius: 0.5rem; background-color: rgba(255,255,255,0.04);">
                <div style="font-size: 0.75rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.25rem;">
                    {{ ucfirst(now()->subMonthNoOverflow()->translatedFormat('F Y')) }}
                </div>

                @if ($lastStatement)
                    <div style="font-size: 1.5rem; font-weight: 700;">
                        {{ number_format($lastStatement->commission_amount, 2) }}
                        {{ $lastStatement->commission_currency }}
                    </div>
                    <div style="margin-top: 0.5rem;">
                        <x-filament::badge :color="$lastStatement->status === 'paid' ? 'success' : 'warning'">
                            {{ $lastStatement->status === 'paid' ? 'Pagada' : 'Pendiente' }}
                        </x-filament::badge>
                    </div>
                @else
                    <div style="font-size: 0.9rem; opacity: 0.6;">
                        Aún no se ha generado un estado de cuenta para este mes.
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>