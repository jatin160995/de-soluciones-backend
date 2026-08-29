@extends('layouts.storefront')

@php
    $effectivePrice = (float) $product->effective_price;
    $basePrice = (float) $product->base_price;

    $hasDiscount = $product->discounted_price !== null
        && $basePrice > $effectivePrice;

    $discountPercent = $hasDiscount
        ? round((($basePrice - $effectivePrice) / $basePrice) * 100)
        : 0;

    $mainImage = $productImages->first();

    $whatsappNumber = $contact['whatsapp'] ?? $contact['phone'] ?? null;

    /*
     * Product variants can have:
     * size
     * color
     * Processor
     * material
     * capacity
     * etc.
     *
     * We don't special-case color into swatches.
     * Every variant attribute is displayed as a normal text selector.
     */
@endphp

@section('title', $product->name . ' | ' . ($siteName ?? 'DE Soluciones'))

@section('meta_description', \Illuminate\Support\Str::limit(
    strip_tags($product->description ?? $product->name),
    155
))

@section('content')

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-trail">
                <li>
                    <a href="{{ route('home') }}">Inicio</a>
                </li>

                <li>
                    <a href="{{ url('/catalogo') }}">Catálogo</a>
                </li>

                @if($product->category)
                    <li>
                        <a href="{{ url('/catalogo?categoria=' . $product->category->slug) }}">
                            {{ $product->category->name }}
                        </a>
                    </li>
                @endif

                <li class="active" aria-current="page">
                    {{ $product->name }}
                </li>
            </ol>
        </nav>
    </div>
</div>


<!-- Product Detail -->
<section class="section-pad-sm">
    <div class="container">

        <div class="row g-5">

            <!-- ========================= -->
            <!-- Gallery -->
            <!-- ========================= -->

            <div class="col-lg-6">

                <div class="product-gallery">

                    <div class="gallery-main">

                        @if($hasDiscount)
                            <span class="ribbon ribbon-best">
                                -{{ $discountPercent }}%
                            </span>
                        @endif

                        <button
                            class="gallery-zoom-btn"
                            type="button"
                            aria-label="Ampliar imagen"
                        >
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>

                        @if($mainImage)
                            <img
                                id="galleryMainImg"
                                src="{{ $mainImage['url'] }}"
                                alt="{{ $mainImage['alt'] }}"
                            >
                        @else
                            <img
                                id="galleryMainImg"
                                src="{{ asset('images/placeholder-product.png') }}"
                                alt="{{ $product->name }}"
                            >
                        @endif

                    </div>


                    @if($productImages->count() > 1)

                        <div class="gallery-thumbs">

                            @foreach($productImages as $index => $image)

                                <div
                                    class="thumb-item {{ $index === 0 ? 'active' : '' }}"
                                    data-full="{{ $image['url'] }}"
                                >
                                    <img
                                        src="{{ $image['thumb'] ?: $image['url'] }}"
                                        alt="{{ $image['alt'] }}"
                                    >
                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>


            <!-- ========================= -->
            <!-- Product Info -->
            <!-- ========================= -->

            <div class="col-lg-6">

                <div class="product-detail-info">

                    @if($product->category)
                        <span class="product-cat">
                            {{ $product->category->name }}
                        </span>
                    @endif


                    <h1>
                        {{ $product->name }}
                    </h1>


                    <!-- Product meta -->
                    <div class="product-detail-meta">

                        {{-- Reviews intentionally removed --}}

                        @if($product->variants->isNotEmpty())

                            @php
                                $firstVariant = $product->variants->first();
                            @endphp

                            @if($firstVariant->sku)
                                <span class="sku-tag">
                                    SKU: {{ $firstVariant->sku }}
                                </span>
                            @endif

                        @endif

                    </div>


                    <!-- Price -->
                    <div class="price-row price-row-lg">

                        <span class="price">
                            {{ number_format($effectivePrice, 2) }}
                        </span>

                        @if($hasDiscount)

                            <span class="price-old">
                                {{ number_format($basePrice, 2) }}
                            </span>

                            <span class="off-tag">
                                -{{ $discountPercent }}%
                            </span>

                        @endif

                    </div>


                    <p class="price-note">
                        <i class="bi bi-cash-coin"></i>
                        Pago contra entrega disponible en toda la región
                    </p>


                    <!-- Description -->
                    @if($product->description)

                        <div class="product-short-desc">
                            {!! nl2br(e($product->description)) !!}
                        </div>

                    @endif


                    <!-- Stock -->
                    @php
                        $totalStock = $product->variants->sum('stock_quantity');

                        /*
                         * If the product has variants, use the total variant stock.
                         * Otherwise the product itself currently doesn't have a
                         * stock_quantity column, so treat it as available.
                         */
                        $hasVariantStock = $product->variants->isNotEmpty();
                        $inStock = ! $hasVariantStock || $totalStock > 0;
                    @endphp


                    <div class="stock-status {{ $inStock ? 'in-stock' : 'out-of-stock' }}">

                        @if($inStock)

                            <i class="bi bi-check-circle-fill"></i>
                            En existencia — listo para enviar hoy

                        @else

                            <i class="bi bi-x-circle-fill"></i>
                            Agotado

                        @endif

                    </div>


                    <!-- ========================= -->
                    <!-- Normal Text Variations -->
                    <!-- ========================= -->

                    @foreach($variantAttributes as $attributeName => $values)

                        <div
                            class="variant-group"
                            data-variant-group="{{ $attributeName }}"
                        >

                            <span class="variant-label">

                                {{ ucfirst($attributeName) }}:

                                <strong class="variant-selected-value">
                                    {{ $values[0] }}
                                </strong>

                            </span>


                            <div class="variant-sizes">

                                @foreach($values as $value)

                                    <button
                                        type="button"
                                        class="size-btn {{ $loop->first ? 'active' : '' }}"
                                        data-attribute="{{ $attributeName }}"
                                        data-value="{{ $value }}"
                                    >
                                        {{ $value }}
                                    </button>

                                @endforeach

                            </div>

                        </div>

                    @endforeach


                    <!-- ========================= -->
                    <!-- Purchase -->
                    <!-- ========================= -->

                    @php
                        /*
                         * Lookup table the add-to-cart script uses to turn the
                         * shopper's attribute picks into a single variant_id.
                         *
                         * The key is a signature built from every rendered
                         * variant group, in the same order the @foreach above
                         * renders them, so the JS can rebuild it from the
                         * active .size-btn in each group:
                         *
                         *   processor=m1|size=13"|color=mid night
                         *
                         * Missing values become empty strings on both sides, so
                         * a variant that doesn't carry one of the attributes
                         * still gets a stable, unambiguous key.
                         *
                         * The id is only a hint — AddToCartRequest re-checks
                         * that the variant really belongs to this product, so a
                         * tampered value can't price one product off another.
                         */
                        $variantKeys = array_keys($variantAttributes);

                        $variantMap = [];

                        foreach ($product->variants as $variant) {
                            $attrs = is_array($variant->attributes) ? $variant->attributes : [];

                            $signature = collect($variantKeys)
                                ->map(fn ($key) => $key . '=' . mb_strtolower((string) ($attrs[$key] ?? '')))
                                ->implode('|');

                            $variantMap[$signature] = [
                                'id'    => $variant->id,
                                'stock' => (int) $variant->stock_quantity,
                                'price' => config('store.currency_symbol') . ' ' . number_format((float) $variant->effective_price, 2),
                            ];
                        }

                        $hasVariants = $product->variants->isNotEmpty();

                        // Variants with no attribute groups leave nothing to
                        // pick, so the only variant is used directly.
                        $soleVariantId = ($hasVariants && empty($variantAttributes))
                            ? $product->variants->first()->id
                            : null;
                    @endphp

                    @if($hasVariants)
                        <script type="application/json" id="productVariants">@json($variantMap)</script>
                    @endif

                    <div class="purchase-row">

                        <div class="qty-stepper">

                            <button
                                type="button"
                                class="qty-btn"
                                data-action="minus"
                                aria-label="Disminuir cantidad"
                            >
                                <i class="bi bi-dash"></i>
                            </button>

                            <input
                                type="number"
                                class="qty-input"
                                value="1"
                                min="1"
                                max="99"
                                aria-label="Cantidad"
                            >

                            <button
                                type="button"
                                class="qty-btn"
                                data-action="plus"
                                aria-label="Aumentar cantidad"
                            >
                                <i class="bi bi-plus"></i>
                            </button>

                        </div>


                        <button
                            type="button"
                            class="btn-cart btn-cart-lg"
                            data-add-to-cart
                            data-product-id="{{ $product->id }}"
                            data-has-variants="{{ $hasVariants ? '1' : '0' }}"
                            @if($soleVariantId) data-variant-id="{{ $soleVariantId }}" @endif
                            @disabled(!$inStock)
                        >
                            <i class="bi bi-cart-plus"></i>
                            Agregar al carrito
                        </button>


                        <button
                            class="btn-wishlist-lg"
                            type="button"
                            aria-label="Agregar a favoritos"
                        >
                            <i class="bi bi-heart"></i>
                        </button>

                    </div>


                    <!-- Buy Now -->

                    @if($inStock)

                        <button
                            type="button"
                            class="btn-buy-now"
                            data-bs-toggle="modal"
                            data-bs-target="#buyNowModal"
                            data-buy-now
                            data-product-id="{{ $product->id }}"
                            data-has-variants="{{ $hasVariants ? '1' : '0' }}"
                            data-product-price="{{ config('store.currency_symbol') }} {{ number_format($effectivePrice, 2) }}"
                            @if($soleVariantId)
                                data-variant-id="{{ $soleVariantId }}"
                            @endif
                        >
                            Comprar ahora
                            <i class="bi bi-arrow-right"></i>
                        </button>

                    @endif


                    <!-- WhatsApp -->

                    @if($whatsappNumber)

                        @php
                            $whatsappMessage = urlencode(
                                'Hola, tengo una duda sobre el producto: ' . $product->name
                            );

                            $whatsappUrl = 'https://wa.me/' .
                                preg_replace('/[^0-9]/', '', $whatsappNumber) .
                                '?text=' . $whatsappMessage;
                        @endphp

                        <a
                            href="{{ $whatsappUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="whatsapp-inline-btn"
                        >
                            <i class="bi bi-whatsapp"></i>
                            ¿Dudas? Escríbenos por WhatsApp
                        </a>

                    @endif


                    <!-- Trust -->
                    <div class="trust-mini-grid">

                        <div class="trust-mini-item">
                            <i class="bi bi-truck"></i>
                            <span>Envío a todo el país</span>
                        </div>

                        <div class="trust-mini-item">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Cambios sin costo</span>
                        </div>

                        <div class="trust-mini-item">
                            <i class="bi bi-patch-check"></i>
                            <span>Garantía de 6 meses</span>
                        </div>

                        <div class="trust-mini-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Compra 100% segura</span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>


<!-- ========================= -->
<!-- Description / Specifications -->
<!-- ========================= -->

<section class="section-pad-sm">

    <div class="container">

        <div class="product-tabs-wrap">

            <ul
                class="nav product-tabs"
                id="productTabs"
                role="tablist"
            >

                <li class="nav-item" role="presentation">

                    <button
                        class="nav-link active"
                        id="tab-desc-btn"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-desc"
                        type="button"
                        role="tab"
                        aria-controls="tab-desc"
                        aria-selected="true"
                    >
                        Descripción
                    </button>

                </li>


                @if(!empty($variantAttributes))

                    <li class="nav-item" role="presentation">

                        <button
                            class="nav-link"
                            id="tab-specs-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-specs"
                            type="button"
                            role="tab"
                            aria-controls="tab-specs"
                            aria-selected="false"
                        >
                            Especificaciones
                        </button>

                    </li>

                @endif

            </ul>


            <div
                class="tab-content product-tab-content"
                id="productTabsContent"
            >

                <!-- Description -->

                <div
                    class="tab-pane fade show active"
                    id="tab-desc"
                    role="tabpanel"
                    aria-labelledby="tab-desc-btn"
                >

                    @if($product->description)

                        <div class="product-description">

                            {!! nl2br(e($product->description)) !!}

                        </div>

                    @else

                        <p>
                            Información del producto próximamente.
                        </p>

                    @endif

                </div>


                <!-- Specifications -->

                @if(!empty($variantAttributes))

                    <div
                        class="tab-pane fade"
                        id="tab-specs"
                        role="tabpanel"
                        aria-labelledby="tab-specs-btn"
                    >

                        <div class="table-responsive">

                            <table class="table">

                                <tbody>

                                    @foreach($variantAttributes as $attributeName => $values)

                                        <tr>

                                            <th>
                                                {{ ucfirst($attributeName) }}
                                            </th>

                                            <td>
                                                {{ implode(', ', $values) }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>

</section>


<!-- ========================= -->
<!-- Related Products -->
<!-- ========================= -->

@if($relatedProducts->isNotEmpty())

<section class="section-pad-sm">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="eyebrow">
                    También te puede interesar
                </span>

                <h2>
                    Productos relacionados
                </h2>

            </div>

        </div>


        <div class="row g-4">

            @foreach($relatedProducts as $related)
                @include('partials.product-card', ['product' => $related])
            @endforeach

        </div>

    </div>

</section>

@endif


<!-- ========================= -->
<!-- Buy Now Modal -->
<!-- ========================= -->

    <div
        class="modal fade"
        id="buyNowModal"
        tabindex="-1"
        aria-labelledby="buyNowModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">

            <div class="modal-content buy-now-modal">

                <!-- Header -->

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="buyNowModalLabel"
                    >
                        <i class="bi bi-lightning-charge-fill"></i>
                        Completa tu pedido
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>


                <!-- Body -->

                <div class="modal-body">

                    {{-- Buy Now validation errors --}}

                    @if($errors->buy_now->any())

                        <div class="alert alert-danger">

                            <strong>
                                Hay errores en el formulario:
                            </strong>

                            <ul class="mb-0 mt-2">

                                @foreach($errors->buy_now->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    <!-- Product summary -->

                    <div class="buy-now-product-summary">

                        <div class="buy-now-product-info">

                            @if($mainImage)

                                <img
                                    src="{{ $mainImage['thumb'] ?: $mainImage['url'] }}"
                                    alt="{{ $product->name }}"
                                    class="buy-now-product-image"
                                >

                            @endif


                            <div>

                                <p class="buy-now-product-name mb-1">
                                    {{ $product->name }}
                                </p>

                                <span
                                    class="buy-now-product-variant"
                                    id="buyNowVariantSummary"
                                >
                                    Producto seleccionado
                                </span>

                            </div>

                        </div>


                        <div class="buy-now-product-price">

                            <span id="buyNowUnitPrice">

                                {{ config('store.currency_symbol') }}
                                {{ number_format($effectivePrice, 2) }}

                            </span>

                            <span class="buy-now-qty">

                                x

                                <span id="buyNowQty">
                                    1
                                </span>

                            </span>

                        </div>

                    </div>


                    <!-- Buy Now Form -->

                    <form
                        id="buyNowForm"
                        method="POST"
                        action="{{ route('buy-now.store') }}"
                        novalidate
                    >

                        @csrf


                        <!-- Product -->

                        <input
                            type="hidden"
                            name="product_id"
                            id="buyNowProductId"
                            value="{{ $product->id }}"
                        >

                        <input
                            type="hidden"
                            name="variant_id"
                            id="buyNowVariantId"
                            value="{{ old('variant_id') }}"
                        >

                        <input
                            type="hidden"
                            name="quantity"
                            id="buyNowQtyInput"
                            value="{{ old('quantity', 1) }}"
                        >


                        <div class="row g-3">


                            <!-- Customer name -->

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="bnName"
                                >
                                    Nombre completo
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="bnName"
                                    name="customer_name"
                                    value="{{ old('customer_name', auth()->user()?->name) }}"
                                    autocomplete="name"
                                    required
                                >

                                @error('customer_name', 'buy_now')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            <!-- WhatsApp -->

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="bnWhatsapp"
                                >
                                    Número de WhatsApp
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="bnWhatsapp"
                                    name="whatsapp_number"
                                    value="{{ old('whatsapp_number', auth()->user()?->whatsapp_number) }}"
                                    placeholder="+504 0000-0000"
                                    autocomplete="tel"
                                    required
                                >

                                @error('whatsapp_number', 'buy_now')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            <!-- Phone -->

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="bnPhone"
                                >
                                    Teléfono
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="bnPhone"
                                    name="customer_phone"
                                    value="{{ old('customer_phone', auth()->user()?->phone) }}"
                                    placeholder="+504 0000-0000"
                                    autocomplete="tel"
                                    required
                                >

                                @error('customer_phone', 'buy_now')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            <!-- Alternate phone -->

                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="bnAltPhone"
                                >
                                    Teléfono alternativo (opcional)
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="bnAltPhone"
                                    name="alternate_phone"
                                    value="{{ old('alternate_phone') }}"
                                    placeholder="+504 0000-0000"
                                >

                            </div>


                            <!-- Email -->

                            <div class="col-12">

                                <label
                                    class="form-label"
                                    for="bnEmail"
                                >
                                    Correo electrónico (opcional)
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="bnEmail"
                                    name="customer_email"
                                    value="{{ old('customer_email', auth()->user()?->email) }}"
                                    autocomplete="email"
                                >

                                @error('customer_email', 'buy_now')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            @auth

                                @if($addresses->isNotEmpty())

                                    <!-- Saved addresses -->

                                    <div class="col-12">

                                        <label
                                            class="form-label"
                                            for="buyNowAddressId"
                                        >
                                            Dirección de entrega
                                        </label>

                                        <select
                                            class="form-select"
                                            id="buyNowAddressId"
                                            name="address_id"
                                        >

                                            <option value="">
                                                Usar una nueva dirección
                                            </option>

                                            @foreach($addresses as $address)

                                                <option
                                                    value="{{ $address->id }}"
                                                    @selected(
                                                        old(
                                                            'address_id',
                                                            $loop->first
                                                                ? $address->id
                                                                : null
                                                        ) == $address->id
                                                    )
                                                >

                                                    {{ $address->label ?: 'Dirección' }}

                                                    —

                                                    {{ $address->line1 }},
                                                    {{ $address->city }}

                                                    @if($address->is_default)

                                                        (Predeterminada)

                                                    @endif

                                                </option>

                                            @endforeach

                                        </select>

                                        <div class="form-text">

                                            Tu dirección predeterminada está
                                            seleccionada automáticamente.

                                        </div>

                                    </div>

                                @endif

                            @endauth


                            <!-- Manual address -->

                            <div
                                id="buyNowManualAddress"
                                class="row g-3 mt-0"
                            >

                                <!-- Address -->

                                <div class="col-12">

                                    <label
                                        class="form-label"
                                        for="bnAddress"
                                    >
                                        Dirección
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="bnAddress"
                                        name="line1"
                                        value="{{ old('line1') }}"
                                        placeholder="Calle, avenida, número de casa"
                                    >

                                    @error('line1', 'buy_now')

                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                <!-- Reference -->

                                <div class="col-12">

                                    <label
                                        class="form-label"
                                        for="bnReference"
                                    >
                                        Referencia de la dirección
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="bnReference"
                                        name="line2"
                                        value="{{ old('line2') }}"
                                        placeholder="Punto de referencia, color de casa, portón, etc."
                                    >

                                    @error('line2', 'buy_now')

                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                <!-- City -->

                                <div class="col-md-7">

                                    <label
                                        class="form-label"
                                        for="bnCity"
                                    >
                                        Ciudad
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="bnCity"
                                        name="city"
                                        value="{{ old('city') }}"
                                    >

                                    @error('city', 'buy_now')

                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                <!-- Department -->

                                <div class="col-md-5">

                                    <label
                                        class="form-label"
                                        for="bnState"
                                    >
                                        Departamento
                                    </label>

                                    <select
                                        class="form-select"
                                        id="bnState"
                                        name="state"
                                    >

                                        <option value="">
                                            Selecciona...
                                        </option>

                                        @foreach([
                                            'Atlántida',
                                            'Choluteca',
                                            'Colón',
                                            'Comayagua',
                                            'Copán',
                                            'Cortés',
                                            'El Paraíso',
                                            'Francisco Morazán',
                                            'Gracias a Dios',
                                            'Intibucá',
                                            'Islas de la Bahía',
                                            'La Paz',
                                            'Lempira',
                                            'Ocotepeque',
                                            'Olancho',
                                            'Santa Bárbara',
                                            'Valle',
                                            'Yoro',
                                        ] as $department)

                                            <option
                                                value="{{ $department }}"
                                                @selected(
                                                    old('state') === $department
                                                )
                                            >
                                                {{ $department }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('state', 'buy_now')

                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>

                            </div>


                            <!-- Preferred courier -->

                            <div class="col-12">

                                <label class="form-label">

                                    Empresa de envío preferida
                                    (opcional)

                                </label>


                                <div class="courier-method-cards">


                                    <!-- No preference -->

                                    <label class="courier-method-card selected">

                                        <input
                                            type="radio"
                                            name="preferred_courier"
                                            value=""
                                            checked
                                        >

                                        <span class="courier-method-none">

                                            <i class="bi bi-shuffle"></i>

                                        </span>

                                        <span class="courier-method-name">

                                            Sin preferencia

                                        </span>

                                    </label>


                                    <!-- C807 -->

                                    <label class="courier-method-card">

                                        <input
                                            type="radio"
                                            name="preferred_courier"
                                            value="c807"
                                            @checked(
                                                old('preferred_courier') === 'c807'
                                            )
                                        >

                                        <img
                                            src="{{ asset('storefront/img/c807.png') }}"
                                            alt="C807 Express"
                                        >

                                    </label>


                                    <!-- Cargo Expreso -->

                                    <label class="courier-method-card">

                                        <input
                                            type="radio"
                                            name="preferred_courier"
                                            value="cargo_expreso"
                                            @checked(
                                                old('preferred_courier') === 'cargo_expreso'
                                            )
                                        >

                                        <img
                                            src="{{ asset('storefront/img/caex.png') }}"
                                            alt="Cargo Expreso"
                                        >

                                    </label>


                                    <!-- Forza -->

                                    <label class="courier-method-card">

                                        <input
                                            type="radio"
                                            name="preferred_courier"
                                            value="forza_delivery"
                                            @checked(
                                                old('preferred_courier') === 'forza_delivery'
                                            )
                                        >

                                        <img
                                            src="{{ asset('storefront/img/forza.png') }}"
                                            alt="Forza Delivery"
                                        >

                                    </label>

                                </div>

                            </div>

                        </div>


                        <!-- Payment -->

                        <div class="buy-now-payment-note mt-3">

                            <i class="bi bi-cash-coin"></i>

                            Pago contra entrega —
                            pagas en efectivo cuando recibes tu pedido.

                        </div>


                        <!-- Terms -->

                        <div class="form-check mt-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="buyNowAcceptTerms"
                                name="accept_terms"
                                value="1"
                                @checked(old('accept_terms'))
                                required
                            >

                            <label
                                class="form-check-label"
                                for="buyNowAcceptTerms"
                            >

                                Acepto los términos y condiciones y
                                la política de privacidad.

                            </label>

                            @error('accept_terms', 'buy_now')

                                <div class="invalid-feedback d-block">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>


                        <!-- Submit -->

                        <button
                            type="submit"
                            id="buyNowSubmitBtn"
                            class="btn-place-order w-100 mt-3"
                        >

                            <i class="bi bi-lock-fill"></i>

                            Confirmar pedido

                        </button>


                        <p class="checkout-secure-note text-center mt-2">

                            <i class="bi bi-shield-check"></i>

                            Tus datos están protegidos

                        </p>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * ========================================
     * Product gallery
     * ========================================
     */

    const mainImage = document.getElementById('galleryMainImg');

    document.querySelectorAll('.thumb-item').forEach(function (thumb) {

        thumb.addEventListener('click', function () {

            const fullImage = this.dataset.full;

            if (mainImage && fullImage) {
                mainImage.src = fullImage;
            }

            document
                .querySelectorAll('.thumb-item')
                .forEach(function (item) {
                    item.classList.remove('active');
                });

            this.classList.add('active');
        });

    });


    /*
     * ========================================
     * Quantity
     * ========================================
     *
     * Handled globally in storefront/js/script.js. A second binding used to
     * live here, which meant every "+" click fired twice and stepped by 2.
     */


    /*
     * ========================================
     * Text-based product variations
     *
     * Color is intentionally handled exactly
     * like every other attribute.
     * ========================================
     */

    document.querySelectorAll('.variant-group').forEach(function (group) {

        const buttons = group.querySelectorAll('.size-btn');

        const selectedLabel =
            group.querySelector('.variant-selected-value');

        buttons.forEach(function (button) {

            button.addEventListener('click', function () {

                buttons.forEach(function (item) {
                    item.classList.remove('active');
                });

                this.classList.add('active');

                if (selectedLabel) {
                    selectedLabel.textContent =
                        this.dataset.value || this.textContent.trim();
                }

            });

        });

    });


    /*
     * ========================================
     * Gallery zoom
     * ========================================
     */

    const zoomButton =
        document.querySelector('.gallery-zoom-btn');

    if (zoomButton && mainImage) {

        zoomButton.addEventListener('click', function () {

            if (mainImage.requestFullscreen) {
                mainImage.requestFullscreen();
            }

        });

    }


        /*
     * ========================================
     * BUY NOW
     * ========================================
     */

    const buyNowButton =
        document.querySelector('[data-buy-now]');

    const buyNowForm =
        document.getElementById('buyNowForm');

    const buyNowAddressSelect =
        document.getElementById('buyNowAddressId');

    const buyNowManualAddress =
        document.getElementById('buyNowManualAddress');

    const buyNowVariantId =
        document.getElementById('buyNowVariantId');

    const buyNowQtyInput =
        document.getElementById('buyNowQtyInput');

    const buyNowQty =
        document.getElementById('buyNowQty');

    const buyNowUnitPrice =
        document.getElementById('buyNowUnitPrice');

    const buyNowVariantSummary =
        document.getElementById('buyNowVariantSummary');


    /*
     * -------------------------------------------------------------
     * Address selection
     * -------------------------------------------------------------
     *
     * Logged-in user with saved address:
     *     manual fields are hidden and disabled.
     *
     * New address:
     *     manual fields become active.
     *
     * Guest:
     *     manual fields are always active.
     */

    function syncBuyNowAddress() {

        if (!buyNowManualAddress) {
            return;
        }

        const usingSavedAddress =
            buyNowAddressSelect &&
            buyNowAddressSelect.value !== '';

        if (usingSavedAddress) {

            buyNowManualAddress.style.display =
                'none';

        } else {

            buyNowManualAddress.style.display =
                '';

        }


        const fields =
            buyNowManualAddress.querySelectorAll(
                'input, select'
            );

        fields.forEach(function (field) {

            field.disabled =
                usingSavedAddress;

            field.required =
                !usingSavedAddress;

        });
    }


    if (buyNowAddressSelect) {

        buyNowAddressSelect.addEventListener(
            'change',
            syncBuyNowAddress
        );

        syncBuyNowAddress();
    }


    /*
     * -------------------------------------------------------------
     * Resolve selected variant
     * -------------------------------------------------------------
     *
     * Reuse the same variant resolver already used by the
     * add-to-cart implementation.
     */

    function resolveBuyNowVariant() {

        if (!buyNowButton) {
            return null;
        }


        /*
         * Product has no attribute selectors.
         *
         * The controller still requires the correct variant ID
         * when the product has variants.
         */

        if (buyNowButton.dataset.variantId) {

            return {
                id: buyNowButton.dataset.variantId,
            };
        }


        if (
            typeof cartResolveVariant ===
            'function'
        ) {

            return cartResolveVariant(
                buyNowButton
            );
        }


        return null;
    }


    /*
     * -------------------------------------------------------------
     * Selected variant summary
     * -------------------------------------------------------------
     */

    function updateBuyNowVariantSummary() {

        if (!buyNowVariantSummary) {
            return;
        }

        const groups =
            document.querySelectorAll(
                '.variant-group[data-variant-group]'
            );

        const values = [];


        groups.forEach(function (group) {

            const active =
                group.querySelector(
                    '.size-btn.active[data-value]'
                );

            if (active) {

                values.push(
                    active.dataset.value
                );

            }

        });


        if (values.length) {

            buyNowVariantSummary.textContent =
                values.join(' · ');

        } else {

            buyNowVariantSummary.textContent =
                'Producto seleccionado';

        }
    }


    /*
     * -------------------------------------------------------------
     * Update Buy Now values
     * -------------------------------------------------------------
     */

    function syncBuyNowValues() {

        if (!buyNowForm) {
            return;
        }


        /*
         * Quantity
         */

        const productQtyInput =
            document.querySelector(
                '.purchase-row .qty-input'
            );

        let quantity = 1;

        if (productQtyInput) {

            quantity =
                parseInt(
                    productQtyInput.value,
                    10
                ) || 1;

        }

        quantity =
            Math.max(1, quantity);


        if (buyNowQtyInput) {

            buyNowQtyInput.value =
                quantity;

        }


        if (buyNowQty) {

            buyNowQty.textContent =
                quantity;

        }


        /*
         * Variant
         */

        const variant =
            resolveBuyNowVariant();


        if (buyNowVariantId) {

            buyNowVariantId.value =
                variant
                    ? variant.id
                    : '';

        }


        /*
         * Variant summary
         */

        updateBuyNowVariantSummary();


        /*
         * Display price.
         *
         * This is ONLY a visual value.
         *
         * The backend resolves the actual price again.
         */

        if (
            variant &&
            variant.price &&
            buyNowUnitPrice
        ) {

            buyNowUnitPrice.textContent =
                variant.price;

        } else if (
            buyNowUnitPrice &&
            buyNowButton
        ) {

            buyNowUnitPrice.textContent =
                buyNowButton.dataset.productPrice
                || '';

        }
    }


    /*
     * -------------------------------------------------------------
     * Open modal
     * -------------------------------------------------------------
     */

    if (buyNowButton) {

        buyNowButton.addEventListener(
            'click',
            function () {

                syncBuyNowValues();

                syncBuyNowAddress();

            }
        );

    }


    /*
     * -------------------------------------------------------------
     * Quantity changes
     * -------------------------------------------------------------
     */

    const productQtyInput =
        document.querySelector(
            '.purchase-row .qty-input'
        );

    if (productQtyInput) {

        productQtyInput.addEventListener(
            'change',
            function () {

                syncBuyNowValues();

            }
        );

        productQtyInput.addEventListener(
            'input',
            function () {

                syncBuyNowValues();

            }
        );

    }


    /*
     * -------------------------------------------------------------
     * Variant changes
     * -------------------------------------------------------------
     *
     * Existing variant click handlers run on the same event.
     * setTimeout ensures the active class has been updated before
     * Buy Now reads it.
     */

    document
        .querySelectorAll(
            '.variant-swatches button, .variant-sizes button'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    setTimeout(
                        function () {

                            syncBuyNowValues();

                        },
                        0
                    );

                }
            );

        });


    /*
     * -------------------------------------------------------------
     * Courier card visual selection
     * -------------------------------------------------------------
     */

    document
        .querySelectorAll(
            '#buyNowForm .courier-method-card'
        )
        .forEach(function (card) {

            card.addEventListener(
                'click',
                function () {

                    document
                        .querySelectorAll(
                            '#buyNowForm .courier-method-card'
                        )
                        .forEach(function (item) {

                            item.classList.remove(
                                'selected'
                            );

                        });


                    card.classList.add(
                        'selected'
                    );

                }
            );

        });


    /*
     * -------------------------------------------------------------
     * Submit
     * -------------------------------------------------------------
     */

    if (buyNowForm) {

        buyNowForm.addEventListener(
            'submit',
            function (event) {

                /*
                 * Update everything immediately before POST.
                 */
                syncBuyNowValues();

                syncBuyNowAddress();


                /*
                 * If the product has variants, the customer must
                 * have selected a valid combination.
                 */

                if (
                    buyNowButton &&
                    buyNowButton.dataset.hasVariants ===
                        '1'
                ) {

                    const variant =
                        resolveBuyNowVariant();


                    if (!variant) {

                        event.preventDefault();


                        if (
                            typeof cartToast ===
                            'function'
                        ) {

                            cartToast(
                                'Selecciona una opción del producto antes de continuar.',
                                'danger'
                            );

                        } else {

                            alert(
                                'Selecciona una opción del producto antes de continuar.'
                            );

                        }

                        return;

                    }

                }


                /*
                 * Prevent double-click / duplicate orders.
                 */

                const submitButton =
                    document.getElementById(
                        'buyNowSubmitBtn'
                    );


                if (submitButton) {

                    submitButton.disabled =
                        true;

                    submitButton.innerHTML =
                        '<i class="bi bi-arrow-repeat"></i> Procesando...';

                }

            }
        );

    }


    /*
     * -------------------------------------------------------------
     * Re-open modal after validation error
     * -------------------------------------------------------------
     */

    @if($errors->buy_now->any())

        const buyNowModalElement =
            document.getElementById(
                'buyNowModal'
            );


        if (
            buyNowModalElement &&
            typeof bootstrap !==
                'undefined'
        ) {

            const buyNowModal =
                bootstrap.Modal.getOrCreateInstance(
                    buyNowModalElement
                );

            syncBuyNowValues();

            syncBuyNowAddress();

            buyNowModal.show();

        }

    @endif

});
</script>

@endpush