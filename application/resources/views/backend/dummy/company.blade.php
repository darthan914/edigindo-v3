@extends('backend.layout.master')

@section('title')
	Create Dummy Company
@endsection

@section('script')

@endsection

@section('content')

	<h1>Create Dummy Company</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.dummy.createDummyCompany') }}" method="post" enctype="multipart/form-data">

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
				<label for="loop_pic" class="control-label col-md-3 col-sm-3 col-xs-12">Rand Loop PIC <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="loop_pic" name="loop_pic" class="form-control {{$errors->first('loop_pic') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 5; $i++)
							<option value="{{ $i + 1 }}" @if(old('loop_pic') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('loop_pic') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="loop_brand" class="control-label col-md-3 col-sm-3 col-xs-12">Rand Loop Brand <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="loop_brand" name="loop_brand" class="form-control {{$errors->first('loop_brand') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 5; $i++)
							<option value="{{ $i + 1 }}" @if(old('loop_brand') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('loop_brand') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="loop_address" class="control-label col-md-3 col-sm-3 col-xs-12">Rand Loop Address <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="loop_address" name="loop_address" class="form-control {{$errors->first('loop_address') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 5; $i++)
							<option value="{{ $i + 1 }}" @if(old('loop_address') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('loop_address') }}</li>
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