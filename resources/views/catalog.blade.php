@extends('layouts.storefront')

@php
    $symbol = config('store.currency_symbol');

    $totalProducts = $products->total();

    $from = $products->firstItem() ?? 0;
    $to = $products->lastItem() ?? 0;

    $selectedCategorySlug = request('categoria');

    $hasFilters =
        request()->filled('q') ||
        request()->filled('categoria') ||
        request()->filled('min') ||
        request()->filled('max') ||
        request()->boolean('stock') ||
        request()->boolean('ofertas');
@endphp

@section('title', 'Catálogo de Productos | ' . ($siteName ?? 'DE Soluciones'))

@section(
    'meta_description',
    'Explora todo el catálogo de DE Soluciones: tecnología, herramientas, bienestar, hogar y jardín con pago contra entrega.'
)

@section('content')

<!-- ========================= -->
<!-- Breadcrumb -->
<!-- ========================= -->

<div class="breadcrumb-bar">

    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">

        <nav aria-label="breadcrumb">

            <ol class="breadcrumb-trail">

                <li>
                    <a href="{{ route('home') }}">
                        Inicio
                    </a>
                </li>

                <li class="active" aria-current="page">
                    Catálogo
                </li>

            </ol>

        </nav>

        <h1 class="breadcrumb-title">
            Catálogo de Productos
        </h1>

    </div>

</div>


<!-- ========================= -->
<!-- Catalog -->
<!-- ========================= -->

<section class="section-pad-sm">

    <div class="container">

        <div class="row g-4">


            <!-- ====================================== -->
            <!-- Desktop Sidebar -->
            <!-- ====================================== -->

            <aside class="col-lg-3 d-none d-lg-block">

                <form
                    method="GET"
                    action="{{ route('catalog') }}"
                    class="filter-sidebar"
                >

                    @if(request('q'))
                        <input
                            type="hidden"
                            name="q"
                            value="{{ request('q') }}"
                        >
                    @endif

                    <!-- Categories -->

                    <div class="filter-block">

                        <h4 class="filter-block-title">
                            Categorías
                        </h4>

                        <ul class="filter-check-list">

                            @foreach($categories as $category)

                                <li>

                                    <label>

                                        <input
                                            type="checkbox"
                                            name="categoria"
                                            value="{{ $category->slug }}"
                                            {{ $selectedCategorySlug === $category->slug ? 'checked' : '' }}
                                            onchange="this.form.submit()"
                                        >

                                        <span>
                                            {{ $category->name }}
                                        </span>

                                        <em>
                                            ({{ $category->products_count }})
                                        </em>

                                    </label>

                                </li>

                            @endforeach

                        </ul>

                    </div>


                    <!-- Price -->

                    <div class="filter-block">

                        <h4 class="filter-block-title">
                            Precio
                        </h4>

                        <div class="price-range-inputs">

                            <input
                                type="number"
                                name="min"
                                class="form-control"
                                placeholder="Mín."
                                value="{{ request('min') }}"
                                min="0"
                                step="0.01"
                            >

                            <span>—</span>

                            <input
                                type="number"
                                name="max"
                                class="form-control"
                                placeholder="Máx."
                                value="{{ request('max') }}"
                                min="0"
                                step="0.01"
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn-filter-apply"
                        >
                            Aplicar
                        </button>

                    </div>


                    <!-- Availability -->

                    <div class="filter-block">

                        <h4 class="filter-block-title">
                            Disponibilidad
                        </h4>

                        <ul class="filter-check-list">

                            <li>

                                <label>

                                    <input
                                        type="checkbox"
                                        name="stock"
                                        value="1"
                                        {{ request()->boolean('stock') ? 'checked' : '' }}
                                    >

                                    <span>
                                        Solo en existencia
                                    </span>

                                </label>

                            </li>


                            <li>

                                <label>

                                    <input
                                        type="checkbox"
                                        name="ofertas"
                                        value="1"
                                        {{ request()->boolean('ofertas') ? 'checked' : '' }}
                                    >

                                    <span>
                                        Incluir ofertas
                                    </span>

                                </label>

                            </li>

                        </ul>

                        <button
                            type="submit"
                            class="btn-filter-apply mt-2"
                        >
                            Aplicar
                        </button>

                    </div>


                    <!-- Clear -->

                    @if($hasFilters)

                        <a
                            href="{{ route('catalog') }}"
                            class="btn-filter-clear"
                        >
                            <i class="bi bi-x-circle"></i>
                            Limpiar filtros
                        </a>

                    @endif

                </form>

            </aside>


            <!-- ====================================== -->
            <!-- Product Listing -->
            <!-- ====================================== -->

            <div class="col-lg-9">


                <!-- Toolbar -->

                <div class="catalog-toolbar">


                    <!-- Mobile filters -->

                    <button
                        class="btn-filter-mobile d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileFilters"
                    >
                        <i class="bi bi-sliders"></i>
                        Filtros
                    </button>


                    <!-- Count -->

                    <p class="catalog-count">

                        @if(!empty($searchTerm))

                            Resultados para

                            <strong>
                                «{{ $searchTerm }}»
                            </strong>

                            ·

                        @endif

                        Mostrando

                        <strong>
                            {{ $from }}–{{ $to }}
                        </strong>

                        de

                        <strong>
                            {{ $totalProducts }}
                        </strong>

                        productos

                    </p>


                    <div class="catalog-toolbar-right">


                        <!-- View toggle -->

                        <div class="view-toggle">

                            <button
                                type="button"
                                class="active"
                                aria-label="Ver en cuadrícula"
                            >
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </button>

                            <button
                                type="button"
                                aria-label="Ver en lista"
                            >
                                <i class="bi bi-list-ul"></i>
                            </button>

                        </div>


                        <!-- Sort -->

                        <form
                            method="GET"
                            action="{{ route('catalog') }}"
                        >

                            @if(request('q'))
                                <input
                                    type="hidden"
                                    name="q"
                                    value="{{ request('q') }}"
                                >
                            @endif

                            @if(request('categoria'))
                                <input
                                    type="hidden"
                                    name="categoria"
                                    value="{{ request('categoria') }}"
                                >
                            @endif

                            @if(request('min'))
                                <input
                                    type="hidden"
                                    name="min"
                                    value="{{ request('min') }}"
                                >
                            @endif

                            @if(request('max'))
                                <input
                                    type="hidden"
                                    name="max"
                                    value="{{ request('max') }}"
                                >
                            @endif

                            @if(request()->boolean('stock'))
                                <input
                                    type="hidden"
                                    name="stock"
                                    value="1"
                                >
                            @endif

                            @if(request()->boolean('ofertas'))
                                <input
                                    type="hidden"
                                    name="ofertas"
                                    value="1"
                                >
                            @endif

                            <select
                                name="sort"
                                class="catalog-sort form-select"
                                aria-label="Ordenar por"
                                onchange="this.form.submit()"
                            >

                                <option
                                    value="relevant"
                                    {{ $filters['sort'] === 'relevant' ? 'selected' : '' }}
                                >
                                    Más relevantes
                                </option>

                                <option
                                    value="price_asc"
                                    {{ $filters['sort'] === 'price_asc' ? 'selected' : '' }}
                                >
                                    Precio: menor a mayor
                                </option>

                                <option
                                    value="price_desc"
                                    {{ $filters['sort'] === 'price_desc' ? 'selected' : '' }}
                                >
                                    Precio: mayor a menor
                                </option>

                                <option
                                    value="newest"
                                    {{ $filters['sort'] === 'newest' ? 'selected' : '' }}
                                >
                                    Más recientes
                                </option>

                            </select>

                        </form>

                    </div>

                </div>


                <!-- ====================================== -->
                <!-- Product Grid -->
                <!-- ====================================== -->

                @if($products->isNotEmpty())

                    <div class="row g-4 product-grid">

                        @foreach($products as $product)

                            @php

                                $hasDiscount =
                                    $product->discounted_price !== null &&
                                    $product->discounted_price < $product->base_price;

                                $displayPrice =
                                    $hasDiscount
                                        ? $product->discounted_price
                                        : $product->base_price;

                                $pct =
                                    $hasDiscount
                                        ? round(
                                            (
                                                1 -
                                                (
                                                    $product->discounted_price /
                                                    $product->base_price
                                                )
                                            ) * 100
                                        )
                                        : null;

                                $image =
                                    $product->getFirstMediaUrl(
                                        'images',
                                        'thumb'
                                    );

                            @endphp


                            <div class="col-6 col-md-4 col-lg-4 product-item">

                                <div class="product-card">


                                    <!-- Product media -->

                                    <div class="product-media">


                                        @if($hasDiscount)

                                            <span class="ribbon ribbon-sale">
                                                -{{ $pct }}%
                                            </span>

                                        @elseif($product->is_featured)

                                            <span class="ribbon ribbon-best">
                                                MÁS VENDIDO
                                            </span>

                                        @endif


                                        <div class="product-quick-actions">

                                            <button
                                                type="button"
                                                aria-label="Agregar a favoritos"
                                            >
                                                <i class="bi bi-heart"></i>
                                            </button>

                                            <button
                                                type="button"
                                                aria-label="Vista rápida"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </button>

                                        </div>


                                        <a
                                            href="{{ route('product.show', $product->slug) }}"
                                        >

                                            <img
                                                src="{{ $image }}"
                                                alt="{{ $product->name }}"
                                                width="380"
                                                height="320"
                                                loading="lazy"
                                            >

                                        </a>

                                    </div>


                                    <!-- Product info -->

                                    <div class="product-info">


                                        @if($product->category)

                                            <span class="product-cat">

                                                {{ $product->category->name }}

                                            </span>

                                        @endif


                                        <h3>

                                            <a
                                                href="{{ route('product.show', $product->slug) }}"
                                            >
                                                {{ $product->name }}
                                            </a>

                                        </h3>


                                        <!--
                                            Reviews are intentionally NOT shown.
                                            There is no reviews/ratings system yet.
                                        -->


                                        <div class="price-row">

                                            <span class="price">

                                                {{ $symbol }}

                                                {{ number_format($displayPrice, 2) }}

                                            </span>


                                            @if($hasDiscount)

                                                <span class="price-old">

                                                    {{ $symbol }}

                                                    {{ number_format($product->base_price, 2) }}

                                                </span>


                                                <span class="off-tag">
                                                    -{{ $pct }}%
                                                </span>

                                            @endif

                                        </div>


                                        <button
                                            class="btn-cart"
                                            type="button"
                                            data-product-id="{{ $product->id }}"
                                        >

                                            <i class="bi bi-cart-plus"></i>

                                            Agregar

                                        </button>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                @else

                    <!-- Empty state -->

                    <div class="text-center py-5">

                        <i
                            class="bi bi-search"
                            style="font-size: 48px;"
                        ></i>

                        <h3 class="mt-3">
                            No encontramos productos
                        </h3>

                        <p>
                            Intenta cambiar los filtros o explorar todo el catálogo.
                        </p>

                        <a
                            href="{{ route('catalog') }}"
                            class="btn-buy-now"
                        >
                            Ver todos los productos
                        </a>

                    </div>

                @endif


                <!-- ====================================== -->
                <!-- Pagination -->
                <!-- ====================================== -->

                @if($products->hasPages())

                    <nav
                        class="catalog-pagination"
                        aria-label="Paginación de catálogo"
                    >

                        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}

                    </nav>

                @endif

            </div>

        </div>

    </div>

