@extends('backend.layout.master')

@section('title')
	SPK Recap
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">

	function number_format (number, decimals, decPoint, thousandsSep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
		var n = !isFinite(+number) ? 0 : +number
		var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
		var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
		var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
		var s = ''

		var toFixedFix = function (n, prec) {
			if (('' + n).indexOf('e') === -1) {
				return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
			} else {
				var arr = ('' + n).split('e')
				var sig = ''
				if (+arr[1] + prec > 0) {
					sig = '+'
				}
				return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
			}
		}

	  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
	  s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
	  if (s[0].length > 3) {
	  	s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
	  }
	  if ((s[1] || '').length < prec) {
	  	s[1] = s[1] || ''
	  	s[1] += new Array(prec - s[1].length + 1).join('0')
	  }

	  return s.join(dec)
	}

	function collectData() {
		$('#stat-onprogress').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-unapprove').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-notcomplete').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-notinvoice').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-notreceivedinvoice').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-notsended').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-kb').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-bk').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-kt').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-0').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');

		@for ($i = 1; $i <= getConfigValue('num_admin'); $i++)
			$('#stat-{{ $i }}').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		@endfor

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'UNFINISH_PROD',
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-onprogress').html(data.count
					@if(Auth::user()->can('viewPrice-invoice'))
					 + '<br/>HM :'  + number_format(data.total_hm) + '<br/>HJ : ' + number_format(data.total_hj)
					@endif
				);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'UNFINISH_SPK',
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-unapprove').html(data.count
					@if(Auth::user()->can('viewPrice-invoice'))
					 + '<br/>HM :'  + number_format(data.total_hm) + '<br/>HJ : ' + number_format(data.total_hj)
					@endif
				);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_complete   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-notcomplete').html(data.count
					@if(Auth::user()->can('viewPrice-invoice'))
					 + ' | HM :'  + number_format(data.total_hm) + ' | HJ : ' + number_format(data.total_hj)
					@endif
				);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -1,
				f_complete   : 1,
				f_inv        : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-notinvoice').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -1,
				f_complete   : 1,
				f_inv        : 1,
				f_received   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-notreceivedinvoice').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -1,
				f_complete   : 1,
				f_inv        : 1,
				f_received   : 1,
				f_send       : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-notsended').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -1,
				f_complete   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-kb').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -2,
				f_complete   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-bk').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : -3,
				f_complete   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-kt').html(data.count);
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'FINISH',
				f_check      : 0,
				f_admin      : 0,
				f_complete   : 0,
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-0').html(data.count);
		});

		@for ($i = 1; $i <= getConfigValue('num_admin'); $i++)
			$.post('{{ route('backend.invoice.getStatus') }}', 
				{
					f_done		 : 'FINISH',
					f_check      : 0,
					f_admin      : {{$i}},
					f_complete   : 0,
					f_sales      : $('*[name=f_sales]').val(),
					f_year		 : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
				}, 
				function(data, textStatus, xhr) {
					$('#stat-{{$i}}').html(data.count);
			});
		@endfor

	}

	$(function() {
		collectData();

		$('input[name=date_faktur]').daterangepicker({
			singleDatePicker: true,
			showDropdowns: true,
			format: 'DD MMMM YYYY'
		});


		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.invoice.datatables') }}",
				type: "POST",
				data: {
					f_admin      : $('*[name=f_admin]').val(),
					f_done		 : $('*[name=f_done]').val(),
					f_complete   : $('*[name=f_complete]').val(),
					f_inv        : $('*[name=f_inv]').val(),
					f_received   : $('*[name=f_received]').val(),
					f_send		 : $('*[name=f_send]').val(),
					f_check      : $('*[name=f_check]').val(),
					f_sales      : $('*[name=f_sales]').val(),
					f_year		 : $('*[name=f_year]').val(),
					f_month      : $('*[name=f_month]').val(),
					search       : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'no_spk', sClass: 'nowrap-cell'},
				{data: 'sum_value_invoice', sClass: 'nowrap-cell'},
				{data: 'check_master', searchable: false},
				{data: 'data_invoice', orderable: false, searchable: false},
				{data: 'note_invoice', searchable: false, sClass: 'nowrap-cell'},

				{data: 'name', visible: false},
			],
			dom: '<lfip<t>ip>',
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
		});

		$(".refresh-data").click(function(){
			collectData();
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

		$('#datatable').on('click', '.add-document', function(){
			$('#add-document input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undo-document', function(){
			$('#undo-document input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.redo-document', function(){
			$('#redo-document input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.update-invoice', function(){
			$('#update-invoice input[name=id]').val($(this).data('id'));
			$('#update-invoice input[name=no_invoice]').val($(this).data('no_invoice'));
			$('#update-invoice input[name=value_invoice]').val($(this).data('value_invoice'));
			$('#update-invoice input[name=date_faktur]').val($(this).data('date_faktur'));
		});

		$('#datatable').on('click', '.undo-invoice', function(){
			$('#undo-invoice input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.add-received', function(){
			$('#add-received input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.undo-received', function(){
			$('#undo-received input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.add-send', function(){
			$('#add-send input[name=id]').val($(this).data('id'));
			$('#add-send input[name=no_sending]').val($(this).data('no_sending'));
		});

		$('#datatable').on('click', '.undo-send', function(){
			$('#undo-send input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.delete-invoice', function(){
			$('#delete-invoice input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('change', 'select[name=code_admin]', function(){
			$.post('{{ route('backend.invoice.noAdmin') }}', {
				id: $(this).data('id'),
				code_admin: $(this).val(),
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
			$.post('{{ route('backend.invoice.checkFinance') }}', {
				id: $(this).data('id'),
				check_finance: setVal,
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});

		$('#datatable').on('change', 'textarea[name=note_invoice]', function(){
			$.post('{{ route('backend.invoice.noteInvoice') }}', {
				id: $(this).data('id'),
				note_invoice: $(this).val(),
			}, function(data) {
			});
		});

		@can('checkMaster-invoice')
		$('#datatable').on('change', 'input[name=check_master]', function(){
			if ($(this).is(':checked')) {
				var setVal = 1;
			}
			else
			{
				var setVal = 0;
			}
			$.post('{{ route('backend.invoice.checkMaster') }}', {
				id: $(this).data('id'),
				check_master: setVal,
			}, function(data) {
			});
		});
		@endcan

		@if(Session::has('update-invoice-error'))
		$('#update-invoice').modal('show');
		@endif
		@if(Session::has('add-send-error'))
		$('#add-send').modal('show');
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

	.fa-spinner {
		 font-size: 20px;
	}
</style>
@endsection

@section('content')
	
	{{-- Add Invoice Document --}}
	<div id="add-document" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.addDocument') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Document?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Invoice Document --}}
	<div id="undo-document" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.undoDocument') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Document?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Redo Invoice Document --}}
	<div id="redo-document" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.redoDocument') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Redo Document?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Add/Edit invoice --}}
	<div id="update-invoice" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.addInvoice') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Invoice</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="no_invoice" class="control-label col-md-3 col-sm-3 col-xs-12">No Invoice <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('no_invoice') != '' ? 'parsley-error' : ''}}" name="no_invoice" value="{{old('no_invoice')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_invoice') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="value_invoice" class="control-label col-md-3 col-sm-3 col-xs-12">Value Invoice <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('value_invoice') != '' ? 'parsley-error' : ''}}" name="value_invoice" value="{{old('value_invoice')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('value_invoice') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="date_faktur" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('date_faktur') != '' ? 'parsley-error' : ''}}" name="date_faktur" value="{{old('date_faktur')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_faktur') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="invoice_id-onedit" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Invoice --}}
	<div id="undo-invoice" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.undoInvoice') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Invoice?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Add Recieved Invoice --}}
	<div id="add-received" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.addReceived') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Complete Invoice?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Recieved Invoice --}}
	<div id="undo-received" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.undoReceived') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Complete Invoice?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Add/Edit Send --}}
	<div id="add-send" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.addSend') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Send</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="no_sending" class="control-label col-md-3 col-sm-3 col-xs-12">No Sending <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('no_sending') != '' ? 'parsley-error' : ''}}" name="no_sending" value="{{old('no_sending')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_sending') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Send --}}
	<div id="undo-send" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.undoSend') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Send?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Delete Invoice --}}
	<div id="delete-invoice" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Invoice?</h4>
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

	<h1>SPK Recap</h1>

	<div class="x_panel" style="overflow: auto;">
		
		<div class="row">
			<div class="col-md-12">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_admin" onchange="this.form.submit()">
						<option value="" {{ $request->f_admin === '' ? 'selected' : '' }}>All No Admin</option>
						<option value="0" {{ $request->f_admin === '0' ? 'selected' : '' }}>Untake</option>
						<option value="-1" {{ $request->f_admin === '-1' ? 'selected' : '' }}>Kurang Bayar</option>
						<option value="-2" {{ $request->f_admin === '-2' ? 'selected' : '' }}>Belum Kirim</option>
						<option value="-3" {{ $request->f_admin === '-3' ? 'selected' : '' }}>Kurang T</option>
						@for ($i = 1; $i <= getConfigValue('num_admin'); $i++)
							<option value="{{ $i }}" {{ $request->f_admin == $i ? 'selected' : '' }}>No {{ $i }}</option>
						@endfor
					</select>
					<select class="form-control select2" name="f_done" onchange="this.form.submit()">
						<option value="">All Status Finish</option>
						<option value="UNFINISH_PROD" {{ $request->f_done == 'UNFINISH_PROD' ? 'selected' : '' }}>Unfinish Production</option>
						<option value="UNFINISH_SPK" {{ $request->f_done == 'UNFINISH_SPK' ? 'selected' : '' }}>Unfinish SPK</option>
						<option value="FINISH" {{ $request->f_done == 'FINISH' ? 'selected' : '' }}>Finish</option>
					</select>
					<select name="f_complete" class="form-control select2" onchange="this.form.submit()">
						<option value="" {{ $request->f_complete === '' ? 'selected' : '' }}>All Status Invoice Complete</option>
						<option value="1" {{ $request->f_complete === '1' ? 'selected' : '' }}>Complete Invoice</option>
						<option value="0" {{ $request->f_complete === '0' ? 'selected' : '' }}>Uncomplete Invoice</option>
					</select>
					<select name="f_inv" class="form-control select2" onchange="this.form.submit()">
						<option value="" {{ $request->f_inv === '' ? 'selected' : '' }}>All Status Value Invoice</option>
						<option value="1" {{ $request->f_inv === '1' ? 'selected' : '' }}>Invoice Filled</option>
						<option value="0" {{ $request->f_inv === '0' ? 'selected' : '' }}>Unfill Invoice</option>
					</select>
					<select name="f_received" class="form-control select2" onchange="this.form.submit()">
						<option value="" {{ $request->f_received === '' ? 'selected' : '' }}>All Status Received Invoice</option>
						<option value="1" {{ $request->f_received === '1' ? 'selected' : '' }}>Received</option>
						<option value="0" {{ $request->f_received === '0' ? 'selected' : '' }}>Not Received</option>
					</select>
					<select name="f_send" class="form-control select2" onchange="this.form.submit()">
						<option value="" {{ $request->f_send === '' ? 'selected' : '' }}>All Status Sending</option>
						<option value="1" {{ $request->f_send === '1' ? 'selected' : '' }}>Sended</option>
						<option value="0" {{ $request->f_send === '0' ? 'selected' : '' }}>Not Sended</option>
					</select>
					<select name="f_check" class="form-control select2" onchange="this.form.submit()">
						<option value="" {{ $request->f_check === '' ? 'selected' : '' }}>All Status Checked</option>
						<option value="1" {{ $request->f_check === '1' ? 'selected' : '' }}>Checked</option>
						<option value="0" {{ $request->f_check === '0' ? 'selected' : '' }}>Uncheck</option>
					</select>
					<select class="form-control select2" name="f_year" onchange="this.form.submit()">
						<option value="" {{ $request->f_year === '' ? 'selected' : '' }}>This Year</option>
						<option value="all" {{ $request->f_year === 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_month" onchange="this.form.submit()">
						<option value="" {{ $request->f_month === '' ? 'selected' : '' }}>This Month</option>
						<option value="all" {{ $request->f_month === 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						<option value="" {{ $request->f_sales === '' ? 'selected' : '' }}>All Sales</option>
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
					</select>
					<input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Search" onchange="this.form.submit()">
				</form>
			</div>
		</div>

		<div class="ln_solid"></div>

		<button type="button" class="btn btn-primary refresh-data btn-xs">Refresh</button>

		<table class="table table-bordered" style="font-size: small;">
			<tr>
				<th scope="col">On Progress</th>
				<th scope="col">Unapprove</th>
				<th colspan="{{ 4 + getConfigValue('num_admin') }}" scope="col">Not Complete</th>
				<th scope="col">Not Invoice</th>
				<th scope="col">Not Invoice Done</th>
				<th scope="col">Not Sended</th>
			</tr>
			<tr>
				<td rowspan="3" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "UNFINISH_PROD"]) }}" id="stat-onprogress">
						
					</a>
				</td>
				<td rowspan="3" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "UNFINISH_SPK"]) }}" id="stat-unapprove">
					</a>
				</td>
				<td colspan="{{ 4 + getConfigValue('num_admin') }}" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0 ]) }}" id="stat-notcomplete">
					</a>
				</td>
				<td rowspan="3" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 1, "f_inv" => 0 ]) }}" id="stat-notinvoice">
					</a>
				</td>
				<td rowspan="3" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 1, "f_inv" => 1, "f_received" => 0]) }}" id="stat-notreceivedinvoice">
					</a>
				</td>
				<td rowspan="3" align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 1, "f_inv" => 1, "f_received" => 1, "f_send" => 0]) }}" id="stat-notsended">
					</a>
				</td>
			</tr>
			<tr>
				<td align="center"><span>KB <i class="fa fa-info"></i></span></td>
				<td align="center"><span data-toggle="tooltip" title="belum kirim">BK <i class="fa fa-info"></i></span></td>
				<td align="center"><span data-toggle="tooltip" title="belum kirim">KT <i class="fa fa-info"></i></span></td>
				<td align="center">0</td>
				@for ($i = 1; $i <= getConfigValue('num_admin'); $i++)
					<td align="center">{{ $i }}</td>
				@endfor
			</tr>
			<tr>
				<td align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0, "f_admin" => -1 ]) }}" id="stat-kb">
					</a>
				</td>
				<td align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0, "f_admin" => -2 ]) }}" id="stat-bk">
					</a>
				</td>
				<td align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0, "f_admin" => -3 ]) }}" id="stat-kt">
					</a>
				</td>
				<td align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0, "f_admin" => 0 ]) }}" id="stat-0">
					</a>
				</td>

				@for ($i = 1; $i <= getConfigValue('num_admin'); $i++)
				<td align="center">
					<a href="{{ route('backend.invoice', ["f_year" => $request->f_year, "f_month" => $request->f_month, "f_done" => "FINISH", "f_check" => 0, "f_complete" => 0, "f_admin" => $i ]) }}" id="stat-{{ $i }}">
					</a>
				</td>
				@endfor
			</tr>
		</table>

		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>Info SPK</th>
					<th>Info Invoice</th>
					<th></th>
					<th>Data Invoice</th>
					<th>Note Invoice</th>
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