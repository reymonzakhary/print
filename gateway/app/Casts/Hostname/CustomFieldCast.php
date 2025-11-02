<?php

namespace App\Casts\Hostname;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class CustomFieldCast implements CastsAttributes
{
    /**
     * @var array
     */
    protected array $data = [];
    protected ?Model $model = null; // Reference to the parent model
    protected ?string $attribute = null; // The attribute name in the model

    /**
     * @var array
     */
    protected array $allowedColumns = [
        'coc',
        'name',
        'email',
        'phone',
        'dial_code',
        'ready',
        'domain',
        'gender',
        'tax_nr',
        'password',
        'email_send',
        'company_name',
        'page_title',
        'page_description',
        'page_media',
        'shared_categories',
        'manager_language',
    ];

    /**
     * @var array
     */
    protected array $defaults = [
        'coc' => null,
        'name' => '',
        'email' => '',
        'phone' => '',
        'dial_code' => '',
        'ready' => false,
        'domain' => null,
        'gender' => 'unknown',
        'tax_nr' => null,
        'password' => '',
        'email_send' => false,
        'company_name' => '',
        'page_title' => '',
        'page_description' => '',
        'page_media' => [],
        'shared_categories' => [],
        'manager_language' => 'en',
    ];

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed|null $value The initial value to be decoded from JSON or set as an empty array.
     */
    public function __construct(
        mixed $value = null
    )
    {
        $this->data = $this->applyDefaults(json_decode($value, true) ?? []);
    }

    /**
     * Retrieves data from the specified model based on the provided key, value, and attributes.
     *
     * @param Model $model The model instance to retrieve data from.
     * @param string $key The key to search for in the model data.
     * @param mixed $value The value to match against the key in the model data.
     * @param array $attributes Additional attributes for data retrieval.
     * @return static The retrieved data from the model.
     */
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): static
    {
        $this->model = $model;
        $this->attribute = $key;
        $this->data = $this->sanitizeInput($this->applyDefaults(json_decode($value, true) ?? []));
        return $this;
    }

    /**
     * Sets the provided value to the specified key in the model after applying default values and validation.
     *
     * @param Model $model The model instance where the value will be set.
     * @param string $key The key to set the value for in the model.
     * @param mixed $value The value to be set in the model after applying defaults.
     * @param array $attributes Additional attributes for setting the value.
     * @return mixed The JSON encoded value that was set in the model.
     * @throws ValidationException
     */
    public function set(
        Model $model,
        string $key,
        mixed  $value,
        array $attributes
    ): mixed
    {

        $value = $this->sanitizeInput($this->applyDefaults($value));

        $this->validate($value);
        // Encode the JSON without unnecessary spaces or escapes
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function sanitizeInput(
        mixed $value
    ): array
    {
        $sanitized = [];
        foreach ((array)$value as $key => $item) {
            if ($key === 'page_media' && (is_array($item) || is_string($item))) {
                // If the field is page_media, process it as media
                $sanitized[$key] = $this->processMedia($item);
            } else {
                $sanitized[$key] = is_array($item)
                    ? $this->sanitizeInput($item)
                    : $item;
            }
        }
        return $sanitized;
    }

    /**
     * Processes media fields and applies a custom function if necessary.
     *
     * @param array|string $media The media data to process.
     * @return array|string The processed media data.
     */
    protected function processMedia(array|string $media): array|string
    {
        // Example: Add a prefix to media filenames or perform other processing
        if (is_array($media)) {
            return array_map(fn($item) => $this->processSingleMedia($item), $media);
        }

        return $this->processSingleMedia($media);
    }

    /**
     * Processes a single media item (e.g., add a prefix or validate it).
     *
     * @param string|array $item The media item to process.
     * @return array The processed media item.
     */
    protected function processSingleMedia(array|string $item): array
    {
        $item = is_array($item) && array_key_exists('path', $item) ? $item['path']: $item;
        return [
            'path' => $item,
            'url' => $this->model?->website?->uuid ?Storage::disk('assets')->url($this->model?->website?->uuid .'/'.$item):null,
        ];
    }

    /**
     * Applies default values to the provided data array. Merges the default values with the given data.
     *
     * @param mixed $data The data array to apply default values to.
     * @return array The resulting array after merging default values with the provided data.
     */
    protected function applyDefaults(mixed $data): array
    {
        if($data instanceof self){
            return array_replace_recursive($this->defaults, $data->toArray());
        }

        if (is_array($data)) {
            return array_replace_recursive($this->defaults, $data);
        }
        return $this->defaults;
    }

    /**
     * Validates the provided data based on specified rules.
     *
     * @param array $data The data to be validated.
     * @throws ValidationException If validation fails based on the specified rules.
     */
    protected function validate($data): void
    {
        $validator = Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|integer',
            'dial_code' => 'nullable|integer',
            'ready' => 'nullable|boolean',
            'domain' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,unknown,other',
            'tax_nr' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
            'email_send' => 'nullable|boolean',
            'company_name' => 'nullable|string|max:255',
            'page_title' => 'nullable|string|max:255',
            'page_description' => 'nullable|string|max:500',
            'page_media' => 'nullable|array',
            'page_media.*.path' => 'nullable|string',
            'page_media.*.url' => 'nullable|string',
            'shared_categories' => 'nullable|array',
            'manager_language' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Retrieves the value from the data array based on the provided key.
     *
     * @param string $key The key to retrieve the value for from the data array.
     * @return mixed|null The value associated with the provided key from the data array, or null if the key doesn't exist.
     */
    public function pick(
        string $key
    ): mixed
    {
        // Ensure you return only the data part, not internal columns
        return $key === '*data' ? (object) $this->data : Arr::get($this->data, $key);
    }

    /**
     * Adds a key-value pair to the data array if the key is allowed.
     *
     * @param string $key The key to add to the data array.
     * @param mixed $value The value associated with the key to add to the data array.
     * @return $this Allowing method chaining by returning the instance of the class.
     */
    public function add(
        string $key,
        mixed $value
    ): static
    {
        if (in_array($key, $this->allowedColumns)) {
            $this->data[$key] = $value;
        }
        return $this; // Allow method chaining
    }

    /**
     * Get all items with default values applied.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return collect($this->applyDefaults($this->data));
    }

    /**
     * Get all items as an array.
     *
     * @return array
     */
    public function toArray():array
    {
        return $this->all()->toArray();
    }

    /**
     * Get all items except for specified keys.
     *
     * @param array $keys The keys to exclude from the collection.
     * @return array
     */
    public function except(
        array $keys = []
    ): array
    {
        return $this->all()->reject(fn($v, $k) => in_array($k, $keys, true))->toArray();
    }
}
