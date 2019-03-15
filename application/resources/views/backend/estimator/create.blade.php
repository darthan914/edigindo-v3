@extends('backend.layout.master')

@section('title')
	Create Estimator
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


		$(".btn-generate").click(function(){
        	$.post("{{ route('backend.estimator.getEstimator') }}",
	        {
	            sales_id: $('select[name=sales_id]').val(),
	        },
	        function(data){
	            $('input[name=no_estimator]').val(data);
	        });
	    });

	    $("select[name=sales_id]").change(function(){
        	$.post("{{ route('backend.estimator.getEstimator') }}",
	        {
	            sales_id: $('select[name=sales_id]').val(),
	        },
	        function(data){
	            $('input[name=no_estimator]').val(data);
	        });
	    });

	});
</script>

@endsection

@section('content')

	<h1>Create Estimator</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.estimator.store') }}" method="post" enctype="multipart/form-data">
		@if (Auth::user()->position != 'marketing')
		<div class="form-group">
			<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2">
					@foreach($sales as $list)
					<option value="{{ $list->id }}" @if(old('sales_id', Auth::id()) == $list->id) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
				</ul>
			</div>
		</div>
		@endif

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="no_estimator" class="control-label col-md-3 col-sm-3 col-xs-12">No Estimator <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<div class="input-group">
					<input type="text" id="no_estimator" name="no_estimator" class="form-control {{$errors->first('no_estimator') != '' ? 'parsley-error' : ''}}" value="{{ old('no_estimator', $no_estimator) }}">
					<span class="input-group-btn">
                        <button type="button" class="btn btn-primary btn-generate">Regenerate</button>
                    </span>
				</div>
				
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('no_estimator') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Note 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="description" name="description" class="form-control {{$errors->first('description') != '' ? 'parsley-error' : ''}}">{{ old('description') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('description') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Photo <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="file" id="photo" name="photo" class="form-control {{$errors->first('photo') != '' ? 'parsley-error' : ''}}" value="{{ old('photo') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('photo') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.estimator') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>
	</form>
	</div>

@endsection