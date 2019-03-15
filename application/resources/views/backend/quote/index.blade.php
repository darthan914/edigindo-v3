@extends('backend.layout.master')

@section('title')
Quote List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">

	function format ( d ) {
		html = '\
		<table class="table">\
			<tr>\
				<th width="20%">Company</th>\
				<td width="80%">'+d.company+'</td>\
			</tr>\
			<tr>\
				<th width="20%">Region</th>\
				<td width="80%">'+d.region+'</td>\
			</tr>\
			<tr>\
				<th width="20%">Interested</th>\
				<td width="80%">'+d.interested+'</td>\
			</tr>\
			<tr>\
				<th width="20%">services</th>\
				<td width="80%">'+d.services+'</td>\
			</tr>\
			<tr>\
				<th width="20%">Budget</th>\
				<td width="80%">'+d.budget+'</td>\
			</tr>\
			<tr>\
				<th width="20%">Description</th>\
				<td width="80%">'+d.project_description+'</td>\
			</tr>\
		</table>\
		';

		return html;
	}

	$(function() {
		
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.quote.datatables') }}",
				type: "POST",
				data: {
					f_id       : getUrlParameter('f_id'),
				},
			},
			columns: [
			{
				class:          "details-messages",
				orderable:      false,
				data:           null,
				defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
			},
			{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
			{data: 'created_at', sClass: 'nowrap-cell'},
			{data: 'fullname', sClass: 'nowrap-cell'},
			{data: 'phone', sClass: 'nowrap-cell'},
			{data: 'email', sClass: 'nowrap-cell'},
			{data: 'company', sClass: 'nowrap-cell'},
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

		$('#datatable tbody').on( 'click', 'td.details-messages > button', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );

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

		$('#datatable').on('click', '.delete-quote', function(){
			$('#delete-quote input[name=id]').val($(this).data('id'));
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
@can('delete-quote')
{{-- Delete SPK --}}
<div id="delete-quote" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-horizontal form-label-left" action="{{ route('backend.quote.delete') }}" method="post" enctype="multipart/form-data">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Quote?</h4>
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

<h1>Quote List</h1>
<div class="x_panel" style="overflow: auto;">
	<div class="row">
		<div class="col-md-6">
			<form class="form-inline" method="get">

			</form>
		</div>
		<div class="col-md-6">
			<form method="post" id="action" action="{{ route('backend.quote.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">

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
				<th>View</th>

				<th nowrap>
					<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
				</th>
				<th>Post</th>
				<th>Name</th>
				<th>Phone</th>

				<th>Email</th>
				<th>Position</th>
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


@endsection