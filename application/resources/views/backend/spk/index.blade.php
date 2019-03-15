@extends('backend.layout.master')

@section('title')
	SPK List
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
				url: "{{ route('backend.spk.datatables') }}",
				type: "POST",
				data: {
			    	f_done  : $('*[name=f_done]').val(),
			    	f_sales : $('*[name=f_sales]').val(),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	search : $('*[name=search]').val(),
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
				{data: 'no_spk', sClass: 'nowrap-cell', visible: false},
				{data: 'name', sClass: 'nowrap-cell'},
				{data: 'total_loss', searchable: false},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
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

		$('#datatable').on('click', '.pdf-spk', function(){
			$('#pdf-spk *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.delete-spk', function(){
			$('#delete-spk *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.finish-spk', function(){
			$('#finish-spk *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoFinish-spk', function(){
			$('#undoFinish-spk *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.confirm-spk', function(){
			$('#confirm-spk *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.unconfirm-spk', function(){
			$('#unconfirm-spk *[name=id]').val($(this).data('id'));
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

		@if(Session::has('pdf-spk-error'))
		$('#pdf-spk').modal('show');
		@endif
		@if(Session::has('finish-spk-error'))
		$('#finish-spk').modal('show');
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
	{{-- Delete SPK --}}
	<div id="delete-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete SPK?</h4>
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

	{{-- Done SPK --}}
	<div id="finish-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.finish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Finish SPK?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="quality" class="control-label col-md-3 col-sm-3 col-xs-12"><span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="quality-yes" name="quality" value="1" @if(old('quality') != '' && old('quality') == 1) checked @endif>Yes</label> 
								<label class="radio-inline"><input type="radio" id="quality-no" name="quality" value="0" @if(old('quality') != '' && old('quality') == 0) checked @endif>No</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quality') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="comment" class="control-label col-md-3 col-sm-3 col-xs-12">Comment <span class="required">* if quality no</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea class="form-control {{$errors->first('comment') != '' ? 'parsley-error' : ''}}" name="comment">{{ old('comment') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('comment') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Finish</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Done SPK --}}
	<div id="undoFinish-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.undoFinish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Finish SPK?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-warning">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Confirm SPK List --}}
	<div id="confirm-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.confirm') }}" method="post" enctype="multipart/form-data">
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

	{{-- Unconfirm SPK List --}}
	<div id="unconfirm-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.unconfirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Unconfirm SPK List?</h4>
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

	{{-- SPK PDF --}}
	<div id="pdf-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.pdf') }}" method="post" enctype="multipart/form-data">
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
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="type-production" name="type" value="production" @if(old('type', 'production') == 'production') checked @endif>Production</label> 
								<label class="radio-inline"><input type="radio" id="type-purchasing" name="type" value="purchasing" @if(old('type', 'production') == 'purchasing') checked @endif>Purchasing</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="hide_client" class="control-label col-md-3 col-sm-3 col-xs-12">Hide Client <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="hide_client-on" name="hide_client" value="on" @if(old('hide_client', 'off') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="hide_client-off" name="hide_client" value="off" @if(old('hide_client', 'off') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('hide_client') }}</li>
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

	<h1>SPK List</h1>
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
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">All Month</option>
						{{-- <option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option> --}}
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allSales-spk')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
						
					</select>
					<select class="form-control" name="f_done" onchange="this.form.submit()">
						<option value="">All Status Finish</option>
						<option value="UNFINISH_PROD" {{ $request->f_done == 'UNFINISH_PROD' ? 'selected' : '' }}>Unfinish Production</option>
						<option value="UNFINISH_SPK" {{ $request->f_done == 'UNFINISH_SPK' ? 'selected' : '' }}>Unfinish SPK</option>
						<option value="FINISH" {{ $request->f_done == 'FINISH' ? 'selected' : '' }}>Finish</option>
					</select>
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.spk.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-spk')
					<a href="{{ route('backend.spk.create') }}" class="btn btn-default">Create</a>
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

					<th></th>
					<th>No SPK</th>
					<th>Information</th>

					<th>Price</th>
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