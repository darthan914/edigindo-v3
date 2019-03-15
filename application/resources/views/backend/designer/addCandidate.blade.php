@extends('backend.layout.master')

@section('title')
	Create Designer
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=date]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$('select[name=designer_id]').select2({
			placeholder: "Select Designer",
		});

	});
</script>

@endsection

@section('content')

	<h1>Create Designer</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.designer.storeDesignCandidate', [$design_request->id]) }}" method="post" enctype="multipart/form-data">
		

		<div class="form-group">
			<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Description <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="description" name="description" class="form-control {{$errors->first('description') != '' ? 'parsley-error' : ''}}">{{ old('description') }}</textarea>
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
				<input type="hidden" name="design_request_id" value="{{ $design_request->id }}">
				<a class="btn btn-primary" href="{{ route('backend.designer.designCandidate') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection