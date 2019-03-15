@extends('backend.layout.master')

@section('title')
	Dashboard Price Recap SPK
@endsection

@section('script')
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

		$.post('{{ route('backend.invoice.ajaxDashboardPrice') }}', 
			{
				f_year		 : $('*[name=f_year]').val(),
				f_month		: $('*[name=f_month]').val(),
			}, 
			function(data, textStatus, xhr) {
				$('#stat-count-onprogress').html (number_format(data.count_on_progress));
				$('#stat-count-unapprove').html (number_format(data.count_unapprove));
				$('#stat-count-0').html (number_format(data.count_not_complete));
				$('#stat-count-kb').html (number_format(data.count_not_complete_kb));
				$('#stat-count-bk').html (number_format(data.count_not_complete_bk));
				$('#stat-count-kt').html (number_format(data.count_not_complete_kt));

				$('#stat-sumHJ-onprogress').html (number_format(data.sumHJ_on_progress));
				$('#stat-sumHJ-unapprove').html (number_format(data.sumHJ_unapprove));
				$('#stat-sumHJ-0').html (number_format(data.sumHJ_not_complete));
				$('#stat-sumHJ-kb').html (number_format(data.sumHJ_not_complete_kb));
				$('#stat-sumHJ-bk').html (number_format(data.sumHJ_not_complete_bk));
				$('#stat-sumHJ-kt').html (number_format(data.sumHJ_not_complete_kt));

				$('#stat-sumInv-onprogress').html (number_format(data.sumInv_on_progress));
				$('#stat-sumInv-unapprove').html (number_format(data.sumInv_unapprove));
				$('#stat-sumInv-0').html (number_format(data.sumInv_not_complete));
				$('#stat-sumInv-kb').html (number_format(data.sumInv_not_complete_kb));
				$('#stat-sumInv-bk').html (number_format(data.sumInv_not_complete_bk));
				$('#stat-sumInv-kt').html (number_format(data.sumInv_not_complete_kt));

				$('#stat-sumPR-onprogress').html (number_format(data.sumPR_on_progress));
				$('#stat-sumPR-unapprove').html (number_format(data.sumPR_unapprove));
				$('#stat-sumPR-0').html (number_format(data.sumPR_not_complete));
				$('#stat-sumPR-kb').html (number_format(data.sumPR_not_complete_kb));
				$('#stat-sumPR-bk').html (number_format(data.sumPR_not_complete_bk));
				$('#stat-sumPR-kt').html (number_format(data.sumPR_not_complete_kt));

				$('#stat-outstand-onprogress').html (number_format(data.sumHJ_on_progress - data.sumInv_on_progress));
				$('#stat-outstand-unapprove').html (number_format(data.sumHJ_unapprove - data.sumInv_unapprove));
				$('#stat-outstand-0').html (number_format(data.sumHJ_not_complete - data.sumInv_not_complete));
				$('#stat-outstand-kb').html (number_format(data.sumHJ_not_complete_kb - data.sumInv_not_complete_kb));
				$('#stat-outstand-bk').html (number_format(data.sumHJ_not_complete_bk - data.sumInv_not_complete_bk));
				$('#stat-outstand-kt').html (number_format(data.sumHJ_not_complete_kt - data.sumInv_not_complete_kt));

				$('#stat-total-count').html (number_format(data.count_on_progress + data.count_unapprove + data.count_not_complete + data.count_not_complete_kb + data.count_not_complete_bk + data.count_not_complete_kt));
				$('#stat-total-sumHJ').html (number_format(data.sumHJ_on_progress + data.sumHJ_unapprove + data.sumHJ_not_complete + data.sumHJ_not_complete_kb + data.sumHJ_not_complete_bk + data.sumHJ_not_complete_kt));
				$('#stat-total-sumInv').html (number_format(data.sumInv_on_progress + data.sumInv_unapprove + data.sumInv_not_complete + data.sumInv_not_complete_kb + data.sumInv_not_complete_bk + data.sumInv_not_complete_kt));
				$('#stat-total-sumPR').html (number_format(data.sumPR_on_progress + data.sumPR_unapprove + data.sumPR_not_complete + data.sumPR_not_complete_kb + data.sumPR_not_complete_bk + data.sumPR_not_complete_kt));
				$('#stat-total-outstand').html (number_format((data.sumHJ_on_progress - data.sumInv_on_progress) + (data.sumHJ_unapprove - data.sumInv_unapprove) + (data.sumHJ_not_complete - data.sumInv_not_complete) + (data.sumHJ_not_complete_kb - data.sumInv_not_complete_kb) + (data.sumHJ_not_complete_bk - data.sumInv_not_complete_bk) + (data.sumHJ_not_complete_kt - data.sumInv_not_complete_kt)));
				
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

	});
</script>
@endsection

@section('css')
<style type="text/css">
	.fa-spinner {
		 font-size: 40px;
	}
</style>
@endsection

@section('content')
	
	<h1>Dashboard Price Recap SPK</h1>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-12">
				<form class="form-inline" method="get">
					
					
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="" {{ $request->f_year === '' ? 'selected' : '' }}>This Year</option>
						<option value="all" {{ $request->f_year === 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="" {{ $request->f_month === '' ? 'selected' : '' }}>This Month</option>
						<option value="all" {{ $request->f_month === 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
				</form>
			</div>
		</div>

		<div class="ln_solid"></div>

		<table class="table table-bordered" style="font-size: small;">
			<tr>
				<th scope="col"></th>
				<th scope="col">Count</th>
				<th scope="col">SUM HJ</th>
				<th scope="col">SUM Invoice</th>
				<th scope="col">SUM PR</th>
				<th scope="col">Outstanding</th>
			</tr>
			<tr>
				<td>On Progress</td>
				<td id="stat-count-onprogress" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumHJ-onprogress" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumInv-onprogress" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumPR-onprogress" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-outstand-onprogress" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
			<tr>
				<td>Unapproved</td>
				<td id="stat-count-unapprove" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumHJ-unapprove" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumInv-unapprove" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumPR-unapprove" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-outstand-unapprove" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
			<tr>
				<td>KB</td>
				<td id="stat-count-kb" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumHJ-kb" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumInv-kb" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumPR-kb" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-outstand-kb" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
			<tr>
				<td>BK</td>
				<td id="stat-count-bk" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumHJ-bk" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumInv-bk" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumPR-bk" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-outstand-bk" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
			<tr>
				<td>KT</td>
				<td id="stat-count-kt" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumHJ-kt" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumInv-kt" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-sumPR-kt" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-outstand-kt" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
			<tr>
				<td>Total</td>
				<td id="stat-total-count" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-total-sumHJ" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-total-sumInv" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-total-sumPR" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
				<td id="stat-total-outstand" align="right"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></td>
			</tr>
		</table>

	</div>
	

@endsection