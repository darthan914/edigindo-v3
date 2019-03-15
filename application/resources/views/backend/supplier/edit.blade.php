@extends('backend.layout.master')

@section('title')
	Edit Supplier
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

	<h1>Edit Supplier</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.supplier.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Supplier <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', $index->name) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="bank" class="control-label col-md-3 col-sm-3 col-xs-12">Bank <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="bank" name="bank" class="form-control {{$errors->first('bank') != '' ? 'parsley-error' : ''}}" value="{{ old('bank', $index->bank) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('bank') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="no_rekening" class="control-label col-md-3 col-sm-3 col-xs-12">No Rekening <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="no_rekening" name="no_rekening" class="form-control {{$errors->first('no_rekening') != '' ? 'parsley-error' : ''}}" value="{{ old('no_rekening', $index->no_rekening) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('no_rekening') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name_rekening" class="control-label col-md-3 col-sm-3 col-xs-12">Name Rekening <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name_rekening" name="name_rekening" class="form-control {{$errors->first('name_rekening') != '' ? 'parsley-error' : ''}}" value="{{ old('name_rekening', $index->name_rekening) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name_rekening') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="cp" class="control-label col-md-3 col-sm-3 col-xs-12">Contact Person <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="cp" name="cp" class="form-control {{$errors->first('cp') != '' ? 'parsley-error' : ''}}" value="{{ old('cp', $index->cp) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('cp') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="phone_home" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Home <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="phone_home" name="phone_home" class="form-control {{$errors->first('phone_home') != '' ? 'parsley-error' : ''}}" value="{{ old('phone_home', $index->phone_home) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('phone_home') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="phone_mobile" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Mobile <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="phone_mobile" name="phone_mobile" class="form-control {{$errors->first('phone_mobile') != '' ? 'parsley-error' : ''}}" value="{{ old('phone_mobile', $index->phone_mobile) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('phone_mobile') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.supplier') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection