/*
 * =============================================================================
 * Cart — shared helpers
 * =============================================================================
 *
 * The server owns the cart. Every endpoint in App\Http\Controllers\CartController
 * answers with money strings it has already formatted, so nothing below ever
 * multiplies a price or reformats a currency — it only writes the server's
 * strings into the DOM.
 *
 * Works for guests: identity comes from the `cart_token` cookie the first add
 * sets (see App\Services\CartService), not from a login.
 */

// Endpoints come off <body data-cart-*> so the Spanish paths stay renameable
// from routes/web.php alone. The fallbacks keep this file from throwing at parse
// time if the tag is ever moved into <head>, where document.body is still null.
const cartBodyData = (document.body && document.body.dataset) || {};

const cartEndpoints = {
  add: cartBodyData.cartAddUrl || '/carrito/agregar',
  item: cartBodyData.cartItemUrl || '/carrito/items',
  summary: cartBodyData.cartSummaryUrl || '/carrito/resumen',
};

function cartCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

/*
 * Resolves to { ok, status, data }. A 422 is an expected answer here (out of
 * stock, quantity out of range), not a failure to handle in .catch() — only a
 * dropped connection lands there.
 */
function cartRequest(url, method, payload) {
  return fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': cartCsrfToken(),
    },
    credentials: 'same-origin',
    body: payload ? JSON.stringify(payload) : undefined,
  }).then(function (response) {
    return response
      .json()
      .catch(function () { return {}; })
      .then(function (data) {
        return { ok: response.ok, status: response.status, data: data };
      });
  });
}

/*
 * Header badge. The layout only renders the node when the count is above zero,
 * so this creates and removes it as the count crosses that line.
 */
function updateCartBadge(cart) {
  const link = document.querySelector('.cart-action');
  if (!link || !cart) return;

  const count = parseInt(cart.lineCount, 10) || 0;
  let badge = link.querySelector('.mini-badge');

  if (count <= 0) {
    if (badge) badge.remove();
    return;
  }

  if (!badge) {
    badge = document.createElement('span');
    badge.className = 'mini-badge';
    link.appendChild(badge);
  }

  badge.textContent = count;
}

/*
 * CartController sends { ok: false, message }; Laravel's own validator sends
 * { message, errors }. Both expose `message`, so one reader covers each.
 */
function cartErrorMessage(result) {
  return (result.data && result.data.message)
    || 'No se pudo actualizar el carrito. Intenta de nuevo.';
}

/*
 * Server messages are full sentences ("Solo quedan 2 unidades...") and don't fit
 * inside a grid card's button, so they surface as a Bootstrap toast. Built on
 * demand — the CDN Bootstrap CSS/JS the layout already loads supplies both the
 * styling and the dismiss behaviour, so this needs no storefront CSS.
 */
function cartToast(message, variant) {
  let host = document.getElementById('cartToastHost');

  if (!host) {
    host = document.createElement('div');
    host.id = 'cartToastHost';
    host.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    host.style.zIndex = '1090';
    document.body.appendChild(host);
  }

  const toast = document.createElement('div');
  toast.className = 'toast align-items-center text-white bg-' + (variant || 'dark') + ' border-0 show';
  toast.setAttribute('role', 'status');
  toast.innerHTML =
    '<div class="d-flex">'
    + '<div class="toast-body"></div>'
    + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>'
    + '</div>';
  toast.querySelector('.toast-body').textContent = message;

  host.appendChild(toast);
  setTimeout(function () { toast.remove(); }, 4500);
}

/*
 * Variant resolution (product page only).
 *
 * product.blade.php emits a signature → { id, stock, price } map. The signature
 * is every rendered variant group in DOM order, lower-cased:
 *
 *   processor=m1|size=13"|color=mid night
 *
 * `overrideGroup`/`overrideValue` exist because the click handler that adds
 * .active to a .size-btn is registered after this one, so on the click itself
 * the DOM hasn't caught up yet.
 */
function cartVariantMap() {
  const node = document.getElementById('productVariants');
  if (!node) return null;

  try {
    return JSON.parse(node.textContent);
  } catch (e) {
    return null;
  }
}

function cartVariantSelection(overrideGroup, overrideValue) {
  const groups = document.querySelectorAll('.variant-group[data-variant-group]');
  const parts = [];
  let complete = groups.length > 0;

  groups.forEach(function (group) {
    const key = group.dataset.variantGroup;
    let value;

    if (overrideGroup !== undefined && overrideGroup === key) {
      value = overrideValue;
    } else {
      const active = group.querySelector('.size-btn.active[data-value]');
      value = active ? active.dataset.value : null;
    }

    if (value === null || value === undefined) complete = false;
    parts.push(key + '=' + String(value || '').toLowerCase());
  });

  return { signature: parts.join('|'), complete: complete };
}

function cartResolveVariant(button, overrideGroup, overrideValue) {
  // A product whose variants carry no attributes renders no groups to pick
  // from, so the button names the only variant outright.
  if (button && button.dataset.variantId) {
    return { id: button.dataset.variantId, stock: null };
  }

  const map = cartVariantMap();
  if (!map) return null;

  const selection = cartVariantSelection(overrideGroup, overrideValue);
  if (!selection.complete) return null;

  return map[selection.signature] || null;
}

/*
 * A back-button restore can serve a page from the bfcache with a badge that was
 * accurate when the page was cached and isn't any more.
 */
window.addEventListener('pageshow', function (event) {
  if (!event.persisted) return;

  cartRequest(cartEndpoints.summary, 'GET')
    .then(function (result) {
      if (result.ok) updateCartBadge(result.data.cart);
    })
    .catch(function () { /* stale badge is not worth an error */ });
});


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

  /*
   * Add to cart.
   *
   * Bound to [data-add-to-cart], not .btn-cart: a product with variants renders
   * an <a class="btn-cart">Ver opciones</a> that should just navigate to the
   * product page, since nobody should be shipped a colour they never picked.
   */
  document.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (btn.disabled) return;

      // A grid card has no stepper, so it always adds one.
      const scope = btn.closest('.purchase-row, .product-info');
      const qtyInput = scope ? scope.querySelector('.qty-stepper .qty-input') : null;
      const quantity = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

      let variantId = null;

      if (btn.dataset.hasVariants === '1') {
        const variant = cartResolveVariant(btn);

        if (!variant) {
          // Attribute values are collected across all variants, so a rendered
          // combination isn't necessarily one that exists.
          cartToast(cartVariantSelection().complete
            ? 'Esa combinación no está disponible.'
            : 'Elige una opción antes de agregar al carrito.');
          return;
        }

        variantId = variant.id;
      }

      const original = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Agregando...';

      cartRequest(cartEndpoints.add, 'POST', {
        product_id: btn.dataset.productId,
        variant_id: variantId,
        quantity: quantity,
      })
        .then(function (result) {
          if (!result.ok) {
            btn.innerHTML = original;
            btn.disabled = false;
            cartToast(cartErrorMessage(result), 'danger');
            return;
          }

          updateCartBadge(result.data.cart);

          btn.innerHTML = '<i class="bi bi-check-lg"></i> Agregado';
          setTimeout(function () {
            btn.innerHTML = original;
            btn.disabled = false;
          }, 1200);
        })
        .catch(function () {
          btn.innerHTML = original;
          btn.disabled = false;
          cartToast('Sin conexión. Intenta de nuevo.', 'danger');
        });
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
  //
  // min/max are read on each click rather than captured at bind time, because
  // both move at runtime: the product page rewrites max when the shopper picks
  // a different variant, and the cart page rewrites it when the server clamps
  // a line to what's left in stock.
  document.querySelectorAll('.qty-stepper').forEach(function (stepper) {
    const input = stepper.querySelector('.qty-input');
    if (!input) return;

    stepper.querySelectorAll('[data-action="minus"], [data-action="plus"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const min = parseInt(input.getAttribute('min'), 10) || 1;
        const max = parseInt(input.getAttribute('max'), 10) || 99;
        const step = btn.getAttribute('data-action') === 'plus' ? 1 : -1;

        let value = parseInt(input.value, 10);
        if (isNaN(value)) value = min;

        value = Math.max(min, Math.min(max, value + step));
        if (String(value) === input.value) return;

        input.value = value;

        // The cart page listens for this to PATCH the line — typing into the
        // input fires `change` natively, so both routes end up in one handler.
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
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

  /*
   * Keep the buy box honest about the selected variant.
   *
   * Stock lives on product_variants, so it only becomes knowable once a
   * combination is picked. This follows it into the qty input's max (which the
   * stepper above reads live) and into the button's disabled state, so a
   * sold-out combination can't be submitted at all — the server rejects it
   * regardless, this just stops the shopper wasting a round trip.
   */
  if (document.getElementById('productVariants')) {
    const variantAddBtn = document.querySelector('.purchase-row [data-add-to-cart]');
    const variantQtyInput = document.querySelector('.purchase-row .qty-input');

    function syncVariantStock(overrideGroup, overrideValue) {
      const variant = cartResolveVariant(null, overrideGroup, overrideValue);
      if (!variant) return;

      const stock = parseInt(variant.stock, 10) || 0;

      if (variantQtyInput) {
        variantQtyInput.setAttribute('max', Math.max(1, stock));
        if ((parseInt(variantQtyInput.value, 10) || 1) > stock) {
          variantQtyInput.value = Math.max(1, stock);
        }
      }

      if (variantAddBtn) {
        variantAddBtn.disabled = stock <= 0;
        variantAddBtn.innerHTML = stock <= 0
          ? '<i class="bi bi-x-circle"></i> Agotado'
          : '<i class="bi bi-cart-plus"></i> Agregar al carrito';
      }
    }

    document.querySelectorAll('.variant-group[data-variant-group] .size-btn[data-value]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        // The clicked value is passed explicitly: the handler that moves
        // .active is registered later (product.blade.php, after this file
        // loads) so at this point the DOM still shows the old selection.
        const group = btn.closest('.variant-group');
        syncVariantStock(group ? group.dataset.variantGroup : undefined, btn.dataset.value);
      });
    });

    // Every group renders its first option pre-selected, so there is already a
    // complete combination to reconcile on load.
    syncVariantStock();
  }
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

  /*
   * ===== Cart Page (/carrito) =====
   *
   * Every number on this page comes from the server. A quantity change PATCHes
   * the line and the response's already-formatted money strings are written
   * straight into the DOM — nothing here multiplies a price, so the browser
   * can't disagree with what checkout will charge.
   *
   * This replaces a block that read a data-price attribute off each row and did
   * the arithmetic locally.
   */
  const cartItemsList = document.getElementById('cartItemsList');

  if (cartItemsList) {

    const cartSyncTimers = new WeakMap();

    function cartToggleEmptyState() {
      const content = document.getElementById('cartContent');
      const empty = document.getElementById('cartEmptyState');
      if (!content || !empty) return;

      const isEmpty = cartItemsList.querySelectorAll('.cart-item').length === 0;
      content.style.display = isEmpty ? 'none' : '';
      empty.style.display = isEmpty ? '' : 'none';
    }

    function cartApplySummary(cart) {
      if (!cart) return;

      const subtotal = document.getElementById('cartSubtotal');
      const total = document.getElementById('cartTotal');
      const count = document.getElementById('cartItemCount');
      const countLabel = document.getElementById('cartItemCountLabel');

      // Subtotal and total are the same figure until checkout adds shipping.
      if (subtotal) subtotal.textContent = cart.subtotalFormatted;
      if (total) total.textContent = cart.subtotalFormatted;
      if (count) count.textContent = cart.lineCount;
      if (countLabel) countLabel.textContent = cart.lineCount === 1 ? 'producto' : 'productos';

      updateCartBadge(cart);
      cartToggleEmptyState();
    }

    function cartApplyLine(item, line) {
      if (!line) return;

      const input = item.querySelector('.qty-input');
      const lineTotal = item.querySelector('.cart-item-line-total');
      const unitPrice = item.querySelector('.cart-item-unit-price');

      if (input) {
        // The server may have clamped this to what's left in stock, so the
        // input follows its answer rather than the shopper's request.
        input.value = line.quantity;
        input.setAttribute('max', line.maxQuantity);
        input.dataset.syncedQty = String(line.quantity);
      }

      if (lineTotal) lineTotal.textContent = line.lineTotalFormatted;
      if (unitPrice) unitPrice.textContent = line.unitPriceFormatted + ' c/u';
    }

    function cartShowNotice(message) {
      const box = document.getElementById('cartNotice');
      const text = document.getElementById('cartNoticeText');

      if (!box || !text) {
        cartToast(message);
        return;
      }

      text.textContent = message;
      box.style.display = '';
    }

    function cartSyncQuantity(item) {
      const input = item.querySelector('.qty-input');
      if (!input) return;

      const min = parseInt(input.getAttribute('min'), 10) || 1;
      let quantity = parseInt(input.value, 10);
      if (isNaN(quantity) || quantity < min) quantity = min;

      // Nothing actually changed — a stepper click that hit the max, or a blur
      // after typing the same number back.
      if (String(quantity) === input.dataset.syncedQty) return;

      item.style.opacity = '0.55';

      cartRequest(cartEndpoints.item + '/' + item.dataset.itemId, 'PATCH', { quantity: quantity })
        .then(function (result) {
          item.style.opacity = '';

          if (!result.ok) {
            // Restore the last value the server confirmed, so the page never
            // shows a quantity the cart doesn't hold.
            input.value = input.dataset.syncedQty || min;
            cartToast(cartErrorMessage(result), 'danger');
            return;
          }

          cartApplyLine(item, result.data.item);
          cartApplySummary(result.data.cart);
          if (result.data.notice) cartShowNotice(result.data.notice);
        })
        .catch(function () {
          item.style.opacity = '';
          input.value = input.dataset.syncedQty || min;
          cartToast('Sin conexión. Intenta de nuevo.', 'danger');
        });
    }

    function cartQueueSync(item) {
      clearTimeout(cartSyncTimers.get(item));
      cartSyncTimers.set(item, setTimeout(function () { cartSyncQuantity(item); }, 350));
    }

    // Record what the server already knows, so a no-op event never hits it.
    cartItemsList.querySelectorAll('.cart-item .qty-input').forEach(function (input) {
      input.dataset.syncedQty = input.value;
    });

    /*
     * The stepper dispatches `change`; typing fires `input`. Both funnel into
     * one debounced path, so holding "+" down sends a single PATCH for the
     * final number instead of one per click.
     */
    ['input', 'change'].forEach(function (eventName) {
      cartItemsList.addEventListener(eventName, function (e) {
        const input = e.target.closest('.qty-input');
        if (!input) return;

        const item = input.closest('.cart-item');
        if (item) cartQueueSync(item);
      });
    });

    // Remove a line. Delegated, so a re-rendered row keeps working.
    cartItemsList.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-cart-remove]');
      if (!btn) return;

      const item = btn.closest('.cart-item');
      if (!item) return;

      btn.disabled = true;

      cartRequest(cartEndpoints.item + '/' + item.dataset.itemId, 'DELETE')
        .then(function (result) {
          if (!result.ok) {
            btn.disabled = false;
            cartToast(cartErrorMessage(result), 'danger');
            return;
          }

          item.style.opacity = '0';
          item.style.transform = 'translateX(12px)';

          setTimeout(function () {
            item.remove();
            cartApplySummary(result.data.cart);
          }, 200);
        })
        .catch(function () {
          btn.disabled = false;
          cartToast('Sin conexión. Intenta de nuevo.', 'danger');
        });
    });
  }

  // ===== Checkout Page (checkout.html) =====
  const checkoutForm = document.getElementById('checkoutForm');
  if (checkoutForm) {

    // Still the mockup's formatter. When checkout is ported this has to follow
    // the storefront convention the cart now uses (L. 1,520.00 from the
    // server) rather than producing $1.520 here.
    function formatCurrency(value) {
      return '$' + Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }


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

// ===== Mi cuenta (mi-cuenta.html) =====

// Keep ?tab= in sync with the sidebar pills so a refresh (or a form redirect)
// lands the visitor back on the panel they were looking at.
document.querySelectorAll('.account-nav [data-bs-toggle="pill"]').forEach(function (pill) {
  pill.addEventListener('shown.bs.tab', function () {
    const target = pill.getAttribute('data-bs-target') || '';
    const tab = target.replace('#tab-', '');
    if (!tab || !window.history.replaceState) return;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    url.searchParams.delete('page');
    window.history.replaceState({}, '', url);
  });
});

// One modal serves both "Agregar nueva dirección" and "Editar": the buttons
// rewrite its action / _method and prefill the fields from their data-* attrs.
const addressModalEl = document.getElementById('addressModal');
if (addressModalEl) {
  const addressModal = new bootstrap.Modal(addressModalEl);
  const addressForm = document.getElementById('addressForm');
  const addressFormMethod = document.getElementById('addressFormMethod');
  const addressFormId = document.getElementById('addressFormId');
  const addressModalLabel = document.getElementById('addressModalLabel');
  const addressStoreAction = addressModalEl.getAttribute('data-store-action');
  const addressIsDefault = document.getElementById('addressIsDefault');

  const addressTextFields = [
    ['addressRecipient', 'data-recipient-name'],
    ['addressPhone', 'data-phone'],
    ['addressLabel', 'data-label'],
    ['addressCity', 'data-city'],
    ['addressLine1', 'data-line1'],
    ['addressLine2', 'data-line2'],
    ['addressState', 'data-state'],
    ['addressPostal', 'data-postal-code']
  ];

  function resetAddressForm() {
    addressForm.classList.remove('was-validated');
    addressForm.setAttribute('action', addressStoreAction);
    addressFormMethod.value = 'POST';
    addressFormId.value = '';
    addressModalLabel.textContent = 'Agregar nueva dirección';
    addressTextFields.forEach(function (pair) {
      const field = document.getElementById(pair[0]);
      if (field) field.value = '';
    });
    if (addressIsDefault) addressIsDefault.checked = false;
    // Clear any server-rendered validation output from the previous attempt.
    addressForm.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
    addressForm.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
  }

  document.querySelectorAll('.js-address-create').forEach(function (btn) {
    btn.addEventListener('click', function () {
      resetAddressForm();
      addressModal.show();
    });
  });

  document.querySelectorAll('.js-address-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
      resetAddressForm();
      addressForm.setAttribute('action', btn.getAttribute('data-action'));
      addressFormMethod.value = 'PUT';
      addressFormId.value = btn.getAttribute('data-address-id') || '';
      addressModalLabel.textContent = 'Editar dirección';
      addressTextFields.forEach(function (pair) {
        const field = document.getElementById(pair[0]);
        if (field) field.value = btn.getAttribute(pair[1]) || '';
      });
      if (addressIsDefault) addressIsDefault.checked = btn.getAttribute('data-is-default') === '1';
      addressModal.show();
    });
  });

  addressForm.addEventListener('submit', function (e) {
    if (!addressForm.checkValidity()) {
      e.preventDefault();
      addressForm.classList.add('was-validated');
      const firstInvalid = addressForm.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }
    const submitBtn = addressForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Guardando...';
    }
  });

  // The server bounced a validation error back — re-open the modal on the
  // "Mis direcciones" tab with the offending values still in place.
  if (addressModalEl.getAttribute('data-open-on-load') === '1') {
    addressModal.show();
  }
}

document.querySelectorAll('.js-address-delete').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!window.confirm('¿Eliminar esta dirección?')) e.preventDefault();
  });
});

// Personal details form (mi-cuenta.html → "Datos personales")
const profileForm = document.getElementById('profileForm');
if (profileForm) {
  profileForm.addEventListener('submit', function (e) {
    if (!profileForm.checkValidity()) {
      e.preventDefault();
      profileForm.classList.add('was-validated');
      const firstInvalid = profileForm.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }
    const submitBtn = profileForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Guardando...';
    }
  });
}