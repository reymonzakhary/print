<?php

namespace Modules\Cms\Foundation;

use App\Models\Tenants\Language;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Contracts\CmsContract;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Foundation\Router\Router;

class Cms
{
    private Router $router;

    public function __construct(
        private Request $request,
        private SessionManager $session
    )
    {
        $this->router = new Router($request);
    }

    public function run()
    {
        return $this->router->route();
    }
    
    private function render($content)
    {
        return $content;
    }


    protected function loadTree(): array
    {
        return Resource::tree()->where(['language' => app()->getLocale()])->select(
            'id','base_id','resource_id', 'isfolder', 'uri', 'long_title','title','intro_text','menu_title','slug',
            'language','sort','hidden','published',
            'parent_id', 'depth', 'path'
            )->get()->toTree()->toArray();
    }

    protected function loadLanguages():array
    {
        return DB::table('languages')->all()->toArray();
    }

    
}
