<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds website-level settings with the exact values currently hardcoded in
 * layouts/storefront.blade.php, so the storefront renders identically once the
 * layout is wired to read from site_settings. Idempotent: firstOrCreate never
 * overwrites values an admin has already edited.
 */
class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'DE Soluciones',

            // Rotating top bar (icon = Bootstrap Icons class, text = message)
            'announcement_items' => [
                ['icon' => 'bi-truck', 'text' => 'ENVÍO A TODO EL PAÍS'],
                ['icon' => 'bi-cash-coin', 'text' => 'PAGO CONTRA ENTREGA'],
                ['icon' => 'bi-lightning-charge-fill', 'text' => 'OFERTAS TODOS LOS DÍAS'],
                ['icon' => 'bi-shield-check', 'text' => 'COMPRA 100% SEGURA'],
            ],

            // Main header nav strip
            'header_menu' => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Categorías', 'url' => '/catalogo'],
                ['label' => 'Más vendidos', 'url' => '/#mas-vendidos'],
                ['label' => 'Ofertas', 'url' => '/ofertas', 'hot' => true],
                ['label' => 'Especial del día', 'url' => '/#especial'],
                ['label' => 'Marcas', 'url' => '/#marcas'],
                ['label' => 'Contacto', 'url' => '/#contacto'],
            ],

            // Footer column 1
            'footer_menu_tienda' => [
                'heading' => 'Tienda',
                'items' => [
                    ['label' => 'Categorías', 'url' => '/catalogo'],
                    ['label' => 'Más vendidos', 'url' => '/#mas-vendidos'],
                    ['label' => 'Ofertas', 'url' => '/ofertas'],
                    ['label' => 'Marcas', 'url' => '/#marcas'],
                ],
            ],

            // Footer column 2
            'footer_menu_ayuda' => [
                'heading' => 'Ayuda',
                'items' => [
                    ['label' => 'Preguntas frecuentes', 'url' => '#'],
                    ['label' => 'Envíos y entregas', 'url' => '#'],
                    ['label' => 'Cambios y devoluciones', 'url' => '#'],
                    ['label' => 'Términos y condiciones', 'url' => '/terminos'],
                ],
            ],

            'social_links' => [
                'instagram' => '#',
                'facebook' => '#',
                'whatsapp' => '#',
                'tiktok' => '#',
            ],

            'footer_about' => 'Tienda especializada en tecnología, herramientas y bienestar, con envío a todo el país y pago contra entrega.',

            'contact_address' => 'Ciudad, País',
            'contact_phone'   => '+00 000 000 0000',
            'contact_email'   => 'contacto@de-soluciones.com',
            'support_phone'   => '+00 000 0000',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            );
        }

        SiteSetting::flush();
    }
}
