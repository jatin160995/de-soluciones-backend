document.addEventListener('DOMContentLoaded', function () {

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      const id = this.getAttribute('href');
      if (id.length > 1) {
        const target = document.querySelector(id);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });

  // Deal countdown (resets at midnight)
  function updateCountdown() {
    const now = new Date();
    const midnight = new Date(now);
    midnight.setHours(24, 0, 0, 0);
    const diff = midnight - now;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff / 60000) % 60);
    const s = Math.floor((diff / 1000) % 60);
    const hEl = document.getElementById('cd-h');
    const mEl = document.getElementById('cd-m');
    const sEl = document.getElementById('cd-s');
    if (hEl) hEl.textContent = String(h).padStart(2, '0');
    if (mEl) mEl.textContent = String(m).padStart(2, '0');
    if (sEl) sEl.textContent = String(s).padStart(2, '0');
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);

  // Category filter tabs on best sellers
  const tabs = document.querySelectorAll('.sec-tab');
  const items = document.querySelectorAll('.product-item');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const filter = tab.getAttribute('data-filter');
      items.forEach(function (item) {
        if (filter === 'all' || item.getAttribute('data-cat') === filter) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });

  // Add to cart micro-interaction
  const cartBadge = document.querySelector('.cart-action .mini-badge');
  let cartCount = cartBadge ? parseInt(cartBadge.textContent, 10) : 0;
  document.querySelectorAll('.btn-cart').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check-lg"></i> Agregado';
      btn.disabled = true;
      cartCount++;
      if (cartBadge) cartBadge.textContent = cartCount;
      setTimeout(function () {
        btn.innerHTML = original;
        btn.disabled = false;
      }, 1200);
    });
  });

  // Wishlist toggle
  document.querySelectorAll('.product-quick-actions button[aria-label="Agregar a favoritos"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const icon = btn.querySelector('i');
      icon.classList.toggle('bi-heart');
      icon.classList.toggle('bi-heart-fill');
      btn.style.color = icon.classList.contains('bi-heart-fill') ? '#E4472A' : '';
    });
  });

  // Newsletter feedback
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = newsletterForm.querySelector('button');
      const input = newsletterForm.querySelector('input');
      const original = btn.textContent;
      btn.textContent = '¡Gracias!';
      btn.disabled = true;
      setTimeout(function () {
        btn.textContent = original;
        btn.disabled = false;
        input.value = '';
      }, 1800);
    });
  }

  // Header shadow on scroll
  const header = document.querySelector('header');
  window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 10);
  });

});

