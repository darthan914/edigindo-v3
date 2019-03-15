@extends('backend.layout.master')

@section('title')
	Edit Contract
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

		$("select[name=offer_id]").change(function(event) {
			$.post("{{ route('backend.contract.getOffer') }}",
		    {
		        offer_id: $(this).data('id'),
		    },
		    function(data){
		    	CKEDITOR.instances['note'].setData(data.note);
		        $('textarea[name=note]').val(data.note);
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
	
	@can('create-spk')
	<div id="generate-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.contract.generateSpk', $index->id) }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create SPK?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="spk_id-onundoDone" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Yes</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Edit Contract</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.contract.update', [$index->id]) }}" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-12">
					<div class="form-group">
						<label for="offer_id" class="control-label col-md-3 col-sm-3 col-xs-12">No Offer <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<select id="offer_id" name="offer_id" class="form-control {{$errors->first('offer_id') != '' ? 'parsley-error' : ''}}">
								<option value=""></option>
								@foreach($offer as $list)
								<option value="{{ $list->id }}" @if(old('offer_id', $index->offer_id) == $list->id) selected @endif>{{ $list->no_document }} - {{ $list->name }}</option>
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
							<input type="text" id="no_contract" name="no_contract" class="form-control {{$errors->first('no_contract') != '' ? 'parsley-error' : ''}}" value="{{ old('no_contract', $index->no_contract) }}">
							
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
					@endif

					<div class="form-group">
						<label for="date" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="date" name="date" class="form-control {{$errors->first('date') != '' ? 'parsley-error' : ''}}" value="{{ old('date', date('d F Y', strtotime($index->date)) ) }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('date') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="material" class="control-label col-md-3 col-sm-3 col-xs-12">Material <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="material" name="material" class="form-control {{$errors->first('material') != '' ? 'parsley-error' : ''}}" value="{{ old('material', $index->material) }}" placeholder="In Percent. e.g: 20">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('material') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="services" class="control-label col-md-3 col-sm-3 col-xs-12">Services <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="services" name="services" class="form-control {{$errors->first('services') != '' ? 'parsley-error' : ''}}" value="{{ old('services', $index->services) }}" placeholder="In Percent. e.g: 20 (Jasa)">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('services') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="director" class="control-label col-md-3 col-sm-3 col-xs-12">Director <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="director" name="director" class="form-control {{$errors->first('director') != '' ? 'parsley-error' : ''}}" value="{{ old('director', $index->director) }}" placeholder="">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('director') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="client" class="control-label col-md-3 col-sm-3 col-xs-12">Client <span class="required">*</span>
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<input type="text" id="client" name="client" class="form-control {{$errors->first('client') != '' ? 'parsley-error' : ''}}" value="{{ old('client', $index->client) }}" placeholder="">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('client') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note', $index->note) }}</textarea>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('note') }}</li>
							</ul>
						</div>
					</div>

					<div class="ln_solid"></div>
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
							{{ csrf_field() }}
							<a class="btn btn-primary" href="{{ route('backend.contract') }}">Cancel</a>
							@can('create-spk')
							<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#generate-spk">Create SPK</button>
							@endcan
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</div>
			</div>
				
		</div>
	</form>
	</div>

@endsection