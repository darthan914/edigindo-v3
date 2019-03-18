@extends('backend.layout.master')

@section('title')
	Dashboard Company
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js"></script>
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

	$(function() {

		var templateKlien = Handlebars.compile($("#details-template-client").html());
		var templateYearly = Handlebars.compile($("#details-template-yearly").html());
		var templateYearlyInvoice = Handlebars.compile($("#details-template-yearlyInvoice").html());

		var table_client = $('#datatable-client').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesClientDashboard') }}",
				type: "post",
				data : {
					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_sales       : $('*[name=f_sales]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
					f_type        : $('*[name=f_type]:checked').val(),
				}
			},
			columns: [
				{
					className  : "details-control",
					orderable  : false,
					searchable : false,
					data       : null,
					defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
				},
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'count_spk', sClass: 'number-format'},
				{data: 'total_offer', sClass: 'number-format'},
				{data: 'total_hm', sClass: 'number-format'},
				{data: 'total_hj', sClass: 'number-format'},
				{data: 'sum_value_invoice', searchable: false, sClass: 'number-format'},
				{data: 'total_amends', searchable: false, sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
		});

		// Add event listener for opening and closing details
		$('#datatable-client tbody').on( 'click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
			var row = table_client.row( tr );
			var tableId = 'posts-' + row.data().id + '-client';
			var companyId = row.data().id;
	 
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child(templateKlien(row.data())).show();
				initTableClient(tableId, companyId, row.data());
				tr.addClass('shown');
				tr.next().find('td').addClass('no-padding bg-gray');
			}
		});

		function initTableClient(tableId, companyId, data) {
			$('#' + tableId).DataTable({
				processing: true,
				serverSide: true,
				ajax: {
				url: "{{ route('backend.company.datatablesDetailDashboard') }}",
					type: "POST",
					data: {
						company_id    : companyId,
						f_year        : $('*[name=f_year]').val(),
						f_month       : $('*[name=f_month]').val(),
						f_sales       : $('*[name=f_sales]').val(),
						f_start_year  : $('*[name=f_start_year]').val(),
						f_start_month : $('*[name=f_start_month]').val(),
						f_end_year    : $('*[name=f_end_year]').val(),
						f_end_month   : $('*[name=f_end_month]').val(),
						f_type        : $('*[name=f_type]:checked').val(),
					},
				},
				columns: [
					{ data: 'no_spk'},
					{ data: 'date_spk'},
					{ data: 'total_hm', sClass: 'number-format'},
					{ data: 'total_hj', sClass: 'number-format'},
					{ data: 'sum_value_invoice', sClass: 'number-format'},
					{ data: 'action'},
				],
				scrollY: "200px",
			});
		}

		var table_monthly = $('#datatable-monthly').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesMonthlyDashboard') }}",
				type: "post",
				data : {
					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_sales       : $('*[name=f_sales]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
					f_type        : $('*[name=f_type]:checked').val(),
				}
			},
			columns: [
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'total_hj_1', sClass: 'number-format'},
				{data: 'total_hj_2', sClass: 'number-format'},
				{data: 'total_hj_3', sClass: 'number-format'},
				{data: 'total_hj_4', sClass: 'number-format'},
				{data: 'total_hj_5', sClass: 'number-format'},
				{data: 'total_hj_6', sClass: 'number-format'},
				{data: 'total_hj_7', sClass: 'number-format'},
				{data: 'total_hj_8', sClass: 'number-format'},
				{data: 'total_hj_9', sClass: 'number-format'},
				{data: 'total_hj_10', sClass: 'number-format'},
				{data: 'total_hj_11', sClass: 'number-format'},
				{data: 'total_hj_12', sClass: 'number-format'},
				{data: 'total_hj', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
		});

		var table_monthly_invoice = $('#datatable-monthlyInvoice').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesMonthlyDashboard') }}",
				type: "post",
				data : {
					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_sales       : $('*[name=f_sales]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
					f_type        : $('*[name=f_type]:checked').val(),
				}
			},
			columns: [
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'sum_value_invoice_1', sClass: 'number-format'},
				{data: 'sum_value_invoice_2', sClass: 'number-format'},
				{data: 'sum_value_invoice_3', sClass: 'number-format'},
				{data: 'sum_value_invoice_4', sClass: 'number-format'},
				{data: 'sum_value_invoice_5', sClass: 'number-format'},
				{data: 'sum_value_invoice_6', sClass: 'number-format'},
				{data: 'sum_value_invoice_7', sClass: 'number-format'},
				{data: 'sum_value_invoice_8', sClass: 'number-format'},
				{data: 'sum_value_invoice_9', sClass: 'number-format'},
				{data: 'sum_value_invoice_10', sClass: 'number-format'},
				{data: 'sum_value_invoice_11', sClass: 'number-format'},
				{data: 'sum_value_invoice_12', sClass: 'number-format'},
				{data: 'sum_value_invoice', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
		});

		var table_yearly = $('#datatable-yearly').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesYearlyDashboard') }}",
				type: "post",
				data : {
					f_sales: $('*[name=f_sales]').val(),
					f_month : $('*[name=f_month]').val(),
				}
			},
			columns: [
				{
					className  : "details-control",
					orderable  : false,
					searchable : false,
					data       : null,
					defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
				},
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'total_hj_minus_4', sClass: 'number-format'},
				{data: 'total_hj_minus_3', sClass: 'number-format'},
				{data: 'total_hj_minus_2', sClass: 'number-format'},
				{data: 'total_hj_minus_1', sClass: 'number-format'},
				{data: 'total_hj_minus_0', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
		});

		$('#datatable-yearly tbody').on('click', 'td.details-control > button', function () {
			var tr = $(this).closest('tr');
			var row = table_yearly.row( tr );
			var tableId = 'posts-' + row.data().id + '-yearly';
			var companyId = row.data().id;
	 
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child(templateYearly(row.data())).show();
				initTableYearly(tableId, companyId, row.data());
				tr.addClass('shown');
				tr.next().find('td').addClass('no-padding bg-gray');
			}
		} );

		function initTableYearly(tableId, companyId, data) {
			$('#' + tableId).DataTable({
				processing: true,
				serverSide: true,
				ajax: {
				url: "{{ route('backend.company.datatablesDataYearlyDetail') }}",
					type: "POST",
					data: {
						company_id : companyId,
						f_sales: $('*[name=f_sales]').val(),
					},
				},
				columns: [
					{data: 'first_name', sClass: 'nowrap-cell'},
					{data: 'total_hj_{{ date('Y') - 4 }}', sClass: 'number-format'},
					{data: 'total_hj_{{ date('Y') - 3 }}', sClass: 'number-format'},
					{data: 'total_hj_{{ date('Y') - 2 }}', sClass: 'number-format'},
					{data: 'total_hj_{{ date('Y') - 1 }}', sClass: 'number-format'},
					{data: 'total_hj_{{ date('Y') - 0 }}', sClass: 'number-format'},
				],
				scrollY: "200px",
			});
		}

		var table_yearly_invoice = $('#datatable-yearlyInvoice').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesYearlyDashboard') }}",
				type: "post",
				data : {
					f_sales: $('*[name=f_sales]').val(),
					f_month : $('*[name=f_month]').val(),
				}
			},
			columns: [
				{
					className  : "details-control",
					orderable  : false,
					searchable : false,
					data       : null,
					defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
				},
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'sum_value_invoice_minus_4', sClass: 'number-format'},
				{data: 'sum_value_invoice_minus_3', sClass: 'number-format'},
				{data: 'sum_value_invoice_minus_2', sClass: 'number-format'},
				{data: 'sum_value_invoice_minus_1', sClass: 'number-format'},
				{data: 'sum_value_invoice_minus_0', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
		});

		// Add event listener for opening and closing details
		$('#datatable-yearlyCount tbody').on('click', 'td.details-control > button', function () {
			var tr = $(this).closest('tr');
			var row = table_yearly_invoice.row( tr );
			var tableId = 'posts-' + row.data().id + '-yearlyInvoice';
			var companyId = row.data().id;
	 
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child(templateYearlyInvoice(row.data())).show();
				initTableYearlyInvoice(tableId, companyId, row.data());
				tr.addClass('shown');
				tr.next().find('td').addClass('no-padding bg-gray');
			}
		} );

		function initTableYearlyInvoice(tableId, companyId, data) {
			$('#' + tableId).DataTable({
				processing: true,
				serverSide: true,
				ajax: {
				url: "{{ route('backend.company.datatablesDataYearlyDetail') }}",
					type: "POST",
					data: {
						company_id : companyId,
						f_sales: $('*[name=f_sales]').val(),
					},
				},
				columns: [
					{data: 'first_name', sClass: 'nowrap-cell'},
					{data: 'sum_value_invoice_{{ date('Y') - 4 }}', sClass: 'number-format'},
					{data: 'sum_value_invoice_{{ date('Y') - 3 }}', sClass: 'number-format'},
					{data: 'sum_value_invoice_{{ date('Y') - 2 }}', sClass: 'number-format'},
					{data: 'sum_value_invoice_{{ date('Y') - 1 }}', sClass: 'number-format'},
					{data: 'sum_value_invoice_{{ date('Y') - 0 }}', sClass: 'number-format'},
				],
				scrollY: "200px",
			});
		}

		var table_yearly_count = $('#datatable-yearlyCount').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesYearlyDashboard') }}",
				type: "post",
				data : {
					f_sales: $('*[name=f_sales]').val(),
					f_month : $('*[name=f_month]').val(),
				}
			},
			columns: [
				{data: 'name', sClass: 'nowarp-cell'},
				{data: 'count_spk_minus_4_q1', sClass: 'number-format'},
				{data: 'count_spk_minus_4_q2', sClass: 'number-format'},
				{data: 'count_spk_minus_4_q3', sClass: 'number-format'},
				{data: 'count_spk_minus_4_q4', sClass: 'number-format'},

				{data: 'count_spk_minus_3_q1', sClass: 'number-format'},
				{data: 'count_spk_minus_3_q2', sClass: 'number-format'},
				{data: 'count_spk_minus_3_q3', sClass: 'number-format'},
				{data: 'count_spk_minus_3_q4', sClass: 'number-format'},

				{data: 'count_spk_minus_2_q1', sClass: 'number-format'},
				{data: 'count_spk_minus_2_q2', sClass: 'number-format'},
				{data: 'count_spk_minus_2_q3', sClass: 'number-format'},
				{data: 'count_spk_minus_2_q4', sClass: 'number-format'},

				{data: 'count_spk_minus_1_q1', sClass: 'number-format'},
				{data: 'count_spk_minus_1_q2', sClass: 'number-format'},
				{data: 'count_spk_minus_1_q3', sClass: 'number-format'},
				{data: 'count_spk_minus_1_q4', sClass: 'number-format'},

				{data: 'count_spk_minus_0_q1', sClass: 'number-format'},
				{data: 'count_spk_minus_0_q2', sClass: 'number-format'},
				{data: 'count_spk_minus_0_q3', sClass: 'number-format'},
				{data: 'count_spk_minus_0_q4', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
			scrollY: "400px",
			scrollX: true,
		});

		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).data('tab-name'));
		});

	});
