@extends('adminlte::layouts.app')

@section('htmlheader_title')
	Order Page
@endsection


@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">

                @if(Auth::user()->is_admin)
    				<!-- Default box -->
    				<div class="box box-solid box-danger">
    					<div class="box-header">
    						<h3 class="box-title">Yankee Market Order
                                @isset($order_id)
                                    {{$order_id}}
                                @endisset
                                @isset($label_id)
                                    Parcel #{{$label_id}}
                                @endisset
                            </h3>

    					</div>
    					<div class="box-body">
                            ERROR: {!! $error !!}
    					</div>
    					<!-- /.box-body -->
    				</div>
    				<!-- /.box -->
                @endif
			</div>
		</div>
	</div>
@endsection
