@extends('backend.layout.master')

@section('title')
	Edit User
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

	<h1>Edit User</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.user.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="username" class="control-label col-md-3 col-sm-3 col-xs-12">Username <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="username" name="username" class="form-control {{$errors->first('username') != '' ? 'parsley-error' : ''}}" value="{{ old('username', $index->username) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('username') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="email" name="email" class="form-control {{$errors->first('email') != '' ? 'parsley-error' : ''}}" value="{{ old('email', $index->email) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('email') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="password" class="control-label col-md-3 col-sm-3 col-xs-12">Password <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="password" id="password" name="password" class="form-control {{$errors->first('password') != '' ? 'parsley-error' : ''}}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('password') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="password_confirmation" class="control-label col-md-3 col-sm-3 col-xs-12">Password Confirmation <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="password" id="password_confirmation" name="password_confirmation" class="form-control {{$errors->first('password_confirmation') != '' ? 'parsley-error' : ''}}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('password_confirmation') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="first_name" class="control-label col-md-3 col-sm-3 col-xs-12">First Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="first_name" name="first_name" class="form-control {{$errors->first('first_name') != '' ? 'parsley-error' : ''}}" value="{{ old('first_name', $index->first_name) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('first_name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="last_name" class="control-label col-md-3 col-sm-3 col-xs-12">Last Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="last_name" name="last_name" class="form-control {{$errors->first('last_name') != '' ? 'parsley-error' : ''}}" value="{{ old('last_name', $index->last_name) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('last_name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="position_id" class="control-label col-md-3 col-sm-3 col-xs-12">Position <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="position_id" name="position_id" class="form-control {{$errors->first('position_id') != '' ? 'parsley-error' : ''}}">
					@foreach($position as $list)
					<option value="{{ $list->id }}" @if(old('position_id', $index->position_id) == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
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
					<option value="{{ $list->id }}" @if(old('division_id', $index->division_id) == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('division_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Number
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="phone" name="phone" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}" value="{{ old('phone', $index->phone) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('phone') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="no_ae" class="control-label col-md-3 col-sm-3 col-xs-12">No AE <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="no_ae" name="no_ae" class="form-control {{$errors->first('no_ae') != '' ? 'parsley-error' : ''}}" value="{{ old('no_ae', $index->no_ae) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('no_ae') }}</li>
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
					<option value="{{ $list->id }}" @if(old('parent_id', $index->parent_id) == $list->id) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
				</ul>
			</div>
		</div>
		

		<div class="form-group">
			<label for="active" class="control-label col-md-3 col-sm-3 col-xs-12">Active 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="active" name="active" class="form-control {{$errors->first('active') != '' ? 'parsley-error' : ''}}">
					<option value="0" @if(old('active', $index->active) == '0') selected @endif>Inactive</option>
					<option value="1" @if(old('active', $index->active) == '1') selected @endif>Active</option>
					<option value="-1" @if(old('active', $index->active) == '-1') selected @endif>Inactive, (Can impersonate)</option>
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('active') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="signature" class="control-label col-md-3 col-sm-3 col-xs-12">Signature
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="signature" name="signature" class="form-control {{$errors->first('signature') != '' ? 'parsley-error' : ''}}">
				@if($index->signature)
				<img src="{{ asset($index->signature) }}" style="width: 100px;" alt="{{$index->signature}}">
				<label class="checkbox-inline"><input type="checkbox" name="remove_signature" value="1" @if(old('remove_signature', $index->remove_signature) == 1) checked @endif>Remove Signature</label>
				@endif
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('signature') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Photo
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="photo" name="photo" class="form-control {{$errors->first('photo') != '' ? 'parsley-error' : ''}}">
				@if($index->photo)
				<img src="{{ asset($index->photo) }}" style="width: 100px;" alt="{{$index->photo}}">
				<label class="checkbox-inline"><input type="checkbox" name="remove_photo" value="1" @if(old('remove_photo', $index->remove_photo) == 1) checked @endif>Remove Photo</label>
				@endif
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('photo') }}</li>
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