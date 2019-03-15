@extends('backend.layout.master')

@section('title')
	Status Item Received List
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
				url: "{{ route('backend.pr.datatablesItem') }}",
				type: "post",
				data: {
			    	f_user : $('*[name=f_user]').val(),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	f_status : $('*[name=f_status]').val(),
			    	f_id         : getUrlParameter('f_id'),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'no_pr'},
				{data: 'item'},
				{data: 'quantity'},
				{data: 'spk'},
				{data: 'name'},
				{data: 'fullname'},
				{data: 'date_order'},
				{data: 'date_request'},
				{data: 'status_received'},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
			],
			order : [[ 5, "desc" ]],
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

		$('#datatable').on('click', '.pr-confirmItem', function(){
			$('#pr-confirmItem input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pr-complainItem', function(){
			$('#pr-complainItem input[name=id]').val($(this).data('id'));
		});

		$('select[name=f_user]').select2({
		});

		@if(Session::has('pr-complainItem-error'))
		$('#pr-complainItem').modal('show');
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

	.rating
	{
		position: relative;
		text-align: right;
		display: flex;
	}

	.flip
	{
		transform: rotateY(180deg);
		display: flex;
	}

	.rating input[type=radio]
	{
		display: none;
	}

	.rating label
	{
		display: block;
		cursor: pointer;
	}

	.rating label:before
	{
		content: '\f005';
		font-family: fontAwesome;
		position: relative;
		display: block;
		color: #101010;
	}

	.rating label:after
	{
		content: '\f005';
		font-family: fontAwesome;
		position: absolute;
		top: 0;
		display: block;
		color: gold;
		opacity: 0;
	}

	.rating label:hover label:after,
	.rating label:hover ~ label:after,
	.rating input[type=radio]:checked ~ label:after
	{
		opacity: 1;
	}
</style>
@endsection

@section('content')
	
	@can('confirmItem-pr')
	{{-- Confirm User --}}
	<div id="pr-confirmItem" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.receivedItem') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Confirm Item?</h4>
					</div>
					<div class="modal-body">
						<div class="rating">
							<div class="flip">
								<input type="radio" name="rating" value="5" id="star5" checked><label for="star5"></label>
								<input type="radio" name="rating" value="4" id="star4"><label for="star4"></label>
								<input type="radio" name="rating" value="3" id="star3"><label for="star3"></label>
								<input type="radio" name="rating" value="2" id="star2"><label for="star2"></label>
								<input type="radio" name="rating" value="1" id="star1"><label for="star1"></label>
							</div>
								
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Confirm</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Inactive User --}}
	<div id="pr-complainItem" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.complainItem') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Complain Item?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="note_received" class="control-label col-md-3 col-sm-3 col-xs-12">Reason<span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea type="note_received" id="note_received" name="note_received" class="form-control {{$errors->first('note_received') != '' ? 'parsley-error' : ''}}"></textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note_received') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Complain</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan
	
	<h1>Status Item Received List</h1>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_status" onchange="this.form.submit()">
						<option value="">Waiting</option>
						<option value="all" {{ $request->f_status == 'all' ? 'selected' : '' }}>All Status</option>
						<option value="CONFIRMED" {{ $request->f_status == 'CONFIRMED' ? 'selected' : '' }}>Confirm</option>
						<option value="COMPLAIN" {{ $request->f_status == 'COMPLAIN' ? 'selected' : '' }}>Complain</option>
					</select>

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

					<select class="form-control" name="f_user" onchange="this.form.submit()">
						<option value="">My PR</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allUser-pr')
							<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All User</option>
						@endcan
						
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
						
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.pr.action') }}" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
					
				</form>
			</div>
		</div>

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>No PR</th>
					<th>Item</th>
					<th>Quantity</th>

					<th>SPK</th>
					<th>SPK Name</th>
					<th>Request by</th>

					<th>Order Date</th>
					<th>Need Date</th>
					<th>Status</th>

					<th>Action</th>
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

					<td></td>
					<td></td>
					<td></td>
					
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	

@endsection