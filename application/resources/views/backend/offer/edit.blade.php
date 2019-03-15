@extends('backend.layout.master')

@section('title')
	{{$index->no_document}} - {{$index->name}}
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'note' );
	CKEDITOR.replace( 'detail' );
	CKEDITOR.replace( 'detail-edit' );

	$(function() {
		$('input[name=date_offer], input[name=deadline], input[name=timeline_company], input[name=timeline_compotitor]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		function format ( d ) {
			html = '';
			html += '<div class="row">';
			html += '	<div class="col-md-6">'+d.detail+'</div>';
			html += '	<div class="col-md-6"><strong>Image : </strong><br/><img src="/edigindo/'+d.photo+'" class="image-detail"></div>';
			html += '</div>';
		    return html;
		}

		var table = $('#datatable-detail').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.offer.datatablesDetail', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{
	                className: "details-control",
	                orderable: false,
	                data:  null,
	                defaultContent: "<button class=\"btn btn-xs btn-info\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button>"
	            },
				{data: 'name'},
				{data: 'status'},
				{data: 'action', orderable: false, searchable: false},
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
			dom:"<l<t>ip>",
		});

		// Add event listener for opening and closing details
	    $('#datatable-detail tbody').on('click', 'td.details-control > button', function () {
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( format(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    } );

		var old_company = {{ old('company_id') != '' ? old('company_id') : 0 }};
		var old_brand   = {{ old('brand_id') != '' ? old('brand_id') : 0 }};
		var old_address = "{{ old('address') != '' ? old('address') : '' }}";
		var old_pic     = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		if(old_company != 0){
			$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", false);

			$.post("{{ route('backend.company.getDetail') }}",
	        {
	            id: $('select[name=company_id]').val(),
	        },
	        function(data){
	            $('select[name=brand_id]').empty();
				$.each(data.brands, function(i, field){
					if (old_brand == field.id) 
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"' selected>"+ field.name+"</option>");
					}
					else
					{
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.name+"</option>");
					}
				});
				$('select[name=brand_id]').val(old_brand).trigger('change');

	            $('select[name=address]').empty();
				$.each(data.addresses, function(i, field){
					if (old_address == field.address) 
					{
						$('select[name=address]').append("<option value='"+ field.address +"' selected>"+ field.address+"</option>");
					}
					else
					{
						$('select[name=address]').append("<option value='"+ field.address +"'>"+ field.address+"</option>");
					}
				});
					$('select[name=address]').val(old_address).trigger('change');

	            $('select[name=pic_id]').empty();
				$.each(data.pic, function(i, field){
					if (old_pic == field.id) 
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"' selected>"+ field.first_name +" "+field.last_name+"</option>");
					}
					else
					{
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"'>"+ field.first_name +" "+field.last_name+"</option>");
					}
				});
				$('select[name=pic_id]').val(old_pic).trigger('change');
	        });
		}
		
		$(document).on('change','select[name=company_id]', function(){
			$('input[name=address]').val('');
			$('input[name=additional_phone]').val('');
			if($(this).val() == ''){
				$('select[name=brand_id], select[name=address], select[name=pic_id]').val('').trigger('change');
				$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);
			}
			else{
				$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", false);

				$.post("{{ route('backend.company.getDetail') }}",
		        {
		            id: $(this).val(),
		        },
		        function(data){
		            $('select[name=brand_id]').empty();
					$.each(data.brands, function(i, field){
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.name+"</option>");
					});
					$('select[name=brand_id]').val('').trigger('change');

		            $('select[name=address]').empty();
					$.each(data.addresses, function(i, field){
						$('select[name=address]').append("<option value='"+ field.address +"'>"+ field.address +"</option>");
					});
					$('select[name=address]').val('').trigger('change');

		            $('select[name=pic_id]').empty();
					$.each(data.pic, function(i, field){
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"'>"+ field.first_name +" "+field.last_name+"</option>");
					});
					$('select[name=pic_id]').val('').trigger('change');
		        });
			}
		});

		$('select[name=address]').on("select2:unselect", function (e) { $('input[name=address]').val(''); });
		$('select[name=pic_id]').on("select2:unselect", function (e) { $('input[name=additional_phone]').val(''); });

		$('button[data-dismiss=modal]').click(function(event) {
			$(".parsley-required").empty();
			$("*").removeClass('parsley-error');
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

		$('#datatable-detail').on('click', '.edit-detail', function(){
			$('#edit-detail *[name=id]').val($(this).data('id'));
			
			$('#edit-detail *[name=name]').val($(this).data('name'));
			$('#edit-detail *[name=quantity]').val($(this).data('quantity'));
			$('#edit-detail *[name=unit]').val($(this).data('unit'));
			$('#edit-detail *[name=value]').val($(this).data('value'));
			
			if($(this).data('detail_photo') != "")
			{
				$('.detail-photo-onedit').attr('href', $(this).data('photo'));
				$('.detail-photo-onedit').html("<i class=\"fa fa-paperclip\" aria-hidden=\"true\"></i>");
			}
			else
			{
				$('.detail-photo-onedit').attr('href', "#");
				$('.detail-photo-onedit').html("");
			}
			
			$.post("{{ route('backend.offer.getDetail') }}",
		    {
		        offer_id: $(this).data('id'),
		    },
		    function(data){
		    	CKEDITOR.instances['detail-edit'].setData(data);
		        $('#edit-detail *[name=detail]').val(data);
		    });
		});

		$('#datatable-detail').on('click', '.status-detail', function(){
			$('#status-detail *[name=id]').val($(this).data('id'));
		});

		$('#datatable-detail').on('click', '.undo-detail', function(){
			$('#undo-detail *[name=id]').val($(this).data('id'));
		});

		$('#datatable-detail').on('click', '.delete-detail', function(){
			$('#delete-detail *[name=id]').val($(this).data('id'));
		});

		$('input[name=status]').change(function() {
			$('.form-reason').slideUp();
			$('.form-pricing').slideUp();
			$('.form-timeline').slideUp();
			$('.form-other').slideUp();

			if($(this).val() == "CANCEL")
			{
				$('.form-other').slideDown();
				$('input[name=reason]').prop('checked', false);
			}
			else if($(this).val() == "FAILED")
			{
				$('.form-reason').slideDown();
			}
			else
			{
				$('input[name=reason]').prop('checked', false);
			}
		});

		$('input[name=reason]').change(function() {
			$('.form-pricing').slideUp();
			$('.form-timeline').slideUp();
			$('.form-other').slideUp();

			if($(this).val() == "PRICING")
			{
				$('.form-pricing').slideDown();
			}
			else if($(this).val() == "TIMELINE")
			{
				$('.form-timeline').slideDown();
			}
			else
			{
				$('.form-other').slideDown();
			}
		});

		@if(Session::has('create-detail-error'))
		$('#create-detail').modal('show');
		@endif
		@if(Session::has('edit-detail-error'))
		$('#edit-detail').modal('show');
		@endif
		@if(Session::has('pdf-offer-error'))
		$('#pdf-offer').modal('show');
		@endif
		@if(Session::has('status-detail-error'))
		$('#status-detail').modal('show');

			@if($errors->first('other') != '')
			$('.form-other').show();
			@endif

			@if($errors->first('reason') != '')
			$('.form-reason').show();
			@endif

			@if($errors->first('hm') != '' || $errors->first('hk') != '')
			$('.form-reason').show();
			$('.form-pricing').show();
			@endif

			@if($errors->first('timeline_company') != '' || $errors->first('timeline_compotitor') != '')
			$('.form-reason').show();
			$('.form-timeline').show();
			@endif
		@endif
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	td.details-control {
	    cursor: pointer;
	}
	.image-detail
	{
		width: 100%;
	}
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')
	
	

	<h1>{{$index->no_document}} - {{$index->name}}</h1>

	{{-- Create Detail --}}
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.storeDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Detail</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Detail <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('name') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="quantity" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="number" id="quantity" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('quantity') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="unit" class="control-label col-md-3 col-sm-3 col-xs-12">Unit <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="unit" name="unit" class="form-control {{$errors->first('unit') != '' ? 'parsley-error' : ''}}" value="{{ old('unit', 'pcs') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('unit') }}</li>
										</ul>
									</div>
								</div>
								
							</div>
							<div class="col-md-6">
								

								<div class="form-group">
									<label for="value" class="control-label col-md-3 col-sm-3 col-xs-12">Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="number" id="value" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('value') }}</li>
										</ul>
									</div>
								</div>


								<div class="form-group">
									<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Photo
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="file" id="photo" name="photo" class="form-control {{$errors->first('photo') != '' ? 'parsley-error' : ''}}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('photo') }}</li>
										</ul>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group">
								<textarea id="detail" name="detail" class="form-control {{$errors->first('detail') != '' ? 'parsley-error' : ''}}">
									@if (old('detail'))
										{{old('detail')}}
									@else
										<ul><li>Material :</li><li>Size :</li><li>Print : </li><li>Finishing : </li></ul>
									@endif
								</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('detail') }}</li>
								</ul>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="offer_id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Edit Detail --}}
	<div id="edit-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.updateDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Edit Detail</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">

								<div class="form-group">
									<label for="name-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Name Detail <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="name-edit" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('name') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="quantity-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Quantity <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="quantity-edit" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('quantity') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="unit-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Units <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="unit-edit" name="unit" class="form-control {{$errors->first('unit') != '' ? 'parsley-error' : ''}}" value="{{ old('unit') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('unit') }}</li>
										</ul>
									</div>
								</div>

							</div>
							<div class="col-md-6">


								<div class="form-group">
									<label for="value-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="value-edit" name="value" class="form-control {{$errors->first('value') != '' ? 'parsley-error' : ''}}" value="{{ old('value') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('value') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="photo" class="control-label col-md-3 col-sm-3 col-xs-12">Photo
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="file" id="photo" name="photo" class="form-control {{$errors->first('photo') != '' ? 'parsley-error' : ''}}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('photo') }}</li>
										</ul>
										<a href="#" class="detail-photo-onedit" target="_new"></a>
										<label class="checkbox-inline"><input type="checkbox" name="remove">Remove Attachment</label>
									</div>
								</div>

							</div>
						</div>

						<div class="form-group">
								<textarea id="detail-edit" name="detail" class="form-control detail-detail-onedit {{$errors->first('detail') != '' ? 'parsley-error' : ''}}">
									@if (old('detail'))
										{{old('detail')}}
									@else
										
									@endif
								</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('detail') }}</li>
								</ul>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="detail_id-onedit" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Delete Detail --}}
	<div id="delete-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.deleteDetail') }}" method="post" enctype="multipart/form-data">
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

	{{-- Offer PDF --}}
	<div id="pdf-offer" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.pdf') }}" method="post" enctype="multipart/form-data">
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

						<div class="form-group">
							<label for="option" class="control-label col-md-3 col-sm-3 col-xs-12">Option <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="option-total" name="option" value="total" @if(old('option', 'total') == 'total') checked @endif>Total all detail</label> 
								<label class="radio-inline"><input type="radio" id="option-choice" name="option" value="choice" @if(old('option', 'total') == 'choice') checked @endif>Don't total all detail</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('option') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="header" class="control-label col-md-3 col-sm-3 col-xs-12">Header <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="header-on" name="header" value="on" @if(old('header', 'on') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="header-off" name="header" value="off" @if(old('header', 'on') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('header') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="expo" class="control-label col-md-3 col-sm-3 col-xs-12">Expo <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="expo-on" name="expo" value="on" @if(old('expo', 'off') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="expo-off" name="expo" value="off" @if(old('expo', 'off') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('expo') }}</li>
								</ul>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Download</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Status Detail --}}
	<div id="status-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.statusDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Status Detail</h4>
					</div>
					<div class="modal-body">


						<div class="form-group">
							<label for="status" class="control-label col-md-3 col-sm-3 col-xs-12">Status <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="status-SUCCESS" name="status" value="SUCCESS" @if(old('status') == 'SUCCESS') checked @endif>Success</label>
								<label class="radio-inline"><input type="radio" id="status-CANCEL" name="status" value="CANCEL" @if(old('status') == 'CANCEL') checked @endif>Cancel</label> 
								<label class="radio-inline"><input type="radio" id="status-FAILED" name="status" value="FAILED" @if(old('status') == 'FAILED') checked @endif>Failed</label> 
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('status') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-reason" style="display: none;">
							<div class="form-group">
								<label for="reason" class="control-label col-md-3 col-sm-3 col-xs-12">Reason <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<label class="radio-inline"><input type="radio" id="reason-PRICING" name="reason" value="PRICING" @if(old('reason') == 'PRICING') checked @endif>Pricing</label>
									<label class="radio-inline"><input type="radio" id="reason-TIMELINE" name="reason" value="TIMELINE" @if(old('reason') == 'TIMELINE') checked @endif>Timeline</label> 
									<label class="radio-inline"><input type="radio" id="reason-OTHER" name="reason" value="OTHER" @if(old('reason') == 'OTHER') checked @endif>Other</label> 
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('reason') }}</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="form-pricing" style="display: none;">
							<div class="form-group">
								<label for="hm" class="control-label col-md-3 col-sm-3 col-xs-12">Modal Price <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="hm" name="hm" class="form-control {{$errors->first('hm') != '' ? 'parsley-error' : ''}}" value="{{ old('hm') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('hm') }}</li>
									</ul>
								</div>
							</div>
							<div class="form-group">
								<label for="hk" class="control-label col-md-3 col-sm-3 col-xs-12">Compotitor Price <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="hk" name="hk" class="form-control {{$errors->first('hk') != '' ? 'parsley-error' : ''}}" value="{{ old('hk') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('hk') }}</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="form-timeline" style="display: none;">
							<div class="form-group">
								<label for="timeline_company" class="control-label col-md-3 col-sm-3 col-xs-12">Timeline <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="timeline_company" name="timeline_company" class="form-control {{$errors->first('timeline_company') != '' ? 'parsley-error' : ''}}" value="{{ old('timeline_company') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('timeline_company') }}</li>
									</ul>
								</div>
							</div>
							<div class="form-group">
								<label for="timeline_compotitor" class="control-label col-md-3 col-sm-3 col-xs-12">Timeline Compotitor <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="timeline_compotitor" name="timeline_compotitor" class="form-control {{$errors->first('timeline_compotitor') != '' ? 'parsley-error' : ''}}" value="{{ old('timeline_compotitor') }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('timeline_compotitor') }}</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="form-other" style="display: none;">
							<div class="form-group">
								<label for="other" class="control-label col-md-3 col-sm-3 col-xs-12">Note if reason other <span class="required">*</span>
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<textarea class="form-control {{$errors->first('other') != '' ? 'parsley-error' : ''}}" name="other">{{ old('other') }}</textarea>
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('other') }}</li>
									</ul>
								</div>
							</div>
						</div>

						


					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" class="detail_id-onstatus" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Status Detail --}}
	<div id="undo-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.offer.undoDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Status Detail?</h4>
					</div>
					<div class="modal-body">

					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" class="detail_id-onundo" name="id" value="{{ old('id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="x_panel">
		<div class="x_title">
			<ul class="nav panel_toolbox">
				<form method="get" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					@can('pdf-offer', $index)
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pdf-offer"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download</button>
					@endcan
				</form>
	        </ul>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<form class="form-horizontal form-label-left" action="{{ route('backend.offer.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-6">
						
						<div class="form-group">
							<label for="no_document" class="control-label col-md-3 col-sm-3 col-xs-12">No Document <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="no_document" name="no_document" class="form-control {{$errors->first('no_document') != '' ? 'parsley-error' : ''}}" value="{{ old('no_document', $index->no_document) }}">
								
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_document') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name', $index->name) }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('name') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Main Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2">
									@foreach($division as $list)
									<option value="{{ $list->id }}" @if(old('division_id', $index->division_id) == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('division_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2">
									<option value="{{$index->sales_id}}">{{$index->sales->fullname}}</option>
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
							<label for="date_offer" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="date_offer" name="date_offer" class="form-control {{$errors->first('date_offer') != '' ? 'parsley-error' : ''}}" value="{{ old('date_offer', date('d F Y', strtotime($index->date_offer))) }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_offer') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="company_id" class="control-label col-md-3 col-sm-3 col-xs-12">Company <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2">
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
							<label for="brand_id" class="control-label col-md-3 col-sm-3 col-xs-12">Brand
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="brand_id" name="brand_id" class="form-control {{$errors->first('brand_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Brand" data-allow-clear="true">
									<option value=""></option>
									@foreach ($brand as $list)
										<option value="{{$list->id}}" @if(old('brand_id', $index->brand_id) == $list->id) selected @endif>{{$list->name}}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('brand_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="address" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="address" name="address" class="form-control {{$errors->first('address') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Address">
									@foreach ($address as $list)
										<option value="{{$list->address}}" @if(old('address', $index->address) == $list->address) selected @endif>{{$list->address}}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('address') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="pic_id" class="control-label col-md-3 col-sm-3 col-xs-12">PIC <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}} select2" onchange="document.getElementById('additional_phone').value = this.options[this.selectedIndex].getAttribute('data-additional_phone');" data-placeholder="Select PIC">
									<option value="" data-additional_phone=""></option>
									@foreach ($pic as $list)
										<option value="{{$list->id}}" @if(old('pic_id', $index->pic_id) == $list->id) selected @endif data-additional_phone="{{ $list->phone }}"> {{ $list->fullname }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('pic_id') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="additional_phone" class="control-label col-md-3 col-sm-3 col-xs-12">Additional Phone 
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="additional_phone" name="additional_phone" class="form-control {{$errors->first('additional_phone') != '' ? 'parsley-error' : ''}}" value="{{ old('additional_phone', $index->additional_phone) }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('additional_phone') }}</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
							<div class="form-group">
								<label for="total_price" class="control-label col-md-3 col-sm-3 col-xs-12">Total Price 
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<input type="text" id="total_price" name="total_price" class="form-control {{$errors->first('total_price') != '' ? 'parsley-error' : ''}}" value="{{ old('total_price', $index->total_price) }}">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('total_price') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">PPn 
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<label class="checkbox-inline"><input type="checkbox" value="10" name="ppn" @if(old('ppn', $index->ppn) > 0) checked @endif>PPn 10%</label>
								</div>
							</div>

							<div class="form-group">
								<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note 
								</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
									<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note', $index->note) }}</textarea>
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('note') }}</li>
									</ul>
								</div>
							</div>
							@if(!$index->done_at)
							<div class="ln_solid"></div>
							<div class="form-group">
								<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
									{{ csrf_field() }}
									<a class="btn btn-primary" href="{{ route('backend.offer') }}">Back</a>
									<button type="submit" class="btn btn-success">Submit</button>
								</div>
							</div>
							@endif
					</div>
						
				</div>
			</form>
		</div>
	</div>

	<div class="x_panel">
		<div class="x_title">

			<h2>Detail</h2>
			<ul class="nav panel_toolbox">
				<form method="post" id="action-detail" action="{{ route('backend.offer.actionDetail') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-detail">Create</button>
					<select class="form-control" name="action">
						<option value="delete">Delete</option>
						<option value="success">Success</option>
						<option value="cancel">Cancel</option>
					</select>
					<button type="submit" class="btn btn-success update-status">Apply Selected</button>
				</form>
	        </ul>
	        <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
			<table class="table table-striped table-bordered" id="datatable-detail">
				<thead>
					<tr>
						<th nowrap>
							<label class="checkbox-inline"><input type="checkbox" data-target="check-detail" class="check-all" id="check-all">S</label>
						</th>
						<th></th>
						<th>Detail Name</th>

						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>

						<td></td>
					</tr>
				</tfoot>
			</table>

			<div class="ln_solid"></div>

			<div class="container-fluid h3">
				
				<div class="row">
					<div class="col-md-8 col-xs-6 text-right">Price :</div>
					<div class="col-md-4 col-xs-6 text-right">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity'))) }}</div>
				</div>
				<div class="row">
					<div class="col-md-8 col-xs-6 text-right">PPN :</div>
					<div class="col-md-4 col-xs-6 text-right">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity')) * (($index->ppn / 100) + 1)) }}</div>
				</div>
			</div>
		</div>
	</div>


@endsection