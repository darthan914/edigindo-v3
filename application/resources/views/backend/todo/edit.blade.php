@extends('backend.layout.master')

@section('title')
	Edit To Do
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	
	$(function() {
		$('input[name=date]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

		$('select[name=company_id]').select2({
			placeholder: "Search Company",
			allowClear: true
		});
		$('select[name=search_brand]').select2({
			placeholder: "Search Brand",
			allowClear: true
		});

		$('select[name=search_brand]').prop("disabled", true);

		$(document).on('change','select[name=company_id]', function(){

			if($(this).val() == ''){
				$('select[name=search_brand]').val('').trigger('change');
				$('select[name=search_brand]').prop("disabled", true);
			}
			else{
				$('select[name=search_brand], select[name=search_address], select[name=search_pic]').prop("disabled", false);

				$.post("{{ route('backend.company.getBrand') }}",
		        {
		            company_id: $('select[name=company_id]').val(),
		        },
		        function(data){
		            $('select[name=search_brand]').empty();
					$.each(data, function(i, field){
						$('select[name=search_brand]').append("<option value='"+ field.brand.replace(/'/g, '"') +"'>"+ field.brand+"</option>");
					});
					$('select[name=search_brand]').val('').trigger('change');
		        });
			}
		});
	});

	function autoCompany(elem)
	{
		document.getElementById('company').value = elem.options[elem.selectedIndex].getAttribute('data-company').replace('"', "'");
	}

	function autoBrand(elem)
	{
		document.getElementById('brand').value = elem.value.replace('"', "'");
	}
</script>

@endsection

@section('content')

	<h1>Edit To Do</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.todo.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="company_id" name="company_id" class="form-control" onchange="autoCompany(this)">
					<option value="0">Prospect</option>
					@foreach($company as $list)
					<option value="{{ $list->id }}" data-company="{{ $list->name }}">{{ $list->name }}</option>
					@endforeach
				</select>
				<input type="text" id="company" name="company" class="form-control {{$errors->first('company') != '' ? 'parsley-error' : ''}}" value="{{ old('company', $index->company) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="search_brand" class="control-label col-md-3 col-sm-3 col-xs-12">Brand
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="search_brand" name="search_brand" class="form-control {{$errors->first('search_brand') != '' ? 'parsley-error' : ''}}" onchange="autoBrand(this)">
					<option value=""></option>
				</select>
				<input type="text" id="brand" name="brand" class="form-control {{$errors->first('brand') != '' ? 'parsley-error' : ''}}" value="{{ old('brand', $index->brand) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('brand') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="event" class="control-label col-md-3 col-sm-3 col-xs-12">Event <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="event" name="event" class="form-control {{$errors->first('event') != '' ? 'parsley-error' : ''}}" value="{{ old('event', $index->event) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('event') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="date_todo" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="date_todo" name="date_todo" class="form-control {{$errors->first('date_todo') != '' ? 'parsley-error' : ''}}" value="{{ old('date_todo', date('d F Y H:i', strtotime($index->date_todo)) ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date_todo') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.todo') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection