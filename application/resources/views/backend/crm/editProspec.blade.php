@extends('backend.layout.master')

@section('title')
	Edit CRM
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {

		$('select[name=pic_id]').select2({
			placeholder: "Select / New PIC",
			allowClear: true
		});

		$('select[name=sales_id]').select2({
			placeholder: "Select Sales",
			allowClear: true
		});


		var old_pic = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		$('select[name=brand_id], select[name=address_id], select[name=pic_id]').prop("disabled", true);

		if($('select[name=company_id]').val() == ''){
			$('select[name=pic_id]').val('').trigger('change');
			$('select[name=pic_id]').prop("disabled", true);
		}
		else{
			$('select[name=pic_id]').prop("disabled", false);

	        $.post("{{ route('backend.company.getPic') }}",
	        {
	            company_id: {{ $index->company_id ?? 0 }},
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


		$(document).on('change','select[name=pic_id]', function(){
			if($(this).val() == 0){
				$('.new-pic').slideDown();
			}
			else
			{
				$('.new-pic').slideUp();
			}
		});

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

	<h1>Edit CRM</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.crm.updateProspec', ["id" => $index->id ]) }}" method="post" enctype="multipart/form-data">

		

		<div class="form-group">
			<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}}">
					<option value=""></option>
					@foreach($sales as $list)
					<option value="{{ $list->id }}" @if(old('sales_id', $index->sales_id) == $list->id) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>


		<div class="form-group">
			<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">Database PIC <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}}">
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
				<input type="text" id="pic_fullname_prospec" name="pic_fullname_prospec" class="form-control {{$errors->first('pic_fullname_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_fullname_prospec', $index->pic_fullname_prospec) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_fullname_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_gender_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Gender <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="radio-inline"><input type="radio" id="pic_gender_prospec-male" name="pic_gender_prospec" value="M" @if(old('pic_gender_prospec', $index->pic_gender_prospec) != '' && old('pic_gender_prospec', $index->pic_gender_prospec) == 'M') checked @endif>Male</label> 
					<label class="radio-inline"><input type="radio" id="pic_gender_prospec-female" name="pic_gender_prospec" value="F" @if(old('pic_gender_prospec', $index->pic_gender_prospec) != '' && old('pic_gender_prospec', $index->pic_gender_prospec) == 'F') checked @endif>Female</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_gender_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_position_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Position 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="pic_position_prospec" name="pic_position_prospec" class="form-control {{$errors->first('pic_position_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_position_prospec', $index->pic_position_prospec) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_position_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_phone_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Phone <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="pic_phone_prospec" name="pic_phone_prospec" class="form-control {{$errors->first('pic_phone_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_phone_prospec', $index->pic_phone_prospec) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_phone_prospec') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group new-pic">
			<label for="pic_email_prospec" class="control-label col-md-3 col-sm-3 col-xs-12">PIC Email 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="email" id="pic_email_prospec" name="pic_email_prospec" class="form-control {{$errors->first('pic_email_prospec') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_email_prospec', $index->pic_email_prospec) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('pic_email_prospec') }}</li>
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