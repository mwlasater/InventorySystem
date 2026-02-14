<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportService
{
    public const IMPORTABLE_FIELDS = [
        'name' => 'Name',
        'description' => 'Description',
        'category' => 'Category',
        'sku' => 'SKU',
        'barcode' => 'Barcode',
        'condition_rating' => 'Condition',
        'brand' => 'Brand',
        'model_number' => 'Model Number',
        'year_manufactured' => 'Year Manufactured',
        'color' => 'Color',
        'dimensions' => 'Dimensions',
        'quantity' => 'Quantity',
        'acquisition_date' => 'Acquisition Date',
        'acquisition_source' => 'Acquisition Source',
        'acquisition_method' => 'Acquisition Method',
        'purchase_price' => 'Purchase Price',
        'purchase_currency' => 'Purchase Currency',
        'estimated_value' => 'Estimated Value',
        'valuation_date' => 'Valuation Date',
        'valuation_source' => 'Valuation Source',
        'status' => 'Status',
        'location' => 'Location',
        'notes' => 'Notes',
        'tags' => 'Tags',
    ];

    /**
     * Read a CSV file and return the headers (first row).
     */
    public function parseHeaders(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        $headers = fgetcsv($handle);
        fclose($handle);

        if ($headers === false || $headers === null) {
            throw new \RuntimeException('Unable to parse CSV headers.');
        }

        // Strip BOM from the first header if present
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);

        return array_map('trim', $headers);
    }

    /**
     * Return the first N data rows (after the header) for preview.
     */
    public function previewRows(string $filePath, int $count = 5): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        // Skip header row
        $headers = fgetcsv($handle);
        if ($headers === false || $headers === null) {
            fclose($handle);
            return [];
        }

        // Strip BOM from the first header
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
        $headers = array_map('trim', $headers);

        $rows = [];
        $i = 0;
        while ($i < $count && ($row = fgetcsv($handle)) !== false) {
            // Return associative array keyed by header
            $rows[] = array_combine($headers, array_pad($row, count($headers), ''));
            $i++;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Given CSV headers, suggest mappings to item fields using fuzzy matching.
     *
     * @return array<string, string|null> ['csv_header' => 'suggested_item_field']
     */
    public function suggestMappings(array $csvHeaders): array
    {
        $importableFields = array_keys(self::IMPORTABLE_FIELDS);
        $importableLabels = array_values(self::IMPORTABLE_FIELDS);
        $mappings = [];

        foreach ($csvHeaders as $csvHeader) {
            $normalizedHeader = $this->normalizeString($csvHeader);
            $bestMatch = null;
            $bestScore = 0;

            foreach ($importableFields as $index => $field) {
                $normalizedField = $this->normalizeString($field);
                $normalizedLabel = $this->normalizeString($importableLabels[$index]);

                // Exact match on field key
                if ($normalizedHeader === $normalizedField) {
                    $bestMatch = $field;
                    $bestScore = 100;
                    break;
                }

                // Exact match on label
                if ($normalizedHeader === $normalizedLabel) {
                    $bestMatch = $field;
                    $bestScore = 100;
                    break;
                }

                // Fuzzy match using similar_text against both field key and label
                $scoreField = 0;
                similar_text($normalizedHeader, $normalizedField, $scoreField);

                $scoreLabel = 0;
                similar_text($normalizedHeader, $normalizedLabel, $scoreLabel);

                $score = max($scoreField, $scoreLabel);

                // Also check levenshtein distance for short strings
                $levField = levenshtein($normalizedHeader, $normalizedField);
                $levLabel = levenshtein($normalizedHeader, $normalizedLabel);
                $levMin = min($levField, $levLabel);

                // Convert levenshtein to a score (lower distance = higher score)
                $maxLen = max(strlen($normalizedHeader), strlen($normalizedField), strlen($normalizedLabel), 1);
                $levScore = (1 - ($levMin / $maxLen)) * 100;

                $finalScore = max($score, $levScore);

                if ($finalScore > $bestScore) {
                    $bestScore = $finalScore;
                    $bestMatch = $field;
                }
            }

            // Only suggest if confidence is above threshold
            $mappings[$csvHeader] = $bestScore >= 60 ? $bestMatch : null;
        }

        return $mappings;
    }

    /**
     * Validate all rows against the provided mappings.
     *
     * @return array{valid_count: int, error_count: int, errors: array, total_count: int}
     */
    public function validate(string $filePath, array $mappings): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        // Skip header row
        fgetcsv($handle);

        $headers = array_keys($mappings);
        $errors = [];
        $validCount = 0;
        $errorCount = 0;
        $rowNumber = 1; // 1-based, starting after header

        $validStatuses = array_keys(Item::STATUS_LABELS);
        $validConditions = array_keys(Item::CONDITION_LABELS);
        $validAcquisitionMethods = array_keys(Item::ACQUISITION_METHODS);

        // Pre-load existing categories and locations for validation
        $existingCategories = Category::pluck('id', 'name')->all();
        $existingLocations = Location::pluck('id', 'name')->all();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $rowData = array_combine(
                array_pad($headers, count($row), ''),
                array_pad($row, count($headers), '')
            );
            $rowErrors = $this->validateRow($rowData, $mappings, $rowNumber, $validStatuses, $validConditions, $validAcquisitionMethods, $existingCategories, $existingLocations);

            if (!empty($rowErrors)) {
                $errorCount++;
                $errors = array_merge($errors, $rowErrors);
            } else {
                $validCount++;
            }
        }

        fclose($handle);

        return [
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'errors' => $errors,
            'total_count' => $validCount + $errorCount,
        ];
    }

    /**
     * Import items from a CSV file using the provided mappings.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function execute(string $filePath, array $mappings, int $userId): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        // Skip header row
        fgetcsv($handle);

        $headers = array_keys($mappings);
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        $validStatuses = array_keys(Item::STATUS_LABELS);
        $validConditions = array_keys(Item::CONDITION_LABELS);
        $validAcquisitionMethods = array_keys(Item::ACQUISITION_METHODS);

        // Caches for categories, locations, and tags to reduce DB queries
        $categoryCache = Category::pluck('id', 'name')->all();
        $locationCache = Location::pluck('id', 'name')->all();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $rowData = array_combine(
                array_pad($headers, count($row), ''),
                array_pad($row, count($headers), '')
            );

            $rowErrors = $this->validateRow(
                $rowData,
                $mappings,
                $rowNumber,
                $validStatuses,
                $validConditions,
                $validAcquisitionMethods,
                $categoryCache,
                $locationCache,
                validateExistence: false, // Don't require existing categories/locations during import
            );

            if (!empty($rowErrors)) {
                $skipped++;
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            try {
                DB::beginTransaction();

                $itemData = $this->buildItemData($rowData, $mappings);
                $tags = $itemData['_tags'] ?? null;
                unset($itemData['_tags']);

                // Resolve category_id by name (create if doesn't exist)
                if (isset($itemData['_category_name'])) {
                    $categoryName = trim($itemData['_category_name']);
                    unset($itemData['_category_name']);

                    if ($categoryName !== '') {
                        if (!isset($categoryCache[$categoryName])) {
                            $category = Category::create(['name' => $categoryName]);
                            $categoryCache[$categoryName] = $category->id;
                        }
                        $itemData['category_id'] = $categoryCache[$categoryName];
                    }
                }

                // Resolve location_id by name (create if doesn't exist)
                if (isset($itemData['_location_name'])) {
                    $locationName = trim($itemData['_location_name']);
                    unset($itemData['_location_name']);

                    if ($locationName !== '') {
                        if (!isset($locationCache[$locationName])) {
                            $location = Location::create(['name' => $locationName]);
                            $locationCache[$locationName] = $location->id;
                        }
                        $itemData['location_id'] = $locationCache[$locationName];
                    }
                }

                $itemData['created_by'] = $userId;

                $item = Item::create($itemData);

                // Handle tags as comma-separated string
                if (!empty($tags)) {
                    $tagNames = array_map('trim', explode(',', $tags));
                    $tagIds = [];

                    foreach ($tagNames as $tagName) {
                        if ($tagName === '') {
                            continue;
                        }
                        $tag = Tag::findOrCreateByName($tagName);
                        $tagIds[] = $tag->id;
                    }

                    if (!empty($tagIds)) {
                        $item->tags()->attach($tagIds);
                    }
                }

                DB::commit();
                $imported++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $skipped++;
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => '',
                    'message' => "Unexpected error: {$e->getMessage()}",
                ];
                Log::error("Import error on row {$rowNumber}", [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Validate a single row of CSV data.
     *
     * @return array<int, array{row: int, field: string, message: string}>
     */
    private function validateRow(
        array $rowData,
        array $mappings,
        int $rowNumber,
        array $validStatuses,
        array $validConditions,
        array $validAcquisitionMethods,
        array $existingCategories,
        array $existingLocations,
        bool $validateExistence = true,
    ): array {
        $errors = [];
        $mapped = [];

        // Build mapped values: item_field => csv_value
        foreach ($mappings as $csvHeader => $itemField) {
            if ($itemField === null || $itemField === '') {
                continue;
            }
            $value = trim($rowData[$csvHeader] ?? '');
            $mapped[$itemField] = $value;
        }

        // name is required
        if (empty($mapped['name'] ?? '')) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'name',
                'message' => 'Name is required.',
            ];
        }

        // category must be a valid category name
        if (!empty($mapped['category'] ?? '') && $validateExistence) {
            if (!isset($existingCategories[$mapped['category']])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'category',
                    'message' => "Category \"{$mapped['category']}\" does not exist.",
                ];
            }
        }

        // location must be a valid location name
        if (!empty($mapped['location'] ?? '') && $validateExistence) {
            if (!isset($existingLocations[$mapped['location']])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'location',
                    'message' => "Location \"{$mapped['location']}\" does not exist.",
                ];
            }
        }

        // status must be a valid status key
        if (!empty($mapped['status'] ?? '')) {
            if (!in_array($mapped['status'], $validStatuses, true)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'status',
                    'message' => "Invalid status \"{$mapped['status']}\". Valid values: " . implode(', ', $validStatuses) . '.',
                ];
            }
        }

        // condition_rating must be valid
        if (!empty($mapped['condition_rating'] ?? '')) {
            if (!in_array($mapped['condition_rating'], $validConditions, true)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'condition_rating',
                    'message' => "Invalid condition \"{$mapped['condition_rating']}\". Valid values: " . implode(', ', $validConditions) . '.',
                ];
            }
        }

        // acquisition_method must be valid
        if (!empty($mapped['acquisition_method'] ?? '')) {
            if (!in_array($mapped['acquisition_method'], $validAcquisitionMethods, true)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'acquisition_method',
                    'message' => "Invalid acquisition method \"{$mapped['acquisition_method']}\". Valid values: " . implode(', ', $validAcquisitionMethods) . '.',
                ];
            }
        }

        // purchase_price must be numeric
        if (!empty($mapped['purchase_price'] ?? '')) {
            if (!is_numeric($mapped['purchase_price'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'purchase_price',
                    'message' => "Purchase price \"{$mapped['purchase_price']}\" must be numeric.",
                ];
            }
        }

        // estimated_value must be numeric
        if (!empty($mapped['estimated_value'] ?? '')) {
            if (!is_numeric($mapped['estimated_value'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'estimated_value',
                    'message' => "Estimated value \"{$mapped['estimated_value']}\" must be numeric.",
                ];
            }
        }

        // quantity must be integer
        if (!empty($mapped['quantity'] ?? '')) {
            if (!ctype_digit($mapped['quantity']) && !preg_match('/^-?\d+$/', $mapped['quantity'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'quantity',
                    'message' => "Quantity \"{$mapped['quantity']}\" must be an integer.",
                ];
            }
        }

        // Dates should be parseable
        foreach (['acquisition_date', 'valuation_date'] as $dateField) {
            if (!empty($mapped[$dateField] ?? '')) {
                try {
                    Carbon::parse($mapped[$dateField]);
                } catch (\Throwable) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'field' => $dateField,
                        'message' => "Date \"{$mapped[$dateField]}\" could not be parsed.",
                    ];
                }
            }
        }

        // year_manufactured should be a valid year
        if (!empty($mapped['year_manufactured'] ?? '')) {
            if (!preg_match('/^\d{4}$/', $mapped['year_manufactured'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'year_manufactured',
                    'message' => "Year manufactured \"{$mapped['year_manufactured']}\" must be a 4-digit year.",
                ];
            }
        }

        return $errors;
    }

    /**
     * Build an array of item attributes from a CSV row using the provided mappings.
     */
    private function buildItemData(array $rowData, array $mappings): array
    {
        $itemData = [];

        foreach ($mappings as $csvHeader => $itemField) {
            if ($itemField === null || $itemField === '') {
                continue;
            }

            $value = trim($rowData[$csvHeader] ?? '');

            if ($value === '') {
                continue;
            }

            match ($itemField) {
                'category' => $itemData['_category_name'] = $value,
                'location' => $itemData['_location_name'] = $value,
                'tags' => $itemData['_tags'] = $value,
                'acquisition_date', 'valuation_date' => $itemData[$itemField] = Carbon::parse($value)->toDateString(),
                'purchase_price', 'estimated_value' => $itemData[$itemField] = (float) $value,
                'quantity' => $itemData[$itemField] = (int) $value,
                'year_manufactured' => $itemData[$itemField] = (int) $value,
                default => $itemData[$itemField] = $value,
            };
        }

        return $itemData;
    }

    /**
     * Normalize a string for fuzzy comparison (lowercase, remove underscores/hyphens/spaces).
     */
    private function normalizeString(string $value): string
    {
        return strtolower(preg_replace('/[\s_\-]+/', '', $value));
    }
}
