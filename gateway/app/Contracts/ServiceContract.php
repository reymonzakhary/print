<?php


namespace App\Contracts;


use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class ServiceContract
{
    /**
     * The base uri to consume the internal users service
     * @var string|null
     */
    public ?string $base_uri;
    /**
     * @var string|null
     */
    public ?string $tenant_id;
    /**
     *
     * @var string|null
     */
    public ?string $tenant_name = null;
    /**
     * The base uri to consume the internal users service
     * @var string|null
     */
    public ?string $sha_request_phrase;
    /**
     * The base uri to consume the internal users service
     * @var string|null
     */
    public ?string $sha_response_phrase;
    /**
     * The base uri to consume the internal users service
     * @var string|null
     */
    public ?string $sha_type;
    /**
     * The base uri to consume the internal users service
     * @var string|null
     */
    public ?string $merchant_identifier;

    /**
     * @var string|null
     */
    protected ?string $access_code= '';
    /**
     * @var string|null
     */
    protected ?string $access_token= '';

    /**
     * @var string|null
     */
    protected ?string $environment = null;

    /**
     * @var string|null
     */
    protected ?string $username;

    /**
     * @var string|null
     */
    protected ?string $password;

    /**
     * @var string|null
     */
    protected ?string $sender;

    /**
     * @var string|null
     */
    protected ?string $template;

    /**
     * @param $methode
     * @param $args
     * @return mixed
     */
    public static function __callStatic(
        $methode,
        $args
    )
    {
        $methode = Str::camel(Str::replace('obtain', '', $methode));
        return (new static)->{$methode}(...$args);
    }

    /**
     * @throws ValidationException
     */
    public function validation(
        array $params = []
    ): void
    {
        $validator = Validator::make(get_object_vars($this), $params);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}
