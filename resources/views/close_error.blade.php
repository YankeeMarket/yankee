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
						<h3 class="box-title">Yankee Market Close Manifest Error {{$date}}</h3>

					</div>
					<div class="box-body">
                        ERROR: {{$error}};
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection
