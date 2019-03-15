@extends('backend.layout.master')

@section('title')
	Edit Designer
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>

<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.designer.datatablesEditDesignCandidate') }}",
				type: "POST",
				data: {
			    	id : {{ $index->id }} 
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'image_preview'},
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

	<h1>Edit Designer</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.designer.updateDesignCandidate', [$index->id]) }}" method="post" enctype="multipart/form-data">
			

			<div class="form-group">
				<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Description <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<textarea id="description" name="description" class="form-control {{$errors->first('description') != '' ? 'parsley-error' : ''}}">{{ old('description', $index->description) }}</textarea>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('description') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="image_preview" class="control-label col-md-3 col-sm-3 col-xs-12">Images <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="file" name="image_preview[]" multiple class="form-control">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('image_preview') }}</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<input type="hidden" name="id" value="{{ $index->id }}">
					<a class="btn btn-primary" href="{{ route('backend.designer.designCandidate') }}">Cancel</a>
					<button type="submit" class="btn btn-success">Update</button>
				</div>
			</div>

		</form>
	</div>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.designer.actionDesignCandidatePreview') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">

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

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Preview</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>

@endsection