// ===== Product Detail Page (producto.html) =====

  // Gallery thumbnail swap
  const galleryMainImg = document.getElementById('galleryMainImg');
  const galleryThumbs = document.querySelectorAll('.thumb-item');
  if (galleryMainImg && galleryThumbs.length) {
    galleryThumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        galleryThumbs.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
        galleryMainImg.setAttribute('src', thumb.getAttribute('data-full'));
        const thumbImg = thumb.querySelector('img');
        if (thumbImg) galleryMainImg.setAttribute('alt', thumbImg.getAttribute('alt'));
      });
    });
  }

  // Quantity stepper
  document.querySelectorAll('.qty-stepper').forEach(function (stepper) {
    const input = stepper.querySelector('.qty-input');
    const minusBtn = stepper.querySelector('[data-action="minus"]');
    const plusBtn = stepper.querySelector('[data-action="plus"]');
    if (!input) return;
    const min = parseInt(input.getAttribute('min'), 10) || 1;
    const max = parseInt(input.getAttribute('max'), 10) || 99;
    if (minusBtn) {
      minusBtn.addEventListener('click', function () {
        const val = parseInt(input.value, 10) || min;
        if (val > min) input.value = val - 1;
      });
    }
    if (plusBtn) {
      plusBtn.addEventListener('click', function () {
        const val = parseInt(input.value, 10) || min;
        if (val < max) input.value = val + 1;
      });
    }
  });

  // Variant selectors (color swatches / presentation buttons)
  document.querySelectorAll('.variant-swatches, .variant-sizes').forEach(function (group) {
    const buttons = group.querySelectorAll('button');
    const variantGroup = group.closest('.variant-group');
    const label = variantGroup ? variantGroup.querySelector('.variant-selected-value') : null;
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (label) label.textContent = btn.getAttribute('data-value');
      });
    });
  });

  // Large wishlist button on product detail page
  document.querySelectorAll('.btn-wishlist-lg').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.classList.toggle('active');
      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-heart');
        icon.classList.toggle('bi-heart-fill');
      }
    });
  });
  // Buy Now modal (producto.html)
  const buyNowModal = document.getElementById('buyNowModal');
  if (buyNowModal) {

    buyNowModal.addEventListener('show.bs.modal', function () {
      const qtyInput = document.querySelector('.qty-stepper .qty-input');
      const qty = qtyInput ? qtyInput.value : '1';
      const colorEl = document.querySelector('.variant-swatches .swatch.active');
      const sizeEl = document.querySelector('.variant-sizes .size-btn.active');

      const qtySummary = document.getElementById('buyNowQty');
      const qtyHidden = document.getElementById('buyNowQtyInput');
      if (qtySummary) qtySummary.textContent = qty;
      if (qtyHidden) qtyHidden.value = qty;

      const colorVal = colorEl ? colorEl.getAttribute('data-value') : '';
      const sizeVal = sizeEl ? sizeEl.getAttribute('data-value') : '';
      const variantSummary = document.getElementById('buyNowVariantSummary');
      const colorHidden = document.getElementById('buyNowColorInput');
      const sizeHidden = document.getElementById('buyNowSizeInput');
      if (variantSummary) variantSummary.textContent = [colorVal, sizeVal].filter(Boolean).join(' · ');
      if (colorHidden) colorHidden.value = colorVal;
      if (sizeHidden) sizeHidden.value = sizeVal;
    });

    const buyNowForm = document.getElementById('buyNowForm');
    if (buyNowForm) {
      buyNowForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!buyNowForm.checkValidity()) {
          buyNowForm.classList.add('was-validated');
          const firstInvalid = buyNowForm.querySelector(':invalid');
          if (firstInvalid) firstInvalid.focus();
          return;
        }
        const submitBtn = document.getElementById('buyNowSubmitBtn');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Procesando pedido...';
        }
        setTimeout(function () {
          window.location.href = 'verificar-pedido.html';
        }, 900);
      });
    }
  }

  // ===== Cart Page (carrito.html) =====
  function formatCurrency(value) {
    return '$' + Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function updateCartTotals() {
    const cartItems = document.querySelectorAll('.cart-item');
    let subtotal = 0;

    cartItems.forEach(function (item) {
      const price = parseFloat(item.getAttribute('data-price')) || 0;
      const qtyInput = item.querySelector('.qty-input');
      const qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
      const lineTotal = price * qty;

      const lineTotalEl = item.querySelector('.cart-item-line-total');
      if (lineTotalEl) lineTotalEl.textContent = formatCurrency(lineTotal);

      subtotal += lineTotal;
    });

    const subtotalEl = document.getElementById('cartSubtotal');
    const totalEl = document.getElementById('cartTotal');
    const countEl = document.getElementById('cartItemCount');
    if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
    if (totalEl) totalEl.textContent = formatCurrency(subtotal);
    if (countEl) countEl.textContent = cartItems.length;

    const cartContent = document.getElementById('cartContent');
    const emptyState = document.getElementById('cartEmptyState');
    if (cartContent && emptyState) {
      if (cartItems.length === 0) {
        cartContent.style.display = 'none';
        emptyState.style.display = '';
      } else {
        cartContent.style.display = '';
        emptyState.style.display = 'none';
      }
    }

    const headerCartBadge = document.querySelector('.cart-action .mini-badge');
    if (headerCartBadge) headerCartBadge.textContent = cartItems.length;
  }

  if (document.getElementById('cartItemsList')) {
    updateCartTotals();

    // Recalculate whenever a quantity stepper is clicked inside the cart
    document.addEventListener('click', function (e) {
      if (e.target.closest('.cart-item .qty-btn')) {
        setTimeout(updateCartTotals, 0);
      }
    });

    // Recalculate on manual quantity typing
    document.querySelectorAll('.cart-item .qty-input').forEach(function (input) {
      input.addEventListener('input', function () {
        const min = parseInt(input.getAttribute('min'), 10) || 1;
        const max = parseInt(input.getAttribute('max'), 10) || 99;
        let val = parseInt(input.value, 10);
        if (isNaN(val)) return;
        if (val < min) val = min;
        if (val > max) val = max;
        input.value = val;
        updateCartTotals();
      });
    });

    // Remove item from cart
    document.querySelectorAll('.cart-item-remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const item = btn.closest('.cart-item');
        if (!item) return;
        item.style.opacity = '0';
        item.style.transform = 'translateX(12px)';
        setTimeout(function () {
          item.remove();
          updateCartTotals();
        }, 200);
      });
    });
  }

  // ===== Checkout Page (checkout.html) =====
  const checkoutForm = document.getElementById('checkoutForm');
  if (checkoutForm) {

    // Payment method cards
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const cardFieldsExtra = document.getElementById('cardFieldsExtra');
    paymentCards.forEach(function (card) {
      const radio = card.querySelector('input[type="radio"]');
      card.addEventListener('click', function () {
        paymentCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        radio.checked = true;
        if (cardFieldsExtra) {
          cardFieldsExtra.style.display = radio.value === 'card' ? '' : 'none';
        }
      });
    });

    // Shipping method + live total recalculation
    const shippingOptions = document.querySelectorAll('.shipping-method-option');
    function recalcCheckoutTotal() {
      const subtotalEl = document.getElementById('checkoutSubtotal');
      const shippingEl = document.getElementById('checkoutShippingCost');
      const totalEl = document.getElementById('checkoutTotal');
      if (!subtotalEl || !shippingEl || !totalEl) return;
      const subtotal = parseFloat(subtotalEl.getAttribute('data-value')) || 0;
      const selected = document.querySelector('.shipping-method-option input[type="radio"]:checked');
      const shippingCost = selected ? (parseFloat(selected.getAttribute('data-cost')) || 0) : 0;
      shippingEl.textContent = shippingCost === 0 ? 'Gratis' : formatCurrency(shippingCost);
      totalEl.textContent = formatCurrency(subtotal + shippingCost);
    }
    shippingOptions.forEach(function (option) {
      const radio = option.querySelector('input[type="radio"]');
      option.addEventListener('click', function () {
        shippingOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        radio.checked = true;
        recalcCheckoutTotal();
      });
    });
    recalcCheckoutTotal();

    // Submit handling (front-end only for now — no backend yet)
    checkoutForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!checkoutForm.checkValidity()) {
        checkoutForm.classList.add('was-validated');
        const firstInvalid = checkoutForm.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      const submitBtn = document.getElementById('placeOrderBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Procesando pedido...';
      }
      setTimeout(function () {
        window.location.href = 'verificar-pedido.html';
      }, 900);
    });
  }

  // OTP verification page (verificar-pedido.html)
  const otpForm = document.getElementById('otpForm');
  if (otpForm) {
    const otpInputs = Array.from(otpForm.querySelectorAll('.otp-input'));
    const otpError = document.getElementById('otpError');
    const otpTimerWrap = document.getElementById('otpTimerWrap');
    const otpTimerEl = document.getElementById('otpTimer');
    const otpResendLink = document.getElementById('otpResendLink');
    const otpSubmitBtn = document.getElementById('otpSubmitBtn');

    otpInputs.forEach(function (input, index) {
      input.addEventListener('input', function () {
        input.value = input.value.replace(/[^0-9]/g, '').slice(0, 1);
        input.classList.remove('otp-invalid');
        if (otpError) otpError.classList.remove('show');
        if (input.value && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !input.value && index > 0) {
          otpInputs[index - 1].focus();
        }
      });
      input.addEventListener('paste', function (e) {
        e.preventDefault();
        const digits = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').split('');
        otpInputs.forEach(function (el, i) { el.value = digits[i] || ''; });
        const nextEmpty = otpInputs.findIndex(function (el) { return !el.value; });
        (nextEmpty === -1 ? otpInputs[otpInputs.length - 1] : otpInputs[nextEmpty]).focus();
      });
    });

    // Resend countdown
    let secondsLeft = 60;
    const countdownInterval = setInterval(function () {
      secondsLeft -= 1;
      if (otpTimerEl) otpTimerEl.textContent = secondsLeft;
      if (secondsLeft <= 0) {
        clearInterval(countdownInterval);
        if (otpTimerWrap) otpTimerWrap.style.display = 'none';
        if (otpResendLink) {
          otpResendLink.style.display = 'inline';
          otpResendLink.classList.remove('disabled');
        }
      }
    }, 1000);

    if (otpResendLink) {
      otpResendLink.addEventListener('click', function (e) {
        e.preventDefault();
        if (otpResendLink.classList.contains('disabled')) return;
        otpResendLink.style.display = 'none';
        otpResendLink.classList.add('disabled');
        if (otpTimerWrap) otpTimerWrap.style.display = 'inline';
        secondsLeft = 60;
        if (otpTimerEl) otpTimerEl.textContent = secondsLeft;
      });
    }

    otpForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const code = otpInputs.map(function (el) { return el.value; }).join('');
      if (code.length < 6) {
        otpInputs.forEach(function (el) {
          if (!el.value) el.classList.add('otp-invalid');
        });
        if (otpError) otpError.classList.add('show');
        return;
      }
      if (otpSubmitBtn) {
        otpSubmitBtn.disabled = true;
        otpSubmitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Verificando...';
      }
      setTimeout(function () {
        window.location.href = 'confirmacion-pedido.html';
      }, 900);
    });
  }

  // Courier logo picker (checkout.html + buy-now modal on producto.html)
  document.querySelectorAll('.courier-method-cards').forEach(function (group) {
    const courierCards = group.querySelectorAll('.courier-method-card');
    courierCards.forEach(function (card) {
      const radio = card.querySelector('input[type="radio"]');
      card.addEventListener('click', function () {
        courierCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        radio.checked = true;
      });
    });
  });

  // Password show/hide toggle (login.html, registro.html)
  document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash'); }
        btn.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye'); }
        btn.setAttribute('aria-label', 'Mostrar contraseña');
      }
    });
  });

  // Login form (login.html)
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      if (!loginForm.checkValidity()) {
        e.preventDefault();
        loginForm.classList.add('was-validated');
        const firstInvalid = loginForm.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      // Valid → let the form submit natively to POST /login; the server authenticates.
      const submitBtn = loginForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Ingresando...';
      }
    });
  }

  // Register form (registro.html)
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    const regPassword = document.getElementById('registerPassword');
    const regPasswordConfirm = document.getElementById('registerPasswordConfirm');

    function checkPasswordsMatch() {
      if (!regPassword || !regPasswordConfirm) return;
      if (regPasswordConfirm.value && regPasswordConfirm.value !== regPassword.value) {
        regPasswordConfirm.setCustomValidity('mismatch');
      } else {
        regPasswordConfirm.setCustomValidity('');
      }
    }
    if (regPassword) regPassword.addEventListener('input', checkPasswordsMatch);
    if (regPasswordConfirm) regPasswordConfirm.addEventListener('input', checkPasswordsMatch);

    registerForm.addEventListener('submit', function (e) {
      checkPasswordsMatch();
      if (!registerForm.checkValidity()) {
        e.preventDefault();
        registerForm.classList.add('was-validated');
        const firstInvalid = registerForm.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      // Valid → let the form submit natively to POST /registro; the server creates the account.
      const submitBtn = registerForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Creando cuenta...';
      }
    });
  }

  // Contact form (contacto.html)
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!contactForm.checkValidity()) {
      contactForm.classList.add('was-validated');
      const firstInvalid = contactForm.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }
    const submitBtn = document.getElementById('contactSubmitBtn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enviando...';
    }
    setTimeout(function () {
      contactForm.style.display = 'none';
      const successMsg = document.getElementById('contactSuccessMsg');
      if (successMsg) successMsg.style.display = '';
    }, 900);
  });
}

