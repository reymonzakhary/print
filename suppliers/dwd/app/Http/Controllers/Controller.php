<?php

namespace App\Http\Controllers;

set_time_limit(-1);

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    /**
     * @var string $user_id
     * 
     * user_id to consume API
     */
    public $user_id;

    /**
     * @var string $api_secret
     * 
     * api_secret key to consume API
     */
    public $api_secret;
    
    /**
     * @var string $url
     * 
     * url to consume API
     */
    public $url;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->url = config('dwd.url');

        $this->user_id = config('dwd.user_id');

        $this->api_secret = config('dwd.api_secret');
    }
}
