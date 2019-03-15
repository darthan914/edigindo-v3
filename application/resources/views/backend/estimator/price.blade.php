@extends('backend.layout.master')

@section('title')
	Estimator Price List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.estimator.datatablesPrice', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell', name: 'sales.fullname'},
				{data: 'value', sClass: 'nowrap-cell'},
				{data: 'note'},
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

		$('#datatable').on('click', '.deletePrice-estimator', function(){
			$('#deletePrice-estimator *[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.editPrice-estimator', function(){
			$('#editPrice-estimator *[name=id]').val($(this).data('id'));
			$('#editPrice-estimator *[name=item]').val($(this).data('item'));
			$('#editPrice-estimator *[name=value]').val($(this).data('value'));
			$('#editPrice-estimator *[name=note]').val($(this).data('note'));
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

	    @if(Session::has('storePrice-estimator-error'))
			$('#createPrice-estimator').modal('show');
		@endif

	    @if(Session::has('updatePrice-estimator-error'))
			$('#editPrice-estimator').modal('show');
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
	{{-- Delete Estimator --}}
	<div id="deletePrice-estimator" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.estimator.deletePrice') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Price?</h4>
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

	{{-- Create Price --}}
	<div id="createPrice-estimator" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.estimator.storePrice', $index) }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Price</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Item<span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Price<span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea type="text" id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="estimator_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Edit Price --}}
	<div id="editPrice-estimator" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.estimator.updatePrice') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Price</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Item<span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Price<span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea type="text" id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>


	<h1>Estimator Price List</h1>

	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="#" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="form-group">
					<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="sales_id" name="sales_id" class="form-control" value="{{ $index->sales->fullname }}" readonly>
					</div>
				</div>

				<div class="form-group">
					<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="name" name="name" class="form-control" value="{{ $index->name }}" readonly>
					</div>
				</div>

				<div class="form-group">
					<label for="no_estimator" class="control-label col-md-3 col-sm-3 col-xs-12">No Estimator <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="no_estimator" name="no_estimator" class="form-control" value="{{ $index->no_estimator }}" readonly>
					</div>
				</div>

				<div class="form-group">
					<label for="date" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="date" name="date" class="form-control" value="{{ date('d F Y', strtotime($index->created_at)) }}" readonly>
					</div>
				</div>

				<div class="form-group">
					<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Note 
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<textarea id="description" name="description" class="form-control" readonly>{{ $index->description }}</textarea>
					</div>
				</div>

				@if($index->photo)
				<div class="form-group">
					<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Photo <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<img src="{{ asset($index->photo) }}" style="width: 100px">
					</div>
				</div>
				@endif

				<div class="ln_solid"></div>

				<div class="form-group">
					<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
						<a class="btn btn-default" href="{{ route('backend.estimator') }}">Back</a>
					</div>
				</div>
			</div>
		</form>
	</div>


	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.estimator.actionPrice') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#createPrice-estimator">Create</button>
					<select class="form-control" name="action">
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
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>

					<th>Item</th>
					<th>Price</th>
					<th>Note</th>

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