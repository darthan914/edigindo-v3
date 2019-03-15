@extends('backend.layout.master')

@section('title')
	Leave List
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
				url: "{{ route('backend.dayoff.datatables') }}",
				type: "POST",
				data: {
					f_id    : getUrlParameter('f_id'),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_user  : $('*[name=f_user]').val(),

			    	f_start_range : $('*[name=f_start_range]').val(),
			    	f_end_range   : $('*[name=f_end_range]').val(),
			    	f_check       : $('*[name=f_check]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{data: 'fullname', sClass: 'nowrap-cell'},

				{data: 'date_dayoff', sClass: 'nowrap-cell'},
				{data: 'created_at', sClass: 'nowrap-cell'},
				{data: 'total_dayoff', sClass: 'nowrap-cell'},
				{data: 'leave_remains', sClass: 'nowrap-cell'},
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


		$('#datatable').on('click', '.edit-dayoff', function(){

			$('#edit-dayoff input[name=id]').val($(this).data('id'));
			$('#edit-dayoff input[name=date_dayoff]').val($(this).data('date_dayoff'));
			$('#edit-dayoff input[name=total_dayoff][value='+$(this).data('total_dayoff')+']').prop('checked', 'checked');
			$('#edit-dayoff input[name=type][value='+$(this).data('type')+']').prop('checked', 'checked');
			$('#edit-dayoff textarea[name=note]').val($(this).data('note'));
		});

		$('#datatable').on('click', '.delete-dayoff', function(){
			$('#delete-dayoff input[name=id]').val($(this).data('id'));
		});


		$('#datatable').on('click', '.confirm-dayoff', function(){
			$('#confirm-dayoff input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.unconfirm-dayoff', function(){
			$('#unconfirm-dayoff input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('create-dayoff-error'))
		$('#create-dayoff').modal('show');
		@endif

		@if(Session::has('edit-dayoff-error'))
		$('#edit-dayoff').modal('show');
		@endif

		@can('checkHRD-dayoff')
		$('#datatable').on('change', 'input[name=check_hrd]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.dayoff.checkHRD') }}', {
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
	@can('create-dayoff')
	{{-- Create Leave List --}}
	<div id="create-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.store') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Leave List (Number Available {{ $number_available }})</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-date_dayoff">
							<label for="date_dayoff" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="date" id="date" name="date_dayoff" class="form-control {{$errors->first('date_dayoff') != '' ? 'parsley-error' : ''}}" value="{{ old('date_dayoff') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_dayoff') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-total_dayoff">
							<label for="total_dayoff" class="control-label col-md-3 col-sm-3 col-xs-12">Full/Half <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								@foreach($total_dayoff as $key => $list)
								<label class="radio-inline"><input type="radio" name="total_dayoff" value="{{$key}}" @if(old('total_dayoff') == $key) checked @endif>{{ $list }}</label> 
								@endforeach
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('total_dayoff') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-type">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								@foreach($type as $key => $list)
								<label class="radio-inline"><input type="radio" class="type-{{$key}}" name="type" value="{{$key}}" @if(old('type') == $key) checked @endif>{{ $list }}</label> 
								@endforeach
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
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

	@can('edit-dayoff')
	{{-- Edit Leave List --}}
	<div id="edit-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.update') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Leave List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-date_dayoff">
							<label for="date_dayoff" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="date" id="date" name="date_dayoff" class="form-control {{$errors->first('date_dayoff') != '' ? 'parsley-error' : ''}}" value="{{ old('date_dayoff') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_dayoff') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-total_dayoff">
							<label for="total_dayoff" class="control-label col-md-3 col-sm-3 col-xs-12">Full/Half <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								@foreach($total_dayoff as $key => $list)
								<label class="radio-inline"><input type="radio" name="total_dayoff" value="{{$key}}" @if(old('total_dayoff') == $key) checked @endif>{{ $list }}</label> 
								@endforeach
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('total_dayoff') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-type">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								@foreach($type as $key => $list)
								<label class="radio-inline"><input type="radio" class="type-{{$key}}" name="type" value="{{$key}}" @if(old('type') == $key) checked @endif>{{ $list }}</label> 
								@endforeach
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
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

	@can('delete-dayoff')
	{{-- Delete Leave List --}}
	<div id="delete-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Leave List?</h4>
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

	@can('confirm-dayoff')
	{{-- Confirm Leave List --}}
	<div id="confirm-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm Leave List?</h4>
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

	{{-- Unconfirm Leave List --}}
	<div id="unconfirm-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Unconfirm Leave List?</h4>
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
	<h1>Leave List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">My Leave</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allUser-dayoff')
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

					<input type="date" name="f_start_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_start_range }}">
					<input type="date" name="f_end_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_end_range }}">
					
					<select name="f_check" class="form-control" onchange="this.form.submit()">
						<option value="" {{ $request->f_check === '' ? 'selected' : '' }}>All Status Checked</option>
						<option value="1" {{ $request->f_check === '1' ? 'selected' : '' }}>Checked</option>
						<option value="0" {{ $request->f_check === '0' ? 'selected' : '' }}>Uncheck</option>
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.dayoff.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-dayoff')
					<button type="button" class="btn btn-default create-dayoff" data-toggle="modal" data-target="#create-dayoff">Create</button>
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

					<th>Date Leave</th>
					<th>Date Request</th>
					<th>Total Leave</th>
					<th>Leave Remain</th>
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