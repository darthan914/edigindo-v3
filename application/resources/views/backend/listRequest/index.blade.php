@extends('backend.layout.master')

@section('title')
	Request List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		@can('status-listRequest')
		$.post('{{ route('backend.listRequest.getStatus') }}', 
			{
				f_year       : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#status').empty();

				$('#status').append( "<tr><th>Division</th><th>Good</th><th>Bad</th>><th>No Respond</th><th>Today</th></tr>" );

				f_year  = $('*[name=f_year]').val();
				f_month = $('*[name=f_month]').val();
				
				$.each(data.status, function(index, list) {
					$('#status').append( "\
					<tr>\
						<td>"+ list.name +"</td>\
						<td>\
							<a href=\"{{ route('backend.listRequest.index') }}?f_division="+list.name+"&f_result=GOOD&f_status_feedback=FEEDBACK&f_year="+f_year+"&f_month="+f_month+"\">"+list.good+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.listRequest.index') }}?f_division="+list.name+"&f_result=BAD&f_status_feedback=FEEDBACK&f_year="+f_year+"&f_month="+f_month+"\">"+list.bad+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.listRequest.index') }}?f_division="+list.name+"&f_status_feedback=NO_FEEDBACK&f_year="+f_year+"&f_month="+f_month+"\">"+list.no_feedback+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.listRequest.index') }}?f_division="+list.name+"&f_status_feedback=NO_FEEDBACK&f_when=TODAY&f_year="+f_year+"&f_month="+f_month+"\">"+list.today+"</a>\
						</td>\
					</tr>\
					" );

				});
		});

		@endcan

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.listRequest.datatables') }}",
				type: "POST",
				data: {
			    	f_year            : $('*[name=f_year]').val(),
			    	f_month           : $('*[name=f_month]').val(),
			    	f_user            : $('*[name=f_user]').val(),
			    	f_respond         : $('*[name=f_respond]').val(),
			    	f_division        : $('*[name=f_division]').val(),
			    	f_type            : $('*[name=f_type]').val(),
			    	f_status_feedback : $('*[name=f_status_feedback]').val(),
			    	f_status_confirm  : $('*[name=f_status_confirm]').val(),
			    	f_id              : getUrlParameter('f_id'),
			    	s_item            : $('*[name=s_item]').val(),

			    	f_when            : getUrlParameter('f_when'),
			    	f_result          : getUrlParameter('f_result'),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'user_fullname', sClass: 'nowrap-cell'},
				{data: 'created_at', sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell'},
				{data: 'division', sClass: 'nowrap-cell'},
				{data: 'feedback', sClass: 'nowrap-cell'},
				{data: 'datetime_confirm', sClass: 'nowrap-cell'},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'respond_time', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'result', orderable: false, searchable: false, sClass: 'nowrap-cell'},
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
			scrollX: true,
		});


		$('#datatable').on('click', '.edit-listRequest', function(){
			$('#edit-listRequest *[name=type]').val($(this).data('type')).trigger('change');
			$('#edit-listRequest *[name=item]').val($(this).data('item'));
			$('#edit-listRequest *[name=division]').val($(this).data('division'));
			$('#edit-listRequest *[name=id]').val($(this).data('id'));

			if($(this).data('attachment') != "")
			{
				$('#edit-listRequest .edit-preview').attr('href', $(this).data('detail_file'));
				$('#edit-listRequest .edit-preview').html("<i class=\"fa fa-paperclip\" aria-hidden=\"true\"></i>");
			}
			else
			{
				$('#edit-listRequest .edit-preview').attr('href', "#");
				$('#edit-listRequest .edit-preview').html("");
			}
		});

		$('#datatable').on('click', '.delete-listRequest', function(){
			$('#delete-listRequest input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.feedback-listRequest', function(){
			$('#feedback-listRequest input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoFeedback-listRequest', function(){
			$('#undoFeedback-listRequest input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.confirm-listRequest', function(){
			$('#confirm-listRequest input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undoConfirm-listRequest', function(){
			$('#undoConfirm-listRequest input[name=id]').val($(this).data('id'));
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

	    $('select[name=f_user], select[name=f_respond], select[name=f_division]').select2({
		});

		@if(Session::has('create-listRequest-error'))
			$('#create-listRequest').modal('show');
		@endif

		@if(Session::has('edit-listRequest-error'))
			$('#edit-listRequest').modal('show');
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
	

	@can('create-listRequest')
	{{-- Create listRequest --}}
	<div id="create-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.store') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create List Request</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select name="type" class="form-control {{$errors->first('type') != '' ? 'parsley-error' : ''}}" value="{{ old('type') }}">
									<option value="">Select Type</option>
									@foreach($type as $key => $list)
									<option value="{{ $key }}" @if(old('type') == $list) selected @endif>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Item <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="division" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="division" name="division" class="form-control {{$errors->first('division') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($division as $list)
									<option value="{{ $list->code }}" @if(old('division') == $list->code) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('division') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="attachment" class="control-label col-md-3 col-sm-3 col-xs-12">Image
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="file" id="attachment" name="attachment" class="form-control {{$errors->first('attachment') != '' ? 'parsley-error' : ''}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('attachment') }}</li>
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

	@can('edit-listRequest')
	{{-- Edit listRequest --}}
	<div id="edit-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.update') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit List Request</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select name="type" class="form-control {{$errors->first('type') != '' ? 'parsley-error' : ''}}" value="{{ old('type') }}">
									<option value="">Select Type</option>
									@foreach($type as $key => $list)
									<option value="{{ $key }}" @if(old('type') == $list) selected @endif>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Item <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="division" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="division" name="division" class="form-control {{$errors->first('division') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($division as $list)
									<option value="{{ $list->code }}" @if(old('division') == $list->code) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('division') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="attachment" class="control-label col-md-3 col-sm-3 col-xs-12">File
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="file" id="attachment" name="attachment" class="form-control {{$errors->first('attachment') != '' ? 'parsley-error' : ''}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('attachment') }}</li>
								</ul>
								<a href="#" target="_new" class="edit-preview"></a>
								<label class="checkbox-inline"><input type="checkbox" name="remove_attachment">Remove Attachment</label>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="listRequest_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('delete-listRequest')
	{{-- Delete listRequest --}}
	<div id="delete-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.delete') }}" method="post" enctype="multipart/form-data">
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

	@can('feedback-listRequest')
	{{-- Feedback listRequest --}}
	<div id="feedback-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.feedback') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Feedback List Request</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="feedback" class="control-label col-md-3 col-sm-3 col-xs-12">Feedback <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="feedback" name="feedback" class="form-control {{$errors->first('feedback') != '' ? 'parsley-error' : ''}}">{{ old('feedback') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('feedback') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="listRequest_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('undoFeedback-listRequest')
	{{-- Undo Feedback listRequest --}}
	<div id="undoFeedback-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.undoFeedback') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Feedback List Request?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('confirm-listRequest')
	{{-- Confirm listRequest --}}
	<div id="confirm-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.confirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm List Request?</h4>
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
	@endcan

	@can('undoConfirm-listRequest')
	{{-- Undo Confirm listRequest --}}
	<div id="undoConfirm-listRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.listRequest.undoConfirm') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Confirm List Request?</h4>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan


	<h1>Request List</h1>
	<div class="x_panel" style="overflow: auto;">
		@can('status-listRequest')
		<table class="table table-bordered" style="font-size: small;">
			<tbody id="status">
				<tr>
					<th>Processing....</th>
				</tr>
			</tbody>
		</table>
		@endcan

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
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_user" onchange="this.form.submit()">
						
						@if((in_array(Auth::user()->position, explode(', ', $feedback_position->value)) || in_array(Auth::id(), explode(', ', $feedback_user->value))))

							<option value="">All Sales</option>
							@foreach($user as $list)
							<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
							@endforeach

						@else
							<option value="">My Request</option>
							<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
							@can('allUser-listRequest')
								<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All Sales</option>
								@foreach($user as $list)
								<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
								@endforeach
								
							@endcan

						@endif
					</select>

					<select class="form-control" name="f_respond" onchange="this.form.submit()">
						<option value="">All Respond</option>
						@foreach($respond as $list)
						<option value="{{ $list->id }}" {{ $request->f_respond == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>

					<select id="f_division" name="f_division" class="form-control" onchange="this.form.submit()">
						<option value="">All Division</option>
						@foreach($division as $list)
						<option value="{{ $list->code }}" @if($request->f_division == $list->code) selected @endif>{{ $list->name }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_type" onchange="this.form.submit()">
						<option value="">All Type</option>
						@foreach($type as $key => $list)
						<option value="{{ $key }}" @if($request->f_type  == $key) selected @endif>{{ $list }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_status_feedback" onchange="this.form.submit()">
						<option value="">All Status Feedback</option>
						@foreach($status_feedback as $key => $list)
						<option value="{{ $key }}" @if($request->f_status_feedback  == $key) selected @endif>{{ $list }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_status_confirm" onchange="this.form.submit()">
						<option value="">All Status Confirm</option>
						@foreach($status_confirm as $key => $list)
						<option value="{{ $key }}" @if($request->f_status_confirm  == $key) selected @endif>{{ $list }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.listRequest.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-listRequest')
					<button type="button" data-toggle="modal" data-target="#create-listRequest" class="btn btn-default">Create</button>
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

					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>From</th>
					<th>Post</th>
					<th>Item</th>
					<th>Division</th>

					<th>Feedback</th>
					<th>Confirm</th>
					<th>Action</th>

					<th>Respond Time</th>
					<th>Rating</th>

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