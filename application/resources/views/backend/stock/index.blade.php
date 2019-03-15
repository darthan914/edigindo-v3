@extends('backend.layout.master')

@section('title')
	Stock List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js"></script>

<script type="text/javascript">
	$(function() {
		var template = Handlebars.compile($("#details-template").html());

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.stock.datatables') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{
	                className  : "details-control",
	                orderable  : false,
	                searchable : false,
	                data       : null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },

				{data: 'photo', sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell'},
				{data: 'place', sClass: 'nowrap-cell'},
				{data: 'quantity', sClass: 'nowrap-cell'},
				{data: 'quantity_borrow', sClass: 'nowrap-cell'},
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

		// Add event listener for opening and closing details
	    $('#datatable tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	        var tableId = 'posts-' + row.data().id;
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child(template(row.data())).show();
	            initTable(tableId, row.data().id,row.data());
	            tr.addClass('shown');
	            tr.next().find('td').addClass('no-padding bg-gray');
	        }
	    } );

	    function initTable(tableId, id, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.stock.datatablesStockBook') }}",
					type: "POST",
					data: {
						f_stock_id : id,
						f_status   : "BORROWING"
					},
				},
	            columns: [
	                {data: 'name_borrow', sClass: 'nowrap-cell'},
					{data: 'quantity_borrow', sClass: 'nowrap-cell'},

					{data: 'date_borrow', sClass: 'nowrap-cell'},
					{data: 'deadline_borrow', sClass: 'nowrap-cell'},

					{data: 'note', sClass: ''},
	            ],
	            scrollY: "200px",

	            
	        })
	    }

		$('#datatable').on('click', '.delete-stock', function(){
			$('.stock_id-ondelete').val($(this).data('id'));
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

<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-bordered details-table" id="posts-@{{id}}">
        <thead>
        <tr>
            <th>Nama Peminjam</th>
			<th>Jumlah Pinjam</th>
            <th>Tanggal Pinjam</th>
            <th>Sampai</th>
            <th>Catatan</th>
        </tr>
        </thead>
    </table>
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
	@can('delete-stock')
	{{-- Delete Stock --}}
	<div id="delete-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Stock?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="stock_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Stock List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.stock.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-stock')
					<a href="{{ route('backend.stock.create') }}" class="btn btn-default">Buat</a>
					@endif
					<select class="form-control" name="action">
						{{-- <option value="enable">Enable</option>
						<option value="disable">Disable</option> --}}
						<option value="delete">Hapus</option>
					</select>
					<button type="submit" class="btn btn-success">Terapkan dipilih</button>
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

					<th>Lihat</th>

					<th>Gambar</th>
					<th>Barang</th>
					<th>Asal</th>
					<th>Jumlah</th>
					<th>Jumlah dipinjam</th>

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