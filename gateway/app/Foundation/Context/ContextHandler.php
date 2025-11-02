<?php

namespace App\Foundation\Context;

use App\Models\Tenants\Address;
use App\Models\Tenants\Context;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\SessionManager;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Request;

class ContextHandler
{

    /**
     * The key used for storage or retrieval in the application
     */
    protected string $key;

    /**
     * @var Model|Collection
     */
    protected Collection|Model $context;

    /**
     * @var Null|Model
     */
    protected Model|null $currentContext;

    /**
     * Constructor for initializing CacheManager, SessionManager, and Request dependencies
     *
     * @param CacheManager $cache The CacheManager instance to be injected
     * @param SessionManager $session The SessionManager instance to be injected
     * @param Request $request The Request instance to be injected
     */
    public function __construct(
        private readonly CacheManager   $cache,
        private readonly SessionManager $session,
        private readonly Request $request
    ){
        $this->load();
    }

    /**
     * Retrieve the current context model.
     *
     * @return Model
     */
    public function current(): Model
    {
        return $this->currentContext;
    }

    /**
     * Set the context to filter by the specified key
     *
     * @param string|null $ctx
     * @return static
     */
    #[NoReturn] public function from(
        string $ctx = null
    ): static
    {
        $this->context = $this->context->where('name', $ctx??$this->key);

        return $this;
    }

    /**
     * Retrieve the first element from the collection of context.
     *
     * @return mixed The first element of the context collection.
     */
    public function first(): mixed
    {
        return $this->context->where('name', $this->key)->first();
    }

    /**
     * Get the collection of contexts
     *
     * @return Collection
     */
    public function get(): Collection
    {
        return $this->context;
    }

    /**
     * Get the addresses related to the current context
     *
     * @return mixed The addresses associated with the current context
     */
    public function addresses(): mixed
    {
        $this->ensureSingleContext();
        return $this->context->addresses;
    }

    /**
     * Return the address associated with the current context
     *
     * @return mixed The first address associated with the current context
     */
    public function address(): Address
    {
        $this->ensureSingleContext();
        return $this->context->addresses->first();
    }

    /**
     * Check if the user has any addresses.
     *
     * @return bool
     */
    public function hasAddress(): bool
    {
        $this->ensureSingleContext();
        return (bool) $this->context->addresses->count();
    }

    /**
     * Check if the context 'mgr' has any addresses associated with it
     *
     * @return bool Returns true if the 'mgr' context has addresses, false otherwise
     */
    public function hasMgrAddress():bool
    {
        return (bool) $this->context->where('name','mgr')
            ->first()?->addresses?->count();
    }

    /**
     * Loads context data from cache or database.
     */
    private function load(): void
    {
        $this->context = $this->cache
            ->remember($this->request->tenant->uuid.".contexts", 1440, function () {
                return Context::with('addresses.country')
                    ->get();
            });
        $this->setLoadedCtx();
    }

    /**
     * Set the loaded context either from the session or default to 'mgr'
     */
    private function setLoadedCtx(): void
    {
        if(!$this->session->exists($this->detectContextKey())) {
            $this->session->put($this->detectContextKey(),
                $this->context->where('name', $this->key)->first()
            );
        }
        $this->currentContext = $this->session->get($this->detectContextKey());
    }

    /**
     * Determines the key for the context based on the request.
     *
     * If the URI pattern contains 'mgr', the context key is built using the user ID with '.mgr.contexts.' and the tenant's UUID.
     * Otherwise, the context key is constructed using the user ID with '.web.contexts.' and the tenant's UUID.
     *
     * @return string The key for the context based on the request
     */
    private function detectContextKey(): string
    {
        if($this->request->is('*/mgr/*')) {
            $this->key = 'mgr';
            $this->session->forget($this->request->user()->id.".web.contexts.".$this->request->tenant->uuid);
            return $this->request->user()->id.".mgr.contexts.".$this->request->tenant->uuid;
        }
        $this->key = 'web';
        $this->session->forget($this->request->user()->id.".mgr.contexts.".$this->request->tenant->uuid);
        return $this->request->user()->id.".web.contexts.".$this->request->tenant->uuid;
    }

    /**
     * Ensure the context is not a collection.
     */
    private function ensureSingleContext(): void
    {
        if($this->context instanceof Collection){
            $this->key = $this->context->pluck('name')->first();
            $this->context = $this->first();
        }
    }

    /**
     * Clear cached contexts and reload fresh data.
     *
     * @return void
     */
    public function refresh(): void
    {
        $cacheKey = $this->request->tenant->uuid . ".contexts";

        // Forget the cache
        $this->cache->forget($cacheKey);

        // Reload contexts into memory
        $this->load();
    }

}
