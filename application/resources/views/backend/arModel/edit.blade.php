@extends('backend.layout.master')

@section('title')
	Edit AR Model
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

	<h1>Edit AR Model</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.arModel.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="code" class="control-label col-md-3 col-sm-3 col-xs-12">Code <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="code" name="code" class="form-control {{$errors->first('code') != '' ? 'parsley-error' : ''}}" value="{{ old('code', $index->phone) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('code') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', $index->name) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name_game_object" class="control-label col-md-3 col-sm-3 col-xs-12">Name Game Object <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name_game_object" name="name_game_object" class="form-control {{$errors->first('name_game_object') != '' ? 'parsley-error' : ''}}" value="{{ old('name_game_object', $index->name_game_object) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name_game_object') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="asset_bundle_android" class="control-label col-md-3 col-sm-3 col-xs-12">Asset Bundle Android <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="asset_bundle_android" name="asset_bundle_android" class="form-control {{$errors->first('asset_bundle_android') != '' ? 'parsley-error' : ''}}" value="{{ old('asset_bundle_android') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('asset_bundle_android') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="asset_bundle_ios" class="control-label col-md-3 col-sm-3 col-xs-12">Asset Bundle iOS <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="asset_bundle_ios" name="asset_bundle_ios" class="form-control {{$errors->first('asset_bundle_ios') != '' ? 'parsley-error' : ''}}" value="{{ old('asset_bundle_ios') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('asset_bundle_ios') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="active" class="control-label col-md-3 col-sm-3 col-xs-12">Active 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" name="active" value="1" @if(old('active', $index->active)) checked @endif>Active</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('active') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.arModel') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection