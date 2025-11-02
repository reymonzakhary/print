<?php

namespace App\Http\Middleware;

use Alexusmai\LaravelFileManager\Middleware\FileManagerACL as FMACL;
use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
use App\Http\Requests\FileManager\RequestValidator;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use Closure;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileManagerACL extends FMACL
{
    public $originalPath = "/";

    protected $size = 0;

//    protected $total = 5368700000;
    protected $total = 268435456000;

    protected $disk = 'tenancy';

    /**
     * FileManagerACL constructor.
     *
     * @param Request $request
     * @param ACL     $acl
     */
    public function __construct(Request $request, ACL $acl)
    {

        parent::__construct($request, $acl);
        $this->disk = $request->has('disk') ? $request->input('disk') : 'tenancy';
        $this->originalPath = $request->has('path')
        && $request->input('path') !== "/"
        && $request->input('path') !== "null"
            ? $request->input('path')
            : '';


        $this->path = $request->has('path') ?
            app(Directory::class)->path($request->input('path')) :
            app(Directory::class)->path();
        $fm = new FileManagerModel();


        $path = explode('/', $this->path);
        $path = array_filter($path);
        $path = implode("/", $path);

        $disks = $fm::all();

        $diskSize = $this->total / count(config('file-manager.diskList'));

        $collect = collect([
            'general' => [
                'sizes' => $disks->groupBy('group')->map(fn($ext) => $ext->sum('size'))->toArray(),
                'total' => $disks->sum('size'),
                //'disk' => 5368700000
                'disk' => $this->total
            ]]);
        $disks_sizes = $disks->groupBy('disk')->map(fn($row) => [
            'sizes' => collect($row->groupBy('group'))->map(fn($ext) => $ext->sum('size'))->toArray(),
            'total' => $row->sum('size'),
//                'disk' => 5368700000
            'disk' => $diskSize
        ]);
        $disks_sizes = $collect->merge($disks_sizes)->toArray();

        $disks = collect(config('file-manager.diskList'))->map(function ($v) use ($disks_sizes, $diskSize) {
            if (!array_key_exists($v, $disks_sizes)) {
                return [
                    $v => [
                        'sizes' => [],
                        'total' => 0,
                        'disk' => $diskSize
                    ]
                ];
            }
        })->flatMap(fn($d) => $d)->merge($disks_sizes)->toArray();

        $this->originalPath = Str::contains($this->originalPath, tenant()->uuid)?
            $this->originalPath:
            tenant()->uuid .'/'. $this->originalPath;

        if($this->IsExternal($request, $fm)) {
            $this->originalPath = Str::replace(tenant()->uuid,'', $this->originalPath);
        }

        $this->acl = $acl;
        $this->request = $request;
        $this->request->merge([
            'disk' => $this->disk,
            'path' => $this->IsExternal($request, $fm) ? Str::replace(app(Directory::class)->path(), '', $path) : $path,
            'originalPath' => $this->originalPath,
            'sizes' => $disks,
            'size' => (int)$fm::sum('size'),
            'fm' => $fm,
        ]);
        $this->size = (int)$fm::sum('size');

    }


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    final public function handle($request, Closure $next): mixed
    {
        $arr = explode('/', $request->path());

        if (array_pop($arr) === "delete" && $request->method() === "POST") {
            return $next($request);
        }
        if (in_array($request->method(), ["PUT", "PATCH", "POST"])) {
            if ((int)$this->size >= (int)$this->total) {
                return response()->json(['message' => 'You have reached your disk limit.', 'status' => 422], 422);
            }
        }


        return $next($request);
    }

    /**
     * @param RequestValidator $request
     * @param FileManagerModel $fm
     * @return bool
     */
    public function IsExternal(
        Request $request,
        FileManagerModel $fm
    ): bool
    {
        if (!$request->input('path')) {
            $pathArray = explode('/', $request->input('path'));
            array_pop($pathArray);
            $path = implode('/', $pathArray);
            return optional($fm::where('path', $path)->first('external'))->first()->external ?? false;
        }
        return false;
    }
}
