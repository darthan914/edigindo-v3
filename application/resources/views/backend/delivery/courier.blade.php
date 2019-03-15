@extends('backend.layout.master')

@section('title')
	My List Delivery
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<script type="text/javascript">

	$(function() {
		$('input[name=f_range]').daterangepicker({
		    showDropdowns: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear',
		        format: 'DD MMM YYYY',
		    }
		});

		$('input[name="f_range"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
	    });

	    $('input[name="f_range"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		function format ( d ) {
		    return d.detail;
		}

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.delivery.datatablesCourier') }}",
				type: "POST",
				data: {
			    	f_range  : $('*[name=f_range]').val(),
			    	f_when   : $('*[name=f_when]').val(),
			    	f_status : $('*[name=f_status]').val(),
			    	f_city   : $('*[name=f_city]').val(),
			    	f_user   : $('*[name=f_user]').val(),
			    	f_courier : $('*[name=f_courier]').val(),
			    	f_id     : getUrlParameter('f_id'),
				},
			},
			columns: [
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'user_name', sClass: 'nowrap-cell'},

				{data: 'city', sClass: 'nowrap-cell'},
				{data: 'status', orderable: false, searchable: false, sClass: 'nowrap-cell'},
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

		$('#datatable').on('click', '.startSend-delivery', function(){
			$('#startSend-delivery input[name=id]').val($(this).data('id'));
			$('#startSend-delivery input[name=start_latitude]').val(0);
        	$('#startSend-delivery input[name=start_longitude]').val(0);

			if ("geolocation" in navigator){ //check geolocation available 
		        //try to get user current location using getCurrentPosition() method
		        navigator.geolocation.getCurrentPosition( function(position){ 
		        	$('#startSend-delivery input[name=start_latitude]').val(position.coords.latitude);
		        	$('#startSend-delivery input[name=start_longitude]').val(position.coords.longitude);
		        });
		    }
		    else{
		        console.log("Browser doesn't support geolocation!");
		        
		    }
		});

		$('#datatable').on('click', '.undoStartSend-delivery', function(){
			$('#undoStartSend-delivery input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.finish-delivery', function(){
			$('#finish-delivery input[name=id]').val($(this).data('id'));

			if ("geolocation" in navigator){ //check geolocation available 
		        //try to get user current location using getCurrentPosition() method
		        navigator.geolocation.getCurrentPosition( function(position){ 
		        	$('#finish-delivery input[name=end_latitude]').val(position.coords.latitude);
		        	$('#finish-delivery input[name=end_longitude]').val(position.coords.longitude);
		        });
		    }
		    else{
		        console.log("Browser doesn't support geolocation!");
		        
		    }

		});

		$('#datatable').on('click', '.undoFinish-delivery', function(){
			$('#undoFinish-delivery input[name=id]').val($(this).data('id'));
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

	    $('select[name=f_user], select[name=f_courier], select[name=f_city]').select2({
		});

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



	<div id="startSend-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.startSend') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Start Send Delivery?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="start_latitude" value="{{old('start_latitude')}}">
						<input type="hidden" name="start_longitude" value="{{old('start_longitude')}}">
						<button type="submit" class="btn btn-success update-current-location">Yes</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="undoStartSend-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.undoStartSend') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Send Delivery?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Undo</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="finish-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.finish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Finish Delivery?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="end_latitude" value="{{old('end_latitude')}}">
						<input type="hidden" name="end_longitude" value="{{old('end_longitude')}}">
						<button type="submit" class="btn btn-success">Yes</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="undoFinish-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.undoFinish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Finish Delivery?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Undo</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>




	<h1>My List Delivery</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					{{-- <input type="text" name="f_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_range }}"  style="width: 220px" placeholder="Filter Date Range"> --}}

					<select class="form-control" name="f_when" onchange="this.form.submit()">
						<option value="" {{ $request->f_when === '' ? 'selected' : '' }}>All Day</option>
						<option value="today" {{ $request->f_when === 'today' ? 'selected' : '' }}>Today</option>
						<option value="tomorrow" {{ $request->f_when === 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
						<option value="future" {{ $request->f_when === 'future' ? 'selected' : '' }}>Future</option>
						<option value="yesterday" {{ $request->f_when === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
					</select>
					<select class="form-control" name="f_status" onchange="this.form.submit()">
						<option value="" {{ $request->f_status === '' ? 'selected' : '' }}>All Status</option>
						<option value="TAKEN" {{ $request->f_status === 'TAKEN' ? 'selected' : '' }}>Waiting</option>
						<option value="SENDING" {{ $request->f_status === 'SENDING' ? 'selected' : '' }}>Sending</option>
						<option value="FINISH" {{ $request->f_status === 'success' ? 'selected' : '' }}>Finish</option>
					</select>

					<select class="form-control" name="f_city" onchange="this.form.submit()">
						<option value="">All City</option>
						
						@foreach($city as $list)
						<option value="{{ $list }}" {{ $request->f_city == $list ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
						
					</select>

					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">All User</option>

						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>
					<select class="form-control" name="f_courier" onchange="this.form.submit()">
						<option value="">My List</option>
						<option value="staff" {{ $request->f_courier == 'staff' ? 'selected' : '' }}>My Staff</option>
						<option value="all" {{ $request->f_courier == 'all' ? 'selected' : '' }}>All User</option>
						
						@foreach($courier as $list)
						<option value="{{ $list->id }}" {{ $request->f_courier == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr>
					<th></th>

					<th>Project - Nama</th>
					<th>Kota</th>

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
				</tr>
			</tfoot>
		</table>
	</div>


@endsection