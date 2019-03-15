@extends('backend.layout.master')

@section('title')
	Archive List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	function format ( d ) {
		html = '\
			<table class="table">\
				<tr>\
					<th width="20%">Last Data</th>\
					<td width="80%">'+d.data+'</td>\
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
				url: "{{ route('backend.archive.datatables') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				{
	                class:          "details-control",
	                orderable:      false,
	                data:           null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'source', sClass: 'nowrap-cell'},
				{data: 'action', sClass: 'nowrap-cell'},
				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'created_at', sClass: 'nowrap-cell'},
				{data: 'data', visible: false},
				{data: 'action2'},
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
	            row.child( format( row.data() ) ).show();
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

	    $('#datatable').on('click', '.recover-archive', function(){
			$('#recover-archive input[name=id]').val($(this).data('id'));
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
	
	<div id="recover-archive" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.archive.recover') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Recover Data?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Recover</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Archive List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					{{-- <th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th> --}}
					<th></th>
					<th>Source</th>
					<th>Action</th>
					<th>User Action</th>
					<th>Datetime Action</th>
					<th></th>
					<th></th>

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