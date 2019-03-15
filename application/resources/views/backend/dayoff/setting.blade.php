@extends('backend.layout.master')

@section('title')
	Leave Setting
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
				url: "{{ route('backend.dayoff.datatablesSetting') }}",
				type: "POST",
				data: {
			    	f_year: $('*[name=f_year]').val(),
				},
			},
			columns: [
				// {data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'year', sClass: 'nowrap-cell'},
				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'number_available', sClass: 'nowrap-cell'},
				{data: 'action', sClass: 'nowrap-cell'},
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

		$('#datatable').on('click', '.updateSetting-dayoff', function(){
			$('#updateSetting-dayoff input[name=number_available]').val($(this).data('number_available'));
			$('#updateSetting-dayoff input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('updateSetting-dayoff-error'))
		$('#updateSetting-dayoff').modal('show');
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
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	@can('setting-dayoff')
	{{-- Create Leave List --}}
	<div id="updateSetting-dayoff" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.dayoff.updateSetting') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Setting Number Available</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-number_available">
							<label for="number_available" class="control-label col-md-3 col-sm-3 col-xs-12">Number Available <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="date" name="number_available" class="form-control {{$errors->first('number_available') != '' ? 'parsley-error' : ''}}" value="{{ old('number_available') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('number_available') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Leave Setting</h1>

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
				<form method="post" id="action" action="#" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					{{-- <th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th> --}}
					<th>Year</th>

					<th>Sales</th>
					<th>Number Available</th>
					<th>Action</th>

				</tr>
			</thead>
			<tfoot>
				<tr>
					{{-- <td></td> --}}

					<td></td>

					<td></td>
					<td></td>
					<td></td>

				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection