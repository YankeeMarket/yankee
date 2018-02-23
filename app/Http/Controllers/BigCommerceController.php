<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Bigcommerce;
use Log;

/**
 * Class BigCommerceController
 * @package App\Http\Controllers
 */
class BigCommerceController extends Controller
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
     * @return Response
     */
    public function index(Request $request)
    {
        Bigcommerce::setAPIVersion('v2');
        Log::debug("Going to connect to BigCommerce");
        $storeHash = env('BC_STORE_HASH');
        $accessToken = env('BC_ACCESS_TOKEN');
        $products = Bigcommerce::setStoreHash($storeHash)->setAccessToken($accessToken)->get("products");
            //["limit" => 20, "page" => 1]);
        Log::debug($products);
        $time = Bigcommerce::getTime();
        if ($time) {
            $data['time'] = $time->format('H:i:s');
        } else {
            $data['time'] = 'Not a valid connection';
        }
        $data['products'] = $products;
        $data['product_list'] = json_encode($products);
        return view('products')->with($data);
    }
}