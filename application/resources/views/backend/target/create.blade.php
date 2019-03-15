@extends('backend.layout.master')

@section('title')
	Create Target
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

	<h1>Create Target</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.target.store') }}" method="post" enctype="multipart/form-data">

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
			<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select multiple class="form-control update-config select2" name="sales_id[]">
					@foreach ($sales as $list)
					<option value="{{ $list->id }}" @if(in_array($list->id, old('sales_id', []))) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="sales_value" class="control-label col-md-3 col-sm-3 col-xs-12">Sales Value <span class="required">*(if sales selected)</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="sales_value" name="sales_value" class="form-control {{$errors->first('sales_value') != '' ? 'parsley-error' : ''}}" value="{{ old('sales_value') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('sales_value') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="less_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Not Reach Target
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="less_target" name="less_target" class="form-control {{$errors->first('less_target') != '' ? 'parsley-error' : ''}}">{{ old('less_target') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('less_target') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="reach_target" class="control-label col-md-3 col-sm-3 col-xs-12">Note If Reach Target
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="reach_target" name="reach_target" class="form-control {{$errors->first('reach_target') != '' ? 'parsley-error' : ''}}">{{ old('reach_target') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('reach_target') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.target') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection