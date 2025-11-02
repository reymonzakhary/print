<?php
namespace App\Traits;

use GuzzleHttp\Client;

trait CurlRequestTrait
{
    /**@var array $errors */
    private $errors;

    /**
     * Send a request to given URL
     *
     * @param string $url
     * @return string
     */
    public function curlRequest(
        string $url
    )
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_ENCODING => "",

                CURLOPT_MAXREDIRS => 10,

                CURLOPT_TIMEOUT => 3000,

                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                CURLOPT_CUSTOMREQUEST => "GET",

                CURLOPT_HTTPHEADER => [
                    "API-Secret: Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                    "User-ID: 0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                    "accept: application/vnd.printdeal-api.v2"
                ]
            ]
        );
        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if($err){
            $this->setError($err);

            return false;
        }

        return json_decode($response, true);
    }

    /**
     * Send POST request to given URL
     *
     * @param $requestType
     * @param $url
     * @param $postfields
     * @return string
     */
    public function curlPostRequest($url, $data = '')
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "API-Secret: {$this->api_secret}",
            "User-ID: {$this->user_id}"
        ]);

        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->setError($err);

            return false;
        } else {
            return [
                'code' => $statusCode,
                'data' => $response
            ];
        }
    }

    /**
     * Data Setter
     *
     * @param string $error
     */
    private function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Data Getter
     *
     * @return array $errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *  Enter a log value (the text that you want to log)
     *
     * @param string $value
     */
    private function setLog($value)
    {
        $value = date('Y-m-d H:i:s') . ' ' . $value;

        echo "$value<br />\r\n";
    }
}
