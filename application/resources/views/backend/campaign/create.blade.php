@extends('backend.layout.master')

@section('title')
	Create Campaign
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {

	});
</script>

@endsection

@section('content')

	<h1>Create Campaign</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.campaign.store') }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="year" class="control-label col-md-3 col-sm-3 col-xs-12">Year <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="year" name="year" class="form-control {{$errors->first('year') != '' ? 'parsley-error' : ''}}" value="{{ old('year') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('year') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('value') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.campaign') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection