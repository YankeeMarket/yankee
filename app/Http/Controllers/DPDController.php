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
use Carbon\Carbon;
use OAuth\Common\Http\Uri\Uri;
use Notification;
use App\Notifications\OrderCreated;

/**
 * Class BigCommerceController
 * @package App\Http\Controllers
 */
class DPDController extends Controller
{

    private $base_url = '';
    private $username = '';
    private $password = '';

    protected $storage;
	private $httpClient;

    public function init()
    {
        $this->base_url = getenv('DPD_BASE', 'https://lt.integration.dpd.eo.pl');
        $this->username = getenv('DPD_USERNAME', 'testuser1');
        $this->password = getenv('DPD_PASSWORD', 'testpassword1');
        $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
        $currentUri = $uriFactory->createFromAbsolute("http://localhost");
        $currentUri->setQuery('');
        $httpClient = new \OAuth\Common\Http\Client\CurlClient();

        $httpClient->setCurlParameters([CURLOPT_HEADER=>true]);
        $httpClient->setCurlParameters([CURLOPT_SSL_VERIFYPEER=>false]);
        $httpClient->setTimeout(60);

		$this->httpClient = $httpClient;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->init();
    }

    private function post($url, $arguments)
    {
        $parameters = array_merge(
            $arguments,
            array(
                'username' => $this->username,
                'password' => $this->password
            )
        );
        $content_type = "application/x-www-form-urlencoded";
        $uri = new Uri($url);
        foreach ($parameters as $key => $val)
        {
            $uri->addToQuery($key, $val);
        }
        Log::debug($uri->getAbsoluteUri());
        $response = $this->httpClient->retrieveResponse($uri, '', [], 'POST');
        return $response;
    }

    public function get_sample_order()
    {
        $whc = new WebhookController();
        $order = $whc->retrieve(100); //known ID for testing
        return $order;
    }

    public function test_create() {
        $details = $this->get_sample_order();
        return $this->create_shipment($details);

        //this below was to skip getting a pl number from DPD
        //return $this->display_order_response(100, $details, '{"status":"ok","errlog":"","pl_number":["05809023217401"]}');
    }

    public function create(Request $request, $order_id)
    {
        $whc = new WebhookController();
        $order = $whc->retrieve($order_id);
        $label = \App\Label::where("order_id", $order_id)->first();
        if ($label) {
            $data['details'][$order_id] = $order;
            $data['order_id'] = $order_id;
            $data['pl_number'] = $label->filename;
            return view("parcel")->with($data);
        }
        return $this->create_shipment($order);
    }


    private function make_create_call($order, $order_id, $the_order)
    {
        $path = '/ws-mapper-rest/createShipment_';

        $shipping_address = $order['addresses']->first();

        $arguments = [];

        $arguments['name1'] = $shipping_address->first_name.' '.$shipping_address->last_name;
        $arguments['street'] = $shipping_address->street_1.' '.$shipping_address->street_2;
        $arguments['city'] = $shipping_address->city;
        $arguments['country'] = $shipping_address->country_iso2;
        $arguments['pcode'] = $shipping_address->zip;
        $arguments['num_of_parcel'] = 1;

        //iterate through items to get total weight
        $weight = 0;
        foreach ($order['products'] as $product) {
            $weight += $product->weight;
        }
        $arguments['weight'] = $weight;

        $arguments['parcel_type'] = env('PARCEL_TYPE');

        if ($shipping_address->phone || $shipping_address->email)
        {
            $arguments['predict'] = 'y';
            $arguments['phone'] = $shipping_address->phone;
            $arguments['email'] = $shipping_address->email;
        }
        else
        {
            $arguments['predict'] = 'n';
        }

        $tstamp = Carbon::now();

        $arguments['order_number'] = $order_id; //test is 100
        $arguments['parcel_number'] = $the_order.'-'.$tstamp->format('His');

        Log::debug($arguments);


        $response = $this->post($this->base_url.$path, $arguments);
        Log::debug($response);
        return $response;
    }

