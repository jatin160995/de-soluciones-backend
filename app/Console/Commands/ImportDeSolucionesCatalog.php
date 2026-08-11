<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class ImportDeSolucionesCatalog extends Command
{
    protected $signature = 'products:import-de-soluciones
        {file : CSV file exported from the DE Soluciones catalog}
        {--store=1 : ID of the store that will own the imported products}
        {--status=active : Product status (draft, active, or inactive)}
        {--download-images : Download image URLs into the product images media collection}
        {--skip-existing : Leave products with an existing slug unchanged}';

    protected $description = 'Import the DE Soluciones product catalog from CSV.';

    public function handle(): int
    {
        $file = $this->argument('file');
        $storeId = (int) $this->option('store');
        $status = (string) $this->option('status');

        if (! is_file($file) || ! is_readable($file)) {
            $this->error("The CSV file [{$file}] cannot be read.");

            return self::FAILURE;
        }

        if (! in_array($status, ['draft', 'active', 'inactive'], true)) {
            $this->error('The --status option must be draft, active, or inactive.');

            return self::FAILURE;
        }

        Store::query()->findOrFail($storeId);

        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle, escape: '\\');

        if ($headers === false) {
            $this->error('The CSV file is empty.');

            return self::FAILURE;
        }

        $headers = array_map(static fn ($header) => trim((string) $header), $headers);
        $requiredHeaders = ['Product Name', 'Regular Price (site $)', 'Sale Price (site $)', 'Image URL'];

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                $this->error("Missing required CSV column: {$requiredHeader}");

                return self::FAILURE;
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $imageFailures = 0;
        $rows = [];

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            $rows[] = array_combine($headers, array_pad($row, count($headers), null));
        }

        fclose($handle);

        $this->withProgressBar($rows, function (array $row) use ($storeId, $status, &$created, &$updated, &$skipped, &$imageFailures): void {
            $name = trim((string) ($row['Product Name'] ?? ''));

            if ($name === '') {
                $skipped++;

                return;
            }

            $slug = Str::slug($name);
            $product = Product::withTrashed()->where('slug', $slug)->first();

            if ($product !== null && $this->option('skip-existing')) {
                $skipped++;

                return;
            }

            $isNew = $product === null;
            $product ??= new Product(['slug' => $slug]);

            $product->fill([
                'store_id' => $storeId,
                'category_id' => null,
                'name' => $name,
                'slug' => $slug,
                'description' => $this->buildDescription($row),
                'base_price' => $this->price($row['Regular Price (site $)'] ?? null),
                'discounted_price' => $this->nullablePrice($row['Sale Price (site $)'] ?? null),
                'status' => $status,
            ]);
            $product->save();

            if ($product->trashed()) {
                $product->restore();
            }

            $isNew ? $created++ : $updated++;

            if ($this->option('download-images') && filled($row['Image URL'] ?? null)) {
                try {
                    $imageUrl = trim((string) $row['Image URL']);
                    $alreadyImported = $product->getMedia('images')
                        ->contains(fn ($media) => $media->getCustomProperty('source_image_url') === $imageUrl);

                    if (! $alreadyImported) {
                        $product->addMediaFromUrl($imageUrl)
                            ->usingName($product->name)
                            ->withCustomProperties(['source_image_url' => $imageUrl])
                            ->toMediaCollection('images');
                    }
                } catch (Throwable $exception) {
                    $imageFailures++;
                    report($exception);
                }
            }
        });

        $this->newLine(2);
        $this->info("Import complete: {$created} created, {$updated} updated, {$skipped} skipped.");

        if ($this->option('download-images')) {
            $this->line("Image downloads failed: {$imageFailures}.");
        }

        $this->warn('Categories remain empty because the source shop does not publish product-level categories.');

        return self::SUCCESS;
    }

    private function price(mixed $value): float
    {
        return (float) str_replace([',', '$', 'L.'], '', (string) $value);
    }

    private function nullablePrice(mixed $value): ?float
    {
        return blank($value) ? null : $this->price($value);
    }

    private function buildDescription(array $row): ?string
    {
        $sourceUrl = trim((string) ($row['Product URL'] ?? ''));

        return $sourceUrl === '' ? null : "Imported from: {$sourceUrl}";
    }
}
