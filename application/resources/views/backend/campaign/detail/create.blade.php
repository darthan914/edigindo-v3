@extends('backend.layout.master')

@section('title')
	Create Campaign Detail
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

	<h1>Create Campaign Detail</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.campaign.storeCampaignDetail') }}" method="post" enctype="multipart/form-data">

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
			<label for="start_month" class="control-label col-md-3 col-sm-3 col-xs-12">Start Month <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="start_month" name="start_month" class="form-control {{$errors->first('start_month') != '' ? 'parsley-error' : ''}}">
					<option value="1" @if(old('start_month') == 1) selected @endif>January</option>
					<option value="2" @if(old('start_month') == 2) selected @endif>Febuary</option>
					<option value="3" @if(old('start_month') == 3) selected @endif>March</option>
					<option value="4" @if(old('start_month') == 4) selected @endif>April</option>
					<option value="5" @if(old('start_month') == 5) selected @endif>May</option>
					<option value="6" @if(old('start_month') == 6) selected @endif>June</option>

					<option value="7" @if(old('start_month') == 7) selected @endif>July</option>
					<option value="8" @if(old('start_month') == 8) selected @endif>August</option>
					<option value="9" @if(old('start_month') == 9) selected @endif>September</option>
					<option value="10" @if(old('start_month') == 10) selected @endif>October</option>
					<option value="11" @if(old('start_month') == 11) selected @endif>November</option>
					<option value="12" @if(old('start_month') == 12) selected @endif>December</option>

				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('start_month') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="end_month" class="control-label col-md-3 col-sm-3 col-xs-12">End Month <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="end_month" name="end_month" class="form-control {{$errors->first('end_month') != '' ? 'parsley-error' : ''}}">
					<option value="1" @if(old('end_month') == 1) selected @endif>January</option>
					<option value="2" @if(old('end_month') == 2) selected @endif>Febuary</option>
					<option value="3" @if(old('end_month') == 3) selected @endif>March</option>
					<option value="4" @if(old('end_month') == 4) selected @endif>April</option>
					<option value="5" @if(old('end_month') == 5) selected @endif>May</option>
					<option value="6" @if(old('end_month') == 6) selected @endif>June</option>

					<option value="7" @if(old('end_month') == 7) selected @endif>July</option>
					<option value="8" @if(old('end_month') == 8) selected @endif>August</option>
					<option value="9" @if(old('end_month') == 9) selected @endif>September</option>
					<option value="10" @if(old('end_month') == 10) selected @endif>October</option>
					<option value="11" @if(old('end_month') == 11) selected @endif>November</option>
					<option value="12" @if(old('end_month') == 12) selected @endif>December</option>
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('end_month') }}</li>
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

		<div class="form-group">
			<label for="for_expo" class="control-label col-md-3 col-sm-3 col-xs-12">
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" value="1" name="for_expo">For Expo</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('value') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<input type="hidden" name="campaign_id" value="{{ $index->id }}">
				<a class="btn btn-primary" href="{{ route('backend.campaign.edit', ['index' => $index->id]) }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection