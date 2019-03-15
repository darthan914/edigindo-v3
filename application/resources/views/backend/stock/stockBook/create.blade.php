@extends('backend.layout.master')

@section('title')
	Create Stock Book
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('select[name=stock_id]').select2({
			placeholder: "Pilih Barang",
			allowClear: true
		});

		$('select[name=need]').select2({
			placeholder: "Keperluan",
			allowClear: true
		});
	});
</script>

@endsection

@section('content')

	<h1>Create Stock Book</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.stock.storeStockBook') }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="stock_id" class="control-label col-md-3 col-sm-3 col-xs-12">Barang <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="stock_id" name="stock_id" class="form-control {{$errors->first('stock_id') != '' ? 'parsley-error' : ''}}" value="{{ old('stock_id') }}">
					<option value=""></option>
					@foreach($stock as $list)
					<option value="{{ $list->id }}" @if(old('stock_id') == $list->id) selected @endif>
						@if($list->photo)
						<img src="{{asset($list->photo)}}" style="width:2em;height:2em;object-fit: contain;"></img>
						@endif
						{{ $list->item }}
					</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('stock_id') }}</li>
				</ul>
			</div>
		</div>


		<div class="form-group">
			<label for="name_borrow" class="control-label col-md-3 col-sm-3 col-xs-12">Nama Peminjam <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name_borrow" name="name_borrow" class="form-control {{$errors->first('name_borrow') != '' ? 'parsley-error' : ''}}" value="{{ old('name_borrow') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name_borrow') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="need" class="control-label col-md-3 col-sm-3 col-xs-12">Keperluan
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="need" name="need" class="form-control {{$errors->first('need') != '' ? 'parsley-error' : ''}}">
					<option value=""></option>
					@foreach($need as $key => $list)
					<option value="{{ $key }}" @if(old('need') == $key) selected @endif>{{ $list }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('need') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="quantity_borrow" class="control-label col-md-3 col-sm-3 col-xs-12">Jumlah dipinjam<span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="number" id="quantity_borrow" name="quantity_borrow" class="form-control {{$errors->first('quantity_borrow') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity_borrow') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('quantity_borrow') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="date_borrow" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal pinjam <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="date" id="date_borrow" name="date_borrow" class="form-control {{$errors->first('date_borrow') != '' ? 'parsley-error' : ''}}" value="{{ old('date_borrow') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date_borrow') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="deadline_borrow" class="control-label col-md-3 col-sm-3 col-xs-12">Sampai <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="date" id="deadline_borrow" name="deadline_borrow" class="form-control {{$errors->first('deadline_borrow') != '' ? 'parsley-error' : ''}}" value="{{ old('deadline_borrow') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('deadline_borrow') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Catatan
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea type="text" id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.stock.stockBook') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection