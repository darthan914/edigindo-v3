@extends('backend.layout.master')

@section('title')
	Todo Dashboard
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

		$.ajax({
			url: "{{ route('backend.todo.ajaxSales') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
				f_month: $('*[name=f_month]').val(),
				f_company  : $('*[name=f_company]').val(),
			},
			success: function(result){
				$("span#countTodo").html(number_format(result.countTodo));
				$("span#countCompany").html(number_format(result.countCompany));
				var tableSales = $('#datatable-estimator').DataTable({
					data: result.data,
					columns: [
						{
			                className  : "details-control",
			                orderable  : false,
			                searchable : false,
			                data       : null,
			                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
			            },
						{data: 'fullname', sClass: 'nowrap-cell'},
						{data: 'countTodo', sClass: 'number-format'},
						{data: 'countCompany', sClass: 'number-format'},
					],
				});

			    // Add event listener for opening and closing details
			    $('#datatable-estimator tbody').on('click', 'td.details-control > button', function () {
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
			    } );
			}
		});


		function initTable(tableId, salesId, data) {
	        $('#' + tableId).DataTable({
	            processing: true,
	            serverSide: true,
	            ajax: {
				url: "{{ route('backend.todo.datatablesDetailSales') }}",
					type: "POST",
					data: {
						sales_id   : salesId,
						f_year     : $('*[name=f_year]').val(),
						f_month    : $('*[name=f_month]').val(),
						f_company  : $('*[name=f_company]').val(),
					},
				},
	            columns: [
	                { data: 'name_company'},
	                { data: 'event'},
	                { data: 'date_todo'},
	            ],
	            scrollY: "200px",

	            
	        })
	    }

	});
</script>

<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-bordered details-table" id="posts-@{{id}}">
        <thead>
        <tr>
            <th>Name Company</th>
            <th>Event</th>
            <th>Datetime</th>
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

	<h1>Todo Dashboard</h1>
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
			<select class="form-control" name="f_company" onchange="this.form.submit()">
				<option value="" {{ $request->f_company === '' ? 'selected' : '' }}>All Company</option>

				@foreach($company as $list)
				<option value="{{ $list->id }}" {{ $request->f_company == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
				@endforeach
			</select>
		</form>
	</div>

	<div class="x_panel" style="overflow: auto;">

		<table class="table table-striped" id="datatable-estimator">
			<thead>
				<th></th>
				<th>Sales</th>
				<th>Count Make Event</th>
				<th>Count Unique Company</th>
			</thead>
			<tfoot>
				<th></th>
				<th></th>
				<th><span id="countTodo"></span></th>
				<th><span id="countCompany"></span></th>
			</tfoot>
		</table>
	</div>

@endsection