@extends('backend.layout.master')

@section('title')
	Create Dummy Users
@endsection

@section('script')

@endsection

@section('content')

	<h1>Create Dummy Users</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.dummy.createDummySupplier') }}" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="loop" class="control-label col-md-3 col-sm-3 col-xs-12">Loop <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<select id="loop" name="loop" class="form-control {{$errors->first('loop') != '' ? 'parsley-error' : ''}}">
						@for ($i = 0; $i < 10; $i++)
							<option value="{{ $i + 1 }}" @if(old('loop') == $i + 1) selected @endif>{{ $i + 1 }}</option>
						@endfor
					</select>
					<input type="hidden" name="position" id="position" value="{{ old('loop') }}">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('loop') }}</li>
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