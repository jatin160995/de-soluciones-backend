@extends('layouts.storefront')

@section('title', 'Ofertas Especiales')

@section('meta_description', 'Aprovecha las ofertas especiales en tecnología, herramientas y bienestar con envío gratis y pago contra entrega.')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb-bar">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-trail">
                <li>
                    <a href="{{ route('home') }}">Inicio</a>
                </li>

                <li class="active" aria-current="page">
                    Ofertas
                </li>
            </ol>
        </nav>

        <h1 class="breadcrumb-title">
            Ofertas Especiales
        </h1>
    </div>
</div>


{{-- Offers hero --}}
<section class="offers-banner">
    <div class="container">
        <div class="offers-banner-inner">

            <div class="offers-banner-copy">

                <span class="offers-banner-flash">
                    <i class="bi bi-fire"></i>
                    OFERTAS DE LA SEMANA
                </span>

                <h2>
                    Hasta {{ $maxDiscount }}% de descuento en productos seleccionados
                </h2>

                <p>
                    Envío gratis incluido en todos los productos.
                    Aprovecha antes de que se agoten.
                </p>

            </div>


            <div class="offers-banner-countdown">

                <span class="offers-banner-countdown-label">
                    Termina en:
                </span>

                <div
                    class="countdown"
                    id="countdown"
                    data-end="{{ now()->endOfWeek()->setTime(23, 59, 59)->toIso8601String() }}"
                >

                    <div class="cd-box">
                        <span id="cd-h">00</span>
                        <small>Horas</small>
                    </div>

                    <div class="cd-sep">:</div>

                    <div class="cd-box">
                        <span id="cd-m">00</span>
                        <small>Min</small>
                    </div>

                    <div class="cd-sep">:</div>

                    <div class="cd-box">
                        <span id="cd-s">00</span>
                        <small>Seg</small>
                    </div>

                </div>

            </div>

        </div>
    </div>
</section>


