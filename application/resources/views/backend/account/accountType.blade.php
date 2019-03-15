@extends('backend.layout.master')

@section('title')
	Account Type List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountType') }}",
				type: "POST",
				data: {
			    	
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				{data: 'name', sClass: 'nowrap-cell'},
				{data: 'action', orderable: false, searchable: false, sClass: 'nowrap-cell'},
			],
			initComplete: function () {
				this.api().columns().every(function () {
					var column = this;
					var input = document.createElement("input");
					$(input).appendTo($(column.footer()).empty())
					.on('keyup', function () {
						column.search($(this).val(), false, false, true).draw();
					});
				});
			},
			scrollY: "400px",
		});

		$('#datatable').on('click', '.editAccountType-account', function(){
			$('#editAccountType-account input[name=id]').val($(this).data('id'));
			$('#editAccountType-account input[name=name]').val($(this).data('name'));
		});

		$('#datatable').on('click', '.deleteAccountType-account', function(){
			$('#editAccountType-account input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('createAccountType-account-error'))
		$('#createAccountType-account').modal('show');
		@endif

		@if(Session::has('editAccountType-account-error'))
		$('#editAccountType-account').modal('show');
		@endif

	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	.nowrap-cell{
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	@can('createAccountType-account')
	{{-- Create Account Type --}}
	<div id="createAccountType-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountType') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Account Type</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('editAccountType-account')
	{{-- Edit Account Type --}}
	<div id="editAccountType-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.updateAccountType') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Account Type</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Update</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('deleteAccountType-account')
	{{-- Delete Account Type --}}
	<div id="deleteAccountType-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountType') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Account Type?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Account Type List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.account.actionAccountType') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('createAccountType-account')
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#createAccountType-account">Create</button>
					@endif
					<select class="form-control" name="action">
						{{-- <option value="enable">Enable</option>
						<option value="disable">Disable</option> --}}
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
			</div>
		</div>
		
		<div class="ln_solid"></div>

		<table class="table table-striped table-bordered no-footer" id="datatable">
			<thead>
				<tr role="row">
					<th nowrap>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection