@extends('backend.layout.master')

@section('title')
	Estimator List
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
				url: "{{ route('backend.estimator.datatables') }}",
				type: "POST",
				data: {
			    	f_sales : $('*[name=f_sales]').val(),
			    	f_estimator : $('*[name=f_estimator]').val(),
			    	f_price : $('*[name=f_price]').val(),
			    	search : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{
					className  : "details-control",
					orderable  : false,
					searchable : false,
					data       : null,
					defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
				},
				{data: 'created_at'},
				{data: 'photo', sClass: 'nowrap-cell'},
				{data: 'sum_value', sClass: 'nowrap-cell'},
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
			dom: '<l<tr>ip>',
		});

		// Add event listener for opening and closing details
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
	            row.child( row.data().detail ).show();
	            tr.addClass('shown');
	        }
	    } );

		$('#datatable').on('click', '.delete-estimator', function(){
			$('#delete-estimator *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.price-estimator', function(){
			$('#price-estimator *[name=id]').val($(this).data('id'));
			$('#price-estimator *[name=price]').val($(this).data('price'));
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

	    @if(Session::has('price-estimator-error'))
			$('#price-estimator').modal('show');
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
	{{-- Delete Estimator --}}
	<div id="delete-estimator" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.estimator.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Estimator?</h4>
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

	<h1>Estimator List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						@if(in_array(Auth::user()->position_id, getConfigValue('sales_position')) || in_array(Auth::id(), getConfigValue('sales_user')))
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@endif
						
						@if(Auth::user()->can('full-user') || in_array(Auth::user()->position_id, getConfigValue('estimator_position')) || in_array(Auth::id(), getConfigValue('estimator_user')))
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endif

						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
					</select>

					<select class="form-control select2" name="f_estimator" onchange="this.form.submit()">
						@if(in_array(Auth::user()->position_id, getConfigValue('estimator_position')) || in_array(Auth::id(), getConfigValue('estimator_user')))
						<option value="">All Estimator</option>
						<option value="staff" {{ $request->f_estimator == 'staff' ? 'selected' : '' }}>My Staff Estimator</option>
						@endif
						
						@if(Auth::user()->can('full-user') || in_array(Auth::user()->position_id, getConfigValue('sales_position')) || in_array(Auth::id(), getConfigValue('sales_user')))
							<option value="all" {{ $request->f_estimator == 'all' ? 'selected' : '' }}>All Estimator</option>
						@endif

						
						@foreach($estimator as $list)
						<option value="{{ $list->id }}" {{ $request->f_estimator == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
					</select>

					<select class="form-control select2" name="f_price" onchange="this.form.submit()">
						<option value="">All Status Price</option>
						<option value="unprice" {{ $request->f_price == 'unprice' ? 'selected' : '' }}>Unprice</option>
						<option value="price" {{ $request->f_price == 'price' ? 'selected' : '' }}>Price</option>
						
					</select>
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>

			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.estimator.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-estimator')
					<a class="btn btn-default" href="{{ route('backend.estimator.create') }}">Create</a>
					@endcan
					<select class="form-control" name="action">
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
					<th></th>

					<th>Information</th>
					<th>Photo</th>
					<th>Estimator</th>
					
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