@extends('adminlte::layouts.app')

@section('htmlheader_title')
	{{ trans('adminlte_lang::message.home') }}
@endsection


@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">

				<!-- Default box -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Yankee Market Orders</h3>
					</div>
                    @if(Auth::user()->is_admin)
    					<div class="box-body">
                            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Status</th>
                                        <th>Customer</th>
                                        <th>Ship to City</th>
                                        <th>Total Items</th>
                                        <th>Total Price</th>
                                        <th>Date Placed</th>
                                    </tr>
                                </thead>
                                <tbody>
    							    @foreach($orders as $order)
                                        <tr>
                                            <td><a href="{{ url('/create/'.$order->id) }}">{{$order->id}}</a></td>
                                            <td>{{$order->status}}</td>
                                            <td>{{$details[$order->id]['addresses']->first()->first_name}}
                                                {{$details[$order->id]['addresses']->first()->last_name}}</td>
                                            <td>{{$details[$order->id]['addresses']->first()->city}}</td>
                                            <td>{{$order->items_total}}</td>
                                            <td>€ {{number_format($order->total_inc_tax, 2)}}</td>
                                            <td>{{Carbon\Carbon::createFromFormat('D, d M Y H:i:s O', $order->date_created)->toDayDateTimeString()}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <hr>
                            <h5><a href="{{url('/orders') }}">View Only Non-Cancelled Orders</a></h5>
    					</div>
					    <!-- /.box-body -->
                    @endif
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection

@section('local_scripts')
<script type="text/javascript">
$(document).ready(function(){
    $('#datatable').DataTable(
        {
            "order": [[0, 'desc']]
        }
    );
});
</script>
@endsection
