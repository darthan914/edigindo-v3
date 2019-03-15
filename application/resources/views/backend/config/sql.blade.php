@extends('backend.layout.master')

@section('title')
	Config
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('.update-config').change(function(event) {
			$.post('{{ route('backend.config.update') }}', {
				id: $(this).data('id'),
				value : $(this).val(),
			}, function(data) {
				if(data != '')
				{
					alert(data);
				}
			});
		});
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('content')

	<h1>Config</h1>

	<div class="x_panel" style="overflow: auto;">
		<form class="form-horizontal form-label-left" action="{{ route('backend.runSql') }}" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<label for="sql" class="control-label col-md-3 col-sm-3 col-xs-12">SQL <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<textarea id="sql" name="sql" class="form-control {{$errors->first('sql') != '' ? 'parsley-error' : ''}}">{{ old('sql') }}</textarea>
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">
							@if(Session::has('error'))
								{{ Session::get('error') }}
							@endif
						</li>
					</ul>
				</div>
			</div>

			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<a class="btn btn-primary" href="{{ route('backend.pr') }}">Cancel</a>
					<button type="submit" class="btn btn-success">GO</button>
				</div>
			</div>
					
		</form>
	</div>
	

@endsection