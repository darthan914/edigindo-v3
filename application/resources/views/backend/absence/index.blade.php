@extends('backend.layout.master')

@section('title')
	Form Absence List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {

		$('select[name=f_user]').select2();


		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.absence.datatables') }}",
				type: "POST",
				data: {
					f_id    : getUrlParameter('f_id'),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	f_user  : $('*[name=f_user]').val(),

			    	f_start_range : $('*[name=f_start_range]').val(),
			    	f_end_range   : $('*[name=f_end_range]').val(),
			    	f_check       : $('*[name=f_check]').val(),
			    	f_confirm     : $('*[name=f_confirm]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{data: 'fullname', name: 'users.fullname', sClass: 'nowrap-cell'},
				{data: 'created_at', sClass: 'nowrap-cell'},

				{data: 'date_absence', sClass: 'nowrap-cell'},
				{data: 'time_check_in', sClass: 'nowrap-cell'},
				{data: 'time_check_out', sClass: 'nowrap-cell'},

				{data: 'note', sClass: ''},

				{data: 'confirm', sClass: 'nowrap-cell'},

				{data: 'check_hrd', sClass: 'nowrap-cell'},

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
			pageLength: 100,
		});


		$('#datatable').on('click', '.edit-absence', function(){

			$('#edit-absence input[name=id]').val($(this).data('id'));
			$('#edit-absence input[name=date_absence]').val($(this).data('date_absence'));
			$('#edit-absence input[name=time_check_in]').val($(this).data('time_check_in'));
			$('#edit-absence input[name=time_check_out]').val($(this).data('time_check_out'));
			$('#edit-absence textarea[name=note]').val($(this).data('note'));
		});

		$('#datatable').on('click', '.delete-absence', function(){
			$('#delete-absence input[name=id]').val($(this).data('id'));
		});


		$('#datatable').on('click', '.confirm-absence', function(){
			$('#confirm-absence input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.unconfirm-absence', function(){
			$('#unconfirm-absence input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('create-absence-error'))
		$('#create-absence').modal('show');
		@endif

		@if(Session::has('edit-absence-error'))
		$('#edit-absence').modal('show');
		@endif

		@can('checkHRD-absence')
		$('#datatable').on('change', 'input[name=check_hrd]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.absence.checkHRD') }}', {
				id: $(this).data('id'),
				check_hrd: setVal,
			}, function(data) {
			});
		});
		@endcan
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

	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	@can('create-absence')
	{{-- Create Form Absence List --}}
	<div id="create-absence" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.absence.store') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Form Absence List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-date_absence">
							<label for="date_absence" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="date" id="date" name="date_absence" max="{{ date('Y-m-d') }}" class="form-control {{$errors->first('date_absence') != '' ? 'parsley-error' : ''}}" value="{{ old('date_absence') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_absence') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-time_check_in">
							<label for="time_check_in" class="control-label col-md-3 col-sm-3 col-xs-12">Check In <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="time" id="time_check_in" name="time_check_in" class="form-control {{$errors->first('time_check_in') != '' ? 'parsley-error' : ''}}" value="{{ old('time_check_in') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_check_in') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-time_check_out">
							<label for="time_check_out" class="control-label col-md-3 col-sm-3 col-xs-12">Check Out <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="time" id="time_check_out" name="time_check_out" class="form-control {{$errors->first('time_check_out') != '' ? 'parsley-error' : ''}}" value="{{ old('time_check_out') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_check_out') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-note">
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
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('edit-absence')
	{{-- Edit Form Absence List --}}
	<div id="edit-absence" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.absence.update') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Form Absence List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-date_absence">
							<label for="date_absence" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="date" id="date" name="date_absence" max="{{ date('Y-m-d') }}" class="form-control {{$errors->first('date_absence') != '' ? 'parsley-error' : ''}}" value="{{ old('date_absence') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_absence') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-time_check_in">
							<label for="time_check_in" class="control-label col-md-3 col-sm-3 col-xs-12">Check In <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="time" id="time_check_in" name="time_check_in" class="form-control {{$errors->first('time_check_in') != '' ? 'parsley-error' : ''}}" value="{{ old('time_check_in') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_check_in') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-time_check_out">
							<label for="time_check_out" class="control-label col-md-3 col-sm-3 col-xs-12">Check Out <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="time" id="time_check_out" name="time_check_out" class="form-control {{$errors->first('time_check_out') != '' ? 'parsley-error' : ''}}" value="{{ old('time_check_out') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_check_out') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-note">
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
						<input type="hidden" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Update</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-absence')
	{{-- Delete Form Absence List --}}
	<div id="delete-absence" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.absence.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Form Absence List?</h4>
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
	@endcan

	@can('confirm-absence')
	{{-- Confirm Form Absence List --}}
	<div id="confirm-absence" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.absence.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm Form Absence List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Confirm</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Unconfirm Form Absence List --}}
	<div id="unconfirm-absence" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.absence.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Unconfirm Form Absence List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-dark">Unconfirm</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan
	<h1>Form Absence List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">My Form Absence</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allUser-absence')
						<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All User</option>
						@endif
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<br/>
					<input type="date" name="f_start_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_start_range }}">
					<input type="date" name="f_end_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_end_range }}">
					
					<select name="f_check" class="form-control" onchange="this.form.submit()">
						<option value="" {{ $request->f_check === '' ? 'selected' : '' }}>All Status Checked</option>
						<option value="1" {{ $request->f_check === '1' ? 'selected' : '' }}>Checked</option>
						<option value="0" {{ $request->f_check === '0' ? 'selected' : '' }}>Uncheck</option>
					</select>

					<select name="f_confirm" class="form-control" onchange="this.form.submit()">
						<option value="" {{ $request->f_confirm === '' ? 'selected' : '' }}>All Status Confirm</option>
						<option value="1" {{ $request->f_confirm === '1' ? 'selected' : '' }}>Confirm</option>
						<option value="0" {{ $request->f_confirm === '0' ? 'selected' : '' }}>Unconfirm</option>
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.absence.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-absence')
					<button type="button" class="btn btn-default create-absence" data-toggle="modal" data-target="#create-absence">Create</button>
					@endif
					<select class="form-control" name="action">
						<option value="confirm">Confirm</option>
						<option value="unconfirm">Deconfirm</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>Name</th>
					<th>Created At</th>

					<th>Date</th>
					<th>Check In</th>
					<th>Check Out</th>

					<th>Note</th>

					<th>Confirm</th>
					<th>Check HRD</th>

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

					<td></td>
					<td></td>

					<td></td>
					
					<td></td>
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection