@extends('backend.layout.master')

@section('title')
	Estimator Dashboard
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

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.estimator.datatablesDashboard') }}",
				type: "POST",
				data: {
			    	f_year  : $('*[name=f_year]').val(),
					f_month : $('*[name=f_month]').val(),
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
				{data: 'estimator_name'},
				{data: 'count_created', sClass: 'number-format'},
				{data: 'less_than_24_count_created', sClass: 'number-format'},
				{data: 'more_than_24_count_created', sClass: 'number-format'},
				{data: 'sum_value', sClass: 'number-format'},
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
			dom: '<l<tr>ip>',
		});

		$('#datatable tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	        var tableId = 'posts-' + row.data().user_estimator_id;
	        var userEstimatorId = row.data().user_estimator_id;
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child(template(row.data())).show();
	            initTable(tableId, userEstimatorId, row.data());
	            tr.addClass('shown');
	            tr.next().find('td').addClass('no-padding bg-gray');
	        }
	    } );


		function initTable(tableId, userEstimatorId, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.estimator.datatablesDetailEstimator') }}",
					type: "POST",
					data: {
						user_estimator_id   : userEstimatorId,
						f_year     : $('*[name=f_year]').val(),
						f_month    : $('*[name=f_month]').val(),
					},
				},
	            columns: [
	                { data: 'no_estimator'},
	                { data: 'name'},
	                { data: 'datetime_estimator'},
	                { data: 'sum_value', sClass: 'number-format'},
	                { data: 'action'},
	            ],
	            scrollY: "200px",
	            dom: '<l<tr>ip>',

	            
	        })
	    }

	});
</script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-bordered details-table" id="posts-@{{user_estimator_id}}">
        <thead>
        <tr>
            <th>No Estimator</th>
            <th>Project</th>
            <th>Datetime</th>
			<th>Total Price</th>
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

	<h1>Estimator Dashboard</h1>
	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
			<select class="form-control" name="f_year" onchange="this.form.submit()">
				<option value="">This Year</option>
				<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
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

	<div class="x_panel" style="overflow: auto;">

		<table class="table table-striped" id="datatable">
			<thead>
				<th></th>
				<th>Name Estimator</th>
				<th>Count Created</th>
				<th>Less Than 24 Hour</th>
				<th>More Than 24 Hour</th>
				<th>Total Price</th>
			</thead>
			<tfoot>
				<th></th>
				<th></th>
				<th><span id="countEstimator"></span></th>
				<th></th>
				<th></th>
				<th><span id="totalPrice"></span></th>
			</tfoot>
		</table>
	</div>

@endsection