@extends('adminlte::layouts.app')

@section('htmlheader_title')
	Order Page
@endsection


@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">

                @isset($error)
                <div class="box box-solid box-danger">
					<div class="box-header">
						<h3 class="box-title">Error</h3>
					</div>
                    <div class="box-body">
                        {{$error}}
                    </div>
                </div>
                @endisset

				<!-- Default box -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Yankee Market Order Labels</h3>
					</div>
                    @if(Auth::user->is_admin)
    					<div class="box-body">
                            @if($order_labels)
                                <h3>Order Labels</h3>
                                <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Order</th>
                                            <th>Label</th>
                                            <th>Order Status</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($order_labels as $label)
                                            <tr>
                                                <td>{{optional($label->created_at)->format('Y-m-d')}}</td>
                                                <td>
                                                    <a href='{{url("create/".$label->order_id) }}'>
                                                        <i class='fa fa-shopping-cart'></i> <span>Order {{$label->order_id}}</span>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href='{{url("label/".$label->order_id."/".$label->filename) }}'>
                                                        <i class='fa fa-file-pdf-o'></i> <span>{{$label->filename}}</span>
                                                    </a>
                                                </td>
                                                <td>
                                                    @if (isset($status) && array_key_exists($label->order_id, $status))
                                                        {{$status[$label->order_id]}}
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href='{{url("delete/".$label->filename) }}'>
                                                        <span class="text-danger"><i class='fa fa-trash'></i> Delete</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                There are no order labels in the local database
                            @endif

                            <hr>
                            <h5><a href='{{ url("/cull") }}'><span class="text-danger"><i class='fa fa-calendar'></i> Delete Labels Created before {{Carbon\Carbon::now()->subMonth()->format('Y-m-d')}}</span></a></h5>

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
    $('#datatable').DataTable();
});
</script>
@endsection
