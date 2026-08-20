@php
    $hasDiscount = $product->discounted_price !== null && $product->discounted_price < $product->base_price;
    $displayPrice = $hasDiscount ? $product->discounted_price : $product->base_price;
    $pct = $hasDiscount ? round((1 - ($product->discounted_price / $product->base_price)) * 100) : null;
    $symbol = config('store.currency_symbol');
@endphp
<div class="col-6 col-md-4 col-lg-3 product-item">
  <div class="product-card">
    <div class="product-media">
      @if($hasDiscount)
        <span class="ribbon ribbon-sale">-{{ $pct }}%</span>
      @elseif($product->is_featured)
        <span class="ribbon ribbon-best">MÁS VENDIDO</span>
      @endif
      <div class="product-quick-actions">
        <button aria-label="Agregar a favoritos"><i class="bi bi-heart"></i></button>
        <button aria-label="Vista rápida"><i class="bi bi-eye"></i></button>
      </div>
      <a href="#">
        <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $product->name }}" width="380" height="320" loading="lazy">
      </a>
    </div>
    <div class="product-info">
      <span class="product-cat">{{ $product->category?->name }}</span>
      <h3>{{ $product->name }}</h3>
      <div class="price-row">
        <span class="price">{{ $symbol }} {{ number_format($displayPrice, 2) }}</span>
        @if($hasDiscount)
          <span class="price-old">{{ $symbol }} {{ number_format($product->base_price, 2) }}</span>
          <span class="off-tag">-{{ $pct }}%</span>
        @endif
      </div>
      <button class="btn-cart" data-product-id="{{ $product->id }}"><i class="bi bi-cart-plus"></i> Agregar</button>
    </div>
  </div>
</div>