{{-- Catalog --}}
<section class="section-pad-sm">

    <div class="container">

        <div class="row g-4">


            {{-- Desktop filters --}}
            <aside class="col-lg-3 d-none d-lg-block">

                <div class="filter-sidebar">


                    {{-- Categories --}}
                    @if($categories->isNotEmpty())

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
                                                name="categories_filter"
                                                value="{{ $category->slug }}"
                                                form="offers-filter-form"
                                                @checked(in_array($category->slug, $selectedCategories, true))
                                            >

                                            <span>
                                                {{ $category->name }}
                                            </span>

                                            <em>
                                                ({{ $category->offers_count }})
                                            </em>

                                        </label>

                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- Price --}}
                    <div class="filter-block">

                        <h4 class="filter-block-title">
                            Precio
                        </h4>

                        <div class="price-range-inputs">

                            <input
                                type="number"
                                class="form-control"
                                name="min_price"
                                form="offers-filter-form"
                                placeholder="Mín."
                                min="0"
                                value="{{ $minPrice }}"
                            >

                            <span>—</span>

                            <input
                                type="number"
                                class="form-control"
                                name="max_price"
                                form="offers-filter-form"
                                placeholder="Máx."
                                min="0"
                                value="{{ $maxPrice }}"
                            >

                        </div>

                        <button
                            type="submit"
                            form="offers-filter-form"
                            class="btn-filter-apply"
                        >
                            Aplicar
                        </button>

                    </div>


                    {{-- Minimum discount --}}
                    <div class="filter-block">

                        <h4 class="filter-block-title">
                            Descuento mínimo
                        </h4>

                        <ul class="filter-check-list">

                            @foreach([10, 20, 30] as $discount)

                                <li>

                                    <label>

                                        <input
                                            type="radio"
                                            name="min_discount"
                                            value="{{ $discount }}"
                                            form="offers-filter-form"
                                            @checked((string) $minDiscount === (string) $discount)
                                        >

                                        <span>
                                            {{ $discount }}% o más
                                        </span>

                                    </label>

                                </li>

                            @endforeach

                        </ul>

                    </div>


                    {{-- Ratings intentionally omitted --}}
                    {{--
                        There is currently no reviews/ratings system in the backend.
                        We should not display fake rating filters on the storefront.
                    --}}


                    <a
                        href="{{ route('offers') }}"
                        class="btn-filter-clear"
                    >
                        <i class="bi bi-x-circle"></i>
                        Limpiar filtros
                    </a>

                </div>

            </aside>


            {{-- Product listing --}}
            <div class="col-lg-9">


                {{-- Filter form --}}
                <form
                    id="offers-filter-form"
                    method="GET"
                    action="{{ route('offers') }}"
                >

                    {{-- Hidden category values --}}
                    @foreach($selectedCategories as $selectedCategory)

                        <input
                            type="hidden"
                            name="categorias[]"
                            value="{{ $selectedCategory }}"
                            data-category-hidden="{{ $selectedCategory }}"
                        >

                    @endforeach

                    <input
                        type="hidden"
                        name="sort"
                        value="{{ $sort }}"
                    >

                </form>


                {{-- Toolbar --}}
                <div class="catalog-toolbar">


                    {{-- Mobile filters --}}
                    <button
                        class="btn-filter-mobile d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileFilters"
                    >
                        <i class="bi bi-sliders"></i>
                        Filtros
                    </button>


                    <p class="catalog-count">

                        Mostrando

                        <strong>
                            {{ $offers->firstItem() ?? 0 }}–{{ $offers->lastItem() ?? 0 }}
                        </strong>

                        de

                        <strong>
                            {{ $offers->total() }}
                        </strong>

                        ofertas

                    </p>


                    <div class="catalog-toolbar-right">


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


                        <form
                            method="GET"
                            action="{{ route('offers') }}"
                        >

                            @foreach($selectedCategories as $category)

                                <input
                                    type="hidden"
                                    name="categorias[]"
                                    value="{{ $category }}"
                                >

                            @endforeach

                            @if($minPrice !== null && $minPrice !== '')
                                <input
                                    type="hidden"
                                    name="min_price"
                                    value="{{ $minPrice }}"
                                >
                            @endif

                            @if($maxPrice !== null && $maxPrice !== '')
                                <input
                                    type="hidden"
                                    name="max_price"
                                    value="{{ $maxPrice }}"
                                >
                            @endif

                            @if($minDiscount !== null && $minDiscount !== '')
                                <input
                                    type="hidden"
                                    name="min_discount"
                                    value="{{ $minDiscount }}"
                                >
                            @endif


                            <select
                                class="catalog-sort form-select"
                                name="sort"
                                aria-label="Ordenar por"
                                onchange="this.form.submit()"
                            >

                                <option
                                    value="discount"
                                    @selected($sort === 'discount')
                                >
                                    Mayor descuento
                                </option>

                                <option
                                    value="price_asc"
                                    @selected($sort === 'price_asc')
                                >
                                    Precio: menor a mayor
                                </option>

                                <option
                                    value="price_desc"
                                    @selected($sort === 'price_desc')
                                >
                                    Precio: mayor a menor
                                </option>

                                <option
                                    value="latest"
                                    @selected($sort === 'latest')
                                >
                                    Más recientes
                                </option>

                            </select>

                        </form>

                    </div>

                </div>


                {{-- Products --}}
                @if($offers->isNotEmpty())

                    <div class="row g-4 product-grid">

                        @foreach($offers as $product)

                            @include('partials.product-card', [
                                'product' => $product,
                            ])

                        @endforeach

                    </div>


                    {{-- Pagination --}}
                    @if($offers->hasPages())

                        <div class="mt-5">

                            {{ $offers->onEachSide(1)->links() }}

                        </div>

                    @endif

                @else

                    <div class="text-center py-5">

                        <div class="mb-3">
                            <i
                                class="bi bi-tags"
                                style="font-size: 3rem;"
                            ></i>
                        </div>

                        <h3>
                            No encontramos ofertas
                        </h3>

                        <p class="text-muted mb-4">
                            No hay productos que coincidan con los filtros seleccionados.
                        </p>

                        <a
                            href="{{ route('offers') }}"
                            class="btn btn-primary"
                        >
                            Ver todas las ofertas
                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>

</section>


{{-- Mobile filters --}}
<div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="mobileFilters"
    aria-labelledby="mobileFiltersLabel"
>

    <div class="offcanvas-header">

        <h5
            class="offcanvas-title"
            id="mobileFiltersLabel"
        >
            Filtrar ofertas
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
            action="{{ route('offers') }}"
        >

            {{-- Categories --}}
            @if($categories->isNotEmpty())

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
                                        name="categorias[]"
                                        value="{{ $category->slug }}"
                                        @checked(in_array($category->slug, $selectedCategories, true))
                                    >

                                    <span>
                                        {{ $category->name }}
                                    </span>

                                    <em>
                                        ({{ $category->offers_count }})
                                    </em>

                                </label>

                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Price --}}
            <div class="filter-block">

                <h4 class="filter-block-title">
                    Precio
                </h4>

                <div class="price-range-inputs">

                    <input
                        type="number"
                        class="form-control"
                        name="min_price"
                        placeholder="Mín."
                        min="0"
                        value="{{ $minPrice }}"
                    >

                    <span>—</span>

                    <input
                        type="number"
                        class="form-control"
                        name="max_price"
                        placeholder="Máx."
                        min="0"
                        value="{{ $maxPrice }}"
                    >

                </div>

            </div>


            {{-- Discount --}}
            <div class="filter-block">

                <h4 class="filter-block-title">
                    Descuento mínimo
                </h4>

                <ul class="filter-check-list">

                    @foreach([10, 20, 30] as $discount)

                        <li>

                            <label>

                                <input
                                    type="radio"
                                    name="min_discount"
                                    value="{{ $discount }}"
                                    @checked((string) $minDiscount === (string) $discount)
                                >

                                <span>
                                    {{ $discount }}% o más
                                </span>

                            </label>

                        </li>

                    @endforeach

                </ul>

            </div>


            <input
                type="hidden"
                name="sort"
                value="{{ $sort }}"
            >


            <button
                type="submit"
                class="btn-filter-apply w-100"
            >
                Aplicar filtros
            </button>


            <a
                href="{{ route('offers') }}"
                class="btn-filter-clear w-100 text-center mt-2"
            >
                <i class="bi bi-x-circle"></i>
                Limpiar filtros
            </a>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * Offers countdown
     *
     * The backend provides the end of the current week.
     */
    const countdown = document.getElementById('countdown');

    if (!countdown) {
        return;
    }

    const endDate = new Date(countdown.dataset.end).getTime();

    const hoursElement = document.getElementById('cd-h');
    const minutesElement = document.getElementById('cd-m');
    const secondsElement = document.getElementById('cd-s');

    function updateCountdown() {

        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance <= 0) {

            hoursElement.textContent = '00';
            minutesElement.textContent = '00';
            secondsElement.textContent = '00';

            return;
        }

        const totalSeconds = Math.floor(distance / 1000);

        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        hoursElement.textContent = String(hours).padStart(2, '0');
        minutesElement.textContent = String(minutes).padStart(2, '0');
        secondsElement.textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();

    setInterval(updateCountdown, 1000);
});


/*
 * Desktop category filters
 *
 * The actual category checkboxes live outside the GET form
 * because the sidebar has its own layout. When the user clicks
 * a checkbox we maintain the hidden categorias[] values.
 */
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('offers-filter-form');

    if (!form) {
        return;
    }

    const categoryInputs = document.querySelectorAll(
        'input[name="categories_filter"]'
    );

    categoryInputs.forEach(function (input) {

        input.addEventListener('change', function () {

            form.querySelectorAll(
                'input[data-category-hidden]'
            ).forEach(function (hiddenInput) {
                hiddenInput.remove();
            });

            categoryInputs.forEach(function (checkbox) {

                if (!checkbox.checked) {
                    return;
                }

                const hidden = document.createElement('input');

                hidden.type = 'hidden';
                hidden.name = 'categorias[]';
                hidden.value = checkbox.value;
                hidden.dataset.categoryHidden = checkbox.value;

                form.appendChild(hidden);
            });

            /*
             * Submit immediately after selecting a category.
             */
            form.submit();
        });

    });

});
</script>

@endpush