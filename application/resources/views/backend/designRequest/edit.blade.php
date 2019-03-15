@extends('backend.layout.master')

@section('title')
	View / Edit Design Request
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>

<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

<script type="text/javascript">
	$(function() {
		$('select[name=division]').select2({
			placeholder: "Select Division"
		});

		$('input[name=datetime_deadline]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.designRequest.datatablesDesignCandidate') }}",
				type: "POST",
				data: {
			    	id : {{ $index->id }} 
				},
			},
			columns: [
				// {data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'fullname', sClass: 'nowrap-cell'},
				{data: 'description', sClass: 'nowrap-cell'},
				{data: 'image_preview'},
				{data: 'status_design', sClass: 'nowrap-cell'},
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
			// scrollX: true,
		});

		$('#datatable').on('click', '.preview-designRequest', function(){
			$('#preview-designRequest img').attr('src', $(this).data('image_preview'));
		});

		$('#datatable').on('click', '.setStatus-designRequest', function(){
			$('#setStatus-designRequest input[name=design_candidate_id]').val($(this).data('design_candidate_id'));
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
	
	<div id="preview-designRequest" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="#" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Image</h4>
					</div>
					<div class="modal-body">
						<img src="" style="width: 100%;">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	@can('setStatus-designRequest')
	{{-- Delete Design Request --}}
	<div id="setStatus-designRequest" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.designRequest.setStatus', [$index->id]) }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Chose this design?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="design_candidate_id" value="{{old('design_candidate_id')}}">
						<button type="submit" class="btn btn-success">Chose</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>View / Edit Design Request</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.designRequest.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<label for="title_request" class="control-label col-md-3 col-sm-3 col-xs-12">Title Request <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="title_request" name="title_request" class="form-control {{$errors->first('title_request') != '' ? 'parsley-error' : ''}}" value="{{ old('title_request', $index->title_request) }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('title_request') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="note_request" class="control-label col-md-3 col-sm-3 col-xs-12">Description Request <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="note_request" name="note_request" class="form-control {{$errors->first('note_request') != '' ? 'parsley-error' : ''}}" value="{{ old('note_request', $index->note_request) }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('note_request') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="division" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="division" name="division" class="form-control {{$errors->first('division') != '' ? 'parsley-error' : ''}}" value="{{ old('division') }}">
						@foreach($division as $list)
						<option value="{{ $list->code }}" @if(old('division', $index->division) == $list->code) selected @endif>{{ $list->name }}</option>
						@endforeach
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('division') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="budget" class="control-label col-md-3 col-sm-3 col-xs-12">Budget <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="budget" name="budget" class="form-control {{$errors->first('budget') != '' ? 'parsley-error' : ''}}" value="{{ old('budget', $index->budget) }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('budget') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="datetime_deadline" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="datetime_deadline" name="datetime_deadline" class="form-control {{$errors->first('datetime_deadline') != '' ? 'parsley-error' : ''}}" value="{{ date('d F Y', strtotime(old('datetime_deadline', $index->datetime_deadline))) }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('datetime_deadline') }}</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<a class="btn btn-primary" href="{{ route('backend.designRequest') }}">Cancel</a>
					<button type="submit" class="btn btn-success">Update</button>
				</div>
			</div>

		</form>
	</div>

	<div class="x_panel" style="overflow: auto;">

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					{{-- <th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th> --}}
					<th>Designer</th>
					<th>Note</th>

					<th>Preview</th>
					<th>Status</th>

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

					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>

@endsection