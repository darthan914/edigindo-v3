@extends('backend.layout.master')

@section('title')
	Edit Activity
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'activity' );
	$(function() {

	});
</script>

@endsection

@section('content')

	<h1>Edit Activity</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.activity.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="date_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="date" id="date_activity" name="date_activity" class="form-control {{$errors->first('date_activity') != '' ? 'parsley-error' : ''}}" value="{{ old('date_activity', $index->date_activity) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="activity" name="activity" class="form-control {{$errors->first('activity') != '' ? 'parsley-error' : ''}}">{{ old('activity', $index->activity) }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('activity') }}</li>
				</ul>
			</div>
		</div>


		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.activity') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection