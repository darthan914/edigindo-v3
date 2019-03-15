@extends('backend.layout.master')

@section('title')
	Edit Sales
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('#datatable').DataTable();

		$('#datatable').on('click', '.edit-detail', function(){
			$('#edit-detail *[name=id]').val($(this).data('id'));
			$('#edit-detail *[name=account_list_id]').val($(this).data('account_list_id')).trigger('change');

			$('#edit-detail *[name=price]').val($(this).data('price'));
			$('#edit-detail *[name=qty]').val($(this).data('qty'));
			$('#edit-detail *[name=discount]').val($(this).data('discount'));

			$('#edit-detail *[name=note]').val($(this).data('note'));
			

			if($(this).data('ppn') == 10)
			{
				$('#edit-detail *[name=ppn]').attr('checked', 'checked');
			}
			else
			{
				$('#edit-detail *[name=ppn]').removeAttr('checked');
			}
		});

		$('#datatable').on('click', '.delete-detail', function(){
			$('#delete-detail *[name=id]').val($(this).data('id'));
		});
	

		@if(Session::has('create-detail-error'))
			$('#create-detail').modal('show');
		@endif

		@if(Session::has('edit-detail-error'))
			$('#edit-detail').modal('show');
		@endif
	});
</script>

@endsection

@section('content')
	
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.storeAccountSalesDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Detail</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="account_list_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_list_id" name="account_list_id" class="form-control {{$errors->first('account_list_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Account List" data-allow-clear="true">
									<option value=""></option>
									@foreach($account_lists as $list)
									<option value="{{ $list->id }}" @if(old('account_list_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_list_id') }}</li>
								</ul>
							</div>
						</div>

						

						<div class="form-group">
							<label for="price" class="control-label col-md-3 col-sm-3 col-xs-12">Price <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="price" name="price" class="form-control {{$errors->first('price') != '' ? 'parsley-error' : ''}}" value="{{ old('price') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('price') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="qty" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="qty" name="qty" class="form-control {{$errors->first('qty') != '' ? 'parsley-error' : ''}}" value="{{ old('qty') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('qty') }}</li>
								</ul>
							</div>

						</div>

						<div class="form-group">
							<label for="discount" class="control-label col-md-3 col-sm-3 col-xs-12">Discount <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="discount" name="discount" class="form-control {{$errors->first('discount') != '' ? 'parsley-error' : ''}}" value="{{ old('discount') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('discount') }}</li>
								</ul>
							</div>

						</div>

						<div class="form-group">
							<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" id="ppn" name="ppn" value="10" @if(old('ppn') == '10') checked @endif>PPn 10%</label>

								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('ppn') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>


					</div>

					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="account_sales_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="edit-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.updateAccountSalesDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Detail</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="account_list_id" class="control-label col-md-3 col-sm-3 col-xs-12">Account <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="account_list_id" name="account_list_id" class="form-control {{$errors->first('account_list_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Account List" data-allow-clear="true">
									<option value=""></option>
									@foreach($account_lists as $list)
									<option value="{{ $list->id }}" @if(old('account_list_id') == $list->id) selected @endif>{{ $list->account_name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('account_list_id') }}</li>
								</ul>
							</div>
						</div>

						

						<div class="form-group">
							<label for="price" class="control-label col-md-3 col-sm-3 col-xs-12">Price <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="price" name="price" class="form-control {{$errors->first('price') != '' ? 'parsley-error' : ''}}" value="{{ old('price') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('price') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="qty" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="qty" name="qty" class="form-control {{$errors->first('qty') != '' ? 'parsley-error' : ''}}" value="{{ old('qty') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('qty') }}</li>
								</ul>
							</div>

						</div>

						<div class="form-group">
							<label for="discount" class="control-label col-md-3 col-sm-3 col-xs-12">Discount <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="number" id="discount" name="discount" class="form-control {{$errors->first('discount') != '' ? 'parsley-error' : ''}}" value="{{ old('discount') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('discount') }}</li>
								</ul>
							</div>

						</div>

						<div class="form-group">
							<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" id="ppn" name="ppn" value="10" @if(old('ppn') == '10') checked @endif>PPn 10%</label>

								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('ppn') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('note') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="delete-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountSalesDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Edit Sales</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.account.updateAccountSales', $index->id) }}" method="post" enctype="multipart/form-data">


		<div class="form-group">
			<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Company" data-allow-clear="true">
					<option value=""></option>
					@foreach($company as $list)
					<option value="{{ $list->id }}" @if(old('company_id', $index->company_id) == $list->id) selected @endif>{{ $list->name }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('company_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="invoice" class="control-label col-md-3 col-sm-3 col-xs-12">Invoice <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="invoice" name="invoice" class="form-control {{$errors->first('invoice') != '' ? 'parsley-error' : ''}}" value="{{ old('invoice', $index->invoice) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('invoice') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="date" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="date" id="date" name="date" class="form-control {{$errors->first('date') != '' ? 'parsley-error' : ''}}" value="{{ old('date', $index->date) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('date') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="spk_id" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="spk_id" name="spk_id" class="form-control {{$errors->first('spk_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select SPK" data-allow-clear="true">
					<option value=""></option>
					@foreach($spk as $list)
					<option value="{{ $list->id }}" @if(old('spk_id', $index->spk_id) == $list->id) selected @endif>{{ $list->spk }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('spk_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Sales" data-allow-clear="true">
					<option value=""></option>
					@foreach($sales as $list)
					<option value="{{ $list->id }}" @if(old('sales_id', $index->sales_id) == $list->id) selected @endif>{{ $list->fullname }}</option>
					@endforeach
				</select>
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('sales_id') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<input type="text" id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}" value="{{ old('note', $index->note) }}">
				<ul class="parsley-errors-list filled">
					<li class="parsley-required">{{ $errors->first('note') }}</li>
				</ul>
			</div>
		</div>

		<div class="form-group">
			<label for="json_detail" class="control-label col-md-3 col-sm-3 col-xs-12">Data <span class="required">*</span>
			</label>
			<div class="col-md-9 col-sm-9 col-xs-12">
				<button type="button" class="btn btn-success" data-toggle="modal" data-target="#create-detail">Add <i class="fa fa-plus"></i></button>
				
				
			</div>
		</div>

		<div class="ln_solid"></div>

		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				{{ csrf_field() }}
				<a class="btn btn-primary" href="{{ route('backend.account.accountSales') }}">Back</a>
				<button type="submit" class="btn btn-success">Submit</button>
				<input type="hidden" name="i" value="{{ old('i') }}">
			</div>
		</div>

		<div class="ln_solid"></div>


		<table class="table table-bordered" id="datatable">
			<thead>
				<tr>
					<th>Account</th>
					<th>Price</th>

					<th>Quantity</th>
					<th>Discount</th>

					<th>PPN</th>
					<th>Total</th>
					<th>Note</th>

					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				
			    @foreach($detail as $list)
				<tr>
					
					<td>{{ $list->account_lists->account_name }}</td>
					<td style="text-align: right;">{{ number_format($list->price) }}</td>

					<td style="text-align: right;">{{ number_format($list->qty) }}</td>
					<td style="text-align: right;">{{ number_format($list->discount) }}  %</td>

					<td style="text-align: right;">{{ number_format($list->ppn) }} %</td>
					<td style="text-align: right;">{{ number_format((($list->price * $list->qty) * ($list->discount / 100)) * (1 + ($list->ppn / 100))) }}</td>
					<td>{{ $list->note }}</td>

					<td>
						<button type="button" class="btn btn-xs btn-warning edit-detail"
							data-toggle="modal"
							data-target="#edit-detail"
							data-id="{{$list->id}}" 
							data-account_list_id="{{$list->account_list_id}}" 
							data-price="{{$list->price}}" 
							data-qty="{{$list->qty}}"
							data-discount="{{$list->discount}}"
							data-ppn="{{$list->ppn}}"
							data-note="{{$list->note}}"
						><i class="fa fa-edit" aria-hidden="true"></i></button>
						<button type="button" class="btn btn-xs btn-danger delete-detail" 
							data-toggle="modal"
							data-target="#delete-detail"
							data-id="{{$list->id}}"
						><i class="fa fa-trash" aria-hidden="true"></i></button>
					</td>
				</tr>
			    @endforeach
			</tbody>
			<tfoot>
				<th></th>
				<th></th>

				<th></th>
				<th></th>

				<th></th>
				<th style="text-align: right;">{{ number_format($index->account_sales_details()->sum(DB::raw('((price * qty) * (discount / 100)) * (1 + (ppn / 100))'))) }}</th>
				<th></th>

				<th></th>
			</tfoot>
		</table>

	</form>
	</div>

@endsection