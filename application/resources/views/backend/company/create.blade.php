@extends('backend.layout.master')

@section('title')
	Create Company
@endsection

@section('script')

@endsection

@section('content')

	<h1>Create Company</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.company.store') }}" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}" onchange="document.getElementById('short_name').value = document.getElementById('name').value.substring(0,5)">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="short_name" class="control-label col-md-3 col-sm-3 col-xs-12">Short Name
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="short_name" name="short_name" class="form-control {{$errors->first('short_name') != '' ? 'parsley-error' : ''}}" value="{{ old('short_name') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('short_name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="phone_company" class="control-label col-md-3 col-sm-3 col-xs-12">Phone</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="phone_company" name="phone_company" class="form-control {{$errors->first('phone_company') != '' ? 'parsley-error' : ''}}" value="{{ old('phone_company') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('phone_company') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="fax" class="control-label col-md-3 col-sm-3 col-xs-12">Fax
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="fax" name="fax" class="form-control {{$errors->first('fax') != '' ? 'parsley-error' : ''}}" value="{{ old('fax') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('fax') }}</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>

			<h2>PIC Company</h2>

			<div class="form-group">
				<label for="first_name" class="control-label col-md-3 col-sm-3 col-xs-12">First Name <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="first_name" name="first_name" class="form-control {{$errors->first('first_name') != '' ? 'parsley-error' : ''}}" value="{{ old('first_name') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('first_name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="last_name" class="control-label col-md-3 col-sm-3 col-xs-12">Last Name
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="last_name" name="last_name" class="form-control {{$errors->first('last_name') != '' ? 'parsley-error' : ''}}" value="{{ old('last_name') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('last_name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="gender" class="control-label col-md-3 col-sm-3 col-xs-12">Gender <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<label class="radio-inline"><input type="radio" id="gender-male" name="gender" value="M" @if(old('gender') != '' && old('gender') == 'M') checked @endif>Male</label> 
					<label class="radio-inline"><input type="radio" id="gender-female" name="gender" value="F" @if(old('gender') != '' && old('gender') == 'F') checked @endif>Female</label>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('gender') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="position" class="control-label col-md-3 col-sm-3 col-xs-12">Position
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="position" name="position" class="form-control {{$errors->first('position') != '' ? 'parsley-error' : ''}}" value="{{ old('position') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('position') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="phone_pic" class="control-label col-md-3 col-sm-3 col-xs-12">Phone
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="phone_pic" name="phone_pic" class="form-control {{$errors->first('phone_pic') != '' ? 'parsley-error' : ''}}" value="{{ old('phone_pic') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('phone_pic') }}</li>
					</ul>
				</div>
			</div>

			

			<div class="form-group">
				<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="email" id="email" name="email" class="form-control {{$errors->first('email') != '' ? 'parsley-error' : ''}}" value="{{ old('email') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('email') }}</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>

			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<a class="btn btn-primary" href="{{ route('backend.company') }}">Cancel</a>
					<button type="submit" class="btn btn-success">Submit</button>
				</div>
			</div>

		</form>
	</div>
	

@endsection