@extends('layouts.storefront')

@section('title', 'Crear Cuenta | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', 'Crea tu cuenta en DE Soluciones para guardar direcciones y ver el historial de tus pedidos.')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb-bar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb-trail">
        <li><a href="{{ route('home') }}">Inicio</a></li>
        <li class="active" aria-current="page">Crear cuenta</li>
      </ol>
    </nav>
  </div>
</div>

{{-- Registro --}}
<section class="section-pad-sm">
  <div class="container">
    <div class="auth-card auth-card-wide">
      <h2>Crear cuenta</h2>
      <p class="auth-subtext">Guarda tus direcciones y consulta el historial de tus pedidos en cualquier momento.</p>

      <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="registerName">Nombre completo</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="registerName" name="name" value="{{ old('name') }}" autocomplete="name" required>
            <div class="invalid-feedback @error('name') d-block @enderror">@error('name'){{ $message }}@else Ingresa tu nombre completo. @enderror</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="registerEmail">Correo electrónico</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="registerEmail" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required>
            <div class="invalid-feedback @error('email') d-block @enderror">@error('email'){{ $message }}@else Ingresa un correo electrónico válido. @enderror</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="registerPhone">Teléfono</label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="registerPhone" name="phone" value="{{ old('phone') }}" placeholder="+504 0000-0000" autocomplete="tel" required>
            <div class="invalid-feedback @error('phone') d-block @enderror">@error('phone'){{ $message }}@else Ingresa tu número de teléfono. @enderror</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="registerWhatsapp">Número de WhatsApp</label>
            <input type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" id="registerWhatsapp" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="+504 0000-0000" autocomplete="tel" required>
            <div class="invalid-feedback @error('whatsapp_number') d-block @enderror">@error('whatsapp_number'){{ $message }}@else Ingresa tu número de WhatsApp. @enderror</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="registerPassword">Contraseña</label>
            <div class="password-field">
              <input type="password" class="form-control @error('password') is-invalid @enderror" id="registerPassword" name="password" placeholder="Mínimo 8 caracteres" minlength="8" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="registerPassword" aria-label="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback @error('password') d-block @enderror">@error('password'){{ $message }}@else La contraseña debe tener al menos 8 caracteres. @enderror</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="registerPasswordConfirm">Confirmar contraseña</label>
            <div class="password-field">
              <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirmation" placeholder="Repite tu contraseña" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="registerPasswordConfirm" aria-label="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback" id="passwordConfirmFeedback">Las contraseñas no coinciden.</div>
          </div>
        </div>

        <label class="auth-checkbox auth-checkbox-terms mt-3">
          <input type="checkbox" name="accepts_terms" value="1" {{ old('accepts_terms') ? 'checked' : '' }} required>
          <span>Acepto los <a href="#" target="_blank">Términos y condiciones</a> y la <a href="#" target="_blank">Política de privacidad</a>.</span>
        </label>
        @error('accepts_terms')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn-place-order w-100 mt-3">
          <i class="bi bi-person-plus"></i> Crear cuenta
        </button>
      </form>

      <div class="auth-divider"><span>o</span></div>

      <p class="auth-switch">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>

      <p class="auth-guest-note">
        <i class="bi bi-bag-check"></i>
        ¿Prefieres comprar sin crear una cuenta? Puedes finalizar tu compra como invitado desde el <a href="{{ route('catalog') }}">catálogo</a>.
      </p>
    </div>
  </div>
</section>

@endsection
