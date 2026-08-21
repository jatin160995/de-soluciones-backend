@extends('layouts.storefront')

@section('content')

<section class="hero">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3 d-none d-lg-block">
        <div class="sidebar-cats">
          <div class="sidebar-cats-head">Categorías</div>
          <ul>
            @foreach($categories as $cat)
              <li><a href="/catalogo?categoria={{ $cat->slug }}">@if($cat->getFirstMediaUrl('image'))
                <img src="{{ $cat->getFirstMediaUrl('image') }}" alt="{{ $cat->name }}" class="cat-icon-img">
                @else
                <i class="bi bi-tag"></i>
                @endif {{ $cat->name }} <i class="bi bi-chevron-right ms-auto"></i></a></li>
            @endforeach
            <li><a href="/catalogo"><i class="bi bi-three-dots"></i> Ver todo <i class="bi bi-chevron-right ms-auto"></i></a></li>
          </ul>
          <div class="sidebar-promo">
            <i class="bi bi-cash-coin"></i>
            <p>Paga al recibir tu producto. Sin adelantos, sin riesgo.</p>
          </div>
        </div>
      </div>

      <div class="col-lg-9">
        {{-- <div class="hero-banner">
          <div class="hero-banner-text">
            <span class="hero-kicker">SÚPER PRECIOS · GANA MÁS POR TU DINERO</span>
            <h1>Ofertas y Promociones<br><span>en tus artículos favoritos</span></h1>
            <p>Tecnología, herramientas y bienestar con los mejores precios y pago contra entrega en toda la región.</p>
            <a href="#mas-vendidos" class="btn-shop-now">Comprar ahora <i class="bi bi-arrow-right"></i></a>
          </div>
          <img src="https://picsum.photos/seed/desol-hero2/560/460" alt="Producto destacado" class="hero-banner-img" width="560" height="460" loading="lazy">
        </div> --}}
        @if($heroBanners->isNotEmpty())
<section class="hero-slider">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      @foreach($heroBanners as $i => $banner)
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
      @endforeach
    </div>
    <div class="carousel-inner">
      @foreach($heroBanners as $i => $banner)
        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
          @if($banner->link_url)
            <a href="{{ $banner->link_url }}">
              <img src="{{ $banner->getFirstMediaUrl('image', 'banner') }}" class="d-block w-100" alt="{{ $banner->title ?? 'Banner' }}">
            </a>
          @else
            <img src="{{ $banner->getFirstMediaUrl('image', 'banner') }}" class="d-block w-100" alt="{{ $banner->title ?? 'Banner' }}">
          @endif
        </div>
      @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
</section>
@endif
      </div>
    </div>
  </div>
</section>

<section class="trust-strip">
  <div class="container">
    <div class="row g-3 g-md-4 text-center">
      <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-cash-coin"></i><span>Pago contra entrega</span></div></div>
      <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-truck"></i><span>Envío a todo el país</span></div></div>
      <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-arrow-repeat"></i><span>Cambios sin costo</span></div></div>
      <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-patch-check"></i><span>Productos garantizados</span></div></div>
    </div>
  </div>
</section>

<section id="categorias" class="section-pad">
  <div class="container">
    <div class="sec-head"><h2>Categorías</h2></div>
    <div class="row g-3 g-lg-4">
      @foreach($categories as $cat)
        <div class="col-4 col-md-2">
          <a href="/catalogo?categoria={{ $cat->slug }}" class="cat-circle">
            <div class="cat-circle-img">@if($cat->getFirstMediaUrl('image'))
            <img src="{{ $cat->getFirstMediaUrl('image') }}" alt="{{ $cat->name }}" class="cat-icon-img">
            @else
            <i class="bi bi-tag"></i>
            @endif</div>
            <span>{{ $cat->name }}</span>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section id="mas-vendidos" class="section-pad bg-alt">
  <div class="container">
    <div class="sec-head">
      <div>
        <span class="sec-kicker">LO MÁS POPULAR</span>
        <h2>Productos más vendidos</h2>
      </div>
    </div>
    <div class="row g-4 product-grid">
      @forelse($featuredProducts as $product)
        @include('partials.product-card', ['product' => $product])
      @empty
        <p class="text-muted">Aún no hay productos destacados.</p>
      @endforelse
    </div>
    <div class="text-center mt-4">
      <a href="/catalogo" class="btn-view-all">Ver todos los productos <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>

@if($dealOfTheDay)
<section id="especial" class="section-pad">
  <div class="container">
    <div class="deal-box">
      <div class="deal-flash"><i class="bi bi-lightning-charge-fill"></i> ESPECIAL DEL DÍA</div>
      <div class="row align-items-center g-4">
        <div class="col-lg-5 text-center">
          <div class="deal-img-wrap">
            <img src="{{ $dealOfTheDay->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $dealOfTheDay->name }}" width="420" height="360" loading="lazy">
          </div>
        </div>
        <div class="col-lg-7">
          <h2>{{ $dealOfTheDay->name }}</h2>
          <p class="deal-copy">{{ \Illuminate\Support\Str::limit($dealOfTheDay->description, 160) }}</p>
          <div class="deal-price-row">
            <span class="deal-price-now">{{ config('store.currency_symbol') }} {{ number_format($dealOfTheDay->discounted_price, 2) }}</span>
            <span class="deal-price-old">{{ config('store.currency_symbol') }} {{ number_format($dealOfTheDay->base_price, 2) }}</span>
            <span class="deal-save-badge">Ahorras {{ round((1 - $dealOfTheDay->discounted_price / $dealOfTheDay->base_price) * 100) }}%</span>
          </div>
          <a href="#" class="btn-shop-now mt-4">Comprar ahora <i class="bi bi-cart-plus ms-1"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<section class="section-pad-sm">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4"><div class="value-card"><i class="bi bi-award"></i><div><h3>Calidad garantizada</h3><p>Productos verificados antes de cada envío.</p></div></div></div>
      <div class="col-md-4"><div class="value-card"><i class="bi bi-truck"></i><div><h3>Entrega rápida</h3><p>Despacho en menos de 48 horas.</p></div></div></div>
      <div class="col-md-4"><div class="value-card"><i class="bi bi-chat-dots"></i><div><h3>Atención cercana</h3><p>Soporte real antes y después de tu compra.</p></div></div></div>
    </div>
  </div>
</section>

@endsection