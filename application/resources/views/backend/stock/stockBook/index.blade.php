@extends('backend.layout.master')

@section('title')
	Stock Book List
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
				url: "{{ route('backend.stock.datatablesStockBook') }}",
				type: "POST",
				data: {
			    	f_status   : $("*[name=f_status]").val()
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				{data: 'name_borrow', sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell'},
				{data: 'need', sClass: 'nowrap-cell'},
				{data: 'quantity_borrow', sClass: 'nowrap-cell'},

				{data: 'date_borrow', sClass: 'nowrap-cell'},
				{data: 'deadline_borrow', sClass: 'nowrap-cell'},
				{data: 'status', sClass: 'nowrap-cell'},

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

		

		$('#datatable').on('click', '.delete-stock', function(){
			$('.stock_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.return-stock', function(){
			$('#return-stock input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.borrow-stock', function(){
			$('#borrow-stock input[name=id]').val($(this).data('id'));
		});

		@if(Session::has('return-stock-error'))
			$('#return-stock').modal('show');
		@endif

		@if(Session::has('borrow-stock-error'))
			$('#borrow-stock').modal('show');
		@endif

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
	@can('delete-stock')
	{{-- Delete Stock Book --}}
	<div id="delete-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.deleteStockBook') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Hapus daftar peminjaman?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="stock_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Hapus</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('statusStockBook-stock')
	{{-- Active Form Stock Book List --}}
	<div id="return-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.statusStockBook') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Jumlah barang dikembalikan?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="returned_at" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Dikembalikan <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="date" id="returned_at" name="returned_at" class="form-control {{$errors->first('returned_at') != '' ? 'parsley-error' : ''}}" value="{{ old('returned_at') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('returned_at') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="quantity_return" class="control-label col-md-3 col-sm-3 col-xs-12">Jumlah <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="quantity_return" name="quantity_return" class="form-control {{$errors->first('quantity_return') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity_return') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quantity_return') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="status" value="RETURNED">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Borrow Form Stock Book List --}}
	<div id="borrow-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.statusStockBook') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Tambah Pinjam Barang?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="quantity_return" class="control-label col-md-3 col-sm-3 col-xs-12">Jumlah <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="quantity_return" name="quantity_return" class="form-control {{$errors->first('quantity_return') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity_return') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quantity_return') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="status" value="BORROWING">
						<button type="submit" class="btn btn-dark">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Stock Book List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_status" onchange="this.form.submit()">
						<option value="">Semua Status</option>
						@foreach($status as $key => $list)
						<option value="{{ $key }}" {{ $request->f_status == $key ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.stock.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('createStockBook-stock')
					<a href="{{ route('backend.stock.createStockBook') }}" class="btn btn-default">Buat</a>
					@endif
					<select class="form-control" name="action">
						{{-- <option value="returned">Set as Returned</option>
						<option value="borrowing">Set as Borrowing</option> --}}
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

					<th>Nama Peminjam</th>
					<th>Barang</th>
					<th>Keperluan</th>
					<th>Jumlah dipinjam</th>

					<th>Tanggal Pinjam</th>
					<th>Sampai</th>
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

					<td></td>
					<td></td>
					<td></td>

					<td></td>
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection