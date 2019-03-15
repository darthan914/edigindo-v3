@extends('backend.layout.master')

@section('title')
	Edit Target
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
				url: "{{ route('backend.target.datatablesDetail', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'sales_id'},
				{data: 'value'},

				{data: 'less_target'},
				{data: 'reach_target'},
				{data: 'action', orderable: false, searchable: false},
			],
			scrollY: "400px",
			dom:"<l<t>ip>",
		});

		$('#datatable-detail').on('click', '.edit-detail', function(){
			$('#edit-detail *[name=id]').val($(this).data('id'));
			
			$('#edit-detail *[name=sales_id]').val($(this).data('sales_id'));
			$('#edit-detail *[name=value]').val($(this).data('value'));
			$('#edit-detail *[name=less_target]').val($(this).data('less_target'));
			$('#edit-detail *[name=reach_target]').val($(this).data('reach_target'));

		});

		$('#datatable-detail').on('click', '.delete-detail', function(){
			$('#delete-detail *[name=id]').val($(this).data('id'));
		});

		@if(Session::has('create-detail-error'))
		$('#create-detail').modal('show');
		@endif
		@if(Session::has('edit-detail-error'))
		$('#edit-detail').modal('show');
		@endif
	});
</script>

@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	td.details-control {
	    cursor: pointer;
	}
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')

	<h1>Edit Target</h1>

	{{-- Create Detail --}}
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.target.storeDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Detail</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select multiple class="form-control update-config select2full" name="sales_id[]">
									@foreach ($sales as $list)
									<option value="{{ $list->id }}" @if(in_array($list->id, old('sales_id', []))) selected @endif>{{ $list->fullname }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="less_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Not Reach Target
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="less_target" name="less_target" class="form-control {{$errors->first('less_target') != '' ? 'parsley-error' : ''}}">{{ old('less_target') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('less_target') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="reach_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Reach Target
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="reach_target" name="reach_target" class="form-control {{$errors->first('reach_target') != '' ? 'parsley-error' : ''}}">{{ old('reach_target') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('reach_target') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="target_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Edit Detail --}}
	<div id="edit-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.target.updateDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Update Detail</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="less_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Not Reach Target
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="less_target" name="less_target" class="form-control {{$errors->first('less_target') != '' ? 'parsley-error' : ''}}">{{ old('less_target') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('less_target') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="reach_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Reach Target
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="reach_target" name="reach_target" class="form-control {{$errors->first('reach_target') != '' ? 'parsley-error' : ''}}">{{ old('reach_target') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('reach_target') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="">
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Delete Detail --}}
	<div id="delete-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.target.deleteDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.target.update', $index) }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="year" class="control-label col-md-3 col-sm-3 col-xs-12">Year <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="year" name="year" class="form-control {{$errors->first('year') != '' ? 'parsley-error' : ''}}" value="{{ old('year', $index->year) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('year') }}</li>
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

		<div class="form-group">
			<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note', $index->note) }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.target') }}">Back</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

	<div class="x_panel">
		<div class="x_title">

			<h2>Detail</h2>
			<ul class="nav panel_toolbox">
				<form method="post" id="action-detail" action="{{ route('backend.target.actionDetail') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-detail">Create</button>
					<select class="form-control" name="action">
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success update-status">Apply Selected</button>
				</form>
	        </ul>
	        <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
			<table class="table table-striped table-bordered" id="datatable-detail">
				<thead>
					<tr>
						<th nowrap>
							<label class="checkbox-inline"><input type="checkbox" data-target="check-detail" class="check-all" id="check-all">S</label>
						</th>
						<th>Name</th>
						<th>Value</th>

						<th>Note Less Target</th>
						<th>Note Reach Target</th>
						<th>Action</th>
					</tr>
				</thead>
			</table>

		</div>
	</div>

@endsection