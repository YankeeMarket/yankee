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
class WebhookController extends Controller
{

    private $hash = '';
    private $token = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->hash = env('BC_STORE_HASH');
        $this->token = env('BC_ACCESS_TOKEN');
        Bigcommerce::setAPIVersion('v2');
    }

    private function get($resource)
    {
        Log::debug("Getting $resource");
        return Bigcommerce::setStoreHash($this->hash)->setAccessToken($this->token)->get($resource);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        Log::debug("Going to connect to BigCommerce");
        $orders = $this->get('orders');
        Log::debug($orders);
        $data['orders'] = $orders;
        foreach ($orders as $order) {
            $order_detail = $this->retrieve($order->id);
            Log::debug($order_detail);
            $data['details'][$order->id] = $order_detail;
        }
        Log::debug($data);
        return view('orders')->with($data);
    }

    public function orderCreated(Request $request)
    {

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $webhookContent = file_get_contents("php://input");
            $result         = json_decode($webhookContent, true);            
            $store_id = $result['store_id'];
            $producer = $result['producer'];
            $scope =    $result['scope']; //should be "store/order/created"
            $type =     $result['data']['type']; //should be "order"
            $order_id = $result['data']['id'];  //should be numeric

            //look up order by id
            $data = $this->retrieve($order_id);
            $dpd = new DPDController();
            $dpd->initiate_order($data);
        }
        
    }

    public function retrieve($order_id)
    {
        $data['order'] = $this->get("orders/$order_id");
        $data['products'] = $this->get("orders/$order_id/products");
        $data['addresses'] = $this->get("orders/$order_id/shippingaddresses");
        return $data;
    }
   
}