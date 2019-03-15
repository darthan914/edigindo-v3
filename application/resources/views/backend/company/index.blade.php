@extends('backend.layout.master')

@section('title')
	Company List
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
				url: "{{ route('backend.company.datatables') }}",
				type: "post",
				data : {
					search : $('*[name=search]').val(),
				}
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
				{data: 'name'},
				{data: 'status'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
			
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

		$('#datatable').on('click', '.delete-company', function(){
			$('.company_id-ondelete').val($(this).data('id'));
		});
		$('#datatable').on('click', '.open-company', function(){
			$('.company_id-onopen').val($(this).data('id'));
		});
		$('#datatable').on('click', '.lock-company', function(){
			$('.company_id-onlock').val($(this).data('id'));
		});

		$('#datatable').on('click', '.confirm-company', function(){
			$('#confirm-company input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.unconfirm-company', function(){
			$('#unconfirm-company input[name=id]').val($(this).data('id'));
		});
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('content')

	<h1>Company List</h1>

	<div class="x_panel" style="overflow: auto;">
		@can('delete-company')
		{{-- Delete Company --}}
		<div id="delete-company" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form-horizontal form-label-left" action="{{ route('backend.company.delete') }}" method="post" enctype="multipart/form-data">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Delete Company?</h4>
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

		@can('lock-company')
		{{-- Active User --}}
		<div id="open-company" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form-horizontal form-label-left" action="{{ route('backend.company.lock') }}" method="post" enctype="multipart/form-data">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Open Company?</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							{{ csrf_field() }}
							<input type="hidden" name="id" class="company_id-onopen" value="{{old('id')}}">
							<button type="submit" class="btn btn-success">Open</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		{{-- Inactive User --}}
		<div id="lock-company" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form-horizontal form-label-left" action="{{ route('backend.company.lock') }}" method="post" enctype="multipart/form-data">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Lock Company?</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							{{ csrf_field() }}
							<input type="hidden" name="id" class="company_id-onlock" value="{{old('id')}}">
							<button type="submit" class="btn btn-dark">Lock</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		@endcan

		@can('confirm-company')
		{{-- Confirm Company List --}}
		<div id="confirm-company" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form-horizontal form-label-left" action="{{ route('backend.company.confirm') }}" method="post" enctype="multipart/form-data">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Confirm Company List?</h4>
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

		{{-- Unconfirm Company List --}}
		<div id="unconfirm-company" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form-horizontal form-label-left" action="{{ route('backend.company.confirm') }}" method="post" enctype="multipart/form-data">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Unconfirm Company List?</h4>
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
		@endcan
	
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.company.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to take this action?')">
					@can('create-company')
					<a href="{{ route('backend.company.create') }}" class="btn btn-default">Create</a>
					@endcan
					<select class="form-control" name="action">
						<option value="confirm">Confirm</option>
						<option value="unconfirm">Unconfirm</option>
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>

		<div class="ln_solid"></div>
		

		<table class="table table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>View</th>
					<th>Name</th>
					<th>Status</th>
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
				</tr>
			</tfoot>
		</table>
	</div>
	
@endsection