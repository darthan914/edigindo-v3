@extends('backend.layout.master')

@section('title')
	Design Request List
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
				url: "{{ route('backend.designer.datatablesDesignCandidate') }}",
				type: "POST",
				data: {
			    	f_status   : $('*[name=f_status]').val(),
			    	f_urgent   : $('*[name=f_urgent]').val(),
			    	f_id       : getUrlParameter('f_id'),
				},
			},
			columns: [
				{data: 'title_request', sClass: 'nowrap-cell'},
				{data: 'note_request', sClass: 'nowrap-cell'},
				{data: 'division', sClass: 'nowrap-cell'},
				{data: 'budget', sClass: 'nowrap-cell'},
				{data: 'datetime_deadline', sClass: 'nowrap-cell'},
				{data: 'status_approval', sClass: 'nowrap-cell'},
				{data: 'my_design', sClass: 'nowrap-cell'},
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

		$('#datatable').on('click', '.deleteDesignCandidate-designer', function(){
			$('.designer_id-ondelete').val($(this).data('id'));
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
	@can('deleteDesignCandidate-designer')
	{{-- Delete Designer --}}
	<div id="deleteDesignCandidate-designer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designer.deleteDesignCandidate') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Your Design?</h4>
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

	<h1>Design Request List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					
					<select name="f_status" class="form-control" onchange="this.form.submit()">
						<option value="">All Status</option>
						@foreach($status as $list)
						<option value="{{ $list }}" @if($request->f_status == $list) selected @endif>{{$list}}</option>
						@endforeach
					</select>

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.designer.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">

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

		<table class="table table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					{{-- <th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th> --}}
					<th>Title</th>
					<th>Note</th>

					<th>Division</th>
					<th>Budget</th>
					<th>Deadline</th>

					<th>Status</th>
					<th>My Design</th>
					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					{{-- <td></td> --}}
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