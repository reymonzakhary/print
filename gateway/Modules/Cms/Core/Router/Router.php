<?php

namespace Modules\Cms\Core\Router;

use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Modules\Cms\Core\Router\Contracts\RouterContractInterface;

class Router implements RouterContractInterface
{

    /**
     * The uri of the rquested url
     * @var string
     */
    public string $uri;

    /**
     *
     */
    public function __construct(
        public Request $request,
        public SessionManager $session,
        public CacheManager $cache,
    )
    {
        $this->uri = $this->request->getRequestUri();
        dd($this->uri);
    }
}