</script>
<script id="details-template-client" type="text/x-handlebars-template">
	<table class="table table-bordered details-table" id="posts-@{{id}}-client">
		<thead>
		<tr>
			<th>SPK</th>
			<th>Date</th>
			<th>Total Modal Price</th>
			<th>Total Sell Price</th>
			<th>Total Invoice</th>
			<th></th>
		</tr>
		</thead>
	</table>
</script>

<script id="details-template-yearly" type="text/x-handlebars-template">
	<table class="table table-bordered details-table" id="posts-@{{id}}-yearly">
		<thead>
		<tr>
			<th>Name</th>
			<th>{{ date('Y') - 4 }}</th>
			<th>{{ date('Y') - 3 }}</th>
			<th>{{ date('Y') - 2 }}</th>
			<th>{{ date('Y') - 1 }}</th>
			<th>{{ date('Y') }}</th>
		</tr>
		</thead>
	</table>
</script>

<script id="details-template-yearlyInvoice" type="text/x-handlebars-template">
	<table class="table table-bordered details-table" id="posts-@{{id}}-yearlyInvoice">
		<thead>
		<tr>
			<th>Name</th>
			<th>{{ date('Y') - 4 }}</th>
			<th>{{ date('Y') - 3 }}</th>
			<th>{{ date('Y') - 2 }}</th>
			<th>{{ date('Y') - 1 }}</th>
			<th>{{ date('Y') }}</th>
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

	.number-format{
		text-align: right;
		white-space: nowrap;
	}

