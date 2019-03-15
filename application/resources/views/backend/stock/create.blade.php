@extends('backend.layout.master')

@section('title')
	Create Stock
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('select[name=stock_places_id]').select2({
			placeholder: "Pilih Lokasi",
			allowClear: true
		});

	});
</script>

@endsection

@section('content')

	<h1>Create Stock</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.stock.store') }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="item" class="control-label col-md-3 col-sm-3 col-xs-12">Barang <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="item" name="item" class="form-control {{$errors->first('item') != '' ? 'parsley-error' : ''}}" value="{{ old('item') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('item') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="stock_places_id" class="control-label col-md-3 col-sm-3 col-xs-12">Lokasi
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="stock_places_id" name="stock_places_id" class="form-control {{$errors->first('stock_places_id') != '' ? 'parsley-error' : ''}}">
					<option value=""></option>
					@foreach($stock_place as $list)
					<option value="{{ $list->id }}" @if(old('stock_places_id') == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('stock_places_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="quantity" class="control-label col-md-3 col-sm-3 col-xs-12">Jumlah <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="quantity" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('quantity') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Gambar
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
				<a class="btn btn-primary" href="{{ route('backend.stock') }}">Batal</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection