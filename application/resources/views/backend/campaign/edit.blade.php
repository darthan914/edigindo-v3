@extends('backend.layout.master')

@section('title')
	Edit Campaign
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable-detail').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.campaign.datatablesCampaignDetail', ['index' => $index->id]) }}",
				type: "POST",
				data: {
				},
			},
			columns: [

				{data: 'name'},
				{data: 'start_month'},
				{data: 'end_month'},
				{data: 'value', sClass: 'number-format'},
				{data: 'for_expo'},
				{data: 'action', orderable: false, searchable: false},
			],
			initComplete: function () {
				this.api().columns().every(function () {
					var column = this;
					var input = document.createElement("input");
					$(input).appendTo($(column.footer()).empty())
					.on('keyup', function () {
						column.search($(this).val(), false, false, true).draw();
					});
				});
			},
			scrollY: "400px",
	        
		});

		$('#datatable-detail').on('click', '.delete-campaignDetail', function(){
			$('#delete-campaignDetail *[name=id]').val($(this).data('id'));
		});
	});
</script>

@endsection

@section('content')

	<h1>Edit Campaign</h1>

	<div id="delete-campaignDetail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.campaign.deleteCampaignDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Campaign?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>


	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.campaign.update', ['index' => $index->id]) }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="year" class="control-label col-md-3 col-sm-3 col-xs-12">Year <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="year" name="year" class="form-control {{$errors->first('year') != '' ? 'parsley-error' : ''}}" value="{{ old('year', $index->year) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('year') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', $index->name) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>


		<div class="form-group">
			<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value', $index->value) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('value') }}</li>
				</ul>
			</div>
		</div>

		

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.campaign') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

	<div class="x_panel">
		<div class="x_title">

			<h2>Detail</h2>
			<ul class="nav panel_toolbox">

				<form method="get" id="action-detail" action="#" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					@can('create-campaign')
					<a href="{{ route('backend.campaign.createCampaignDetail', ['index' => $index->id]) }}" class="btn btn-default">Create</a>
					@endcan
				</form>

	        </ul>
	        <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
			<table class="table table-striped table-bordered" id="datatable-detail">
				<thead>
					<tr>

						<th>Name</th>
						<th>Start Month</th>
						<th>End Month</th>

						<th>Value</th>
						<th>Expo</th>
						<th>Action</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td></td>
						<td></td>

						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

@endsection