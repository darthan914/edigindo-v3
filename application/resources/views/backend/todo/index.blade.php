@extends('backend.layout.master')

@section('title')
	To do List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		function format ( d ) {
		    return d.detail;
		}

		$('input[name=f_date]').daterangepicker({
		    singleDatePicker: true,
		    // timePicker: true,
		    // timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="f_date"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	        $( "#filter" ).submit();
	    });

	    $('input[name="f_date"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		    $( "#filter" ).submit();
		});

	    $('input[name=f_next_date]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="f_next_date"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	        $( "#filter" ).submit();

	    });


	    $('input[name="f_next_date"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		    $( "#filter" ).submit();
		});

		$('input[name=reschedule_date]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="reschedule_date"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	    });

	    $('input[name="reschedule_date"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		$('input[name=next_date]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="next_date"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	    });

	    $('input[name="next_date"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		$('select[name=f_sales]').select2({
			placeholder: "Filter Sales",
			allowClear: true
		});

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.todo.datatables') }}",
				type: "POST",
				data: {
			    	f_sales     : $('*[name=f_sales]').val(),
			    	f_date      : $('*[name=f_date]').val(),
			    	f_next_date : $('*[name=f_next_date]').val(),
			    	f_status    : $('*[name=f_status]').val(),
			    	f_year      : $('*[name=f_year]').val(),
			    	f_month     : $('*[name=f_month]').val(),
				},
			},
			columns: [
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'sales_name', sClass: 'nowrap-cell'},
				{data: 'company', sClass: 'nowrap-cell'},
				
				{data: 'brand', sClass: 'nowrap-cell'},
				{data: 'event'},
				{data: 'date_todo', sClass: 'nowrap-cell'},

				{data: 'status', sClass: 'nowrap-cell'},
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
		});

		// Add event listener for opening and closing details
	    $('#datatable tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( format(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );

		$('#datatable').on('click', '.delete-todo', function(){
			$('.todo_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.status-todo', function(){
			$('.todo_id-onstatus').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undo-todo', function(){
			$('.todo_id-onundo').val($(this).data('id'));
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

	    @if(Session::has('status-todo-error'))
		$('#status-todo').modal('show');
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
	@can('delete-todo')
	{{-- Delete Todo --}}
	<div id="delete-todo" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.todo.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Supplier?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="todo_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('status-todo')
	{{-- Status Todo --}}
	<div id="status-todo" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.todo.status') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Update Status</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="status" class="control-label col-md-3 col-sm-3 col-xs-12">Status <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="status-SUCCESS" name="status" value="SUCCESS" @if(old('status') == 'SUCCESS') checked @endif>Success</label> 
								<label class="radio-inline"><input type="radio" id="status-FAILED" name="status" value="FAILED" @if(old('status') == 'FAILED') checked @endif>Failed</label>
								<label class="radio-inline"><input type="radio" id="status-RESCHEDULE" name="status" value="RESCHEDULE" @if(old('status') == 'RESCHEDULE') checked @endif>Reschedule</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('status') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="reason" class="control-label col-md-3 col-sm-3 col-xs-12">Reason <span class="required">*if status Reschedule or Failed</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="reason" name="reason" class="form-control {{$errors->first('reason') != '' ? 'parsley-error' : ''}}" value="{{ old('reason') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('reason') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="next" class="control-label col-md-3 col-sm-3 col-xs-12">What Next <span class="required">*if status Success</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="next" name="next" class="form-control {{$errors->first('next') != '' ? 'parsley-error' : ''}}" value="{{ old('next') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('next') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="reschedule_date" class="control-label col-md-3 col-sm-3 col-xs-12">Reschedule <span class="required">*if status Reschedule</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="reschedule_date" name="reschedule_date" class="form-control {{$errors->first('reschedule_date') != '' ? 'parsley-error' : ''}}" value="{{ old('reschedule_date') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('reschedule_date') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="next_date" class="control-label col-md-3 col-sm-3 col-xs-12">Next Date <span class="required">*if status Success</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="next_date" name="next_date" class="form-control {{$errors->first('next_date') != '' ? 'parsley-error' : ''}}" value="{{ old('next_date') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('next_date') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="todo_id-onstatus" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('undo-todo')
	{{-- Undo Todo --}}
	<div id="undo-todo" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.todo.undo') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Status?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="todo_id-onundo" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>To Do List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get" id="filter">
					<input type="text" name="f_date" class="form-control" value="{{ $request->f_date ? date('d F Y', strtotime($request->f_date)) : '' }}" placeholder="Search Date Todo">
					{{-- <input type="text" name="f_next_date" class="form-control" value="{{ $request->f_next_date ? date('d F Y', strtotime($request->f_next_date)) : '' }}" placeholder="Search Date Next"> --}}
					{{-- <select class="form-control" name="f_status" onchange="this.form.submit()">
						<option value="">All Status</option>
						@foreach($status as $list)
						<option value="{{ $list }}" {{ $request->f_status == $list ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select> --}}
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allSales-todo')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.todo.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-todo')
					<a href="{{ route('backend.todo.create') }}" class="btn btn-default">Create</a>
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

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th></th>
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Name</th>
					<th>Company</th>

					<th>Brand</th>
					<th>Event</th>
					<th>Date</th>

					<th>Status</th>
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
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection