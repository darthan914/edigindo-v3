@extends('backend.layout.master')

@section('title')
	Create Purchasing
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

	<h1>Create Purchasing</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountPurchasing') }}" method="post" enctype="multipart/form-data">


		<div class="form-group">
			<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Company" data-allow-clear="true">
					<option value=""></option>
					@foreach($company as $list)
					<option value="{{ $list->id }}" @if(old('company_id') == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="invoice" class="control-label col-md-3 col-sm-3 col-xs-12">Invoice <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="invoice" name="invoice" class="form-control {{$errors->first('invoice') != '' ? 'parsley-error' : ''}}" value="{{ old('invoice') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('invoice') }}</li>
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
			<label for="spk_id" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="spk_id" name="spk_id" class="form-control {{$errors->first('spk_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select SPK" data-allow-clear="true">
					<option value=""></option>
					@foreach($spk as $list)
					<option value="{{ $list->id }}" @if(old('spk_id') == $list->id) selected @endif>{{ $list->spk }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('spk_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="supplier_id" class="control-label col-md-3 col-sm-3 col-xs-12">Supplier <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="supplier_id" name="supplier_id" class="form-control {{$errors->first('supplier_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Supplier" data-allow-clear="true">
					<option value=""></option>
					@foreach($supplier as $list)
					<option value="{{ $list->id }}" @if(old('supplier_id') == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('supplier_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}" value="{{ old('note') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.account.accountPurchasing') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
				<input type="hidden" name="i" value="{{ old('i') }}">
			</div>
		</div>

	</form>
	</div>

@endsection