// The header search form submits natively via GET to the catalog route
// (action="/catalogo", name="q"), so no JS interception is needed here.

// Password recovery page (recuperar-password.html)
const recoverForm = document.getElementById('recoverForm');
if (recoverForm) {
  const recoverFormState = document.getElementById('recoverFormState');
  const recoverSuccessState = document.getElementById('recoverSuccessState');
  const recoverEmailTarget = document.getElementById('recoverEmailTarget');
  const recoverTimerWrap = document.getElementById('recoverTimerWrap');
  const recoverTimerEl = document.getElementById('recoverTimer');
  const recoverResendLink = document.getElementById('recoverResendLink');
  let recoverCountdownInterval = null;

  function startRecoverCountdown() {
    let secondsLeft = 60;
    if (recoverTimerEl) recoverTimerEl.textContent = secondsLeft;
    if (recoverTimerWrap) recoverTimerWrap.style.display = 'inline';
    if (recoverResendLink) {
      recoverResendLink.style.display = 'none';
      recoverResendLink.classList.add('disabled');
    }
    if (recoverCountdownInterval) clearInterval(recoverCountdownInterval);
    recoverCountdownInterval = setInterval(function () {
      secondsLeft -= 1;
      if (recoverTimerEl) recoverTimerEl.textContent = secondsLeft;
      if (secondsLeft <= 0) {
        clearInterval(recoverCountdownInterval);
        if (recoverTimerWrap) recoverTimerWrap.style.display = 'none';
        if (recoverResendLink) {
          recoverResendLink.style.display = 'inline';
          recoverResendLink.classList.remove('disabled');
        }
      }
    }, 1000);
  }

  recoverForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!recoverForm.checkValidity()) {
      recoverForm.classList.add('was-validated');
      const firstInvalid = recoverForm.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }
    const submitBtn = document.getElementById('recoverSubmitBtn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enviando...';
    }
    const emailInput = document.getElementById('recoverEmail');
    setTimeout(function () {
      if (recoverEmailTarget && emailInput) recoverEmailTarget.textContent = emailInput.value;
      if (recoverFormState) recoverFormState.style.display = 'none';
      if (recoverSuccessState) recoverSuccessState.style.display = '';
      startRecoverCountdown();
    }, 900);
  });

  if (recoverResendLink) {
    recoverResendLink.addEventListener('click', function (e) {
      e.preventDefault();
      if (recoverResendLink.classList.contains('disabled')) return;
      startRecoverCountdown();
    });
  }
}