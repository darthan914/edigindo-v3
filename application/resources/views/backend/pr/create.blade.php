@extends('backend.layout.master')

@section('title')
	Create PR
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('input[name=datetime_order]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$('input[name=deadline]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$('select[name=spk_id]').select2({
			placeholder: "Select SPK",
			allowClear: true
		});

		$('select[name=division]').select2({
			placeholder: "Select Division",
			allowClear: true
		});

		$('button.spk-item').click(function(){
			$.post('{{ route('backend.pr.getSpkItem') }}', {id: $('select[name=spk_id]').val()}, function(data) {
				$('.item-list').empty();

				$('.item-list').append('\
					<tr>\
						<th>No PR</th>\
						<th>Item</th>\
						<th>Name Order</th>\
						<th>Quantity</th>\
					</tr>\
				');
				console.log(data);
				$.each(data, function(i, field) {
					$('.item-list').append('\
					<tr>\
						<td>'+ field.no_pr +'</td>\
						<td>'+ field.item +'</td>\
						<td>'+ field.name +'</td>\
						<td>'+ field.quantity + ' ' + field.unit +'</td>\
					</tr>\
				');
				});
			});
			
		});

	});
</script>

@endsection

@section('content')

	{{-- Item SPK --}}
	<div id="spk-item" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Item Comfirmed</h4>
					</div>
					<div class="modal-body">
						<table class="table item-list">
							

						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Create PR</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.pr.store') }}" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', Auth::user()->fullname ) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('name') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="no_pr" class="control-label col-md-3 col-sm-3 col-xs-12">No PR <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="no_pr" name="no_pr" class="form-control {{$errors->first('no_pr') != '' ? 'parsley-error' : ''}}" value="{{ old('no_pr') }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('no_pr') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="spk_id" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<div class="input-group">
					<select id="spk_id" name="spk_id" class="form-control {{$errors->first('spk_id') != '' ? 'parsley-error' : ''}}">
						<option value=""></option>
						@foreach($spk as $list)
						<option value="{{ $list->id }}" @if(old('spk_id') == $list->id) selected @endif>{{ $list->spk }} - {{ $list->name }}</option>
						@endforeach
					</select>
					<span class="input-group-btn">
                        <button type="button" class="btn btn-primary spk-item" data-toggle="modal" data-target="#spk-item">Check</button>
                    </span>
				</div>
					
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('spk_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="division" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="division" name="division" class="form-control {{$errors->first('division') != '' ? 'parsley-error' : ''}}" value="{{ old('division') }}">
					<option value=""></option>
					@foreach($division as $list)
					<option value="{{ $list->code }}" @if(old('division') == $list->code) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('division') }}</li>
				</ul>
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.pr') }}">Cancel</a>
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
		</div>
				
	</form>
	</div>

@endsection