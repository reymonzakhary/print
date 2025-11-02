<?php


namespace App\Utilities\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\UploadedFile;

trait ConsumesExternalServices
{
    /**
     * Send a request to any service
     * @param string $method
     * @param string $requestUrl
     * @param array $queryParams
     * @param array $formParams
     * @param array $headers
     * @param bool $hasFile
     * @param bool $forceJson
     * @return array|string|object|null
     * @throws GuzzleException
     */
    public function makeRequest(
        string $method,
        string $requestUrl,
        array  $queryParams = [],
        array  $formParams = [],
        array  $headers = [],
        bool   $hasFile = false,
        bool   $forceJson = false
    ): array|string|object|null
    {
        try {
            $client = new Client([
                'base_uri' => $this->base_uri,
            ]);
            if ($forceJson) {
                $bodyType = RequestOptions::JSON;
            } else {
                $bodyType = 'form_params';
            }
            if ($hasFile) {
                $bodyType = 'multipart';
                $multipart = [];

                foreach ($formParams as $name => $contents) {
                    if (!is_array($contents)) {
                        $pre = [
                            'name' => $name,
                            'contents' => $contents,
                        ];
                        if ($contents instanceof UploadedFile) {
                            $pre['filename'] = optional($contents)->getClientOriginalName() ?? null;
                            $pre['contents'] = $contents->getContent();
                        }
                        $multipart[] = $pre;
                        continue;
                    }


                    foreach ($contents as $multiKey => $multiValue) {
                        $multiName = $name . '[' . $multiKey . ']' . (is_array($multiValue) ? '[' . key($multiValue) . ']' : '') . '';
                        $multiValue = (is_array($multiValue) ? reset($multiValue) : $multiValue);
                        $preps = [
                            'name' => $multiName,
                            'contents' => $multiValue,
                        ];
                        if ($multiValue instanceof UploadedFile) {
                            $preps['filename'] = $multiValue->getClientOriginalName();
                            $preps['contents'] = $multiValue->getContent();
                        }
                        $multipart[] = $preps;
                    }
                }
            }

            $res = $client->request($method, $requestUrl, [
                'query' => $queryParams,
                $bodyType => $hasFile ? $multipart : $formParams,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents(), true, 512);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = $res->getBody();
            }


        } catch (RequestException $e) {
            $response = [
                "message" => var_export($e->getMessage(), true),
                "status" => $e->getCode()
            ];
        }
        return $response->data ?? $response;
    }
}
