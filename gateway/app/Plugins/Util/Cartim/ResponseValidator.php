<?php

declare(strict_types=1);

namespace App\Plugins\Util\Cartim;

use Illuminate\Validation\ValidationException;

final readonly class ResponseValidator
{
    private function isListArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

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

        // Handle wrapped Python response: { data, message, status }
        if (is_array($response) && array_key_exists('data', $response) && array_key_exists('status', $response)) {
            $status = (int) ($response['status'] ?? 0);
            if ($status !== 200) {
                throw ValidationException::withMessages([
                    'error' => sprintf(
                        'Plugin request failed with status %d: %s',
                        $status,
                        (string) ($response['message'] ?? 'Unknown error')
                    )
                ]);
            }

            $data = $response['data'];

            // Single-item payload with price
            if (is_array($data) && array_key_exists('price', $data)) {
                return;
            }

            // List payload: each item must be an array with price
            if (is_array($data) && $this->isListArray($data)) {
                if (empty($data)) {
                    throw ValidationException::withMessages([
                        'response' => 'Malformed response has been received from the plugin (Empty data array)'
                    ]);
                }
                foreach ($data as $index => $item) {
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
                return;
            }

            // At this point, data must be an array and contain price
            throw ValidationException::withMessages([
                'response' => 'Malformed response has been received from the plugin (Expected `data` to contain price or list of priced items)'
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
