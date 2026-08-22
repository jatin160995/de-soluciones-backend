-- ============================================================================
-- cart_items → product-aware
-- ============================================================================
--
-- Hand-run equivalent of
-- database/migrations/2026_08_22_000001_make_cart_items_product_aware.php.
--
-- WHY THIS EXISTS
--   cart_items was created with variant_id NOT NULL, but almost no product in
--   this catalogue has variant rows — so the cart could only ever hold the one
--   product that does. order_items already solved this the same way: variant_id
--   is nullable and the line hangs off the product.
--
--   variant_key exists purely so the uniqueness constraint works. A plain
--   UNIQUE (cart_id, product_id, variant_id) would not dedupe variant-less
--   lines, because MariaDB treats every NULL as distinct — the same product
--   could be inserted twice. Collapsing NULL to 0 in a STORED generated column
--   gives a real constraint. product_variants already uses this trick for
--   attr_size / attr_color.
--
-- WHY STEP 1 COMES FIRST
--   The table shipped with cart_items_cart_variant_unique (cart_id, variant_id)
--   as the only index having cart_id leftmost, so cart_items_cart_fk was
--   leaning on it. Dropping that index without a replacement fails outright:
--
--     1553 Cannot drop index 'cart_items_cart_variant_unique':
--          needed in a foreign key constraint
--
--   cart_items_cart_idx is therefore added first, and kept permanently — the
--   migration's down() drops the new composite unique, which would otherwise
--   leave the cart FK homeless and hit the same error in reverse.
--
-- SAFE TO RE-RUN. Every statement is guarded, so it works from a clean table or
-- from a half-applied one.
--
-- Requires MariaDB 10.0+ (10.4.28 here) for IF [NOT] EXISTS on DDL.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- 1. Give cart_items_cart_fk an index of its own. Must precede step 3.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  ADD INDEX IF NOT EXISTS `cart_items_cart_idx` (`cart_id`);


-- ----------------------------------------------------------------------------
-- 2. The FK sits on the column about to become nullable, so it goes now and
--    comes back in step 8.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  DROP FOREIGN KEY IF EXISTS `cart_items_variant_fk`;


-- ----------------------------------------------------------------------------
-- 3. Retire the old constraint. Superseded by step 7.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  DROP INDEX IF EXISTS `cart_items_cart_variant_unique`;


-- ----------------------------------------------------------------------------
-- 4. The new shape.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  ADD COLUMN IF NOT EXISTS `product_id` BIGINT UNSIGNED NOT NULL AFTER `cart_id`,
  ADD INDEX  IF NOT EXISTS `cart_items_product_idx` (`product_id`);


-- ----------------------------------------------------------------------------
-- 5. Widen variant_id.
--
--    Guarded on current nullability rather than written as a plain MODIFY:
--    once step 6 has run, variant_key reads from this column, and MariaDB is
--    uncooperative about altering a column a stored generated column depends
--    on. This way a re-run skips the statement entirely instead of risking it.
-- ----------------------------------------------------------------------------
SET @variant_id_is_not_null := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'cart_items'
    AND COLUMN_NAME  = 'variant_id'
    AND IS_NULLABLE  = 'NO'
);

SET @sql := IF(
  @variant_id_is_not_null > 0,
  'ALTER TABLE `cart_items` MODIFY `variant_id` BIGINT UNSIGNED NULL',
  'DO 0'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ----------------------------------------------------------------------------
-- 6. The generated column the unique constraint needs.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  ADD COLUMN IF NOT EXISTS `variant_key` BIGINT UNSIGNED
    GENERATED ALWAYS AS (IFNULL(`variant_id`, 0)) STORED;


-- ----------------------------------------------------------------------------
-- 7. Backfill before the FK in step 8 can object.
--
--    ADD COLUMN ... NOT NULL with no default fills integers with 0, which the
--    incoming FK rejects. Any pre-existing row has a variant (the column was
--    NOT NULL until step 5), so its product is recoverable from there.
--
--    Rows still at 0 point at a variant that no longer exists — already dead
--    lines that simply had nothing to fail against before.
-- ----------------------------------------------------------------------------
UPDATE `cart_items` `ci`
  INNER JOIN `product_variants` `pv` ON `pv`.`id` = `ci`.`variant_id`
SET `ci`.`product_id` = `pv`.`product_id`
WHERE `ci`.`product_id` = 0;

DELETE FROM `cart_items` WHERE `product_id` = 0;


-- ----------------------------------------------------------------------------
-- 8. The real constraint, plus both FKs back.
--    Runs after the backfill so duplicate product_id = 0 rows can't collide.
-- ----------------------------------------------------------------------------
ALTER TABLE `cart_items`
  ADD UNIQUE INDEX IF NOT EXISTS `cart_items_cart_product_variant_unique`
    (`cart_id`, `product_id`, `variant_key`);

ALTER TABLE `cart_items`
  ADD CONSTRAINT IF NOT EXISTS `cart_items_product_fk`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT IF NOT EXISTS `cart_items_variant_fk`
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;


-- ----------------------------------------------------------------------------
-- 9. Record it, so `php artisan migrate` never attempts this again and
--    `migrate:status` tells the truth.
-- ----------------------------------------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_22_000001_make_cart_items_product_aware',
       COALESCE((SELECT MAX(`batch`) FROM `migrations` `m`), 0) + 1
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` `m2`
  WHERE `m2`.`migration` = '2026_08_22_000001_make_cart_items_product_aware'
);
