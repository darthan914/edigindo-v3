@extends('backend.layout.master')

@section('title')
	Offer Dashboard
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
	
	$(function() {
		var table_sales = $('#datatable-sales').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.offer.datatablesDashboardSales') }}",
				type: "POST",
				data: {
			    	f_type        : $('*[name=f_type]').val(),
			    	f_year        : $('*[name=f_year]').val(),
			    	f_start_month : $('*[name=f_start_month]').val(),
			    	f_start_year  : $('*[name=f_start_year]').val(),
			    	f_end_month   : $('*[name=f_end_month]').val(),
			    	f_end_year    : $('*[name=f_end_year]').val(),
				},
			},
			columns: [
				{data: 'first_name', sClass: 'nowrap-cell'},

				{data: 'total_hj', sClass: 'number-format'},
				{data: 'offer_expo_sum_value', sClass: 'number-format'},

				{data: 'offer_all_count'},
				{data: 'offer_all_sum_value', sClass: 'number-format'},

				{data: 'offer_waiting_count'},
				{data: 'offer_waiting_sum_value', sClass: 'number-format'},

				{data: 'offer_success_count'},
				{data: 'offer_success_sum_value', sClass: 'number-format'},

				{data: 'offer_cancel_count'},
				{data: 'offer_cancel_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_count'},
				{data: 'offer_failed_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_pricing_count'},
				{data: 'offer_failed_pricing_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_timeline_count'},
				{data: 'offer_failed_timeline_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_other_count'},
				{data: 'offer_failed_other_sum_value', sClass: 'number-format'},
			],
			// scrollY: "400px",
			scrollX: true,
			dom: '<l<tr>ip>',
		});

		$('#datatable-sales').on('click', '.data-offer', function(){
	    	$('#datatables-data').DataTable().clear();
	    	$('#datatables-data').DataTable().destroy();
	    	$.post('{{ route('backend.offer.getData') }}', {
	    		id            : $(this).data('id'),
				type          : $(this).data('type'),
				arrange       : "SALES",
				f_year        : $('*[name=f_year]').val(),
				f_start_year  : $('*[name=f_start_year]').val(),
				f_start_month : $('*[name=f_start_month]').val(),
				f_end_year    : $('*[name=f_end_year]').val(),
				f_end_month   : $('*[name=f_end_month]').val(),
				f_type        : $('*[name=f_type]:checked').val(),
	    	},
	    	function(data) {
				$('#datatables-data').DataTable({
					data: data,
					columns: [
						{data: 'no_document'},
						{data: 'name'},
						{data: 'name_detail'},
						{data: 'value', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html(number_format(oData.value));
					        }
					    },
						{data: 'id', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<a class="btn btn-primary btn-xs" href="/edigindo/offer/edit/'+oData.id+'"><i class="fa fa-eye"></i></a>');
					        }
					    },
					],
				});
			});
	    });

		var table_client = $('#datatable-client').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.offer.datatablesDashboardClient') }}",
				type: "POST",
				data: {
			    	f_type        : $('*[name=f_type]').val(),
			    	f_year        : $('*[name=f_year]').val(),
			    	f_start_month : $('*[name=f_start_month]').val(),
			    	f_start_year  : $('*[name=f_start_year]').val(),
			    	f_end_month   : $('*[name=f_end_month]').val(),
			    	f_end_year    : $('*[name=f_end_year]').val(),
				},
			},
			columns: [
				{data: 'name', sClass: 'nowrap-cell'},

				{data: 'total_hj', sClass: 'number-format'},
				{data: 'offer_expo_sum_value', sClass: 'number-format'},

				{data: 'offer_all_count'},
				{data: 'offer_all_sum_value', sClass: 'number-format'},

				{data: 'offer_waiting_count'},
				{data: 'offer_waiting_sum_value', sClass: 'number-format'},

				{data: 'offer_success_count'},
				{data: 'offer_success_sum_value', sClass: 'number-format'},

				{data: 'offer_cancel_count'},
				{data: 'offer_cancel_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_count'},
				{data: 'offer_failed_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_pricing_count'},
				{data: 'offer_failed_pricing_sum_value', sClass: 'number-format'},

				{data: 'offer_failed_timeline_count'},
				{data: 'offer_failed_timeline_sum_value', sClass: 'number-format'},
				
				{data: 'offer_failed_other_count'},
				{data: 'offer_failed_other_sum_value', sClass: 'number-format'},
			],
			// scrollY: "400px",
			scrollX: true,
			dom: '<l<tr>ip>',
		});

		$('#datatable-client').on('click', '.data-offer', function(){
	    	$('#datatables-data').DataTable().clear();
	    	$('#datatables-data').DataTable().destroy();
	    	$.post('{{ route('backend.offer.getData') }}', {
	    		id            : $(this).data('id'),
				type          : $(this).data('type'),
				arrange       : "CLIENT",
				f_year        : $('*[name=f_year]').val(),
				f_start_year  : $('*[name=f_start_year]').val(),
				f_start_month : $('*[name=f_start_month]').val(),
				f_end_year    : $('*[name=f_end_year]').val(),
				f_end_month   : $('*[name=f_end_month]').val(),
				f_type        : $('*[name=f_type]:checked').val(),
	    	},
	    	function(data) {
				$('#datatables-data').DataTable({
					data: data,
					columns: [
						{data: 'no_document'},
						{data: 'name'},
						{data: 'name_detail'},
						{data: 'value', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html(number_format(oData.value));
					        }
					    },
						{data: 'id', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<a class="btn btn-primary btn-xs" href="/edigindo/offer/edit/'+oData.id+'"><i class="fa fa-eye"></i></a>');
					        }
					    },
					],
				});
			});
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
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')


	{{-- Data Offer --}}
	<div id="data-offer" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Data List</h4>
					</div>
					<div class="modal-body">
						<table class="table table-striped" id="datatables-data">
							<thead>
							    <tr>
							        <th>No Document</th>
							        <th>Name Project</th>
							        <th>Name Detail</th>
							        <th>Total Price</th>
							        <th>Link</th>
							    </tr>
						    </thead>
						    <tbody class="data-list">
						    	
						    </tbody>
						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Offer Dashboard</h1>
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
							<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
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


	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li role="presentation" class="active"><a href="#dashboard-sales" id="dashboard-sales-tab" role="dashboard-sales-tab" data-toggle="tab" aria-expanded="true">Data Sales</a>
				</li>
				<li role="presentation" class=""><a href="#dashboard-klien" role="tab" id="dashboard-klien-tab" data-toggle="tab" aria-expanded="false">Data Company</a>
				</li>
			</ul>
			<div id="dashboardTabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade active in" id="dashboard-sales" aria-labelledby="dashboard-sales-tab">
					<table class="table table-striped table-bordered" id="datatable-sales">
						<thead>
							<tr>
								<th rowspan="2">Name Sales</th>
								<th rowspan="2">Sell Price</th>
								<th rowspan="2">Offer Expo</th>
								<th colspan="2" align="center">Offer</th>

								<th colspan="2" align="center">Waiting</th>
								<th colspan="2" align="center">Success</th>
								<th colspan="2" align="center">Cancel</th>

								<th colspan="2" align="center">Total Failed</th>
								<th colspan="2" align="center">Failed -> Pricing</th>
								<th colspan="2" align="center">Failed -> Timeline</th>

								<th colspan="2" align="center">Failed -> Other</th>
							</tr>
							<tr>
								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>
							</tr>
						</thead>
						<tfoot>
							<th>Total Offer</th>

							<th>Rp. {{ number_format( $sales->sum('total_hj')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_expo_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_all_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_all_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_waiting_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_waiting_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_success_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_success_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_cancel_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_cancel_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_failed_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_failed_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_failed_pricing_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_failed_pricing_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_failed_timeline_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_failed_timeline_sum_value')) }}</th>

							<th>{{ number_format( $sales->sum('offer_failed_other_count')) }}</th>
							<th>Rp. {{ number_format( $sales->sum('offer_failed_other_sum_value')) }}</th>
						</tfoot>
					</table>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="dashboard-klien" aria-labelledby="dashboard-klien-tab">
					<table class="table table-striped table-bordered" id="datatable-client">
						<thead>
							<tr>
								<th rowspan="2">Name Company</th>
								<th rowspan="2">Sell Price</th>
								<th rowspan="2">Offer Expo</th>
								<th colspan="2" align="center">Offer</th>

								<th colspan="2" align="center">Waiting</th>
								<th colspan="2" align="center">Success</th>
								<th colspan="2" align="center">Cancel</th>

								<th colspan="2" align="center">Failed</th>
								<th colspan="2" align="center">Failed -> Pricing</th>
								<th colspan="2" align="center">Failed -> Timeline</th>

								<th colspan="2" align="center">Failed -> Other</th>
							</tr>
							<tr>
								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>

								<th>Count</th>
								<th>Value</th>
							</tr>
						</thead>
						<tfoot>
							<th>Total Offer</th>

							<th>Rp. {{ number_format( $client->sum('total_hj')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_expo_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_all_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_all_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_waiting_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_waiting_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_success_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_success_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_cancel_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_cancel_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_failed_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_failed_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_failed_pricing_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_failed_pricing_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_failed_timeline_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_failed_timeline_sum_value')) }}</th>

							<th>{{ number_format( $client->sum('offer_failed_other_count')) }}</th>
							<th>Rp. {{ number_format( $client->sum('offer_failed_other_sum_value')) }}</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		
	</div>

@endsection