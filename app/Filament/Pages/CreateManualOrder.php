<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Lets a sales agent (or an admin, on behalf of any agent) log a phone/WhatsApp
 * sale by hand - covers both agents who resell above the listed price and
 * customers who can only communicate by voice/call and never touch the
 * storefront themselves.
 *
 * Every order created here gets orders.source = 'manual', and every line item
 * stores both the sale price and the product's real price at that moment
 * (order_items.base_unit_price), so CommissionCalculator::manualMarkupBonus()
 * can compute the markup bonus later without depending on the product's
 * current (possibly since-changed) price.
 */
class CreateManualOrder extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.create-manual-order';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Pedidos';

    protected static ?string $navigationLabel = 'Nuevo Pedido Manual';

    protected static ?string $title = 'Nuevo Pedido Manual';

    protected static ?int $navigationSort = 1;

    /** Form state container. */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->hasRole('sales_agent'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'status' => 'confirmed',
            'items' => [[]],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $isAdmin = auth()->user()?->hasRole('super_admin') ?? false;

        return $schema
            ->components([
                Section::make('Vendedor')
                    ->visible($isAdmin)
                    ->schema([
                        Select::make('sales_agent_id')
                            ->label('Agente de ventas')
                            ->options(fn() => User::role('sales_agent')->pluck('name', 'id'))
                            ->searchable()
                            ->required($isAdmin)
                            ->helperText('El pedido y su comisión se asignarán a este agente.'),
                    ]),

                Section::make('Cliente')
                    ->description('Datos tomados por teléfono o WhatsApp.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer_name')
                                ->label('Nombre del cliente')
                                ->required(),
                            TextInput::make('customer_phone')
                                ->label('Teléfono')
                                ->tel()
                                ->required(),
                            TextInput::make('customer_email')
                                ->label('Correo (opcional)')
                                ->email(),
                        ]),
                    ]),

                Section::make('Productos')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    Select::make('variant_id')
                                        ->label('Producto')
                                        ->searchable()
                                        ->getSearchResultsUsing(function (string $search) {
                                            return ProductVariant::query()
                                                ->where('is_active', true)
                                                ->where(function ($q) use ($search) {
                                                    $q->where('sku', 'like', "%{$search}%")
                                                        ->orWhereHas('product', function ($q2) use ($search) {
                                                            $q2->where('name', 'like', "%{$search}%")
                                                                ->orWhere('description', 'like', "%{$search}%")
                                                                ->orWhere('slug', 'like', "%{$search}%");
                                                        });
                                                })
                                                ->with('product')
                                                ->limit(30)
                                                ->get()
                                                ->mapWithKeys(fn(ProductVariant $v) => [
                                                    $v->id => $v->product->name . ($v->sku ? " ({$v->sku})" : ''),
                                                ]);
                                        })
                                        ->getOptionLabelUsing(function ($value) {
                                            $variant = ProductVariant::with('product')->find($value);

                                            return $variant
                                                ? $variant->product->name . ($variant->sku ? " ({$variant->sku})" : '')
                                                : null;
                                        })
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $variant = ProductVariant::find($state);

                                            if ($variant) {
                                                $set('base_price', (float) $variant->effective_price);
                                                $set('sale_price', (float) $variant->effective_price);
                                            }
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('base_price')
                                        ->label('Precio real')
                                        ->prefix('L.')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->default(0),

                                    TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required(),

                                    TextInput::make('sale_price')
                                        ->label('Precio de venta')
                                        ->prefix('L.')
                                        ->numeric()
                                        ->required()
                                        ->live(onBlur: true)
                                        ->rule(function ($get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $base = (float) $get('base_price');

                                                if ($base > 0 && (float) $value < $base) {
                                                    $fail("No puede ser menor al precio real (L. {$base}).");
                                                }
                                            };
                                        })
                                        ->columnSpan(2),
                                ]),
                            ])
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar producto')
                            ->required(),
                    ]),

                Section::make('Estado y notas')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Estado inicial del pedido')
                                ->options([
                                    'pending' => 'Pendiente',
                                    'phone_verified' => 'Teléfono verificado',
                                    'confirmed' => 'Confirmado',
                                    'shipped' => 'Enviado',
                                    'delivered' => 'Entregado',
                                ])
                                ->default('confirmed')
                                ->required()
                                ->helperText('Usa "Entregado" si la venta ya se completó por completo.'),
                            TextInput::make('shipping_cost')
                                ->label('Costo de envío')
                                ->prefix('L.')
                                ->numeric()
                                ->default(0),
                        ]),
                        Textarea::make('notes')
                            ->label('Notas internas')
                            ->rows(2)
                            ->placeholder('Ej. Cliente contactado por WhatsApp, confirmó por audio')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();
        $isAdmin = $user->hasRole('super_admin');

        $agentId = $isAdmin ? ($data['sales_agent_id'] ?? null) : $user->id;

        if (! $agentId) {
            Notification::make()
                ->title('Selecciona un agente de ventas')
                ->danger()
                ->send();

            return;
        }

        $items = collect($data['items'] ?? [])->filter(fn($item) => filled($item['variant_id'] ?? null));

        if ($items->isEmpty()) {
            Notification::make()
                ->title('Agrega al menos un producto')
                ->danger()
                ->send();

            return;
        }

        $order = DB::transaction(function () use ($data, $agentId, $items) {
            $subtotal = $items->sum(fn($item) => (float) $item['sale_price'] * (int) $item['quantity']);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);

            $order = new Order([
                'order_number' => $this->generateOrderNumber(),
                'store_id' => Store::query()->value('id') ?? 1,
                'sales_agent_id' => $agentId,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_percent' => 0,
                'payment_method' => 'cod',
                'source' => 'manual',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $order->save();

            foreach ($items as $item) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);

                if (! $variant) {
                    continue;
                }

                $quantity = (int) $item['quantity'];
                $salePrice = (float) $item['sale_price'];
                $basePrice = (float) $item['base_price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variant->attributes,
                    'sku' => $variant->sku,
                    'unit_price' => $salePrice,
                    'base_unit_price' => $basePrice,
                    'quantity' => $quantity,
                    'line_total' => round($salePrice * $quantity, 2),
                ]);

                $variant->decrement('stock_quantity', $quantity);

                InventoryMovement::create([
                    'variant_id' => $variant->id,
                    'type' => 'out',
                    'quantity' => $quantity,
                    'reason' => 'Venta manual (pedido #' . $order->order_number . ')',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'created_by' => Auth::id(),
                ]);
            }

            return $order;
        });

        Notification::make()
            ->title('Pedido manual creado')
            ->success()
            ->send();

        $this->redirect(OrderResource::getUrl('edit', ['record' => $order]));
    }

    protected function generateOrderNumber(): string
    {
        do {
            $candidate = 'ORD-MAN-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Order::withTrashed()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
