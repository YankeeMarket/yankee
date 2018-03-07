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
						<h3 class="box-title">Yankee Market Order {{$order_id}}</h3>

						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
								<i class="fa fa-minus"></i></button>
							<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
								<i class="fa fa-times"></i></button>
						</div>
					</div>
					<div class="box-body">
						<?php
						$order = $details[$order_id]['order'];
						?>
                        PARCEL NUMBER: {{$pl_number}}
                        <br>
                        <a href='{{url("/label/$pl_number")}}'>Get Shipping Label</a><br>
                        ORDER: {{$order_id}}
                        <br>
                        Customer: {{$order['customer_id']}}
                        <br>
                        Status: {{$order['status_id']}}
                        <br>
                        Total Items: {{$order['items_total']}}
                        <br>
                        From Country: {{$order['geoip_country']}}
                        <br>
                        <ul>
                        @foreach($details[$order_id]['products'] as $product)
                        <li>
                            {{$product->name}} :
                            {{$product->weight}} kg<br>
                        </li>
                        @endforeach
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection
