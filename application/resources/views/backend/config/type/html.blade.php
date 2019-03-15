@extends('backend.layout.master')

@section('title')
	{{ $index->for }}
@endsection

@section('script')
	<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
	<script type="text/javascript">
		CKEDITOR.replace( 'value' );
	</script>
@endsection

@section('content')

	<h1>{{ $index->for }}</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.config.update', $index->id) }}" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Value <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<textarea id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}">{{ old('value', $index->value) }}</textarea>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('value') }}</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>

			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<input type="hidden" name="id" value="{{ $index->id }}">
					<a class="btn btn-primary" href="{{ route('backend.config') }}">back</a>
					<button type="submit" class="btn btn-success">Submit</button>
				</div>
			</div>

		</form>
	</div>
	

@endsection