</section>


<!-- ====================================== -->
<!-- Mobile Filters -->
<!-- ====================================== -->

<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="mobileFilters"
>

    <div class="offcanvas-header">

        <h5 class="offcanvas-title">
            Filtros
        </h5>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Cerrar"
        ></button>

    </div>


    <div class="offcanvas-body">

        <form
            method="GET"
            action="{{ route('catalog') }}"
            class="filter-sidebar filter-sidebar-mobile"
        >

            @if(request('q'))
                <input
                    type="hidden"
                    name="q"
                    value="{{ request('q') }}"
                >
            @endif


            <!-- Categories -->

            <div class="filter-block">

                <h4 class="filter-block-title">
                    Categorías
                </h4>

                <ul class="filter-check-list">

                    @foreach($categories as $category)

                        <li>

                            <label>

                                <input
                                    type="radio"
                                    name="categoria"
                                    value="{{ $category->slug }}"
                                    {{ $selectedCategorySlug === $category->slug ? 'checked' : '' }}
                                >

                                <span>
                                    {{ $category->name }}
                                </span>

                                <em>
                                    ({{ $category->products_count }})
                                </em>

                            </label>

                        </li>

                    @endforeach

                </ul>

            </div>


            <!-- Price -->

            <div class="filter-block">

                <h4 class="filter-block-title">
                    Precio
                </h4>

                <div class="price-range-inputs">

                    <input
                        type="number"
                        name="min"
                        class="form-control"
                        placeholder="Mín."
                        value="{{ request('min') }}"
                        min="0"
                    >

                    <span>—</span>

                    <input
                        type="number"
                        name="max"
                        class="form-control"
                        placeholder="Máx."
                        value="{{ request('max') }}"
                        min="0"
                    >

                </div>

            </div>


            <!-- Availability -->

            <div class="filter-block">

                <h4 class="filter-block-title">
                    Disponibilidad
                </h4>

                <ul class="filter-check-list">

                    <li>

                        <label>

                            <input
                                type="checkbox"
                                name="stock"
                                value="1"
                                {{ request()->boolean('stock') ? 'checked' : '' }}
                            >

                            <span>
                                Solo en existencia
                            </span>

                        </label>

                    </li>


                    <li>

                        <label>

                            <input
                                type="checkbox"
                                name="ofertas"
                                value="1"
                                {{ request()->boolean('ofertas') ? 'checked' : '' }}
                            >

                            <span>
                                Incluir ofertas
                            </span>

                        </label>

                    </li>

                </ul>

            </div>


            <button
                type="submit"
                class="btn-filter-apply"
            >
                Aplicar filtros
            </button>


            @if($hasFilters)

                <a
                    href="{{ route('catalog') }}"
                    class="btn-filter-clear"
                >
                    <i class="bi bi-x-circle"></i>
                    Limpiar filtros
                </a>

            @endif

        </form>

    </div>

</div>

@endsection