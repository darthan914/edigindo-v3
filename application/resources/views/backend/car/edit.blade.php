@extends('backend.layout.master')

@section('title')
	Edit Car
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=stnk], input[name=kir1], input[name=kir2], input[name=gps], input[name=insurance], input[name=date_km]').daterangepicker({
		    singleDatePicker: true,
		    timePicker: true,
		    timePicker24Hour: true,
		    autoUpdateInput: false,
		    locale: {
		        cancelLabel: 'Clear'
		    }
		});

		$('input[name=stnk], input[name=kir1], input[name=kir2], input[name=gps], input[name=insurance], input[name=date_km]').on('apply.daterangepicker', function(ev, picker) {
	        $(this).val(picker.startDate.format('DD MMMM YYYY'));
	    });

	    $('input[name=stnk], input[name=kir1], input[name=kir2], input[name=gps], input[name=insurance], input[name=date_km]').on('cancel.daterangepicker', function(ev, picker) {
		    $(this).val('');
		});
	});
</script>

@endsection

@section('content')

	<h1>Edit Car</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.car.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="num_car" class="control-label col-md-3 col-sm-3 col-xs-12">Name Car <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="num_car" name="num_car" class="form-control {{$errors->first('num_car') != '' ? 'parsley-error' : ''}}" value="{{ old('num_car', $index->num_car) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('num_car') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="stnk" class="control-label col-md-3 col-sm-3 col-xs-12">STNK <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="stnk" name="stnk" class="form-control {{$errors->first('stnk') != '' ? 'parsley-error' : ''}}" value="{{ old('stnk', $index->stnk ? date('d F Y', strtotime($index->stnk)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('stnk') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="kir1" class="control-label col-md-3 col-sm-3 col-xs-12">KIR 1 <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="kir1" name="kir1" class="form-control {{$errors->first('kir1') != '' ? 'parsley-error' : ''}}" value="{{ old('kir1', $index->kir1 ? date('d F Y', strtotime($index->kir1)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('kir1') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="kir2" class="control-label col-md-3 col-sm-3 col-xs-12">KIR 2 <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="kir2" name="kir2" class="form-control {{$errors->first('kir2') != '' ? 'parsley-error' : ''}}" value="{{ old('kir2', $index->kir2 ? date('d F Y', strtotime($index->kir2)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('kir2') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="gps" class="control-label col-md-3 col-sm-3 col-xs-12">GPS <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="gps" name="gps" class="form-control {{$errors->first('gps') != '' ? 'parsley-error' : ''}}" value="{{ old('gps', $index->gps ? date('d F Y', strtotime($index->gps)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('gps') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="insurance" class="control-label col-md-3 col-sm-3 col-xs-12">Insurance <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="insurance" name="insurance" class="form-control {{$errors->first('insurance') != '' ? 'parsley-error' : ''}}" value="{{ old('insurance', $index->insurance ? date('d F Y', strtotime($index->insurance)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('insurance') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="date_km" class="control-label col-md-3 col-sm-3 col-xs-12">Date Note KM <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="date_km" name="date_km" class="form-control {{$errors->first('date_km') != '' ? 'parsley-error' : ''}}" value="{{ old('date_km', $index->date_km ? date('d F Y', strtotime($index->date_km)) : '' ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date_km') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="weekly_km" class="control-label col-md-3 col-sm-3 col-xs-12">Weekly KM <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="weekly_km" name="weekly_km" class="form-control {{$errors->first('weekly_km') != '' ? 'parsley-error' : ''}}" value="{{ old('weekly_km', $index->weekly_km) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('weekly_km') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="paper_km" class="control-label col-md-3 col-sm-3 col-xs-12">Paper KM <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="paper_km" name="paper_km" class="form-control {{$errors->first('paper_km') != '' ? 'parsley-error' : ''}}" value="{{ old('paper_km', $index->paper_km) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('paper_km') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.car') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection