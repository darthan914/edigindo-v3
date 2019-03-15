@extends('backend.layout.master')

@section('title')
	Dashboard
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/vendors/Chart.js/dist/Chart.min.js') }}"></script>
<script type="text/javascript">
	// on page load...
	moveProgressBar();
	// on browser resize...
	$(window).resize(function() {
	  moveProgressBar();
	});

	// SIGNATURE PROGRESS
	function moveProgressBar() {
	  var getPercentTarget = ($('.target-bar').data('progress-percent') / 100);
	  var getProgressWrapWidthTarget = $('.target-bar').width();
	  var progressTotalTarget = getPercentTarget * getProgressWrapWidthTarget;
	  var animationLength = 2500;

	  // on page load, animate percentage bar to data percentage length
	  // .stop() used to prevent animation queueing
	  $('.target-bar .progress-bar-state').stop().animate({
	    left: progressTotalTarget
	  }, animationLength);

	  var getPercentCampaign = ($('.campaign-bar').data('progress-percent') / 100);
	  var getProgressWrapWidthCampaign = $('.campaign-bar').width();
	  var progressTotalCampaign = getPercentCampaign * getProgressWrapWidthCampaign;
	  var animationLength = 2500;

	  // on page load, animate percentage bar to data percentage length
	  // .stop() used to prevent animation queueing
	  $('.campaign-bar .progress-bar-state').stop().animate({
	    left: progressTotalCampaign
	  }, animationLength);
	}

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
		@can('incomeOutcome-dashboard')
		$.ajax({
			url: "{{ route('backend.home.ajaxChartInOut') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
			},
		}).done(function(data) {
			$("#total_income_2").html(number_format(data.in_total_minus_2));
			$("#total_income_1").html(number_format(data.in_total_minus_1));
			$("#total_income_0").html(number_format(data.in_total_minus_0));

			$("#total_outcome_2").html(number_format(data.out_total_minus_2));
			$("#total_outcome_1").html(number_format(data.out_total_minus_1));
			$("#total_outcome_0").html(number_format(data.out_total_minus_0));
			// Chart InOut
			var ctx = document.getElementById("chart-inout");
			var chartMonthly = new Chart(ctx, {
				type: 'bar',
				legend: true,
				data: {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [{
						label: "Income Last 2 Year",
						backgroundColor: "rgb(0,75,0)",
						data: data.income_minus_2,
					}, {
						label: "Outcome Last 2 Year",
						backgroundColor: "rgb(75,0,0)",
						data: data.outcome_minus_2,
					}, {
						label: "Income Last Year",
						backgroundColor: "rgb(0,155,0)",
						data: data.income_minus_1,
					}, {
						label: "Outcome Last Year",
						backgroundColor: "rgb(155,0,0)",
						data: data.outcome_minus_1,
					}, {
						label: "Income",
						backgroundColor: "rgb(0,255,0)",
						data: data.income_minus_0,
					}, {
						label: "Outcome",
						backgroundColor: "rgb(255,0,0)",
						data: data.outcome_minus_0,
					}]
				},
			});
		});
		@endcan

		@can('sales-dashboard')
		$.ajax({
			url: "{{ route('backend.home.ajaxChartMonthly') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
			},
		}).done(function(data) {
			// Chart Monthly
			var ctx = document.getElementById("chart-monthly");
			var chartMonthly = new Chart(ctx, {
				type: 'line',
				data: {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [{
						label: "Real Omset",
						backgroundColor: "rgba(100, 255, 100, 0.8)",
						borderColor: "rgba(100, 255, 100, 0.70)",
						pointBorderColor: "rgba(100, 255, 100, 0.70)",
						pointBackgroundColor: "rgba(100, 255, 100, 0.70)",
						pointHoverBackgroundColor: "#fff",
						pointHoverBorderColor: "rgba(151,187,205,1)",
						pointBorderWidth: 1,
						data: data.chartRO,
					}, {
						label: "Sell Price",
						backgroundColor: "rgba(255, 100, 100, 0.8)",
						borderColor: "rgba(255, 100, 100, 0.7)",
						pointBorderColor: "rgba(255, 100, 100, 0.7)",
						pointBackgroundColor: "rgba(255, 100, 100, 0.7)",
						pointHoverBackgroundColor: "#fff",
						pointHoverBorderColor: "rgba(220,220,220,1)",
						pointBorderWidth: 1,
						data: data.chartHJ,
					}]
				},
			});
		});

		$.ajax({
			url: "{{ route('backend.home.ajaxChartTarget') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
			},
		}).done(function(data) {
			// Chart Monthly
			// var ctx = document.getElementById("chart-target");
			// var chartMonthly = new Chart(ctx, {
			// 	type: 'pie',
			// 	data: {
			// 		datasets: [{
			// 			data: data.chartTarget,
			// 			backgroundColor: [
			// 				"#8080ff",
			// 				"#c2c2a3",
			// 			  ],
			// 			label: 'Current Target' // for legend
			// 		}],
			// 		labels: [
			// 			"Current",
			// 			"Remaining",
			// 		]
			// 	},
			// });

			var percent = (data.chartTarget[0] / (data.chartTarget[0] + data.chartTarget[1])) * 100;
			percent = percent ? percent : 0;
			$('.target-bar').data('progress-percent', percent);
			$('.target-bar .progress-bar-state').html(number_format(percent,2) + "%");
			$('.target-bar .before').html('');
			$('.target-bar .after').html(number_format(number_format(data.chartTarget[0])) + "/" +number_format(data.chartTarget[0] + data.chartTarget[1]));

			moveProgressBar();

			$("#note_target").html(data.note_target);
		});

		$.ajax({
			url: "{{ route('backend.home.ajaxChartCampaign') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
				next  : "N",
			},
		}).done(function(data) {
			if(data.chartTarget[1] <= 0)
			{
				$.ajax({
					url: "{{ route('backend.home.ajaxChartCampaign') }}",
					type: "POST",
					data: {
						f_year: $('*[name=f_year]').val(),
						next  : "Y",
					},
				}).done(function(data) {
					// Chart Monthly
					// var ctx = document.getElementById("chart-campaign");
					// var chartMonthly = new Chart(ctx, {
					// 	type: 'pie',
					// 	data: {
					// 		datasets: [{
					// 			data: data.chartTarget,
					// 			backgroundColor: [
					// 				"#8080ff",
					// 				"#c2c2a3",
					// 			  ],
					// 			label: 'Current Target' // for legend
					// 		}],
					// 		labels: [
					// 			"Current",
					// 			"Remaining",
					// 		]
					// 	},
					// });

					var percent = (data.chartTarget[0] / (data.chartTarget[0] + data.chartTarget[1])) * 100;
					percent = percent ? percent : 0;
					$('.campaign-bar').data('progress-percent', percent);
					$('.campaign-bar .progress-bar-state').html(number_format(percent,2) + "%");
					$('.campaign-bar .before').html('');
					$('.campaign-bar .after').html(number_format(data.chartTarget[0]) + "/" +number_format(data.chartTarget[0] + data.chartTarget[1]));

					moveProgressBar();

					$('#campaign-header').html("Congratulation Target has been reached <br> \
					 your next target is to " + data.location_target + " (Million)");

				});
			}

			else
			{
				// Chart Monthly
				// var ctx = document.getElementById("chart-campaign");
				// var chartMonthly = new Chart(ctx, {
				// 	type: 'pie',
				// 	data: {
				// 		datasets: [{
				// 			data: data.chartTarget,
				// 			backgroundColor: [
				// 				"#8080ff",
				// 				"#c2c2a3",
				// 			  ],
				// 			label: 'Current Target' // for legend
				// 		}],
				// 		labels: [
				// 			"Current",
				// 			"Remaining",
				// 		]
				// 	},
				// });

				var percent = (data.chartTarget[0] / (data.chartTarget[0] + data.chartTarget[1])) * 100;
				percent = percent ? percent : 0;
				$('.campaign-bar').data('progress-percent', percent);
				$('.campaign-bar .progress-bar-state').html(number_format(percent,2) + "%");
				$('.campaign-bar .before').html('');
				$('.campaign-bar .after').html(number_format(data.chartTarget[0]) + "/" +number_format(data.chartTarget[0] + data.chartTarget[1]));

				moveProgressBar();

				$('#campaign-header').html("Campaign target road to " + data.location_target + " (Million)");

			}
			
		});

		@endcan
		
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

	.progress {
		width: 100%;
		height: 50px;
	}
	.progress-wrap .before {
		position: absolute;
		left: 5px;
		line-height: 50px;
		color: #fff;
	}
	.progress-wrap .after {
		right: 5px;
		position: absolute;
		line-height: 50px;
	}
	.progress-wrap {
		background: #26B99A;
		margin: 20px 0;
		overflow: hidden;
		position: relative;
	}
	.progress-wrap .progress-bar-state {
		background: #ddd;
		left: 0;
		position: absolute;
		top: 0;
		line-height: 50px;
	}
