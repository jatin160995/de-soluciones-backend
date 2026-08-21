@extends('layouts.storefront')

@section('title', 'Iniciar Sesión | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Inicia sesión en tu cuenta de DE Soluciones para ver tu historial de pedidos y direcciones guardadas.')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li class="active" aria-current="page">Iniciar sesión</li>
      </ol>
    </nav>
  </div>
</div>

{{-- Login --}}
<section class="section-pad-sm">
  <div class="container">
    <div class="auth-card">
      <h2>Iniciar sesión</h2>
      <p class="auth-subtext">Ingresa a tu cuenta para ver tu historial de pedidos y direcciones guardadas.</p>

      <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
          <label class="form-label" for="loginIdentifier">Correo electrónico o teléfono</label>
          <input type="text" class="form-control @error('identifier') is-invalid @enderror" id="loginIdentifier" name="identifier" value="{{ old('identifier') }}" placeholder="tucorreo@ejemplo.com" autocomplete="username" required autofocus>
          <div class="invalid-feedback @error('identifier') d-block @enderror">@error('identifier'){{ $message }}@else Ingresa tu correo electrónico o número de teléfono. @enderror</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="loginPassword">Contraseña</label>
          <div class="password-field">
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="loginPassword" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
            <button type="button" class="password-toggle-btn" data-target="loginPassword" aria-label="Mostrar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="invalid-feedback @error('password') d-block @enderror">@error('password'){{ $message }}@else Ingresa tu contraseña. @enderror</div>
        </div>

        <div class="auth-row">
          <label class="auth-checkbox">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>Recordarme</span>
          </label>
          <a href="#" class="auth-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-place-order w-100 mt-3">
          <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
        </button>
      </form>

      <div class="auth-divider"><span>o</span></div>

      <p class="auth-switch">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>

      <p class="auth-guest-note">
        <i class="bi bi-bag-check"></i>
        ¿Prefieres comprar sin crear una cuenta? Puedes finalizar tu compra como invitado desde el <a href="{{ route('catalog') }}">catálogo</a>.
      </p>
    </div>
  </div>
</section>

@endsection
