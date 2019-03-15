@extends('backend.layout.master')

@section('title')
	Production List
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
				url: "{{ route('backend.production.datatables') }}",
				type: "POST",
				data: {
					f_finish   : $('*[name=f_finish]').val(),
					f_yeard    : $('*[name=f_yeard]').val(),
			    	f_monthd   : $('*[name=f_monthd]').val(),
			    	f_division : $('*[name=f_division]').val(),
			    	f_source   : $('*[name=f_source]').val(),
			    	f_sales    : $('*[name=f_sales]').val(),
			    	f_year     : $('*[name=f_year]').val(),
			    	f_month    : $('*[name=f_month]').val(),
			    	search     : $('*[name=search]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'no_spk'},
				{data: 'name'},
				{data: 'action'},
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

		$('#datatable').on('click', '.pdf-production', function(){
			$('#pdf-production *[name=id]').val($(this).data('id'));
			
		});

		$('#datatable').on('click', '.complete-production', function(){
			$('#complete-production *[name=id]').val($(this).data('id'));
		});


		$('#datatable').on('click', '.history-production', function(){
			$.post('{{ route('backend.production.history') }}', {id: $(this).data('id')}, function(data) {
				$('.history-list').empty();
				$.each(data, function(i, field) {
					console.log(field.created_at)
					$('.history-list').append('<tr>\
						<td>'+field.created_at.date+'</td>\
						<td>'+field.user_name+'</td>\
						<td>'+field.action+'</td>\
						<td>\
							<b>Quantity</b> : '+field.quantity+'<br/>\
							<b>Count Finish</b> : '+field.count_finish+'<br/>\
							<b>Repair</b> : '+field.count_repair+'\
						</td></tr>\
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

		@if(Session::has('pdf-production-error'))
		$('#pdf-production').modal('show');
		@endif
		@if(Session::has('complete-production-error'))
		$('#complete-production').modal('show');
		@endif


	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('content')


	{{-- Production PDF --}}
	<div id="pdf-production" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.production.pdf') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size','A4') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation','portrait') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation','portrait') == 'landscape') checked @endif>Landscape</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('orientation') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Complete Project --}}
	<div id="complete-production" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.production.complete') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Complete Project</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="count_finish" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity Complete <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" class="form-control {{$errors->first('count_finish') != '' ? 'parsley-error' : ''}}" name="count_finish" value="{{old('count_finish')}}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('count_finish') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- History Production --}}
	<div id="history-production" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">History Production</h4>
					</div>
					<div class="modal-body">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Datetime</th>
									<th>User</th>
									<th>Action</th>
									<th>Data</th>
								</tr>
							</thead>
							<tbody class="history-list"></tbody>
						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Production List</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-8">
				<form class="form-inline" method="get">
					<select name="f_finish" class="form-control" onchange="this.form.submit()">
						<option value="" {{ $request->f_finish === '' ? 'selected' : '' }}>All Status Production</option>
						<option value="1" {{ $request->f_finish === '1' ? 'selected' : '' }}>Production Finish</option>
						<option value="0" {{ $request->f_finish === '0' ? 'selected' : '' }}>Production On Progress</option>
					</select>
					<select class="form-control" name="f_year" onchange="this.form.submit()">
						<option value="" {{ $request->f_year == '' ? 'selected' : '' }}>All Year Date SPK</option>
						@foreach($year as $list)
						<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_month" onchange="this.form.submit()">
						<option value="" {{ $request->f_month == '' ? 'selected' : '' }}>All Month Date SPK</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_yeard" onchange="this.form.submit()">
						<option value="">This Year Deadline</option>
						<option value="all" {{ $request->f_yeard == 'all' ? 'selected' : '' }}>All Year Deadline</option>
						@foreach($yeard as $list)
						<option value="{{ $list->year }}" {{ $request->f_yeard == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_monthd" onchange="this.form.submit()">
						<option value="">This Month Deadline</option>
						<option value="all" {{ $request->f_monthd == 'all' ? 'selected' : '' }}>All Month Deadline</option>
						@php $numMonth = 1; @endphp
						@foreach($month as $list)
						<option value="{{ $numMonth }}" {{ $request->f_monthd == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_division" onchange="this.form.submit()">
						<option value="">{{ Auth::user()->divisions->name }}</option>
						<option value="all" {{ $request->f_division == 'all' ? 'selected' : '' }}>All Division</option>
						@foreach($division as $list)
						<option value="{{ $list->id }}" {{ $request->f_division == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_source" onchange="this.form.submit()">
						<option value="" {{ $request->f_source == '' ? 'selected' : '' }}>All Source</option>
						<option value="INSOURCE" {{ $request->f_source == 'INSOURCE' ? 'selected' : '' }}>Insource</option>
						<option value="OUTSOURCE" {{ $request->f_source == 'OUTSOURCE' ? 'selected' : '' }}>OutSource</option>
					</select>
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">All Sales</option>
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
					</select>
					<input type="text" name="search" placeholder="Search" value="{{ $request->search }}" class="form-control" onchange="this.form.submit()">
				</form>
			</div>
			<div class="col-md-4">
				<form method="post" id="action" action="{{ route('backend.production.action') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					<select class="form-control" name="action">
						<option value="complete">Finish</option>
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
					<th>Information SPK</th>
					<th>Information Production</th>
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