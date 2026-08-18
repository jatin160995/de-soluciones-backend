<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Widgets\OrderSummaryWidget;
use App\Filament\Resources\Orders\Widgets\OrderStatusTimelineWidget;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
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
                    $record->statusChangeNote = $data['note'] ?? null;
                    $record->status = $data['status'];
                    $record->save();
                })
                ->after(fn() => redirect(request()->header('Referer')))
                ->modalHeading('Cambiar estado del pedido')
                ->modalSubmitActionLabel('Guardar'),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderSummaryWidget::make(['record' => $this->record]),
            OrderStatusTimelineWidget::make(['record' => $this->record]),
        ];
    }
}
