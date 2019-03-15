@extends('backend.layout.master')

@section('title')
	Campaign Dasboard
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
		
		// var template = Handlebars.compile($("#details-template").html());

		$.ajax({
			url: "{{ route('backend.campaign.ajaxOldSales') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
			},
			success: function(result){
				$("span#realOmsetJanApr").html(number_format(result.realOmsetJanApr));
				$("span#realOmsetMayAug").html(number_format(result.realOmsetMayAug));
				$("span#realOmsetSepDec").html(number_format(result.realOmsetSepDec));
				var tableSales = $('#datatable-oldsales').DataTable({
					data: result.data,
					columns: [
						/*{
			                className  : "details-control",
			                orderable  : false,
			                searchable : false,
			                data       : null,
			                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
			            },*/
						{data: 'fullname', sClass: 'nowrap-cell'},

						{data: 'realOmsetJanApr', sClass: 'number-format'},
						{data: 'countJanApr', sClass: 'number-format'},
						{data: 'remainJanApr', sClass: 'number-format'},
						{data: 'percentJanApr', sClass: 'number-format'},

						{data: 'realOmsetMayAug', sClass: 'number-format'},
						{data: 'countMayAug', sClass: 'number-format'},
						{data: 'remainMayAug', sClass: 'number-format'},
						{data: 'percentMayAug', sClass: 'number-format'},

						{data: 'realOmsetSepDec', sClass: 'number-format'},
						{data: 'countSepDec', sClass: 'number-format'},
						{data: 'remainSepDec', sClass: 'number-format'},
						{data: 'percentSepDec', sClass: 'number-format'},

					],
					// scrollY: "400px",
		            scrollX: true,
					
				});

				/*$('#datatable-sales tbody').on('click', 'td.update-target > button', function () {
			        var tr = $(this).closest('tr');
			        var row = tableSales.row( tr );

			        var target = row.data().target;

			        $(".id-onupdateTarget").val(row.data().target_id);
			        $(".target-onupdateTarget").val(target.replace(/,/g, ''));

			        $('#spk-updateTarget').modal('show');
			    });*/

			    // Add event listener for opening and closing details
			    /*$('#datatable-sales tbody').on('click', 'td.details-control > button', function () {
			        var tr = $(this).closest('tr');
			        var row = tableSales.row( tr );
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
			    } );*/
			}
		});


		$.ajax({
			url: "{{ route('backend.campaign.ajaxNewSales') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
			},
			success: function(result){
				$("span#realOmsetJanAug").html(number_format(result.realOmsetJanAug));
				$("span#realOmsetMayDec").html(number_format(result.realOmsetMayDec));
				var tableSales = $('#datatable-newsales').DataTable({
					data: result.data,
					columns: [
						/*{
			                className  : "details-control",
			                orderable  : false,
			                searchable : false,
			                data       : null,
			                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
			            },*/
						{data: 'fullname', sClass: 'nowrap-cell'},

						{data: 'realOmsetJanAug', sClass: 'number-format'},
						{data: 'countJanAug', sClass: 'number-format'},
						{data: 'remainJanAug', sClass: 'number-format'},
						{data: 'percentJanAug', sClass: 'number-format'},

						{data: 'realOmsetMayDec', sClass: 'number-format'},
						{data: 'countMayDec', sClass: 'number-format'},
						{data: 'remainMayDec', sClass: 'number-format'},
						{data: 'percentMayDec', sClass: 'number-format'},

					],
					// scrollY: "400px",
		            // scrollX: true,
					
				});

				/*$('#datatable-sales tbody').on('click', 'td.update-target > button', function () {
			        var tr = $(this).closest('tr');
			        var row = tableSales.row( tr );

			        var target = row.data().target;

			        $(".id-onupdateTarget").val(row.data().target_id);
			        $(".target-onupdateTarget").val(target.replace(/,/g, ''));

			        $('#spk-updateTarget').modal('show');
			    });*/

			    // Add event listener for opening and closing details
			    /*$('#datatable-sales tbody').on('click', 'td.details-control > button', function () {
			        var tr = $(this).closest('tr');
			        var row = tableSales.row( tr );
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
			    } );*/
			}
		});

		



		/*function initTable(tableId, salesId, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.spk.datatablesDetailDashboard') }}",
					type: "POST",
					data: {
						sales_id   : salesId,
						f_year     : $('*[name=f_year]').val(),
						f_month    : $('*[name=f_month]').val(),
					},
				},
	            columns: [
	                { data: 'spk'},
	                { data: 'date'},
	                { data: 'totalHM', sClass: 'number-format'},
	                { data: 'totalHJ', sClass: 'number-format'},
	                { data: 'totalRealOmset', sClass: 'number-format'},
	                { data: 'totalLoss', sClass: 'number-format'},
	                { data: 'action'},
	            ],
	            scrollY: "200px",

	            
	        })
	    }*/
	});
