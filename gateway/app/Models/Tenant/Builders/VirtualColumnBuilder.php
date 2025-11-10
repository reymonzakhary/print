<?php

declare(strict_types=1);

namespace App\Models\Tenant\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class VirtualColumnBuilder extends Builder
{
    /**
     * Update records in the database with virtual column support
     */
    public function update(array $values)
    {
        if (empty($values)) {
            return parent::update($values);
        }

        $model = $this->getModel();

        // Check if the model uses VirtualColumn trait
        if (!method_exists($model, 'getDataColumn') || !method_exists($model, 'getCustomColumns')) {
            return parent::update($values);
        }

        $dataColumn = $model->getDataColumn();
        $customColumns = $model->getCustomColumns();

        // Separate virtual columns from regular columns
        $virtualColumns = [];
        $regularColumns = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $customColumns)) {
                $regularColumns[$key] = $value;
            } else {
                $virtualColumns[$key] = $value;
            }
        }

        // If no virtual columns, use regular update
        if (empty($virtualColumns)) {
            return parent::update($regularColumns);
        }

        // Handle virtual columns by updating JSON data
        $affectedRows = 0;

        try {
            // Get all matching records to update them individually
            $records = $this->get(['id', $dataColumn]);

            foreach ($records as $record) {
                $currentData = $record->getAttribute($dataColumn) ?? [];

                // Ensure currentData is an array
                if (!is_array($currentData)) {
                    $currentData = [];
                }

                // Merge virtual columns with existing data
                $updatedData = array_merge($currentData, $virtualColumns);

                // Prepare update values
                $updateValues = array_merge($regularColumns, [
                    $dataColumn => json_encode($updatedData)
                ]);

                // Update the specific record
                $updated = $this->getQuery()
                    ->where('id', $record->id)
                    ->update($updateValues);

                if ($updated) {
                    $affectedRows++;
                }
            }
        } catch (\Exception $e) {
            // Fallback to regular update
            return parent::update($values);
        }

        return $affectedRows;
    }

    /**
     * Increment a virtual column value
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        $model = $this->getModel();

        if (!method_exists($model, 'getCustomColumns') ||
            in_array($column, $model->getCustomColumns())) {
            return parent::increment($column, $amount, $extra);
        }

        // Handle virtual column increment
        $dataColumn = $model->getDataColumn();

        return $this->update([
            $column => new Expression("CAST(JSON_UNQUOTE(JSON_EXTRACT({$dataColumn}, '$.{$column}')) AS UNSIGNED) + {$amount}"),
            ...$extra
        ]);
    }

    /**
     * Decrement a virtual column value
     */
    public function decrement($column, $amount = 1, array $extra = []): int
    {
        return $this->increment($column, -$amount, $extra);
    }

    /**
     * Add a where clause for virtual columns
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // Handle array conditions (e.g., where(['col1' => 'val1', 'col2' => 'val2']))
        if (is_array($column)) {
            return parent::where($column, $operator, $value, $boolean);
        }

        // Handle closure conditions
        if ($column instanceof \Closure) {
            return parent::where($column, $operator, $value, $boolean);
        }

        // Only process string column names for virtual column conversion
        if (is_string($column)) {
            $model = $this->getModel();

            // Check if it's a virtual column
            if (method_exists($model, 'getCustomColumns') &&
                !in_array($column, $model->getCustomColumns()) &&
                method_exists($model, 'getColumnForQuery')) {

                $column = $model->getColumnForQuery($column);
            }
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where" clause for virtual columns
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }
}

