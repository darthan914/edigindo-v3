@extends('backend.layout.master')

@section('title')
	Delivery View Distance Client
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.delivery.datatablesViewDistance') }}",
				type: "post",
				data: {
			    	f_max_distance : $('*[name=f_max_distance]').val(),
				},
			},
			columns: [
				{data: 'name'},
				{data: 'address'},
				{data: 'distance'},
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

	<h1>Delivery View Distance Client</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<input type="text" name="f_max_distance" class="form-control" onchange="this.form.submit()" value="{{ $request->f_max_distance }}"  style="width: 220px" placeholder="Maximum Distance">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Address</th>
					<th>Distance</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>


@endsection