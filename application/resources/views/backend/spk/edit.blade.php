@extends('backend.layout.master')

@section('title')
	{{$index->no_spk}} - {{$index->name}}
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
	@if( true )
	CKEDITOR.replace( 'detail' );
	CKEDITOR.replace( 'detail-edit' );
	@endif

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	$(function() {
		$('input[name=date_spk], input[name=deadline]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		var table = $('#datatable-detail').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.spk.datatablesDetail', $index) }}",
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

				{data: 'hm'},

				{data: 'deadline'},

				{data: 'action', orderable: false, searchable: false},
			],
			
			scrollY: "400px",
			dom: '<l<tr>ip>',
		});

		// Add event listener for opening and closing details
	    $('#datatable-detail tbody').on('click', 'td.details-control > button', function () {

	    	
	        var tr = $(this).closest('tr');
	        var row = table.row( tr );
	        console.log(row.data().detail);
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( row.data().detail ).show();
	            tr.addClass('shown');
	        }
	    } );

		
		var old_company = {{ old('company_id') != '' ? old('company_id') : 0 }};
		var old_brand   = {{ old('brand_id') != '' ? old('brand_id') : 0 }};
		var old_address = "{{ old('address') != '' ? old('address') : '' }}";
		var old_pic     = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		var old_estimator = {{ old('estimator_id') != '' ? old('estimator_id') : 0 }};
		var old_hm   = {{ old('hm') != '' ? old('hm') : 0 }};

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

		if(old_estimator != 0){
			$('select[name=old_hm]').prop("disabled", false);

			$.post("{{ route('backend.estimator.getDetail') }}",
	        {
	            id: $('select[name=estimator_id]').val(),
	        },
	        function(data){
	            $('select[name=hm]').empty();
				$.each(data, function(i, field){
					if (old_hm == field.id) 
					{
						$('select[name=hm]').append("<option value='"+ field.value +"' selected>Rp. "+number_format(field.value)+" - "+ field.item +"</option>");
					}
					else
					{
						$('select[name=hm]').append("<option value='"+ field.value +"'>Rp. "+number_format(field.value)+" - "+ field.item +"</option>");
					}
				});
				$('select[name=hm]').val(old_hm).trigger('change');
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

		$(document).on('change','select[name=estimator_id]', function(){
			if($(this).val() == ''){
				$('select[name=hm]').val('').trigger('change');
				$('select[name=hm]').prop("disabled", true);
			}
			else{
				$('select[name=hm]').prop("disabled", false);

				$.post("{{ route('backend.estimator.getDetail') }}",
		        {
		            id: $('select[name=estimator_id]').val(),
		        },

		        function(data){
		            $('select[name=hm]').empty();
					$.each(data, function(i, field){
						$('select[name=hm]').append("<option value='"+ field.value +"'>Rp. "+number_format(field.value)+" - "+ field.item +"</option>");
					});
					$('select[name=hm]').val('').trigger('change');
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
			$('#edit-detail *[name=division_id]').val($(this).data('division_id')).trigger('change');
			$('#edit-detail *[name=source][value='+$(this).data('source')+']').prop('checked', true);
			$('#edit-detail *[name=profitable][value='+$(this).data('profitable')+']').prop('checked', true);

			$('#edit-detail *[name=deadline]').val($(this).data('deadline'));
			$('#edit-detail *[name=hm]').val($(this).data('hm'));
			$('#edit-detail *[name=he]').val($(this).data('he'));
			$('#edit-detail *[name=hj]').val($(this).data('hj'));

			$.post("{{ route('backend.spk.getDetail') }}",
		    {
		        production_id: $(this).data('id'),
		    },
		    function(data){
		    	CKEDITOR.instances['detail-edit'].setData(data);
		        $('#edit-detail *[name=detail]').val(data);
		    });
		});

		$('#datatable-detail').on('click', '.repair-detail', function(){
			$('.repair-onrepair').val($(this).data('production_id'));
		});

		$('#datatable-detail').on('click', '.delete-detail', function(){
			$('.production_id-ondelete').val($(this).data('id'));
		});

		$('#datatable-detail').on('click', '.history-production', function(){
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

		@if(Session::has('create-detail-error'))
		$('#create-detail').modal('show');
		@endif
		@if(Session::has('edit-detail-error'))
		$('#edit-detail').modal('show');
		@endif
		@if(Session::has('repair-detail-error'))
		$('#repair-detail').modal('show');
		@endif
		@if(Session::has('finish-spk-error'))
		$('#finish-spk').modal('show');
		@endif
		@if(Session::has('pdf-spk-error'))
		$('#pdf-spk').modal('show');
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
	.number-format{
		text-align: right;
		white-space: nowrap;
	}
</style>
@endsection

@section('content')

	<h1>{{$index->no_spk}} - {{$index->name}}</h1>
	@if($index->finish_spk_at)
		<h2>Finish : {{ $index->finish_spk_at_readable }}</h2>
	@endif

	{{-- Done SPK --}}
	<div id="finish-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.finish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Finish SPK?</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="quality" class="control-label col-md-3 col-sm-3 col-xs-12"><span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="quality-yes" name="quality" value="1" @if(old('quality') != '' && old('quality') == 1) checked @endif>Yes</label> 
								<label class="radio-inline"><input type="radio" id="quality-no" name="quality" value="0" @if(old('quality') != '' && old('quality') == 0) checked @endif>No</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('quality') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="comment" class="control-label col-md-3 col-sm-3 col-xs-12">Comment <span class="required">* if quality no</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<textarea class="form-control {{$errors->first('comment') != '' ? 'parsley-error' : ''}}" name="comment">{{ old('comment') }}</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('comment') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="spk_id-ondone" value="{{ $index->id }}">
						<button type="submit" class="btn btn-success">Finish</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Undo Done SPK --}}
	<div id="undoFinish-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.undoFinish') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Undo Finish SPK?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="spk_id-onundoDone" value="{{ $index->id }}">
						<button type="submit" class="btn btn-warning">Undo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Create Detail --}}
	<div id="create-detail" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.storeDetail') }}" method="post" enctype="multipart/form-data">
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
									<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Division">
											<option value=""></option>
											@foreach($division as $list)
											<option value="{{ $list->id }}" @if(old('division_id') == $list->id) selected @endif>{{ $list->name }}</option>
											@endforeach
										</select>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('division_id') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="source" class="control-label col-md-3 col-sm-3 col-xs-12">Source <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<label class="radio-inline"><input type="radio" id="source-INSOURCE" name="source" value="INSOURCE" @if(old('source') != '' && old('source') == 'INSOURCE') checked @endif>Insource</label> 
										<label class="radio-inline"><input type="radio" id="source-OUTSOURCE" name="source" value="OUTSOURCE" @if(old('source') != '' && old('source') == 'OUTSOURCE') checked @endif>Outsource</label>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('source') }}</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="deadline" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="deadline" name="deadline" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('deadline') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('deadline') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="estimator_id" class="control-label col-md-3 col-sm-3 col-xs-12">Estimator <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<select id="estimator_id" name="estimator_id" class="form-control {{$errors->first('estimator_id') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Estimator">
											<option value=""></option>
											@foreach($estimator as $list)
											<option value="{{ $list->id }}" @if(old('estimator_id') == $list->id) selected @endif>{{ $list->no_estimator }} - {{ $list->name }}</option>
											@endforeach
										</select>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('estimator_id') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="hm" class="control-label col-md-3 col-sm-3 col-xs-12">Modal Price
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<select id="hm" name="hm" class="form-control {{$errors->first('hm') != '' ? 'parsley-error' : ''}} select2full" data-placeholder="Select Modal Price">
											<option value=""></option>
										</select>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('brand_id') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="hj" class="control-label col-md-3 col-sm-3 col-xs-12">Sell Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="hj" name="hj" class="form-control {{$errors->first('hj') != '' ? 'parsley-error' : ''}}" value="{{ old('hj') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('hj') }}</li>
										</ul>
									</div>

								</div>

								<div class="form-group">
									<label for="profitable" class="control-label col-md-3 col-sm-3 col-xs-12">Profitable <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<label class="radio-inline"><input type="radio" id="profitable" class="profitable-onedit" name="profitable" value="1" @if(old('profitable') == '1') checked @endif>Profitable</label>
										<label class="radio-inline"><input type="radio" id="profitable" class="profitable-off-onedit" name="profitable" value="0" @if(old('profitable') == '0') checked @endif>Not Profitable</label> 
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('profitable') }}</li>
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
										<table style="width: 100%; border-collapse: collapse;" border="1">
											<tr>
												<th>detail</th>
												<th>Size</th>
												<th>Machine</th>
												<th>Quantity</th>
												<th>Finishing</th>
											</tr>
											<tr>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
											</tr>
										</table>
									@endif
								</textarea>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('detail') }}</li>
								</ul>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="spk_id" value="{{ $index->id }}">
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
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.updateDetail') }}" method="post" enctype="multipart/form-data">
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
										<input type="number" id="quantity-edit" name="quantity" class="form-control {{$errors->first('quantity') != '' ? 'parsley-error' : ''}}" value="{{ old('quantity') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('quantity') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="division_id-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<select id="division_id-edit" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2full">
											@foreach($division as $list)
											<option value="{{ $list->id }}" @if(old('division_id') == $list->id) selected @endif>{{ $list->name }}</option>
											@endforeach
										</select>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('division_id') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="source-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Source <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<label class="radio-inline"><input type="radio" id="source-INSOURCE" name="source" value="INSOURCE" @if(old('source') == 'INSOURCE') checked @endif>Insource</label> 
										<label class="radio-inline"><input type="radio" id="source-OUTSOURCE" name="source" value="OUTSOURCE" @if(old('source') == 'OUTSOURCE') checked @endif>Outsource</label>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('source') }}</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="deadline-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Deadline <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="deadline-edit" name="deadline" class="form-control {{$errors->first('deadline') != '' ? 'parsley-error' : ''}}" value="{{ old('deadline') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('deadline') }}</li>
										</ul>
									</div>
								</div>

								<div class="form-group">
									<label for="hm-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Modal Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="hm-edit" name="hm" class="form-control {{$errors->first('hm') != '' ? 'parsley-error' : ''}}" value="{{ old('hm') }}" @if(!Auth::user()->can('editHM-spk')) disabled @endif>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('hm') }}</li>
										</ul>
									</div>
								</div>

								@can('editHE-spk')
								<div class="form-group">
									<label for="he-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Expo Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="he-edit" name="he" class="form-control {{$errors->first('he') != '' ? 'parsley-error' : ''}}" value="{{ old('he') }}">
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('he') }}</li>
										</ul>
									</div>
								</div>
								@endcan

								<div class="form-group">
									<label for="hj-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Sell Price <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">
										<input type="text" id="hj-edit" name="hj" class="form-control {{$errors->first('hj') != '' ? 'parsley-error' : ''}}" value="{{ old('hj') }}" @if(!Auth::user()->can('editHJ-spk')) disabled @endif>
										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('hj') }}</li>
										</ul>
									</div>

								</div>

								<div class="form-group">
									<label for="profitable" class="control-label col-md-3 col-sm-3 col-xs-12">Profitable <span class="required">*</span>
									</label>
									<div class="col-md-9 col-sm-9 col-xs-12">

										<label class="radio-inline"><input type="radio" id="profitable" class="profitable-onedit" name="profitable" value="1" @if(old('profitable', $index->profitable) == '1') checked @endif>Profitable</label> 

										<label class="radio-inline"><input type="radio" id="profitable" class="profitable-off-onedit" name="profitable" value="0" @if(old('profitable', $index->profitable) == '0') checked @endif>Not Profitable</label> 

										<ul class="parsley-errors-list filled">
											<li class="parsley-required">{{ $errors->first('profitable') }}</li>
										</ul>
									</div>
								</div>

							</div>
						</div>

						<div class="form-group">
								<textarea id="detail-edit" name="detail" class="form-control detail_detail-onedit {{$errors->first('detail') != '' ? 'parsley-error' : ''}}">
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
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.deleteDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Delete detail?</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="production_id-ondelete" value="">
						<button type="submit" class="btn btn-danger">Delete</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- Repair Detail --}}
	<div id="repair-detail" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.repairDetail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Repair Detail</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="repair" class="control-label col-md-3 col-sm-3 col-xs-12">Total item to repair <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="repair" name="repair" class="form-control {{$errors->first('repair') != '' ? 'parsley-error' : ''}}" value="{{ old('repair') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('repair') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="production_id" class="repair-onrepair" value="{{ old('production_id') }}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	{{-- SPK PDF --}}
	<div id="pdf-spk" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.spk.pdf') }}" method="post" enctype="multipart/form-data">
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
							<label for="type" class="control-label col-md-3 col-sm-3 col-xs-12">Type <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="type-production" name="type" value="production" @if(old('type', 'production') == 'production') checked @endif>Production</label> 
								<label class="radio-inline"><input type="radio" id="type-purchasing" name="type" value="purchasing" @if(old('type', 'production') == 'purchasing') checked @endif>Purchasing</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('type') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="hide_client" class="control-label col-md-3 col-sm-3 col-xs-12">Hide Client <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="radio-inline"><input type="radio" id="hide_client-on" name="hide_client" value="on" @if(old('hide_client', 'off') == 'on') checked @endif>On</label> 
								<label class="radio-inline"><input type="radio" id="hide_client-off" name="hide_client" value="off" @if(old('hide_client', 'off') == 'off') checked @endif>Off</label>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('hide_client') }}</li>
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

	<div class="x_panel">
		<div class="x_title">
			<ul class="nav panel_toolbox">
				<form method="get" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					@if(Auth::user()->can('undo-spk', $index) && $index->finish_spk_at)
					<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#undoFinish-spk">
						<i class="fa fa-undo" aria-hidden="true"></i> Undo Finish
					</button>
					@endif
					
					@cannot('finish-spk', $index)
					<button type="button" class="btn btn-success" disabled>
						<i class="fa fa-flag-checkered" aria-hidden="true"></i> Finish
					</button>
					@endcannot
					
					@can('finish-spk', $index)
					<button type="button" class="btn btn-success" data-toggle="modal" data-target="#finish-spk">
						<i class="fa fa-flag-checkered" aria-hidden="true"></i> Finish
					</button>
					@endcan
					
					@can('pdf-spk', $index)
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pdf-spk"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Download</button>
					@endif
				</form>
	        </ul>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<form class="form-horizontal form-label-left" action="{{ route('backend.spk.update', $index) }}" method="post" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-6">
						
						<div class="form-group">
							<label for="no_spk" class="control-label col-md-3 col-sm-3 col-xs-12">SPK <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="no_spk" name="no_spk" class="form-control {{$errors->first('no_spk') != '' ? 'parsley-error' : ''}}" value="{{ old('no_spk', $index->no_spk) }}">
								
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('no_spk') }}</li>
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
							<label for="main_division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Main Division <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select id="main_division_id" name="main_division_id" class="form-control {{$errors->first('main_division_id') != '' ? 'parsley-error' : ''}} select2">
									@foreach($division as $list)
									<option value="{{ $list->id }}" @if(old('main_division_id', $index->main_division_id) == $list->id) selected @endif>{{ $list->name }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('main_division_id') }}</li>
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
							<label for="date_spk" class="control-label col-md-3 col-sm-3 col-xs-12">Date <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" id="date_spk" name="date_spk" class="form-control {{$errors->first('date_spk') != '' ? 'parsley-error' : ''}}" value="{{ old('date_spk', date('d F Y', strtotime($index->date_spk))) }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_spk') }}</li>
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
							<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">PPn 
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline"><input type="checkbox" value="10" name="ppn" @if(old('ppn', $index->ppn) > 0) checked @endif>PPn 10%</label>
							</div>
						</div>

						<div class="form-group">
							<label for="do_transaction" class="control-label col-md-3 col-sm-3 col-xs-12" title="Tanda bisa melakukan transaksi sebelum proyek selesai">Do Transaction 
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<label class="checkbox-inline" title="Tanda bisa melakukan transaksi sebelum proyek selesai"><input type="checkbox" name="do_transaction" @if(old('do_transaction', $index->do_transaction) == 1) checked @endif>Yes</label>
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

						<div class="ln_solid"></div>

						<div class="form-group">
							<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
								{{ csrf_field() }}
								<a class="btn btn-primary" href="{{ route('backend.spk') }}">Back</a>
								<button type="submit" class="btn btn-success">Submit</button>
							</div>
						</div>
					</div>
						
				</div>
			</form>
		</div>
	</div>

	<div class="x_panel">
		<div class="x_title">

			<h2>Detail</h2>
			<ul class="nav panel_toolbox">
				<form method="post" id="action-detail" action="{{ route('backend.spk.actionDetail') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
					<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-detail">Create</button>
					<select class="form-control" name="action">
						<option value="delete">Delete</option>
					</select>
					<button type="submit" class="btn btn-success">Apply Selected</button>
				</form>
	        </ul>
	        <div class="clearfix"></div>
        </div>
        <div class="x_content table-responsive">
			<table class="table table-bordered" id="datatable-detail">
				<thead>
					<tr role="row">
						<th nowrap>
							<label class="checkbox-inline"><input type="checkbox" data-target="check-detail" class="check-all" id="check-all">S</label>
						</th>

						<th></th>

						<th>Detail Name</th>
						<th>Value</th>
						<th>Stat Production</th>

						<th>Action</th>
					</tr>
				</thead>
				
			</table>

			<div class="ln_solid"></div>

			<div class="container-fluid h3">
				<div class="row">
					<div class="col-md-8 col-xs-6 text-right">HM :</div>
					<div class="col-md-4 col-xs-6 text-right">Rp. {{ number_format($index->total_hm) }}</div>
				</div>
				<div class="row">
					<div class="col-md-8 col-xs-6 text-right">HJ :</div>
					<div class="col-md-4 col-xs-6 text-right">Rp. {{ number_format($index->total_hj) }}</div>
				</div>
				<div class="row">
					<div class="col-md-8 col-xs-6 text-right">PPN :</div>
					<div class="col-md-4 col-xs-6 text-right">Rp. {{ number_format($index->total_ppn) }}</div>
				</div>
			</div>
		</div>
	</div>


@endsection