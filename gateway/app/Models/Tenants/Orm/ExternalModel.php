<?php

namespace App\Models\Tenants\Orm;

use App\Contracts\ServiceContract;
use ArrayAccess;
use Exception;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonSerializable;

/**
 * Service name.
 *
 * @var string
 */
abstract class ExternalModel implements Arrayable, ArrayAccess, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable
{

    /**
     * @var string
     */
    public string $service;

    /**
     * @var mixed
     */
    public mixed $model;

    /**
     * @var string
     */
    public string $method;

    /**
     * @var mixed
     */
    protected mixed $results;


    public function __construct()
    {
        $this->bootIfNotBooted();
    }

    /**
     * Perform a transformation and return the results if not empty.
     *
     * @return void
     * @throws ValidationException
     */
    protected function ifNotEmpty(): void
    {
        if(!in_array(optional($this->results)['status'], [200,201], true)) {
            $key = Str::lower(Str::afterLast(get_class($this), '\\'));
            throw ValidationException::withMessages([
                $key => ($this->results)['message']
            ]);
        }

        $this->results = $this->changeKeyRecursive(optional($this->results)['data']??[]);
    }

    /**
     * Get the results of the query.
     *
     * @return Collection
     * @throws Exception
     */
    public function get(): Collection
    {
        $this->ifNotEmpty();
        return collect(json_decode(json_encode($this->results, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Get the first result as a stdClass object.
     *
     * @return \stdClass
     * @throws ValidationException|\JsonException
     */
    public function first(): \stdClass
    {
        $this->ifNotEmpty();

        return json_decode(
                json_encode(
                    $this->results, JSON_THROW_ON_ERROR
                ), false, 512, JSON_THROW_ON_ERROR
            );
    }

    /**
     * Update the data.
     *
     * @return bool
     * @throws ValidationException
     */
    public function update(): true
    {
        $this->ifNotEmpty();
        return true;
    }

    /**
     * Delete the record.
     *
     * @return bool
     * @throws ValidationException
     */
    public function delete(): true
    {
        $this->ifNotEmpty();
        return true;
    }

    /**
     * Handle dynamic method calls.
     *
     * @param string $method
     * @param array  $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->results = $this->model->{$method}(...$args);
        return $this;
    }

    /**
     * Handle dynamic static method calls.
     *
     * @param string $name      The name of the method being called.
     * @param array  $arguments Arguments passed to the method.
     * @return static
     */
    public static function __callStatic(string $name, array $arguments)
    {
        (new static)->$name(...$arguments);

        return new static;
    }

    /**
     * Recursively changes the key "_id" to "id" in an array or Collection.
     *
     * @param array|Collection $item The array or Collection to modify.
     * @return Collection|array The modified array.
     */
    protected function changeKeyRecursive(
        array|Collection $item = []
    ): Collection|array
    {
        if (is_array($item) || $item instanceof Collection) {
            return collect($item)->map(function($value, $key) {
                if ($key === '_id') {
                    $key = 'id';
                }
                if (is_array($value) || $value instanceof Collection) {
                    $value = $this->changeKeyRecursive($value);
                }
                return [$key => $value];
            })->collapse()->toArray();
        }

        return $item;
    }

    /**
     * @return void
     */
    protected function bootIfNotBooted(): void
    {
        $this->model = app($this->service);
    }

    /**
     * Perform a query operation.
     *
     * @return $this
     * @throws ValidationException
     */
    protected function query(): static
    {
        if($this->model instanceof ServiceContract) {
            return $this;
        }

        throw ValidationException::withMessages([
            'model' => [
                __('Model is not a instance of Service Contract.')
            ]
        ]);

    }

    public function resolveRouteBinding($value, $field = null)
    {
        dd($value, $field);
    }



}
