@extends('backend.layout.master')

@section('title')
	Create User
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

		$('select[name=position_id]').select2({
			placeholder: "Select Position",
		});

		$('select[name=division_id]').select2({

		});

		$('select[name=parent_id]').select2({
		});

	});
</script>

@endsection

@section('content')

	<h1>Create User</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.user.store') }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="email" name="email" class="form-control {{$errors->first('email') != '' ? 'parsley-error' : ''}}" value="{{ old('email') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('email') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="position_id" class="control-label col-md-3 col-sm-3 col-xs-12">Position <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="position_id" name="position_id" class="form-control {{$errors->first('position_id') != '' ? 'parsley-error' : ''}}">
					@foreach($position as $list)
					<option value="{{ $list->id }}" @if(old('position_id') == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<input type="hidden" name="position" id="position" value="{{ old('position') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('position_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}}">
					<option value="">Any</option>
					@foreach($division as $list)
					<option value="{{ $list->id }}" @if(old('division_id', Auth::user()->divisions->id) == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('division_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="parent_id" class="control-label col-md-3 col-sm-3 col-xs-12">Leader
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="parent_id" name="parent_id" class="form-control {{$errors->first('parent_id') != '' ? 'parsley-error' : ''}}">
					@if(in_array(Auth::user()->positions->id, getConfigValue('super_admin_position', true)) || in_array(Auth::id(), getConfigValue('super_admin_user', true)))
					<option value="">Root</option>
					@endif
					@foreach($parent as $list)
					<option value="{{ $list->id }}" @if(old('parent_id', Auth::id()) == $list->id) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="password_user" class="control-label col-md-3 col-sm-3 col-xs-12">Password User<span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="password" id="password_user" name="password_user" class="form-control {{$errors->first('password_user') != '' ? 'parsley-error' : ''}}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('password_user') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.user') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection