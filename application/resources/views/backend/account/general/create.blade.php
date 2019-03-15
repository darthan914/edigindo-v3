@extends('backend.layout.master')

@section('title')
	Create General
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

	<h1>Create General</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountGeneral') }}" method="post" enctype="multipart/form-data">


		<div class="form-group">
			<label for="date" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="date" id="date" name="date" class="form-control {{$errors->first('date') != '' ? 'parsley-error' : ''}}" value="{{ old('date') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date') }}</li>
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
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.account.accountJournal', ['tab' => 'GENERAL']) }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
				<input type="hidden" name="i" value="{{ old('i') }}">
			</div>
		</div>

	</form>
	</div>

@endsection