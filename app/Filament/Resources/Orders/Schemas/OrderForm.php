<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del pedido')
                    ->description('Se establece automáticamente cuando el pedido se realiza en la tienda en línea.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('order_number')
                                ->label('Número de pedido')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('store_id')
                                ->relationship('store', 'name')
                                ->label('Tienda')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('user_id')
                                ->relationship('user', 'name')
                                ->label('Cuenta del cliente')
                                ->placeholder('Compra como invitado')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('sales_agent_id')
                                ->relationship('salesAgent', 'name')
                                ->label('Agente de ventas')
                                ->placeholder(fn() => auth()->user()?->hasRole('sales_agent')
                                    ? 'Sin asignar (se asigna al confirmar)'
                                    : 'Ninguno')
                                ->helperText(fn() => auth()->user()?->hasRole('sales_agent')
                                    ? null
                                    : 'Un admin puede asignar/reasignar manualmente. Los agentes lo obtienen automáticamente al confirmar el pedido.')
                                ->disabled(fn() => auth()->user()?->hasRole('sales_agent') ?? false)
                                ->dehydrated(fn() => ! (auth()->user()?->hasRole('sales_agent') ?? false)),
                            Select::make('address_id')
                                ->relationship('address', 'line1')
                                ->label('Dirección de envío')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('payment_method')
                                ->label('Método de pago')
                                ->options([
                                    'cod' => 'Contra entrega',
                                    'card' => 'Tarjeta',
                                    'paypal' => 'PayPal',
                                ])
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Totales')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('L.')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('discount_percent')
                                ->label('Descuento (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->default(0)
                                ->live(onBlur: true)
                                ->helperText('Para cuando el cliente pide un descuento al confirmar el pedido.')
                                ->afterStateUpdated(function ($state, $get, $set) {
                                    $subtotal = (float) $get('subtotal');
                                    $shipping = (float) $get('shipping_cost');
                                    $discountAmount = round($subtotal * ((float) $state / 100), 2);

                                    $set('discount_amount', $discountAmount);
                                    $set('total', round($subtotal - $discountAmount + $shipping, 2));
                                }),
                            TextInput::make('discount_amount')
                                ->label('Monto descontado')
                                ->numeric()
                                ->prefix('L.')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('shipping_cost')
                                ->label('Costo de envío')
                                ->numeric()
                                ->prefix('L.')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('total')
                                ->label('Total')
                                ->numeric()
                                ->prefix('L.')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Información del cliente')
                    ->description('Editable para correcciones de soporte (ej. un error de tipeo en el teléfono/correo).')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer_name')
                                ->label('Nombre del cliente'),
                            TextInput::make('customer_phone')
                                ->label('Teléfono')
                                ->tel(),
                            TextInput::make('customer_email')
                                ->label('Correo electrónico')
                                ->email(),
                        ]),
                    ]),

                Section::make('Estado')
                    ->schema([
                        Select::make('status')
                            ->label('Estado del pedido')
                            ->options([
                                'pending' => 'Pendiente',
                                'phone_verified' => 'Teléfono verificado',
                                'confirmed' => 'Confirmado',
                                'shipped' => 'Enviado',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Usa el botón "Cambiar Estado" arriba para actualizar y dejar una nota.'),
                        Textarea::make('notes')
                            ->label('Notas internas')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
