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
						<h3 class="box-title">BigCommerce Connection: {{$time}}</h3>
					</div>
                    @if(Auth::user->is_admin)
    					<div class="box-body">
    						<ul>
    							@foreach($products as $product)
    								<li>PRODUCT: {{$product->name}}
    								{{$product->weight}} kg<br>
    								{!!$product->description !!}
    								</li>
    							@endforeach
    						</ul>
    					</div>
    					<!-- /.box-body -->
                    @endif
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection
