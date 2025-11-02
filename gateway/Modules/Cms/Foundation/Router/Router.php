<?php

namespace Modules\Cms\Foundation\Router;

use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Compiler\Compiler;
use Modules\Cms\Foundation\Helpers\Account;
use Modules\Cms\Foundation\Helpers\Cart;
use Modules\Cms\Foundation\Helpers\EmailVerification;
use Modules\Cms\Foundation\Helpers\Login;
use Modules\Cms\Foundation\Helpers\Registration;

class Router
{

    public function __construct(private $request)
    {}

    public function route()
    {
        $resource = $this->loadResource();

        if (!$resource){
            $uri = $this->getNotFoundResourceUri();
            return $uri? redirect($uri) : abort(404, __("Page not found."));
        }

        $userResources = [];
        $userResources = auth()?->user()?->user_resources->pluck('resource_id')->toArray()??[];

        if ($resource->groups_count && !in_array($resource->resource_id, $userResources, true)) {
            return redirect()->back();
        }

        return match ($this->request->method()) {
            'GET' => $this->processGetRequest($resource),
            'POST' => $this->processPOSTRequest(),
        };
    }

    protected function processGetRequest($resource)
    {

        $compiler = new Compiler(
            $resource
        );
        $content = $compiler->compile();

        return $content;
    }

    protected function processPOSTRequest()
    {
        if (!$this->request->__command) {
            return redirect('/');
        }

        return match ($this->request->__command) {
            'login' => Login::build($this->request)->fill($this->request->__data)->login(),
            'registration' => Registration::build($this->request)->fill($this->request->__data)->registration(),
            'logout' => Login::build($this->request)->fill($this->request->__data??[])->logout(),
            'codeVerification' => EmailVerification::build($this->request)->fill($this->request->__data??[])->verify(),
            'sendVerificationEmail' => EmailVerification::build($this->request)->fill($this->request->__data??[])->sendEmail(),
            'addToCart' => Cart::build($this->request)->fill($this->request->__data??[])->addToCart(),
            'removeFromCart' => Cart::build($this->request)->fill($this->request->__data??[])->removeFromCart(),
            'emptyCart' => Cart::build($this->request)->fill($this->request->__data??[])->emptyCart(),
            'createAddress' => Account::build($this->request)->fill($this->request->__data??[])->createAddress(),
            'updateAddress' => Account::build($this->request)->fill($this->request->__data??[])->updateAddress(),
            'deleteAddress' => Account::build($this->request)->fill($this->request->__data??[])->deleteAddress(),
            'updateAccount' => Account::build($this->request)->fill($this->request->__data??[])->updateAccount(),
            'checkout' => Cart::build($this->request)->fill($this->request->__data??[])->checkout(),
        };
    }

    /**
     * @return string
     */
    private function getNotFoundResourceUri()
    {
        return Resource::where('uri', '/404')->orWhere('uri', '/not-found')->first()?->uri;
    }

    private function loadResource()
    {
        $uri = '/'. $this->request->uri;
        if (in_array($uri, ['/', null], true)) {
            $uriQuery = function ($q) {
                $q->where('uri', '/home')->orWhere('uri', '/');
            };
        } else {
            $uriQuery = ['uri' => $uri];
        }

        return Resource::withCount('groups')->with('media')->where(['language' => app()->getLocale()])->where('published', true)->where($uriQuery)->first();
    }
}
