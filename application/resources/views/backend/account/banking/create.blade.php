@extends('backend.layout.master')

@section('title')
	Create Banking
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

	<h1>Create Banking</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountBanking') }}" method="post" enctype="multipart/form-data">


		<div class="form-group">
			<label for="account_list_id_header" class="control-label col-md-3 col-sm-3 col-xs-12">Account Bank <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="account_list_id_header" name="account_list_id_header" class="form-control {{$errors->first('account_list_id_header') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Bank" data-allow-clear="true">
					<option value=""></option>
					@foreach($account_lists as $list)
					<option value="{{ $list->id }}" @if(old('account_list_id_header') == $list->id) selected @endif>{{ $list->account_name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('account_list_id_header') }}</li>
				</ul>
			</div>
		</div>

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
			<label for="note_header" class="control-label col-md-3 col-sm-3 col-xs-12">Note <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="note_header" name="note_header" class="form-control {{$errors->first('note_header') != '' ? 'parsley-error' : ''}}" value="{{ old('note_header') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note_header') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.account.accountBanking') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
				<input type="hidden" name="i" value="{{ old('i') }}">
			</div>
		</div>

	</form>
	</div>

@endsection