@extends('backend.layout.master')

@section('title')
	Create Design Request
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('select[name=division]').select2({
			placeholder: "Select Division"
		});

		$('input[name=datetime_deadline]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});
	});
</script>

@endsection

@section('content')

	<h1>Create Design Request</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.designRequest.store') }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="title_request" class="control-label col-md-3 col-sm-3 col-xs-12">Title Request <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="title_request" name="title_request" class="form-control {{$errors->first('title_request') != '' ? 'parsley-error' : ''}}" value="{{ old('title_request') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('title_request') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="note_request" class="control-label col-md-3 col-sm-3 col-xs-12">Description Request <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="note_request" name="note_request" class="form-control {{$errors->first('note_request') != '' ? 'parsley-error' : ''}}" value="{{ old('note_request') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note_request') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="division" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="division" name="division" class="form-control {{$errors->first('division') != '' ? 'parsley-error' : ''}}" value="{{ old('division') }}">
					@foreach($division as $list)
					<option value="{{ $list->code }}" @if(old('division') == $list->code) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('division') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="budget" class="control-label col-md-3 col-sm-3 col-xs-12">Budget <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="budget" name="budget" class="form-control {{$errors->first('budget') != '' ? 'parsley-error' : ''}}" value="{{ old('budget') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('budget') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="datetime_deadline" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="datetime_deadline" name="datetime_deadline" class="form-control {{$errors->first('datetime_deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('datetime_deadline') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('datetime_deadline') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.designRequest') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection