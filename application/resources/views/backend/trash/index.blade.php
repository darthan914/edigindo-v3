@extends('backend.layout.master')

@section('title')
	{{ $name }} Deleted List
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
				url: "{{ $url }}",
				type: "post",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'name'},
				{data: 'deleted_at'},
				{data: 'action', orderable: false, searchable: false},
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

		$('#datatable').on('click', '.delete-trash', function(){
			$('#delete-trash input[name=id]').val($(this).data('id'));
			$('#delete-trash input[name=model]').val($(this).data('model'));
		});
		
		$('#datatable').on('click', '.restore-trash', function(){
			$('#restore-trash input[name=id]').val($(this).data('id'));
			$('#restore-trash input[name=model]').val($(this).data('model'));
		});

		$('.delete-trash').click(function(){
			$('#delete-trash input[name=id]').val($(this).data('id'));
			$('#delete-trash input[name=model]').val($(this).data('model'));
		});

		$('.restore-trash').click(function(){
			$('#restore-trash input[name=id]').val($(this).data('id'));
			$('#restore-trash input[name=model]').val($(this).data('model'));
		});
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('content')

	<h1>{{ $name }} Deleted List</h1>
	@can('delete-trash')
	{{-- Delete --}}
	<div id="delete-trash" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.trash.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Data?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="model" value="{{old('model')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('restore-trash')
	{{-- Restore --}}
	<div id="restore-trash" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.trash.restore') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Restore Data?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="model" value="{{old('model')}}">
						<button type="submit" class="btn btn-success">Restore</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan


	<div class="x_panel" style="overflow: auto;">
		<form method="post" id="action" action="{{ route('backend.trash.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to take this action?')">
			@can('restore-trash')
			<button type="button" class="btn btn-success restore-trash" data-toggle="modal" data-target="#restore-trash" data-id="-1" data-model="{{ $model }}">Restore All</button>
			@endcan

			@can('delete-trash')
			<button type="button" class="btn btn-danger delete-trash" data-toggle="modal" data-target="#delete-trash" data-id="-1" data-model="{{ $model }}">Delete All</button>
			@endcan

			<select class="form-control" name="action">
				<option value="delete">Delete</option>
				<option value="delete">Restore</option>
			</select>
			<input type="hidden" name="model" value="{{ $model }}">
			<button type="submit" class="btn btn-success">Apply Selected</button>
		</form>

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>Name</th>
					<th>Deleted At</th>
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