@extends('backend.layout.master')

@section('title')
	Create Delivery
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAel9fAfMQ3xomX3v_iLWJSkNUE3TSkLI&libraries=places"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/geocomplete/1.7.0/jquery.geocomplete.min.js"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'detail' );
	$(function() {
		$('input[name=datetime_send]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name="datetime_send"]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY HH:mm'));
	    });

		$('select[name=search_spk]').select2({
			placeholder: "Search SPK",
			allowClear: true
		});
		$('select[name=task]').select2({
			placeholder: "Select Task",
			allowClear: true
		});
		$('select[name=search_company]').select2({
			placeholder: "Search Company",
			allowClear: true
		});
		$('select[name=search_brand]').select2({
			placeholder: "Search Brand",
			allowClear: true
		});
		$('select[name=search_address]').select2({
			placeholder: "Search Address",
			allowClear: true
		});
		$('select[name=search_pic]').select2({
			placeholder: "Search PIC",
			allowClear: true
		});

		$('select[name=city]').select2({
			placeholder: "Search City",
			allowClear: true
		});


		$(".btn-generate").click(function(){
        	$.post("{{ route('backend.spk.getSpk') }}",
	        {
	            sales_id: $('select[name=sales_id]').val(),
	            date: $('input[name=date]').val(),
	        },
	        function(data){
	        	// alert(data);
	            $('input[name=spk]').val(data);
	        });
	    });

		$('select[name=search_brand], select[name=search_address], select[name=search_pic]').prop("disabled", true);

		$(document).on('change','select[name=search_company]', function(){

			if($(this).val() == ''){
				$('select[name=search_brand], select[name=search_address], select[name=search_pic]').val('').trigger('change');
				$('select[name=search_brand], select[name=search_address], select[name=search_pic]').prop("disabled", true);
			}
			else{
				$('select[name=search_brand], select[name=search_address], select[name=search_pic]').prop("disabled", false);

				$.post("{{ route('backend.company.getBrand') }}",
		        {
		            company_id: $('select[name=search_company]').val(),
		        },
		        function(data){
		            $('select[name=search_brand]').empty();
					$.each(data, function(i, field){
						$('select[name=search_brand]').append("<option value='"+ field.brand.replace(/'/g, '"') +"'>"+ field.brand+"</option>");
					});
					$('select[name=search_brand]').val('').trigger('change');
		        });

				$.post("{{ route('backend.company.getAddress') }}",
		        {
		            company_id: $('select[name=search_company]').val(),
		        },
		        function(data){
		            $('select[name=search_address]').empty();
					$.each(data, function(i, field){
						$('select[name=search_address]').append("<option value='"+ field.address.replace(/'/g, '"') +"' data-address='"+field.address+"' data-latitude='"+field.latitude+"' data-longitude='"+field.longitude+"'>"+ field.address+"</option>");
					});
					$('select[name=search_address]').val('').trigger('change');
		        });

		        $.post("{{ route('backend.company.getPic') }}",
		        {
		            company_id: $('select[name=search_company]').val(),
		        },
		        function(data){
		            $('select[name=search_pic]').empty();
					$.each(data, function(i, field){
						$('select[name=search_pic]').append("<option value='"+ field.fullname.replace(/'/g, '"') +"' data-pic_phone='"+field.phone+"'>"+ field.fullname +" ("+field.nickname+")"+"</option>");
					});
					$('select[name=search_pic]').val('').trigger('change');
		        });
			}
		});

		$("textarea[name=address]").change(function(event) {
			$("input[name=autosearch_address]").geocomplete("find", $(this).val());
		});

		$("select[name=search_address]").change(function(event) {
			$("input[name=autosearch_address]").geocomplete("find", $(this).val());
		});

		$("input[name=autosearch_address]").geocomplete({
			map: "#map-location",
			markerOptions: {
				draggable: true
			},
			details: "form",
			detailsAttribute: "data-geo"
		}).bind("geocode:dragged", function(event, result){

			$("input[name=latitude]").val(result.lat());
			$("input[name=longitude]").val(result.lng());
		});

	});

	function autoSpk(elem)
	{
		document.getElementById('spk').value = elem.value.replace('"', "'");
	}

	function autoCompany(elem)
	{
		document.getElementById('company').value = elem.options[elem.selectedIndex].getAttribute('data-company').replace('"', "'");
	}

	function autoBrand(elem)
	{
		document.getElementById('brand').value = elem.value.replace('"', "'");
	}

	function autoAddress(elem)
	{
		document.getElementById('address').value = elem.value.replace('"', "'");
		document.getElementById('latitude').value = elem.options[elem.selectedIndex].getAttribute("data-latitude");
		document.getElementById('longitude').value = elem.options[elem.selectedIndex].getAttribute("data-longitude");
	}

	function autoPic(elem)
	{
		document.getElementById('pic_name').value = elem.value.replace('"', "'");
		document.getElementById('pic_phone').value = elem.options[elem.selectedIndex].getAttribute('data-pic_phone');
	}
</script>

@endsection

@section('css')
<style type="text/css">
	#map-location{
		width: : 100%;
		height: 15em;
	}

	.pac-container {
		z-index: 1051 !important;
	}
</style>
@endsection

@section('content')

	<h1>Create Delivery</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.beta.postDelivery') }}" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-6">
					<div class="form-group">
						<label for="spk" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select class="form-control" name="search_spk" onchange="autoSpk(this)">
								<option value=""></option>
								@foreach ($spk as $list)
									<option value="{{ $list->spk }}">{{ $list->spk }} - {{ $list->name }}</option>
								@endforeach
							</select>
							<input type="text" id="spk" name="spk" class="form-control {{$errors->first('spk') != '' ? 'parsley-error' : ''}}" value="{{ old('spk') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('spk') }}</li>
							</ul>
						</div>
					</div>

					{{-- <div class="form-group">
						<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Request <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', Auth::user()->fullname) }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('name') }}</li>
							</ul>
						</div>
					</div> --}}

					{{-- <div class="form-group">
						<label for="project" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="project" name="project" class="form-control {{$errors->first('project') != '' ? 'parsley-error' : ''}}" value="{{ old('project') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('project') }}</li>
							</ul>
						</div>
					</div> --}}

					<div class="form-group">
						<label for="datetime_send" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Send <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="datetime_send" name="datetime_send" class="form-control {{$errors->first('datetime_send') != '' ? 'parsley-error' : ''}}" value="{{ old('datetime_send') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('datetime_send') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="get_from" class="control-label col-md-3 col-sm-3 col-xs-12">Pickup Address <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="get_from" name="get_from" class="form-control {{$errors->first('get_from') != '' ? 'parsley-error' : ''}}" value="{{ old('get_from') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('get_from') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="via" class="control-label col-md-3 col-sm-3 col-xs-12">Via <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="radio-inline"><input type="radio" name="via" value="Supir" @if(old('via') == "Supir") checked @endif>Supir</label>
							<label class="radio-inline"><input type="radio" name="via" value="Kurir" @if(old('via') == "Kurir") checked @endif>Kurir</label>
							
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('via') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="task" class="control-label col-md-3 col-sm-3 col-xs-12">Task 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="task" name="task" class="form-control">
								<option value=""></option>
								<option value="SEND|PACKAGE" @if(old('task') == "SEND|PACKAGE") selected @endif>Send Packages</option>
								<option value="SEND|DOCUMENT" @if(old('task') == "SEND|DOCUMENT") selected @endif>Send Documents</option>
								<option value="SEND|SERVICE" @if(old('task') == "SEND|SERVICE") selected @endif>Send Services</option>
								<option value="PICKUP|PACKAGE" @if(old('task') == "PICKUP|PACKAGE") selected @endif>Pickup Packages</option>
								<option value="PICKUP|DOCUMENT" @if(old('task') == "PICKUP|DOCUMENT") selected @endif>Pickup Documents</option>
								<option value="BUY" @if(old('task') == "BUY") selected @endif>Buy Stuff</option>
								<option value="OTHER" @if(old('task') == "OTHER") selected @endif>Other</option>
							</select>

							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('task') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<textarea id="detail" name="detail" class="form-control {{$errors->first('detail') != '' ? 'parsley-error' : ''}}">{{ old('detail', '
								<ol>
									<li>
										Stuff (Quantity/Size)
									</li>
								</ol>') }}</textarea>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('detail') }}</li>
							</ul>
						</div>
					</div>
			</div>
			<div class="col-md-6">
					

					<div class="form-group">
						<label for="search_company" class="control-label col-md-3 col-sm-3 col-xs-12">Company 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="search_company" name="search_company" class="form-control" onchange="autoCompany(this)">
								<option value=""></option>
								@foreach($company as $list)
								<option value="{{ $list->id }}" data-company="{{ $list->name }}">{{ $list->name }}</option>
								@endforeach
							</select>
							<input type="text" id="company" name="company" class="form-control {{$errors->first('company') != '' ? 'parsley-error' : ''}}" value="{{ old('company') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('company') }}</li>
							</ul>
						</div>
					</div>

					

					<div class="form-group">
						<label for="search_pic" class="control-label col-md-3 col-sm-3 col-xs-12">Name PIC 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="search_pic" name="search_pic" class="form-control {{$errors->first('search_pic') != '' ? 'parsley-error' : ''}}" value="{{ old('search_pic') }}" onchange="autoPic(this)">
								<option value="" data-pic_phone=""></option>
							</select>
							<input type="text" id="pic_name" name="pic_name" class="form-control {{$errors->first('pic_name') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_name') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('pic_name') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="pic_phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone PIC 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="pic_phone" name="pic_phone" class="form-control {{$errors->first('pic_phone') != '' ? 'parsley-error' : ''}}" value="{{ old('pic_phone') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('pic_phone') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="city" class="control-label col-md-3 col-sm-3 col-xs-12">City <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="city" name="city" class="form-control {{$errors->first('city') != '' ? 'parsley-error' : ''}}">
								<option value=""></option>
								@foreach ($city as $list)
									<option value="{{ $list }}" @if(old('city') == $list) selected @endif>{{ $list }}</option>
								@endforeach
							</select>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('city') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="search_address" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="search_address" name="search_address" class="form-control {{$errors->first('search_address') != '' ? 'parsley-error' : ''}}" onchange="autoAddress(this)">
								<option value=""></option>
							</select>
							<textarea id="address" name="address" class="form-control {{$errors->first('address') != '' ? 'parsley-error' : ''}}" >{{ old('address') }}</textarea>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('address') }}</li>
							</ul>
						</div>
					</div>

					
					<div class="form-group">
						<label for="marker" class="control-label col-md-3 col-sm-3 col-xs-12">Marker
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="update_autosearch_address" name="autosearch_address" class="form-control {{$errors->first('autosearch_address') != '' ? 'parsley-error' : ''}}" autocomplete="off" value="{{ old('autosearch_address') }}">
							<div id="map-location"></div>
						</div>
					</div>

					{{-- <div class="form-group">
						<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">PPn 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="checkbox-inline"><input type="checkbox" value="10" name="ppn" @if(old('ppn') == "10") checked @endif>PPn 10%</label>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('ppn') }}</li>
							</ul>
						</div>
					</div> --}}

					
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
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
						<input type="hidden" id="latitude" name="latitude" data-geo="lat" value="{{ old('latitude') }}">
						<input type="hidden" id="longitude" name="longitude" data-geo="lng" value="{{ old('longitude') }}">

						<a class="btn btn-primary" href="{{ route('backend.delivery') }}">Cancel</a>
						<button type="submit" class="btn btn-success">Submit</button>
					</div>
				</div>
			</div>
				
		</div>
	</form>
	</div>

@endsection