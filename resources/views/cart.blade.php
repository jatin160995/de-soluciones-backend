@extends('layouts.storefront')

@section('title', 'Carrito de Compras | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Revisa los productos en tu carrito antes de finalizar tu compra con pago contra entrega.')

@section('content')

{{--
  Ported from carrito.html. Every price on this page is a string the server
  already formatted (CartService::formatMoney) — the browser never multiplies a
  unit price, it only swaps in the strings the cart endpoints send back.
--}}

@php
  $isEmpty = $lines->isEmpty();
@endphp

{{-- Breadcrumb --}}
<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li class="active" aria-current="page">Carrito</li>
      </ol>
    </nav>
  </div>
</div>

{{-- Cart --}}
<section class="section-pad-sm">
  <div class="container">

    <div class="cart-page-head">
      <h1>Tu Carrito de Compras</h1>
      <p>
        <span id="cartItemCount">{{ $summary['lineCount'] }}</span>
        <span id="cartItemCountLabel">{{ $summary['lineCount'] === 1 ? 'producto' : 'productos' }}</span>
        en tu carrito
      </p>
    </div>

    {{--
      Two notices: the server one fires when lines() had to drop something on
      load, the JS one when an endpoint corrects a quantity mid-session.
    --}}
    @if($notice)
      <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ $notice }}
      </div>
    @endif

    <div id="cartNotice" class="alert alert-warning align-items-center gap-2" role="alert" style="display:none;">
      <i class="bi bi-exclamation-triangle-fill"></i> <span id="cartNoticeText"></span>
    </div>

    <div id="cartContent" class="row g-4" @if($isEmpty) style="display:none;" @endif>

      {{-- Cart items --}}
      <div class="col-lg-8">
        <div class="cart-items-list" id="cartItemsList">

          @foreach($lines as $line)
            <div class="cart-item" data-item-id="{{ $line['id'] }}">

              <a href="{{ $line['url'] }}" class="cart-item-img">
                <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}" width="96" height="96" loading="lazy">
              </a>

              <div class="cart-item-body">
                <div class="cart-item-top">
                  <div>
                    @if($line['category'])
                      <span class="product-cat">{{ $line['category'] }}</span>
                    @endif
                    <h3><a href="{{ $line['url'] }}">{{ $line['name'] }}</a></h3>
                    @if($line['variantLabel'])
                      <p class="cart-item-variant">{{ $line['variantLabel'] }}</p>
                    @endif
                  </div>
                  <button type="button" class="cart-item-remove" data-cart-remove aria-label="Eliminar {{ $line['name'] }}">
                    <i class="bi bi-trash3"></i>
                  </button>
                </div>

                <div class="cart-item-bottom">
                  {{-- max comes from the variant's stock; 99 when the product has no variants --}}
                  <div class="qty-stepper">
                    <button type="button" class="qty-btn" data-action="minus" aria-label="Disminuir cantidad"><i class="bi bi-dash"></i></button>
                    <input type="number" class="qty-input" value="{{ $line['quantity'] }}" min="1" max="{{ $line['maxQuantity'] }}" aria-label="Cantidad de {{ $line['name'] }}">
                    <button type="button" class="qty-btn" data-action="plus" aria-label="Aumentar cantidad"><i class="bi bi-plus"></i></button>
                  </div>

                  <div class="cart-item-price">
                    <span class="cart-item-unit-price">{{ $line['unitPriceFormatted'] }} c/u</span>
                    <span class="cart-item-line-total">{{ $line['lineTotalFormatted'] }}</span>
                  </div>
                </div>
              </div>

            </div>
          @endforeach

        </div>

        <div class="cart-actions-row">
          <a href="{{ route('catalog') }}" class="btn-continue-shopping"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
        </div>
      </div>

      {{-- Summary --}}
      <div class="col-lg-4">
        <div class="cart-summary">
          <h4>Resumen del pedido</h4>

          <div class="summary-row">
            <span>Subtotal</span>
            <span id="cartSubtotal">{{ $summary['subtotalFormatted'] }}</span>
          </div>
          <div class="summary-row">
            <span>Envío</span>
            <span class="summary-muted">Se calcula en el pago</span>
          </div>
          <hr>
          {{-- No shipping or coupon yet, so the total is the subtotal; checkout owns both --}}
          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="cartTotal">{{ $summary['subtotalFormatted'] }}</span>
          </div>

          {{-- /checkout is the next step and is not built yet --}}
          <a href="/checkout" class="btn-checkout">Proceder al pago <i class="bi bi-arrow-right"></i></a>

          <div class="summary-payment-icons">
            <span><i class="bi bi-cash-coin"></i> Contra entrega</span>
            <span><i class="bi bi-credit-card"></i> Tarjeta</span>
            <span><i class="bi bi-paypal"></i> PayPal</span>
          </div>

          <div class="summary-trust">
            <p><i class="bi bi-shield-check"></i> Compra 100% segura</p>
            <p><i class="bi bi-arrow-repeat"></i> Cambios sin costo hasta 15 días</p>
          </div>
        </div>
      </div>

    </div>

    {{-- Shown when the cart starts empty, or once the last line is removed --}}
    <div id="cartEmptyState" class="cart-empty-state" @unless($isEmpty) style="display:none;" @endunless>
      <i class="bi bi-cart-x"></i>
      <h3>Tu carrito está vacío</h3>
      <p>Explora nuestro catálogo y encuentra productos para ti.</p>
      <a href="{{ route('catalog') }}" class="btn-view-all">Ir al catálogo</a>
    </div>

  </div>
</section>

@endsection
