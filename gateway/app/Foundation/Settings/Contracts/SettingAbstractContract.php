<?php

namespace App\Foundation\Settings\Contracts;

use App\Models\Tenant\Setting;
use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;

abstract class SettingAbstractContract
{

    /**
     * The fallback setting if the application not authenticated on a tenant.
     *
     * @var array
     */
    protected array $fallback = [];

    /**
     * Column selection options from db.
     *
     * @var array|string[]
     */
    protected array $columns = ['value'];

    /**
     * The name of drive to be used for getting the settings.
     *
     * @var string
     */
    protected string $driver = 'system';

    /**
     * The Eloquent model.
     * The model name is the path to the model where we will build our query based on it.
     * @var string
     */
    protected string $model_name = Setting::class;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * The callback that may modify the user retrieval queries.
     *
     * @var Closure|null (Closure(Builder):mixed)|null
     */
    protected ?Closure $queryCallback;

    public function __construct(
        public Request  $request,
        public SessionManager $session,
        public CacheManager $cache,
        ?Closure $queryCallback = null
    )
    {
        $this->queryCallback = $queryCallback ?? null;
    }

    /**
     * @param $key
     * @return Builder|Model|object|null
     */
    public function __get(
        $key
    )
    {
        if($tenant = tenant()) {
            return $this->cache->remember("{$tenant->uuid}_settings_{$this->driver}_{$key}",1440, function() use ($key) {
                // First we will add each credential element to the query as a where clause.
                // Then we can execute the query and, if we found a user, return it in a
                // Eloquent User "model" that will be utilized by the Guard instances.
                $query = $this->newModelQuery();
                $params = ['key' => $key];

                if($this->driver === 'user') {
                    $params = array_merge($params, ['user_id' => $this->request->user()->id]);
                }

                foreach ($params as $k => $value) {
                    if (is_array($value) || $value instanceof Arrayable) {
                        $query->whereIn($k, $value);
                    } elseif ($value instanceof Closure) {
                        $value($query);
                    } else {
                        $query->where($k, $value);
                    }
                }

                return $query->select(...$this->columns)->first();
            });
        }

        return (object) $this->fallback;
    }

    public function __set($key, $value)
    {
//        dump(__METHOD__, $key);
    }

    public function __isset($key)
    {
//        dump(__METHOD__, $key);
    }

    /**
     * Get a new query builder for the model instance.
     *
     * @param Model|null $model
     * @return Builder
     */
    public function newModelQuery(
        Model $model = null
    ): Builder
    {
        $query = is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();

        with($query, $this->queryCallback);

        return $query;
    }

    /**
     * Create a new instance of the model.
     *
     * @return Model
     */
    public function createModel(): Model
    {
        $class = '\\' . ltrim($this->model_name, '\\');
        return new $class;
    }
}
