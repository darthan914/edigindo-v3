@extends('backend.layout.master')

@section('title')
	History - {{ $offer->no_document }}
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {

		$(".check-all").click(function(){
			if ($(this).is(':checked'))
			{
				$('.' + $(this).attr('data-target')).prop('checked', true);
			}
			else
			{
				$('.' + $(this).attr('data-target')).prop('checked', false);
			}
		});
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	.list
	{
		height: 195px;
		overflow: auto;
	}
</style>
@endsection

@section('content')

	<h1>History - {{ $offer->no_document }}</h1>
	

	<div class="x_panel">

		<div class="row">
			@foreach($index as $list)
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
					<div class="x_title">
						<div class="row">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
								<h2>
									{{ date('d/m/y H:i:s', strtotime($list->created_at)) }} - {{ $list->old_data->name ?? '' }} {{ ($count > 1 ? '[Rev'.--$count.']' : '[Init]') }}
								</h2>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right">
								<button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#list-{{ $list->id }}"><i class="fa fa-eye"></i></button>
							</div>
						</div>
						
						
						<div class="clearfix"></div>
					</div>
					<div class="x_content list collapse" id="list-{{ $list->id }}">
						<table class="table">
							<tr>
								<th width="20%">Quantity</th>
								<td width="80%">{{ $list->old_data->quantity ?? 0 }} {{ $list->old_data->unit ?? ''}}</td>
							</tr>
							<tr>
								<th width="20%">Price</th>
								<td width="80%">
									{{ number_format($list->old_data->value ?? 0) }}
								</td>
							</tr>
							<tr>
								<th width="20%">Detail</th>
								<td width="80%">
									{!! $list->old_data->detail !!}
								</td>
							</tr>

						</table>
					</div>
				</div>
			</div>
			@endforeach
		</div>

	</div>

@endsection