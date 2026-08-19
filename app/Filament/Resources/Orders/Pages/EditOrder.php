<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $isSalesAgent = auth()->user()?->hasRole('sales_agent') ?? false;

        return [
            Action::make('changeStatus')
                ->label('Cambiar Estado')
                ->color('warning')
                ->schema([
                    Select::make('status')
                        ->label('Nuevo estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'phone_verified' => 'Teléfono verificado',
                            'confirmed' => 'Confirmado',
                            'shipped' => 'Enviado',
                            'delivered' => 'Entregado',
                            'cancelled' => 'Cancelado',
                            'returned' => 'Devuelto',
                        ])
                        ->default(fn($record) => $record->status)
                        ->required()
                        ->native(false),
                    Textarea::make('note')
                        ->label('Nota (opcional)')
                        ->rows(3)
                        ->placeholder('Ej. Cliente confirmó dirección por WhatsApp'),
                ])
                ->action(function (array $data, Order $record) {
                    $user = auth()->user();
                    $newStatus = $data['status'];

                    // A sales agent can't touch an order that already belongs to a different agent.
                    if (
                        $user->hasRole('sales_agent')
                        && $record->sales_agent_id !== null
                        && $record->sales_agent_id !== $user->id
                    ) {
                        Notification::make()
                            ->title('Este pedido ya pertenece a otro agente')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Whoever confirms an unassigned order becomes the agent of record.
                    // This is an atomic conditional UPDATE (not a read-then-write) so that
                    // if two agents confirm the same order at the same instant, only one
                    // of them can actually win the claim - the second one gets rejected below.
                    if ($newStatus === 'confirmed' && $record->sales_agent_id === null && $user->hasRole('sales_agent')) {
                        $claimed = DB::table('orders')
                            ->where('id', $record->id)
                            ->whereNull('sales_agent_id')
                            ->update(['sales_agent_id' => $user->id]);

                        if ($claimed === 0) {
                            Notification::make()
                                ->title('Este pedido ya fue confirmado por otro agente')
                                ->body('Alguien más lo tomó justo antes que tú.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->sales_agent_id = $user->id;
                    }

                    $record->statusChangeNote = $data['note'] ?? null;
                    $record->status = $newStatus;
                    $record->save();

                    Notification::make()
                        ->title('Estado actualizado')
                        ->success()
                        ->send();
                })
                ->after(fn() => redirect(request()->header('Referer')))
                ->modalHeading('Cambiar estado del pedido')
                ->modalSubmitActionLabel('Guardar'),

            DeleteAction::make()->visible(! $isSalesAgent),
            ForceDeleteAction::make()->visible(! $isSalesAgent),
            RestoreAction::make()->visible(! $isSalesAgent),
        ];
    }
}
