<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'DE Soluciones | Tecnología, Herramientas y Ofertas')</title>
<meta name="description" content="@yield('meta_description', 'Tienda de tecnología con súper precios, pago contra entrega y envío rápido a todo el país.')">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('storefront/css/style.css') }}">
@stack('styles')
</head>
<body>

<div class="announce-bar">
  <div class="announce-track">
    <span><i class="bi bi-truck"></i> ENVÍO A TODO EL PAÍS</span>
    <span><i class="bi bi-cash-coin"></i> PAGO CONTRA ENTREGA</span>
    <span><i class="bi bi-lightning-charge-fill"></i> OFERTAS TODOS LOS DÍAS</span>
    <span><i class="bi bi-shield-check"></i> COMPRA 100% SEGURA</span>
    <span><i class="bi bi-truck"></i> ENVÍO A TODO EL PAÍS</span>
    <span><i class="bi bi-cash-coin"></i> PAGO CONTRA ENTREGA</span>
    <span><i class="bi bi-lightning-charge-fill"></i> OFERTAS TODOS LOS DÍAS</span>
    <span><i class="bi bi-shield-check"></i> COMPRA 100% SEGURA</span>
  </div>
</div>

<header class="sticky-top">
  <div class="header-main">
    <div class="container d-flex align-items-center gap-3 py-3">
      <button class="menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

      <a class="logo-link" href="{{ route('home') }}">
        <svg width="42" height="42" viewBox="0 0 48 48" fill="none" aria-label="DE Soluciones logo">
          <rect x="1" y="1" width="46" height="46" rx="12" fill="#FFC02E"/>
          <path d="M13 33V15h9c5.5 0 9 3.6 9 9s-3.5 9-9 9h-9Z" stroke="#141B2D" stroke-width="3" fill="none" stroke-linejoin="round"/>
          <circle cx="35" cy="13" r="3.4" fill="#141B2D"/>
        </svg>
        <span class="logo-text">DE<span class="logo-accent">SOLUCIONES</span></span>
      </a>

      <button class="cat-toggle d-none d-lg-flex" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
        <i class="bi bi-grid-fill"></i> Categorías
      </button>

      <form class="search-form flex-grow-1" role="search" action="/buscar" method="GET">
        <input type="search" name="q" class="form-control" placeholder="Buscar orilladoras, audífonos, herramientas...">
        <button type="submit"><i class="bi bi-search"></i></button>
      </form>

      <div class="header-actions d-none d-md-flex">
        <a href="/mi-cuenta" class="header-action-item">
          <i class="bi bi-person"></i>
          <span>Cuenta</span>
        </a>
        <a href="#" class="header-action-item">
          <i class="bi bi-heart"></i>
          <span>Favoritos</span>
        </a>
        {{-- Cart badge is a placeholder until the Carrito step wires a real Livewire counter --}}
        <a href="/carrito" class="header-action-item cart-action">
          <i class="bi bi-cart3"></i>
          <span>Carrito</span>
        </a>
      </div>
    </div>
  </div>

  <nav class="cat-strip d-none d-lg-block">
    <div class="container d-flex align-items-center gap-4">
      <a href="{{ route('home') }}" class="cat-strip-link {{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
      <a href="/catalogo" class="cat-strip-link">Categorías</a>
      <a href="{{ route('home') }}#mas-vendidos" class="cat-strip-link">Más vendidos</a>
      <a href="/ofertas" class="cat-strip-link">Ofertas <span class="hot-dot"></span></a>
      <a href="{{ route('home') }}#especial" class="cat-strip-link">Especial del día</a>
      <a href="{{ route('home') }}#marcas" class="cat-strip-link">Marcas</a>
      <a href="{{ route('home') }}#contacto" class="cat-strip-link">Contacto</a>
      <div class="ms-auto small-note"><i class="bi bi-headset"></i> Soporte: +00 000 0000</div>
    </div>
  </nav>
</header>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Categorías</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="mobile-cat-list">
      @foreach($categories ?? [] as $cat)
        <li><a href="/catalogo?categoria={{ $cat->slug }}">@if($cat->getFirstMediaUrl('image'))
  <img src="{{ $cat->getFirstMediaUrl('image') }}" alt="{{ $cat->name }}" class="cat-icon-img">
@else
  <i class="bi bi-tag"></i>
@endif{{ $cat->name }}</a></li>
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
          <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
            <rect x="1" y="1" width="46" height="46" rx="12" fill="#FFC02E"/>
            <path d="M13 33V15h9c5.5 0 9 3.6 9 9s-3.5 9-9 9h-9Z" stroke="#141B2D" stroke-width="3" fill="none" stroke-linejoin="round"/>
            <circle cx="35" cy="13" r="3.4" fill="#141B2D"/>
          </svg>
          <span class="logo-text text-white">DE<span class="logo-accent">SOLUCIONES</span></span>
        </a>
        <p class="footer-about">Tienda especializada en tecnología, herramientas y bienestar, con envío a todo el país y pago contra entrega.</p>
        <div class="footer-social">
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Tienda</h5>
        <ul>
          <li><a href="/catalogo">Categorías</a></li>
          <li><a href="{{ route('home') }}#mas-vendidos">Más vendidos</a></li>
          <li><a href="/ofertas">Ofertas</a></li>
          <li><a href="{{ route('home') }}#marcas">Marcas</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Ayuda</h5>
        <ul>
          <li><a href="#">Preguntas frecuentes</a></li>
          <li><a href="#">Envíos y entregas</a></li>
          <li><a href="#">Cambios y devoluciones</a></li>
          <li><a href="/terminos">Términos y condiciones</a></li>
        </ul>
      </div>
      <div class="col-lg-4 col-md-4">
        <h5>Contáctanos</h5>
        <ul>
          <li><i class="bi bi-geo-alt"></i> Ciudad, País</li>
          <li><i class="bi bi-telephone"></i> +00 000 000 0000</li>
          <li><i class="bi bi-envelope"></i> contacto@de-soluciones.com</li>
        </ul>
      </div>
    </div>
    <hr>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} DE Soluciones. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storefront/js/script.js') }}"></script>
@stack('scripts')
</body>
</html>