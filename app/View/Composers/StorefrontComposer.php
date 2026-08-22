<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\Store;
use App\Services\CartService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Feeds the storefront layout (header, announcement bar, footer) with
 * website-level settings, the current store (for its logo + WhatsApp), and the
 * top-level categories used by the nav. Only runs for views that use the
 * storefront layout, so the admin panel is unaffected.
 */
class StorefrontComposer
{
    public function __construct(protected CartService $cart)
    {
    }

    public function compose(View $view): void
    {
        $site  = SiteSetting::map();
        $store = Store::query()->where('status', 'active')->first();

        $view->with([
            'siteName'          => $site['site_name'] ?? config('app.name', 'DE Soluciones'),
            'logoUrl'           => $this->logoUrl($site),
            'announcementItems' => $site['announcement_items'] ?? [],
            'headerMenu'        => $site['header_menu'] ?? [],
            'footerTienda'      => $site['footer_menu_tienda'] ?? ['heading' => 'Tienda', 'items' => []],
            'footerAyuda'       => $site['footer_menu_ayuda'] ?? ['heading' => 'Ayuda', 'items' => []],
            'socialLinks'       => $site['social_links'] ?? [],
            'footerAbout'       => $site['footer_about'] ?? '',
            'contact'           => [
                'address'       => $site['contact_address'] ?? '',
                'phone'         => $site['contact_phone'] ?? '',
                'email'         => $site['contact_email'] ?? '',
                'support_phone' => $site['support_phone'] ?? '',
            ],
            'store'         => $store,
            //'logoUrl'       => $this->logoUrl($store),
            'navCategories' => $this->navCategories(),

            /*
             * Header cart badge. Server-rendered so the count is already right
             * on first paint — no flash of an empty badge, and no JS needed for
             * plain navigation. Read-only: never seeds a carts row for a
             * visitor who hasn't added anything.
             */
            'cartCount'     => $this->cart->count(),
        ]);
    }

    /**
     * The logo comes from the stores.logo_path column (per project decision),
     * stored relative to the public disk. It is only returned when the file
     * actually exists; otherwise null tells the layout to render the built-in
     * SVG + wordmark. We intentionally do NOT fall back to the media library:
     * the legacy 'logo' media lives on the non-public 'local' disk and 404s.
     */
    /**
     * Website-level logo, stored in site_settings ('site_logo') as a path on the
     * public disk. Returns null when unset/missing so the layout falls back to
     * the built-in SVG wordmark.
     */
    protected function logoUrl(array $site): ?string
    {
        $path = $site['site_logo'] ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    protected function navCategories()
    {
        return Category::query()
            ->where('type', 'product')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
