@extends('backend.layout.master')

@section('title')
	SPK Dashboard
@endsection

@section('script')
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

		var template = Handlebars.compile($("#details-template").html());

		function initTable(tableId, salesId, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.spk.datatablesDetailDashboard') }}",
					type: "POST",
					data: {
						id            : salesId,

						f_type        : $('*[name=f_type]:checked').val(),
						
						f_year        : $('*[name=f_year]').val(),
						f_month       : $('*[name=f_month]').val(),
						
						f_start_year  : $('*[name=f_start_year]').val(),
						f_start_month : $('*[name=f_start_month]').val(),
						f_end_year    : $('*[name=f_end_year]').val(),
						f_end_month   : $('*[name=f_end_month]').val(),
					},
				},
	            columns: [
	                { data: 'no_spk'},
	                { data: 'date_spk'},
	                { data: 'total_hm', sClass: 'number-format'},
	                { data: 'total_hj', sClass: 'number-format'},
	                { data: 'total_real_omset', sClass: 'number-format'},
	                { data: 'total_loss', sClass: 'number-format'},
	                { data: 'action'},
	            ],
	            scrollY: "200px",
	            dom: '<l<tr>ip>',
	        })
	    }
		
		var table_sales = $('#datatable-sales').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesSalesDashboard') }}",
				type: "POST",
				data: {
					f_year : $('*[name=f_year]').val(),
				},
			},
			columns: [
				{
	                className  : "details-control",
	                orderable  : false,
	                searchable : false,
	                data       : null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'first_name', orderable: true, searchable: true, sClass: 'nowrap-cell'},
				{data: 'total_offer', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'count_spk', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hm', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_loss', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'value', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'percent', orderable: true, searchable: false, sClass: 'nowrap-cell'},
			],
			dom: '<l<tr>ip>',
		});

		$('#datatable-sales tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table_sales.row( tr );
	        var tableId = 'posts-' + row.data().id;
	        var salesId = row.data().id;
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child(template(row.data())).show();
	            initTable(tableId, salesId, row.data());
	            tr.addClass('shown');
	            tr.next().find('td').addClass('no-padding bg-gray');
	        }
	    } );

		var table_monthly = $('#datatable-monthly').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesMonthlyDashboard') }}",
				type: "POST",
				data: {
					f_type : $('*[name=f_type]').val(),
					f_expo : 'nonexpo',

					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
				},
			},
			columns: [
				{data: 'first_name', orderable: true, searchable: true, sClass: 'nowrap-cell'},
				{data: 'total_hj_1', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_2', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_3', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_4', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_5', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_6', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_7', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_8', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_9', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_10', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_11', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_12', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj', orderable: true, searchable: false, sClass: 'nowrap-cell'},
			],
			dom: '<l<tr>ip>',
		});

		var table_monthlyRealOmset = $('#datatable-monthlyRealOmset').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesMonthlyDashboard') }}",
				type: "POST",
				data: {
					f_type : $('*[name=f_type]').val(),
					f_expo : 'nonexpo',

					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
				},
			},
			columns: [
				{data: 'first_name', orderable: true, searchable: true, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_1', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_2', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_3', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_4', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_5', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_6', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_7', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_8', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_9', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_10', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_11', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_12', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset', orderable: true, searchable: false, sClass: 'nowrap-cell'},
			],
			dom: '<l<tr>ip>',
		});

		var table_monthly_expo = $('#datatable-monthly-expo').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesMonthlyDashboard') }}",
				type: "POST",
				data: {
					f_type : $('*[name=f_type]').val(),
					f_expo : 'expo',

					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
				},
			},
			columns: [
				{data: 'first_name', orderable: true, searchable: true, sClass: 'nowrap-cell'},
				{data: 'total_hj_1', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_2', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_3', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_4', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_5', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_6', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_7', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_8', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_9', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_10', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_11', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj_12', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_hj', orderable: true, searchable: false, sClass: 'nowrap-cell'},
			],
			dom: '<l<tr>ip>',
		});

		var table_monthlyRealOmset = $('#datatable-monthlyRealOmset-expo').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesMonthlyDashboard') }}",
				type: "POST",
				data: {
					f_type : $('*[name=f_type]').val(),
					f_expo : 'expo',

					f_year        : $('*[name=f_year]').val(),
					f_month       : $('*[name=f_month]').val(),
					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
				},
			},
			columns: [
				{data: 'first_name', orderable: true, searchable: true, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_1', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_2', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_3', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_4', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_5', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_6', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_7', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_8', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_9', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_10', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_11', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset_12', orderable: true, searchable: false, sClass: 'nowrap-cell'},
				{data: 'total_real_omset', orderable: true, searchable: false, sClass: 'nowrap-cell'},
			],
			dom: '<l<tr>ip>',
		});

		
		@if(Session::has('excel-spk-error'))
		$('#excel-spk').modal('show');
		@endif


		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).data('id'));
		});
	});
