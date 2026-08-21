<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website-level (platform-wide) key/value settings.
 *
 * Unlike `store_settings` (which is scoped per store_id), these settings apply
 * to the whole website — the shared chrome that stays the same across every
 * store in the future multi-vendor phase (announcement bar, header/footer
 * menus, social links, contact info, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable(); // JSON-encoded (scalar or structured)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
