@extends('backend.layout.master')

@section('title')
	Create Offer
@endsection

@section('script')
<script src="{{ asset('backend/js/moment/moment.min.js') }}"></script>
<script src="{{ asset('backend/js/datepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'note' );
	$(function() {
		$('input[name=date_offer]').daterangepicker({
		    singleDatePicker: true,
		    showDropdowns: true,
		    format: 'DD MMMM YYYY'
		});

		$(".btn-generate").click(function(){
        	$.post("{{ route('backend.offer.getDocument') }}",
	        {
	            company_id: $('select[name=company_id]').val(),
	            sales_id: $('select[name=sales_id]').val(),
	            date: $('input[name=date]').val(),
	        },
	        function(data){
	        	if(data.error)
	        	{
	        		alert(data.error);
	        	}
	        	else
	        	{
	        		$('input[name=no_document]').val(data);
	        	}
	            
	        });
	    });

		$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);

		old_brand = {{ old('brand_id') != '' ? old('brand_id') : 0 }};
		old_address = "{{ old('address') != '' ? old('address') : '' }}";
		old_pic = {{ old('pic_id') != '' ? old('pic_id') : 0 }};

		if($('select[name=company_id]').val() == ''){
			$('select[name=brand_id], select[name=address], select[name=pic_id]').val('').trigger('change');
			$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", true);
		}
		else{
			$('select[name=brand_id], select[name=address], select[name=pic_id]').prop("disabled", false);

			$.post("{{ route('backend.company.getDetail') }}",
			{
				id : $('select[name=company_id]').val(),
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
					id : $(this).val(),
				},
		        function(data){
		            $('select[name=brand_id]').empty();
					$.each(data.brands, function(i, field){
						console.log(field.id)
						$('select[name=brand_id]').append("<option value='"+ field.id +"'>"+ field.name+"</option>");
					});
					$('select[name=brand_id]').val('').trigger('change');

		            $('select[name=address]').empty();
					$.each(data.addresses, function(i, field){
						$('select[name=address]').append("<option value='"+ field.address +"'>"+ field.address+"</option>");
					});
					$('select[name=address]').val('').trigger('change');

		            $('select[name=pic_id]').empty();
					$.each(data["pic"], function(i, field){
						$('select[name=pic_id]').append("<option value='"+ field.id +"' data-additional_phone='"+field.phone+"'>"+ field.first_name +" "+field.last_name+"</option>");
					});
					$('select[name=pic_id]').val('').trigger('change');
		        });
			}
		});

		$('select[name=address]').on("select2:unselect", function (e) { $('input[name=address]').val(''); });
		$('select[name=pic_id]').on("select2:unselect", function (e) { $('input[name=additional_phone]').val(''); });
	});
</script>

@endsection

