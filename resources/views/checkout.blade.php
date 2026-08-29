@extends('layouts.storefront')

@section('title', 'Finalizar Compra | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Completa tus datos de envío para finalizar tu compra en DE Soluciones. Pago contra entrega.')

@section('content')

{{--
  Ported from checkout.html. Two differences from the prototype on purpose:
    - No OTP / "verificar-pedido" step - Daniel: "checkout como invitado sin OTP".
      The stepper below has 3 steps (Carrito -> Datos y pago -> Confirmación),
      not 4.
    - customer_email is optional, not required - not needed for direct (COD)
      payment when there's no email OTP to send.
  Card/PayPal stay visible but disabled: M4 (gateways) hasn't been built yet.
--}}

@php
  $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
  $currency = config('store.currency_symbol', 'L.');
  $formatMoney = fn ($v) => $currency . ' ' . number_format((float) $v, 2);
@endphp

<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li><a href="{{ route('cart.index') }}">Carrito</a></li>
        <li class="active" aria-current="page">Finalizar Compra</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-pad-sm">
  <div class="container">

    <div class="checkout-steps">
      <div class="step-item completed">
        <span class="step-circle"><i class="bi bi-check-lg"></i></span>
        <span class="step-label">Carrito</span>
      </div>
      <div class="step-line"></div>
      <div class="step-item active">
        <span class="step-circle">2</span>
        <span class="step-label">Datos y pago</span>
      </div>
      <div class="step-line"></div>
      <div class="step-item">
        <span class="step-circle">3</span>
        <span class="step-label">Confirmación</span>
      </div>
    </div>

    {{-- @error('checkout')
      <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
      </div>
    @enderror --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Hay errores en el formulario:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form id="checkoutForm" class="checkout-form" action="{{ route('checkout.store') }}" method="POST">
      @csrf
      <div class="row g-4">

        {{-- Form column --}}
        <div class="col-lg-7">

          <div class="checkout-section">
            <h3 class="checkout-section-title"><span>1</span> Información de contacto</h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="customerName">Nombre completo</label>
                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customerName" name="customer_name" value="{{ old('customer_name') }}" required>
                @error('customer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="customerPhone">Teléfono</label>
                <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" id="customerPhone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+504 0000-0000" required>
                @error('customer_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="customerWhatsapp">Número de WhatsApp</label>
                <input type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" id="customerWhatsapp" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="+504 0000-0000" required>
                @error('whatsapp_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <p class="field-hint"><i class="bi bi-whatsapp"></i> Te contactaremos aquí para confirmar tu pedido.</p>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="customerAltPhone">Teléfono alternativo (opcional)</label>
                <input type="tel" class="form-control" id="customerAltPhone" name="alternate_phone" value="{{ old('alternate_phone') }}" placeholder="+504 0000-0000">
              </div>
              <div class="col-12">
                <label class="form-label" for="customerEmail">Correo electrónico (opcional)</label>
                <input type="email" class="form-control @error('customer_email') is-invalid @enderror" id="customerEmail" name="customer_email" value="{{ old('customer_email') }}" placeholder="tucorreo@ejemplo.com">
                @error('customer_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="checkout-section">
            <h3 class="checkout-section-title"><span>2</span> Dirección de envío</h3>

            @if($addresses->isNotEmpty())
              <div class="row g-3 mb-2">
                <div class="col-12">
                  <label class="form-label" for="savedAddressSelect">Usar una dirección guardada</label>
                  <select class="form-select" id="savedAddressSelect" name="address_id" onchange="deSolucionesToggleAddressFields(this)">
                    @foreach($addresses as $saved)
                      <option value="{{ $saved->id }}" @selected(old('address_id', $defaultAddress?->id) == $saved->id)>
                        {{ $saved->label ?: 'Dirección' }} - {{ $saved->line1 }}, {{ $saved->city }}
                      </option>
                    @endforeach
                    <option value="" @selected(old('address_id') === '')>Usar otra dirección</option>
                  </select>
                </div>
              </div>
            @endif

            @php
              $manualFieldsHidden = $addresses->isNotEmpty() && old('address_id', $defaultAddress?->id ?? '') !== '';
            @endphp

            <div id="manualAddressFields" class="row g-3" @if($manualFieldsHidden) style="display:none;" @endif>
              <div class="col-md-6">
                <label class="form-label" for="recipientName">Nombre de quien recibe</label>
                <input type="text" class="form-control @error('recipient_name') is-invalid @enderror" id="recipientName" name="recipient_name" value="{{ old('recipient_name') }}" @unless($manualFieldsHidden) required @endunless>
                @error('recipient_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="addressLabel">Etiqueta (opcional)</label>
                <select class="form-select" id="addressLabel" name="label">
                  <option value="">Sin etiqueta</option>
                  <option value="Casa" @selected(old('label') === 'Casa')>Casa</option>
                  <option value="Oficina" @selected(old('label') === 'Oficina')>Oficina</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="addressLine1">Dirección</label>
                <input type="text" class="form-control @error('line1') is-invalid @enderror" id="addressLine1" name="line1" value="{{ old('line1') }}" placeholder="Calle, avenida, número de casa" @unless($manualFieldsHidden) required @endunless>
                @error('line1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="addressLine2">Referencia de la dirección</label>
                <input type="text" class="form-control @error('line2') is-invalid @enderror" id="addressLine2" name="line2" value="{{ old('line2') }}" placeholder="Punto de referencia, color de casa, portón, etc." @unless($manualFieldsHidden) required @endunless>
                @error('line2')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-5">
                <label class="form-label" for="addressCity">Ciudad</label>
                <input type="text" class="form-control @error('city') is-invalid @enderror" id="addressCity" name="city" value="{{ old('city') }}" @unless($manualFieldsHidden) required @endunless>
                @error('city')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="addressState">Departamento</label>
                <select class="form-select @error('state') is-invalid @enderror" id="addressState" name="state" @unless($manualFieldsHidden) required @endunless>
                  <option value="">Selecciona...</option>
                  @foreach(['Atlántida','Choluteca','Colón','Comayagua','Copán','Cortés','El Paraíso','Francisco Morazán','Gracias a Dios','Intibucá','Islas de la Bahía','La Paz','Lempira','Ocotepeque','Olancho','Santa Bárbara','Valle','Yoro'] as $dept)
                    <option @selected(old('state') === $dept)>{{ $dept }}</option>
                  @endforeach
                </select>
                @error('state')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label" for="addressPostal">Código postal</label>
                <input type="text" class="form-control" id="addressPostal" name="postal_code" value="{{ old('postal_code') }}">
              </div>
              <div class="col-12">
                <label class="form-label" for="addressCountry">País</label>
                <input type="text" class="form-control" id="addressCountry" name="country" value="Honduras" readonly>
              </div>

              @auth
                <div class="col-12">
                  <label class="terms-check">
                    <input type="checkbox" name="save_address" value="1" @checked(old('save_address'))>
                    <span>Guardar esta dirección para mis próximos pedidos</span>
                  </label>
                </div>
              @endauth
            </div>
          </div>

          <div class="checkout-section">
            <h3 class="checkout-section-title"><span>3</span> Método de envío</h3>
            <div class="shipping-method-list">
              <label class="shipping-method-option selected">
                <input type="radio" name="shipping_method" value="standard" data-cost="{{ $standardShipping }}" onchange="deSolucionesRecalcShipping(this)" checked>
                <span class="shipping-method-info">
                  <strong>Envío estándar</strong>
                  <small>Incluido en el precio - 3–5 días hábiles</small>
                </span>
                <span class="shipping-method-cost">{{ $standardShipping > 0 ? $formatMoney($standardShipping) : 'Gratis' }}</span>
              </label>
              <label class="shipping-method-option">
                <input type="radio" name="shipping_method" value="express" data-cost="{{ $expressShipping }}" onchange="deSolucionesRecalcShipping(this)">
                <span class="shipping-method-info">
                  <strong>Envío exprés</strong>
                  <small>Entrega prioritaria</small>
                </span>
                <span class="shipping-method-cost">{{ $formatMoney($expressShipping) }}</span>
              </label>
            </div>
            <div class="row g-3 mt-1">
              <div class="col-12">
                <label class="form-label">Empresa de envío preferida (opcional)</label>
                <div class="courier-method-cards">
                  <label class="courier-method-card selected">
                    <input type="radio" name="preferred_courier" value="" checked>
                    <span class="courier-method-none"><i class="bi bi-shuffle"></i></span>
                    <span class="courier-method-name">Sin preferencia</span>
                  </label>
                  <label class="courier-method-card">
                    <input type="radio" name="preferred_courier" value="c807">
                    <img src="{{ asset('storefront/img/c807.png') }}" alt="C807 Express">
                  </label>
                  <label class="courier-method-card">
                    <input type="radio" name="preferred_courier" value="cargo_expreso">
                    <img src="{{ asset('storefront/img/caex.png') }}" alt="Cargo Expreso">
                  </label>
                  <label class="courier-method-card">
                    <input type="radio" name="preferred_courier" value="forza_delivery">
                    <img src="{{ asset('storefront/img/forza.png') }}" alt="Forza Delivery">
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="checkout-section">
            <h3 class="checkout-section-title"><span>4</span> Método de pago</h3>
            <div class="payment-method-cards">
              <label class="payment-method-card selected">
                <input type="radio" name="payment_method" value="cod" checked>
                <i class="bi bi-cash-coin"></i>
                <span>Contra entrega</span>
                <small>Paga en efectivo al recibir</small>
              </label>
              <label class="payment-method-card disabled">
                <input type="radio" name="payment_method" value="card" disabled>
                <i class="bi bi-credit-card"></i>
                <span>Tarjeta</span>
                <small>Próximamente</small>
              </label>
              <label class="payment-method-card disabled">
                <input type="radio" name="payment_method" value="paypal" disabled>
                <i class="bi bi-paypal"></i>
                <span>PayPal</span>
                <small>Próximamente</small>
              </label>
            </div>
          </div>

          <div class="checkout-section">
            <h3 class="checkout-section-title"><span>5</span> Notas del pedido (opcional)</h3>
            <textarea class="form-control" name="notes" rows="3" placeholder="Instrucciones especiales para la entrega...">{{ old('notes') }}</textarea>
          </div>

        </div>

        {{-- Summary column --}}
        <div class="col-lg-5">
          <div class="checkout-summary">
            <h4>Resumen del pedido</h4>

            <div class="checkout-summary-items">
              @foreach($lines as $line)
                <div class="summary-mini-item">
                  <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}">
                  <div class="summary-mini-info">
                    <p>{{ $line['name'] }}</p>
                    <span>Cantidad: {{ $line['quantity'] }}</span>
                  </div>
                  <span class="summary-mini-price">{{ $line['lineTotalFormatted'] }}</span>
                </div>
              @endforeach
            </div>

            <a href="{{ route('cart.index') }}" class="edit-cart-link"><i class="bi bi-pencil"></i> Editar carrito</a>

            <hr>

            <div class="col-12 mb-3">
              <label class="form-label" for="couponCode">¿Tienes un cupón?</label>
              <input type="text" class="form-control @error('coupon_code') is-invalid @enderror" id="couponCode" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="CÓDIGO">
              @error('coupon_code')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <p class="field-hint">El descuento se aplica al confirmar el pedido.</p>
              @enderror
            </div>

            <div class="summary-row">
              <span>Subtotal</span>
              <span id="checkoutSubtotal" data-value="{{ $summary['subtotal'] }}">{{ $summary['subtotalFormatted'] }}</span>
            </div>
            <div class="summary-row">
              <span>Envío</span>
              <span id="checkoutShippingCost" data-value="{{ $standardShipping }}">{{ $standardShipping > 0 ? $formatMoney($standardShipping) : 'Gratis' }}</span>
            </div>
            <hr>
            <div class="summary-row summary-total">
              <span>Total</span>
              <span id="checkoutTotal">{{ $formatMoney($summary['subtotal'] + $standardShipping) }}</span>
            </div>

            <label class="terms-check">
              <input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))>
              <span>Acepto los <a href="/terminos" target="_blank">términos y condiciones</a> y la <a href="/privacidad" target="_blank">política de privacidad</a></span>
            </label>

            <button type="submit" id="placeOrderBtn" class="btn-place-order">
              <i class="bi bi-lock-fill"></i> Confirmar pedido
            </button>

            <p class="checkout-secure-note"><i class="bi bi-shield-check"></i> Pago 100% seguro y protegido</p>
          </div>
        </div>

      </div>
    </form>

  </div>
</section>

@push('scripts')
<script>
  // Saved-address select: "Usar otra dirección" reveals the manual fields.
  // The required attribute has to move with it - a required field left
  // inside a display:none container silently blocks the whole form in
  // Chrome (it can't focus a hidden field to show the validation message).
  function deSolucionesToggleAddressFields(select) {
    var manual = document.getElementById('manualAddressFields');
    var usingNew = select.value === '';
    manual.style.display = usingNew ? '' : 'none';

    ['recipientName', 'addressLine1', 'addressLine2', 'addressCity', 'addressState'].forEach(function (id) {
      var field = document.getElementById(id);
      if (field) field.required = usingNew;
    });
  }

  // Recomputes the visible total when the shipping method changes. The
  // server always recomputes this for real on submit - this is display only.
  function deSolucionesRecalcShipping(radio) {
    document.querySelectorAll('.shipping-method-option').forEach(function (el) {
      el.classList.toggle('selected', el.contains(radio) || el.querySelector('input') === radio);
    });

    var cost = parseFloat(radio.dataset.cost || '0');
    var subtotal = parseFloat(document.getElementById('checkoutSubtotal').dataset.value || '0');
    var currency = '{{ $currency }}';

    var format = function (v) {
      return v > 0 ? currency + ' ' + v.toLocaleString('es-HN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Gratis';
    };

    document.getElementById('checkoutShippingCost').textContent = format(cost);
    document.getElementById('checkoutTotal').textContent = currency + ' ' + (subtotal + cost).toLocaleString('es-HN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }
</script>
@endpush

@endsection