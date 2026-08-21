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

                @php
                    $relatedImage = $related->getFirstMediaUrl('images');
                    $relatedPrice = $related->effective_price;
                    $relatedHasDiscount =
                        $related->discounted_price !== null &&
                        $related->base_price > $relatedPrice;
                @endphp

                <div class="col-6 col-md-4 col-lg-3">

                    <article class="product-card">

                        <a
                            href="{{ route('product.show', $related->slug) }}"
                            class="product-card-image"
                        >

                            @if($relatedImage)

                                <img
                                    src="{{ $relatedImage }}"
                                    alt="{{ $related->name }}"
                                >

                            @else

                                <img
                                    src="{{ asset('images/placeholder-product.png') }}"
                                    alt="{{ $related->name }}"
                                >

                            @endif

                        </a>


                        <div class="product-card-body">

                            @if($related->category)

                                <span class="product-card-cat">
                                    {{ $related->category->name }}
                                </span>

                            @endif


                            <h3 class="product-card-title">

                                <a href="{{ route('product.show', $related->slug) }}">
                                    {{ $related->name }}
                                </a>

                            </h3>


                            <div class="price-row">

                                <span class="price">
                                    {{ number_format($relatedPrice, 2) }}
                                </span>

                                @if($relatedHasDiscount)

                                    <span class="price-old">
                                        {{ number_format($related->base_price, 2) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </article>

                </div>

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
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="buyNowModalLabel"
                >
                    Comprar ahora
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <div class="modal-body">

                <p>
                    Vas a comprar:
                </p>

                <strong>
                    {{ $product->name }}
                </strong>

                <div class="mt-3">

                    <strong>
                        {{ number_format($effectivePrice, 2) }}
                    </strong>

                </div>

                <div class="mt-3">

                    <a
                        href="{{ url('/checkout') }}"
                        class="btn-buy-now w-100"
                    >
                        Continuar al checkout
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

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
     */

    document.querySelectorAll('.qty-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            const input = this
                .closest('.qty-stepper')
                .querySelector('.qty-input');

            if (!input) {
                return;
            }

            let value = parseInt(input.value, 10) || 1;

            if (this.dataset.action === 'plus') {
                value++;
            }

            if (this.dataset.action === 'minus') {
                value--;
            }

            const min = parseInt(input.min || 1, 10);
            const max = parseInt(input.max || 99, 10);

            value = Math.max(min, Math.min(max, value));

            input.value = value;

        });

    });


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

});
</script>

@endpush