</style>
@endsection

@section('content')
	
	<h1>Dashboard Company</h1>

	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
			<div class="row">
				<div class="col-md-12">
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						@can('full-user')
							<option value="" {{ $request->f_sales == '' ? 'selected' : '' }}>All Sales</option>
						@endcan
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="radio-inline"><input type="radio" name="f_type"  @if($request->f_type == 'single' || $request->f_type == '') checked @endif value="single" onchange="this.form.submit()">Single</label>
						<label class="radio-inline"><input type="radio" name="f_type"  @if($request->f_type == 'range') checked @endif value="range" onchange="this.form.submit()">Range</label>
					</div>
				</div>
			</div>
			
			<div class="row">
				@if($request->f_type == 'single' || $request->f_type == '')
				<div class="col-md-12">
					<p>Single Filter</p>
					<div class="form-group">
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
					</div>
				</div>
				@elseif($request->f_type == 'range')
				<div class="col-md-12">
					<p>Range Filter</p>
					<div class="form-group">
						<select class="form-control select2" name="f_start_month" onchange="this.form.submit()">
							<option value="">This Month Start Range</option>
							@php $numMonth = 1; @endphp
							@foreach($month as $list)
							<option value="{{ $numMonth }}" {{ $request->f_start_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
							@endforeach
						</select>
						<select class="form-control select2" name="f_start_year" onchange="this.form.submit()">
							<option value="">This Year Start Range</option>
							@foreach($year as $list)
							<option value="{{ $list->year }}" {{ $request->f_start_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
							@endforeach
						</select>
					<div class="form-group">
					</div>
						<select class="form-control select2" name="f_end_month" onchange="this.form.submit()">
							<option value="">This Month End Range</option>
							@php $numMonth = 1; @endphp
							@foreach($month as $list)
							<option value="{{ $numMonth }}" {{ $request->f_end_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
							@endforeach
						</select>
						<select class="form-control select2" name="f_end_year" onchange="this.form.submit()">
							<option value="">This Year End</option>
							@foreach($year as $list)
							<option value="{{ $list->year }}" {{ $request->f_end_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
							@endforeach
						</select>
					</div>
				</div>
				@endif
			</div>
			
			<input type="hidden" name="tab">
		</form>
	</div>

	<div class="x_panel" style="overflow: auto;">
		<div class="" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li class="{{ $request->tab === 'dashboard_client' || $request->tab == '' ? 'active' : ''}}">
					<a href="#dashboard_client" data-toggle="tab" class="tab-active" data-tab-name="dashboard_client">Data Client</a>
				</li>
				<li class="{{ $request->tab === 'sell-price-monthly-tab' ? 'active' : ''}}">
					<a href="#sell_price_monthly" data-toggle="tab" class="tab-active" data-tab-name="sell_price_monthly">Sell Price Monthly</a>
				</li>
				<li class="{{ $request->tab === 'real-omset-monthly-tab' ? 'active' : ''}}">
					<a href="#real_omset_monthly" data-toggle="tab" class="tab-active" data-tab-name="real_omset_monthly">Invoice Monthly</a>
				</li>
				<li class="{{ $request->tab === 'sell-price-yearly-tab' ? 'active' : ''}}">
					<a href="#sell_price_yearly" data-toggle="tab" class="tab-active" data-tab-name="sell_price_yearly">Sell Price Yearly</a>
				</li>
				<li class="{{ $request->tab === 'real-omset-yearly-tab' ? 'active' : ''}}">
					<a href="#real_omset_yearly" data-toggle="tab" class="tab-active" data-tab-name="real_omset_yearly">Invoice Yearly</a>
				</li>

				<li class="{{ $request->tab === 'count_yearly_tab' ? 'active' : ''}}">
					<a href="#count_yearly_tab" data-toggle="tab" class="tab-active" data-tab-name="count_yearly_tab">Count SPK Yearly</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'home-tab' || $request->tab == '' ? 'active in' : ''}}" id="dashboard_client">
					<table class="table table-striped" id="datatable-client">
						<thead>
							<th></th>
							<th>Name Company</th>
							<th>Count SPK</th>

							<th>Total Offer</th>
							<th>Total Modal Price</th>
							<th>Total Sell Price</th>
							<th>Total Invoice</th>
							<th>Total Amends</th>
						</thead>
						<tfoot>
							<th>Total</th>
							<th></th>
							<th><span id="countSPK"></span></th>

							<th><span id="totalOffer"></span></th>
							<th><span id="totalHM"></span></th>
							<th><span id="totalHJ"></span></th>
							<th><span id="totalInvoice"></span></th>
							<th><span id="totalOutstanding"></span></th>
						</tfoot>
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'sell-price-monthly-tab' ? 'active in' : ''}}" id="sell_price_monthly">
					<table class="table table-striped" id="datatable-monthly">
						<thead>
							<th>Name Company</th>

							<th>January</th>
							<th>Febuary</th>
							<th>March</th>

							<th>April</th>
							<th>May</th>
							<th>June</th>

							<th>July</th>
							<th>August</th>
							<th>September</th>

							<th>October</th>
							<th>November</th>
							<th>December</th>

							<th>Total</th>
						</thead>
						<tfoot>
							<th>Total</th>

							<th><span id="total_january"></span></th>
							<th><span id="total_febuary"></span></th>
							<th><span id="total_march"></span></th>

							<th><span id="total_april"></span></th>
							<th><span id="total_may"></span></th>
							<th><span id="total_june"></span></th>

							<th><span id="total_july"></span></th>
							<th><span id="total_august"></span></th>
							<th><span id="total_september"></span></th>

							<th><span id="total_october"></span></th>
							<th><span id="total_november"></span></th>
							<th><span id="total_december"></span></th>

							<th><span id="total_all"></span></th>
						</tfoot>
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'real-omset-monthly-tab' ? 'active in' : ''}}" id="real_omset_monthly">
					<table class="table table-striped" id="datatable-monthlyInvoice">
						<thead>
							<th>Name Company</th>

							<th>January</th>
							<th>Febuary</th>
							<th>March</th>

							<th>April</th>
							<th>May</th>
							<th>June</th>

							<th>July</th>
							<th>August</th>
							<th>September</th>

							<th>October</th>
							<th>November</th>
							<th>December</th>

							<th>Total</th>
						</thead>
						<tfoot>
							<th>Total</th>

							<th><span id="total_january-inv"></span></th>
							<th><span id="total_febuary-inv"></span></th>
							<th><span id="total_march-inv"></span></th>

							<th><span id="total_april-inv"></span></th>
							<th><span id="total_may-inv"></span></th>
							<th><span id="total_june-inv"></span></th>

							<th><span id="total_july-inv"></span></th>
							<th><span id="total_august-inv"></span></th>
							<th><span id="total_september-inv"></span></th>

							<th><span id="total_october-inv"></span></th>
							<th><span id="total_november-inv"></span></th>
							<th><span id="total_december-inv"></span></th>

							<th><span id="total_all-inv"></span></th>
						</tfoot>
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'sell-price-yearly-tab' ? 'active in' : ''}}" id="sell_price_yearly">
					<table class="table table-striped" id="datatable-yearly">
						<thead>
							<th></th>
							<th>Name Company</th>

							<th>{{ date('Y') - 4 }}</th>
							<th>{{ date('Y') - 3 }}</th>
							<th>{{ date('Y') - 2 }}</th>

							<th>{{ date('Y') - 1 }}</th>
							<th>{{ date('Y') }}</th>

						</thead>
						<tfoot>
							<th></th>
							<th>Total</th>

							<th><span id="total_yearminus4"></span></th>
							<th><span id="total_yearminus3"></span></th>
							<th><span id="total_yearminus2"></span></th>

							<th><span id="total_yearminus1"></span></th>
							<th><span id="total_yearminus0"></span></th>

						</tfoot>
					</table>
				</div>

				<div class="tab-pane fade {{ $request->tab === 'real-omset-yearly-tab' ? 'active in' : ''}}" id="real_omset_yearly">
					<table class="table table-striped" id="datatable-yearlyInvoice">
						<thead>
							<th></th>
							<th>Name Company</th>

							<th>{{ date('Y') - 4 }}</th>
							<th>{{ date('Y') - 3 }}</th>
							<th>{{ date('Y') - 2 }}</th>

							<th>{{ date('Y') - 1 }}</th>
							<th>{{ date('Y') }}</th>

						</thead>
						<tfoot>
							<th></th>
							<th>Total</th>

							<th><span id="total_yearminus4-inv"></span></th>
							<th><span id="total_yearminus3-inv"></span></th>
							<th><span id="total_yearminus2-inv"></span></th>

							<th><span id="total_yearminus1-inv"></span></th>
							<th><span id="total_yearminus0-inv"></span></th>

						</tfoot>
					</table>
				</div>

				<div class="tab-pane fade {{ $request->tab === 'count_yearly_tab' ? 'active in' : ''}}" id="count_yearly_tab">
					<table class="table table-striped table-bordered" id="datatable-yearlyCount">
						<thead>
							<tr>
								<th rowspan="2">Name Company</th>

								<th colspan="4">{{ date('Y') - 4 }}</th>
								<th colspan="4">{{ date('Y') - 3 }}</th>
								<th colspan="4">{{ date('Y') - 2 }}</th>

								<th colspan="4">{{ date('Y') - 1 }}</th>
								<th colspan="4">{{ date('Y') }}</th>
							</tr>
							<tr>
								<th>Q1</th>
								<th>Q2</th>
								<th>Q3</th>
								<th>Q4</th>

								<th>Q1</th>
								<th>Q2</th>
								<th>Q3</th>
								<th>Q4</th>

								<th>Q1</th>
								<th>Q2</th>
								<th>Q3</th>
								<th>Q4</th>

								<th>Q1</th>
								<th>Q2</th>
								<th>Q3</th>
								<th>Q4</th>

								<th>Q1</th>
								<th>Q2</th>
								<th>Q3</th>
								<th>Q4</th>
							</tr>

						</thead>

					</table>
				</div>

			</div>
		</div>

					
	</div>
	

@endsection