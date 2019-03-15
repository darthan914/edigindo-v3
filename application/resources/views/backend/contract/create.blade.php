@extends('backend.layout.master')

@section('title')
	Create Contract
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'note' );
	$(function() {
		$('input[name=date]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$('select[name=sales_id]').select2({
			placeholder: "Select Sales",
			allowClear: true
		});

		$('select[name=offer_id]').select2({
			placeholder: "Select Contract",
			allowClear: true
		});

		$(".btn-generate").click(function(){
        	$.post("{{ route('backend.contract.getContract') }}",
	        {
	            sales_id: $('select[name=sales_id]').val(),
	            offer_id: $('select[name=offer_id]').val(),
	            date: $('input[name=date]').val(),
	        },
	        function(data){
	        	if(data.error)
	        	{
	        		alert(data.error);
	        	}
	        	else
	        	{
	        		$('input[name=no_contract]').val(data);
	        	}
	            
	        });
	    });

		$("select[name=offer_id]").change(function(event) {
			$.post("{{ route('backend.contract.getOffer') }}",
		    {
		        id: $(this).val(),
		    },
		    function(data){
		    	CKEDITOR.instances['note'].setData(data.note);
		        $('textarea[name=note]').val(data.note);
		        $('select[name=sales_id]').val(data.sales_id).trigger('change');
		    });
		});

		$("input[name=material]").change(function(event) {
			$("input[name=services]").val(100 - $(this).val());
		});

		$("input[name=services]").change(function(event) {
			$("input[name=material]").val(100 - $(this).val());
		});

	    
	});

</script>

@endsection

@section('content')

	<h1>Create Contract</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.contract.store') }}" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-12">
					<div class="form-group">
						<label for="offer_id" class="control-label col-md-3 col-sm-3 col-xs-12">No Offer <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="offer_id" name="offer_id" class="form-control {{$errors->first('offer_id') != '' ? 'parsley-error' : ''}}" value="{{ old('offer_id') }}">
								<option value=""></option>
								@foreach($offer as $list)
								<option value="{{ $list->id }}" @if(old('offer_id') == $list->id) selected @endif>{{ $list->no_document }} - {{ $list->name }}</option>
								@endforeach
							</select>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('offer_id') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="no_contract" class="control-label col-md-3 col-sm-3 col-xs-12">No Contract <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<div class="input-group">
								<input type="text" id="no_contract" name="no_contract" class="form-control {{$errors->first('no_contract') != '' ? 'parsley-error' : ''}}" value="{{ old('no_contract') }}">
								<span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-generate">Regenerate</button>
                                </span>
							</div>
							
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('no_contract') }}</li>
							</ul>
						</div>
					</div>

					

					@if ((!in_array(Auth::user()->position, explode(', ', $sales_position->value)) && !in_array(Auth::id(), explode(', ', $sales_user->value))) || in_array(Auth::user()->position, explode(', ', $super_admin_position->value)) || in_array(Auth::id(), explode(', ', $super_admin_user->value)))
					<div class="form-group">
						<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}}" value="{{ old('sales_id') }}">
								<option value=""></option>
								@foreach($sales as $list)
								<option value="{{ $list->id }}" @if(old('sales_id') == $list->id) selected @endif>{{ $list->fullname }}</option>
								@endforeach
							</select>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
							</ul>
						</div>
					</div>
					@endif

					<div class="form-group">
						<label for="date" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="date" name="date" class="form-control {{$errors->first('date') != '' ? 'parsley-error' : ''}}" value="{{ old('date') }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('date') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="material" class="control-label col-md-3 col-sm-3 col-xs-12">Material <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="material" name="material" class="form-control {{$errors->first('material') != '' ? 'parsley-error' : ''}}" value="{{ old('material') }}" placeholder="In Percent. e.g: 20">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('material') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="services" class="control-label col-md-3 col-sm-3 col-xs-12">Services <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="services" name="services" class="form-control {{$errors->first('services') != '' ? 'parsley-error' : ''}}" value="{{ old('services') }}" placeholder="In Percent. e.g: 20 (Jasa)">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('services') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="director" class="control-label col-md-3 col-sm-3 col-xs-12">Director <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="director" name="director" class="form-control {{$errors->first('director') != '' ? 'parsley-error' : ''}}" value="{{ old('director') }}" placeholder="">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('director') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="client" class="control-label col-md-3 col-sm-3 col-xs-12">Client <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="client" name="client" class="form-control {{$errors->first('client') != '' ? 'parsley-error' : ''}}" value="{{ old('client') }}" placeholder="">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('client') }}</li>
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

					@can('create-spk')
					<div class="form-group">
						<label for="create_spk" class="control-label col-md-3 col-sm-3 col-xs-12"> 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="checkbox-inline"><input type="checkbox" value="1" name="create_spk" @if(old('note') == 1) checked @endif>Create SPK</label>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('create_spk') }}</li>
							</ul>
						</div>
					</div>
					@endcan

					<div class="ln_solid"></div>
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
							{{ csrf_field() }}
							<a class="btn btn-primary" href="{{ route('backend.contract') }}">Cancel</a>
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</div>
			</div>
				
		</div>
	</form>
	</div>

@endsection