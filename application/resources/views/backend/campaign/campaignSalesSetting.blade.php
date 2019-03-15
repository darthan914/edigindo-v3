@extends('backend.layout.master')

@section('title')
	Campaign Sales Setting
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
				url: "{{ route('backend.campaign.datatablesCampaignSalesSetting') }}",
				type: "POST",
				data: {
			    	f_year: $('*[name=f_year]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'year', sClass: 'nowrap-cell'},
				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'status', sClass: 'nowrap-cell'},
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
			scrollX: true,
		});

		$('#datatable').on('click', '.delete-campaign', function(){
			$('#delete-campaign input[name=id]').val($(this).data('id'));
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
	<h1>Campaign Sales Setting</h1>


	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.campaign.actionCampaignSalesSetting') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('create-campaign')
					<a href="{{ route('backend.campaign.create') }}" class="btn btn-default">Create</a>
					@endif
					<select class="form-control" name="action">
						<option value="old">Set to Tim Tempur</option>
						<option value="new">Set to Super Junior</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Year</th>

					<th>Sales</th>
					<th>Status</th>

				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>

					<td></td>
					<td></td>
					<td></td>

				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection