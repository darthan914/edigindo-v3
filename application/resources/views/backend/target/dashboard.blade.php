@extends('backend.layout.master')

@section('title')
	Target Dashboard {{ $index->year }}
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
		
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.target.datatablesDashboard', $index) }}",
				type: "POST",
				data: {
				},
			},
			columns: [
				{data: 'first_name', sClass: 'nowrap-cell'},
				{data: 'total_profit', sClass: 'number-format'},
				{data: 'value', sClass: 'number-format'},
				{data: 'total_to_reach_target', sClass: 'number-format', orderable: false},
				

			],
			scrollY: "400px",
			dom :'<l<t>ip>'
		});
		
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


	<h1>Target Dashboard {{ $index->year }}</h1>

	{{-- <div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">

			
			<input type="hidden" name="tab">
		</form>
	</div> --}}

	<div class="x_panel" style="overflow: auto;">

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>Sales</th>
					<th>Real Omset</th>
					<th>Target</th>
					<th>Remaining To Reach Target</th>
				</tr>
			</thead>
			<tfoot>
				<th>Target Company : </th>
				<th>Rp. {{number_format($master->sum('total_profit'))}}</th>
				<th>Rp. {{number_format($index->value)}}</th>
				<th>Rp. {{number_format(max($index->value - $master->sum('total_profit'), 0))}}</th>
			</tfoot>
		</table>

	</div>

@endsection