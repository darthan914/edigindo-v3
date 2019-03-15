@extends('backend.layout.master')

@section('title')
	Account List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('select[name=relation]').select2({
			placeholder: "Select Relation",
			allowClear: true
		});
		$('select[name=parent_id]').select2({
			placeholder: "Select Parent Account",
			allowClear: true
		});
		$('select[name=account_class_id]').select2({
			placeholder: "Select Account Classification",
			allowClear: true
		});
		$('select[name=account_type_id]').select2({
			placeholder: "Select Account Type",
			allowClear: true
		});

		$('select[name=to_id]').select2({
			placeholder: "Select Account to Merge",
			allowClear: true
		});

		$('select[name=f_account_class]').select2();


		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountList') }}",
				type: "POST",
				data: {
			    	f_account_class : $('*[name=f_account_class]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},

				// {data: 'header', sClass: 'nowrap-cell'},
				{data: 'account_number', sClass: 'nowrap-cell'},
				// {data: 'account_name', sClass: 'nowrap-cell'},

				{data: 'account_balance', sClass: 'number-format'},
				// {data: 'account_class', sClass: 'nowrap-cell'},
				{data: 'account_type', sClass: 'nowrap-cell'},

				{data: 'active', sClass: 'nowrap-cell'},

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
			pageLength: 100,
		});

		$('.createAccountList-account').click(function(event) {
			$('#createAccountList-account .input-parent_id').hide();
			$('#createAccountList-account .input-account_class_id').hide();
			$('#createAccountList-account .input-account_type_id').hide();
			$('#createAccountList-account .input-account_number').hide();
			$('#createAccountList-account .input-account_name').hide();
			$('#createAccountList-account .input-account_balance').hide();
			$('#createAccountList-account .input-active').hide();
		});

		$('#createAccountList-account').on('change', 'select[name=relation]', function(){
			console.log('change');
			if($(this).val() == "CHILD")
			{
				$('#createAccountList-account .input-parent_id').slideDown();
				$('#createAccountList-account .input-account_class_id').slideUp();
				$('#createAccountList-account .input-account_type_id').slideDown();
				$('#createAccountList-account .input-account_number').slideDown();
				$('#createAccountList-account .input-account_name').slideDown();
				$('#createAccountList-account .input-account_balance').slideDown();
				$('#createAccountList-account .input-active').slideDown();
			}
			else if($(this).val() == "PARENT")
			{
				$('#createAccountList-account .input-parent_id').slideDown();
				$('#createAccountList-account .input-account_class_id').slideDown();
				$('#createAccountList-account .input-account_type_id').slideDown();
				$('#createAccountList-account .input-account_number').slideDown();
				$('#createAccountList-account .input-account_name').slideDown();
				$('#createAccountList-account .input-account_balance').slideUp();
				$('#createAccountList-account .input-active').slideDown();
			}
			else
			{
				$('#createAccountList-account .input-parent_id').slideUp();
				$('#createAccountList-account .input-account_class_id').slideUp();
				$('#createAccountList-account .input-account_type_id').slideUp();
				$('#createAccountList-account .input-account_number').slideUp();
				$('#createAccountList-account .input-account_name').slideUp();
				$('#createAccountList-account .input-account_balance').slideUp();
				$('#createAccountList-account .input-active').slideUp();
			}
		});

		$('#editAccountList-account').on('change', 'select[name=relation]', function(){
			console.log('change');
			if($(this).val() == "CHILD")
			{
				$('#editAccountList-account .input-parent_id').slideDown();
				$('#editAccountList-account .input-account_class_id').slideUp();
				$('#editAccountList-account .input-account_type_id').slideDown();
				$('#editAccountList-account .input-account_number').slideDown();
				$('#editAccountList-account .input-account_name').slideDown();
				$('#editAccountList-account .input-account_balance').slideDown();
				$('#editAccountList-account .input-active').slideDown();
			}
			else if($(this).val() == "PARENT")
			{
				$('#editAccountList-account .input-parent_id').slideDown();
				$('#editAccountList-account .input-account_class_id').slideDown();
				$('#editAccountList-account .input-account_type_id').slideDown();
				$('#editAccountList-account .input-account_number').slideDown();
				$('#editAccountList-account .input-account_name').slideDown();
				$('#editAccountList-account .input-account_balance').slideUp();
				$('#editAccountList-account .input-active').slideDown();
			}
			else
			{
				$('#editAccountList-account .input-parent_id').slideUp();
				$('#editAccountList-account .input-account_class_id').slideUp();
				$('#editAccountList-account .input-account_type_id').slideUp();
				$('#editAccountList-account .input-account_number').slideUp();
				$('#editAccountList-account .input-account_name').slideUp();
				$('#editAccountList-account .input-account_balance').slideUp();
				$('#editAccountList-account .input-active').slideUp();
			}
		});

		$('#datatable').on('click', '.editAccountList-account', function(){

			if($(this).data('relation') == "CHILD")
			{
				$('#editAccountList-account .input-parent_id').slideDown();
				$('#editAccountList-account .input-account_class_id').slideUp();
				$('#editAccountList-account .input-account_type_id').slideDown();
				$('#editAccountList-account .input-account_number').slideDown();
				$('#editAccountList-account .input-account_name').slideDown();
				$('#editAccountList-account .input-account_balance').slideDown();
				$('#editAccountList-account .input-active').slideDown();
			}
			else if($(this).data('relation') == "PARENT")
			{
				$('#editAccountList-account .input-parent_id').slideDown();
				$('#editAccountList-account .input-account_class_id').slideDown();
				$('#editAccountList-account .input-account_type_id').slideDown();
				$('#editAccountList-account .input-account_number').slideDown();
				$('#editAccountList-account .input-account_name').slideDown();
				$('#editAccountList-account .input-account_balance').slideUp();
				$('#editAccountList-account .input-active').slideDown();
			}
			else
			{
				$('#editAccountList-account .input-parent_id').slideUp();
				$('#editAccountList-account .input-account_class_id').slideUp();
				$('#editAccountList-account .input-account_type_id').slideUp();
				$('#editAccountList-account .input-account_number').slideUp();
				$('#editAccountList-account .input-account_name').slideUp();
				$('#editAccountList-account .input-account_balance').slideUp();
				$('#editAccountList-account .input-active').slideUp();
			}

			$('#editAccountList-account input[name=id]').val($(this).data('id'));
			$('#editAccountList-account select[name=relation]').val($(this).data('relation')).trigger('change');
			$('#editAccountList-account select[name=parent_id]').val($(this).data('parent_id')).trigger('change');
			$('#editAccountList-account select[name=account_class_id]').val($(this).data('account_class_id')).trigger('change');
			$('#editAccountList-account select[name=account_type_id]').val($(this).data('account_type_id')).trigger('change');
			$('#editAccountList-account input[name=account_number]').val($(this).data('account_number'));
			$('#editAccountList-account input[name=account_name]').val($(this).data('account_name'));
			$('#editAccountList-account input[name=account_balance]').val($(this).data('account_balance'));

			if($(this).data('active') == 1)
			{
				$('#editAccountList-account input[name=active]').prop('checked', true);
			}
			else
			{
				$('#editAccountList-account input[name=active]').prop('checked', false);
			}
		});

		$('#datatable').on('click', '.deleteAccountList-account', function(){
			$('#deleteAccountList-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.setParentAccountList-account', function(){
			$('#setParentAccountList-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.setChildAccountList-account', function(){
			$('#setChildAccountList-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.mergeAccountList-account', function(){
			$('#mergeAccountList-account input[name=from_id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.activeAccountList-account', function(){
			$('#activeAccountList-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.inactiveAccountList-account', function(){
			$('#inactiveAccountList-account input[name=id]').val($(this).data('id'));
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

	    @if(Session::has('createAccountList-account-error'))
		var relation = "{{ old('relation') }}";
		if(relation == "CHILD")
		{
			$('#createAccountList-account .input-parent_id').slideDown();
			$('#createAccountList-account .input-account_class_id').slideUp();
			$('#createAccountList-account .input-account_type_id').slideDown();
			$('#createAccountList-account .input-account_number').slideDown();
			$('#createAccountList-account .input-account_name').slideDown();
			$('#createAccountList-account .input-account_balance').slideDown();
			$('#createAccountList-account .input-active').slideDown();
		}
		else if(relation == "PARENT")
		{
			$('#createAccountList-account .input-parent_id').slideDown();
			$('#createAccountList-account .input-account_class_id').slideDown();
			$('#createAccountList-account .input-account_type_id').slideDown();
			$('#createAccountList-account .input-account_number').slideDown();
			$('#createAccountList-account .input-account_name').slideDown();
			$('#createAccountList-account .input-account_balance').slideUp();
			$('#createAccountList-account .input-active').slideDown();
		}
		else
		{
			$('#createAccountList-account .input-parent_id').slideUp();
			$('#createAccountList-account .input-account_class_id').slideUp();
			$('#createAccountList-account .input-account_type_id').slideUp();
			$('#createAccountList-account .input-account_number').slideUp();
			$('#createAccountList-account .input-account_name').slideUp();
			$('#createAccountList-account .input-account_balance').slideUp();
			$('#createAccountList-account .input-active').slideUp();
		}
		$('#createAccountList-account').modal('show');


		@endif

		@if(Session::has('editAccountList-account-error'))
		var relation = "{{ old('relation') }}";
		if(relation == "CHILD")
		{
			$('#editAccountList-account .input-parent_id').slideDown();
			$('#editAccountList-account .input-account_class_id').slideUp();
			$('#editAccountList-account .input-account_type_id').slideDown();
			$('#editAccountList-account .input-account_number').slideDown();
			$('#editAccountList-account .input-account_name').slideDown();
			$('#editAccountList-account .input-account_balance').slideDown();
			$('#editAccountList-account .input-active').slideDown();
		}
		else if(relation == "PARENT")
		{
			$('#editAccountList-account .input-parent_id').slideDown();
			$('#editAccountList-account .input-account_class_id').slideDown();
			$('#editAccountList-account .input-account_type_id').slideDown();
			$('#editAccountList-account .input-account_number').slideDown();
			$('#editAccountList-account .input-account_name').slideDown();
			$('#editAccountList-account .input-account_balance').slideUp();
			$('#editAccountList-account .input-active').slideDown();
		}
		else
		{
			$('#editAccountList-account .input-parent_id').slideUp();
			$('#editAccountList-account .input-account_class_id').slideUp();
			$('#editAccountList-account .input-account_type_id').slideUp();
			$('#editAccountList-account .input-account_number').slideUp();
			$('#editAccountList-account .input-account_name').slideUp();
			$('#editAccountList-account .input-account_balance').slideUp();
			$('#editAccountList-account .input-active').slideUp();
		}

		$('#editAccountList-account').modal('show');
		@endif

		@if(Session::has('setParentAccountList-account-error'))
		$('#setParentAccountList-account').modal('show');
		@endif

		@if(Session::has('setChildAccountList-account-error'))
		$('#setChildAccountList-account').modal('show');
		@endif

		@if(Session::has('mergeAccountList-account-error'))
		$('#mergeAccountList-account').modal('show');
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

	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	@can('createAccountList-account')
	{{-- Create Account List --}}
	<div id="createAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Account List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-relation">
							<label for="relation" class="control-label col-md-3 col-sm-3 col-xs-12">Relation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="relation" name="relation" class="form-control {{$errors->first('relation') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($relation as $key => $list)
									<option value="{{ $key }}" @if(old('relation') == $key) selected @endif>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('relation') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-parent_id">
							<label for="parent_id" class="control-label col-md-3 col-sm-3 col-xs-12">Parent <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="parent_id" name="parent_id" class="form-control {{$errors->first('parent_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_parent as $list)
									<option value="{{ $list->id }}" @if(old('parent_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_class_id">
							<label for="account_class_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account Class <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_class_id" name="account_class_id" class="form-control {{$errors->first('account_class_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_class as $list)
									<option value="{{ $list->id }}" @if(old('account_class_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_class_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_type_id">
							<label for="account_type_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_type_id" name="account_type_id" class="form-control {{$errors->first('account_type_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_type as $list)
									<option value="{{ $list->id }}" @if(old('account_type_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_type_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_number">
							<label for="account_number" class="control-label col-md-3 col-sm-3 col-xs-12">Account Number <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_number" name="account_number" class="form-control {{$errors->first('account_number') != '' ? 'parsley-error' : ''}}" value="{{ old('account_number') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_number') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_name">
							<label for="account_name" class="control-label col-md-3 col-sm-3 col-xs-12">Account Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_name" name="account_name" class="form-control {{$errors->first('account_name') != '' ? 'parsley-error' : ''}}" value="{{ old('account_name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_name') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_balance">
							<label for="account_balance" class="control-label col-md-3 col-sm-3 col-xs-12">Account Balance <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_balance" name="account_balance" class="form-control {{$errors->first('account_balance') != '' ? 'parsley-error' : ''}}" value="{{ old('account_balance') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_balance') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-active">
							<label for="active" class="control-label col-md-3 col-sm-3 col-xs-12">Active <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" value="1" name="active" @if(old('active') == 1) checked @endif>Active</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('active') }}</li>
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

	@can('editAccountList-account')
	{{-- Edit Account List --}}
	<div id="editAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.updateAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Account List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-relation">
							<label for="relation" class="control-label col-md-3 col-sm-3 col-xs-12">Relation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="relation" name="relation" class="form-control {{$errors->first('relation') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($relation as $key => $list)
									<option value="{{ $key }}" @if(old('relation') == $key) selected @endif>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('relation') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-parent_id">
							<label for="parent_id" class="control-label col-md-3 col-sm-3 col-xs-12">Parent <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="parent_id" name="parent_id" class="form-control {{$errors->first('parent_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_parent as $list)
									<option value="{{ $list->id }}" @if(old('parent_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_class_id">
							<label for="account_class_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account Class <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_class_id" name="account_class_id" class="form-control {{$errors->first('account_class_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_class as $list)
									<option value="{{ $list->id }}" @if(old('account_class_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_class_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_type_id">
							<label for="account_type_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_type_id" name="account_type_id" class="form-control {{$errors->first('account_type_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_type as $list)
									<option value="{{ $list->id }}" @if(old('account_type_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_type_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_number">
							<label for="account_number" class="control-label col-md-3 col-sm-3 col-xs-12">Account Number <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_number" name="account_number" class="form-control {{$errors->first('account_number') != '' ? 'parsley-error' : ''}}" value="{{ old('account_number') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_number') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_name">
							<label for="account_name" class="control-label col-md-3 col-sm-3 col-xs-12">Account Name <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_name" name="account_name" class="form-control {{$errors->first('account_name') != '' ? 'parsley-error' : ''}}" value="{{ old('account_name') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_name') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_balance">
							<label for="account_balance" class="control-label col-md-3 col-sm-3 col-xs-12">Account Balance <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_balance" name="account_balance" class="form-control {{$errors->first('account_balance') != '' ? 'parsley-error' : ''}}" value="{{ old('account_balance') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_balance') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-active">
							<label for="active" class="control-label col-md-3 col-sm-3 col-xs-12">Active <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" value="1" name="active" @if(old('active') == 1) checked @endif>Active</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('active') }}</li>
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

	@can('deleteAccountList-account')
	{{-- Delete Account List --}}
	<div id="deleteAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Account List?</h4>
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

	@can('relationAccountList-account')
	{{-- Set As Detail Account List --}}
	<div id="setChildAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.setChildAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Set As Detail Account List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-parent_id">
							<label for="parent_id" class="control-label col-md-3 col-sm-3 col-xs-12">Parent <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="parent_id" name="parent_id" class="form-control {{$errors->first('parent_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_parent as $list)
									<option value="{{ $list->id }}" @if(old('parent_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('parent_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group input-account_balance">
							<label for="account_balance" class="control-label col-md-3 col-sm-3 col-xs-12">Account Balance <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="account_balance" name="account_balance" class="form-control {{$errors->first('account_balance') != '' ? 'parsley-error' : ''}}" value="{{ old('account_balance') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_balance') }}</li>
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

	{{-- Set As Parent Account List --}}
	<div id="setParentAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.setParentAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Set As Parent Account List?</h4>
					</div>
					<div class="modal-body">
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

	@can('activeAccountList-account')
	{{-- Active Account List --}}
	<div id="activeAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.activeAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Active Account List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Active</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Inactive Account List --}}
	<div id="inactiveAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.activeAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Inactive Account List?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-dark">Inactive</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('mergeAccountList-account')
	{{-- Merge Account List --}}
	<div id="mergeAccountList-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.mergeAccountList') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Merge Account List</h4>
					</div>
					<div class="modal-body">

						<div class="form-group input-to_id">
							<label for="to_id" class="control-label col-md-3 col-sm-3 col-xs-12">Merge With <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="to_id" name="to_id" class="form-control {{$errors->first('to_id') != '' ? 'parsley-error' : ''}}">
									<option value=""></option>
									@foreach($account_child as $list)
									<option value="{{ $list->id }}" @if(old('to_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('to_id') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="from_id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<h1>Account List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_account_class" onchange="this.form.submit()">
						<option value="">All Classification</option>
						@foreach($account_class as $list)
						<option value="{{ $list->id }}" {{ $request->f_account_class == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.account.actionAccountList') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('createAccountList-account')
					<button type="button" class="btn btn-default createAccountList-account" data-toggle="modal" data-target="#createAccountList-account">Create</button>
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

					{{-- <th>Parent</th> --}}
					<th>Account Number</th>
					{{-- <th>Name</th> --}}

					<th>Balance</th>
					{{-- <th>Classification</th> --}}
					<th>Type</th>

					<th>Active</th>

					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>

					{{-- <td></td> --}}
					<td></td>
					{{-- <td></td> --}}

					<td></td>
					{{-- <td></td> --}}
					<td></td>

					<td></td>
					
					<td></td>
				</tr>
			</tfoot>
		</table>

		
			
	</div>
	

@endsection