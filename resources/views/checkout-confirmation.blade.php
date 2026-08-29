@extends('layouts.storefront')

@section('title', 'Pedido Confirmado | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Tu pedido ha sido confirmado. Gracias por comprar en DE Soluciones.')

@section('content')

{{--
  Ported from confirmacion-pedido.html, with the "Verificación" step removed
  from the stepper - no OTP step exists (Daniel: "checkout como invitado sin
  OTP"), so this page follows directly after "Datos y pago".
--}}

@php
  $snapshot = $order->shipping_snapshot ?? [];
  $currency = config('store.currency_symbol', 'L.');
  $formatMoney = fn ($v) => $currency . ' ' . number_format((float) $v, 2);

  $courierNames = [
    'c807'           => 'C807 Express',
    'cargo_expreso'  => 'Cargo Expreso',
    'forza_delivery' => 'Forza Delivery',
  ];
@endphp

<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li class="active" aria-current="page">Confirmación</li>
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
      <div class="step-item completed">
        <span class="step-circle"><i class="bi bi-check-lg"></i></span>
        <span class="step-label">Datos y pago</span>
      </div>
      <div class="step-line"></div>
      <div class="step-item completed">
        <span class="step-circle"><i class="bi bi-check-lg"></i></span>
        <span class="step-label">Confirmación</span>
      </div>
    </div>

    <div class="confirmation-hero">
      <div class="confirmation-icon"><i class="bi bi-check-lg"></i></div>
      <h2>¡Gracias por tu compra!</h2>
      <p class="confirmation-subtext">Tu pedido fue recibido y pronto un asesor te contactará para confirmarlo.</p>
      <div class="confirmation-order-number">
        Pedido <strong>#{{ $order->order_number }}</strong>
      </div>

      <div class="confirmation-actions">
        @if(!empty($snapshot['whatsapp_number']) || $order->customer_phone)
          <a href="https://wa.me/{{ preg_replace('/\D/', '', $snapshot['whatsapp_number'] ?? $order->customer_phone) }}" target="_blank" rel="noopener" class="btn-whatsapp">
            <i class="bi bi-whatsapp"></i> Escríbenos por WhatsApp
          </a>
        @endif
        <a href="{{ route('catalog') }}" class="btn-outline-navy">
          <i class="bi bi-bag"></i> Seguir comprando
        </a>
      </div>
    </div>

    <div class="row g-4 confirmation-details">

      {{-- Delivery recap --}}
      <div class="col-lg-7">
        <div class="delivery-recap">
          <h4><i class="bi bi-truck"></i> Datos de entrega</h4>
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Nombre</span>
            <span>{{ $order->customer_name }}</span>
          </div>
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Teléfono</span>
            <span>{{ $order->customer_phone }}</span>
          </div>
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Dirección</span>
            <span>{{ $snapshot['line1'] ?? '' }}@if(!empty($snapshot['line2'])), {{ $snapshot['line2'] }}@endif</span>
          </div>
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Ciudad / Departamento</span>
            <span>{{ $snapshot['city'] ?? '' }}@if(!empty($snapshot['state'])), {{ $snapshot['state'] }}@endif</span>
          </div>
          @if(!empty($snapshot['preferred_courier']) && isset($courierNames[$snapshot['preferred_courier']]))
            <div class="delivery-recap-row">
              <span class="delivery-recap-label">Empresa de envío</span>
              <span>{{ $courierNames[$snapshot['preferred_courier']] }}</span>
            </div>
          @endif
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Método de envío</span>
            <span>{{ ($snapshot['shipping_method'] ?? 'standard') === 'express' ? 'Exprés' : 'Estándar' }}</span>
          </div>
          <div class="delivery-recap-row">
            <span class="delivery-recap-label">Método de pago</span>
            <span>Contra entrega (efectivo)</span>
          </div>
          <p class="delivery-recap-note"><i class="bi bi-info-circle"></i> Un asesor te llamará o escribirá por WhatsApp para confirmar la entrega.</p>
        </div>
      </div>

      {{-- Order summary --}}
      <div class="col-lg-5">
        <div class="checkout-summary">
          <h4>Resumen del pedido</h4>

          <div class="checkout-summary-items">
            @foreach($order->items as $item)
              <div class="summary-mini-item">
                <div class="summary-mini-info">
                  <p>{{ $item->product_name }}</p>
                  <span>Cantidad: {{ $item->quantity }}</span>
                </div>
                <span class="summary-mini-price">{{ $formatMoney($item->line_total) }}</span>
              </div>
            @endforeach
          </div>

          <hr>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>{{ $formatMoney($order->subtotal) }}</span>
          </div>
          @if((float) $order->discount_amount > 0)
            <div class="summary-row">
              <span>Descuento</span>
              <span>- {{ $formatMoney($order->discount_amount) }}</span>
            </div>
          @endif
          <div class="summary-row">
            <span>Envío</span>
            <span>{{ (float) $order->shipping_cost > 0 ? $formatMoney($order->shipping_cost) : 'Gratis' }}</span>
          </div>
          <hr>
          <div class="summary-row summary-total">
            <span>Total</span>
            <span>{{ $formatMoney($order->total) }}</span>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>

@endsection