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
        //$this->middleware('auth');
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
     public function view_all_orders(Request $request)
     {
         $orders = $this->get('orders');
         //Log::debug($orders);
         $data['orders'] = $orders;
         foreach ($orders as $order) {
             $order_detail = $this->retrieve($order->id);
             //Log::debug($order_detail);
             $data['details'][$order->id] = $order_detail;
         }
         //Log::debug($data);
         return view('all_orders')->with($data);
     }

     public function view_non_cancelled_orders(Request $request)
     {
         $orders = $this->get('orders');
         //Log::debug($orders);
         $data = [];
         foreach ($orders as $order) {
             if ($order->status == 'Cancelled') {
                 continue;
             }
             $data['orders'][] = $order;
             $order_detail = $this->retrieve($order->id);
             //Log::debug($order_detail);
             $data['details'][$order->id] = $order_detail;
         }
         //Log::debug($data);
         return view('orders')->with($data);
     }


    public function orderCreated(Request $request)
    {

        //security check
        $headers = array();
        if (function_exists('apache_request_headers'))
        {
            $headers = apache_request_headers();
        }
        else
        {
            foreach ($_SERVER as $name => $value)
            {
                if(substr($name,0,5)=='HTTP_')
                {
                    $name=substr($name,5);
                    $name=str_replace('_',' ',$name);
                    $name=strtolower($name);
                    $name=ucwords($name);
                    $name=str_replace(' ', '-', $name);

                    $headers[$name] = $value;
                }
            }
        }

        Log::debug($headers);
        if (!array_key_exists('Secretkey', $headers))
        {
            Log::debug("Secret key header not present");
            return response('', 403);
        } else if ($headers['Secretkey'] != getenv("SECRET_HEADER"))
        {
            Log::debug("Secret key header value not correct");
            Log::debug("Value: ".$headers['Secretkey']);
            Log::debug("Should be ".getenv("SECRET_HEADER"));
            return response('', 403);
        }
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $webhookContent = file_get_contents("php://input");
            $result         = json_decode($webhookContent, true);
            Log::debug($result);

            $store_id = $result['store_id'];
            $producer = $result['producer'];
            $scope =    $result['scope']; //should be "store/order/created"
            $type =     $result['data']['type']; //should be "order"
            $order_id = $result['data']['id'];  //should be numeric

            //look up order by id
            $data = $this->retrieve($order_id);
            $dpd = new DPDController();
            $dpd->initiate_order($data);
            return response($order_id, 200);
        }

    }

    public function retrieve($order_id)
    {
        $data['order'] = $this->get("orders/$order_id");
        $customer_id = $data['order']['customer_id'];
        $data['customer'] = $this->get("customers/$customer_id");
        $data['products'] = $this->get("orders/$order_id/products");
        $data['addresses'] = $this->get("orders/$order_id/shippingaddresses");
        return $data;
    }

    public function get_status($order_id)
    {
        $order = $this->get("orders/$order_id");
        if ($order)
        {
            return $order['status'];
        } else {
            return null;
        }
    }

}