</style>
@endsection

@section('content')

	<div class="x_panel" style="overflow: auto;">

		<form class="form-inline" method="get">
			<select class="form-control" name="f_year" onchange="this.form.submit()">
				<option value="">This Year</option>
				<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
				@for ($year = 2013; $year <= date('Y'); $year++)
				<option value="{{ $year }}" {{ $request->f_year == $year ? 'selected' : '' }}>{{ $year }}</option>
				@endfor
			</select>
		</form>
	</div>

	@can('incomeOutcome-dashboard')
	<div class="x_panel">
		<div class="x_title">
			<h2>Income / Outcome (Million)</h2>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<canvas id="chart-inout"></canvas>

			<div class="row tile_count">
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-user"></i> Total Income last 2 year</span>
					<div class="count" id="total_income_2">0</div>
				</div>
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-clock-o"></i> Total Outcome last 2 year</span>
					<div class="count" id="total_outcome_2">0</div>
				</div>
			</div>

			<div class="row tile_count">
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-user"></i> Total Income last year</span>
					<div class="count" id="total_income_1">0</div>
				</div>
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-clock-o"></i> Total Outcome last year</span>
					<div class="count" id="total_outcome_1">0</div>
				</div>
			</div>

			<div class="row tile_count">
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-user"></i> Total Income</span>
					<div class="count" id="total_income_0">0</div>
				</div>
				<div class="col-md-6 col-sm-6 col-xs-6 tile_stats_count">
					<span class="count_top"><i class="fa fa-clock-o"></i> Total Outcome</span>
					<div class="count" id="total_outcome_0">0</div>
				</div>
			</div>
		</div>
	</div>
	@endcan
			
	@can('sales-dashboard')

	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="x_panel">
				<div class="x_title">
					<h2 id="campaign-header"></h2>
					<div class="clearfix"></div>
				</div>
				<div class="x_content">
					<div class="progress-wrap progress campaign-bar">
						<span class="before">0</span>
						<div class="progress-bar-state progress">0</div>
						<span class="after">0</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="x_panel">
				<div class="x_title">
					<h2>Chart Target (Million)</h2>
					<div class="clearfix"></div>
				</div>
				<div class="x_content">
					<div class="progress-wrap progress target-bar">
						<span class="before">0</span>
						<div class="progress-bar-state progress">0</div>
						<span class="after">0</span>
					</div>
				</div>
				<div class="row tile_count">
					<div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count">
						<span class="count_top"><i class="fa fa-user"></i> Note</span>
						<div class="count" id="note_target">-</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="x_panel">
				<div class="x_title">
					<h2>Chart Monthly (Million)</h2>
					<div class="clearfix"></div>
				</div>
				<div class="x_content">
					<canvas id="chart-monthly"></canvas>
				</div>
			</div>
		</div>
	</div>

	@endcan

@endsection