@extends('backend.layout.master')

@section('title')
	AR Model List
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
				url: "{{ route('backend.arModel.datatables') }}",
				type: "POST",
				data: {
			    	f_user : $('*[name=f_user]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'phone', sClass: 'nowrap-cell'},

				{data: 'name', sClass: 'nowrap-cell'},
				{data: 'name_game_object', sClass: 'nowrap-cell'},
				{data: 'active', sClass: 'nowrap-cell'},

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

		$('#datatable').on('click', '.delete-arModel', function(){
			$('.arModel_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.active-arModel', function(){
			$('#active-arModel input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.inactive-arModel', function(){
			$('#inactive-arModel input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pdf-arModel', function(){
			$('#pdf-arModel input[name=id]').val($(this).data('id'));
			$('#pdf-arModel').submit();
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
	<form class="form-horizontal form-label-left" id="pdf-arModel" action="{{ route('backend.arModel.pdf') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id"></form>

	@can('delete-arModel')
	{{-- Delete AR Model --}}
	<div id="delete-arModel" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.arModel.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete AR Model?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="arModel_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('active-arModel')
	{{-- Active Form AR Model List --}}
	<div id="active-arModel" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.arModel.active') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Active AR Model List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Active</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Inactive Form AR Model List --}}
	<div id="inactive-arModel" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.arModel.active') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Inactive AR Model List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-dark">Inactive</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>AR Model List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_user" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allUser-arModel')
							<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All User</option>
						@endcan
						
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.arModel.action') }}" class="form-inline text-right" onsubmit="return active('Are you sure to apply this selected?')">
					@can('create-arModel')
					<a href="{{ route('backend.arModel.create') }}" class="btn btn-default">Create</a>
					@endif
					<select class="form-control" name="action">
						<option value="active">Enable</option>
						<option value="incative">Disable</option>
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

					<th>Owner</th>
					<th>Code</th>

					<th>Name Project</th>
					<th>Name Game Object</th>
					<th>Active</th>

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