@extends('adminlte::layouts.app')

@section('htmlheader_title')
	Order Page
@endsection


@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">

				<!-- Default box -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Yankee Market Order {{$order->id}}</h3>
					</div>
                    @if(Auth::user->is_admin)
                        <div class="box-body">
                                <a href="{{ url('/create/'.$order->id)}}">Create or view shipping label</a><br>
                                ORDER: {{$order->id}}
                                <br>
                                Customer: {{$order->customer_id}}
                                <br>
                                Status: {{$order->status}}
                                <br>
                                Total Items: {{$order->items_total}}
                                <br>
                                From Country: {{$order->geoip_country}}
                                <br>
                                Total: € {{number_format($order->total_inc_tax, 2)}}
                                <ul>
                                    @foreach($details[$order->id]['products'] as $product)
                                        <li>
                                            {{$product->name}} (ID {{$product->id}})<br>
                                            {{$product->weight}} kg<br>
                                            Item cost: €{{number_format($product->total_inc_tax, 2)}}
                                        </li>
                                    @endforeach
                                </ul>
                                Ship to: <br>
                                {{$details[$order->id]['addresses']->first()->street_1}}<br>
                                {{$details[$order->id]['addresses']->first()->street_2}}<br>
                                {{$details[$order->id]['addresses']->first()->city}}<br>
                                {{$details[$order->id]['addresses']->first()->zip}}<br>
                                {{$details[$order->id]['addresses']->first()->country}}<br>
                                {{$details[$order->id]['addresses']->first()->country_iso2}}<br>
                                Shipping: {{$details[$order->id]['addresses']->first()->shipping_method}}<br>
                                Shipping cost: €{{number_format($details[$order->id]['addresses']->first()->cost_inc_tax, 2)}}<br>

    					</div>
    					<!-- /.box-body -->
                    @endif
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection
