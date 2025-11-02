<?php

namespace Modules\Cms\Http\Controllers;

use App\Http\Middleware\CmsMiddlewareManager;
use App\Models\Tenants\Context;
use App\Models\Tenants\User;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Modules\Cms\Compiler\Chunks\ChunkCompiler;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Entities\Template;
use Modules\Cms\Foundation\Cms;
use Modules\Cms\Foundation\Contracts\CmsContract;
use Modules\Cms\Foundation\Forms\Form;
use Modules\Cms\Foundation\Forms\FormFactory;
use Modules\Cms\Plugins\Menu\Menu;
use Modules\Cms\Plugins\Menu\MenuRepository;
use Modules\Cms\Plugins\Resources\Resource as ResourcePlugin;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class CmsController extends Controller
{

    /**
     * @var string
     * building private pages
     * login pages
     * register pages
     * reset password pages
     *
     */

    protected Context $context;

    protected string $uri;
    protected Cms $cms;

    public function __construct()
    {
        $this->uri = \Request::getRequestUri();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string
     */
    public function index(
        Request $request
    )
    {
        return app(Cms::class)->run();

        // if (in_array($this->uri, ['/', null], true)) {
        //     $uriQuery = function ($q) {
        //         $q->where('uri', '/home')->orWhere('uri', '/');
        //     };
        // } else {
        //     $uriQuery = ['uri' => $this->uri];
        // }
        // app()->setLocale('nl');
        // $resource = Resource::withCount('groups')->where(['language' => app()->getLocale()])->where($uriQuery)->first();

        // if (!$resource) {
        //     return response()->json([
        //         'data' => [
        //             'message' => __('Page not found'),
        //             'status' => Response::HTTP_NOT_FOUND
        //         ]
        //     ], Response::HTTP_NOT_FOUND);
        // }

        // $tree = Resource::tree()->where(['language' => app()->getLocale()])->select(
        //     'id','base_id','resource_id', 'isfolder', 'uri', 'long_title','title','intro_text','menu_title','slug',
        //     'language','sort','hidden','published',
        //     'parent_id', 'depth', 'path'
        //     )->get()->toTree()->toArray();

        // $userResources = [];
        // if (auth()->check()) {
        //     $userResources = auth()->user()->user_resources->pluck('resource_id')->toArray();
        // }

        // if ($resource->groups_count && !in_array($resource->resource_id, $userResources, true)) {
        //     abort(401, "can not access this area");
        // }

        // $html = optional(optional($resource)->template)->content;
        // if ($html) {
        //     $html = htmlspecialchars_decode(ChunkCompiler::flatHtml($html));
        //     return $this->compile($tree, $html, $resource, optional($resource)->template);
        // }
        // // $html = htmlspecialchars(Menu::build($html));
        // return Blade::compileString($html);
    }

    protected function compile(
        array $menu,
        string    $string,
        Resource  $resource,
        ?Template $template
    )
    {
        if ($string) {

            preg_match_all('/\[\[(.*?)\]\]/', $string, $matched);
            // extract php tags
            /*            $string = preg_replace_callback('/<\?[^"]+\?>/', function($match) {*/
//                return '';
//            },$string);

            // get chunks
            $string = preg_replace_callback('/\[\[\$(.*?)\]\]/', function ($chunk) {
                $co = optional(Chunk::where('name', $chunk[1])->first())->content;
                if ($co) {
                    return htmlspecialchars_decode($co);
                }
            }, $string);


            $string = Menu::render($string);
            $string = ResourcePlugin::render($string);
//            $string = preg_replace_callback('/\[\[!menu?[^"]+]]/', function($match) use ($string) {
//
//                $start = 0;
//                $depth = 0;
//                $outerTpl = null;
//                $rowTpl = null;
//                $innerTpl = null;
//                $innerRowTpl = null;
//                if(count($match)>0){
//                    $match = str_replace(['[[', ']]', '`', '!menu?'], ['','','',''],$match[0]);
//                    parse_str($match, $output);
//                    $output = array_map('trim',$output);
//                    foreach($output as $key => $value) {
//                        if(preg_match('/(.*?)+Tpl$/', $key, $match)) {
//                            $output[$key] = htmlspecialchars_decode(optional(Chunk::where('name', $value)->first())->content);
//                        }else{
//                            $output[$key] = $value;
//                        }
//                    }
//
//                    $resources = Resource::where([
//                        ['language' , app()->getLocale()],
//                        ['published' , true],
//                        ['hidden' , false]
//                    ])->with(['children'])
//                        ->orderBy($output['sort']??'sort', $output['sortDir']??'ASC')
//                        ->isParent()
//                        ->get();
//
//                    $outerTpl = $this->blader($output['outerTpl'],['resources' => $resources->toArray()]);
//                    $outerTpl = preg_replace_callback('/\[\[\+m.rowTpl\]\]/',  function($matche) use ($output, $resources) {
//                        $rowTpl = '';
//                        foreach($resources as $resource) {
//                                $rowTpl .= $this->blader($output['rowTpl'], $resource->toArray());
//                                $rowTpl = preg_replace_callback('/\[\[\+m.innerTpl\]\]/',  function($matche) use ($output, $resource) {
//                                    $innerTpl = '';
//                                    if(count($matche) > 0 && $resource->children()->count() > 0) {
//                                        $innerTpl = $this->blader($output['innerTpl'], []);
//                                        $innerTpl = preg_replace_callback('/\[\[\+m.innerRowTpl\]\]/',  function($matche) use ($output, $resource) {
//                                            $innerRowTpl = '';
//                                            foreach($resource->children()->where([
//                                                ['language' , app()->getLocale()],
//                                                ['published' , true],
//                                                ['hidden' , false]
//                                            ])->get() as $child) {
//                          value                      $innerRowTpl .= $this->blader($output['innerRowTpl'], $child->toArray());
//                                                $innerRowTpl = preg_replace_callback('/\[\[\+m.childrenTpl\]\]/',  function($matche) use ($output, $child) {
//                                                    $childrenTpl = '';
//                                                    if(count($matche) > 0 && $child->children()->count() > 0) {
//
//                                                        foreach($child->children()->where([
//                                                            ['language' , app()->getLocale()],
//                                                            ['published' , true],
//                                                            ['hidden' , false]
//                                                        ])->get() as $child) {
//                                                            $childrenTpl .= $this->blader($output['childrenTpl'], $child->toArray());
//                                                        }
//                                                    }
//                                                    return $childrenTpl;
//                                                }, $innerRowTpl);
//                                            }
//                                            return $innerRowTpl;
//                                        }, $innerTpl);
//                                    }
//
//                                    return $innerTpl;
//                                }, $rowTpl);
//                        }
//                        return $rowTpl;
//                    },$outerTpl);
//
//                    return $outerTpl;
//
//                }
//            },$string);


            preg_match_all('/(src|href)="([^"]+\.(js|css|png|jpg|jpeg|gif|woff2|woff|html|svg|ico))"/', $string, $source);
//            preg_match_all('/\[\[(.*?)\]\]/',  $string, $matched);

            preg_match_all('/^\*+&name=`(.*?)`/', $string, $tags);
//            dd($matched, $tags);
            $default = collect($resource->toArray())->map(function ($v, $k) {
                if (in_array($k, ['title', 'long_title', 'intro_text', 'description', 'menu_title', 'slug', 'uri'])) {
                    return $v;
                }
            })->toArray();

            $content = [];

            $string = $this->renderAssets($string);

            if ($resource->content) {
                foreach ($resource->content ?? [] as $k => $v) {
                    $value = '';
                    if (optional($v)['type'] == 'file') {
                        $value = url('api/v1/en/mgr/media-manager/file-manager/public?disk=tenancy&path=' . optional(optional($v)['value'])['path']);
                    } else {
                        $value = optional($v)['value'];
                    }
                    $content[$v['key']] = $value;
                }
            }

            $htm = preg_replace_callback('/\[\[(.*?)\]\]/', function ($matches) use ($resource, $content) {
                if ($resource->content) {
                    foreach ($resource->content as $v) {
                        preg_match('/&name=`(.*?)`/', $matches[1], $tag);
                        if (count($tag) > 0 && in_array($tag[1], $v)) {
                            return "{{\${$tag[1]}}}";
                        }
                    }
                }
            }, $string);

            $content = array_merge(['menu' => $menu],$content, $default, ['url' => 'http://reseller.prindustry.test']);
            return $this->blader($htm, $content);
        }
        return $this->blader('', []);
    }

    /**
     * @throws FatalThrowableError
     */
    protected function blader(
        string $str,
        array  $data = []
    )
    {
        $empty_filesystem_instance = new Filesystem;
        $blade = new BladeCompiler($empty_filesystem_instance, 'datatables');
        $parsed_string = $blade->render($str);
        
        $obLevel = ob_get_level();
        ob_start();
        $__data['__env'] = app(\Illuminate\View\Factory::class);
        extract($__data, EXTR_SKIP);
        ob_start() and extract($data, EXTR_SKIP);
        try {
            eval('?' . '>' . $parsed_string);
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            return $e->getMessage();
        }
        return ob_get_clean();
    }

    /**
     * @param String $string
     * @return mixed|String
     */
    protected function renderAssets(
        string $string
    )
    {
        $tenant = request()->tenant->uuid;
        preg_match_all('/(src|href)="([^"]+\.(js|css|png|jpg|jpeg|gif|woff2|woff|html|svg|ico))"/', $string, $source);

        if (count($source) > 0) {
            foreach ($source[2] as $src) {
                if (str_contains($src, 'assets')) {

                    if (file_exists(public_path("storage/{$tenant}/{$src}"))) {
                        $string = str_replace('"' . $src . '"', '"' .Storage::disk('assets')->url("{$tenant}/{$src}") . '"', $string);
                    }
                } else {
                    if (file_exists(public_path("storage/{$tenant}/assets/{$src}"))) {
                        $string = str_replace('"' . $src . '"', '"' . url("storage/{$tenant}/assets/{$src}") . '"', $string);
                    }
                }
            }
        }

        return $string;

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('cms::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        return app(Cms::class)->run();
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (!$credentials) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        } else {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->intended('/');
            }
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('cms::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('cms::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int     $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    protected function getResources()
    {
        dd(Resource::get());
    }


    protected function getContext()
    {

    }

}
