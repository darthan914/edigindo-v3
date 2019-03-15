@extends('backend.layout.master')

@section('title')
	PR Dashboard
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
		$.ajax({
			url: "{{ route('backend.pr.ajaxDashboard') }}",
			type: "POST",
			data: {
				f_year: $('*[name=f_year]').val(),
				f_sales: $('*[name=f_sales]').val(),
				f_month: $('*[name=f_month]').val(),
				f_budget: $('*[name=f_budget]').val(),
			},
			success: function(result){
				var template = Handlebars.compile($("#details-template").html());

				$("span#totalHM").html(number_format(result.allTotalHM));
				$("span#totalHE").html(number_format(result.allTotalHE));
				$("span#totalHJ").html(number_format(result.allTotalHJ));

				$("span#totalPR").html(number_format(result.allTotalPR));
				$("span#profit").html(number_format(result.allTotalProfit));
				$("span#budget").html(number_format(result.allTotalBudget));

				$("span#budgetE").html(number_format(result.allTotalBudgetE));
				var table = $('#datatables-pr').DataTable({
					data: result.data,
					columns: [
						{
							className  : "details-control",
							orderable  : false,
							searchable : false,
							data       : null,
							defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
						},

						{data: 'spk', sClass: 'nowrap-cell'},
						{data: 'name'},
						{data: 'totalHM', sClass: 'number-format'},

						@can('advanceDashboard-pr')
						{data: 'totalHE', sClass: 'number-format'},
						{data: 'totalHJ', sClass: 'number-format'},
						@endcan
						
						{data: 'totalPR', sClass: 'number-format'},

						@can('advanceDashboard-pr')
						{data: 'profit', sClass: 'number-format'},
						{data: 'margin', sClass: 'number-format'},
						@endif

						{data: 'budget', sClass: 'number-format'},

						@can('advanceDashboard-pr')
						{data: 'budgetE', sClass: 'number-format'},
						@endcan
					],
					scrollY: "400px",
					// scrollX: true,
				});

				// Add event listener for opening and closing details
				$('#datatables-pr tbody').on('click', 'td.details-control > button', function () {
					var tr = $(this).closest('tr');
					var row = table.row( tr );
					var tableId = 'posts-' + row.data().id;
					var id = row.data().id;

					console.log(id);
			 
					if ( row.child.isShown() ) {
						// This row is already open - close it
						row.child.hide();
						tr.removeClass('shown');
					}
					else {
						// Open this row
						row.child(template(row.data())).show();
						initTable(tableId, id, row.data());
						tr.addClass('shown');
						tr.next().find('td').addClass('no-padding bg-gray');
					}
				} );

				function initTable(tableId, id, data) {
					$('#' + tableId).DataTable({
						processing: true,
						serverSide: true,
						ajax: {
						url: "{{ route('backend.pr.datatablesDetailDashboard') }}",
							type: "POST",
							data: {
								id       : id,
								f_year   : $('*[name=f_year]').val(),
								f_month  : $('*[name=f_month]').val(),
							},
						},
						columns: [
							{ data: 'no_pr'},
							{ data: 'name'},
							{ data: 'item'},
							{ data: 'quantity', sClass: 'nowrap-cell'},
							{ data: 'purchasing'},
							{ data: 'countPO'},
							{ data: 'po'},
							{ data: 'action'},
						],
						scrollY: "200px",
					});

					// $('#' + tableId).on('click', '.delete-pr', function(){
					// 	$('.pr_id-ondelete').val($(this).data('id'));
					// });

					// $('#' + tableId).on('click', '.pr-pdf', function(){
					// 	$('.pr_id-onpdf').val($(this).data('id'));
					// });
				}
			}
		});

		$('select[name=f_sales]').select2({
		});



		@if(Session::has('pr-pdf-error'))
		$('#pr-pdf').modal('show');
		@endif


		
		@if(Session::has('spk-excel-error'))
		$('#spk-excel').modal('show');
		@endif
	});
</script>
<script id="details-template" type="text/x-handlebars-template">
	<table class="table table-bordered details-table" id="posts-@{{id}}">
		<thead>
		<tr>
			<th>No PR</th>
			<th>From</th>
			<th>Item</th>
			<th>Quantity</th>
			<th>Purchasing</th>
			<th>Count PO</th>
			<th>Data PO</th>
			<th>Action</th>
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

	@can('excel-pr')
	{{-- PR Excel --}}
	<div id="pr-excel" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.excel') }}" method="post" enctype="multipart/form-data">
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
	@endcan

	{{-- @can('delete-pr')
	<div id="delete-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.deleteDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete PR Detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="pr_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('pdf-pr')
	<div id="pr-pdf" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.pdf') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_id" class="pr_id-onpdf" value="{{old('pr_id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan --}}

	<h1>PR Dashboard</h1>
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
				<option value="">This Month</option>
				<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
				@php $numMonth = 1; @endphp
				@foreach($month as $list)
				<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
				@endforeach
			</select>
			<select class="form-control" name="f_sales" onchange="this.form.submit()">
				<option value="">My Project</option>
				<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
				@can('allSales-spk')
					<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
				@endcan
				
				@foreach($sales as $list)
				<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
				@endforeach
				
			</select>
			<select name="f_budget" class="form-control" onchange="this.form.submit()">
				<option value="" {{ $request->f_budget === '' ? 'selected' : '' }}>All Budget</option>
				<option value="1" {{ $request->f_budget === '1' ? 'selected' : '' }}>On Budget</option>
				<option value="0" {{ $request->f_budget === '0' ? 'selected' : '' }}>Over Budget</option>
			</select>
		</form>
	</div>

	@can('excel-pr')
	<div class="x_panel" style="overflow: auto;">
		<button data-toggle="modal" data-target="#pr-excel" class="btn btn-success"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</button>
	</div>
	@endcan

	<div class="x_panel" style="overflow: auto;">
		<table class="table table-striped" id="datatables-pr">
			<thead>
				<th></th>
				<th>SPK</th>
				<th>Project</th>
				<th>Total HM</th>

				@can('advanceDashboard-pr')
				<th>Total HE</th>
				<th>Total HJ</th>
				@endcan
				
				<th>Total PR</th>

				@can('advanceDashboard-pr')
				<th>Profit/Loss</th>
				<th>Margin</th>
				@endif

				<th>Budget</th>

				@can('advanceDashboard-pr')
				<th>BudgetE</th>
				@endcan
			</thead>
			<tfoot>
				<th></th>
				<th>Total</th>
				<th></th>
				<th><span id="totalHM"></span></th>

				@can('advanceDashboard-pr')
				<th><span id="totalHE"></span></th>
				<th><span id="totalHJ"></span></th>
				@endcan

				<th><span id="totalPR"></span></th>

				@can('advanceDashboard-pr')
				<th><span id="profit"></span></th>
				<th><span id="margin"></span></th>
				@endcan

				<th><span id="budget"></span></th>

				@can('advanceDashboard-pr')
				<th><span id="budgetE"></span></th>
				@endcan
			</tfoot>
		</table>
	</div>

@endsection