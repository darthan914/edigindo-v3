@extends('backend.layout.master')

@section('title')
	PR List
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
	$(function() {
		$('.storeOfficePr').click(function(event) {
			$('#storeOfficePr-pr').submit();
		});

		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.pr.datatables') }}",
				type: "post",
				data: {
			    	f_user : $('*[name=f_user]').val(),
			    	f_year  : $('*[name=f_year]').val(),
			    	f_month : $('*[name=f_month]').val(),
			    	search : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'name'},
				{data: 'barcode'},
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
			dom: '<l<tr>ip>'
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

		$('#datatable').on('click', '.delete-pr', function(){
			$('.pr_id-ondelete').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pdf-pr', function(){
			$('.pr_id-onpdf').val($(this).data('id'));
		});

		$('select[name=f_user]').select2({
		});

		@if(Session::has('pdf-pr-error'))
		$('#pdf-pr').modal('show');
		@endif

		@if(Session::has('createProjectPr-pr-error'))
		$('#createProjectPr-pr').modal('show');
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
	<form class="form-horizontal form-label-left" id="storeOfficePr-pr" action="{{ route('backend.pr.storeOfficePr') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}</form>

	{{-- Item SPK --}}
	<div id="spk-item" class="modal fade" role="dialog" style="z-index: 1100;">
		<div class="modal-dialog modal-lg">
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
	
	{{-- Delete PR --}}
	<div id="delete-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.delete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete PR?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="pr_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- PR PDF --}}
	<div id="pdf-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.pdf') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size', 'A4') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation', 'portrait') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation', 'portrait') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="pr_id" class="pr_id-onpdf" value="{{old('pr_id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="createProjectPr-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.storeProjectPr') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Pr For Project</h4>
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


						<div class="form-group">
							<label for="spk_id" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="spk_id" name="spk_id" class="form-control {{$errors->first('spk_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select SPK">
									<option value=""></option>
									@foreach($spk as $list)
									<option value="{{ $list->id }}" @if(old('spk_id') == $list->id) selected @endif>{{ $list->no_spk }} - {{ $list->name }}</option>
									@endforeach
								</select>
		                        <button type="button" class="btn btn-primary btn-block spk-item" data-toggle="modal" data-target="#spk-item">Check</button>
			                </div>
								
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('spk_id') }}</li>
							</ul>
						</div>

						<div class="form-group">
							<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Division">
									<option value=""></option>
									@foreach($division as $list)
									<option value="{{ $list->id }}" @if(old('division_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
							</div>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('division_id') }}</li>
							</ul>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="createPaymentPr-pr" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.pr.storePaymentPr') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Pr For Payment</h4>
					</div>
					<div class="modal-body">

						<div class="form-group">
							<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Division">
									<option value=""></option>
									@foreach($division as $list)
									<option value="{{ $list->id }}" @if(old('division_id') == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
							</div>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('division_id') }}</li>
							</ul>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>PR List</h1>

	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control select2" name="f_year" onchange="this.form.submit()">
						<option value="">This Year</option>
						<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control select2" name="f_month" onchange="this.form.submit()">
						<option value="">This Month</option>
						<option value="all" {{ $request->f_month == 'all' ? 'selected' : '' }}>All Month</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>

					<select class="form-control select2" name="f_user" onchange="this.form.submit()">
						<option value="">My PR</option>
						<option value="staff" {{ $request->f_user == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('full-user')
							<option value="all" {{ $request->f_user == 'all' ? 'selected' : '' }}>All User</option>
						@endcan
						
						@foreach($user as $list)
						<option value="{{ $list->id }}" {{ $request->f_user == $list->id ? 'selected' : '' }}>{{ $list->first_name }} {{ $list->last_name }}</option>
						@endforeach
						
					</select>

					<input type="text" name="search" placeholder="Search" class="form-control" onchange="this.form.submit()" value="{{ $request->search }}">
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.pr.action') }}" class="form-inline text-right" onsubmit="return confirm('Are your sure to take this action?')">
					@can('create-pr')
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								Create <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li><button class="btn btn-link" type="button" data-toggle="modal" data-target="#createProjectPr-pr">For Project</button>
								</li>
								<li><button class="btn btn-link storeOfficePr" type="button">For Office</button>
								</li>
								<li><button class="btn btn-link" type="button" data-toggle="modal" data-target="#createPaymentPr-pr">For Payment</button>
								</li>
							</ul>
						</div>
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

		<table class="table table-striped table-bordered" id="datatable">
			<thead>
				<tr>
					<th>
						<label class="checkbox-inline"><input type="checkbox" data-target="check" class="check-all" id="check-all">S</label>
					</th>
					<th>Information</th>
					<th>Barcode</th>

					<th>Action</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td></td>

					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	

@endsection