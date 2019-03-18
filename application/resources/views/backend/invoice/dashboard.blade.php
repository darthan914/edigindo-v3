@extends('backend.layout.master')

@section('title')
	Dashboard SPK Recap
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

	function collectData() {
		$('#stat-count-onprogress, #stat-sumHJ-onprogress, #stat-sumInv-onprogress, #stat-sumPR-onprogress, #stat-amends-onprogress').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-count-unapprove, #stat-sumHJ-unapprove, #stat-sumInv-unapprove, #stat-sumPR-unapprove, #stat-amends-unapprove').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-count-kb, #stat-sumHJ-kb, #stat-sumInv-kb, #stat-sumPR-kb, #stat-amends-kb').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-count-bk, #stat-sumHJ-bk, #stat-sumInv-bk, #stat-sumPR-bk, #stat-amends-bk').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
		$('#stat-count-kt, #stat-sumHJ-kt, #stat-sumInv-kt, #stat-sumPR-kt, #stat-amends-kt').html('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');

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
				$('#stat-count-onprogress').html(number_format(data.count));
				$('#stat-sumHJ-onprogress').html(number_format(data.total_hj));
				$('#stat-sumInv-onprogress').html(number_format(data.sum_value_invoice));
				$('#stat-sumPR-onprogress').html(number_format(data.sum_value_pr));
				$('#stat-amends-onprogress').html(number_format(data.amends));
		});

		$.post('{{ route('backend.invoice.getStatus') }}', 
			{
				f_done		 : 'UNFINISH_SPK',
				f_sales      : $('*[name=f_sales]').val(),
				f_year		 : $('*[name=f_year]').val(),
				f_month      : $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-count-unapprove').html(number_format(data.count));
				$('#stat-sumHJ-unapprove').html(number_format(data.total_hj));
				$('#stat-sumInv-unapprove').html(number_format(data.sum_value_invoice));
				$('#stat-sumPR-unapprove').html(number_format(data.sum_value_pr));
				$('#stat-amends-unapprove').html(number_format(data.amends));
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
				$('#stat-count-kb').html(number_format(data.count));
				$('#stat-sumHJ-kb').html(number_format(data.total_hj));
				$('#stat-sumInv-kb').html(number_format(data.sum_value_invoice));
				$('#stat-sumPR-kb').html(number_format(data.sum_value_pr));
				$('#stat-amends-kb').html(number_format(data.amends));
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
				$('#stat-count-bk').html(number_format(data.count));
				$('#stat-sumHJ-bk').html(number_format(data.total_hj));
				$('#stat-sumInv-bk').html(number_format(data.sum_value_invoice));
				$('#stat-sumPR-bk').html(number_format(data.sum_value_pr));
				$('#stat-amends-bk').html(number_format(data.amends));
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
				$('#stat-count-kt').html(number_format(data.count));
				$('#stat-sumHJ-kt').html(number_format(data.total_hj));
				$('#stat-sumInv-kt').html(number_format(data.sum_value_invoice));
				$('#stat-sumPR-kt').html(number_format(data.sum_value_pr));
				$('#stat-amends-kt').html(number_format(data.amends));
		});

	}

	$(function() {
		collectData();

		$(".refresh-data").click(function(){
			collectData();
		});
		
		var template = Handlebars.compile($("#details-template").html());

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.invoice.datatablesDashboard') }}",
				type: "POST",
				data: {
					f_sales : $('*[name=f_sales]').val(),
					f_year  : $('*[name=f_year]').val(),
					f_month : $('*[name=f_month]').val(),

					f_start_year  : $('*[name=f_start_year]').val(),
					f_start_month : $('*[name=f_start_month]').val(),
					f_end_year    : $('*[name=f_end_year]').val(),
					f_end_month   : $('*[name=f_end_month]').val(),
					f_type        : $('*[name=f_type]:checked').val(),
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

				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'count_spk', sClass: 'nowrap-cell'},
				{data: 'total_hm', searchable: false},
				{data: 'total_hj', orderable: false, searchable: false},
				{data: 'sum_value_invoice', searchable: false, sClass: 'nowrap-cell'},
				{data: 'amends', searchable: false, sClass: 'nowrap-cell'},

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
			dom: '<l<tr>ip>',
		});

		$('#datatable tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	        var tableId = 'posts-' + row.data().sales_id;
	        var salesId = row.data().sales_id;
	 
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


	    function initTable(tableId, salesId, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.invoice.datatablesDetailDashboard') }}",
					type: "POST",
					data: {
						sales_id : salesId,
						f_year   : $('*[name=f_year]').val(),
						f_month  : $('*[name=f_month]').val(),
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
	                { data: 'name'},
	                { data: 'total_hj', sClass: 'number-format'},
	                { data: 'sum_value_invoice', sClass: 'number-format'},
	                { data: 'amends', sClass: 'number-format'},
	                { data: 'data_invoice'},
	                { data: 'note_invoice'},
	            ],
	            scrollY: "200px",
	        })
	    }


		$('#datatable').on('change', 'textarea[name=note_invoice]', function(){
			$.post('{{ route('backend.invoice.noteInvoice') }}', {
				id: $(this).data('id'),
				note_invoice: $(this).val(),
			}, function(data) {
			});
		});

		@if(Session::has('invoice-excel-error'))
		$('#invoice-excel').modal('show');
		@endif
	});
