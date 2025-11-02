<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenants\Media\FileManager;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Tenant File Cleanup Command
 *
 * Synchronizes files between S3 storage and database for multi-tenant applications.
 * Uses S3 as the source of truth and manages database records accordingly.
 *
 * ============================================================================
 * COMMAND SYNTAX
 * ============================================================================
 * php artisan tenants:clean [options]
 *
 * ============================================================================
 * AVAILABLE OPTIONS
 * ============================================================================
 * --dry-run              Preview changes without making modifications
 * --tenant=UUID          Process only a specific tenant
 * --sync-only            Only sync S3 files to DB (no deletions)
 * --delete-only          Only delete orphaned records (no sync)
 * --update-models        Update existing records with missing model_type/model_id
 * --fix-duplicates       Remove duplicate records (keeps oldest)
 * --debug                Show detailed debug information
 * --show-discrepancies   Show detailed file discrepancies
 * --id=ID                Process only specific file manager record
 * -v, --verbose          Show detailed output
 *
 * ============================================================================
 * WHAT THE COMMAND DOES
 * ============================================================================
 *
 * 1. Fix Duplicates (--fix-duplicates)
 *    - Finds records with same disk, name, and path
 *    - Keeps oldest record (by created_at)
 *    - Deletes newer duplicates
 *
 * 2. Update Model Info (--update-models)
 *    - Finds records where model_type or model_id is NULL
 *    - Only processes: quotations/, orders/, invoices/ paths
 *    - Extracts model information from path structure
 *
 * 3. Sync S3 → Database (default)
 *    - Scans all files in S3 storage
 *    - Creates DB records for missing files
 *    - Auto-extracts: model_type, model_id, collection
 *
 * 4. Clean Orphaned Records (default)
 *    - Scans all DB records
 *    - Deletes records where S3 file doesn't exist
 *
 * ============================================================================
 * PATH STRUCTURE & MODEL DETECTION
 * ============================================================================
 *
 * Pattern 1: Items in Quotations/Orders
 *   quotations/270/items/250/file.pdf → Item model, ID: 250
 *   orders/150/items/300/file.pdf     → Item model, ID: 300
 *   collection: items
 *
 * Pattern 2: Direct Attachments
 *   quotations/251/file.pdf → Quotation model, ID: 251
 *   orders/123/file.pdf     → Order model, ID: 123
 *   collection: attachments (default)
 *
 * Pattern 3: Named Collections
 *   quotations/251/signatures/file.pdf → Quotation, ID: 251
 *   collection: signatures
 *
 * ============================================================================
 * COMMON USE CASES
 * ============================================================================
 *
 * Initial Setup / Data Migration:
 *   php artisan tenants:clean --fix-duplicates --update-models --dry-run
 *   php artisan tenants:clean --fix-duplicates --update-models
 *
 * Regular Maintenance (cron job):
 *   php artisan tenants:clean
 *
 * Specific Tenant:
 *   php artisan tenants:clean --tenant=UUID --show-discrepancies
 *   php artisan tenants:clean --tenant=UUID
 *
 * Safe Operations:
 *   php artisan tenants:clean --sync-only      (no deletions)
 *   php artisan tenants:clean --delete-only    (no creation)
 *
 * Debugging:
 *   php artisan tenants:clean --tenant=UUID --debug -v
 *   php artisan tenants:clean --id=2142 -v
 *
 * ============================================================================
 * WORKFLOW RECOMMENDATIONS
 * ============================================================================
 *
 * 1. Initial Cleanup (One-time):
 *    a) php artisan tenants:clean --fix-duplicates --update-models --show-discrepancies --dry-run
 *    b) Review output carefully
 *    c) php artisan tenants:clean --fix-duplicates --update-models
 *    d) php artisan tenants:clean --show-discrepancies (verify)
 *
 * 2. Regular Maintenance (Cron - daily at 2 AM):
 *    0 2 * * * cd /var/www && php artisan tenants:clean >> /var/log/tenant-cleanup.log 2>&1
 *
 * 3. After Deployment:
 *    php artisan tenants:clean --sync-only
 *
 * ============================================================================
 * STORAGE DISKS
 * ============================================================================
 * tenancy - Main tenant files
 * assets  - Asset files
 * carts   - Shopping cart files
 *
 * ============================================================================
 * SAFETY FEATURES
 * ============================================================================
 * - Dry run mode: Always test with --dry-run first
 * - Duplicate detection: Keeps oldest record
 * - Path normalization: Handles trailing slashes automatically
 * - Chunking: Processes 100 records at a time
 * - Progress tracking: Visual feedback for multi-tenant operations
 *
 * ============================================================================
 * TROUBLESHOOTING
 * ============================================================================
 * Too many duplicates:        php artisan tenants:clean --fix-duplicates
 * Files in S3 not in DB:      php artisan tenants:clean --sync-only
 * Orphaned DB records:        php artisan tenants:clean --delete-only
 * Missing model info:         php artisan tenants:clean --update-models
 * Specific tenant issues:     php artisan tenants:clean --tenant=UUID --debug -v
 *
 * ============================================================================
 * BEST PRACTICES
 * ============================================================================
 * Always test with --dry-run first
 * Use --show-discrepancies to understand issues
 * Run --fix-duplicates before other operations
 * Schedule regular maintenance via cron
 * Test on single tenant before running on all
 * Back up database before major cleanup operations
 *
 * ============================================================================
 * OUTPUT SUMMARY
 * ============================================================================
 * After each run, displays:
 * - Files already synced (skipped)
 * - Duplicate records removed
 * - Existing records updated
 * - New DB records created from S3
 * - Orphaned DB records deleted
 * - Total changes made
 *
 * ============================================================================
 * PERFORMANCE
 * ============================================================================
 * - Processes in chunks of 100 records
 * - ~1-5 seconds per tenant (varies by file count)
 * - Memory efficient for large datasets
 *
 * @package App\Console\Commands\Tenant
 * @author Reymon Zakhary
 * @version 1.0.0
 */
class RunCleanupMissingFiles extends Command
{
    protected $signature = 'tenants:clean
                            {--dry-run : Run without making any changes}
                            {--tenant= : Clean specific tenant by UUID}
                            {--sync-only : Only sync S3 files to DB without deleting}
                            {--delete-only : Only delete orphaned DB records without syncing}
                            {--update-models : Update existing records with missing model_type/model_id}
                            {--fix-duplicates : Remove duplicate records keeping the oldest}
                            {--debug : Show detailed debug information}
                            {--show-discrepancies : Show detailed file discrepancies}
                            {--id= : Process only specific file manager ID}';

    protected $description = 'Sync S3 files to database and clean up orphaned database records';

    private const CHUNK_SIZE = 100;
    private const STORAGE_DISKS = ['tenancy', 'assets', 'carts'];

    private int $dbDeletionCount = 0;
    private int $dbCreationCount = 0;
    private int $dbSkippedCount = 0;
    private int $dbUpdatedCount = 0;
    private int $dbDuplicatesRemovedCount = 0;
    private array $debugInfo = [];

    public function handle(): int
    {
        $this->info('Starting tenant file synchronization process...');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Running in DRY RUN mode - no changes will be made');
        }

        try {
            $tenants = $this->getTenants();

            if ($tenants->isEmpty()) {
                $this->warn('No tenants found to process');
                return Command::SUCCESS;
            }

            $this->info("Processing {$tenants->count()} tenant(s)...");

            $progressBar = $this->output->createProgressBar($tenants->count());
            $progressBar->start();

            $tenants->each(function ($website) use ($isDryRun, $progressBar) {
                $this->processTenant($website['uuid'], $isDryRun);
                $progressBar->advance();
            });

            $progressBar->finish();
            $this->newLine(2);

            $this->displaySummary();

            if ($this->option('debug')) {
                $this->displayDebugInfo();
            }

            if ($this->option('show-discrepancies')) {
                $this->displayDiscrepancies();
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Synchronization failed: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function getTenants(): Collection
    {
        $tenantUuid = $this->option('tenant');

        if ($tenantUuid) {
            $tenant = Website::where('uuid', $tenantUuid)->first(['uuid']);
            return $tenant ? collect([$tenant->toArray()]) : collect();
        }

        return collect(Website::all(['uuid'])->toArray());
    }

    private function processTenant(string $tenantUuid, bool $isDryRun): void
    {
        try {
            switchSupplier($tenantUuid);
            $directory = DB::connection('tenant')->getDatabaseName();

            if ($this->option('debug') || $this->option('show-discrepancies')) {
                $this->debugInfo[$tenantUuid] = [
                    'directory' => $directory,
                    's3_files' => [],
                    's3_file_list' => [],
                    'db_files' => 0,
                    'db_file_list' => [],
                    'created' => [],
                    'updated' => [],
                    'deleted' => [],
                    'duplicates_removed' => [],
                    'orphaned_db' => [],
                    'missing_in_db' => [],
                ];
            }

            $syncOnly = $this->option('sync-only');
            $deleteOnly = $this->option('delete-only');
            $updateModels = $this->option('update-models');
            $fixDuplicates = $this->option('fix-duplicates');

            // Step 0a: Fix duplicate records
            if ($fixDuplicates) {
                $this->fixDuplicateRecords($tenantUuid, $isDryRun);
            }

            // Step 0b: Update existing records with missing model info
            if ($updateModels) {
                $this->updateExistingRecordsWithModelInfo($tenantUuid, $isDryRun);
            }

            // Step 1: Sync S3 files to database (unless delete-only mode)
            if (!$deleteOnly) {
                $this->syncStorageFilesToDatabase($tenantUuid, $directory, $isDryRun);
            }

            // Step 2: Clean orphaned database records (unless sync-only mode)
            if (!$syncOnly) {
                $this->cleanOrphanedDatabaseRecords($tenantUuid, $directory, $isDryRun);
            }
        } catch (Throwable $e) {
            $this->error("Error processing tenant {$tenantUuid}: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
        }
    }

    private function fixDuplicateRecords(string $tenantUuid, bool $isDryRun): void
    {
        $this->info("Finding and removing duplicate records...");

        // Get all records grouped by disk, name, and normalized path
        $allRecords = FileManager::all();
        $grouped = $allRecords->groupBy(function ($record) {
            return $record->disk . '|' . $record->name . '|' . trim($record->path, '/');
        });

        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                // We have duplicates - keep the oldest, delete the rest
                $oldest = $group->sortBy('created_at')->first();
                $duplicates = $group->where('id', '!=', $oldest->id);

                foreach ($duplicates as $duplicate) {
                    if ($this->option('verbose')) {
                        $this->warn("Removing duplicate: ID:{$duplicate->id} {$duplicate->name}");
                        $this->info("  Keeping: ID:{$oldest->id} (created: {$oldest->created_at})");
                        $this->info("  Removing: ID:{$duplicate->id} (created: {$duplicate->created_at})");
                    }

                    if (!$isDryRun) {
                        $duplicate->delete();
                    }

                    if ($this->option('debug') || $this->option('show-discrepancies')) {
                        $this->debugInfo[$tenantUuid]['duplicates_removed'][] = [
                            'removed_id' => $duplicate->id,
                            'kept_id' => $oldest->id,
                            'name' => $duplicate->name,
                            'path' => $duplicate->path,
                        ];
                    }

                    $this->dbDuplicatesRemovedCount++;
                }
            }
        }

        if ($this->option('verbose')) {
            $this->info("Removed {$this->dbDuplicatesRemovedCount} duplicate records");
        }
    }

    private function updateExistingRecordsWithModelInfo(string $tenantUuid, bool $isDryRun): void
    {
        $this->info("Updating existing records with missing model information...");

        // Get records where model_type or model_id is null AND path matches our patterns
        $recordsToUpdate = FileManager::query()
            ->where(function ($query) {
                $query->whereNull('model_type')
                    ->orWhereNull('model_id');
            })
            ->where(function ($query) {
                // Only paths that start with quotations/ or orders/
                $query->where('path', 'like', 'quotations/%')
                    ->orWhere('path', 'like', 'orders/%')
                    ->orWhere('path', 'like', 'invoices/%');
            })
            ->get();

        if ($this->option('verbose')) {
            $this->info("Found {$recordsToUpdate->count()} records to update");
        }

        foreach ($recordsToUpdate as $record) {
            $path = trim($record->path, '/');
            $modelInfo = $this->extractModelInfoFromPath($path);

            // Only update if we successfully extracted model info
            if ($modelInfo['model_type'] && $modelInfo['model_id']) {
                if ($this->option('verbose')) {
                    $this->info("Updating record ID:{$record->id} - {$record->name}");
                    $this->info("  Old: model_type={$record->model_type}, model_id={$record->model_id}, collection={$record->collection}");
                    $this->info("  New: model_type={$modelInfo['model_type']}, model_id={$modelInfo['model_id']}, collection={$modelInfo['collection']}");
                }

                if (!$isDryRun) {
                    $record->update([
                        'model_type' => $modelInfo['model_type'],
                        'model_id' => $modelInfo['model_id'],
                        'collection' => $modelInfo['collection'],
                        'user_id' => $modelInfo['user_id'] ?? $record->user_id,
                    ]);
                }

                if ($this->option('debug') || $this->option('show-discrepancies')) {
                    $this->debugInfo[$tenantUuid]['updated'][] = [
                        'id' => $record->id,
                        'name' => $record->name,
                        'path' => $record->path,
                        'old_model_type' => $record->model_type,
                        'new_model_type' => $modelInfo['model_type'],
                        'old_model_id' => $record->model_id,
                        'new_model_id' => $modelInfo['model_id'],
                        'new_collection' => $modelInfo['collection'],
                    ];
                }

                $this->dbUpdatedCount++;
            }
        }

        if ($this->option('verbose')) {
            $this->info("Updated {$this->dbUpdatedCount} records");
        }
    }

    private function syncStorageFilesToDatabase(string $tenantUuid, string $directory, bool $isDryRun): void
    {
        foreach (self::STORAGE_DISKS as $disk) {
            if (!Storage::disk($disk)->exists($directory)) {
                if ($this->option('verbose')) {
                    $this->info("Skipping disk '{$disk}' - directory not found: {$directory}");
                }
                continue;
            }

            $s3Files = Storage::disk($disk)->allFiles($directory);

            if ($this->option('debug') || $this->option('show-discrepancies')) {
                $this->debugInfo[$tenantUuid]['s3_files'][$disk] = count($s3Files);
            }

            foreach ($s3Files as $s3FilePath) {
                $normalizedPath = $this->normalizeFilePath($s3FilePath);

                if ($this->option('show-discrepancies')) {
                    $pathInfo = pathinfo($normalizedPath);
                    $fileName = $pathInfo['basename'];
                    $relativePath = $this->extractRelativePath($pathInfo['dirname'], $directory);

                    $this->debugInfo[$tenantUuid]['s3_file_list'][] = [
                        'disk' => $disk,
                        'name' => $fileName,
                        'path' => $relativePath,
                        'full_path' => $normalizedPath,
                    ];
                }

                if (!$this->fileExistsInDatabase($disk, $normalizedPath, $directory)) {
                    if ($this->option('show-discrepancies')) {
                        $pathInfo = pathinfo($normalizedPath);
                        $fileName = $pathInfo['basename'];
                        $relativePath = $this->extractRelativePath($pathInfo['dirname'], $directory);

                        $this->debugInfo[$tenantUuid]['missing_in_db'][] = [
                            'disk' => $disk,
                            'name' => $fileName,
                            'path' => $relativePath,
                            'full_path' => $normalizedPath,
                        ];
                    }
                    $this->createDatabaseRecord($tenantUuid, $disk, $directory, $normalizedPath, $isDryRun);
                } else {
                    $this->dbSkippedCount++;
                }
            }
        }
    }

    private function normalizeFilePath(string $path): string
    {
        return preg_replace('#/+#', '/', trim($path));
    }

    private function createDatabaseRecord(string $tenantUuid, string $disk, string $directory, string $filePath, bool $isDryRun): void
    {
        $pathInfo = pathinfo($filePath);
        $fileName = $pathInfo['basename'];
        $extension = $pathInfo['extension'] ?? '';

        $dirName = $pathInfo['dirname'];
        $relativePath = $this->extractRelativePath($dirName, $directory);

        $cleanFileName = preg_replace('/\x{AD}/u', '', $fileName);

        // Extract model information from path
        $modelInfo = $this->extractModelInfoFromPath($relativePath);

        if ($this->option('verbose') || $this->option('debug')) {
            $this->info("Creating: {$cleanFileName}");
            $this->info("  Path: {$relativePath}");
            $this->info("  Model Type: " . ($modelInfo['model_type'] ?? 'null'));
            $this->info("  Model ID: " . ($modelInfo['model_id'] ?? 'null'));
            $this->info("  Collection: " . ($modelInfo['collection'] ?? 'null'));
        }

        if (!$isDryRun) {
            try {
                $fullPath = $filePath;
                $fileSize = Storage::disk($disk)->size($fullPath);
                $mimeType = Storage::disk($disk)->mimeType($fullPath);

                $group = $this->getGroupType($mimeType);

                $created = FileManager::create([
                    'user_id' => $modelInfo['user_id'] ?? null,
                    'name' => $cleanFileName,
                    'disk' => $disk,
                    'group' => $group,
                    'path' => $relativePath . '/',
                    'ext' => $extension,
                    'type' => $mimeType,
                    'size' => $fileSize,
                    'model_type' => $modelInfo['model_type'] ?? null,
                    'model_id' => $modelInfo['model_id'] ?? null,
                    'collection' => $modelInfo['collection'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->dbCreationCount++;

                if ($this->option('debug')) {
                    $this->debugInfo[$tenantUuid]['created'][] = [
                        'id' => $created->id,
                        'name' => $cleanFileName,
                        'path' => $relativePath,
                        'disk' => $disk,
                        'model_type' => $modelInfo['model_type'] ?? null,
                        'model_id' => $modelInfo['model_id'] ?? null,
                        'collection' => $modelInfo['collection'] ?? null,
                    ];
                }

                if ($this->option('verbose')) {
                    $this->info("✓ Created ID:{$created->id} - {$cleanFileName}");
                }
            } catch (Throwable $e) {
                $this->error("Failed to create DB record for {$filePath}: {$e->getMessage()}");
            }
        } else {
            $this->dbCreationCount++;
        }
    }

    private function extractModelInfoFromPath(string $path): array
    {
        $info = [
            'model_type' => null,
            'model_id' => null,
            'collection' => null,
            'user_id' => null,
        ];

        $parts = explode('/', trim($path, '/'));

        if (empty($parts) || empty($parts[0])) {
            return $info;
        }

        $mainEntity = $parts[0];

        // Check for nested items pattern: quotations/270/items/250 or orders/150/items/300
        if (count($parts) >= 4 && $parts[2] === 'items' && is_numeric($parts[1]) && is_numeric($parts[3])) {
            $info['model_type'] = 'App\\Models\\Tenants\\Item';
            $info['model_id'] = (int) $parts[3];
            $info['collection'] = 'items';
            $info['user_id'] = (int) $parts[1];

            return $info;
        }

        // Pattern: quotations/251 or orders/123 or quotations/251/signatures
        if (count($parts) >= 2 && is_numeric($parts[1])) {
            $entityMap = [
                'quotations' => 'App\\Models\\Tenants\\Quotation',
                'orders' => 'App\\Models\\Tenants\\Order',
                'invoices' => 'App\\Models\\Tenants\\Invoice',
                'products' => 'App\\Models\\Tenants\\Product',
                'users' => 'App\\Models\\Tenants\\User',
                'customers' => 'App\\Models\\Tenants\\Customer',
            ];

            if (isset($entityMap[$mainEntity])) {
                $info['model_type'] = $entityMap[$mainEntity];
                $info['model_id'] = (int) $parts[1];

                if (count($parts) >= 3 && !is_numeric($parts[2])) {
                    $info['collection'] = $parts[2];
                } else {
                    $info['collection'] = 'attachments';
                }
            }

            return $info;
        }

        return $info;
    }

    private function extractRelativePath(string $dirname, string $directory): string
    {
        $relativePath = str_replace($directory . '/', '', $dirname);
        $relativePath = str_replace($directory, '', $relativePath);

        return trim($relativePath, '/');
    }

    private function getGroupType(string $mimeType): string
    {
        return match(true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            in_array($mimeType, ['application/pdf']) => 'document',
            in_array($mimeType, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
            ]) => 'document',
            in_array($mimeType, ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed']) => 'archive',
            default => 'other',
        };
    }

    private function cleanOrphanedDatabaseRecords(string $tenantUuid, string $directory, bool $isDryRun): void
    {
        if ($this->option('id')) {
            $allFiles = FileManager::query()->where('id', $this->option('id'))->get();
        } else {
            $allFiles = FileManager::query()->get();
        }

        if ($this->option('debug') || $this->option('show-discrepancies')) {
            $this->debugInfo[$tenantUuid]['db_files'] = $allFiles->count();
        }

        foreach ($allFiles as $file) {
            if ($this->option('show-discrepancies')) {
                $this->debugInfo[$tenantUuid]['db_file_list'][] = [
                    'id' => $file->id,
                    'disk' => $file->disk,
                    'name' => $file->name,
                    'path' => $file->path,
                ];
            }

            $fullPath = $this->buildStoragePath($directory, $file);
            $normalizedPath = $this->normalizeFilePath($fullPath);

            $exists = $this->fileExistsInStorage($file->disk, $normalizedPath);

            if (!$exists) {
                if ($this->option('show-discrepancies')) {
                    $this->debugInfo[$tenantUuid]['orphaned_db'][] = [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'disk' => $file->disk,
                        'expected_s3_path' => $normalizedPath,
                    ];
                }

                if ($this->option('verbose')) {
                    $this->warn("Deleting orphaned: {$file->name} (ID: {$file->id})");
                    $this->warn("  Expected S3 path: {$normalizedPath}");
                    $this->warn("  DB disk: {$file->disk}");
                }

                if (!$isDryRun) {
                    $file->delete();
                }

                if ($this->option('debug')) {
                    $this->debugInfo[$tenantUuid]['deleted'][] = [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'disk' => $file->disk,
                        'expected_s3_path' => $normalizedPath,
                    ];
                }

                $this->dbDeletionCount++;
            }
        }
    }

    private function buildStoragePath(string $directory, FileManager $file): string
    {
        $path = trim($file->path, '/');

        if (empty($path)) {
            return "{$directory}/{$file->name}";
        }

        return "{$directory}/{$path}/{$file->name}";
    }

    private function fileExistsInStorage(string $disk, string $fullPath): bool
    {
        try {
            return Storage::disk($disk)->exists($fullPath);
        } catch (Throwable $e) {
            if ($this->option('verbose')) {
                $this->error("Error checking storage for {$fullPath}: {$e->getMessage()}");
            }
            return false;
        }
    }

    private function fileExistsInDatabase(string $disk, string $filePath, string $directory): bool
    {
        $pathInfo = pathinfo($filePath);
        $fileName = $pathInfo['basename'];

        $cleanFileName = preg_replace('/\x{AD}/u', '', $fileName);

        $relativePath = $this->extractRelativePath($pathInfo['dirname'], $directory);

        // Check for both path with and without trailing slash
        return FileManager::query()
            ->where('disk', $disk)
            ->where('name', $cleanFileName)
            ->where(function ($query) use ($relativePath) {
                $query->where('path', $relativePath)
                    ->orWhere('path', $relativePath . '/')
                    ->orWhere('path', '/' . $relativePath)
                    ->orWhere('path', '/' . $relativePath . '/');
            })
            ->exists();
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('              SYNCHRONIZATION SUMMARY');
        $this->info('═══════════════════════════════════════════════════════');

        $this->table(
            ['Operation', 'Count'],
            [
                ['Files already synced (skipped)', $this->dbSkippedCount],
                ['Duplicate records removed', $this->dbDuplicatesRemovedCount],
                ['Existing records updated', $this->dbUpdatedCount],
                ['New DB records created from S3', $this->dbCreationCount],
                ['Orphaned DB records deleted', $this->dbDeletionCount],
                ['Total changes', $this->dbDuplicatesRemovedCount + $this->dbUpdatedCount + $this->dbCreationCount + $this->dbDeletionCount],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('⚠ DRY RUN MODE - No actual changes were made');
            $this->info('Run without --dry-run to apply these changes');
        } else {
            if ($this->dbDuplicatesRemovedCount > 0) {
                $this->info("✓ Removed {$this->dbDuplicatesRemovedCount} duplicate record(s)");
            }

            if ($this->dbUpdatedCount > 0) {
                $this->info("✓ Updated {$this->dbUpdatedCount} existing record(s) with model information");
            }

            if ($this->dbCreationCount > 0) {
                $this->info("✓ Synced {$this->dbCreationCount} file(s) from S3 to database");
            }

            if ($this->dbDeletionCount > 0) {
                $this->warn("✓ Removed {$this->dbDeletionCount} orphaned database record(s)");
            }

            if ($this->dbDuplicatesRemovedCount === 0 && $this->dbUpdatedCount === 0 && $this->dbCreationCount === 0 && $this->dbDeletionCount === 0) {
                $this->info('✓ Everything is in sync - no changes needed');
            }
        }

        $this->info('═══════════════════════════════════════════════════════');
    }

    private function displayDebugInfo(): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('                  DEBUG INFORMATION');
        $this->info('═══════════════════════════════════════════════════════');

        foreach ($this->debugInfo as $tenantUuid => $info) {
            $this->info("Tenant: {$tenantUuid}");
            $this->info("  Directory: {$info['directory']}");
            $this->info("  S3 Files: " . json_encode($info['s3_files']));
            $this->info("  DB Files: " . ($info['db_files'] ?? 0));
            $this->info("  Duplicates Removed: " . count($info['duplicates_removed']));
            $this->info("  Updated: " . count($info['updated']));
            $this->info("  Created: " . count($info['created']));
            $this->info("  Deleted: " . count($info['deleted']));

            if (!empty($info['duplicates_removed'])) {
                $this->info("  Duplicate records removed:");
                foreach (array_slice($info['duplicates_removed'], 0, 10) as $record) {
                    $this->line("    - Removed ID:{$record['removed_id']}, Kept ID:{$record['kept_id']} - {$record['name']}");
                }
            }

            if (!empty($info['updated'])) {
                $this->info("  Updated records:");
                foreach (array_slice($info['updated'], 0, 10) as $record) {
                    $this->line("    - ID:{$record['id']} {$record['name']}");
                    $this->line("      Path: {$record['path']}");
                    $this->line("      Old: {$record['old_model_type']} (ID: {$record['old_model_id']})");
                    $this->line("      New: {$record['new_model_type']} (ID: {$record['new_model_id']}, Collection: {$record['new_collection']})");
                }
            }

            if (!empty($info['created'])) {
                $this->info("  Created records:");
                foreach (array_slice($info['created'], 0, 10) as $record) {
                    $this->line("    - ID:{$record['id']} {$record['name']}");
                    $this->line("      Path: {$record['path']}");
                    $this->line("      Model: {$record['model_type']} (ID: {$record['model_id']}, Collection: {$record['collection']})");
                }
            }

            if (!empty($info['deleted'])) {
                $this->warn("  Deleted records:");
                foreach (array_slice($info['deleted'], 0, 5) as $record) {
                    $this->line("    - ID:{$record['id']} {$record['name']} (expected: {$record['expected_s3_path']})");
                }
            }
        }
    }

    private function displayDiscrepancies(): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('              DISCREPANCY DETAILS');
        $this->info('═══════════════════════════════════════════════════════');

        foreach ($this->debugInfo as $tenantUuid => $info) {
            $totalS3 = array_sum($info['s3_files'] ?? []);
            $totalDB = $info['db_files'] ?? 0;
            $difference = $totalDB - $totalS3;

            $this->warn("Tenant: {$tenantUuid}");
            $this->info("  S3 Total: {$totalS3}");
            $this->info("  DB Total: {$totalDB}");
            $this->info("  Difference: " . ($difference > 0 ? "+{$difference}" : $difference));

            if (!empty($info['missing_in_db'])) {
                $this->newLine();
                $this->error("  ❌ Files in S3 but NOT in DB: " . count($info['missing_in_db']));
                foreach (array_slice($info['missing_in_db'], 0, 20) as $file) {
                    $this->line("    - [{$file['disk']}] {$file['name']} (path: {$file['path']})");
                }
                if (count($info['missing_in_db']) > 20) {
                    $this->line("    ... and " . (count($info['missing_in_db']) - 20) . " more");
                }
            }

            if (!empty($info['orphaned_db'])) {
                $this->newLine();
                $this->error("  ❌ Files in DB but NOT in S3: " . count($info['orphaned_db']));
                foreach (array_slice($info['orphaned_db'], 0, 20) as $file) {
                    $this->line("    - ID:{$file['id']} [{$file['disk']}] {$file['name']}");
                    $this->line("      DB path: {$file['path']}");
                    $this->line("      Expected S3: {$file['expected_s3_path']}");
                }
                if (count($info['orphaned_db']) > 20) {
                    $this->line("    ... and " . (count($info['orphaned_db']) - 20) . " more");
                }
            }

            if (empty($info['missing_in_db']) && empty($info['orphaned_db'])) {
                $this->newLine();
                $this->info("  ✓ All files are in sync!");
            }

            $this->newLine();
        }
    }
}
