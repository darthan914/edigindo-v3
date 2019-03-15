@extends('backend.layout.master')

@section('title')
	Division List
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
				url: "{{ route('backend.division.datatables') }}",
				type: "post",
				data: {
					search : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'name'},
				{data: 'active'},
				{data: 'client_available'},
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

		$('#datatable').on('click', '.delete-division', function(){
			$('.division_id-ondelete').val($(this).data('id'));
		});
		$('#datatable').on('click', '.active-division', function(){
			$('.division_id-onactive').val($(this).data('id'));
		});
		$('#datatable').on('click', '.inactive-division', function(){
			$('.division_id-oninactive').val($(this).data('id'));
		});

		$('#datatable').on('click', '.activeClient-division', function(){
			$('.division_id-onactive').val($(this).data('id'));
		});
		$('#datatable').on('click', '.inactiveClient-division', function(){
			$('.division_id-oninactive').val($(this).data('id'));
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

	<h1>Division List</h1>
	{{-- Delete Division --}}
	<div id="delete-division" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.division.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Division?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="division_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Active Division --}}
	<div id="active-division" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.division.active') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Active Division?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="division_id-onactive" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Active</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Inactive Division --}}
	<div id="inactive-division" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.division.active') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Inactive Division?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="division_id-oninactive" value="{{old('id')}}">
						<button type="submit" class="btn btn-dark">Inactive</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Active Division --}}
	<div id="activeClient-division" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.division.activeClient') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Active Client Division?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="division_id-onactive" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Active</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Inactive Division --}}
	<div id="inactiveClient-division" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.division.activeClient') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Client Division?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="division_id-oninactive" value="{{old('id')}}">
						<button type="submit" class="btn btn-dark">Inactive</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.division.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to take this action?')">
					@can('create-division', App\Division::class)
					<a href="{{ route('backend.division.create') }}" class="btn btn-default">Create</a>
					@endcan
					<select class="form-control" name="action">
						<option value="enable">Enable</option>
						<option value="disable">Disable</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>

		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Name</th>
					<th>Status</th>
					<th>Client Available</th>
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