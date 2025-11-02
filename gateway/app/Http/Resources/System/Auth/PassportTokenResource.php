<?php

declare(strict_types=1);

namespace App\Http\Resources\System\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PassportTokenResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(
        Request $request
    ): array
    {
        return [
            'token_type' => $this->resource->token_type ?? null,
            'access_token' => $this->resource->access_token ?? null,
            'refresh_token' => $this->resource->refresh_token ?? null,
            'expires_in' => $this->resource->expires_in ?? null,
        ];
    }
}
