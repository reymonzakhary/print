<?php

declare(strict_types=1);

namespace App\Plugins\Util\DWD;

use Illuminate\Validation\ValidationException;

final readonly class ResponseValidator
{
    /**
     * @param mixed $response
     *
     * @return void
     * @throws ValidationException
     */
    public function ensurePluginResponseIsValid(mixed $response): void
    {
        // Handle case where response is a string (JSON decode failed)
        if (is_string($response)) {
            throw ValidationException::withMessages([
                'error' => sprintf(
                    'Malformed response has been received from the plugin (Expected JSON array but received string: "%s")',
                    substr($response, 0, 100) . (strlen($response) > 100 ? '...' : '')
                )
            ]);
        }

        // Handle case where response is null
        if ($response === null) {
            throw ValidationException::withMessages([
                'response' => 'Malformed response has been received from the plugin (Expected array but received null)'
            ]);
        }

        // Handle case where response is an object (should be converted to array)
        if (is_object($response)) {
            $response = (array) $response;
        }

        // Handle case where response has error status (from RequestException)
        if (is_array($response) && array_key_exists('status', $response) && array_key_exists('message', $response)) {
            throw ValidationException::withMessages([
                'error' => sprintf(
                    'Plugin request failed with status %d: %s',
                    $response['status'],
                    $response['message']
                )
            ]);
        }

        // Handle case where response is a single item with price key
        if (is_array($response) && array_key_exists('price', $response)) {
            $response = [$response];
        }

        if (!is_array($response)) {
            throw ValidationException::withMessages([
                'response' => sprintf(
                    'Malformed response has been received from the plugin (Expect array but received "%s")',
                    gettype($response)
                )
            ]);
        }

        // Handle empty array
        if (empty($response)) {
            throw ValidationException::withMessages([
                'response' => 'Malformed response has been received from the plugin (Empty response array)'
            ]);
        }

        foreach ($response as $index => $item) {
            if (!is_array($item)) {
                throw ValidationException::withMessages([
                    'item' => sprintf(
                        'Malformed response item at index %d (Expected array, got "%s")',
                        $index,
                        gettype($item)
                    )
                ]);
            }

            if (!array_key_exists('price', $item)) {
                throw ValidationException::withMessages([
                    'price' => sprintf(
                        'Malformed response item at index %d (No `price` key found. Available keys: %s)',
                        $index,
                        implode(', ', array_keys($item))
                    )
                ]);
            }
        }
    }
}
