@extends('backend.layout.master')

@section('title')
	Account Journal
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

	function formatGeneral ( d ) {
		html = '';
		html += '<div class="row">';
		html += '	<div class="col-md-12">'+d.detail+'</div>';
		html += '</div>';
	    return html;
	}

	
	
	$(function() {

		var tableGeneral = $('#datatable-general').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountGeneral') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'date', sClass: 'nowrap-cell'},
				{data: 'note', sClass: 'nowrap-cell'},
				{data: 'status', sClass: 'nowrap-cell'},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
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
		});

		// Add event listener for opening and closing details
	    $('#datatable-general tbody').on('click', 'td.details-control > button', function () {
	    	console.log('click');
	        var tr = $(this).closest('tr');
	        var row = tableGeneral.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( formatGeneral(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );

		$('#datatable-general').on('click', '.deleteAccountGeneral-account', function(){
			$('#deleteAccountGeneral-account input[name=id]').val($(this).data('id'));
		});

		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).attr('id'));
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

	<h1>Account Journal</h1>

	@can('deleteAccountGeneral-account')
	{{-- Delete Account Classification --}}
	<div id="deleteAccountGeneral-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountGeneral') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Account General?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	{{-- <div class="x_panel" style="overflow: auto;">
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
	</div> --}}


	<div class="x_panel" style="overflow: auto;">

		<div class="">
			<ul id="tab" class="nav nav-tabs bar_tabs">
				<li class="{{ $request->tab === 'GENERAL' || $request->tab == '' ? 'active' : ''}}"><a href="#general" id="general-tab" data-toggle="tab" class="tab-active">General</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'GENERAL' || $request->tab == '' ? 'active in' : ''}}" id="general">
					<div class="row">
						<div class="col-md-6">
							<form class="form-inline" method="get">

							</form>
						</div>
						<div class="col-md-6">
							<form method="post" id="action" action="{{ route('backend.account.actionAccountGeneral') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
								@can('createAccountClass-account')
								<a href="{{ route('backend.account.createAccountGeneral') }}" class="btn btn-default">Create</a>
								@endif
								<select class="form-control" name="action">
									{{-- <option value="enable">Enable</option>
									<option value="disable">Disable</option> --}}
									<option value="delete">Delete</option>
								</select>
								<button type="submit" class="btn btn-success">Apply Selected</button>
							</form>
						</div>
					</div>
					
					<div class="ln_solid"></div>

					<table class="table table-bordered" id="datatable-general">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
								</th>
								<th>View</th>
								<th>Date</th>
								<th>Note</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
							
						</thead>
						<tfoot>
							<th></th>
							<th></th>
							<th></th>
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