</script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-bordered details-table" id="posts-@{{id}}">
        <thead>
        <tr>
            <th>SPK</th>
            <th>Date</th>
            <th>Total Modal Price</th>
			<th>Total Sell Price</th>
			<th>Total Real Omset</th>
			<th>Total Loss</th>
            <th></th>
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

	{{-- SPK Excel --}}
	<div id="excel-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.excel') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Export Excel</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="xls_year" class="control-label col-md-3 col-sm-3 col-xs-12">Year <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" name="xls_year">
									<option value="">This Year</option>
									<option value="all" {{ old('xls_year') == 'all' ? 'selected' : '' }}>All Year</option>
									@foreach($year as $list)
									<option value="{{ $list->year }}" {{ old('xls_year')  == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('xls_year') }}</li>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<label for="xls_month" class="control-label col-md-3 col-sm-3 col-xs-12">Month <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" name="xls_month">
									<option value="">This Month</option>
									<option value="all" {{ old('xls_month') == 'all' ? 'selected' : '' }}>All Month</option>
									@php $numMonth = 1; @endphp
									@foreach($month as $list)
									<option value="{{ $numMonth }}" {{ old('xls_month') == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('xls_month') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="spk_id" class="spk_id-onpdf" value="{{old('spk_id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>SPK Dashboard</h1>
	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
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
						<select class="form-control" name="f_year" onchange="this.form.submit()">
							<option value="">This Year</option>
							@foreach($year as $list)
							<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
							@endforeach
						</select>
					</div>
				</div>
				@elseif($request->f_type == 'range')
				<div class="col-md-12">
					<p>Range Filter</p>
					<div class="form-group">
						<select class="form-control" name="f_start_month" onchange="this.form.submit()">
							<option value="">This Month Start Range</option>
							@php $numMonth = 1; @endphp
							@foreach($month as $list)
							<option value="{{ $numMonth }}" {{ $request->f_start_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
							@endforeach
						</select>
						<select class="form-control" name="f_start_year" onchange="this.form.submit()">
							<option value="">This Year Start Range</option>
							@foreach($year as $list)
							<option value="{{ $list->year }}" {{ $request->f_start_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
							@endforeach
						</select>
					<div class="form-group">
					</div>
						<select class="form-control" name="f_end_month" onchange="this.form.submit()">
							<option value="">This Month End Range</option>
							@php $numMonth = 1; @endphp
							@foreach($month as $list)
							<option value="{{ $numMonth }}" {{ $request->f_end_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
							@endforeach
						</select>
						<select class="form-control" name="f_end_year" onchange="this.form.submit()">
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

	@can('excel-spk')
	<div class="x_panel" style="overflow: auto;">
		<button data-toggle="modal" data-target="#excel-spk" class="btn btn-success"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</button>
	</div>
	@endcan

	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li class="{{ $request->tab === 'dashboard' || $request->tab == '' ? 'active' : ''}}"><a href="#dashboard" data-toggle="tab" class="tab-active" data-id="dashboard">Data Sales</a>
				</li>
				<li class="{{ $request->tab === 'sell_price_monthly' ? 'active' : ''}}"><a href="#sell_price_monthly" data-toggle="tab" class="tab-active" data-id="sell_price_monthly">Sell Price Monthly</a>
				</li>
				<li class="{{ $request->tab === 'real_omset_monthly' ? 'active' : ''}}"><a href="#real_omset_monthly" data-toggle="tab" class="tab-active" data-id="real_omset_monthly">Real Omset Monthly</a>
				</li>

				<li class="{{ $request->tab === 'sell_price_monthly_expo' ? 'active' : ''}}"><a href="#sell_price_monthly_expo" data-toggle="tab" class="tab-active" data-id="sell_price_monthly_expo">Sell Price Monthly Expo</a>
				</li>
				<li class="{{ $request->tab === 'real_omset_monthly_expo' ? 'active' : ''}}"><a href="#real_omset_monthly_expo" data-toggle="tab" class="tab-active" data-id="real_omset_monthly_expo">Real Omset Monthly Expo</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'dashboard' || $request->tab == '' ? 'active in' : ''}}" id="dashboard" >
					<table class="table table-striped" id="datatable-sales">
						<thead>
							<th></th>

							<th>Name Sales</th>
							<th>Total Offer</th>
							<th>Count SPK</th>

							<th>Total Modal Price</th>
							<th>Total Sell Price</th>
							<th>Total Real Omset</th>

							<th>Total Loss</th>
							<th>Target</th>

							<th>Percent</th>
						</thead>
						
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'sell_price_monthly' ? 'active in' : ''}}" id="sell_price_monthly">
					<table class="table table-striped" id="datatable-monthly">
						<thead>
							<th>Name Sales</th>

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
						
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'real_omset_monthly' ? 'active in' : ''}}" id="real_omset_monthly">
					<table class="table table-striped" id="datatable-monthlyRealOmset">
						<thead>
							<th>Name Sales</th>

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
						
					</table>
				</div>

				<div class="tab-pane fade {{ $request->tab === 'sell_price_monthly_expo' ? 'active in' : ''}}" id="sell_price_monthly_expo">
					<table class="table table-striped" id="datatable-monthly-expo">
						<thead>
							<th>Name Sales</th>

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
						
					</table>
				</div>
				<div class="tab-pane fade {{ $request->tab === 'real_omset_monthly_expo' ? 'active in' : ''}}" id="real_omset_monthly_expo">
					<table class="table table-striped" id="datatable-monthlyRealOmset-expo">
						<thead>
							<th>Name Sales</th>

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
						
					</table>
				</div>
			</div>
		</div>

	</div>

@endsection