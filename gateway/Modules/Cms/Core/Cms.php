<?php

namespace Modules\Cms\Core;

use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Modules\Cms\Core\Contracts\CmsFactoryInterface;
use Modules\Cms\Core\Router\Router;

class Cms
{
    /**
     * @var CmsStack
     */
    public CmsStack $stack;

    /**
     * @var CmsFactory
     */
    public CmsFactory $factory;

    /**
     * @param Request        $request
     * @param SessionManager $session
     * @param CacheManager   $cache
     */
    public function __construct(
        public Request $request,
        public SessionManager $session,
        public CacheManager $cache,
    ) {
       $this->stack = app(CmsStack::class);
       $this->factory = app(CmsFactoryInterface::class);

    }


    public function cms()
    {
        $this->init();
        dd($this->uri);
        return 'running';
    }


    private function init()
    {
        collect($this->int)->each(function($class, $key) {
            $c = str_replace(['ContractInterface','\\Contracts'], ['',''], $class);
            $class = "\\{$c}";
            if(class_exists($class)) {
                app()->bind($key, function() use ($class) {
                    new $class($this->request, $this->session, $this->cache);
                });
            }
        });
    }

}
