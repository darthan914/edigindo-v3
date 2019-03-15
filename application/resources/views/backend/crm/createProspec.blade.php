@extends('backend.layout.master')

@section('title')
	Create CRM
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {

		$('select[name=company_id]').select2({
			placeholder: "Select / New Company",
			allowClear: true
		});
		$('select[name=brand_id]').select2({
			placeholder: "Select / New Brand",
			allowClear: true
		});
		$('select[name=address_id]').select2({
			placeholder: "Select / New Address",
			allowClear: true
		});
		$('select[name=pic_id]').select2({
			placeholder: "Select / New PIC",
			allowClear: true
		});


		var old_company = {{ old('company_id') != '' ? old('company_id') : 0 }};
		var old_brand = {{ old('brand_id') != '' ? old('brand_id') : 0 }};
		var old_address = {{ old('address_id') != '' ? old('address_id') : 0 }};
		var old_pic = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", true);

		if($('select[name=company_id]').val() == ''){
			$('select[name=brand_id], select[name=address_id], select[name=pic_id]').val('').trigger('change');
			$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", true);
		}
		else{
			$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", false);

			$.post("{{ route('backend.company.getBrand') }}",
	        {
	            company_id: $('select[name=company_id]').val(),
	        },
	        function(data){
	            $('select[name=brand_id]').empty();
	            $('select[name=brand_id]').append("<option value=''>Select / New Brand</option>");

				$.each(data, function(i, field){
					if (old_brand == field.id) 
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"' selected>"+ field.brand+"</option>");
						$('select[name=brand_id]').val(old_brand).trigger('change');
					}
					else
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.brand+"</option>");
					}
					
				});
	        });

	        $.post("{{ route('backend.company.getAddress') }}",
	        {
	            company_id: $('select[name=company_id]').val(),
	        },
	        function(data){
	            $('select[name=address_id]').empty();
	            $('select[name=address_id]').append("<option value=''>Select / New Address</option>");

				$.each(data, function(i, field){
					if (old_address == field.address) 
					{
						$('select[name=address_id]').append("<option value='"+ field.id +"' selected>"+ field.address+"</option>");
						$('select[name=address_id]').val(old_address).trigger('change');
					}
					else
					{
						$('select[name=address_id]').append("<option value='"+ field.id +"'>"+ field.address+"</option>");
					}
					
				});
	        });

	        $.post("{{ route('backend.company.getPic') }}",
	        {
	            company_id: $('select[name=company_id]').val(),
	        },
	        function(data){
	            $('select[name=pic_id]').empty();
	            $('select[name=pic_id]').append("<option value=''>Select / New PIC</option>");

				$.each(data, function(i, field){
					if (old_pic == field.id) 
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"' selected>"+ field.fullname +" ("+field.nickname+")"+"</option>");
						$('select[name=pic_id]').val(old_pic).trigger('change');
					}
					else
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"'>"+ field.fullname +" ("+field.nickname+")"+"</option>");
					}
					
					
				});
	        });
		}

		

		
	
		$(document).on('change','select[name=company_id]', function(){

			if($(this).val() == ''){
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').val('').trigger('change');
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", true);

				$('.new-company').slideDown();
			}
			else{
				$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", false);
				$('.new-company').slideUp();

				$.post("{{ route('backend.company.getBrand') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){

		        	$('select[name=brand_id]').empty();
		            $('select[name=brand_id]').append("<option value=''>Select / New Brand</option>");

					$.each(data, function(i, field){
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.brand+"</option>");
					});

					$('select[name=brand_id]').val('').trigger('change');
		        });

				$.post("{{ route('backend.company.getAddress') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=address_id]').empty();
		            $('select[name=address_id]').append("<option value=''>Select / New Address</option>");
					$.each(data, function(i, field){
						$('select[name=address_id]').append("<option value='"+ field.id +"'></option>");
					});
					$('select[name=address_id]').val('').trigger('change');
		        });

		        $.post("{{ route('backend.company.getPic') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=pic_id]').empty();
		            $('select[name=pic_id]').append("<option value=''>Select / New PIC</option>");

					$.each(data, function(i, field){
						$('select[name=pic_id]').append("<option value='"+ field.id +"'>"+ field.fullname +" ("+field.nickname+")"+"</option>");
					});
					$('select[name=pic_id]').val('').trigger('change');
		        });
			}
		});

		$(document).on('change','select[name=brand_id]', function(){
			if($(this).val() == 0){
				$('.new-brand').slideDown();
			}
			else
			{
				$('.new-brand').slideUp();
			}
		});

		$(document).on('change','select[name=address_id]', function(){
			if($(this).val() == 0){
				$('.new-address').slideDown();
			}
			else
			{
				$('.new-address').slideUp();
			}
		});

		$(document).on('change','select[name=pic_id]', function(){
			if($(this).val() == 0){
				$('.new-pic').slideDown();
			}
			else
			{
				$('.new-pic').slideUp();
			}
		});

		if(old_company == 0){
			$('.new-company').show();
		}
		else
		{
			$('.new-company').hide();
		}

		if(old_brand == 0){
			$('.new-brand').show();
		}
		else
		{
			$('.new-brand').hide();
		}

		if(old_address == 0){
			$('.new-address').show();
		}
		else
		{
			$('.new-address').hide();
		}

		if(old_pic == 0){
			$('.new-pic').show();
		}
		else
		{
			$('.new-pic').hide();
		}

	});
</script>

@endsection

@section('content')

	<h1>Create CRM</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.crm.storeProspec') }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Database Company <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}}" value="{{ old('company_id') }}">
					<option value="">Select / New Company</option>
					@foreach($company as $list)
					<option value="{{ $list->id }}" @if(old('company_id') == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-company">
			<label for="company_name_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">Company Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="company_name_prospec" name="company_name_prospec" class="form-control {{$errors->first('company_name_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('company_name_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_name_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-company">
			<label for="company_phone_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">Company Phone 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="company_phone_prospec" name="company_phone_prospec" class="form-control {{$errors->first('company_phone_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('company_phone_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_phone_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-company">
			<label for="company_fax_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">Company Fax 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="company_fax_prospec" name="company_fax_prospec" class="form-control {{$errors->first('company_fax_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('company_fax_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_fax_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<label for="brand_id" class="control-label col-md-3 col-sm-3 col-xs-12">Database Brand 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="brand_id" name="brand_id" class="form-control {{$errors->first('brand_id') != '' ? 'parsley-error' : ''}}" value="{{ old('brand_id') }}">
					<option value="">Select / New Brand</option>
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('brand_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-brand">
			<label for="brand_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">Brand Name 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="brand_prospec" name="brand_prospec" class="form-control {{$errors->first('brand_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('brand_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('brand_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<label for="address_id" class="control-label col-md-3 col-sm-3 col-xs-12">Database Address
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="address_id" name="address_id" class="form-control {{$errors->first('address_id') != '' ? 'parsley-error' : ''}}">
					<option value="">Select / New Address</option>
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('address_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-address">
			<label for="address_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">Address 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="address_prospec" name="address_prospec" class="form-control {{$errors->first('address_prospec') != '' ? 'parsley-error' : ''}}">{{ old('address_prospec') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('address_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">Database PIC <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_id') }}">
					<option value="" data-second_phone="">Select / New PIC</option>
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_fullname_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Fullname <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="pic_fullname_prospec" name="pic_fullname_prospec" class="form-control {{$errors->first('pic_fullname_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_fullname_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_fullname_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_gender_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Gender <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="radio-inline"><input type="radio" id="pic_gender_prospec-male" name="pic_gender_prospec" value="M" @if(old('pic_gender_prospec') != '' && old('pic_gender_prospec') == 'M') checked @endif>Male</label> 
					<label class="radio-inline"><input type="radio" id="pic_gender_prospec-female" name="pic_gender_prospec" value="F" @if(old('pic_gender_prospec') != '' && old('pic_gender_prospec') == 'F') checked @endif>Female</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_gender_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_position_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Position 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="pic_position_prospec" name="pic_position_prospec" class="form-control {{$errors->first('pic_position_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_position_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_position_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_phone_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Phone <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="pic_phone_prospec" name="pic_phone_prospec" class="form-control {{$errors->first('pic_phone_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_phone_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_phone_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_email_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Email <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="email" id="pic_email_prospec" name="pic_email_prospec" class="form-control {{$errors->first('pic_email_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_email_prospec') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_email_prospec') }}</li>
				</ul>
			</div>
		</div>


		<div class="ln_solid"></div>

		<div class="form-group">
			<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select class="form-control" name="activity">
					<option value="">Select Activity</option>
					@foreach($activity as $key => $list)
					<option value="{{ $key }}" {{ old('activity') == $key ? 'selected' : '' }}>{{ $list }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('activity') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
			</label>
			<div class="col-md-6 col-sm-6 col-xs-6">
				<input type="date" name="date_activity" class="form-control" value="{{ old('date_activity') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
				</ul>
			</div>
			<div class="col-md-3 col-sm-3 col-xs-6">
				<input type="time" name="time_activity" class="form-control" value="{{ old('time_activity') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.crm') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>


	</form>
	</div>

@endsection