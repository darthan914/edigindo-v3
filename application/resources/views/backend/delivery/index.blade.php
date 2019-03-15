@extends('backend.layout.master')

@section('title')
	Delivery List
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

		// $('input[name=datetime_send]').daterangepicker({
		// 	autoApply: true,
		//     singleDatePicker: true,
		//     timePicker: true,
		//     timePicker24Hour: true,
		//     autoUpdateInput: false,
		//     locale: {
		//         cancelLabel: 'Cancel',
		//         format: 'DD MMMM YYYY HH:mm',
		//     }
		// });

		// $('input[name="datetime_send"]').on('apply.daterangepicker', function(ev, picker) {
	 //        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	 //    });

	    $('input[name=date_sended]').daterangepicker({
		    autoApply: true,
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Cancel'
		    }
		});

		$('input[name="date_sended"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

	    $('input[name="date_sended"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		$('input[name=date_arrived]').daterangepicker({
		    autoApply: true,
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Cancel'
		    }
		});

		$('input[name="date_arrived"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

	    $('input[name="date_arrived"]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});

		function format ( d ) {
		    return d.detail;
		}

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.delivery.datatables') }}",
				type: "POST",
				data: {
					f_via    : $('*[name=f_via]').val(),
			    	f_range  : $('*[name=f_range]').val(),
			    	f_when   : $('*[name=f_when]').val(),
			    	f_status : $('*[name=f_status]').val(),
			    	f_city   : $('*[name=f_city]').val(),
			    	f_user   : $('*[name=f_user]').val(),
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

				{data: 'fullname', sClass: 'nowrap-cell'},

				// {data: 'project'},

				{data: 'spk', sClass: 'nowrap-cell'},
				{data: 'is_ppn'},

				// {data: 'company'},
				{data: 'datetime_send', sClass: 'nowrap-cell'},
				{data: 'city', sClass: 'nowrap-cell'},
				{data: 'via'},
				{data: 'status', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'action_2', orderable: false, searchable: false},
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
			scrollX: true,
			pageLength: 100,
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

		$('#datatable').on('click', '.change-delivery', function(){
			$('.via-onchange[value='+$(this).data('via')+']').prop('checked', true);
			$('.datetime_send-onchange').val($(this).data('datetime_send'));
			$('.delivery_id-onchange').val($(this).data('id'));
		});

		$('#datatable').on('click', '.send-delivery', function(){
			$('.delivery_id-onsend').val($(this).data('id'));
		});

		$('#datatable').on('click', '.take-delivery', function(){
			$('#take-delivery input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoTake-delivery', function(){
			$('#undoTake-delivery input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.confirm-delivery', function(){
			$('.delivery_id-onconfirm').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoSend-delivery', function(){
			$('.delivery_id-onundosend').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoConfirm-delivery', function(){
			$('.delivery_id-onundoconfirm').val($(this).data('id'));
		});

		$('#datatable').on('click', '.delete-delivery', function(){
			$('.delivery_id-ondelete').val($(this).data('id'));
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

	    $('select[name=f_user], select[name=f_city]').select2({
		});


		@if(Session::has('change-delivery-error'))
		$('#change-delivery').modal('show');
		@endif
		@if(Session::has('send-delivery-error'))
		$('#send-delivery').modal('show');
		@endif
		@if(Session::has('confirm-delivery-error'))
		$('#confirm-delivery').modal('show');
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

	@can('change-delivery')
	{{-- Change Delivery --}}
	<div id="change-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.change') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Change Delivery</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="via" class="control-label col-md-3 col-sm-3 col-xs-12">Via <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="via-Kurir" name="via" value="Kurir" class="via-onchange" @if(old('via') == 'Kurir') checked @endif>Kurir</label> 
								<label class="radio-inline"><input type="radio" id="via-Supir" name="via" value="Supir" class="via-onchange" @if(old('via') == 'Supir') checked @endif>Supir</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('via') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="datetime_send" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Send <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="datetime-local" class="form-control {{$errors->first('datetime_send') != '' ? 'parsley-error' : ''}} datetime_send-onchange" name="datetime_send" value="{{old('datetime_send')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('datetime_send') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-onchange" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('send-delivery')
	{{-- Send Delivery --}}
	<div id="send-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.send') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Send Delivery</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="name_courier" class="control-label col-md-3 col-sm-3 col-xs-12">Name Kurir/Supir <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('name_courier') != '' ? 'parsley-error' : ''}} name_courier-onsend" name="name_courier" value="{{old('name_courier')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name_courier') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="date_sended" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Send
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_sended') != '' ? 'parsley-error' : ''}} date_sended-onsend" name="date_sended" value="{{old('date_sended')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_sended') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-onsend" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('take-delivery')
	{{-- Take Delivery --}}
	<div id="take-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.take') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Take Delivery?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Take</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('undoTake-delivery')
	{{-- Take Delivery --}}
	<div id="undoTake-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.undoTake') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Take Delivery?</h4>
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
	@endcan

	@can('confirm-delivery')
	{{-- Confirm Delivery --}}
	<div id="confirm-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm Delivery</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="status" class="control-label col-md-3 col-sm-3 col-xs-12">Status <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="status-SUCCESS" name="status" value="SUCCESS" class="status-onconfirm" @if(old('status') == 'SUCCESS') checked @endif>Success</label> 
								<label class="radio-inline"><input type="radio" id="status-FAILED" name="status" value="FAILED" class="status-onconfirm" @if(old('status') == 'FAILED') checked @endif>Failed</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('status') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="date_arrived" class="control-label col-md-3 col-sm-3 col-xs-12">Date Arrived  <span class="required">(if success)</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_arrived') != '' ? 'parsley-error' : ''}} date_arrived-onconfirm" name="date_arrived" value="{{old('date_arrived')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_arrived') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="received_by" class="control-label col-md-3 col-sm-3 col-xs-12">Recieved By
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('received_by') != '' ? 'parsley-error' : ''}} received_by-onconfirm" name="received_by" value="{{old('received_by')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('received_by') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="reason" class="control-label col-md-3 col-sm-3 col-xs-12">Reason Failed <span class="required">(if failed)</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('reason') != '' ? 'parsley-error' : ''}} reason-onconfirm" name="reason" value="{{old('reason')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('reason') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-onconfirm" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('undoSend-delivery')
	{{-- Undo Change Delivery --}}
	<div id="undoSend-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.undoSend') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Send Delivery ?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-onundosend" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Undo</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('undoConfirm-delivery')
	{{-- Undo Confirm Delivery --}}
	<div id="undoConfirm-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.undoConfirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Confirm Delivery ?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-onundoconfirm" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Undo</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-delivery')
	{{-- Delete Delivery --}}
	<div id="delete-delivery" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.delivery.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Delivery ?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="delivery_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Delivery List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<input type="text" name="f_range" class="form-control" onchange="this.form.submit()" value="{{ $request->f_range }}"  style="width: 220px" placeholder="Filter Date Range">
					<select class="form-control" name="f_via" onchange="this.form.submit()">
						<option value="" {{ $request->f_via === '' ? 'selected' : '' }}>All Via</option>
						<option value="Kurir" {{ $request->f_via === 'Kurir' ? 'selected' : '' }}>Kurir</option>
						<option value="Supir" {{ $request->f_via === 'Supir' ? 'selected' : '' }}>Supir</option>
					</select>
					<select class="form-control" name="f_when" onchange="this.form.submit()">
						<option value="" {{ $request->f_when === '' ? 'selected' : '' }}>Today</option>
						<option value="all" {{ $request->f_when === 'all' ? 'selected' : '' }}>All Day</option>
						<option value="tomorrow" {{ $request->f_when === 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
						<option value="future" {{ $request->f_when === 'future' ? 'selected' : '' }}>Future</option>
						<option value="yesterday" {{ $request->f_when === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
					</select>
					<select class="form-control" name="f_status" onchange="this.form.submit()">
						<option value="" {{ $request->f_status === '' ? 'selected' : '' }}>All Status</option>
						<option value="WAITING" {{ $request->f_status === 'WAITING' ? 'selected' : '' }}>Waiting</option>
						<option value="SENDING" {{ $request->f_status === 'SENDING' ? 'selected' : '' }}>Sending</option>
						<option value="TAKEN" {{ $request->f_status === 'TAKEN' ? 'selected' : '' }}>Taken</option>
						<option value="FINISH" {{ $request->f_status === 'FINISH' ? 'selected' : '' }}>Finish</option>
						<option value="SUCCESS" {{ $request->f_status === 'SUCCESS' ? 'selected' : '' }}>Success</option>
						<option value="FAILED" {{ $request->f_status === 'FAILED' ? 'selected' : '' }}>Failed</option>
					</select>

					<select class="form-control" name="f_city" onchange="this.form.submit()">
						<option value="">All City</option>
						
						@foreach($city as $list)
						<option value="{{ $list }}" {{ $request->f_city == $list ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
						
					</select>

					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">My Delivery</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All User</option>
						
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>


					
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-delivery')
					<a href="{{ route('backend.delivery.create') }}" class="btn btn-default">Create</a>
					@endcan
					{{-- <select class="form-control" name="action">
						<option value="enable">Enable</option>
						<option value="disable">Disable</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button> --}}
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr>
					<th></th>

					<th>Project - Nama</th>
					<th>SPK</th>
					<th>PPN</th>

					<th>Tanggal Tiba</th>
					<th>Kota</th>
					<th>Jalur</th>

					<th>Status</th>
					<th>Action</th>

					<th></th>
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