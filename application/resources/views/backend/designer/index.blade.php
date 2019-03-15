@extends('backend.layout.master')

@section('title')
	Designer List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>


<script type="text/javascript">

	function format ( d ) {
		html = '\
			<table class="table">\
				<tr>\
					<th width="20%">Project</th>\
					<td width="80%">'+d.project+'</td>\
				</tr>\
				<tr>\
					<th width="20%">Description</th>\
					<td width="80%">'+d.description+'</td>\
				</tr>\
				<tr>\
					<th width="20%">Note Project</th>\
					<td width="80%">'+d.note_project+'</td>\
				</tr>\
			</table>\
		';

	    return html;
	}

	$(function() {

		var tableProgress = $('#datatableProgress').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.designer.datatables') }}",
				type: "POST",
				data: {
			    	f_sales    : $('*[name=f_sales]').val(),
			    	f_designer : $('*[name=f_designer]').val(),
			    	f_year     : $('*[name=f_year]').val(),
			    	f_month    : $('*[name=f_month]').val(),
			    	f_status   : 'NOT_FINISH',
			    	f_urgent   : $('*[name=f_urgent]').val(),
			    	f_id       : getUrlParameter('f_id'),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{
	                class:          "details-control-progress",
	                orderable:      false,
	                data:           null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'sales', sClass: 'nowrap-cell'},
				{data: 'start_project', sClass: 'nowrap-cell'},

				{data: 'status_project', sClass: 'nowrap-cell'},
				{data: 'approved_sales', sClass: 'nowrap-cell'},

				{data: 'result_project', sClass: 'nowrap-cell'},
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
			pageLength: 100,
		});

		$('#datatableProgress tbody').on( 'click', 'td.details-control-progress > button', function () {
			console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableProgress.row( tr );

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


		var tableFinishToday = $('#datatableFinishToday').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.designer.datatables') }}",
				type: "POST",
				data: {
			    	f_date : {{ date('Y-m-d') }},
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{
	                class:          "details-control",
	                orderable:      false,
	                data:           null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'sales', sClass: 'nowrap-cell'},
				{data: 'start_project', sClass: 'nowrap-cell'},

				{data: 'status_project', sClass: 'nowrap-cell'},
				{data: 'approved_sales', sClass: 'nowrap-cell'},

				{data: 'result_project', sClass: 'nowrap-cell'},
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
			pageLength: 100,
		});

		$('#datatableFinishToday tbody').on( 'click', 'td.details-control > button', function () {
			console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableFinishToday.row( tr );

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



		var tableFinish = $('#datatableFinish').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.designer.datatables') }}",
				type: "POST",
				data: {
			    	f_sales    : $('*[name=f_sales]').val(),
			    	f_designer : $('*[name=f_designer]').val(),
			    	f_year     : $('*[name=f_year]').val(),
			    	f_month    : $('*[name=f_month]').val(),
			    	f_status   : 'FINISH',
			    	f_urgent   : $('*[name=f_urgent]').val(),
			    	f_id       : getUrlParameter('f_id'),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{
	                class:          "details-control",
	                orderable:      false,
	                data:           null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'sales', sClass: 'nowrap-cell'},
				{data: 'start_project', sClass: 'nowrap-cell'},

				{data: 'status_project', sClass: 'nowrap-cell'},
				{data: 'approved_sales', sClass: 'nowrap-cell'},

				{data: 'result_project', sClass: 'nowrap-cell'},
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
			pageLength: 100,
		});

		$('#datatableFinish tbody').on( 'click', 'td.details-control > button', function () {
			console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableFinish.row( tr );

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



		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.delete-designer', function(){
			$('.designer_id-ondelete').val($(this).data('id'));
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.take-designer', function(){
			$('#take-designer input[name=id]').val($(this).data('id'));
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.finish-designer', function(){
			$('.designer_id-onfinish').val($(this).data('id'));
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.approve-designer', function(){
			$('#approve-designer input[name=id]').val($(this).data('id'));
			$('#approve-designer').submit();
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.reject-designer', function(){
			$('#reject-designer input[name=id]').val($(this).data('id'));
			$('#reject-designer').submit();
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.success-designer', function(){
			$('#success-designer input[name=id]').val($(this).data('id'));
			$('#success-designer').submit();
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.failed-designer', function(){
			$('#failed-designer input[name=id]').val($(this).data('id'));
			$('#failed-designer').submit();
		});

		$('#datatableProgress, #datatableFinish, #datatableFinishToday').on('click', '.revision-designer', function(){
			$('.designer_id-onrevision').val($(this).data('id'));
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

	    $('select[name=f_sales], select[name=f_designer]').select2({
		});

		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).attr('id'));
		});

		@if(Session::has('finish-designer-error'))
		$('#finish-designer').modal('show');
		@endif
		@if(Session::has('revision-designer-error'))
		$('#revision-designer').modal('show');
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

	<form class="form-horizontal form-label-left" id="approve-designer" action="{{ route('backend.designer.approve') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""></form>
	<form class="form-horizontal form-label-left" id="reject-designer" action="{{ route('backend.designer.reject') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""></form>
	<form class="form-horizontal form-label-left" id="success-designer" action="{{ route('backend.designer.success') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""></form>
	<form class="form-horizontal form-label-left" id="failed-designer" action="{{ route('backend.designer.failed') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""></form>

	@can('delete-designer')
	{{-- Delete Designer --}}
	<div id="delete-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Designer Project?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('take-designer')
	{{-- take Designer --}}
	<div id="take-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.take') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Take Designer Project ?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onfinish" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Take</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('finish-designer')
	{{-- Finish Designer --}}
	<div id="finish-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.finish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Finish Designer Project</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="note_project" class="control-label col-md-3 col-sm-3 col-xs-12">Note Project
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="note_project" name="note_project" class="form-control {{$errors->first('note_project') != '' ? 'parsley-error' : ''}}">{{ old('note_project') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note_project') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onfinish" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	{{-- @can('approved-designer')
	Approve Designer
	<div id="approve-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.approve') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Approve Designer Project?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onapprove" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Approve</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	Reject Designer
	<div id="reject-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.reject') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Reject Designer Project?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onreject" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Reject</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('project-designer')
	Success Designer
	<div id="success-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.success') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Success Designer Project?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onsuccess" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Success</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	Failed Designer
	<div id="failed-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.failed') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Failed Designer Project?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onfailed" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Failed</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan --}}

	@can('revision-designer')
	{{-- Revision Designer --}}
	<div id="revision-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.revision') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Revision Designer Project</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Description <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="description" name="description" class="form-control {{$errors->first('description') != '' ? 'parsley-error' : ''}}">{{ old('description') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('description') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="urgent" class="control-label col-md-3 col-sm-3 col-xs-12">
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" name="urgent" value="1" @if(old('urgent') == 1) checked @endif>Urgent</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('urgent') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="designer_id-onrevision" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Designer List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						@if (in_array(Auth::user()->position, explode(', ', $sales_position->value)) || in_array(Auth::id(), explode(', ', $sales_user->value)))
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allSales-designer')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						@else
						@can('allSales-designer')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						@endif
						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>

					<select class="form-control" name="f_designer" onchange="this.form.submit()">
						@if (in_array(Auth::user()->position, explode(', ', $designer_position->value)) || in_array(Auth::id(), explode(', ', $designer_user->value)))
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_designer == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allDesigner-designer')
							<option value="all" {{ $request->f_designer == 'all' ? 'selected' : '' }}>All Designer</option>
						@endcan
						@else
						@can('allDesigner-designer')
							<option value="all" {{ $request->f_designer == 'all' ? 'selected' : '' }}>All Designer</option>
						@endcan
						@endif
						
						@foreach($designer as $list)
						<option value="{{ $list->id }}" {{ $request->f_designer == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>

					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>

					<select name="f_urgent" class="form-control" onchange="this.form.submit()">
						<option value="" @if($request->f_urgent === '') selected @endif>All Status Urgent</option>
						<option value="1" @if($request->f_urgent == 1) selected @endif>Urgent</option>
					</select>

					<input type="hidden" name="tab" value="{{ $request->tab }}">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.designer.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-designer')
					<a href="{{ route('backend.designer.create') }}" class="btn btn-default">Create</a>
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
		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li role="presentation" class="{{ $request->tab === 'not_finish-tab' || $request->tab == '' ? 'active' : ''}}">
					<a href="#not_finish" id="not_finish-tab" data-toggle="tab" aria-expanded="true" class="tab-active">Progress</a>
				</li>
				<li role="presentation" class="{{ $request->tab === 'finish-tab' ? 'active' : ''}}">
					<a href="#finish" id="finish-tab" data-toggle="tab" aria-expanded="false" class="tab-active">Finish</a>
				</li>
				
			</ul>
			<div id="dashboardTabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade {{ $request->tab === 'not_finish-tab' || $request->tab == '' ? 'active in' : ''}}" id="not_finish">
					<table class="table table-bordered no-footer" id="datatableProgress">
						<thead>
							<tr role="row">
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th>View</th>

								<th></th>
								<th>Datetime</th>

								<th>Status Designer</th>
								<th>Status Sales</th>

								<th>Result Project</th>
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
							</tr>
						</tfoot>
					</table>

					<div class="ln_solid"></div>

					<h2>Finished Today</h2>

					<table class="table table-bordered no-footer" id="datatableFinishToday">
						<thead>
							<tr role="row">
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th>View</th>

								<th></th>
								<th>Datetime</th>

								<th>Status Designer</th>
								<th>Status Sales</th>

								<th>Result Project</th>
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
							</tr>
						</tfoot>
					</table>
				</div>
				<div role="tabpanel" class="tab-pane fade {{ $request->tab === 'finish-tab' ? 'active in' : ''}}" id="finish">
					<table class="table table-bordered no-footer" id="datatableFinish">
						<thead>
							<tr role="row">
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th>View</th>

								<th></th>
								<th>Datetime</th>

								<th>Status Designer</th>
								<th>Status Sales</th>

								<th>Result Project</th>
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
							</tr>
						</tfoot>
					</table>
				</div>
				
			</div>
		</div>
		

		

		
			
	</div>
	

@endsection