    public function create_shipment($order)
    {

        //pull information from the order object
        //Log::debug($order);
        $the_order = $order['order']->first();
        Log::debug($the_order);
        $order_id = rand().$the_order; //add randomness to the order number
        //$order_id = $the_order; //just use the store ID

        $label = \App\Label::where('order_id', $the_order)->first();
        if ($label)
        {
            $data['pl_number'] = $label->filename;
            return view('parcel')->with($data);
        }

        $response = $this->make_create_call($order, $order_id, $the_order);

        return $this->display_order_response($the_order, $order, $response);

    }

    public function display_order_response($the_order, $order, $response)
    {
        Log::debug($response);
        $data['details'][$the_order] = $order;
        $data['order_id'] = $the_order;
        $json = json_decode($response, true);
        if (is_array($json) &&
            array_key_exists('status', $json))
        {
            if ($json['status'] == 'ok')
            {
                $pl = $json['pl_number'][0];
                $data['pl_number'] = $pl;
                Log::debug($pl);
                return view('parcel')->with($data);
            }
            else if ($json['status'] == 'err')
            {
                $data['error'] = $json['errlog'];
                return view('error')->with($data);
            }
        }
        /*
        else if (substr($response, 0,21) == '<!DOCTYPE HTML PUBLIC')
        {
            echo $response;
        } */
        Log::debug("Returning raw response");
        $data['error'] = $response;
        return view('error')->with($data);
    }

    public function label_from_order(Request $request, $order_id, $pl_number)
    {
        $label = \App\Label::where("filename", $pl_number)->first();
        if ($label && $label->filename) {
            Log::debug($label->filename);
            $file = base64_decode(stream_get_contents($label->file));
            if (strlen($file) > 0)
            {
                return $this->display_pdf($file, $pl_number);
            }
        }
        try {
            $this->get_label($pl_number, $order_id);
        } catch (Exception $e) {
            $data['label_id'] = $pl_number;
            $data['error'] = 'This label is not in the database.';
            return view('error')->with($data);
        }
    }

    private function request_label($pl_number)
    {
        $label_path = '/ws-mapper-rest/parcelPrint_';
        $arguments = [];
        $arguments['printType'] = 'pdf';
        $arguments['printFormat'] = 'A4';
        $arguments['parcels'] = $pl_number;

        $response = $this->post($this->base_url.$label_path, $arguments);
        return $response;
    }

    public function get_label($pl_number, $order_id) {

        $response = $this->request_label($pl_number);

        //Log::debug($response);

        if (json_decode($response, true))
        {
            $data['label_id'] = $pl_number;
            $data['error'] = $response;
            return view('error')->with($data);
        }
        $start = substr($response, 0,21);
        if ($start == '<!DOCTYPE HTML PUBLIC')
        {
            echo $response; //error message formatted as html
        }
        else
        {
            $this->store_pdf($response, 'order', $pl_number, "Label $pl_number", $order_id);
            $this->display_pdf($response, $pl_number);
        }
    }

    public function test_close()
    {
        return $this->close_manifest();
    }

    public function cli_close()
    {
        $response = $this->make_close_manifest_call();
        $start = substr($response, 0,21);
        if ($start == '<!DOCTYPE HTML PUBLIC')
        {
            echo "Unable to close manifest - see logs.";
        }
        else if (json_decode($response, true))
        {
            echo "Unable to close manifest.".PHP_EOL;
            echo $response.PHP_EOL;
        }
        else
        {
            $this->store_pdf($response, 'close', 'Manifest_'.$date, "Close Manifest Label for $date", 0);
            echo "Close PDF stored in database as Manifest_$date";
        }
    }

    private function make_close_manifest_call()
    {
        $path = '/ws-mapper-rest/parcelManifestPrint_';
        $date = Carbon::now('Europe/Vilnius')->format('Y-m-d');
        $arguments['date'] = $date;
        $response = $this->post($this->base_url.$path, $arguments);
        Log::debug($response);

        return $response;
    }

    /*
    *   Close the manifest and display the result via web
    */
    public function close_manifest()
    {
        $response = $this->make_close_manifest_call();
        $date = Carbon::now('Europe/Vilnius')->format('Y-m-d');
        if (json_decode($response, true)) {
            $data['error'] = $response;
            $data['date'] = $date;
            return view('close_error')->with($data);
        }
       $this->display_pdf($response, "Manifest_$date");
    }

