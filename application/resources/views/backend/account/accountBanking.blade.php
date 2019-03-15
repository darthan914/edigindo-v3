@extends('backend.layout.master')

@section('title')
	Account Banking
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">

	function formatGeneral ( d ) {
		html = '';
		html += '<div class="row">';
		html += '	<div class="col-md-12">'+d.detail+'</div>';
		html += '</div>';
	    return html;
	}
		
	$(function() {



		var table = $('#datatable').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.account.datatablesAccountBanking') }}",
				type: "POST",
				data: {
			    	// f_account_class : $('*[name=f_account_class]').val(),
				},
			},
			columns: [
				{data: 'check', orderable: false, searchable: false, sClass: 'nowrap-cell'},
				
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'date', sClass: 'nowrap-cell'},

				{data: 'account_name', sClass: 'nowrap-cell'},
				// {data: 'total_price', sClass: 'number-format'},

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

		// Add event listener for opening and closing details
	    $('#datatable tbody').on('click', 'td.details-control > button', function () {
	    	console.log('click');
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( formatGeneral(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );


		$('#datatable').on('click', '.deleteAccountBanking-account', function(){
			$('#deleteAccountBanking-account input[name=id]').val($(this).data('id'));
		});

		$('#datatable').on('click', '.pdfAccountBanking-account', function(){
			$('#pdfAccountBanking-account input[name=id]').val($(this).data('id'));
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


	@can('deleteAccountBanking-account')
	{{-- Delete Account Banking --}}
	<div id="deleteAccountBanking-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.deleteAccountBanking') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete Account Banking?</h4>
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

	<div id="pdfAccountBanking-account" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.account.pdfAccountBanking') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Download PDF</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="size" class="control-label col-md-3 col-sm-3 col-xs-12">Paper Size <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="size-A4" name="size" value="A4" @if(old('size') == 'A4') checked @endif>A4</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('size') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="orientation" class="control-label col-md-3 col-sm-3 col-xs-12">Orientation <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="orientation-portrait" name="orientation" value="portrait" @if(old('orientation') == 'portrait') checked @endif>Portrait</label> 
								<label class="radio-inline"><input type="radio" id="orientation-landscape" name="orientation" value="landscape" @if(old('orientation') == 'landscape') checked @endif>Landscape</label>
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


	<h1>Account Banking</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					
				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="{{ route('backend.account.actionAccountBanking') }}" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					@can('createAccountBanking-account')
					<a href="{{ route('backend.account.createAccountBanking') }}" class="btn btn-default">Create</a>
					@endif
					<select class="form-control" name="action">
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

					<th></th>
					<th>Date</th>

					<th>Bank Account</th>
					{{-- <th>Amount</th> --}}
					<th>Action</th>

				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>

					<td></td>
					<td></td>

					<td></td>
					{{-- <td></td> --}}
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
@endsection