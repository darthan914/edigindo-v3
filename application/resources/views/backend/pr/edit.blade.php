@extends('backend.layout.master')

@section('title')
	Edit/View PR - {{$index->no_pr}} - {{$index->barcode}}
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=date], input[name=deadline], input[name=deadline]').daterangepicker({
			timePicker24Hour: true,
		    singleDatePicker: true,
		    showDropdowns: true,
		    timePicker: true,
		    format: 'DD MMMM YYYY H:mm'
		});

		$('button[data-dismiss=modal]').click(function(event) {
			$(".parsley-required").empty();
			$("*").removeClass('parsley-error');
		});

		function format ( d ) {
		    return d.material;
		}

		var table = $('#datatable-detail').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatablesDetail', $index) }}",
				type: "POST",
				data: {
			    	id  : {{ $index->id }},
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'item'},
				{data: 'quantity'},
				{data: 'purchasing_id'},
				{data: 'deadline'},
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
			scrollY: "400px",
	        
		});

		// Add event listener for opening and closing details
	    $('#datatable-detail tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( format(row.data()) ).show();
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

		$('#datatable-detail').on('click', '.edit-detail', function(){
			$('#edit-detail *[name=id]').val($(this).data('id'));
			
			$('#edit-detail *[name=item]').val($(this).data('item'));
			$('#edit-detail *[name=quantity]').val($(this).data('quantity'));
			$('#edit-detail *[name=unit]').val($(this).data('unit'));
			$('#edit-detail *[name=deadline]').val($(this).data('deadline'));
			$('#edit-detail *[name=purchasing_id]').val($(this).data('purchasing_id')).trigger('change');
			$('#edit-detail *[name=no_rekening]').val($(this).data('no_rekening'));
			$('#edit-detail *[name=value]').val($(this).data('value'));
		});

		$('#datatable-detail').on('click', '.delete-detail', function(){
			$('.detail_id-ondelete').val($(this).data('id'));
		});

		$('button.spk-item').click(function(){
			$.post('{{ route('backend.pr.getSpkItem') }}', {id: $('select[name=spk_id]').val()}, function(data) {
				$('.item-list').empty();

				$('.item-list').append('\
					<tr>\
						<th>No PR</th>\
						<th>Item</th>\
						<th>Name Order</th>\
						<th>Quantity</th>\
					</tr>\
				');
				console.log(data);
				$.each(data, function(i, field) {
					$('.item-list').append('\
					<tr>\
						<td>'+ field.no_pr +'</td>\
						<td>'+ field.item +'</td>\
						<td>'+ field.name +'</td>\
						<td>'+ field.quantity + ' ' + field.unit +'</td>\
					</tr>\
				');
				});
			});
			
		});

		@if(Session::has('create-detail-error'))
		$('#create-detail').modal('show');
		@endif
		@if(Session::has('edit-detail-error'))
		$('#edit-detail').modal('show');
		@endif
		@if(Session::has('spk-pdf-error'))
		$('#spk-pdf').modal('show');
		@endif
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	td.details-control {
	    cursor: pointer;
	}
</style>
@endsection

@section('content')

	<h1>Edit/View PR - {{$index->no_pr}} - {{$index->barcode}}</h1>

	{{-- Create Detail --}}
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.storeDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Detail</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Item Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						@if($index->type == 'PROJECT' || $index->type == 'OFFICE')
							<div class="form-group">
								<label for="quantity" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="quantity" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('quantity') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<label for="unit" class="control-label col-md-3 col-sm-3 col-xs-12">Units <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="unit" name="unit" class="form-control {{$errors->first('unit') != '' ? 'parsley-error' : ''}}" value="{{ old('unit', 'Pcs') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('unit') }}</li>
									</ul>
								</div>
							</div>
						@endif

						<div class="form-group">
							<label for="deadline" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="deadline" name="deadline" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('deadline') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('deadline') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="purchasing_id" class="control-label col-md-3 col-sm-3 col-xs-12">{{ $index->type != 'PAYMENT' ? 'Purchasing' : 'Finance'}}  <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="purchasing_id" name="purchasing_id" class="form-control {{$errors->first('purchasing_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Purchasing">
									<option value=""></option>
									@foreach($purchasing as $list)
									<option value="{{ $list->id }}" @if(old('purchasing_id') == $list->id) selected @endif>{{ $list->fullname }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('purchasing_id') }}</li>
								</ul>
							</div>
						</div>

						@if($index->type == 'PAYMENT')
						<div class="form-group">
							<label for="no_rekening" class="control-label col-md-3 col-sm-3 col-xs-12">Rekening
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="no_rekening" name="no_rekening" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('no_rekening') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_rekening') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="value" name="value" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>
						@endif
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Edit Detail --}}
	<div id="edit-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.updateDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Detail</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="item-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Item Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="item-edit" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('item') }}</li>
								</ul>
							</div>
						</div>

						@if($index->type == 'PROJECT' || $index->type == 'OFFICE')
						<div class="form-group">
							<label for="quantity-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="quantity-edit" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quantity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="unit-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Units <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="unit-edit" name="unit" class="form-control {{$errors->first('unit') != '' ? 'parsley-error' : ''}}" value="{{ old('unit') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('unit') }}</li>
								</ul>
							</div>
						</div>
						@endif

						<div class="form-group">
							<label for="deadline-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="deadline-edit" name="deadline" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('deadline') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('deadline') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="purchasing_id-edit" class="control-label col-md-3 col-sm-3 col-xs-12">{{ $index->type != 'PAYMENT' ? 'Purchasing' : 'Finance'}} <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="purchasing_id-edit" name="purchasing_id" class="form-control {{$errors->first('purchasing_id') != '' ? 'parsley-error' : ''}} select2full">
									<option value=""></option>
									@foreach($purchasing as $list)
									<option value="{{ $list->id }}" @if(old('purchasing_id') == $list->id) selected @endif>{{ $list->fullname }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('purchasing_id') }}</li>
								</ul>
							</div>
						</div>

						@if($index->type == 'PAYMENT')
						<div class="form-group">
							<label for="no_rekening" class="control-label col-md-3 col-sm-3 col-xs-12">Rekening
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="no_rekening" name="no_rekening" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('no_rekening') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_rekening') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="value" name="value" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>
						@endif

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="detail_id-onedit" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Delete Detail --}}
	<div id="delete-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.deleteDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="detail_id-ondelete" value="">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- SPK PDF --}}
	<div id="spk-pdf" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.pdf') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size', 'A4') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation', 'portrait') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation', 'portrait') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Item SPK --}}
	<div id="spk-item" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Item Comfirmed</h4>
					</div>
					<div class="modal-body">
						<table class="table item-list">
							

						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="x_panel">
		<div class="x_title">
			<ul class="nav panel_toolbox">
				<form method="get" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">

					@can('pdf-pr')
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#spk-pdf"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download</button>
					@endcan
				</form>
	        </ul>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<form class="form-horizontal form-label-left" action="{{ route('backend.pr.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', $index->name ) }}">
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('name') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="no_pr" class="control-label col-md-3 col-sm-3 col-xs-12">No PR <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="no_pr" name="no_pr" class="form-control {{$errors->first('no_pr') != '' ? 'parsley-error' : ''}}" value="{{ old('no_pr', $index->no_pr) }}" readonly>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('no_pr') }}</li>
						</ul>
					</div>
				</div>

				@if($index->type == 'PROJECT')
				<div class="form-group">
					<label for="spk_id" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<div class="input-group">
							<select id="spk_id" name="spk_id" class="form-control {{$errors->first('spk_id') != '' ? 'parsley-error' : ''}}">
								<option value=""></option>
								@foreach($spk as $list)
								<option value="{{ $list->id }}" @if(old('spk_id', $index->spk_id) == $list->id) selected @endif>{{ $list->no_spk }} - {{ $list->name }}</option>
								@endforeach
							</select>
							<span class="input-group-btn">
		                        <button type="button" class="btn btn-primary spk-item" data-toggle="modal" data-target="#spk-item">Check</button>
		                    </span>
						</div>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('spk_id') }}</li>
						</ul>
					</div>
				</div>
				@endif


				@if($index->type == 'PROJECT' || $index->type == 'PAYMENT')
				<div class="form-group">
					<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2">
							<option value=""></option>
							@foreach($division as $list)
							<option value="{{ $list->id }}" @if(old('division_id', $index->division_id) == $list->id) selected @endif>{{ $list->name }}</option>
							@endforeach
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('division_id') }}</li>
						</ul>
					</div>
				</div>
				@endif
				
				<div class="ln_solid"></div>
				<div class="form-group">
					<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
						{{ csrf_field() }}
						<a class="btn btn-primary" href="{{ route('backend.pr') }}">Back</a>
						@if(Auth::user()->can('update-pr', $index))
						<button type="submit" class="btn btn-success">Submit</button>
						@endif
					</div>
				</div>	
			</form>
		</div>
	</div>

	<div class="x_panel">
		<div class="x_title">

			<h2>Detail</h2>
			<ul class="nav panel_toolbox">
				@if(Auth::user()->can('update-pr', $index))
				<form method="post" id="action-detail" action="{{ route('backend.pr.actionDetail') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">

					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-detail">Create</button>
					<select class="form-control" name="action">
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
				@endif
	        </ul>
	        <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
			<table class="table table-striped table-bordered" id="datatable-detail">
				<thead>
					<tr>
						<th nowrap>
							<label class="checkbox-inline"><input type="checkbox" data-target="check-detail" class="check-all" id="check-all">S</label>
						</th>
						<th>Item</th>
						<th>Quantity</th>
						<th>Purchasing</th>
						<th>Deadline</th>
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
					</tr>
				</tfoot>
			</table>

		</div>
	</div>


@endsection