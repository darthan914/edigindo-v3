@extends('backend.layout.master')

@section('title')
	Edit Company
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAel9fAfMQ3xomX3v_iLWJSkNUE3TSkLI&libraries=places"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/geocomplete/1.7.0/jquery.geocomplete.min.js"></script>

<script src="{{ asset('backend/vendors/ckeditor/ckeditor.js') }}"></script>

<script type="text/javascript">
	// CKEDITOR.replace( 'text-email' );
	$(function() {
		$('button[data-dismiss=modal]').click(function(event) {
			$(".parsley-required").empty();
			$("*").removeClass('parsley-error');
		});

		$('#datatable-pic').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesPic', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'fullname'},
				{data: 'gender'},
				{data: 'position'},
				{data: 'phone'},
				{data: 'email'},
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
			dom: '<l<tr>ip>',

					
		});


		$('#datatable-pic').on('click', '.edit-pic', function(){
			$('#edit-pic *[name=id]').val($(this).data('id'));
			$('#edit-pic *[name=first_name]').val($(this).data('first_name'));
			$('#edit-pic *[name=last_name]').val($(this).data('last_name'));
			$('#edit-pic *[name=gender][value='+$(this).data('gender')+']').prop('checked', true);
			$('#edit-pic *[name=position]').val($(this).data('position'));
			$('#edit-pic *[name=phone]').val($(this).data('phone'));
			$('#edit-pic *[name=email]').val($(this).data('email'));
		});

		$('#datatable-pic').on('click', '.delete-pic', function(){
			$('#delete-pic *[name=id]').val($(this).data('id'));
		});

		$('#datatable-pic').on('click', '.whatsapp-pic', function(){
			$('#whatsapp-pic *[name=id]').val($(this).data('id'));
		});

		$('#datatable-pic').on('click', '.email-pic', function(){
			$('#email-pic *[name=id]').val($(this).data('id'));
		});

		$('#datatable-address').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesAddress', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'address'},
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
			dom: '<l<tr>ip>',

		});

		$("textarea[name=address]").change(function(event) {
			$("input[name=new_autosearch_address]").geocomplete("find", $(this).val());
		});

		$("input[name=new_autosearch_address]").geocomplete({
			map: "#new-map-location",
			markerOptions: {
				draggable: true
			},
			details: "form",
			detailsAttribute: "data-geo"
		}).bind("geocode:dragged", function(event, result){
			var geocoder = new google.maps.Geocoder();
			var latlng = new google.maps.LatLng(result.lat(), result.lng());
			geocoder.geocode({
				'latLng': latlng
			}, function (results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					if (results[1]) {
						$("input[name=latitude]").val(result.lat());
						$("input[name=longitude]").val(result.lng());
					} else {
						alert('No results found');
					}
				} else {
					alert('Geocoder failed due to: ' + status);
				}
			});
		});

		$("input[name=update_autosearch_address]").geocomplete({
			map: "#update-map-location",
			markerOptions: {
				draggable: true
			},
			details: "form",
			detailsAttribute: "data-geo"
		}).bind("geocode:dragged", function(event, result){
			var geocoder = new google.maps.Geocoder();
			var latlng = new google.maps.LatLng(result.lat(), result.lng());
			geocoder.geocode({
				'latLng': latlng
			}, function (results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					if (results[1]) {
						$("input[name=latitude]").val(result.lat());
						$("input[name=longitude]").val(result.lng());
					} else {
						alert('No results found');
					}
				} else {
					alert('Geocoder failed due to: ' + status);
				}
			});
		});


		$('#datatable-address').on('click', '.edit-address', function(){
			$('#edit-address *[name=id]').val($(this).data('id'));
			$('#edit-address *[name=address]').val($(this).data('address'));
			$('#edit-address *[name=latitude]').val($(this).data('latitude'));
			$('#edit-address *[name=longitude]').val($(this).data('longitude'));

			var lat_and_long = $(this).data('latitude') + ", " + $(this).data('longitude');
			var update_map = $("*[name=update_autosearch_address]").geocomplete("find", lat_and_long);
		});

		$('#datatable-address').on('click', '.delete-address', function(){
			$('#delete-address *[name=id]').val($(this).data('id'));
		});

		$('#datatable-brand').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: "{{ route('backend.company.datatablesBrand', $index) }}",
				type: "POST",
			},
			columns: [
				{data: 'check', orderable: false, searchable: false},
				{data: 'name'},
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
			dom: '<l<tr>ip>',

					
		});

		$('#datatable-brand').on('click', '.edit-brand', function(){
			$('#edit-brand *[name=id]').val($(this).data('id'));
			$('#edit-brand *[name=name]').val($(this).data('name'));
		});

		$('#datatable-brand').on('click', '.delete-brand', function(){
			$('#delete-brand *[name=id]').val($(this).data('id'));
		});

		$('#datatable-pic, #datatable-address, #datatable-brand').on('click', '.check-all', function(){
			if ($(this).is(':checked'))
			{
				$('.' + $(this).attr('data-target')).prop('checked', true);
			}
			else
			{
				$('.' + $(this).attr('data-target')).prop('checked', false);
			}
		});

		$('.tab-active').click(function(event) {
			$('*[name=tab]').val($(this).data('id'));
		});

		

		@if(Session::has('create-pic-error'))
		$('#create-pic').modal('show');
		@endif
		@if(Session::has('edit-pic-error'))
		$('#edit-pic').modal('show');
		@endif

		@if(Session::has('create-address-error'))
		$('#create-address').modal('show');
		@endif
		@if(Session::has('edit-address-error'))
		$('#edit-address').modal('show');
		@endif

		@if(Session::has('create-name-error'))
		$('#create-name').modal('show');
		@endif
		@if(Session::has('edit-name-error'))
		$('#edit-name').modal('show');
		@endif

		
	});
