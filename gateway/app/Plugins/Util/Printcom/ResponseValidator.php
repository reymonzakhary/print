<?php

declare(strict_types=1);

namespace App\Plugins\Util\Printcom;

use Illuminate\Http\Response;
use LogicException;
use RuntimeException;

final readonly class ResponseValidator
{
    /**
     * @param mixed $response
     *
     * @return void
     */
    public function ensurePluginResponseIsValid(mixed $response): void
    {
        if (!is_array($response)) {
            throw new LogicException(
                sprintf(
                    'Malformed response has been received from the plugin (Expect array but received "%s"',
                    gettype($response)
                )
            );
        }

        if (!array_key_exists('status', $response)) {
            throw new LogicException(
                'Malformed response has been received from the plugin (No `status` key has been found)'
            );
        }

        if ($response['status'] !== Response::HTTP_OK) {
            throw new RuntimeException(
                sprintf('Could not perform the operation (%s)', $response['message'])
            );
        }

        if (!array_key_exists('data', $response)) {
            throw new LogicException(
                'Malformed response has been received from the plugin (No `data` key has been found)'
            );
        }
    }
}