    public function label_display(Request $request, $filename)
    {
        $label = \App\Label::where('filename', $filename)->first();
        if ($label)
        {
            $file = base64_decode(stream_get_contents($label->file));
            return $this->display_pdf($file, $filename);
        } else {
            $data['error'] = 'No Such Label in the system.';
            $data['order_labels'] = \App\Label::where('type', 'order')->get();
            return view('labels')->with($data);
        }
    }

    public function labels()
    {
        $whc = new WebhookController();
        $data['order_labels'] = \App\Label::where('type', 'order')->get();
        foreach($data['order_labels'] as $label)
        {
            $data['status'][$label->order_id] = $whc->get_status($label->order_id);
        }
        return view('labels')->with($data);
    }

    function display_pdf($response, $filename)
    {
        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=$filename");
        echo $response;
    }

    function store_pdf($response, $type, $filename, $description, $order_id)
    {
        $label = \App\Label::where("order_id", $order_id)->first();
        if (!$label)
        {
            $label = new \App\Label();
        }
        $label->description = $description;
        $label->filename = $filename;
        $label->type = $type;
        $label->file = base64_encode($response);
        $label->order_id = $order_id;
        $label->save();

    }

    public function view_order(Request $request, $order_id)
    {
        $order_detail = $this->retrieve($order_id);
        $data['details'][$order_id] = $order_detail;
        $data['order'] = $order_detail['order'];
        return view('order')->with($data);
    }

    public function initiate_order($data)
    {
        //$data comes from WebhookController->retrieve

        //$data['order'] = $this->get("orders/$order_id");
        //$data['products'] = $this->get("orders/$order_id/products");
        //$data['addresses'] = $this->get("orders/$order_id/shippingaddresses");

        //we want to create a parcel here

        $the_order = $data['order']->first();
        $order_id = rand().$the_order;
        $label = \App\Label::where("order_id", $the_order)->first();
        if ($label) {
            //we already have a label for this order (test or error)
            $data['pl_number'] = $label->filename;
        } else {
            $response = $this->make_create_call($data, $order_id, $the_order);
            $json = json_decode($response, true);
            if (is_array($json) &&
                array_key_exists('status', $json) &&
                $json['status'] == 'ok')
            {
                $pl = $json['pl_number'][0];
                $data['pl_number'] = $pl;
            }
            if (array_key_exists('pl_number', $data))
            {
                $labelresponse = $this->request_label($data['pl_number']);

                //Log::debug($response);

                if (json_decode($response, true))
                {
                    $data['error'] = $response;
                    //we have to report the error somehow
                }
                $start = substr($response, 0,21);
                if ($start == '<!DOCTYPE HTML PUBLIC')
                {
                    //hopefully this happens less now that we're in production
                    //we have to report this problem as well (Proxy Error or whatever)
                    //echo $response; //error message formatted as html
                }
                else
                {
                    $this->store_pdf($response, 'order', $pl_number, 'Label '.$data['pl_number'], $the_order);

                }
            }

        }
        if (array_key_exists('pl_number', $data))
        {

            //now we set up the email and provide the good news
            //$this->display_pdf($response, $pl_number);
            $users = \App\User::all(); //only admins log in here
            Log::debug("Users will now be sent an email");
            foreach ($users as $user)
            {
                Log::debug($user->email);
            }
            Notification::send($users, new OrderCreated($the_order, $data['pl_number']));
        }

    }

    public function remove_label($pl_number)
    {
        $label = \App\Label::where("filename", $pl_number)->first();
        if ($label)
        {
            $label->delete(); //that's it, the database space is freed
        }
    }

    public function delete_label(Request $request, $pl_number)
    {
        $this->remove_label($pl_number);
        return $this->labels();
    }

    public function find_old_labels()
    {
        $month_ago = Carbon::now()->subMonth();
        Log::debug("going to delete labels from before ".$month_ago);
        //echo "going to delete labels from before ".$month_ago.PHP_EOL;
        $labels = \App\Label::where([
                ["type", "order"],
                ["updated_at", '<=', $month_ago]
            ])->get();
        foreach($labels as $label)
        {
            //echo ("Would delete Label ".$label->id." with pl_number ".$label->filename.PHP_EOL);
            Log::debug("Would delete Label ".$label->id." with pl_number ".$label->filename);
            $this->remove_label($label->filename);
        }

    }

    public function cull_labels()
    {
        $this->find_old_labels();
        return $this->labels();
    }

}
