@extends('backend.layout.master')

@section('title')
	Edit Access
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$(".check-all").click(function(){
			if ($(this).is(':checked'))
			{
				$('.' + $(this).attr('data-target')).prop('checked', true);
			}
			else
			{
				$('.' + $(this).attr('data-target')).prop('checked', false);
			}
		});
	});
</script>

@endsection

@section('content')

	<h1>Grant</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.user.accessUpdate', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">

		<h2>Grant</h2>

		@foreach($key as $list)
			<div class="form-group">
				<label class="control-label checkbox-inline col-md-3 col-sm-3 col-xs-12">
					<input type="checkbox" data-target="grant-{{ $list['id'] }}" class="check-all"> Grant {{ $list['name'] }}
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					@foreach($list['data'] as $list2)
					<label class="checkbox-inline"><input type="checkbox" name="grant[]" class="grant-{{ $list['id'] }}" value="{{ $list2['value'] }}" @if(in_array($list2['value'], old('grant', explode(', ', $index->grant)))) checked @endif>{{ $list2['name'] }}</label>
					@endforeach
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('grant') }}</li>
					</ul>
				</div>
			</div>
		@endforeach

		<div class="ln_solid"></div>

		<h2>Denied</h2>

		@foreach($key as $list)
			<div class="form-group">
				<label class="control-label checkbox-inline col-md-3 col-sm-3 col-xs-12">
					<input type="checkbox" data-target="denied-{{ $list['id'] }}" class="check-all"> Denied {{ $list['name'] }}
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					@foreach($list['data'] as $list2)
					<label class="checkbox-inline"><input type="checkbox" name="denied[]" class="denied-{{ $list['id'] }}" value="{{ $list2['value'] }}" @if(in_array($list2['value'], old('denied', explode(', ', $index->denied)))) checked @endif>{{ $list2['name'] }}</label>
					@endforeach
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('denied') }}</li>
					</ul>
				</div>
			</div>
		@endforeach

		<div class="form-group">
			<label for="password" class="control-label col-md-3 col-sm-3 col-xs-12">Password User<span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="password" id="password" name="password" class="form-control {{$errors->first('password') != '' ? 'parsley-error' : ''}}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('password') }}</li>
				</ul>
			</div>
		</div>


		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.position') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection