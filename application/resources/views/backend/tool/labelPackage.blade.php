@extends('backend.layout.master')

@section('title')
	Label Package Tool
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

	<h1>Label Package Tool</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.tool.generateLabelPackage') }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="spk" class="control-label col-md-3 col-sm-3 col-xs-12">No SPK <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="spk" name="spk" class="form-control {{$errors->first('spk') != '' ? 'parsley-error' : ''}}" value="{{ old('spk') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('spk') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="po" class="control-label col-md-3 col-sm-3 col-xs-12">No PO <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="po" name="po" class="form-control {{$errors->first('po') != '' ? 'parsley-error' : ''}}" value="{{ old('po') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('po') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="image" class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="image" name="image" class="form-control {{$errors->first('image') != '' ? 'parsley-error' : ''}}" value="{{ old('image') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('image') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="image_name" class="control-label col-md-3 col-sm-3 col-xs-12">Image Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="image_name" name="image_name" class="form-control {{$errors->first('image_name') != '' ? 'parsley-error' : ''}}" value="{{ old('image_name') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('image_name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="with_logo" class="control-label col-md-3 col-sm-3 col-xs-12">With Logo 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" name="with_logo" checked>Yes</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('with_logo') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="import" class="control-label col-md-3 col-sm-3 col-xs-12">Import Excel <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="import" name="import" class="form-control {{$errors->first('import') != '' ? 'parsley-error' : ''}}" value="{{ old('import') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('import') }}</li>
				</ul>
			</div>
		</div>


		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.tool.downloadLabelPackageTemplate') }}">Download Template</a>
				<button type="submit" class="btn btn-success">Generate</button>
			</div>
		</div>

	</form>
	</div>

@endsection