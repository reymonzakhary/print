<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * This trait lets you add a "data" column functionality to any Eloquent model.
 * It serializes attributes which don't exist as columns on the model's table
 * into a JSON column named data (customizable by overriding getDataColumn).
 *
 * @mixin Model
 */
trait VirtualColumn
{
    /**
     * Encrypted castables have to be handled using a special approach that prevents the data from getting encrypted repeatedly.
     */
    public static array $customEncryptedCastables = [];

    /**
     * We need this property, because both created & saved event listeners
     * decode the data but we don't want the data to be decoded twice.
     */
    public bool $dataEncoded = false;

    /**
     * Track which attributes have been changed to avoid overwriting unchanged nested data
     */
    protected array $virtualColumnChanges = [];

    /**
     * Store the original data column value for reference during encoding
     */
    protected ?array $originalDataColumnValue = null;

    /**
     * Fire a model event and decode if encoded
     *
     * @param string $event The event to fire
     * @param bool $halt Whether to halt event propagation
     * @return mixed The result of firing the event
     */
    protected function fireModelEvent($event, $halt = true): mixed
    {
        // Only decode if the model exists and has data
        if ($this->exists) {
            $this->decodeIfEncoded();
        }

        $result = parent::fireModelEvent($event, $halt);

        $this->runAfterListeners($event, $halt);

        return $result;
    }

    /**
     * Decode virtual column if it is encoded
     */
    protected function decodeIfEncoded(): void
    {
        if ($this->dataEncoded) {
            $this->decodeVirtualColumn();
        }
    }

    /**
     * Decode the virtual column if it is encoded
     */
    protected function decodeVirtualColumn(): void
    {
        if (!$this->dataEncoded) {
            return;
        }

        $dataColumn = static::getDataColumn();
        $dataValue = $this->getAttribute($dataColumn);

        // Handle cases where data column might be null or empty
        if (empty($dataValue) || !is_array($dataValue)) {
            $this->dataEncoded = false;
            return;
        }

        // Store the original data for later reference
        $this->originalDataColumnValue = $dataValue;

        $encryptedCastables = array_merge(
            static::$customEncryptedCastables,
            ['encrypted', 'encrypted:array', 'encrypted:collection', 'encrypted:encrypted:json', 'encrypted:object']
        );

        foreach ($dataValue as $key => $value) {
            $attributeHasEncryptedCastable = in_array(data_get($this->getCasts(), $key), $encryptedCastables);

            if ($value && $attributeHasEncryptedCastable && $this->valueEncrypted($value)) {
                $this->attributes[$key] = $value;
            } else {
                $this->setAttribute($key, $value);
            }

            // Set the original value so isDirty works correctly
            $this->syncOriginalAttribute($key);
        }

        // Only clear the data column if we're not creating a new model
        if ($this->exists) {
            unset($this->attributes[$dataColumn]);
        }

        $this->dataEncoded = false;

        // Clear any tracked changes since we just loaded from database
        $this->virtualColumnChanges = [];
    }

    /**
     * Encode the attributes into the data column if not already encoded
     */
    protected function encodeAttributes(): void
    {
        if ($this->dataEncoded) {
            return;
        }

        $dataColumn = static::getDataColumn();
        $customColumns = static::getCustomColumns();

        // Get existing data - use the stored original data column value
        $existingData = [];

        if ($this->exists && $this->originalDataColumnValue) {
            // Use the original data that was decoded when the model was loaded
            $existingData = $this->originalDataColumnValue;
        } elseif ($this->exists) {
            // Fallback: get current data column value
            $currentDataValue = $this->getOriginal($dataColumn) ?? $this->getAttribute($dataColumn);
            $existingData = is_array($currentDataValue) ? $currentDataValue : [];
        } else {
            // For new models, start with current data column value or empty array
            $currentDataValue = $this->getAttribute($dataColumn);
            $existingData = is_array($currentDataValue) ? $currentDataValue : [];
        }

        // Get attributes that should be moved to data column (current virtual attributes)
        $virtualAttributes = array_filter(
            $this->getAttributes(),
            fn($key) => !in_array($key, $customColumns) && $key !== $dataColumn,
            ARRAY_FILTER_USE_KEY
        );

        // Start with existing data and merge current virtual attributes
        $mergedData = $existingData;

        // Apply all current virtual attributes (this preserves unchanged ones and applies changed ones)
        foreach ($virtualAttributes as $key => $value) {
            $mergedData[$key] = $value;
        }

        // Only remove virtual attributes from model if we have some virtual data to store
        if (!empty($virtualAttributes)) {
            foreach ($virtualAttributes as $key => $value) {
                unset($this->attributes[$key]);
                // Only unset from original if the model exists
                if ($this->exists) {
                    unset($this->original[$key]);
                }
            }
        }

        // Set the merged data only if we have data to store
        if (!empty($mergedData)) {
            $this->setAttribute($dataColumn, $mergedData);
        }

        $this->dataEncoded = true;
    }

    /**
     * Set the attribute value and track virtual column changes if applicable
     *
     * @param string $key The key of the attribute
     * @param mixed $value The value to set the attribute to
     * @return mixed
     */
    public function setAttribute($key, $value): mixed
    {
        $dataColumn = static::getDataColumn();
        $customColumns = static::getCustomColumns();

        // Only track virtual column changes if we're not currently decoding
        // and it's actually a virtual column
        if (!$this->dataEncoded &&
            !in_array($key, $customColumns) &&
            $key !== $dataColumn) {

            // Get the current value to compare
            $currentValue = $this->getAttribute($key);

            // Only track as changed if the value is actually different
            if ($currentValue !== $value) {
                $this->virtualColumnChanges[$key] = $value;
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Determine if the model or given attributes have been modified
     *
     * @param array|string|null $attributes
     * @return bool
     */
    public function isDirty($attributes = null): bool
    {
        if (is_null($attributes)) {
            return parent::isDirty() || !empty($this->virtualColumnChanges);
        }

        $attributes = is_array($attributes) ? $attributes : func_get_args();

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $this->virtualColumnChanges)) {
                return true;
            }
        }

        return parent::isDirty($attributes);
    }

    /**
     * Determines if a given value is encrypted using Laravel's Crypt::decryptString.
     *
     * @param string $value The encrypted value to check
     * @return bool True if the value is encrypted, false otherwise
     */
    public function valueEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException) {
            return false;
        }
    }

    /**
     * Retrieves the after listeners for model events.
     *
     * @return array An array containing callbacks for various model events
     */
    protected function getAfterListeners(): array
    {
        return [
            'retrieved' => [
                function () {
                    $this->dataEncoded = true;
                    $this->decodeVirtualColumn();
                },
            ],
            'saving' => [
                [$this, 'encodeAttributes'],
            ],
            'creating' => [
                [$this, 'encodeAttributes'],
            ],
            'updating' => [
                [$this, 'encodeAttributes'],
            ],
            'saved' => [
                function () {
                    // Clear virtual column changes after successful save
                    $this->virtualColumnChanges = [];

                    // Decode again after save to restore virtual attributes
                    $this->dataEncoded = true;
                    $this->decodeVirtualColumn();
                },
            ],
            'created' => [
                function () {
                    // Clear virtual column changes after successful creation
                    $this->virtualColumnChanges = [];

                    // Decode again after creation to restore virtual attributes
                    $this->dataEncoded = true;
                    $this->decodeVirtualColumn();
                },
            ],
        ];
    }

    /**
     * Runs the after listeners for a given event.
     *
     * @param string $event The event for which after listeners should be executed
     * @param bool $halt Whether to stop executing listeners after the first one encountered an error
     * @return void
     */
    public function runAfterListeners(string $event, bool $halt = true): void
    {
        $listeners = $this->getAfterListeners()[$event] ?? [];

        if (!$event) {
            return;
        }

        foreach ($listeners as $listener) {
            if (is_string($listener)) {
                $listener = app($listener);
                $handle = [$listener, 'handle'];
            } else {
                $handle = $listener;
            }

            $handle($this);
        }
    }

    /**
     * Get the casts for the model.
     *
     * @return array
     */
    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), [
            static::getDataColumn() => 'array',
        ]);
    }

    /**
     * Get the name of the column that stores additional data.
     */
    public static function getDataColumn(): string
    {
        return 'data';
    }

    /**
     * Get the columns that should NOT be stored in the data column.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            static::getDataColumn(),
        ];
    }

    /**
     * Get a column name for an attribute that can be used in SQL queries.
     */
    /**
     * Get a column name for an attribute that can be used in SQL queries.
     */
    public function getColumnForQuery(string $column): string
    {
        // Handle qualified column names (e.g., "orders.id" -> "id")
        $columnName = $column;

        if (str_contains($column, '.')) {
            $parts = explode('.', $column);
            $columnName = end($parts); // Get just the column name part
        }

        // Check if the actual column name (not the qualified name) is a custom column
        if (in_array($columnName, static::getCustomColumns(), true)) {
            return $column; // Return original qualified name for regular columns
        }

        // For virtual columns, we need to use JSON path
        // Use the column name without table qualification for JSON path
        return static::getDataColumn() . '->' . $columnName;
    }

    /**
     * Boot the trait
     */
    public static function bootVirtualColumn(): void
    {
        // This will be called when the model is booted
    }
}
