@extends('backend.layout.master')

@section('title')
	Config
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('content')

	<h1>Config</h1>

	<div class="x_panel" style="overflow: auto;">
		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>Key</th>
					<th>Value</th>
					<th>Edit</th>

				</tr>
			</thead>
			<tbody>
				@foreach($index as $list)
				<tr>
					<td>{{ $list->for }}</td>
					<td>{!! $list->view_value !!}</td>
					<td>
						<a href="{{ route('backend.config.edit', ['id' => $list->id]) }}" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
					</td>
				</tr>
				@endforeach
				
			</tbody>
		</table>
	</div>
	

@endsection