@section('content')

	<h1>Create Offer</h1>
	<div class="x_panel">
	<form class="form-horizontal form-label-left" action="{{ route('backend.offer.store') }}" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="no_document" class="control-label col-md-3 col-sm-3 col-xs-12">No Document <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<div class="input-group">
							<input type="text" id="no_document" name="no_document" class="form-control {{$errors->first('no_document') != '' ? 'parsley-error' : ''}}" value="{{ old('no_document') }}">
							<span class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-generate">Regenerate</button>
                            </span>
						</div>
						
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('no_document') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name Project <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') }}">
						<ul class="parsley-errors-list filled">
							<li class="parsley-required">{{ $errors->first('name') }}</li>
						</ul>
					</div>
				</div>

				<div class="form-group">
					<label for="division_id" class="control-label col-md-3 col-sm-3 col-xs-12">Division <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="division_id" name="division_id" class="form-control {{$errors->first('division_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Division">
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
					<label for="sales_id" class="control-label col-md-3 col-sm-3 col-xs-12">Sales <span class="required">*</span>
					</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<select id="sales_id" name="sales_id" class="form-control {{$errors->first('sales_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Sales">
							<option value="{{ (in_array(Auth::id(), getConfigValue('sales_user', true)) || in_array(Auth::user()->position_id, getConfigValue('sales_position', true)) ? Auth::id() : '') }}">Select Sales</option>
							@foreach($sales as $list)
							<option value="{{ $list->id }}" @if(old('sales_id') == $list->id) selected @endif>{{ $list->fullname }}</option>
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
						<input type="text" id="date_offer" name="date_offer" class="form-control {{$errors->first('date_offer') != '' ? 'parsley-error' : ''}}" value="{{ old('date_offer') }}">
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
						<select id="company_id" name="company_id" class="form-control {{$errors->first('company_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select Company">
							<option value=""></option>
							@foreach($company as $list)
							<option value="{{ $list->id }}" @if(old('company_id') == $list->id) selected @endif>{{ $list->name }}</option>
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
							<option value=""></option>
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
						<select id="pic_id" name="pic_id" class="form-control {{$errors->first('pic_id') != '' ? 'parsley-error' : ''}} select2" data-placeholder="Select PIC" onchange="document.getElementById('additional_phone').value = this.options[this.selectedIndex].getAttribute('data-additional_phone');">
							<option value="" data-additional_phone=""></option>
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
						<input type="text" id="additional_phone" name="additional_phone" class="form-control {{$errors->first('additional_phone') != '' ? 'parsley-error' : ''}}" value="{{ old('additional_phone') }}">
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
							<input type="text" id="total_price" name="total_price" class="form-control {{$errors->first('total_price') != '' ? 'parsley-error' : ''}}" value="{{ old('total_price', 0) }}">
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('total_price') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="ppn" class="control-label col-md-3 col-sm-3 col-xs-12">PPn 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<label class="checkbox-inline"><input type="checkbox" value="10" name="ppn" @if(old('ppn') == "10") checked @endif>PPn 10%</label>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('ppn') }}</li>
							</ul>
						</div>
					</div>

					<div class="form-group">
						<label for="note" class="control-label col-md-3 col-sm-3 col-xs-12">Note 
						</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<textarea id="note" name="note" class="form-control {{$errors->first('note') != '' ? 'parsley-error' : ''}}">{{ old('note', '
								
								<ol>
									<li>
										Detail Keterangan packing
										<ol>
											<li>Packaging barang sesuai dengan keterangan di deskripsi barang dan jika ada perubahan maka akan disesuaikan dengan biaya yang diperlukan.</li>
										</ol>
									</li>
									<li>
										Pembayaran
										<ol>
											<li>Pembayaran di muka (DP) adalah sebesar 50% dari nilai kontrak.</li>
											<li>Pelunasan adalah 30 hari kalender setelah deadline projek yang disepakati bersama di awal.</li>
											<li>Jika pengiriman belum terelasasi disebabkan karena pemberi po, maka proses pembayaran pelunasan tetap sesuai waktu 30 setelah deadline barang selesai.</li>
										</ol>
									</li>
									<li>
										Pengiriman
										<ol>
											<li>Pengiriman gratis ke 1 drop point di  seluruh area Jakarta, Tangerang, Depok, Bekasi.</li>
											<li>Pengiriman area Jakarta di atas 1 drop point akan dikenakan biaya (biaya akan diberitahukan selanjutnya).</li>
											<li>Pengiriman area luar Jakarta akan dikenakan biaya (biaya akan diberitahukan selanjutnya).</li>
										</ol>
									</li>
									<li>
										Penyimpanan
										<ol>
											<li>Jangka waktu penyimpanan di gudang digindo adalah maksimum 60 hari kalender sejak deadline barang selesai.</li>
											<li>Penyimpanan di atas masa 60 hari, maka selanjutnya akan dikenakan biaya sewa yang nilainya akan disesuaikan dengan jumlah dan dimensi barang.</li>
										</ol>
									</li>
									<li>
										Pembatalan sepihak
										<ol>
											<li>Pembatalan oleh pihak pertama (pemberi PO) yang dilakukan maksimal 3 hari setelah keluar PO maka wajib membayar 50% dari nilai PO kepada pihak kedua.</li>
											<li>Pembatalan oleh pihak pertama (pemberi PO) yang dilakukan lebih dari 3 hari setelah keluar PO maka wajib membayar 100% dari nilai PO kepada pihak kedua.</li>
										</ol>
									</li>
								</ol>

								<p>Poin tambahan di existing.</p>
								<ol>
									<li>Penawaran berlaku 14 hari sejak tanggal terbit.</li>
									<li>Harga tercantum di PO berlaku 30 hari sejak tanggal terbit, dan apabila proses produksi tidak bisa dilaksanakan pihak penerima PO oleh karena sebab yang bersangkutan dengan pihak pemberi PO, maka nilai kontrak dan hal2 yang sehubungan di dalamnya akan dapat direview kembali.</li>
								</ol>

								') }}</textarea
							>
							<ul class="parsley-errors-list filled">
								<li class="parsley-required">{{ $errors->first('note') }}</li>
							</ul>
						</div>
					</div>

					<div class="ln_solid"></div>
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
							{{ csrf_field() }}
							<a class="btn btn-primary" href="{{ route('backend.offer') }}">Cancel</a>
							<button type="submit" class="btn btn-success">Submit</button>
						</div>
					</div>
			</div>
				
		</div>
	</form>
	</div>

@endsection