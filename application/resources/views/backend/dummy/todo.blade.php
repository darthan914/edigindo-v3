@extends('backend.layout.master')

@section('title')
	Create Dummy Todo
@endsection

@section('script')

@endsection

@section('content')

	<h1>Create Dummy Todo</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.dummy.createDummyTodo') }}" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="loop" class="control-label col-md-3 col-sm-3 col-xs-12">Loop <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="loop" name="loop" class="form-control {{$errors->first('loop') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 10; $i++)
							<option value="{{ $i + 1 }}" @if(old('loop') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('loop') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="rand_month" class="control-label col-md-3 col-sm-3 col-xs-12">Rand Month <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="rand_month" name="rand_month" class="form-control {{$errors->first('rand_month') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 12; $i++)
							<option value="{{ $i + 1 }}" @if(old('rand_month') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('rand_month') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="rand_year" class="control-label col-md-3 col-sm-3 col-xs-12">Rand Year <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="rand_year" name="rand_year" class="form-control {{$errors->first('rand_year') != '' ? 'parsley-error' : ''}}">
						@for ($i = 2014; $i < date('Y'); $i++)
							<option value="{{ $i + 1 }}" @if(old('rand_year') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('rand_year') }}</li>
					</ul>
				</div>
			</div>


			<div class="ln_solid"></div>

			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<button type="submit" class="btn btn-success">Submit</button>
				</div>
			</div>

		</form>
	</div>
	

@endsection