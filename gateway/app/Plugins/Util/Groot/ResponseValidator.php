<?php

declare(strict_types=1);

namespace App\Plugins\Util\Groot;

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
        if (!is_array($response) || !array_key_exists('data', $response) || !array_key_exists('status', $response)) {
            throw ValidationException::withMessages([
                'response' => 'Malformed response has been received from the plugin (Expected `data` and `status` keys)'
            ]);
        }

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

        // Validate staffel exists and has data
        if (array_key_exists('staffel', $data) && empty($data['staffel'])) {
            throw ValidationException::withMessages([
                'data.staffel' => 'No prices available for this product configuration'
            ]);
        }
    }
}
