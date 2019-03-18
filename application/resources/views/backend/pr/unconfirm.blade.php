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
			    	search : $('*[name=search]').val(),
			    	f_service : 0,
				},
			},
			columns: [
				{data: 'confirm', orderable: false, searchable: false},
				{data: 'reject', orderable: false, searchable: false},
				{data: 'pr_id', sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell'},
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
			dom: "<l<tr>ip>"
			
		});


		var table = $('#datatable-service').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatablesUnconfirm') }}",
				type: "post",
				data: {
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	search : $('*[name=search]').val(),
			    	f_service : 1,
				},
			},
			columns: [
				{data: 'confirm', orderable: false, searchable: false},
				{data: 'confirm_not_service', orderable: false, searchable: false},
				{data: 'reject', orderable: false, searchable: false},
				{data: 'pr_id', sClass: 'nowrap-cell'},
				{data: 'item', sClass: 'nowrap-cell'},
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
			dom: "<l<tr>ip>"
			
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

		@if(Session::has('pr-pdf-error'))
		$('#pr-pdf').modal('show');
		@endif

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
</style>
@endsection

@section('content')
	
	<h1>Unconfirm Item</h1>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_year" onchange="this.form.submit()">
						<option value="">All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_month" onchange="this.form.submit()">
						<option value="">All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<input type="text" name="search" placeholder="Search" class="form-control" onchange="this.form.submit()" value="{{ $request->search }}">
					<input type="hidden" name="tab">
				</form>
			</div>
			<div class="col-md-6">
				@can('confirm-pr')
				<form method="post" id="action" action="{{ route('backend.pr.updateConfirm') }}" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
					<button type="submit" class="btn btn-success">Apply Selected</button>
					{{ csrf_field() }}
				</form>
				@endcan
			</div>
		</div>
	</div>

	<div class="x_panel" style="overflow: auto;">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="unconfirmTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li class="{{ $request->tab === 'unconfirm' || $request->tab == '' ? 'active' : ''}}"><a href="#unconfirm" data-toggle="tab" class="tab-active" data-id="unconfirm">Unconfirm</a>
				</li>
				<li class="{{ $request->tab === 'unconfirm_service' ? 'active' : ''}}"><a href="#unconfirm_service" data-toggle="tab" class="tab-active" data-id="unconfirm_service">Unconfirm Service</a>
				</li>

			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'unconfirm' || $request->tab == '' ? 'active in' : ''}}" id="unconfirm" >
					<table class="table table-striped table-bordered" id="datatable">
						<thead>
							<tr>
								<th>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-confirm" class="check-all">Confirm</label>
								</th>
								<th>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-reject" class="check-all">Reject</label>
								</th>

								<th>Info</th>
								<th>Item</th>
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

							</tr>
						</tfoot>
					</table>
				</div>

				<div class="tab-pane fade {{ $request->tab === 'unconfirm_service' || $request->tab == '' ? 'active in' : ''}}" id="unconfirm_service" >
					<table class="table table-striped table-bordered" id="datatable-service">
						<thead>
							<tr>
								<th>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-confirm" class="check-all">Confirm</label>
								</th>
								<th>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-confirm_not_service" class="check-all">Confirm AS Non Service</label>
								</th>
								<th>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-reject" class="check-all">Reject</label>
								</th>

								<th>Info</th>
								<th>Item</th>
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

							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

	</div>
	

@endsection