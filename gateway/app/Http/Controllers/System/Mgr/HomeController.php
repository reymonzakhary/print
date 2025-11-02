<?php

namespace App\Http\Controllers\System\Mgr;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return Inertia::render('Dashboard', []);
    }
}
