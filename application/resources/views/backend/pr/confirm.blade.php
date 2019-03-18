@extends('backend.layout.master')

@section('title')
	Confirm Item
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$.post('{{ route('backend.pr.getStatusConfirmProject') }}', 
			{
				f_year       : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#status-project').empty();

				$('#status-project').append( "<tr><th>Name</th><th>Pending</th><th>Cancel</th><th>Stock</th><th>< -4 Day</th><th>-3 Day</th><th>-2 Day</th><th>-1 Day</th><th>Today</th></tr>" );

				f_year  = $('*[name=f_year]').val();
				f_month = $('*[name=f_month]').val();
				tab = 'PROJECT';
				
				$.each(data.status, function(index, list) {
					$('#status-project').append( "\
					<tr>\
						<td>"+ list.name +"</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=PENDING&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.pending+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=CANCEL&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.cancel+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=STOCK&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.stock+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=4&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_4_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=3&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_3_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=2&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_2_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=1&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_1_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=0&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.today+"</a>\
						</td>\
					</tr>\
					" );

				});
		});

		$.post('{{ route('backend.pr.getStatusConfirmPayment') }}', 
			{
				f_year       : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#status-payment').empty();

				$('#status-payment').append( "<tr><th>Name</th><th>Pending</th><th>Cancel</th><th>Stock</th><th>< -4 Day</th><th>-3 Day</th><th>-2 Day</th><th>-1 Day</th><th>Today</th></tr>" );

				f_year  = $('*[name=f_year]').val();
				f_month = $('*[name=f_month]').val();
				tab = 'PAYMENT';
				
				$.each(data.status, function(index, list) {
					$('#status-payment').append( "\
					<tr>\
						<td>"+ list.name +"</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=PENDING&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.pending+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=CANCEL&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.cancel+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=STOCK&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.stock+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=4&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_4_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=3&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_3_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=2&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_2_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=1&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_1_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_status=none&f_day=0&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.today+"</a>\
						</td>\
					</tr>\
					" );

				});
		});

		var table_project = $('#datatable-project').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatablesConfirm') }}",
				type: "post",
				data: {
					f_year       : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
					f_purchasing : $('*[name=f_purchasing]').val(),
					f_status     : $('*[name=f_status]').val(),
					f_day        : $('*[name=f_day]').val(),
					f_value      : $('*[name=f_value]').val(),
					f_audit      : $('*[name=f_audit]').val(),
					f_finance    : $('*[name=f_finance]').val(),
					f_id         : getUrlParameter('f_id'),
					search       : $('*[name=search]').val(),
					type         : 'PROJECT',
				},
			},
			columns: [
				{data: 'info', sClass: 'nowrap-cell'},

				{data: 'purchasing'},
				{data: 'po'},
				{data: 'action'},
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
			dom: '<lip<tr>ip>',
			// scrollY: "400px",
			scrollX: true,
		});

		var tablePayment = $('#datatable-payment').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatablesConfirm') }}",
				type: "post",
				data: {
					f_year       : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
					f_purchasing : $('*[name=f_purchasing]').val(),
					f_status     : $('*[name=f_status]').val(),
					f_day        : $('*[name=f_day]').val(),
					f_value      : $('*[name=f_value]').val(),
					f_audit      : $('*[name=f_audit]').val(),
					f_finance    : $('*[name=f_finance]').val(),
					f_id         : getUrlParameter('f_id'),
					search       : $('*[name=search]').val(),
					type         : 'PAYMENT',
				},
			},
			columns: [
				{data: 'info', sClass: 'nowrap-cell'},

				{data: 'purchasing'},
				{data: 'po'},
				{data: 'action'},
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
			dom: '<lip<tr>ip>',
			// scrollY: "400px",
			scrollX: true,
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

		$('#datatable-project, #datatable-payment').on('click', '.delete-pr', function(){
			$('.pr_id-ondelete').val($(this).data('id'));
		});

		$('#datatable-project').on('click', '.add-poProject', function(){
			$('#add-poProject input[name=pr_detail_id]').val($(this).data('id'));
		});

		$('#datatable-project').on('click', '.edit-poProject', function(){
			$('#edit-poProject input[name=id]').val($(this).data('id'));
			$('#edit-poProject input[name=quantity]').val($(this).data('quantity'));
			$('#edit-poProject input[name=no_po]').val($(this).data('no_po'));
			$('#edit-poProject input[name=date_po]').val($(this).data('date_po'));
			$('#edit-poProject input[name=type]').val($(this).data('type')).trigger('change');
			$('#edit-poProject input[name=supplier_id]').val($(this).data('supplier_id')).trigger('change');
			$('#edit-poProject input[name=name_supplier]').val($(this).data('name_supplier'));
			$('#edit-poProject input[name=value]').val($(this).data('value'));
		});

		$('#datatable-payment').on('click', '.add-poPayment', function(){
			$('#add-poPayment input[name=pr_detail_id]').val($(this).data('id'));
			$('#add-poPayment input[name=value]').val($(this).data('value'));
		});

		$('#datatable-payment').on('click', '.edit-poPayment', function(){
			$('#edit-poPayment input[name=id]').val($(this).data('id'));
			$('#edit-poPayment input[name=date_po]').val($(this).data('date_po'));
			$('#edit-poPayment input[name=value]').val($(this).data('value'));
		});

		$('#datatable-project, #datatable-payment').on('click', '.delete-po', function(){
			$('.po_id-ondelete').val($(this).data('id'));
		});

		$('#datatable-project, #datatable-payment').on('click', '.undo-po', function(){
			$('.pr_detail_id-onundo').val($(this).data('id'));
		});

		$('#datatable-project, #datatable-payment').on('click', '.pr-pdf', function(){
			$('.pr_id-onpdf').val($(this).data('id'));
		});

		$('input[name=date_po]').daterangepicker({
			singleDatePicker: true,
			showDropdowns: true,
			format: 'DD MMMM YYYY'
		});

		$('#datatable-project, #datatable-payment').on('change', 'select[name=purchasing_id]', function(){
			$.post('{{ route('backend.pr.changePurchasing') }}', {
				id: $(this).data('id'),
				purchasing_id : $(this).val(),
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});

		$('#datatable-project, #datatable-payment').on('change', 'select[name=status]', function(){
			$.post('{{ route('backend.pr.changeStatus') }}', {
				id: $(this).data('id'),
				status : $(this).val(),
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});

		$('#datatable-project, #datatable-payment').on('change', 'input[name=check_audit]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.pr.checkAudit') }}', {
				id: $(this).data('id'),
				check_audit: setVal,
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});

		$('#datatable-project, #datatable-payment').on('change', 'input[name=check_finance]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.pr.checkFinance') }}', {
				id: $(this).data('id'),
				check_finance: setVal,
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});

		$('#datatable-project, #datatable-payment').on('change', 'textarea[name=note_audit]', function(){
			$.post('{{ route('backend.pr.noteAudit') }}', {
				id: $(this).data('id'),
				note_audit: $(this).val(),
			}, function(data) {
			});
		});

		$('#datatable-project, #datatable-payment').on('click', '.delete-detail', function(){
			$('.detail_id-ondelete').val($(this).data('id'));
		});

		$('#datatable-project, #datatable-payment').on('click', '.revision-detail', function(){
			$('#revision-detail input[name=id]').val($(this).data('id'));
		});

		@if(Session::has('addEdit-po-error'))
		$('#addEdit-po').modal('show');
		@endif

		@if(Session::has('add-poProject-error'))
		$('#add-poProject').modal('show');
		@endif

		@if(Session::has('edit-poProject-error'))
		$('#edit-poProject').modal('show');
		@endif

		@if(Session::has('add-poPayment-error'))
		$('#add-poPayment').modal('show');
		@endif

		@if(Session::has('edit-poPayment-error'))
		$('#edit-poPayment').modal('show');
		@endif

		@if(Session::has('pr-pdf-error'))
		$('#pr-pdf').modal('show');
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
	
	{{-- Delete PR --}}
	<div id="delete-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete PR?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="pr_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	{{-- Add PO --}}
	<div id="add-poProject" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.storePoProject') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add PO</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="quantity" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}} quantity" name="quantity" value="{{old('quantity')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quantity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="no_po" class="control-label col-md-3 col-sm-3 col-xs-12">No PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('no_po') != '' ? 'parsley-error' : ''}} no_po" name="no_po" value="{{old('no_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="date_po" class="control-label col-md-3 col-sm-3 col-xs-12">Date PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_po') != '' ? 'parsley-error' : ''}} date_po" name="date_po" value="{{old('date_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="type" name="type" class="form-control {{$errors->first('type') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									<option value="Type 1" @if(old('type') == 'Type 1') selected @endif>Type 1</option>
									<option value="Type 2" @if(old('type') == 'Type 2') selected @endif>Type 2</option>
									<option value="Type 3" @if(old('type') == 'Type 3') selected @endif>Type 3</option>
									<option value="Type 4" @if(old('type') == 'Type 4') selected @endif>Type 4</option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="supplier_id" class="control-label col-md-3 col-sm-3 col-xs-12">Supplier <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="supplier_id" name="supplier_id" class="form-control {{$errors->first('supplier_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									<option value="0"  @if(old('supplier_id') === 0) selected @endif>No Supplier</option>
									@foreach($supplier as $list)
									<option value="{!! $list->id !!}" @if(old('supplier_id') == $list->id) selected @endif>{!! $list->name !!} - {!! $list->no_rekening !!} - {!! $list->name_rekening !!}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('supplier_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="name_supplier" class="control-label col-md-3 col-sm-3 col-xs-12">Name Supplier <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('name_supplier') != '' ? 'parsley-error' : ''}} date_po" name="name_supplier" value="{{old('name_supplier')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name_supplier') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}} value" name="value" value="{{old('value')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_detail_id" class="pr_detail_id-onadd" value="{{old('pr_detail_id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="add-poPayment" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.storePoPayment') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Payment</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="date_po" class="control-label col-md-3 col-sm-3 col-xs-12">Date PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_po') != '' ? 'parsley-error' : ''}} date_po" name="date_po" value="{{old('date_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" name="value" value="{{old('value')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_detail_id" class="pr_detail_id-onadd" value="{{old('pr_detail_id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Edit PO --}}
	<div id="edit-poProject" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.updatePoProject') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Update PO</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="quantity" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}} quantity-onedit" name="quantity" value="{{old('quantity')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quantity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="no_po" class="control-label col-md-3 col-sm-3 col-xs-12">No PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('no_po') != '' ? 'parsley-error' : ''}} no_po-onedit" name="no_po" value="{{old('no_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="date_po" class="control-label col-md-3 col-sm-3 col-xs-12">Date PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_po') != '' ? 'parsley-error' : ''}} date_po-onedit" name="date_po" value="{{old('date_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="type" name="type" class="form-control {{$errors->first('type') != '' ? 'parsley-error' : ''}} type-onedit">
									<option value=""></option>
									<option value="Type 1" @if(old('type') == 'Type 1') selected @endif>Type 1</option>
									<option value="Type 2" @if(old('type') == 'Type 2') selected @endif>Type 2</option>
									<option value="Type 3" @if(old('type') == 'Type 3') selected @endif>Type 3</option>
									<option value="Type 4" @if(old('type') == 'Type 4') selected @endif>Type 4</option>
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="supplier_id" class="control-label col-md-3 col-sm-3 col-xs-12">Supplier <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="supplier_id" name="supplier_id" class="form-control {{$errors->first('supplier_id') != '' ? 'parsley-error' : ''}} supplier_id-onedit">
									<option value=""></option>
									<option value="0"  @if(old('supplier_id') === 0) selected @endif>No Supplier</option>
									@foreach($supplier as $list)
									<option value="{!! $list->id !!}" @if(old('supplier_id') == $list->id) selected @endif>{!! $list->name !!} - {!! $list->no_rekening !!} - {!! $list->name_rekening !!}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('supplier_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="name_supplier" class="control-label col-md-3 col-sm-3 col-xs-12">Name Supplier <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('name_supplier') != '' ? 'parsley-error' : ''}} name_supplier-onedit" name="name_supplier" value="{{old('name_supplier')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name_supplier') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}} value-onedit" name="value" value="{{old('value')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="po_id-onedit" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="edit-poPayment" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.updatePoPayment') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Update Payment</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="date_po" class="control-label col-md-3 col-sm-3 col-xs-12">Date PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_po') != '' ? 'parsley-error' : ''}}" name="date_po" value="{{old('date_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" name="value" value="{{old('value')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="po_id-onedit" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Delete PO --}}
	<div id="delete-po" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.deletePo') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete PO?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="po_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo PO --}}
	<div id="undo-po" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.undoPo') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo PO?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="pr_detail_id-onundo" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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

	{{-- Unconfirm Detail --}}
	<div id="revision-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.revision') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Set to revision?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="">
						<button type="submit" class="btn btn-danger">revision</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Confirm Item</h1>

	<div class="x_panel">
		<div class="row">
			<div class="col-md-12">
				<form class="form-inline" method="get">
					<input type="hidden" name="tab" value="PROJECT">
					<select class="form-control select2" name="f_purchasing" onchange="this.form.submit()">
						<option value="">My Data</option>
						<option value="staff" {{ $request->f_purchasing == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('full-user')
							<option value="all" {{ $request->f_purchasing == 'all' ? 'selected' : '' }}>All Purchasing</option>
						@endcan
						
						@foreach($purchasing as $list)
						<option value="{{ $list->id }}" {{ $request->f_purchasing == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach

						@foreach($finance as $list)
						<option value="{{ $list->id }}" {{ $request->f_purchasing == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>

					<select class="form-control select2" name="f_status" onchange="this.form.submit()">
						<option value="">All Status</option>
						<option value="none" {{ $request->f_status === 'none' ? 'selected' : '' }}>None</option>
						<option value="PENDING" {{ $request->f_status === 'PENDING' ? 'selected' : '' }}>Pending</option>
						<option value="STOCK" {{ $request->f_status === 'STOCK' ? 'selected' : '' }}>Stock</option>
						<option value="CANCEL" {{ $request->f_status === 'CANCEL' ? 'selected' : '' }}>Cancel</option>
					</select>

					<select class="form-control select2" name="f_day" onchange="this.form.submit()">
						<option value="">All Day</option>
						<option value="0" {{ $request->f_day === '0' ? 'selected' : '' }}>Today</option>
						<option value="1" {{ $request->f_day === '1' ? 'selected' : '' }}>Past 1 day</option>
						<option value="2" {{ $request->f_day === '2' ? 'selected' : '' }}>Past 2 days</option>
						<option value="3" {{ $request->f_day === '3' ? 'selected' : '' }}>Past 3 days</option>
						<option value="4" {{ $request->f_day === '4' ? 'selected' : '' }}>Past 4 days</option>
					</select>
					<select class="form-control select2" name="f_value" onchange="this.form.submit()">
						<option value="">All Value</option>
						<option value="1" {{ $request->f_value === '1' ? 'selected' : '' }}>With Value</option>
						<option value="0" {{ $request->f_value === '0' ? 'selected' : '' }}>No Value</option>
					</select>
					<select class="form-control select2" name="f_audit" onchange="this.form.submit()">
						<option value="">All Check Audit</option>
						<option value="1" {{ $request->f_audit === '1' ? 'selected' : '' }}>Checked Audit</option>
						<option value="0" {{ $request->f_audit === '0' ? 'selected' : '' }}>Unchecked Audit</option>
					</select>
					<select class="form-control select2" name="f_finance" onchange="this.form.submit()">
						<option value="">All Check Finance</option>
						<option value="1" {{ $request->f_finance === '1' ? 'selected' : '' }}>Checked Finance</option>
						<option value="0" {{ $request->f_finance === '0' ? 'selected' : '' }}>Unchecked Finance</option>
					</select>
					<select class="form-control select2" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_month" onchange="this.form.submit()">
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<input type="text" name="search" placeholder="Search" class="form-control" onchange="this.form.submit()" value="{{ $request->search }}">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
					{{-- <button type="submit" class="btn btn-success">Apply Selected</button> --}}
					{{ csrf_field() }}
				</form>
			</div>
		</div>
	</div>

	<div class="x_panel" style="overflow: auto;">
		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="tab" class="nav nav-tabs bar_tabs" role="tablist">
				<li role="presentation" class="{{ $request->tab === 'PROJECT' || $request->tab == '' ? 'active' : ''}}"><a href="#project" id="project-tab" role="data-sales-tab" data-toggle="tab" aria-expanded="true" class="tab-active">Project</a>
				</li>

				<li role="presentation" class="{{ $request->tab === 'PAYMENT' ? 'active' : ''}}"><a href="#payment" role="tab" id="payment-tab" data-toggle="tab" aria-expanded="false" class="tab-active">Payment</a>
				</li>
			</ul>
			<div id="tabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade {{ $request->tab === 'PROJECT' || $request->tab == '' ? 'active in' : ''}}" id="project" aria-labelledby="project-tab">
					<table class="table table-bordered" style="font-size: small;">
						<thead>						
							<tr>
								<th>Name</th>
								<th>Pending</th>
								<th>Cancel</th>
								<th>Stock</th>
								<th>H-4</th>
								<th>H-3</th>
								<th>H-2</th>
								<th>H-1</th>
								<th>H</th>
							</tr>
						</thead>
						<tbody>
							@foreach($purchasing as $list)
							<tr>
								<td>{{ $list->fullname }}</td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "PENDING", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-pending-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "CANCEL", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-cancel-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "STOCK", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-stock-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 4, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-4-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 3, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-3-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 2, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-2-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 1, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-1-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PROJECT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 0, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="project-0-{{ $list->id }}"></a></td>
							</tr>
							@endforeach
						</tbody>
					</table>

					<table class="table table-bordered" id="datatable-project">
						<thead>
							<tr>
								<th>Info</th>

								<th>Purchasing</th>
								<th>Data PO</th>
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

				<div role="tabpanel" class="tab-pane fade {{ $request->tab === 'PAYMENT' ? 'active in' : ''}}" id="payment" aria-labelledby="payment-tab">
					<table class="table table-bordered" style="font-size: small;">
						<thead>						
							<tr>
								<th>Name</th>
								<th>Pending</th>
								<th>Cancel</th>
								<th>Stock</th>
								<th>H-4</th>
								<th>H-3</th>
								<th>H-2</th>
								<th>H-1</th>
								<th>H</th>
							</tr>
						</thead>
						<tbody>
							@foreach($finance as $list)
							<tr>
								<td>{{ $list->fullname }}</td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "PENDING", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-pending-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "CANCEL", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-cancel-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "STOCK", "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-stock-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 4, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-4-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 3, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-3-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 2, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-2-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 1, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-1-{{ $list->id }}"></a></td>
								<td><a href="{{ route('backend.pr.confirm', ["tab" => "PAYMENT", "f_purchasing" => $list->id, "f_status" => "NONE", "f_day" => 0, "f_audit" => 0, "f_value" => 0, "f_year" => $request->f_year, "f_month" => $request->f_month]) }}" id="payment-0-{{ $list->id }}"></a></td>
							</tr>
							@endforeach
						</tbody>
					</table>

					<table class="table table-bordered" id="datatable-payment">
						<thead>
							<tr>
								<th>Info</th>

								<th>Purchasing</th>
								<th>Data PO</th>
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
			</div>
		</div>
	</div>
	

@endsection