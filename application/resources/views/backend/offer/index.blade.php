@extends('backend.layout.master')

@section('title')
	Offer List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.offer.datatables') }}",
				type: "POST",
				data: {
			    	f_division : $('*[name=f_division]').val(),
			    	f_sales    : $('*[name=f_sales]').val(),
			    	f_year     : $('*[name=f_year]').val(),
			    	f_month    : $('*[name=f_month]').val(),
			    	search     : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{
					className  : "details-control",
					orderable  : false,
					searchable : false,
					data       : null,
					defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
				},
				{data: 'no_document'},
				{data: 'action', orderable: false, searchable: false},
			],
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
	            row.child( row.data().view ).show();
	            tr.addClass('shown');
	        }
	    } );

		$('#datatable').on('click', '.pdf-offer', function(){
			$('#pdf-offer *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.delete-offer', function(){
			$('#delete-offer *[name=id').val($(this).data('id'));
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


		@if(Session::has('pdf-offer-error'))
		$('#pdf-offer').modal('show');
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
	
	{{-- Delete Offer --}}
	<div id="delete-offer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Offer?</h4>
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

	{{-- Offer PDF --}}
	<div id="pdf-offer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.pdf') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size', 'A4') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation', 'portrait') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation', 'portrait') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="option" class="control-label col-md-3 col-sm-3 col-xs-12">Option <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="option-total" name="option" value="total" @if(old('option', 'total') == 'total') checked @endif>Total all detail</label> 
								<label class="radio-inline"><input type="radio" id="option-choice" name="option" value="choice" @if(old('option', 'total') == 'choice') checked @endif>Don't total all detail</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('option') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="header" class="control-label col-md-3 col-sm-3 col-xs-12">Header <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="header-on" name="header" value="on" @if(old('header', 'on') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="header-off" name="header" value="off" @if(old('header', 'on') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('header') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="expo" class="control-label col-md-3 col-sm-3 col-xs-12">Expo <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="expo-on" name="expo" value="on" @if(old('expo', 'off') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="expo-off" name="expo" value="off" @if(old('expo', 'off') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('expo') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Offer List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_month" onchange="this.form.submit()">
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('full-user')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
						
					</select>
					<select class="form-control select2" name="f_division" onchange="this.form.submit()">
						<option value="">Current Division</option>
						<option value="all" {{ $request->f_division == 'all' ? 'selected' : '' }}>All Division</option>
						@foreach($division as $list)
						<option value="{{ $list->id }}" {{ $request->f_division == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
						@endforeach
					</select>
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.offer.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-offer')
					<a href="{{ route('backend.offer.create') }}" class="btn btn-default">Create</a>
					@endcan
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
					<th></th>
					<th>Information</th>
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