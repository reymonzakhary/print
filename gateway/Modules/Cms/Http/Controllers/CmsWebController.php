<?php

namespace Modules\Cms\Http\Controllers;

use App\Models\Tenants\Context;
use App\Models\Tenants\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Entities\Template;

class CmsWebController extends Controller
{
    protected ?User $user;

    protected Context $context;

    protected string $uri;

    protected array $config = [];

    protected ?Resource $resource;

    protected ?Template $template;

    /**
     * @param Request $request
     * @param string  $uri
     * @param array   $params
     * @return mixed
     */
    public function __invoke(
        Request $request,
        string  $uri = '/',
        array   $params = []
    )
    {
        return $this->getContext()
            ->getAuth($request)
            ->makeUrl($uri)
            ->getPage($request)
            ->render();
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function getPage(
        Request $request
    ): self
    {
        $this->resource = Resource::where([
            ['uri', $this->uri],
            ['language', app()->getLocale()]
        ])->first();

        if ($this->resource) {
            $this->template = $this->resource->getTemplate();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function getContext(): self
    {
        $baseUri = parse_url(URL::to(''));
        $this->config = Context::whereJsonContains('config->base_url', $baseUri['host'])->pluck('config')->toArray();
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    private function getAuth(Request $request): self
    {
        if ($user = $request->user()) {
            $this->user = $user;
            $this->groups = $user->teams();
        }
        return $this;
    }

    /**
     * @param string $url
     * @return string
     */
    private function makeUrl(string $url)
    {
        $this->uri = preg_match('/^\//', $url) ? $url : '/' . $url;
        return $this;
    }

    public function render()
    {
        return $this->resource;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('cms::index');
    }
}
