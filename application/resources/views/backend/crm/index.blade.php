@extends('backend.layout.master')

@section('title')
	CRM List
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.crm.datatables') }}",
				type: "POST",
				data: {
			    	f_year    : $('*[name=f_year]').val(),
			    	f_month   : $('*[name=f_month]').val(),
			    	f_sales   : $('*[name=f_sales]').val(),
			    	f_company : $('*[name=f_company]').val(),
			    	f_time    : $('*[name=f_time]').val(),
			    	f_omset   : $('*[name=f_omset]').val(),
			    	f_id      : getUrlParameter('f_id'),
			    	s_no_crm  : $('*[name=s_no_crm]').val(),
				},
			},
			columns: [
				{data: 'action', orderable: false, searchable: false, sClass: ''},

				{data: 'check', orderable: false, searchable: false},
				
				{data: 'client', sClass: 'nowrap-cell'},

				{data: 'sales_fullname', sClass: 'nowrap-cell'},
				
				{data: 'activity', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'action', orderable: false, searchable: false, sClass: ''},
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

		$('#create-crm input[name=datetime_activity]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('#create-crm input[name=datetime_activity]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

		$('#next-crm input[name=datetime_activity]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('#next-crm input[name=datetime_activity]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

		$('#reschedule-crm input[name=datetime_activity]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('#reschedule-crm input[name=datetime_activity]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });


		$('#datatable').on('click', '.edit-crm', function(){
			$('#edit-crm *[name=company_id]').val($(this).data('company_id')).trigger('change');

			$.post("{{ route('backend.company.getPic') }}",
	        {
	            company_id: $(this).data('company_id'),
	        },
	        function(data){
	            $('#edit-crm *[name=pic_id]').empty();
				$.each(data, function(i, field){
					$('#edit-crm *[name=pic_id]').append("<option value='"+ field.id +"'>"+ field.fullname +" ("+field.nickname+")"+"</option>");
				});
				$('#edit-crm *[name=pic_id]').val($(this).data('pic_id'));
	        });

			$('#edit-crm *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.delete-crm', function(){
			$('#delete-crm input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.next-crm', function(){
			$('#next-crm input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.reschedule-crm', function(){
			$('#reschedule-crm input[name=id]').val($(this).data('id'));
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

	    $('select[name=f_sales]').select2({
		});

		$("#create-crm").on('change','select[name=company_id]', function(){
			if($(this).val() == ''){
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').val('').trigger('change');
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", true);
			}
			else{
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", false);

				$.post("{{ route('backend.company.getBrand') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=brand_id]').empty();
					$.each(data, function(i, field){
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.brand+"</option>");
					});
					$('select[name=brand_id]').val('').trigger('change');
		        });

				$.post("{{ route('backend.company.getAddress') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=address_id]').empty();
					$.each(data, function(i, field){
						$('select[name=address_id]').append("<option value='"+ field.id +"'>"+ field.address+"</option>");
					});
					$('select[name=address_id]').val('').trigger('change');
		        });

		        $.post("{{ route('backend.company.getPic') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=pic_id]').empty();
					$.each(data, function(i, field){
						$('select[name=pic_id]').append("<option value='"+ field.id +"'>"+ field.fullname +" ("+field.nickname+")"+"</option>");
					});
					$('select[name=pic_id]').val('').trigger('change');
		        });
			}
		});

		@if(Session::has('create-crm-error'))
			$('#create-crm').modal('show');
		@endif

		@if(Session::has('edit-crm-error'))
			$('#edit-crm').modal('show');
		@endif

		@if(Session::has('next-crm-error'))
			$('#next-crm').modal('show');
		@endif

		@if(Session::has('reschedule-crm-error'))
			$('#reschedule-crm').modal('show');
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

	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	

	@can('create-crm')
	{{-- Create crm --}}
	<div id="create-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.store') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create CRM</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Company" data-allow-clear="true">
									<option value=""></option>
									@foreach($company2 as $list)
									<option value="{{ $list->id }}" @if(old('company_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('company_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="brand_id" class="control-label col-md-3 col-sm-3 col-xs-12">Brand
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="brand_id" name="brand_id" class="form-control {{$errors->first('brand_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Brand" data-allow-clear="true">
									<option value=""></option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('brand_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="address_id" class="control-label col-md-3 col-sm-3 col-xs-12">Address
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="address_id" name="address_id" class="form-control {{$errors->first('address_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Address" data-allow-clear="true">
									<option value=""></option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('address_id') }}</li>
								</ul>
							</div>
							<input type="hidden" name="address" value="{{ old('address') }}" id="address">
						</div>

						<div class="form-group">
							<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">PIC <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select PIC" data-allow-clear="true">
									<option value=""></option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('pic_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<input type="date" name="date_activity" class="form-control" value="{{ old('date_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
								</ul>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-6">
								<input type="time" name="time_activity" class="form-control" value="{{ old('time_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
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

	@can('edit-crm')
	{{-- Edit crm --}}
	<div id="edit-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.update') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit CRM</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Sales" data-allow-clear="true">
									<option value=""></option>
									@foreach($user as $list)
									<option value="{{ $list->id }}" @if(old('sales_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">PIC <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}} select2"  data-placeholder="Select Activity" data-allow-clear="true">
									<option value=""></option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('pic_id') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="crm_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-crm')
	{{-- Delete crm --}}
	<div id="delete-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete List Request?</h4>
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

	@can('next-crm')
	{{-- next crm --}}
	<div id="next-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.next') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Next Event</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" name="activity" data-placeholder="Select Activity" data-allow-clear="true">
									<option value="">Select Activity</option>
									@foreach($activity as $key => $list)
									<option value="{{ $key }}" {{ old('activity') == $key ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('activity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<input type="date" name="date_activity" class="form-control" value="{{ old('date_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
								</ul>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-6">
								<input type="time" name="time_activity" class="form-control" value="{{ old('time_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="crm_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('reschedule-crm')
	{{-- Rechedule crm --}}
	<div id="reschedule-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.reschedule') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Rechedule Event</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control select2" name="activity" data-placeholder="Select Activity" data-allow-clear="true">
									<option value="">Select Activity</option>
									@foreach($activity as $key => $list)
									<option value="{{ $key }}" {{ old('activity') == $key ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('activity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<input type="date" name="date_activity" class="form-control" value="{{ old('date_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
								</ul>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-6">
								<input type="time" name="time_activity" class="form-control" value="{{ old('time_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Reschedule</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>CRM List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					{{-- <select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">All Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select> --}}
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">My CRM</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allSales-crm')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>

					<select class="form-control" name="f_company" onchange="this.form.submit()">
						<option value="">All Company</option>
						@foreach($company as $list)
						<option value="{{ $list->company_id }}, {{ $list->brand_id }}" {{ $request->f_company == ($list->company_id . ', ' . $list->brand_id) ? 'selected' : '' }}>{{ $list->company_name }} - {{ $list->brand_name }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_time" onchange="this.form.submit()">
						<option value="">All Time</option>
						@foreach($time as $key => $list)
						<option value="{{ $key }}" @if($request->f_time == $key) selected @endif>{{ $list }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_omset" onchange="this.form.submit()">
						<option value="">All Omset</option>
						@foreach($omset as $key => $list)
						<option value="{{ $key }}" @if($request->f_omset  == $key) selected @endif>{{ $list }}</option>
						@endforeach
					</select>

					<input type="text" name="s_no_crm" class="form-control" value="{{ $request->s_no_crm }}" placeholder="Search No CRM" onchange="this.form.submit()">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.crm.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-crm')
					<button type="button" data-toggle="modal" data-target="#create-crm" class="btn btn-default">Create</button>
					<a href="{{ route('backend.crm.createProspec') }}" class="btn btn-default">Create For Prospec</a>
					@endif
					<select class="form-control" name="action">
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
					<th>Action</th>

					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>Client</th>

					<th>Sales</th>
					<th>Activity</th>

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

@endsection