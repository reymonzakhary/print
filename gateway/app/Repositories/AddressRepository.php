<?php

namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use App\Models\Tenants\Address;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class AddressRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        // TODO: Implement show() method.
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10)
    {
        return $this->model->paginate($per_page);
    }

    /**
     * @param array $attributes
     *
     * @return Address
     */
    public function firstOrCreate(array $attributes): Address
    {
        $uniqueConstraintKeywords = ['address', 'number', 'city', 'region', 'country_id', 'zip_code', 'format_address', 'floor', 'apartment', 'neighborhood', 'landmark'];
        $allowedAttributesKeywords = array_merge($uniqueConstraintKeywords, ['lat', 'lng']);

        $attributesFilteredByUniqueConstraint = collect($attributes)->filter(
            function ($value, $key) use ($uniqueConstraintKeywords) {
                return in_array($key, $uniqueConstraintKeywords, true);
            }
        )->toarray();

        $attributesFilteredByAllowed = collect($attributes)->filter(
            function ($value, $key) use ($allowedAttributesKeywords) {
                return in_array($key, $allowedAttributesKeywords, true);
            }
        )->toArray();

        return $this->model->firstOrCreate($attributesFilteredByUniqueConstraint, $attributesFilteredByAllowed);
    }

    /**
     * Sync without detaching an address to a model
     *
     * @param Address $address
     * @param Model $model
     * @param array $pivotAttributes
     *
     * @return void
     */
    public function syncWithoutDetachingToModel(
        Address $address,
        Model $model,
        array $pivotAttributes
    ): void
    {
        if (!method_exists($model, 'addresses')) { # Temp approach - should be replaced by an interface instead
            throw new LogicException(
                sprintf('Model "%s" does not have the relation/method addresses()', get_class($model))
            );
        }

        $pivotAttributesFiltered = collect($pivotAttributes)
            ->filter(
                fn($value, $key) => in_array($key, [
                    'type',
                    'default',
                    'company_name',
                    'phone_number',
                    'tax_nr',
                    'full_name',
                    'team_address',
                    'team_id',
                    'team_name',
                    'dial_code'
                ], true)
            )->toArray();

        $model->addresses()->syncWithoutDetaching([
            $address->getAttribute('id') => $pivotAttributesFiltered
        ]);
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Address
    {
        return $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        if ($address = $this->model->where('id', $id)->first()) {
            return $address->update(
                $attributes
            );
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->model->detach($id);
    }
}
