@extends('backend.layout.master')

@section('title')
	Activity List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	function format ( d ) {

	    return d.activity;
	}

	$(function() {
		$('select[name=f_user]').select2();


		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.activity.datatables') }}",
				type: "POST",
				data: {
					f_id    : getUrlParameter('f_id'),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	f_user  : $('*[name=f_user]').val(),

			    	f_start_range : $('*[name=f_start_range]').val(),
			    	f_end_range   : $('*[name=f_end_range]').val(),
			    	f_check       : $('*[name=f_check]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{
	                class:          "details-control",
	                orderable:      false,
	                data:           null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'fullname', sClass: 'nowrap-cell'},

				{data: 'date_activity', sClass: 'nowrap-cell'},

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

		$('#datatable tbody').on( 'click', 'td.details-control > button', function () {
			console.log('click');
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );

	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( format( row.data() ) ).show();
	            tr.addClass('shown');
	        }
	    } );


		$('#datatable').on('click', '.edit-activity', function(){

			$('#edit-activity input[name=id]').val($(this).data('id'));
			$('#edit-activity input[name=date_activity]').val($(this).data('date_activity'));
			$('#edit-activity input[name=time_start]').val($(this).data('time_start'));
			$('#edit-activity input[name=time_end]').val($(this).data('time_end'));
			$('#edit-activity textarea[name=activity]').val($(this).data('activity'));
			$('#edit-activity textarea[name=result]').val($(this).data('result'));
		});

		$('#datatable').on('click', '.delete-activity', function(){
			$('#delete-activity input[name=id]').val($(this).data('id'));
		});


		$('#datatable').on('click', '.confirm-activity', function(){
			$('#confirm-activity input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.unconfirm-activity', function(){
			$('#unconfirm-activity input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('create-activity-error'))
		$('#create-activity').modal('show');
		@endif

		@if(Session::has('edit-activity-error'))
		$('#edit-activity').modal('show');
		@endif

		@can('checkHRD-activity')
		$('#datatable').on('change', 'input[name=check_hrd]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.activity.checkHRD') }}', {
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
	

	@can('delete-activity')
	{{-- Delete Activity List --}}
	<div id="delete-activity" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.activity.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Activity List?</h4>
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

	@can('confirm-activity')
	{{-- Confirm Activity List --}}
	<div id="confirm-activity" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.activity.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm Activity List?</h4>
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

	{{-- Unconfirm Activity List --}}
	<div id="unconfirm-activity" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.activity.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Unconfirm Activity List?</h4>
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
	<h1>Activity List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">My Activity</option>
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
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
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
				<form method="post" id="action" action="{{ route('backend.activity.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-activity')
					<a href="{{ route('backend.activity.create') }}" class="btn btn-default">Create</a>
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
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>View</th>
					<th>Name</th>
					<th>Date</th>

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
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection