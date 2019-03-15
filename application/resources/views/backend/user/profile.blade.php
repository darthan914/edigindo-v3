@extends('backend.layout.master')

@section('title')
	Profile
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

		$('select[name=division]').select2({
			placeholder: "Select Division",
			allowClear: true
		});

		$('select[name=parent_id]').select2({
			placeholder: "Select Leader",
			allowClear: true
		});
	});
</script>

@endsection

@section('content')

	<h1>Profile</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.user.updateProfile') }}" method="post" enctype="multipart/form-data">
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

				<a class="btn btn-primary" href="{{ route('backend.home') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>
	</form>
	</div>

@endsection