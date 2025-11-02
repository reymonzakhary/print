<?php

namespace App\Http\Controllers\Tenant\Mgr\Namespaces;

use App\Http\Controllers\Controller;
use App\Models\Tenants\Npace;
use Illuminate\Http\Request;

/**
 * @group Tenant Settings
 * 
 * @subgroup Tenant Name spaces
 */
class NamespaceController extends Controller
{
    /**
     * List of namespaces
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     *      {
	 *      	"id": 1,
	 *      	"name": "core",
	 *      	"slug": "core",
	 *      	"sort": 1,
	 *      	"icon": null,
	 *      	"created_at": "2024-05-01T11:39:44.000000Z",
	 *      	"updated_at": "2024-05-01T11:39:44.000000Z",
	 *      	"disabled": false
	 *      },
     * }
     * 
     */
    public function index()
    {
        return Npace::get();
    }

    /**
     * Store NameSpace
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @bodyParam name string required name of namespace
     * @bodyParam icon string icon of name space 
     * 
     * @response 201 
     * {
     *  "id": 1,
	 *  "name": "core",
	 *  "slug": "core",
	 *  "sort": 1,
	 *  "icon": null,
	 *  "created_at": "2024-05-01T11:39:44.000000Z",
	 *  "updated_at": "2024-05-01T11:39:44.000000Z",
	 *  "disabled": false
     * }
     * 
     */
    public function store(Request $request)
    {
        return Npace::create([
            'name' => $request->name,
            'icon' => $request->icon
        ]);
    }
}
