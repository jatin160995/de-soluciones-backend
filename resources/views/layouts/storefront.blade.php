<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
{{-- The cart fetch calls read this instead of hunting for a hidden form field --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', ($siteName ?? 'DE Soluciones') . ' | Tecnología, Herramientas y Ofertas')</title>
<meta name="description" content="@yield('meta_description', 'Tienda de tecnología con súper precios, pago contra entrega y envío rápido a todo el país.')">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('storefront/css/style.css') }}">
@stack('styles')
</head>
{{--
  Cart endpoints live on the body so script.js never hard-codes a URL — the
  paths are Spanish and could be renamed from routes/web.php alone.
--}}
<body
  data-cart-add-url="{{ route('cart.store') }}"
  data-cart-item-url="{{ url('/carrito/items') }}"
  data-cart-summary-url="{{ route('cart.summary') }}"
  data-cart-url="{{ route('cart.index') }}"
>

@if(!empty($announcementItems))
<div class="announce-bar">
  <div class="announce-track">
    {{-- Items rendered twice so the CSS marquee (translateX -50%) loops seamlessly --}}
    @foreach(array_merge($announcementItems, $announcementItems) as $a)
      <span><i class="bi {{ $a['icon'] ?? 'bi-star' }}"></i> {{ $a['text'] ?? '' }}</span>
    @endforeach
  </div>
</div>
@endif

<header class="sticky-top">
  <div class="header-main">
    <div class="container d-flex align-items-center  justify-content-between gap-3 py-3">
      <button class="menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

      <a class="logo-link" href="{{ route('home') }}">
        @if(!empty($logoUrl))
          <img src="{{ $logoUrl }}" alt="{{ $siteName ?? 'DE Soluciones' }}" class="logo-img">
        @else
          <svg width="42" height="42" viewBox="0 0 48 48" fill="none" aria-label="{{ $siteName ?? 'DE Soluciones' }} logo">
            <rect x="1" y="1" width="46" height="46" rx="12" fill="#FFC02E"/>
            <path d="M13 33V15h9c5.5 0 9 3.6 9 9s-3.5 9-9 9h-9Z" stroke="#141B2D" stroke-width="3" fill="none" stroke-linejoin="round"/>
            <circle cx="35" cy="13" r="3.4" fill="#141B2D"/>
          </svg>
          <span class="logo-text">DE<span class="logo-accent">SOLUCIONES</span></span>
        @endif
      </a>

      <button class="cat-toggle d-none d-lg-flex" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
        <i class="bi bi-grid-fill"></i> Menú
      </button>

      {{-- Search submits to the catalog, which handles ?q= alongside its own filters/sort --}}
      <form class="search-form flex-grow-1" role="search" action="{{ route('catalog') }}" method="GET">
        <label class="visually-hidden" for="headerSearch">Buscar productos</label>
        <input type="search" id="headerSearch" name="q" class="form-control" value="{{ request('q') }}" placeholder="Buscar orilladoras, audífonos, herramientas..." autocomplete="off" maxlength="80">
        <button type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
      </form>

      <div class="header-actions d-none d-md-flex">
        @php $onAccount = request()->routeIs('account.*'); @endphp
        @guest
          <a href="{{ route('login') }}" class="header-action-item">
            <i class="bi bi-person"></i>
            <span>Cuenta</span>
          </a>
        @else
          {{-- Signed in: the Cuenta button becomes the entry point to /mi-cuenta --}}
          <a href="{{ route('account.index') }}" class="header-action-item {{ $onAccount ? 'active' : '' }}" title="{{ Auth::user()->name }}">
            <i class="bi {{ $onAccount ? 'bi-person-fill' : 'bi-person-check' }}"></i>
            <span>{{ explode(' ', trim(Auth::user()->name))[0] }}</span>
          </a>
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="header-action-item header-action-btn">
              <i class="bi bi-box-arrow-right"></i>
              <span>Salir</span>
            </button>
          </form>
        @endguest
        <a href="#" class="header-action-item">
          <i class="bi bi-heart"></i>
          <span>Favoritos</span>
        </a>
        {{--
          Badge is server-rendered from StorefrontComposer, so the count is
          correct on first paint and on plain navigation. script.js only creates
          or removes the node when the count crosses zero.
        --}}
        <a href="{{ route('cart.index') }}" class="header-action-item cart-action {{ request()->routeIs('cart.*') ? 'active' : '' }}">
          <i class="bi bi-cart3"></i>
          <span>Carrito</span>
          @if(($cartCount ?? 0) > 0)
            <span class="mini-badge">{{ $cartCount }}</span>
          @endif
        </a>
      </div>
    </div>
  </div>

  <nav class="cat-strip d-none d-lg-block">
    <div class="container d-flex align-items-center gap-4">
      @php
        $curPath = '/' . trim(request()->path(), '/');
        $curPath = $curPath === '/' ? '/' : rtrim($curPath, '/');
      @endphp
      @foreach($headerMenu ?? [] as $item)
        @php
          $mUrl    = $item['url'] ?? '#';
          $mAnchor = str_contains($mUrl, '#');
          $mPath   = parse_url($mUrl, PHP_URL_PATH) ?: '/';
          $mPath   = $mPath === '/' ? '/' : '/' . trim($mPath, '/');
          $mActive = ! $mAnchor && $mPath === $curPath;
        @endphp
        <a href="{{ url($mUrl) }}" class="cat-strip-link {{ $mActive ? 'active' : '' }}">{{ $item['label'] ?? '' }}@if(!empty($item['hot'])) <span class="hot-dot"></span>@endif</a>
      @endforeach
      @if(!empty($contact['support_phone']))
        <div class="ms-auto small-note"><i class="bi bi-headset"></i> Soporte: {{ $contact['support_phone'] }}</div>
      @endif
    </div>
  </nav>
</header>

{{-- <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Categorías</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="mobile-cat-list">
      @foreach($navCategories ?? [] as $cat)
        <li><a href="{{ url('/catalogo?categoria=' . $cat->slug) }}">@if($cat->getFirstMediaUrl('image'))
  <img src="{{ $cat->getFirstMediaUrl('image') }}" alt="{{ $cat->name }}" class="cat-icon-img">
@else
  <i class="bi bi-tag"></i>
@endif{{ $cat->name }}</a></li>
      @endforeach
      <li><a href="/ofertas"><i class="bi bi-fire"></i> Ofertas</a></li>
    </ul>
  </div>
</div> --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">

    {{-- ===== Account / Cart actions — mobile only ===== --}}
    <div class="mobile-user-actions">
      @guest
        <a href="{{ route('login') }}" class="mobile-user-action-btn">
          <i class="bi bi-person"></i>
          <span>Iniciar sesión</span>
        </a>
      @else
        @php $onAccount = request()->routeIs('account.*'); @endphp
        <a href="{{ route('account.index') }}" class="mobile-user-action-btn {{ $onAccount ? 'active' : '' }}">
          <i class="bi {{ $onAccount ? 'bi-person-fill' : 'bi-person-check' }}"></i>
          <span>{{ explode(' ', trim(Auth::user()->name))[0] }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
          @csrf
          <button type="submit" class="mobile-user-action-btn">
            <i class="bi bi-box-arrow-right"></i>
            <span>Salir</span>
          </button>
        </form>
      @endguest

      <a href="#" class="mobile-user-action-btn">
        <i class="bi bi-heart"></i>
        <span>Favoritos</span>
      </a>

      <a href="{{ route('cart.index') }}" class="mobile-user-action-btn {{ request()->routeIs('cart.*') ? 'active' : '' }}">
        <i class="bi bi-cart3"></i>
        <span>Carrito</span>
        @if(($cartCount ?? 0) > 0)
          <span class="mobile-cart-badge">{{ $cartCount }}</span>
        @endif
      </a>
    </div>
    {{-- ===== Header nav menu links — mobile only ===== --}}
    @if(!empty($headerMenu))
      <div class="mobile-menu-divider">
        <span>Menú</span>
      </div>
      <ul class="mobile-nav-menu">
        @php
          $curPath = '/' . trim(request()->path(), '/');
          $curPath = $curPath === '/' ? '/' : rtrim($curPath, '/');
        @endphp
        @foreach($headerMenu as $item)
          @php
            $mUrl    = $item['url'] ?? '#';
            $mAnchor = str_contains($mUrl, '#');
            $mPath   = parse_url($mUrl, PHP_URL_PATH) ?: '/';
            $mPath   = $mPath === '/' ? '/' : '/' . trim($mPath, '/');
            $mActive = ! $mAnchor && $mPath === $curPath;
          @endphp
          <li>
            <a href="{{ url($mUrl) }}" class="{{ $mActive ? 'active' : '' }}">
              {{ $item['label'] ?? '' }}
              @if(!empty($item['hot']))<span class="hot-dot"></span>@endif
            </a>
          </li>
        @endforeach
        @if(!empty($contact['support_phone']))
          <li class="mobile-nav-support">
            <i class="bi bi-headset"></i> Soporte: {{ $contact['support_phone'] }}
          </li>
        @endif
      </ul>
    @endif

    <div class="mobile-menu-divider">
      <span>Categorías</span>
    </div>

    {{-- ===== Categories ===== --}}
    <ul class="mobile-cat-list">
      @foreach($navCategories ?? [] as $cat)
        <li>
          <a href="{{ url('/catalogo?categoria=' . $cat->slug) }}">
            @if($cat->getFirstMediaUrl('image'))
              <img src="{{ $cat->getFirstMediaUrl('image') }}" alt="{{ $cat->name }}" class="cat-icon-img">
            @else
              <i class="bi bi-tag"></i>
            @endif
            {{ $cat->name }}
          </a>
        </li>
      @endforeach
      <li><a href="/ofertas"><i class="bi bi-fire"></i> Ofertas</a></li>
    </ul>

  </div>
</div>

@yield('content')

<footer id="contacto" class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <a class="logo-link mb-3" href="{{ route('home') }}">
          @if(!empty($logoUrl))
            <img src="{{ $logoUrl }}" alt="{{ $siteName ?? 'DE Soluciones' }}" class="logo-img">
          @else
            <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
              <rect x="1" y="1" width="46" height="46" rx="12" fill="#FFC02E"/>
              <path d="M13 33V15h9c5.5 0 9 3.6 9 9s-3.5 9-9 9h-9Z" stroke="#141B2D" stroke-width="3" fill="none" stroke-linejoin="round"/>
              <circle cx="35" cy="13" r="3.4" fill="#141B2D"/>
            </svg>
            <span class="logo-text text-white">DE<span class="logo-accent">SOLUCIONES</span></span>
          @endif
        </a>
        @if(!empty($footerAbout))
          <p class="footer-about">{{ $footerAbout }}</p>
        @endif
        @php
          $socialMeta = [
            'instagram' => ['icon' => 'bi-instagram', 'label' => 'Instagram'],
            'facebook'  => ['icon' => 'bi-facebook',  'label' => 'Facebook'],
            'whatsapp'  => ['icon' => 'bi-whatsapp',  'label' => 'WhatsApp'],
            'tiktok'    => ['icon' => 'bi-tiktok',    'label' => 'TikTok'],
          ];
        @endphp
        <div class="footer-social">
          @foreach($socialMeta as $key => $meta)
            @if(!empty($socialLinks[$key]))
              <a href="{{ $socialLinks[$key] }}" aria-label="{{ $meta['label'] }}"><i class="bi {{ $meta['icon'] }}"></i></a>
            @endif
          @endforeach
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>{{ $footerTienda['heading'] ?? 'Tienda' }}</h5>
        <ul>
          @foreach($footerTienda['items'] ?? [] as $li)
            <li><a href="{{ url($li['url'] ?? '#') }}">{{ $li['label'] ?? '' }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>{{ $footerAyuda['heading'] ?? 'Ayuda' }}</h5>
        <ul>
          @foreach($footerAyuda['items'] ?? [] as $li)
            <li><a href="{{ url($li['url'] ?? '#') }}">{{ $li['label'] ?? '' }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-lg-4 col-md-4">
        <h5>Contáctanos</h5>
        <ul>
          @if(!empty($contact['address']))<li><i class="bi bi-geo-alt"></i> {{ $contact['address'] }}</li>@endif
          @if(!empty($contact['phone']))<li><i class="bi bi-telephone"></i> {{ $contact['phone'] }}</li>@endif
          @if(!empty($contact['email']))<li><i class="bi bi-envelope"></i> {{ $contact['email'] }}</li>@endif
        </ul>
      </div>
    </div>
    <hr>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} {{ $siteName ?? 'DE Soluciones' }}. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>
{{-- ===== Floating cart button — mobile only ===== --}}
<a href="{{ route('cart.index') }}" class="floating-cart-btn d-lg-none cart-action" aria-label="Ver carrito">
  <i class="bi bi-cart3"></i>
  @if(($cartCount ?? 0) > 0)
    <span class="floating-cart-badge mini-badge" id="floatingCartBadge">{{ $cartCount }}</span>
  @else
    <span class="floating-cart-badge" id="floatingCartBadge" style="display:none;">0</span>
  @endif
</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storefront/js/script.js') }}"></script>
@stack('scripts')
</body>
</html>