</script>
@endsection

@section('css')
<link href="{{ asset('backend/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css') }}" rel="stylesheet">
<style type="text/css">
	#new-map-location, #update-map-location {
		width: : 100%;
		height: 15em;
	}

	.pac-container {
		z-index: 1051 !important;
	}
</style>
@endsection

@section('content')
	
	

	<h1>Edit Company</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="{{ route('backend.company.update', ['id' => $index->id]) }}" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Name <span class="required">*</span>
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="name" name="name" class="form-control {{$errors->first('name') != '' ? 'parsley-error' : ''}}" value="{{ old('name') == '' ? $index->name : old('name') }}" onchange="autoUrl(this.id, 'slug');">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="short_name" class="control-label col-md-3 col-sm-3 col-xs-12">Short Name
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="short_name" name="short_name" class="form-control {{$errors->first('short_name') != '' ? 'parsley-error' : ''}}" value="{{ old('short_name') == '' ? $index->short_name : old('short_name') }}" onchange="document.getElementById('short_name').value = document.getElementById('name').value.substring(0,5)">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('short_name') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="phone" name="phone" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}" value="{{ old('phone') == '' ? $index->phone : old('phone') }}" onchange="autoUrl(this.id, 'slug');">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('phone') }}</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label for="fax" class="control-label col-md-3 col-sm-3 col-xs-12">Fax
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="fax" name="fax" class="form-control {{$errors->first('fax') != '' ? 'parsley-error' : ''}}" value="{{ old('fax') == '' ? $index->fax : old('fax') }}" onchange="autoUrl(this.id, 'slug');">
					<ul class="parsley-errors-list filled">
						<li class="parsley-required">{{ $errors->first('fax') }}</li>
					</ul>
				</div>
			</div>
			
			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					{{ csrf_field() }}
					<a class="btn btn-primary" href="{{ route('backend.company') }}">Back</a>
					@can('update-company')
					<button type="submit" class="btn btn-success">Submit</button>
					@endcan
				</div>
			</div>
		</form>
	</div>

	<div class="x_panel">

		<div class="" role="tabpanel" data-example-id="togglable-tabs">
			<ul id="dashboardTab" class="nav nav-tabs bar_tabs" role="tablist">
				<li class="{{ $request->tab === 'pic' || $request->tab == '' ? 'active' : ''}}"><a href="#pic" data-toggle="tab" class="tab-active" data-id="pic">PIC</a>
				</li>
				<li class="{{ $request->tab === 'brand' ? 'active' : ''}}"><a href="#brand" data-toggle="tab" class="tab-active" data-id="brand">Brand</a>
				</li>
				<li class="{{ $request->tab === 'address' ? 'active' : ''}}"><a href="#address" data-toggle="tab" class="tab-active" data-id="address">Address</a>
				</li>

			</ul>
			<div class="tab-content">
				<div class="tab-pane fade {{ $request->tab === 'pic' || $request->tab == '' ? 'active in' : ''}}" id="pic" >
					{{-- PIC block --}}

					{{-- Create PIC --}}
					<div id="create-pic" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.storePic') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Create PIC</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="first_name" class="control-label col-md-3 col-sm-3 col-xs-12">First Name <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="first_name" name="first_name" class="form-control {{$errors->first('first_name') != '' ? 'parsley-error' : ''}}" value="{{ old('first_name') }}" onchange="document.getElementById('last_name').value = document.getElementById('first_name').value.substring(0,5)">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('first_name') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="last_name" class="control-label col-md-3 col-sm-3 col-xs-12">Last Name
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="last_name" name="last_name" class="form-control {{$errors->first('last_name') != '' ? 'parsley-error' : ''}}" value="{{ old('last_name') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('last_name') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="gender" class="control-label col-md-3 col-sm-3 col-xs-12">Gender <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<label class="radio-inline"><input type="radio" id="gender-male" name="gender" value="M" @if(old('gender') != '' && old('gender') == 'M') checked @endif>Male</label> 
												<label class="radio-inline"><input type="radio" id="gender-female" name="gender" value="F" @if(old('gender') != '' && old('gender') == 'F') checked @endif>Female</label>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('gender') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="position" class="control-label col-md-3 col-sm-3 col-xs-12">Position
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="position" name="position" class="form-control {{$errors->first('position') != '' ? 'parsley-error' : ''}}" value="{{ old('position') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('position') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="phone" name="phone" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}" value="{{ old('phone') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('phone') }}</li>
												</ul>
											</div>
										</div>

										

										<div class="form-group">
											<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="email" id="email" name="email" class="form-control {{$errors->first('email') != '' ? 'parsley-error' : ''}}" value="{{ old('email') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('email') }}</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="company_id" value="{{ $index->id }}">
										<button type="submit" class="btn btn-success">Submit</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Edit PIC --}}
					<div id="edit-pic" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.updatePic') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Edit PIC</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="first_name-edit" class="control-label col-md-3 col-sm-3 col-xs-12">First Name <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="first_name-edit" name="first_name" class="form-control {{$errors->first('first_name') != '' ? 'parsley-error' : ''}}" value="{{ old('first_name') }}" onchange="document.getElementById('last_name-edit').value = document.getElementById('first_name-edit').value.substring(0,5)">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('first_name') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="last_name-edit" class="control-label col-md-3 col-sm-3 col-xs-12">Last Name
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="last_name-edit" name="last_name" class="form-control {{$errors->first('last_name') != '' ? 'parsley-error' : ''}}" value="{{ old('last_name') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('last_name') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="gender" class="control-label col-md-3 col-sm-3 col-xs-12">Gender <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<label class="radio-inline"><input type="radio" id="gender-male" name="gender" value="M" @if(old('gender') != '' && old('gender') == 'M') checked @endif>Male</label> 
												<label class="radio-inline"><input type="radio" id="gender-female" name="gender" value="F" @if(old('gender') != '' && old('gender') == 'F') checked @endif>Female</label>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('gender') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="position" class="control-label col-md-3 col-sm-3 col-xs-12">Position
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="position" name="position" class="form-control {{$errors->first('position') != '' ? 'parsley-error' : ''}}" value="{{ old('position') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('position') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="phone" name="phone" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}" value="{{ old('phone') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('phone') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="email" id="email" name="email" class="form-control {{$errors->first('email') != '' ? 'parsley-error' : ''}}" value="{{ old('email') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('email') }}</li>
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

					{{-- Delete PIC --}}
					<div id="delete-pic" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.deletePic') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Delete PIC?</h4>
									</div>
									<div class="modal-body">
									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="id" class="pic_id-ondelete" value="{{old('id')}}">
										<button type="submit" class="btn btn-danger">Delete</button>
										<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					@can('send-company')
					{{-- Whatsapp PIC --}}
					<div id="whatsapp-pic" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.whatsappPic') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Whatsapp PIC</h4>
									</div>
									<div class="modal-body">
										

										<div class="form-group">
											<label for="text" class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<textarea type="text" id="text" name="text" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}">{{ old('text') }}</textarea>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('text') }}</li>
												</ul>
											</div>
										</div>

									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="id" value="{{old('id')}}">
										<button type="submit" class="btn btn-success">Send</button>
										<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Email PIC --}}
					<div id="email-pic" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.emailPic') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Whatsapp PIC</h4>
									</div>
									<div class="modal-body">

										<div class="form-group">
											<label for="subject" class="control-label col-md-3 col-sm-3 col-xs-12">Subject
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="subject" name="subject" class="form-control {{$errors->first('subject') != '' ? 'parsley-error' : ''}}" value="{{ old('subject') }}">
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('subject') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="text" class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<textarea type="text" id="text-email" name="text" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}">{{ old('text') }}</textarea>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('text') }}</li>
												</ul>
											</div>
										</div>

									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="id" value="{{old('id')}}">
										<button type="submit" class="btn btn-success">Send</button>
										<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>
					@endcan


					@can('update-company')
					<form method="post" id="action-pic" action="{{ route('backend.company.actionPic') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-pic">Create</button>
						<select class="form-control" name="action">
							<!-- <option value="enable">Enable</option>
							<option value="disable">Disable</option> -->
							<option value="delete">Delete</option>
						</select>
						<button type="submit" class="btn btn-success">Apply Selected</button>
					</form>
					@endcan

					<div class="ln_solid"></div>

					<table class="table table-striped table-bordered" id="datatable-pic">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-pic" class="check-all" id="check-all">S</label>
								</th>
								<th>Fullname</th>
								<th>Gender</th>
								<th>Position</th>
								<th>Phone</th>
								<th>Email</th>
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
								<td></td>
								<td></td>
							</tr>
						</tfoot>
					</table>


				</div>
				<div class="tab-pane fade {{ $request->tab === 'brand' ? 'active in' : ''}}" id="brand">
					{{-- Brand block --}}
					@can('update-company')
					{{-- Create Brand --}}
					<div id="create-brand" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.storeBrand') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Create Brand</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Brand <span class="required">*</span>
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
										<input type="hidden" name="company_id" value="{{ $index->id }}">
										<button type="submit" class="btn btn-success">Submit</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Edit Brand --}}
					<div id="edit-brand" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.updateBrand') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Edit Brand</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Brand <span class="required">*</span>
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
										<input type="hidden" name="id"  value="{{ old('id') }}">
										<button type="submit" class="btn btn-success">Submit</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Delete Brand --}}
					<div id="delete-brand" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.deleteBrand') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Delete Brand?</h4>
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

					@can('update-company')
					<form method="post" id="action-brand" action="{{ route('backend.company.actionBrand') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-brand">Create</button>
						<select class="form-control" name="action">
							<!-- <option value="enable">Enable</option>
							<option value="disable">Disable</option> -->
							<option value="delete">Delete</option>
						</select>
						<button type="submit" class="btn btn-success">Apply Selected</button>
					</form>
					@endcan

					<div class="ln_solid"></div>

					<table class="table table-striped table-bordered" id="datatable-brand">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-brand" class="check-all" id="check-all">S</label>
								</th>
								<th>Brand</th>
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
				<div class="tab-pane fade {{ $request->tab === 'address' ? 'active in' : ''}}" id="address">

					{{-- Address block --}}
					@can('update-company')
					{{-- Create Address --}}
					<div id="create-address" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.storeAddress') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Create Address</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="address" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<textarea id="address" name="address" class="form-control {{$errors->first('address') != '' ? 'parsley-error' : ''}}" >{{ old('address') }}</textarea>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('address') }}</li>
												</ul>
											</div>
										</div>

										<div class="form-group">
											<label for="marker" class="control-label col-md-3 col-sm-3 col-xs-12">Marker
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="new_autosearch_address" name="new_autosearch_address" class="form-control {{$errors->first('new_autosearch_address') != '' ? 'parsley-error' : ''}}" autocomplete="off" value="{{ old('new_autosearch_address') }}">
												<div id="new-map-location"></div>
												

											</div>
										</div>


									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="company_id" value="{{ $index->id }}">
										<input type="hidden" name="latitude" data-geo="lat" value="{{ old('latitude') }}">
										<input type="hidden" name="longitude" data-geo="lng" value="{{ old('longitude') }}">
										<button type="submit" class="btn btn-success">Submit</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Edit Address --}}
					<div id="edit-address" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.updateAddress') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Edit Address</h4>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label for="address" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required">*</span>
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<textarea id="address" name="address" class="form-control {{$errors->first('address') != '' ? 'parsley-error' : ''}}">{{ old('address') }}</textarea>
												<ul class="parsley-errors-list filled">
													<li class="parsley-required">{{ $errors->first('address') }}</li>
												</ul>
											</div>
										</div>
										<div class="form-group">
											<label for="marker" class="control-label col-md-3 col-sm-3 col-xs-12">Marker
											</label>
											<div class="col-md-9 col-sm-9 col-xs-12">
												<input type="text" id="update_autosearch_address" name="update_autosearch_address" class="form-control {{$errors->first('update_autosearch_address') != '' ? 'parsley-error' : ''}}" autocomplete="off" value="{{ old('update_autosearch_address') }}">
												<div id="update-map-location"></div>
											</div>
										</div>

									</div>
									<div class="modal-footer">
										{{ csrf_field() }}
										<input type="hidden" name="id" value="{{ old('id') }}">
										<input type="hidden" name="latitude" data-geo="lat" value="{{ old('latitude') }}">
										<input type="hidden" name="longitude" data-geo="lng" value="{{ old('longitude') }}">
										<button type="submit" class="btn btn-success">Submit</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					{{-- Delete Address --}}
					<div id="delete-address" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<form class="form-horizontal form-label-left" action="{{ route('backend.company.deleteAddress') }}" method="post" enctype="multipart/form-data">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Delete Address?</h4>
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

					@can('update-company')
					<form method="post" id="action-address" action="{{ route('backend.company.actionAddress') }}" class="form-inline text-right" onsubmit="return confirm('Apply selected data?')">
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-address">Create</button>
						<select class="form-control" name="action">
							<!-- <option value="enable">Enable</option>
							<option value="disable">Disable</option> -->
							<option value="delete">Delete</option>
						</select>
						<button type="submit" class="btn btn-success">Apply Selected</button>
					</form>
					@endcan

					<div class="ln_solid"></div>

					<table class="table table-striped table-bordered" id="datatable-address">
						<thead>
							<tr>
								<th nowrap>
									<label class="checkbox-inline"><input type="checkbox" data-target="check-address" class="check-all" id="check-all">S</label>
								</th>
								<th>Address</th>
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
			</div>
		</div>


	</div>

@endsection