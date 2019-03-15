@extends('backend.layout.master')

@section('title')
	Campaign Dashboard {{ $index->name }}
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
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
		
		@foreach($index->campaign_details as $list)
		var table_{{ $list->id }} = $('#datatable-{{ $list->id }}').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.campaign.datatablesDashboard', [ 'campaign_detail' => $list->id ]) }}",
				type: "POST",
				data: {
			    	f_sales : $('*[name=f_sales]').val(),
				},
			},
			columns: [
				{data: 'fullname', sClass: 'nowrap-cell'},
				@php $delta_month = $list->end_month - $list->start_month + 1; @endphp
				@for($i=0; $i < $delta_month; $i++)
					{data: 'real_omset_{{ $i }}', searchable: false, sClass: 'number-format'},
					{data: 'countProduction_{{ $i }}', searchable: false, sClass: 'number-format'},
					{data: 'remain_target_{{ $i }}', searchable: false, sClass: 'number-format'},
					{data: 'percent_{{ $i }}', searchable: false, sClass: 'number-format'},
				@endfor

			],
			// initComplete: function () {
			// 	this.api().columns().every(function () {
			// 		var column = this;
			// 		var input = document.createElement("input");
			// 		$(input).appendTo($(column.footer()).empty())
			// 		.on('keyup', function () {
			// 			column.search($(this).val(), false, false, true).draw();
			// 		});
			// 	});
			// },
			scrollY: "400px",
			scrollX: true,
		});
		@endforeach
		
		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).data('id'));
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


	<h1>Campaign Dashboard {{ $index->name }}</h1>

	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">

			
			<input type="hidden" name="tab">
		</form>
	</div>

	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				@foreach($index->campaign_details as $list)
				<li class="{{ $request->tab == $list->id || ($list->id == $index->campaign_details()->first()->id && $request->tab == '') ? 'active' : ''}}"><a href="#{{ $list->id }}" data-toggle="tab" class="tab-active" data-id="{{ $list->id }}">{{ $list->name }}</a>
				</li>
				@endforeach

			</ul>
			<div class="tab-content">
				@foreach($index->campaign_details as $list)
				<div class="tab-pane fade {{ $request->tab == $list->id || ($list->id == $index->campaign_details()->first()->id && $request->tab == '')  ? 'active in' : ''}}" id="{{ $list->id }}" >
					<table class="table table-striped" id="datatable-{{ $list->id }}">
						<thead>
							<tr>
								@php $delta_month = $list->end_month - $list->start_month + 1; @endphp
								<th rowspan="2" valign="middle" >Name Sales</th>
								@for($i=0; $i < $delta_month; $i++)
									<th colspan="4" align="center" >{{ $long_month[$i+($list->start_month-1)] }}</th>
								@endfor
							</tr>
							<tr>
								@for($i=0; $i < $delta_month; $i++)
									<th>Real Omset</th>
									<th>SPK Count</th>
									<th>Remain Target</th>
									<th>Percent</th>
								@endfor
							</tr>
							
						</thead>
					</table>
				</div>
				@endforeach
			</div>
		</div>

	</div>

@endsection