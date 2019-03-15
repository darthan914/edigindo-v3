@extends('backend.layout.master')

@section('title')
	Unconfirm Item
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatablesUnconfirm') }}",
				type: "post",
				data: {
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
				},
			},
			columns: [
				{data: 'confirm', orderable: false, searchable: false},
				{data: 'reject', orderable: false, searchable: false},
				{data: 'spk', sClass: 'nowrap-cell'},
				{data: 'spk_name'},
				{data: 'no_pr'},
				{data: 'name'},
				{data: 'item'},
				{data: 'quantity'},
				{data: 'deadline'},
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

		$('#datatable').on('click', '.delete-pr', function(){
			$('.pr_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pr-pdf', function(){
			$('.pr_id-onpdf').val($(this).data('id'));
		});

		$('select[name=f_user]').select2({
		});

		@if(Session::has('pr-pdf-error'))
		$('#pr-pdf').modal('show');
		@endif
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
</style>
@endsection

@section('content')
	
	<h1>Unconfirm Item</h1>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">All Year</option>
						{{-- <option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option> --}}
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="">All Month</option>
						{{-- <option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option> --}}
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				@can('confirmItem-pr')
				<form method="post" id="action" action="{{ route('backend.pr.confirmItem') }}" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
					<button type="submit" class="btn btn-success">Apply Selected</button>
					{{ csrf_field() }}
				</form>
				@endcan
			</div>
		</div>

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check-confirm" class="check-all">Confirm</label>
					</th>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check-reject" class="check-all">Reject</label>
					</th>
					<th>SPK</th>
					<th>SPK Name</th>
					<th>No PR</th>
					<th>Name Order</th>

					<th>Item</th>
					<th>Quantity</th>
					<th>Deadline</th>
					

				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>

					<td></td>
					<td></td>

				</tr>
			</tfoot>
		</table>
	</div>
	

@endsection