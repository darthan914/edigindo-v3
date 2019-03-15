@extends('backend.layout.master')

@section('title')
	Lokasi Asal Barang List
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
				url: "{{ route('backend.stock.datatablesStockPlace') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				// {data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'name', sClass: 'nowrap-cell'},
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

		$('#datatable').on('click', '.editStockPlace-stock', function(){
			$('#editStockPlace-stock input[name=id]').val($(this).data('id'));
			$('#editStockPlace-stock input[name=name]').val($(this).data('name'));
		});

		$('#datatable').on('click', '.deleteStockPlace-stock', function(){
			$('#deleteStockPlace-stock input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('createStockPlace-stock-error'))
		$('#createStockPlace-stock').modal('show');
		@endif

		@if(Session::has('editStockPlace-stock-error'))
		$('#editStockPlace-stock').modal('show');
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
	@can('createStockPlace-stock')
	{{-- Buat Lokasi Asal Barang --}}
	<div id="createStockPlace-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.storeStockPlace') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Buat Lokasi Asal Barang</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Nama <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('editStockPlace-stock')
	{{-- Edit Lokasi Asal Barang --}}
	<div id="editStockPlace-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.updateStockPlace') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Lokasi Asal Barang</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Nama <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Update</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('deleteStockPlace-stock')
	{{-- Hapus Lokasi Asal Barang --}}
	<div id="deleteStockPlace-stock" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.stock.deleteStockPlace') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Hapus Lokasi Asal Barang?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Hapus</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Lokasi Asal Barang List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				{{-- <form method="post" id="action" action="{{ route('backend.stock.actionStockPlace') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')"> --}}
				<form method="post" id="action" action="#" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('createStockPlace-stock')
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#createStockPlace-stock">Buat</button>
					@endif
					{{-- <select class="form-control" name="action">
						<option value="enable">Enable</option>
						<option value="disable">Disable</option>
						<option value="delete">Hapus</option>
					</select>
					<button type="submit" class="btn btn-success">Terapkan</button> --}}
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					{{-- <th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th> --}}
					<th>Nama</th>
					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					{{-- <td></td> --}}
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection