<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Makes `cart_items` product-aware.
 *
 * The table was created with `variant_id` NOT NULL, but almost no product in
 * this catalogue has variant rows — so as it stood the cart could only ever
 * hold the one product that does. `order_items` already solved this the same
 * way: `variant_id` is nullable and the line hangs off the product.
 *
 * `variant_key` exists purely so the uniqueness constraint works. A plain
 * UNIQUE (cart_id, product_id, variant_id) would not dedupe variant-less
 * lines, because MySQL/MariaDB treats every NULL as distinct — the same
 * product could be inserted twice. Collapsing NULL to 0 in a STORED generated
 * column gives us a real constraint. `product_variants` already uses this
 * trick for attr_size / attr_color.
 *
 * ---------------------------------------------------------------------------
 * Two things about the shape of this file
 * ---------------------------------------------------------------------------
 *
 * 1. `cart_items_cart_idx` is added first and never removed. The table shipped
 *    with `cart_items_cart_variant_unique (cart_id, variant_id)` as the only
 *    index having `cart_id` leftmost, so `cart_items_cart_fk` was leaning on
 *    it. Dropping that index without a replacement fails outright:
 *
 *      1553 Cannot drop index 'cart_items_cart_variant_unique':
 *           needed in a foreign key constraint
 *
 *    Giving the FK a dedicated index of its own also keeps down() safe — that
 *    method drops the new composite unique, which would otherwise leave the
 *    cart FK homeless and hit the same error in reverse.
 *
 * 2. Every step is guarded. DDL does not participate in a transaction on
 *    MySQL/MariaDB, so a migration that fails midway leaves the earlier
 *    statements committed and the next attempt dies on ground it already
 *    covered. Checking information_schema first makes this re-runnable from
 *    whatever state the table is actually in.
 *
 * MySQL/MariaDB specific by necessity (generated columns, named FKs).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Must come before the composite unique is dropped — see note 1 above.
        if (! $this->hasIndex('cart_items_cart_idx')) {
            DB::statement('ALTER TABLE `cart_items` ADD KEY `cart_items_cart_idx` (`cart_id`)');
        }

        // The FK sits on the column we're about to make nullable, so it has to
        // go first and come back at the end.
        if ($this->hasForeignKey('cart_items_variant_fk')) {
            DB::statement('ALTER TABLE `cart_items` DROP FOREIGN KEY `cart_items_variant_fk`');
        }

        if ($this->hasIndex('cart_items_cart_variant_unique')) {
            DB::statement('DROP INDEX `cart_items_cart_variant_unique` ON `cart_items`');
        }

        if (! $this->hasColumn('product_id')) {
            DB::statement('ALTER TABLE `cart_items` ADD COLUMN `product_id` BIGINT UNSIGNED NOT NULL AFTER `cart_id`');
        }

        /*
         * Widened before `variant_key` is created, not after: MariaDB is
         * uncooperative about MODIFYing a column a stored generated column
         * reads from.
         */
        if (! $this->variantIdIsNullable()) {
            DB::statement('ALTER TABLE `cart_items` MODIFY `variant_id` BIGINT UNSIGNED NULL');
        }

        if (! $this->hasColumn('variant_key')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD COLUMN `variant_key` BIGINT UNSIGNED
                        GENERATED ALWAYS AS (IFNULL(`variant_id`, 0)) STORED
            SQL);
        }

        /*
         * ADD COLUMN ... NOT NULL with no default backfills integers with 0,
         * which the incoming FK would then reject. Any pre-existing row has a
         * variant (the column was NOT NULL until a moment ago), so the product
         * it belongs to is recoverable from there.
         */
        DB::statement(<<<'SQL'
            UPDATE `cart_items` `ci`
                INNER JOIN `product_variants` `pv` ON `pv`.`id` = `ci`.`variant_id`
            SET `ci`.`product_id` = `pv`.`product_id`
            WHERE `ci`.`product_id` = 0
        SQL);

        // Anything still at 0 points at a variant that no longer exists, so the
        // line was already dead — it just had nothing to fail against before.
        DB::statement('DELETE FROM `cart_items` WHERE `product_id` = 0');

        if (! $this->hasIndex('cart_items_cart_product_variant_unique')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD UNIQUE KEY `cart_items_cart_product_variant_unique`
                        (`cart_id`, `product_id`, `variant_key`)
            SQL);
        }

        if (! $this->hasIndex('cart_items_product_idx')) {
            DB::statement('ALTER TABLE `cart_items` ADD KEY `cart_items_product_idx` (`product_id`)');
        }

        if (! $this->hasForeignKey('cart_items_product_fk')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD CONSTRAINT `cart_items_product_fk` FOREIGN KEY (`product_id`)
                        REFERENCES `products` (`id`) ON DELETE CASCADE
            SQL);
        }

        if (! $this->hasForeignKey('cart_items_variant_fk')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD CONSTRAINT `cart_items_variant_fk` FOREIGN KEY (`variant_id`)
                        REFERENCES `product_variants` (`id`) ON DELETE CASCADE
            SQL);
        }
    }

    public function down(): void
    {
        foreach (['cart_items_variant_fk', 'cart_items_product_fk'] as $constraint) {
            if ($this->hasForeignKey($constraint)) {
                DB::statement("ALTER TABLE `cart_items` DROP FOREIGN KEY `{$constraint}`");
            }
        }

        // `cart_items_cart_idx` deliberately survives this: dropping the
        // composite unique below is only legal while some other index still
        // covers `cart_id` for `cart_items_cart_fk`.
        foreach (['cart_items_cart_product_variant_unique', 'cart_items_product_idx'] as $index) {
            if ($this->hasIndex($index)) {
                DB::statement("DROP INDEX `{$index}` ON `cart_items`");
            }
        }

        // Rows with a NULL variant_id cannot exist in the pre-migration shape.
        DB::statement('DELETE FROM `cart_items` WHERE `variant_id` IS NULL');

        // Generated column goes before the MODIFY it would otherwise block.
        if ($this->hasColumn('variant_key')) {
            DB::statement('ALTER TABLE `cart_items` DROP COLUMN `variant_key`');
        }

        if ($this->hasColumn('product_id')) {
            DB::statement('ALTER TABLE `cart_items` DROP COLUMN `product_id`');
        }

        if ($this->variantIdIsNullable()) {
            DB::statement('ALTER TABLE `cart_items` MODIFY `variant_id` BIGINT UNSIGNED NOT NULL');
        }

        if (! $this->hasIndex('cart_items_cart_variant_unique')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD UNIQUE KEY `cart_items_cart_variant_unique` (`cart_id`, `variant_id`)
            SQL);
        }

        if (! $this->hasForeignKey('cart_items_variant_fk')) {
            DB::statement(<<<'SQL'
                ALTER TABLE `cart_items`
                    ADD CONSTRAINT `cart_items_variant_fk` FOREIGN KEY (`variant_id`)
                        REFERENCES `product_variants` (`id`) ON DELETE CASCADE
            SQL);
        }
    }

    protected function hasColumn(string $column): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS `found` FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['cart_items', $column]
        );
    }

    protected function hasIndex(string $index): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS `found` FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            ['cart_items', $index]
        );
    }

    protected function hasForeignKey(string $constraint): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS `found` FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            ['cart_items', $constraint, 'FOREIGN KEY']
        );
    }

    protected function variantIdIsNullable(): bool
    {
        $column = DB::selectOne(
            'SELECT IS_NULLABLE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['cart_items', 'variant_id']
        );

        return $column !== null && strtoupper($column->IS_NULLABLE) === 'YES';
    }
};
