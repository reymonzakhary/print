<?php

namespace Modules\Ecommerce\Http\Controllers\Mgr\Categories;

use App\Cart\Contracts\CartContractInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Ecommerce\Entities\Category;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\Variation;
use Modules\Ecommerce\Transformers\Categories\CategoryResource;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(CartContractInterface $cart)
    {
//        $cart->add(Variation::find(6), 1);
        $product = Product::where('category_id', 1)->first();
        return Variation::where('product_id', $product->id)->orderBy('sort')->tree()->get()->toTree();
        dd($product->variations->sortBy('sort')->groupBy('box')->tree()->get()->toTree());
        return CategoryResource::collection(
            Category::tree()
                ->where('iso', app()->getLocale())
                ->orderBy(request()->order_by ?? 'id', request()->order_dir ?? 'asc')
                ->get()->toTree()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('ecommerce::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('ecommerce::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('ecommerce::edit');
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
}
