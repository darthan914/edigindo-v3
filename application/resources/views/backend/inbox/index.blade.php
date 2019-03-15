@extends('backend.layout.master')

@section('title')
	Inbox
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('#datatable-buttons').DataTable({
			"columnDefs": [
			    { "orderable": false, "targets": 0 }
			]
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

	<h1>Inbox</h1>
	<div class="x_panel" style="overflow: auto;">
		<form method="post" id="action" action="{{ route('admin.inbox.action') }}" class="form-inline text-right">
			<select class="form-control" name="action">
				<option value="read">Set As Readed</option>
				<option value="unread">Set As Unread</option>
				<option value="delete">Hapus</option>
			</select>
			<button type="submit" class="btn btn-success">Ubahd Selected</button>
		</form>

		<table class="table table-striped table-bordered" id="datatable-buttons">
			<thead>
				<tr>
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">Pilih Semua</label>
					</th>
					<th>No.</th>
					<th>Read</th>
					<th>Time Post</th>
					<th>Nama</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Messages</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				@php $count=0; @endphp
				@foreach($index as $list)
				<tr>
					<td class="a-center ">
						<input type="checkbox" class="check" value="{{ $list->id }}" name="id[]" form="action">
					</td>
					<td>{{ ++$count }}</td>
					<td>{{ $list->read == 1 ? 'Readed' : 'Unread' }}</td>
					<td>{{ date('d F Y H:i:s', strtotime($list->created_at)) }}</td>
					<td>{{ $list->name }}</td>
					<td>{{ $list->email }}</td>
					<td>{{ $list->phone }}</td>
					<td>{{ $list->messages }}</td>
					<td nowrap>
						<a href="{{ route('admin.inbox.view', ['id' => $list->id]) }}" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>
						<a href="{{ route('admin.inbox.delete', ['id' => $list->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('Hapus Data?')"><i class="fa fa-trash"></i></a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	

@endsection