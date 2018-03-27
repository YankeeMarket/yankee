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

						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
								<i class="fa fa-minus"></i></button>
							<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
								<i class="fa fa-times"></i></button>
						</div>
					</div>
					<div class="box-body">
                        @if($order_labels)
                            <h3>Order Labels</h3>
                            <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>PL Number</th>
                                        <th>Date</th>
                                        <th>Link</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($order_labels as $label)
                                        <tr>
                                            <td>{{$label->filename}}</td>
                                            <td>{{optional($label->created_at)->format('Y-M-D')}}</td>
                                            <td><a href={{url("label/".$label->filename) }}><i class='fa fa-file-pdf'></i> View</a></td>
                                            <td><a href={{url("delete/".$label->filename) }}><i class='fa fa-trash-alt'></i> Delete</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            There are no order labels in the system
                        @endif

					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->

			</div>
		</div>
	</div>
@endsection
