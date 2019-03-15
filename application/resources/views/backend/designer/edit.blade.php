@extends('backend.layout.master')

@section('title')
	Edit Designer
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

		$('select[name=designer_id]').select2({
			placeholder: "Select Designer",
		});

	});
</script>

@endsection

@section('content')

	<h1>Edit Designer</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.designer.update', ['id' => $index->id ]) }}" method="post" enctype="multipart/form-data">
		
		<div class="form-group">
			<label for="designer_id" class="control-label col-md-3 col-sm-3 col-xs-12">Designer <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select name="designer_id" id="designer_id" class="form-control {{$errors->first('designer_id') != '' ? 'parsley-error' : ''}}">
					<option value=""></option>
					@foreach($leader as $list)
					<optgroup label="Group {{$list->fullname}}">
						<option value="{!! $list->id !!}" @if(old('designer_id', $index->designer_id) == $list->id) selected @endif>{!! $list->fullname !!}</option>
						@foreach($designer as $list2)
							@if($list2->leader == $list->id)
							<option value="{!! $list2->id !!}" @if(old('designer_id', $index->designer_id) == $list2->id) selected @endif>{!! $list2->fullname !!}</option>
							@endif
						@endforeach
					</optgroup>
					@endforeach
					
					@foreach($designer as $list)
						{{-- @if($list->leader == '' && $list->level == 0) --}}
						<option value="{!! $list->id !!}" @if(old('designer_id', $index->designer_id) == $list->id) selected @endif>{!! $list->fullname !!}</option>
						{{-- @endif --}}
					@endforeach
				</select>
				
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('designer_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="project" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="project" name="project" class="form-control {{$errors->first('project') != '' ? 'parsley-error' : ''}}" value="{{ old('project', $index->project) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('project') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="description" class="control-label col-md-3 col-sm-3 col-xs-12">Description <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<textarea id="description" name="description" class="form-control {{$errors->first('description') != '' ? 'parsley-error' : ''}}">{{ old('description', $index->description) }}</textarea>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('description') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="urgent" class="control-label col-md-3 col-sm-3 col-xs-12"> 
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<label class="checkbox-inline"><input type="checkbox" value="1" name="urgent" @if(old('urgent', $index->urgent) == "1") checked @endif>Urgent</label>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('urgent') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.designer') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>

	</form>
	</div>

@endsection