@extends('layouts.storefront')

@section('title', 'Mi Cuenta | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Consulta tu historial de pedidos, direcciones guardadas y datos personales en ' . ($siteName ?? 'DE Soluciones') . '.')

@section('content')

@php
  $symbol = config('store.currency_symbol');

  // "María Fernández" → "MF"
  $initials = collect(preg_split('/\s+/', trim($user->name)))
      ->filter()
      ->take(2)
      ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
      ->implode('') ?: '?';

  // Badge classes all exist in storefront/css/style.css.
  $statusMeta = [
      'pending'        => ['label' => 'Pendiente',            'class' => 'status-pending'],
      'phone_verified' => ['label' => 'Teléfono verificado',   'class' => 'status-verified'],
      'confirmed'      => ['label' => 'Confirmado',            'class' => 'status-confirmed'],
      'shipped'        => ['label' => 'Enviado',               'class' => 'status-shipped'],
      'delivered'      => ['label' => 'Entregado',             'class' => 'status-delivered'],
      'cancelled'      => ['label' => 'Cancelado',             'class' => 'status-cancelled'],
      // The orders.status enum also allows "returned"; it has no badge colour of
      // its own, so it borrows the cancelled treatment.
      'returned'       => ['label' => 'Devuelto',              'class' => 'status-cancelled'],
  ];

  $paymentLabels = [
      'cod'    => 'Contra entrega (efectivo)',
      'card'   => 'Tarjeta',
      'paypal' => 'PayPal',
  ];

  /*
   * Both forms on this page live in the same DOM, so each request uses its own
   * error bag (see App\Http\Requests\Account\*) and old() is only replayed
   * into the form that actually failed.
   */
  $profileFailed = $errors->profile->any();
  $addressFailed = $errors->address->any();

  $profileOld = $profileFailed ? old() : [];
  $addressOld = $addressFailed ? old() : [];

  // Same list as the checkout address block.
  $departments = [
      'Atlántida', 'Choluteca', 'Colón', 'Comayagua', 'Copán', 'Cortés',
      'El Paraíso', 'Francisco Morazán', 'Gracias a Dios', 'Intibucá',
      'Islas de la Bahía', 'La Paz', 'Lempira', 'Ocotepeque', 'Olancho',
      'Santa Bárbara', 'Valle', 'Yoro',
  ];
@endphp

{{-- Breadcrumb --}}
<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li class="active" aria-current="page">Mi cuenta</li>
      </ol>
    </nav>
  </div>
</div>

{{-- Mi Cuenta --}}
<section class="section-pad-sm">
  <div class="container">
    <div class="row g-4 account-layout">

      {{-- Sidebar --}}
      <div class="col-lg-3">
        <div class="account-sidebar">
          <div class="account-user">
            <div class="account-avatar">{{ $initials }}</div>
            <div>
              <p class="account-user-name">{{ $user->name }}</p>
              <span class="account-user-email">{{ $user->email }}</span>
            </div>
          </div>
          <nav class="account-nav nav flex-column" role="tablist">
            <button class="account-nav-link {{ $activeTab === 'pedidos' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-pedidos" type="button" role="tab">
              <i class="bi bi-box-seam"></i> Mis pedidos
            </button>
            <button class="account-nav-link {{ $activeTab === 'direcciones' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-direcciones" type="button" role="tab">
              <i class="bi bi-geo-alt"></i> Mis direcciones
            </button>
            <button class="account-nav-link {{ $activeTab === 'datos' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-datos" type="button" role="tab">
              <i class="bi bi-person"></i> Datos personales
            </button>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="account-nav-link account-logout w-100">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
              </button>
            </form>
          </nav>
        </div>
      </div>

      {{-- Content --}}
      <div class="col-lg-9">

        @if(session('status'))
          <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
          </div>
        @endif

        <div class="tab-content">

          {{-- Mis pedidos --}}
          <div class="tab-pane fade {{ $activeTab === 'pedidos' ? 'show active' : '' }}" id="tab-pedidos" role="tabpanel">
            <h3 class="account-content-title">Mis pedidos</h3>

            @forelse($orders as $order)
              @php
                $meta      = $statusMeta[$order->status] ?? ['label' => $order->status, 'class' => 'status-pending'];
                $itemCount = $order->items->count();
                $snapshot  = $order->shipping_snapshot ?? [];
                $address   = $order->address;
              @endphp

              <div class="order-card">
                <div class="order-card-header">
                  <div>
                    <p class="order-card-number">Pedido #{{ $order->order_number }}</p>
                    <span class="order-card-date">{{ $order->created_at?->isoFormat('D [de] MMMM, YYYY') }}</span>
                  </div>
                  <span class="order-status-badge {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                </div>

                <div class="order-card-items">
                  @foreach($order->items->take(3) as $item)
                    @php $thumb = $item->variant?->product?->getFirstMediaUrl('images', 'thumb'); @endphp
                    @if($thumb)
                      <img src="{{ $thumb }}" alt="{{ $item->product_name }}">
                    @endif
                  @endforeach
                  <span class="order-card-items-label">{{ $itemCount }} {{ $itemCount === 1 ? 'producto' : 'productos' }}</span>
                </div>

                <div class="order-card-footer">
                  <span class="order-card-total">Total: {{ $symbol }}{{ number_format($order->total, 2) }}</span>
                  <button type="button" class="btn-outline-navy btn-sm-outline" data-bs-toggle="collapse" data-bs-target="#order-detail-{{ $order->id }}" aria-expanded="false" aria-controls="order-detail-{{ $order->id }}">
                    Ver detalle
                  </button>
                </div>

                {{-- Order detail (expands in place; no separate detail page in the design) --}}
                <div class="collapse" id="order-detail-{{ $order->id }}">
                  <div class="row g-3 pt-3">

                    <div class="col-lg-6">
                      <div class="delivery-recap">
                        <h4><i class="bi bi-truck"></i> Datos de entrega</h4>
                        <div class="delivery-recap-row">
                          <span class="delivery-recap-label">Nombre</span>
                          <span>{{ $address->recipient_name ?? $snapshot['recipient_name'] ?? $order->customer_name ?? '—' }}</span>
                        </div>
                        <div class="delivery-recap-row">
                          <span class="delivery-recap-label">Teléfono</span>
                          <span>{{ $address->phone ?? $snapshot['phone'] ?? $order->customer_phone ?? '—' }}</span>
                        </div>
                        <div class="delivery-recap-row">
                          <span class="delivery-recap-label">Dirección</span>
                          <span>{{ $address->line1 ?? $snapshot['line1'] ?? '—' }}{{ ($address->line2 ?? $snapshot['line2'] ?? null) ? ', ' . ($address->line2 ?? $snapshot['line2']) : '' }}</span>
                        </div>
                        <div class="delivery-recap-row">
                          <span class="delivery-recap-label">Ciudad / Departamento</span>
                          <span>{{ collect([$address->city ?? $snapshot['city'] ?? null, $address->state ?? $snapshot['state'] ?? null])->filter()->implode(', ') ?: '—' }}</span>
                        </div>
                        <div class="delivery-recap-row">
                          <span class="delivery-recap-label">Método de pago</span>
                          <span>{{ $paymentLabels[$order->payment_method] ?? ($order->payment_method ?? '—') }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="checkout-summary">
                        <h4>Resumen del pedido</h4>

                        <div class="checkout-summary-items">
                          @php
                            // Snapshot keys are English ("size", "color"); anything outside
                            // this map falls back to a capitalised version of the raw key.
                            $attrLabels = ['size' => 'Talla', 'color' => 'Color', 'material' => 'Material'];
                          @endphp

                          @foreach($order->items as $item)
                            @php
                              $variant = $item->variant;
                              $thumb   = $variant?->product?->getFirstMediaUrl('images', 'thumb');

                              /*
                               * Prefer the snapshot stored on the order line — it is what the
                               * customer actually bought, and it survives the variant being
                               * edited or deleted later. Only fall back to the live variant
                               * (through its size/color/extra_attributes accessors) when the
                               * line has no snapshot.
                               */
                              $attrs = collect($item->variant_attributes ?? [])->filter(fn ($v) => filled($v));

                              if ($attrs->isEmpty() && $variant) {
                                  $attrs = collect(['size' => $variant->size, 'color' => $variant->color])
                                      ->merge($variant->extra_attributes ?? [])
                                      ->filter(fn ($v) => filled($v));
                              }

                              $variantLine = $attrs
                                  ->map(fn ($value, $key) => ($attrLabels[$key] ?? ucfirst((string) $key)) . ': ' . $value)
                                  ->implode(' · ');

                              $sku = $item->sku ?: $variant?->sku;
                            @endphp

                            <div class="summary-mini-item">
                              @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $item->product_name }}">
                              @endif
                              <div class="summary-mini-info">
                                <p>{{ $item->product_name }}</p>
                                @if($variantLine)
                                  <span class="d-block">{{ $variantLine }}</span>
                                @endif
                                @if($sku)
                                  <span class="d-block">SKU: {{ $sku }}</span>
                                @endif
                                <span class="d-block">{{ $item->quantity }} &times; {{ $symbol }}{{ number_format($item->unit_price, 2) }}</span>
                              </div>
                              <span class="summary-mini-price">{{ $symbol }}{{ number_format($item->line_total, 2) }}</span>
                            </div>
                          @endforeach
                        </div>

                        <hr>

                        <div class="summary-row">
                          <span>Subtotal</span>
                          <span>{{ $symbol }}{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if((float) $order->discount_amount > 0)
                          <div class="summary-row">
                            <span>Descuento @if((float) $order->discount_percent > 0)({{ rtrim(rtrim(number_format($order->discount_percent, 2), '0'), '.') }}%)@endif</span>
                            <span>-{{ $symbol }}{{ number_format($order->discount_amount, 2) }}</span>
                          </div>
                        @endif
                        <div class="summary-row">
                          <span>Envío</span>
                          <span>{{ $symbol }}{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                        <hr>
                        <div class="summary-row summary-total">
                          <span>Total</span>
                          <span>{{ $symbol }}{{ number_format($order->total, 2) }}</span>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 48px;"></i>
                <h3 class="mt-3">Aún no tienes pedidos</h3>
                <p>Cuando hagas tu primera compra, la verás aquí con su estado de entrega.</p>
                <a href="{{ route('catalog') }}" class="btn-buy-now">Explorar el catálogo</a>
              </div>
            @endforelse

            @if($orders->hasPages())
              <nav class="catalog-pagination" aria-label="Paginación de pedidos">
                {{ $orders->onEachSide(1)->links('pagination::bootstrap-5') }}
              </nav>
            @endif
          </div>

          {{-- Mis direcciones --}}
          <div class="tab-pane fade {{ $activeTab === 'direcciones' ? 'show active' : '' }}" id="tab-direcciones" role="tabpanel">
            <h3 class="account-content-title">Mis direcciones</h3>

            <div class="address-grid">
              @foreach($addresses as $address)
                <div class="address-card">
                  <div class="address-card-header">
                    <span class="address-card-label">
                      <i class="bi {{ $address->label === 'Oficina' ? 'bi-building' : 'bi-house-door' }}"></i>
                      {{ $address->label ?: 'Dirección' }}
                    </span>
                    @if($address->is_default)
                      <span class="address-badge-default">Predeterminada</span>
                    @endif
                  </div>
                  <p class="address-card-name">{{ $address->recipient_name }}</p>
                  <p class="address-card-text">{{ collect([$address->line1, $address->line2])->filter()->implode(', ') }}</p>
                  <p class="address-card-text">{{ collect([$address->city, $address->state])->filter()->implode(', ') }}</p>
                  <p class="address-card-phone"><i class="bi bi-whatsapp"></i> {{ $address->phone }}</p>
                  <div class="address-card-actions">
                    <button type="button"
                            class="address-action-btn js-address-edit"
                            data-action="{{ route('account.addresses.update', $address) }}"
                            data-address-id="{{ $address->id }}"
                            data-label="{{ $address->label }}"
                            data-recipient-name="{{ $address->recipient_name }}"
                            data-phone="{{ $address->phone }}"
                            data-line1="{{ $address->line1 }}"
                            data-line2="{{ $address->line2 }}"
                            data-city="{{ $address->city }}"
                            data-state="{{ $address->state }}"
                            data-postal-code="{{ $address->postal_code }}"
                            data-is-default="{{ $address->is_default ? '1' : '0' }}">
                      <i class="bi bi-pencil"></i> Editar
                    </button>
                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" class="js-address-delete">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="address-action-btn address-action-danger">
                        <i class="bi bi-trash3"></i> Eliminar
                      </button>
                    </form>
                  </div>
                </div>
              @endforeach

              <button type="button" class="address-card-add js-address-create">
                <i class="bi bi-plus-circle"></i>
                <span>Agregar nueva dirección</span>
              </button>
            </div>

            @if($addresses->isEmpty())
              <p class="account-user-email d-block mt-3">
                Guarda una dirección para que tus próximos pedidos se completen más rápido.
              </p>
            @endif
          </div>

          {{-- Datos personales --}}
          <div class="tab-pane fade {{ $activeTab === 'datos' ? 'show active' : '' }}" id="tab-datos" role="tabpanel">
            <h3 class="account-content-title">Datos personales</h3>

            <form id="profileForm" class="checkout-form account-profile-form" method="POST" action="{{ route('account.profile.update') }}" novalidate>
              @csrf
              @method('PUT')

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="profileName">Nombre completo</label>
                  <input type="text" class="form-control @error('name', 'profile') is-invalid @enderror" id="profileName" name="name" value="{{ $profileOld['name'] ?? $user->name }}" required>
                  @error('name', 'profile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="profileEmail">Correo electrónico</label>
                  <input type="email" class="form-control @error('email', 'profile') is-invalid @enderror" id="profileEmail" name="email" value="{{ $profileOld['email'] ?? $user->email }}" required>
                  @error('email', 'profile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="profilePhone">Teléfono</label>
                  <input type="tel" class="form-control @error('phone', 'profile') is-invalid @enderror" id="profilePhone" name="phone" value="{{ $profileOld['phone'] ?? $user->phone }}" placeholder="+504 0000-0000" required>
                  @error('phone', 'profile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="profileWhatsapp">Número de WhatsApp</label>
                  <input type="tel" class="form-control @error('whatsapp_number', 'profile') is-invalid @enderror" id="profileWhatsapp" name="whatsapp_number" value="{{ $profileOld['whatsapp_number'] ?? $user->whatsapp_number }}" placeholder="+504 0000-0000" required>
                  @error('whatsapp_number', 'profile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
              </div>

              <button type="submit" class="btn-place-order account-save-btn">
                <i class="bi bi-check-lg"></i> Guardar cambios
              </button>
            </form>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

{{-- Address modal (shared by "Agregar" and "Editar") --}}
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true"
     @if($addressFailed) data-open-on-load="1" @endif
     data-store-action="{{ route('account.addresses.store') }}">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">
          {{ !empty($addressOld['address_id']) ? 'Editar dirección' : 'Agregar nueva dirección' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="addressForm" class="checkout-form" method="POST"
            action="{{ !empty($addressOld['address_id']) ? route('account.addresses.update', $addressOld['address_id']) : route('account.addresses.store') }}"
            novalidate>
        @csrf
        <input type="hidden" name="_method" id="addressFormMethod" value="{{ !empty($addressOld['address_id']) ? 'PUT' : 'POST' }}">
        <input type="hidden" name="address_id" id="addressFormId" value="{{ $addressOld['address_id'] ?? '' }}">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="addressRecipient">Nombre de quien recibe</label>
              <input type="text" class="form-control @error('recipient_name', 'address') is-invalid @enderror" id="addressRecipient" name="recipient_name" value="{{ $addressOld['recipient_name'] ?? '' }}" required>
              @error('recipient_name', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="addressPhone">Teléfono / WhatsApp</label>
              <input type="tel" class="form-control @error('phone', 'address') is-invalid @enderror" id="addressPhone" name="phone" value="{{ $addressOld['phone'] ?? '' }}" placeholder="+504 0000-0000" required>
              @error('phone', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="addressLabel">Etiqueta (opcional)</label>
              <select class="form-select @error('label', 'address') is-invalid @enderror" id="addressLabel" name="label">
                @php $selectedLabel = $addressOld['label'] ?? ''; @endphp
                <option value="" @selected($selectedLabel === '')>Sin etiqueta</option>
                <option value="Casa" @selected($selectedLabel === 'Casa')>Casa</option>
                <option value="Oficina" @selected($selectedLabel === 'Oficina')>Oficina</option>
              </select>
              @error('label', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="addressCity">Ciudad</label>
              <input type="text" class="form-control @error('city', 'address') is-invalid @enderror" id="addressCity" name="city" value="{{ $addressOld['city'] ?? '' }}" required>
              @error('city', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label" for="addressLine1">Dirección</label>
              <input type="text" class="form-control @error('line1', 'address') is-invalid @enderror" id="addressLine1" name="line1" value="{{ $addressOld['line1'] ?? '' }}" placeholder="Calle, avenida, número de casa" required>
              @error('line1', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label" for="addressLine2">Referencia de la dirección</label>
              <input type="text" class="form-control @error('line2', 'address') is-invalid @enderror" id="addressLine2" name="line2" value="{{ $addressOld['line2'] ?? '' }}" placeholder="Punto de referencia, color de casa, portón, etc." required>
              @error('line2', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="addressState">Departamento</label>
              <select class="form-select @error('state', 'address') is-invalid @enderror" id="addressState" name="state" required>
                <option value="">Selecciona...</option>
                @foreach($departments as $department)
                  <option value="{{ $department }}" @selected(($addressOld['state'] ?? '') === $department)>{{ $department }}</option>
                @endforeach
              </select>
              @error('state', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="addressPostal">Código postal</label>
              <input type="text" class="form-control @error('postal_code', 'address') is-invalid @enderror" id="addressPostal" name="postal_code" value="{{ $addressOld['postal_code'] ?? '' }}">
              @error('postal_code', 'address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="addressCountry">País</label>
              <input type="text" class="form-control" id="addressCountry" value="Honduras" readonly>
            </div>
            <div class="col-12">
              <label class="auth-checkbox">
                <input type="checkbox" name="is_default" id="addressIsDefault" value="1" @checked(!empty($addressOld['is_default']))>
                <span>Usar como dirección predeterminada</span>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-outline-navy btn-sm-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-place-order account-save-btn m-0">
            <i class="bi bi-check-lg"></i> Guardar dirección
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

@endsection
