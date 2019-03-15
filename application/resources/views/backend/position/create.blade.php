@extends('backend.layout.master')

@section('title')
	Create Position
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=date]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

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

	<h1>Create Position</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.position.store') }}" method="post" enctype="multipart/form-data">

		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Position <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="parent_id" class="control-label col-md-3 col-sm-3 col-xs-12">Parent
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="parent_id" name="parent_id" class="form-control select2 {{$errors->first('parent_id') != '' ? 'parsley-error' : ''}}">
					@if(in_array(Auth::user()->positions->id, getConfigValue('super_admin_position', true)) || in_array(Auth::id(), getConfigValue('super_admin_user', true)))
					<option value="">Root</option>
					@endif
					@foreach($parent as $list)
					<option value="{{ $list->id }}" @if(old('parent_id', Auth::user()->positions->id) == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="active" class="control-label col-md-3 col-sm-3 col-xs-12"> 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" value="1" name="active" @if(old('active') == "1") checked @endif>Active</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('active') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label checkbox-inline col-md-3 col-sm-3 col-xs-12">
				<input type="checkbox" data-target="group-master" class="check-all"> Config

			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" name="permission[]" class="group-master" value="configuration" @if(in_array('configuration', old('permission', []))) checked @endif>Configuration</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('permission') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label checkbox-inline col-md-3 col-sm-3 col-xs-12">
				<input type="checkbox" data-target="group-developer" class="check-all"> Developer

			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" name="permission[]" class="group-developer" value="migration" @if(in_array('migration', old('permission', []))) checked @endif>Migration Table</label>
				<label class="checkbox-inline"><input type="checkbox" name="permission[]" class="group-developer" value="sql" @if(in_array('sql', old('permission', []))) checked @endif>SQL</label>
				<label class="checkbox-inline"><input type="checkbox" name="permission[]" class="group-developer" value="beta" @if(in_array('beta', old('permission', []))) checked @endif>Beta Tester</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('permission') }}</li>
				</ul>
			</div>
		</div>

		

		@foreach($key as $list)
			<div class="form-group">
				<label class="control-label checkbox-inline col-md-3 col-sm-3 col-xs-12">
					<input type="checkbox" data-target="group-{{ $list['id'] }}" class="check-all"> Access {{ $list['name'] }}
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					@foreach($list['data'] as $list2)
					<label class="checkbox-inline"><input type="checkbox" name="permission[]" class="group-{{ $list['id'] }}" value="{{ $list2['value'] }}" @if(in_array($list2['value'], old('permission', []))) checked @endif>{{ $list2['name'] }}</label>
					@endforeach
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('permission') }}</li>
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