@extends('backend.layout.master')

@section('title')
	Account Sales
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.2.4/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript">
	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	function formatGeneral ( d ) {
		html = '';
		html += '<div class="row">';
		html += '	<div class="col-md-12">'+d.detail+'</div>';
		html += '</div>';
	    return html;
	}

	
	
	$(function() {

		var tableOrder = $('#datatable-order').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountSales') }}",
				type: "POST",
				data: {
			    	f_status: "ORDER",
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'company_name', sClass: 'nowrap-cell'},
				{data: 'invoice', sClass: 'nowrap-cell'},
				{data: 'spk', sClass: 'nowrap-cell'},
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

		var tableInvoice = $('#datatable-invoice').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountSales') }}",
				type: "POST",
				data: {
			    	f_status: "INVOICE",
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'company_name', sClass: 'nowrap-cell'},
				{data: 'invoice', sClass: 'nowrap-cell'},
				{data: 'spk', sClass: 'nowrap-cell'},
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

		var tableClosed = $('#datatable-closed').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountSales') }}",
				type: "POST",
				data: {
			    	f_status: "CLOSED",
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'company_name', sClass: 'nowrap-cell'},
				{data: 'invoice', sClass: 'nowrap-cell'},
				{data: 'spk', sClass: 'nowrap-cell'},
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

		// Add event listener for opening and closing details
	    $('#datatable-order tbody').on('click', 'td.details-control > button', function () {
	    	console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableOrder.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( formatGeneral(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );

	    $('#datatable-invoice tbody').on('click', 'td.details-control > button', function () {
	    	console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableInvoice.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( formatGeneral(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );

	    $('#datatable-closed tbody').on('click', 'td.details-control > button', function () {
	    	console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableClosed.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( formatGeneral(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );


		$('#datatable-order, #datatable-invoice, #datatable-closed').on('click', '.deleteAccountSales-account', function(){
			$('#deleteAccountSales-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable-order, #datatable-invoice, #datatable-closed').on('click', '.pdfAccountSales-account', function(){
			$('#pdfAccountSales-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable-order, #datatable-invoice, #datatable-closed').on('click', '.statusAccountSales-account', function(){
			$('#statusAccountSales-account input[name=id]').val($(this).data('id'));
			$('#statusAccountSales-account input[name=status]').val($(this).data('status'));
		});

		$('#datatable-order, #datatable-invoice, #datatable-closed').on('click', '.statusClosedAccountSales-account', function(){
			$('#statusClosedAccountSales-account input[name=id]').val($(this).data('id'));
			$('#statusClosedAccountSales-account input[name=status]').val($(this).data('status'));
		});

		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).attr('id'));
		});

		@if(Session::has('status-closed-crm-error'))
		$('#statusClosedAccountSales-account').modal('show');
		@endif

		@if(Session::has('pdf-error'))
		$('#pdfAccountSales-account').modal('show');
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
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')

	<h1>Account Sales</h1>

	@can('deleteAccountSales-account')
	<div id="deleteAccountSales-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountSales') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Account Sales?</h4>
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
	@endcan

	@can('statusAccountSales-account')
	<div id="statusAccountSales-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.statusAccountSales') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Change Status Account Sales?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="status" value="{{old('status')}}">
						<button type="submit" class="btn btn-primary">Change</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="statusClosedAccountSales-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.statusAccountSales') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Closed Status Account Sales?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="account_list_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account List <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_list_id" name="account_list_id" class="form-control {{$errors->first('account_list_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Account" data-allow-clear="true">
									<option value=""></option>
									@foreach($account_lists as $list)
									<option value="{{ $list->id }}" @if(old('account_list_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_list_id') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<input type="hidden" name="status" value="{{old('status')}}">
						<button type="submit" class="btn btn-primary">Change</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<div id="pdfAccountSales-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.pdfAccountSales') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>


					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>


	<div class="x_panel" style="overflow: auto;">

		<div class="">
			<ul id="tab" class="nav nav-tabs bar_tabs">
				<li class="{{ $request->tab === 'ORDER' || $request->tab == '' ? 'active' : ''}}"><a href="#order" id="order-tab" data-toggle="tab" class="tab-active">Order</a>
				</li>
				<li class="{{ $request->tab === 'INVOICE' ? 'active' : ''}}"><a href="#invoice" id="invoice-tab" data-toggle="tab" class="tab-active">Invoice</a>
				</li>
				<li class="{{ $request->tab === 'CLOSED' ? 'active' : ''}}"><a href="#closed" id="closed-tab" data-toggle="tab" class="tab-active">Closed</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'ORDER' || $request->tab == '' ? 'active in' : ''}}" id="order">
					<div class="row">
						<div class="col-md-6">
							<form class="form-inline" method="get">

							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="{{ route('backend.account.actionAccountSales') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
								@can('createAccountSales-account')
								<a href="{{ route('backend.account.createAccountSales') }}" class="btn btn-default">Create</a>
								@endif
								<select class="form-control" name="action">
									<option value="delete">Delete</option>
								</select>
								<button type="submit" class="btn btn-success">Apply Selected</button>
							</form>
						</div>
					</div>
					
					<div class="ln_solid"></div>

					<table class="table table-bordered" id="datatable-order">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th></th>
								<th>Client</th>
								<th>No Invoice</th>
								<th>No SPK</th>
								<th>Action</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tfoot>
					</table>
				</div>


				<div class="tab-pane fade {{ $request->tab === 'INVOICE' ? 'active in' : ''}}" id="invoice">
					<div class="row">
						<div class="col-md-6">
							<form class="form-inline" method="get">

							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="{{ route('backend.account.actionAccountSales') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
								@can('createAccountSales-account')
								<a href="{{ route('backend.account.createAccountSales') }}" class="btn btn-default">Create</a>
								@endif
								<select class="form-control" name="action">
									<option value="delete">Delete</option>
								</select>
								<button type="submit" class="btn btn-success">Apply Selected</button>
							</form>
						</div>
					</div>
					
					<div class="ln_solid"></div>

					<table class="table table-bordered" id="datatable-invoice">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th></th>
								<th>Client</th>
								<th>No Invoice</th>
								<th>No SPK</th>
								<th>Action</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tfoot>
					</table>
				</div>

				<div class="tab-pane fade {{ $request->tab === 'CLOSED' ? 'active in' : ''}}" id="closed">
					<div class="row">
						<div class="col-md-6">
							<form class="form-inline" method="get">

							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="{{ route('backend.account.actionAccountSales') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
								@can('createAccountSales-account')
								<a href="{{ route('backend.account.createAccountSales') }}" class="btn btn-default">Create</a>
								@endif
								<select class="form-control" name="action">
									<option value="delete">Delete</option>
								</select>
								<button type="submit" class="btn btn-success">Apply Selected</button>
							</form>
						</div>
					</div>
					
					<div class="ln_solid"></div>

					<table class="table table-bordered" id="datatable-closed">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th></th>
								<th>Client</th>
								<th>No Invoice</th>
								<th>No SPK</th>
								<th>Action</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tfoot>
					</table>
				</div>

			</div>
		</div>

		
	</div>

@endsection