</script>

{{-- <script id="details-template" type="text/x-handlebars-template">
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
</script> --}}
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

	.pie {
	  width: 50px; height: 50px;
	  border-radius: 50%;
	  background: #655 ;
	  background-image: linear-gradient(to right, transparent 50%, yellowgreen 0);
	  position: relative;
	}

	.pie > div {
	  position: absolute;
	  display: block;
	  margin-left: 50%;
	  height: 100%;
      width: 50%;
	  border-radius: 0 100% 100% 0 / 50%;
	  background: yellowgreen;
	  transform-origin: left;
	}

	.pie > div {
	  background: #655 ;
	}

	.pie > div.over50 {
	  background: yellowgreen;
	}
</style>
@endsection

@section('content')
	<h1>Campaign Dasboard</h1>

	

	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
			<select class="form-control" name="f_year" onchange="this.form.submit()">
				<option value="">This Year</option>
				@foreach($year as $list)
				<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
				@endforeach
			</select>
			{{-- <select class="form-control" name="f_month" onchange="this.form.submit()">
				<option value="" {{ $request->f_month === '' ? 'selected' : '' }}>This Month</option>
				<option value="all" {{ $request->f_month === 'all' ? 'selected' : '' }}>All Month</option>
				@php $numMonth = 1; @endphp
				@foreach($month as $list)
				<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
				@endforeach
			</select> --}}
		</form>
	</div>


	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li role="presentation" class="active"><a href="#old-sales" id="old-sales-tab" role="data-old-sales" data-toggle="tab" aria-expanded="true">Tim Tempur</a>
				</li>
				<li role="presentation" class=""><a href="#new-sales" role="tab" id="new-sales-tab" data-toggle="tab" aria-expanded="false">Super Junior</a>
				</li>
			</ul>
			<div id="dashboardTabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade active in" id="old-sales" aria-labelledby="old-sales-tab">
					<table class="table table-striped table-bordered" id="datatable-oldsales">
						<thead>
							<tr>
								<th rowspan="2" valign="middle" >Name Sales</th>
								<th colspan="4" align="center" >({{ $campaign->location_1 }}) Jan - Apr | Target: {{ number_format($campaign->target_jan_to_apr) }}</th>
								<th colspan="4" align="center" >({{ $campaign->location_2 }}) May - Aug | Target: {{ number_format($campaign->target_may_to_aug) }}</th>
								<th colspan="4" align="center" >({{ $campaign->location_3 }}) Sep - Dec | Target: {{ number_format($campaign->target_sep_to_dec )}}</th>
							</tr>
							<tr>
								<th>Real Omset</th>
								<th>SPK Count</th>
								<th>Remain Target</th>
								<th>Percent</th>

								<th>Real Omset</th>
								<th>SPK Count</th>
								<th>Remain Target</th>
								<th>Percent</th>

								<th>Real Omset</th>
								<th>SPK Count</th>
								<th>Remain Target</th>
								<th>Percent</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>

							<th><span id="realOmsetJanApr"></span></th>
							<th></th>
							<th></th>
							<th></th>

							<th><span id="realOmsetMayAug"></span></th>
							<th></th>
							<th></th>
							<th></th>

							<th><span id="realOmsetSepDec"></span></th>
							<th></th>
							<th></th>
							<th></th>

						</tfoot>
					</table>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="new-sales" aria-labelledby="new-sales-tab">
					<table class="table table-striped table-bordered" id="datatable-newsales">
						<thead>
							<tr>
								<th rowspan="2" valign="middle" >Name Sales</th>
								<th colspan="4" align="center" >({{ $campaign->location_2 }}) Jan - Aug | Target: {{ number_format($campaign->target_jan_to_aug) }}</th>
								<th colspan="4" align="center" >({{ $campaign->location_3 }}) May - Dec | Target: {{ number_format($campaign->target_may_to_dec) }}</th>
							</tr>
							<tr>
								<th>Real Omset</th>
								<th>SPK Count</th>
								<th>Remain Target</th>
								<th>Percent</th>

								<th>Real Omset</th>
								<th>SPK Count</th>
								<th>Remain Target</th>
								<th>Percent</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>

							<th><span id="realOmsetJanAug"></span></th>
							<th></th>
							<th></th>
							<th></th>

							<th><span id="realOmsetMayDec"></span></th>
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