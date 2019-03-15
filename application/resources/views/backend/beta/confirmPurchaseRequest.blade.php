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
		$.post('{{ route('backend.beta.getStatusConfirmProject') }}', 
			{
				f_year       : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#status-project').empty();

				$('#status-project').append( "<tr><th>Name</th><th>< -4 Day</th><th>-3 Day</th><th>-2 Day</th><th>-1 Day</th><th>Today</th></tr>" );

				f_year  = $('*[name=f_year]').val();
				f_month = $('*[name=f_month]').val();
				tab = 'PROJECT';
				
				$.each(data.status, function(index, list) {
					$('#status-project').append( "\
					<tr>\
						<td>"+ list.name +"</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=4&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_4_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=3&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_3_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=2&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_2_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=1&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_1_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=0&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.today+"</a>\
						</td>\
					</tr>\
					" );

				});
		});

		$.post('{{ route('backend.beta.getStatusConfirmPayment') }}', 
			{
				f_year       : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#status-payment').empty();

				$('#status-payment').append( "<tr><th>Name</th><th>< -4 Day</th><th>-3 Day</th><th>-2 Day</th><th>-1 Day</th><th>Today</th></tr>" );

				f_year  = $('*[name=f_year]').val();
				f_month = $('*[name=f_month]').val();
				tab = 'PAYMENT';
				
				$.each(data.status, function(index, list) {
					$('#status-payment').append( "\
					<tr>\
						<td>"+ list.name +"</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=4&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_4_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=3&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_3_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=2&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_2_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=1&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.past_1_day+"</a>\
						</td>\
						<td>\
							<a href=\"{{ route('backend.pr.confirm') }}?tab="+tab+"&f_purchasing="+list.id+"&f_audit=0&f_day=0&f_value=0&f_audit=0&f_year="+f_year+"&f_month="+f_month+"\">"+list.today+"</a>\
						</td>\
					</tr>\
					" );

				});
		});

		var tableProject = $('#datatableProject').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.beta.datatablesConfirmProject') }}",
				type: "post",
				data: {
					f_year       : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
					f_purchasing : $('*[name=f_purchasing]').val(),
					f_day        : $('*[name=f_day]').val(),
					f_value      : $('*[name=f_value]').val(),
					f_audit      : $('*[name=f_audit]').val(),
					f_finance    : $('*[name=f_finance]').val(),
					f_id         : getUrlParameter('f_id'),
					s_no_pr      : $('*[name=s_no_pr]').val(),
					s_item       : $('*[name=s_item]').val(),
					s_no_po      : $('*[name=s_no_po]').val(),
				},
			},
			columns: [
				{data: 'spk', sClass: 'nowrap-cell'},
				{data: 'spk_name'},
				{data: 'no_pr'},

				{data: 'name'},
				{data: 'item'},
				{data: 'quantity', sClass: 'nowrap-cell'},

				{data: 'datetime_confirm', sClass: 'nowrap-cell'},
				{data: 'date_request', sClass: 'nowrap-cell'},
				{data: 'purchasing'},

				{data: 'po'},

				// {data: 'no_po', sClass: 'nowrap-cell'},
				// {data: 'date_po'},
				// {data: 'type', sClass: 'nowrap-cell'},
				// {data: 'name_supplier'},
				// {data: 'name_rekening'},
				// {data: 'no_rekening'},
				// {data: 'value'},
				// {data: 'check_audit'},
				// {data: 'check_finance'},
				// {data: 'note_audit'},
				
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
			dom: '<lfip<t>ip>',
			// scrollY: "400px",
			scrollX: true,
		});

		var tablePayment = $('#datatablePayment').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.beta.datatablesConfirmPayment') }}",
				type: "post",
				data: {
					f_year       : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
					f_purchasing : $('*[name=f_purchasing]').val(),
					f_day        : $('*[name=f_day]').val(),
					f_value      : $('*[name=f_value]').val(),
					f_audit      : $('*[name=f_audit]').val(),
					f_finance    : $('*[name=f_finance]').val(),
					f_id         : getUrlParameter('f_id'),
					s_no_pr      : $('*[name=s_no_pr]').val(),
					s_item       : $('*[name=s_item]').val(),
					// s_no_po      : $('*[name=s_no_po]').val(),
				},
			},
			columns: [
				{data: 'no_pr'},
				{data: 'name'},
				{data: 'item'},

				{data: 'datetime_confirm', sClass: 'nowrap-cell'},
				{data: 'date_request', sClass: 'nowrap-cell'},
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
			dom: '<lfip<t>ip>',
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



		$('#datatable').on('click', '.delete-pr', function(){
			$('.pr_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.add-po', function(){
			$('.pr_detail_id-onadd').val($(this).data('id'));
		});

		$('#datatable').on('click', '.edit-po', function(){
			$('.po_id-onedit').val($(this).data('id'));
			$('.quantity-onedit').val($(this).data('quantity'));
			$('.no_po-onedit').val($(this).data('no_po'));
			$('.date_po-onedit').val($(this).data('date_po'));
			$('.type-onedit').val($(this).data('type')).trigger('change');
			$('.supplier_id-onedit').val($(this).data('supplier_id')).trigger('change');
			$('.name_supplier-onedit').val($(this).data('name_supplier'));
			$('.value-onedit').val($(this).data('value'));
		});

		$('#datatable').on('click', '.addEdit-po', function(){
			$('.pr_detail_id-onaddEdit').val($(this).data('id'));
			$('.quantity-onaddEdit').val($(this).data('quantity'));
			$('.no_po-onaddEdit').val($(this).data('no_po'));
			$('.date_po-onaddEdit').val($(this).data('date_po'));
			$('.type-onaddEdit').val($(this).data('type')).trigger('change');
			$('.supplier_id-onaddEdit').val($(this).data('supplier_id')).trigger('change');
			$('.name_supplier-onaddEdit').val($(this).data('name_supplier'));
			$('.value-onaddEdit').val($(this).data('value'));
		});

		$('#datatable').on('click', '.delete-po', function(){
			$('.po_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undo-po', function(){
			$('.pr_detail_id-onundo').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pr-pdf', function(){
			$('.pr_id-onpdf').val($(this).data('id'));
		});

		$('input[name=date_po]').daterangepicker({
			singleDatePicker: true,
			showDropdowns: true,
			format: 'DD MMMM YYYY'
		});

		$('select[name=supplier_id]').select2({
			placeholder: "Select Supplier",
			allowClear: true,
			width: '292px'
		});

		$('select[name=type]').select2({
			placeholder: "Select Type",
			allowClear: true,
			width: 'resolve'
		});

		$('select[name=f_purchasing]').select2({
		});

		$('#datatable').on('change', 'select[name=purchasing_id]', function(){
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

		$('#datatable').on('change', 'input[name=check_audit]', function(){
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

		$('#datatable').on('change', 'input[name=check_finance]', function(){
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

		$('#datatable').on('change', 'textarea[name=note_audit]', function(){
			$.post('{{ route('backend.pr.noteAudit') }}', {
				id: $(this).data('id'),
				note_audit: $(this).val(),
			}, function(data) {
			});
		});

		@if(Session::has('addEdit-po-error'))
		$('#addEdit-po').modal('show');
		@endif

		@if(Session::has('add-po-error'))
		$('#add-po').modal('show');
		@endif

		@if(Session::has('edit-po-error'))
		$('#edit-po').modal('show');
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
	
	@can('delete-pr')
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
	@endcan
	
	{{-- Add/Edit PO --}}
	<div id="addEdit-po" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.addEditPo') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add/Edit PO</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="no_po" class="control-label col-md-3 col-sm-3 col-xs-12">No PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('no_po') != '' ? 'parsley-error' : ''}} no_po-onaddEdit" name="no_po" value="{{old('no_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="date_po" class="control-label col-md-3 col-sm-3 col-xs-12">Date PO <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_po') != '' ? 'parsley-error' : ''}} date_po-onaddEdit" name="date_po" value="{{old('date_po')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_po') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="type" name="type" class="form-control {{$errors->first('type') != '' ? 'parsley-error' : ''}} type-onaddEdit">
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
								<select id="supplier_id" name="supplier_id" class="form-control {{$errors->first('supplier_id') != '' ? 'parsley-error' : ''}} supplier_id-onaddEdit">
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
								<input type="text" class="form-control {{$errors->first('name_supplier') != '' ? 'parsley-error' : ''}} name_supplier-onaddEdit" name="name_supplier" value="{{old('name_supplier')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name_supplier') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}} value-onaddEdit" name="value" value="{{old('value')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="pr_detail_id-onaddEdit" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	@can('addPo-pr')
	{{-- Add PO --}}
	<div id="add-po" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.addPo') }}" method="post" enctype="multipart/form-data">
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
	@endcan

	@can('editPo-pr')
	{{-- Edit PO --}}
	<div id="edit-po" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.editPo') }}" method="post" enctype="multipart/form-data">
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
	@endcan

	@can('deletePo-pr')
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
	@endcan

	@can('undoPo-pr')
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
	@endcan

	<h1>Confirm Item</h1>

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
						<tbody id="status-project">
							<tr>
								<th>Processing....</th>
							</tr>
						</tbody>
					</table>

					<div class="row">
						<div class="col-md-12">
							<form class="form-inline" method="get">
								<input type="hidden" name="tab" value="PROJECT">
								<select class="form-control" name="f_purchasing" onchange="this.form.submit()">
									<option value="">My Data</option>
									<option value="staff" {{ $request->f_purchasing == 'staff' ? 'selected' : '' }}>My Staff</option>
									@can('allUser-pr')
										<option value="all" {{ $request->f_purchasing == 'all' ? 'selected' : '' }}>All Purchasing</option>
									@endcan
									
									@foreach($purchasing as $list)
									<option value="{{ $list->id }}" {{ $request->f_purchasing == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
									@endforeach
									
								</select>

								<select class="form-control" name="f_day" onchange="this.form.submit()">
									<option value="">All Day</option>
									<option value="0" {{ $request->f_day === '0' ? 'selected' : '' }}>Today</option>
									<option value="1" {{ $request->f_day === '1' ? 'selected' : '' }}>Past 1 day</option>
									<option value="2" {{ $request->f_day === '2' ? 'selected' : '' }}>Past 2 days</option>
									<option value="3" {{ $request->f_day === '3' ? 'selected' : '' }}>Past 3 days</option>
									<option value="4" {{ $request->f_day === '4' ? 'selected' : '' }}>Past 4 days</option>
								</select>
								<select class="form-control" name="f_value" onchange="this.form.submit()">
									<option value="">All Value</option>
									<option value="1" {{ $request->f_value === '1' ? 'selected' : '' }}>With Value</option>
									<option value="0" {{ $request->f_value === '0' ? 'selected' : '' }}>No Value</option>
								</select>
								<select class="form-control" name="f_audit" onchange="this.form.submit()">
									<option value="">All Check Audit</option>
									<option value="1" {{ $request->f_audit === '1' ? 'selected' : '' }}>Checked Audit</option>
									<option value="0" {{ $request->f_audit === '0' ? 'selected' : '' }}>Unchecked Audit</option>
								</select>
								<select class="form-control" name="f_finance" onchange="this.form.submit()">
									<option value="">All Check Finance</option>
									<option value="1" {{ $request->f_finance === '1' ? 'selected' : '' }}>Checked Finance</option>
									<option value="0" {{ $request->f_finance === '0' ? 'selected' : '' }}>Unchecked Finance</option>
								</select>
								<select class="form-control" name="f_year" onchange="this.form.submit()">
									<option value="">This Year</option>
									<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
									@foreach($year as $list)
									<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
									@endforeach
								</select>
								<select class="form-control" name="f_month" onchange="this.form.submit()">
									<option value="">This Month</option>
									<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
									@php $numMonth = 1; @endphp
									@foreach($month as $list)
									<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<input type="text" name="s_no_pr" placeholder="Search No PR" class="form-control" onchange="this.form.submit()" value="{{ $request->s_no_pr }}">
								<input type="text" name="s_item" placeholder="Search Item" class="form-control" onchange="this.form.submit()" value="{{ $request->s_item }}">
								<input type="text" name="s_no_po" placeholder="Search No PO" class="form-control" onchange="this.form.submit()" value="{{ $request->s_no_po }}">

							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
								{{-- <button type="submit" class="btn btn-success">Apply Selected</button> --}}
								{{ csrf_field() }}
							</form>
						</div>
					</div>

					<table class="table table-bordered" id="datatableProject">
						<thead>
							<tr>
								<th>SPK</th>
								<th>SPK Name</th>
								<th>No PR</th>

								<th>Name Order</th>
								<th>Item</th>
								<th>Quantity</th>

								<th>Datetime Confirm</th>
								<th>Date Request</th>
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

				<div role="tabpanel" class="tab-pane fade {{ $request->tab === 'PAYMENT' ? 'active in' : ''}}" id="payment" aria-labelledby="payment-tab">
					<table class="table table-bordered" style="font-size: small;">
						<tbody id="status-payment">
							<tr>
								<th>Processing....</th>
							</tr>
						</tbody>
					</table>

					<div class="row">
						<div class="col-md-12">
							<form class="form-inline" method="get">
								<input type="hidden" name="tab" value="PAYMENT">
								<select class="form-control" name="f_purchasing" onchange="this.form.submit()">
									<option value="">My Data</option>
									<option value="staff" {{ $request->f_purchasing == 'staff' ? 'selected' : '' }}>My Staff</option>
									@can('allUser-pr')
										<option value="all" {{ $request->f_purchasing == 'all' ? 'selected' : '' }}>All Purchasing</option>
									@endcan
									
									@foreach($finance as $list)
									<option value="{{ $list->id }}" {{ $request->f_purchasing == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
									@endforeach
									
								</select>

								<select class="form-control" name="f_day" onchange="this.form.submit()">
									<option value="">All Day</option>
									<option value="0" {{ $request->f_day === '0' ? 'selected' : '' }}>Today</option>
									<option value="1" {{ $request->f_day === '1' ? 'selected' : '' }}>Past 1 day</option>
									<option value="2" {{ $request->f_day === '2' ? 'selected' : '' }}>Past 2 days</option>
									<option value="3" {{ $request->f_day === '3' ? 'selected' : '' }}>Past 3 days</option>
									<option value="4" {{ $request->f_day === '4' ? 'selected' : '' }}>Past 4 days</option>
								</select>
								<select class="form-control" name="f_value" onchange="this.form.submit()">
									<option value="">All Value</option>
									<option value="1" {{ $request->f_value === '1' ? 'selected' : '' }}>With Value</option>
									<option value="0" {{ $request->f_value === '0' ? 'selected' : '' }}>No Value</option>
								</select>
								<select class="form-control" name="f_audit" onchange="this.form.submit()">
									<option value="">All Check Audit</option>
									<option value="1" {{ $request->f_audit === '1' ? 'selected' : '' }}>Checked Audit</option>
									<option value="0" {{ $request->f_audit === '0' ? 'selected' : '' }}>Unchecked Audit</option>
								</select>
								<select class="form-control" name="f_finance" onchange="this.form.submit()">
									<option value="">All Check Finance</option>
									<option value="1" {{ $request->f_finance === '1' ? 'selected' : '' }}>Checked Finance</option>
									<option value="0" {{ $request->f_finance === '0' ? 'selected' : '' }}>Unchecked Finance</option>
								</select>
								<select class="form-control" name="f_year" onchange="this.form.submit()">
									<option value="">This Year</option>
									<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
									@foreach($year as $list)
									<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
									@endforeach
								</select>
								<select class="form-control" name="f_month" onchange="this.form.submit()">
									<option value="">This Month</option>
									<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
									@php $numMonth = 1; @endphp
									@foreach($month as $list)
									<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<input type="text" name="s_no_pr" placeholder="Search No PR" class="form-control" onchange="this.form.submit()" value="{{ $request->s_no_pr }}">
								<input type="text" name="s_item" placeholder="Search Item" class="form-control" onchange="this.form.submit()" value="{{ $request->s_item }}">
								{{-- <input type="text" name="s_no_po" placeholder="Search No PO" class="form-control" onchange="this.form.submit()" value="{{ $request->s_no_po }}"> --}}
							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
								{{-- <button type="submit" class="btn btn-success">Apply Selected</button> --}}
								{{ csrf_field() }}
							</form>
						</div>
					</div>

					<table class="table table-bordered" id="datatablePayment">
						<thead>
							<tr>
								<th>No PR</th>
								<th>Name Order</th>
								<th>Item</th>

								<th>Datetime Confirm</th>
								<th>Date Request</th>
								<th>Finance</th>

								<th>Data</th>
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
			</div>
		</div>

					
	</div>
	

@endsection