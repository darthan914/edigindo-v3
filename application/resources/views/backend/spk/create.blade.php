@extends('backend.layout.master')

@section('title')
	Create SPK
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=date_spk]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$(".btn-generate").click(function(){
        	$.post("{{ route('backend.spk.getSpk') }}",
	        {
	            sales_id: $('select[name=sales_id]').val(),
	            date_spk: $('input[name=date_spk]').val(),
	        },
	        function(data){
	            $('input[name=no_spk]').val(data);
	        });
	    });

		$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);

		old_brand = {{ old('brand_id') != '' ? old('brand_id') : 0 }};
		old_address = "{{ old('address') != '' ? old('address') : '' }}";
		old_pic = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		if($('select[name=company_id]').val() == ''){
			$('select[name=brand_id], select[name=address], select[name=pic_id]').val('').trigger('change');
			$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);
		}
		else{
			$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", false);

			$.post("{{ route('backend.company.getDetail') }}",
			{
				id : $('select[name=company_id]').val(),
			},
	        function(data){
	            $('select[name=brand_id]').empty();
				$.each(data.brands, function(i, field){
					if (old_brand == field.id) 
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"' selected>"+ field.name+"</option>");
					}
					else
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.name+"</option>");
					}
				});
				$('select[name=brand_id]').val(old_brand).trigger('change');

	            $('select[name=address]').empty();
				$.each(data.addresses, function(i, field){
					if (old_address == field.address) 
					{
						$('select[name=address]').append("<option value='"+ field.address +"' selected>"+ field.address+"</option>");
					}
					else
					{
						$('select[name=address]').append("<option value='"+ field.address +"'>"+ field.address+"</option>");
					}
				});
				$('select[name=address]').val(old_address).trigger('change');

	            $('select[name=pic_id]').empty();
				$.each(data.pic, function(i, field){
					if (old_pic == field.id) 
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"' selected>"+ field.first_name +" "+field.last_name+"</option>");
					}
					else
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"'>"+ field.first_name +" "+field.last_name+"</option>");
					}
				});
				$('select[name=pic_id]').val(old_pic).trigger('change');
	        });
		}
		
		$(document).on('change','select[name=company_id]', function(){

			$('input[name=address]').val('');
			$('input[name=additional_phone]').val('');

			if($(this).val() == ''){
				$('select[name=brand_id], select[name=address], select[name=pic_id]').val('').trigger('change');
				$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);
			}
			else{
				$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", false);

				$.post("{{ route('backend.company.getDetail') }}",
				{
					id : $(this).val(),
				},
		        function(data){
		            $('select[name=brand_id]').empty();
					$.each(data.brands, function(i, field){
						console.log(field.id)
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.name+"</option>");
					});
					$('select[name=brand_id]').val('').trigger('change');

		            $('select[name=address]').empty();
					$.each(data.addresses, function(i, field){
						$('select[name=address]').append("<option value='"+ field.address +"'>"+ field.address+"</option>");
					});
					$('select[name=address]').val('').trigger('change');

		            $('select[name=pic_id]').empty();
					$.each(data["pic"], function(i, field){
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"'>"+ field.first_name +" "+field.last_name+"</option>");
					});
					$('select[name=pic_id]').val('').trigger('change');
		        });
			}
		});

		$('select[name=address]').on("select2:unselect", function (e) { $('input[name=address]').val(''); });
		$('select[name=pic_id]').on("select2:unselect", function (e) { $('input[name=additional_phone]').val(''); });
	});
</script>

@endsection

@section('content')

	<h1>Create SPK</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.spk.store') }}" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="no_spk" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<div class="input-group">
							<input type="text" id="no_spk" name="no_spk" class="form-control {{$errors->first('no_spk') != '' ? 'parsley-error' : ''}}" value="{{ old('no_spk', $spk) }}">
							<span class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-generate">Regenerate</button>
                            </span>
						</div>
						
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('no_spk') }}</li>
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
					<label for="main_division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Main Division <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="main_division_id" name="main_division_id" class="form-control {{$errors->first('main_division_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Division">
							<option value=""></option>
							@foreach($division as $list)
							<option value="{{ $list->id }}" @if(old('main_division_id') == $list->id) selected @endif>{{ $list->name }}</option>
							@endforeach
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('main_division_id') }}</li>
						</ul>
					</div>
				</div>


				<div class="form-group">
					<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Sales">
							<option value="{{ (in_array(Auth::id(), getConfigValue('sales_user', true)) || in_array(Auth::user()->position_id, getConfigValue('sales_position', true)) ? Auth::id() : '') }}">Select Sales</option>
							@foreach($sales as $list)
							<option value="{{ $list->id }}" @if(old('sales_id') == $list->id) selected @endif>{{ $list->fullname }}</option>
							@endforeach
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="date_spk" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="date_spk" name="date_spk" class="form-control {{$errors->first('date_spk') != '' ? 'parsley-error' : ''}}" value="{{ old('date_spk') }}">
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('date_spk') }}</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Company">
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
					<label for="brand_id" class="control-label col-md-3 col-sm-3 col-xs-12">Brand
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="brand_id" name="brand_id" class="form-control {{$errors->first('brand_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Brand" data-allow-clear="true">
							<option value=""></option>
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('brand_id') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="address" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="address" name="address" class="form-control {{$errors->first('address') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Address">
							<option value=""></option>
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('address') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">PIC <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select PIC" onchange="document.getElementById('additional_phone').value = this.options[this.selectedIndex].getAttribute('data-additional_phone');">
							<option value="" data-additional_phone=""></option>
						</select>
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('pic_id') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="additional_phone" class="control-label col-md-3 col-sm-3 col-xs-12">Additional Phone 
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="additional_phone" name="additional_phone" class="form-control {{$errors->first('additional_phone') != '' ? 'parsley-error' : ''}}" value="{{ old('additional_phone') }}">
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('additional_phone') }}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
					<div class="form-group">
						<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">PPn 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="checkbox-inline"><input type="checkbox" value="10" name="ppn" checked>PPn 10%</label>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('ppn') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="do_transaction" class="control-label col-md-3 col-sm-3 col-xs-12" title="Tanda bisa melakukan transaksi sebelum proyek selesai">Do Transaction 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="checkbox-inline" title="Tanda bisa melakukan transaksi sebelum proyek selesai"><input type="checkbox" name="do_transaction"  @if(old('do_transaction')) checked @endif>Yes</label>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('do_transaction') }}</li>
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
							<a class="btn btn-primary" href="{{ route('backend.spk') }}">Cancel</a>
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</div>
			</div>
				
		</div>
	</form>
	</div>

@endsection