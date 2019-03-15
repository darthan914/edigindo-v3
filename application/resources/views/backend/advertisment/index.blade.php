@extends('backend.layout.master')

@section('title')
	Advertisment List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.advertisment.datatables') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'name', sClass: 'nowrap-cell'},
				{data: 'detail', sClass: 'nowrap-cell'},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
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
			// scrollX: true,
		});

		$('input[name=end_valid]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name=end_valid]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	    });

	    $('input[name=end_valid]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		$('#datatable').on('click', '.edit', function(){
			$('.advertisment_id-onedit').val($(this).data('id'));
			$('.name-onedit').val($(this).data('name'));
		});

		$('#datatable').on('click', '.delete', function(){
			$('.advertisment_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.create-detail', function(){
			$('.advertisment_id-oncreateDetail').val($(this).data('id'));
		});

		$('#datatable').on('click', '.edit-detail', function(){
			$('.advertisment_id-oneditDetail').val($(this).data('id'));
			$('.detail-oneditDetail').val($(this).data('detail'));
			$('.payment-oneditDetail').val($(this).data('payment'));
			$('.date_valid-oneditDetail').val($(this).data('date_valid'));
			$('.count-oneditDetail').val($(this).data('count'));
			$('.end_valid-oneditDetail').val($(this).data('end_valid'));
			$('.note-oneditDetail').val($(this).data('note'));
		});

		$('#datatable').on('click', '.delete-detail', function(){
			$('.advertisment_id-ondeleteDetail').val($(this).data('id'));
		});

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

	    @if(Session::has('create-error'))
		$('#create').modal('show');
		@endif
		@if(Session::has('edit-error'))
		$('#edit').modal('show');
		@endif

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
	.nowrap-cell{
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	@can('create-advertisment')
	{{-- Create Advertisment --}}
	<div id="create" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.store') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Advertisment</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('edit-advertisment')
	{{-- Edit Advertisment --}}
	<div id="edit" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.update') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Advertisment</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}} name-onedit" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="advertisment_id-onedit" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-advertisment')
	{{-- Delete Advertisment --}}
	<div id="delete" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Advertisment?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="advertisment_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan



	@can('create-advertisment')
	{{-- Create Detail --}}
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.storeDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Detail Detail</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="detail" class="control-label col-md-3 col-sm-3 col-xs-12">Detail <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="detail" name="detail" class="form-control {{$errors->first('detail') != '' ? 'parsley-error' : ''}}" value="{{ old('detail') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('detail') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="payment" class="control-label col-md-3 col-sm-3 col-xs-12">Payment <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="payment" name="payment" class="form-control {{$errors->first('payment') != '' ? 'parsley-error' : ''}}" value="{{ old('payment') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('payment') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="date_valid" class="control-label col-md-3 col-sm-3 col-xs-12">Date Valid <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="date_valid" name="date_valid" class="form-control {{$errors->first('date_valid') != '' ? 'parsley-error' : ''}}" value="{{ old('date_valid') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_valid') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="count" class="control-label col-md-3 col-sm-3 col-xs-12">Total Count <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="count" name="count" class="form-control {{$errors->first('count') != '' ? 'parsley-error' : ''}}" value="{{ old('count') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('count') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="end_valid" class="control-label col-md-3 col-sm-3 col-xs-12">End Valid <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="end_valid" name="end_valid" class="form-control {{$errors->first('end_valid') != '' ? 'parsley-error' : ''}}" value="{{ old('end_valid') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('end_valid') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="advertisment_id" class="advertisment_id-oncreateDetail" value="{{old('advertisment_id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('edit-advertisment')
	{{-- Edit Advertisment --}}
	<div id="edit-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.updateDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Advertisment Detail</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="detail" class="control-label col-md-3 col-sm-3 col-xs-12">Detail <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="detail" name="detail" class="form-control {{$errors->first('detail') != '' ? 'parsley-error' : ''}} detail-oneditDetail" value="{{ old('detail') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('detail') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="payment" class="control-label col-md-3 col-sm-3 col-xs-12">Payment <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="payment" name="payment" class="form-control {{$errors->first('payment') != '' ? 'parsley-error' : ''}} payment-oneditDetail" value="{{ old('payment') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('payment') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="date_valid" class="control-label col-md-3 col-sm-3 col-xs-12">Date Valid <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="date_valid" name="date_valid" class="form-control {{$errors->first('date_valid') != '' ? 'parsley-error' : ''}} date_valid-oneditDetail" value="{{ old('date_valid') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_valid') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="count" class="control-label col-md-3 col-sm-3 col-xs-12">Total Count <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="count" name="count" class="form-control {{$errors->first('count') != '' ? 'parsley-error' : ''}} count-oneditDetail" value="{{ old('count') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('count') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="end_valid" class="control-label col-md-3 col-sm-3 col-xs-12">End Valid <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="end_valid" name="end_valid" class="form-control {{$errors->first('end_valid') != '' ? 'parsley-error' : ''}} end_valid-oneditDetail" value="{{ old('end_valid') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('end_valid') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}} note-oneditDetail">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="advertisment_id-oneditDetail" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-advertisment')
	{{-- Delete Advertisment --}}
	<div id="delete-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.advertisment.deleteDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Advertisment Detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="advertisment_id-ondeleteDetail" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Advertisment List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.advertisment.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-advertisment')
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create">Create</button>
					@endif
					<select class="form-control" name="action">
						{{-- <option value="enable">Enable</option>
						<option value="disable">Disable</option> --}}
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Name</th>
					<th>Detail</th>

					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>

					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	

@endsection