</script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-bordered details-table" id="posts-@{{sales_id}}">
        <thead>
        <tr>
            <th>SPK</th>
            <th>Project</th>
            <th>Total Sell Price</th>
            <th>Total Invoice</th>
            <th>Outstanding</th>
            <th>Data Invoice</th>
            <th>Note Invoice</th>
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
	<div id="invoice-excel" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.invoice.excel') }}" method="post" enctype="multipart/form-data">
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
	
	<h1>Dashboard SPK Recap</h1>

	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
			<div class="row">
				<div class="col-md-12">
					<select class="form-control select2" name="f_sales" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('full-user')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
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

	@can('excel-invoice')
	<div class="x_panel" style="overflow: auto;">
		<button data-toggle="modal" data-target="#invoice-excel" class="btn btn-success"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</button>
	</div>
	@endcan

	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li role="presentation" class="active"><a href="#dashboard" id="home-tab" role="data-sales-tab" data-toggle="tab" aria-expanded="true">Data Sales</a>
				</li>
				<li role="presentation" class=""><a href="#dataPrice" role="tab" id="data-price-tab" data-toggle="tab" aria-expanded="false">Data Price</a>
				</li>
			</ul>
			<div id="dashboardTabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade active in" id="dashboard" aria-labelledby="data-sales-tab">
					<table class="table table-striped table-bordered" id="datatable">
						<thead>
							<tr>
								<th></th>
								<th>Sales</th>
								<th>SPK Count</th>
								<th>Total Modal Price</th>
								<th>Total Sell Price</th>
								<th>Total Invoice</th>
								<th>Amends</th>
							</tr>
						</thead>
						
						<tfoot>
							<tr>
								<th></th>
								<th></th>
								<th><span id="countSPK"></span></th>
								<th><span id="sumHM"></span></th>
								<th><span id="sumHJ"></span></th>
								<th><span id="sumInvoice"></span></th>
								<th><span id="sumOutstanding"></span></th>
							</tr>
						</tfoot>
					</table>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="dataPrice" aria-labelledby="data-price-tab">
					<button type="button" class="btn btn-primary refresh-data btn-xs">Refresh</button>

					<table class="table table-bordered" style="font-size: small;">
						<tr>
							<th scope="col"></th>
							<th scope="col">Count</th>
							<th scope="col">SUM HJ</th>
							<th scope="col">SUM Invoice</th>
							<th scope="col">SUM PR</th>
							<th scope="col">Amends</th>
						</tr>
						<tr>
							<td>On Progress</td>
							<td id="stat-count-onprogress" align="right"></td>
							<td id="stat-sumHJ-onprogress" align="right"></td>
							<td id="stat-sumInv-onprogress" align="right"></td>
							<td id="stat-sumPR-onprogress" align="right"></td>
							<td id="stat-amends-onprogress" align="right"></td>
						</tr>
						<tr>
							<td>Unapproved</td>
							<td id="stat-count-unapprove" align="right"></td>
							<td id="stat-sumHJ-unapprove" align="right"></td>
							<td id="stat-sumInv-unapprove" align="right"></td>
							<td id="stat-sumPR-unapprove" align="right"></td>
							<td id="stat-amends-unapprove" align="right"></td>
						</tr>
						<tr>
							<td>KB</td>
							<td id="stat-count-kb" align="right"></td>
							<td id="stat-sumHJ-kb" align="right"></td>
							<td id="stat-sumInv-kb" align="right"></td>
							<td id="stat-sumPR-kb" align="right"></td>
							<td id="stat-amends-kb" align="right"></td>
						</tr>
						<tr>
							<td>BK</td>
							<td id="stat-count-bk" align="right"></td>
							<td id="stat-sumHJ-bk" align="right"></td>
							<td id="stat-sumInv-bk" align="right"></td>
							<td id="stat-sumPR-bk" align="right"></td>
							<td id="stat-amends-bk" align="right"></td>
						</tr>
						<tr>
							<td>KT</td>
							<td id="stat-count-kt" align="right"></td>
							<td id="stat-sumHJ-kt" align="right"></td>
							<td id="stat-sumInv-kt" align="right"></td>
							<td id="stat-sumPR-kt" align="right"></td>
							<td id="stat-amends-kt" align="right"></td>
						</tr>

					</table>
				</div>

			</div>
		</div>

		
	</div>
	

@endsection