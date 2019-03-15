@extends('backend.layout.master')

@section('title')
	CRM Calendar - {{ $index->no_crm }}
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
<script src="{{ asset('backend/vendors/starrr/dist/starrr.js') }}"></script>

<script>
	function copyToClipboard(target) {
		/* Get the text field */
		var copyText = document.getElementById(target);

		/* Select the text field */
		copyText.select();

		/* Copy the text inside the text field */
		document.execCommand("copy");

		/* Alert the copied text */
		// alert("Copied the text: " + copyText.value);
	}

	$(function(){
		$('.stars-0').starrr({
			rating: 0,
			readOnly: true
		});
		$('.stars-1').starrr({
			rating: 1,
			readOnly: true
		});
		$('.stars-2').starrr({
			rating: 2,
			readOnly: true
		});
		$('.stars-3').starrr({
			rating: 3,
			readOnly: true
		});
		$('.stars-4').starrr({
			rating: 4,
			readOnly: true
		});
		$('.stars-5').starrr({
			rating: 5,
			readOnly: true
		});

		$('#calendar2').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'year,month,agendaWeek,agendaDay'
			},
			eventLimit: true,
	        eventSources: [
		        {
		            url: '{{ route('backend.crm.ajaxCalendar') }}',
		            type: 'POST',
		            data: {
		                id: {{ $index->id }},
		            },
		        }

		    ],
		    eventClick:  function(event, jsEvent, view) {
	            $('#calendar-sales_name').html(event.sales_fullname);
	            $('#calendar-event').html(event.activity);
	            $('#calendar-date').html(event.datetime_activity);
	            $('#view-calendar').modal("show");
	        },
	    });

	    $('body').on('click', '.next-crm', function(){
			$('#next-crm input[name=id]').val($(this).data('id'));
		});

		$('body').on('click', '.reschedule-crm', function(){
			$('#reschedule-crm input[name=id]').val($(this).data('id'));
			$('#reschedule-crm select[name=activity]').val($(this).data('activity')).trigger('change');
			$('#reschedule-crm input[name=date_activity]').val($(this).data('date_activity'));
			$('#reschedule-crm input[name=time_activity]').val($(this).data('time_activity'));
		});

		$('body').on('click', '.view-crm', function(){
			$('#view-crm .data-datetime_check_in').html($(this).data('datetime_check_in'));
			$('#view-crm .data-map_check_in').html('<div style="position:relative"><img src="{{ asset('frontend/pinpoint.png')}}" style="position: absolute;left:15em;top:6em; width:2em" /> <img src="https://api.tomtom.com/map/1/staticimage?layer=basic&style=main&format=png&zoom=16&center='+$(this).data('longitude_check_in')+','+$(this).data('latitude_check_in')+'&width=1024&height=512&view=Unified&key={{ env('TOMTOM_MAPS_API') }}" style="width: 32em" /></div>');
			$('#view-crm .data-datetime_check_out').html($(this).data('datetime_check_out'));
			$('#view-crm .data-map_check_out').html('<div style="position:relative"><img src="{{ asset('frontend/pinpoint.png')}}" style="position: absolute;left:15em;top:6em; width:2em" /> <img src="https://api.tomtom.com/map/1/staticimage?layer=basic&style=main&format=png&zoom=16&center='+$(this).data('longitude_check_out')+','+$(this).data('latitude_check_out')+'&width=1024&height=512&view=Unified&key={{ env('TOMTOM_MAPS_API') }}" style="width: 32em" /></div>');
			$('#view-crm .data-feedback_email').html($(this).data('feedback_email'));
			$('#view-crm .data-feedback_phone').html($(this).data('feedback_phone'));

		});

		$('body').on('click', '.sendEmail-crm', function(){
			$('#sendEmail-crm input[name=id]').val($(this).data('id'));
			$('#sendEmail-crm input[name=feedback_email]').val($(this).data('feedback_email'));

		});

		$('body').on('click', '.sendWhatsapp-crm', function(){
			$('#sendWhatsapp-crm input[name=id]').val($(this).data('id'));
			$('#sendWhatsapp-crm input[name=feedback_phone]').val($(this).data('feedback_phone'));

		});

	    $('select[name=f_sales]').select2({
		});

		$('body').on('click', '.checkIn-crm', function(){
			$('#checkIn-crm input[name=id]').val($(this).data('id'));

			if ("geolocation" in navigator){ //check geolocation available 
		        //try to get user current location using getCurrentPosition() method
		        navigator.geolocation.getCurrentPosition( function(position){ 
		        	$('#checkIn-crm input[name=latitude_check_in]').val(position.coords.latitude);
		        	$('#checkIn-crm input[name=longitude_check_in]').val(position.coords.longitude);
		        	$('#checkIn-crm').submit();
		        });
		    }
		    else{
		        alert("Give Access Location to complete this action!");
		        // $('#checkIn-crm').submit();
		        
		    }

			
		});

		$('body').on('click', '.checkOut-crm', function(){
			$('#checkOut-crm input[name=id]').val($(this).data('id'));

			if ("geolocation" in navigator){ //check geolocation available 
		        //try to get user current location using getCurrentPosition() method
		        navigator.geolocation.getCurrentPosition( function(position){ 
		        	$('#checkOut-crm input[name=latitude_check_out]').val(position.coords.latitude);
		        	$('#checkOut-crm input[name=longitude_check_out]').val(position.coords.longitude);
		        	$('#checkOut-crm').submit();
		        });
		    }
		    else{
		        alert("Give Access Location to complete this action!");
		        // $('#checkOut-crm').submit();
		    }

		});


		@if(Session::has('next-crm-error'))
			$('#next-crm').modal('show');
		@endif

		@if(Session::has('reschedule-crm-error'))
			$('#reschedule-crm').modal('show');
		@endif

		@if(Session::has('send-crm-error'))
			$('#send-crm').modal('show');
		@endif
	});
</script>
@endsection
<link rel="stylesheet" type="text/css" href="{{ asset('backend/vendors/fullcalendar/dist/fullcalendar.min.css') }}">
<link href="{{ asset('backend/vendors/starrr/dist/starrr.css') }}" rel="stylesheet">
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('backend/vendors/fullcalendar/dist/fullcalendar.print.css') }}"> --}}
@section('css')

@endsection

@section('content')

	<form class="form-horizontal" id="checkIn-crm" action="{{ route('backend.crm.checkIn') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""><input type="hidden" name="latitude_check_in" value=""><input type="hidden" name="longitude_check_in" value=""></form>
	<form class="form-horizontal" id="checkOut-crm" action="{{ route('backend.crm.checkOut') }}" method="post" enctype="multipart/form-data">{{ csrf_field() }}<input type="hidden" name="id" value=""><input type="hidden" name="latitude_check_out" value=""><input type="hidden" name="longitude_check_out" value=""></form>

	@can('next-crm')
	{{-- next crm --}}
	<div id="next-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.next') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Create Next Event</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" name="activity">
									<option value="">Select Activity</option>
									@foreach($activity as $key => $list)
									<option value="{{ $key }}" {{ old('activity') == $key ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('activity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<input type="date" name="date_activity" class="form-control" value="{{ old('datetime_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
								</ul>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-6">
								<input type="time" name="time_activity" class="form-control" value="{{ old('datetime_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" class="crm_id-ondelete" value="{{old('id')}}">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	@can('reschedule-crm')
	{{-- Rechedule crm --}}
	<div id="reschedule-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.reschedule') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Rechedule Event</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="activity" class="control-label col-md-3 col-sm-3 col-xs-12">Activity <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" name="activity">
									<option value="">Select Activity</option>
									@foreach($activity as $key => $list)
									<option value="{{ $key }}" {{ old('activity') == $key ? 'selected' : '' }}>{{ $list }}</option>
									@endforeach
								</select>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('activity') }}</li>
								</ul>
							</div>
						</div>

						<div class="form-group">
							<label for="datetime_activity" class="control-label col-md-3 col-sm-3 col-xs-12">Datetime Activity <span class="required">*</span>
							</label>
							<div class="col-md-6 col-sm-6 col-xs-6">
								<input type="date" name="date_activity" class="form-control" value="{{ old('date_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('date_activity') }}</li>
								</ul>
							</div>
							<div class="col-md-3 col-sm-3 col-xs-6">
								<input type="time" name="time_activity" class="form-control" value="{{ old('time_activity') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('time_activity') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Reschedule</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endcan

	<div id="sendEmail-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.sendFeedbackByEmail') }}" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Send Feedback</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="feedback_email" class="control-label col-md-3 col-sm-3 col-xs-12">Email <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="email" name="feedback_email" class="form-control" value="{{ old('feedback_email') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('feedback_email') }}</li>
								</ul>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Send</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="sendWhatsapp-crm" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="{{ route('backend.crm.sendFeedbackByWhatsapp') }}" method="post" enctype="multipart/form-data" target="_blank">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Send Feedback By Whatsapp</h4>
					</div>

					<div class="modal-body">
						<div class="form-group">
							<label for="feedback_phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone <span class="required">*</span>
							</label>
							<div class="col-md-9 col-sm-9 col-xs-12">
								<input type="text" name="feedback_phone" class="form-control" value="{{ old('feedback_phone') }}">
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('feedback_phone') }}</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{old('id')}}">
						<button type="submit" class="btn btn-primary">Send</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="view-crm" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="#" method="post" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Detail Activity</h4>
					</div>
					<div class="modal-body">
						<table class="table">
							<tr>
								<th width="20%">Email</th>
								<td width="80%" class="data-feedback_email"></td>
							</tr>
							<tr>
								<th width="20%">Phone</th>
								<td width="80%" class="data-feedback_phone"></td>
							</tr>
							<tr>
								<th width="20%">Datetime Check In</th>
								<td width="80%" class="data-datetime_check_in"></td>
							</tr>
							<tr>
								<th width="20%"></th>
								<td width="80%" class="data-map_check_in"></td>
							</tr>
							<tr>
								<th width="20%">Datetime Check Out</th>
								<td width="80%" class="data-datetime_check_out">
								</td>
							</tr>
							<tr>
								<th width="20%"></th>
								<td width="80%" class="data-map_check_out"></td>
							</tr>
							
						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="view-calendar" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Event Detail</h4>
				</div>
				<div class="modal-body">
					<table class="table table-bordered">
						<tr>
							<th>Sales Name</th>
							<td id="calendar-sales_name"></td>
						</tr>
						<tr>
							<th>Event</th>
							<td id="calendar-event"></td>
						</tr>
						<tr>
							<th>Date</th>
							<td id="calendar-date"></td>
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<h1>CRM Calendar - {{ $index->no_crm }} - {{ $index->company_name ?? $index->company_name_prospec }} - {{ $index->brand_name  ?? $index->brand_name_prospec}}</h1>
	<div class="x_panel">

		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">

				</form>
			</div>
			<div class="col-md-6">
				<form method="post" id="action" action="#" class="form-inline text-right" onsubmit="return confirm('Are you sure to apply this selected?')">
					<a class="btn btn-primary" href="{{ route('backend.crm') }}">Back</a>
					@if(Auth::user()->can('next-crm') && Auth::id() == $index->sales_id)
						<button type="button" class="btn btn-primary next-crm" data-toggle="modal" data-target="#next-crm" data-id="{{ $index->id }}">Next Event</button>
					@endif
					@if(Auth::user()->can('reschedule-crm') && Auth::id() == $index->sales_id)
						{{-- <button type="button" class="btn btn-warning reschedule-crm" data-toggle="modal" data-target="#reschedule-crm" data-id="{{ $crm_detail[0]->id }}">Reschedule Event</button> --}}
					@endif
				</form>
			</div>
		</div>

		<ul class="nav nav-tabs bar_tabs">
			<li role="presentation" class="{{ $request->tab === 'milestone-tab' || $request->tab == '' ? 'active' : ''}}">
				<a href="#milestone" id="milestone-tab" data-toggle="tab" aria-expanded="true" class="tab-active">Milestone</a>
			</li>
			<li role="presentation" class="{{ $request->tab === 'calender-tab' ? 'active' : ''}}">
				<a href="#calender" id="calender-tab" data-toggle="tab" aria-expanded="false" class="tab-active">Calendar</a>
			</li>
			
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade {{ $request->tab === 'milestone-tab' || $request->tab == '' ? 'active in' : ''}}" id="milestone">
				<ul class="list-unstyled timeline">
					@foreach($crm_detail as $list)
					<li>
						<div class="block">
							<div class="tags">
								<a href="" class="tag">
									<span>{{ $activity[$list->activity] }}</span>
								</a>
							</div>
							<div class="block_content">
								<h2 class="title">
									 {{ $index->company_name ?? $index->company_name_prospec }} - {{ $index->brand_name  ?? $index->brand_name_prospec}}
								</h2>
								<div class="byline">
									<span>{{ date('d M Y H:i', strtotime($list->datetime_activity)) }} - {{ getDateDiff($list->datetime_activity, date('Y-m-d H:i:s')) }} ago</span> by <a>{{ $list->sales_fullname }}</a>
								</div>
								<p>Klien : {{ $list->pic_fullname }} <div class="starrr stars-{{ $list->rating }}" style="display: inline;"></div></p>
								@if($list->datetime_check_in == '' && $list->sales_id == Auth::id())

								@if(Auth::user()->can('reschedule-crm'))
									<button type="button" class="btn btn-xs btn-warning reschedule-crm"
										data-toggle="modal" data-target="#reschedule-crm"
										data-id="{{ $list->id }}"
										data-activity="{{ $list->activity }}"
										data-date_activity="{{ date('Y-m-d', strtotime($list->datetime_activity)) }}"
										data-time_activity="{{ date('H:i', strtotime($list->datetime_activity)) }}"
									>Reschedule</button>
									@endif
									<button type="button" class="btn btn-xs btn-primary checkIn-crm" data-id="{{ $list->id }}">Check In</button>
								@elseif($list->datetime_check_out == '' && $list->sales_id == Auth::id())
									<button type="button" class="btn btn-xs btn-primary checkOut-crm" data-id="{{ $list->id }}">Check Out</button>
								@elseif($list->rating == null)
									{{-- <div class="input-group">
										<input type="text" class="form-control" readonly value="{{ route('backend.crm.feedback', ["token" => $list->feedback_token ]) }}" id="token-{{ $list->id }}">
										<span class="input-group-btn">
											<button type="button" class="btn btn-primary btn-copy" onclick="copyToClipboard('token-{{ $list->id }}')"><i class="fa fa-clipboard" aria-hidden="true"></i></button>
										</span>
									</div> --}}

									<button type="button" class="btn btn-xs btn-primary sendEmail-crm"
										data-toggle="modal"
										data-target="#sendEmail-crm"
										data-id="{{ $list->id }}"
										data-feedback_email="{{$list->feedback_email}}"
									>Send Feedback By Email</button>

									<button type="button" class="btn btn-xs btn-success sendWhatsapp-crm"
										data-toggle="modal"
										data-target="#sendWhatsapp-crm"
										data-id="{{ $list->id }}"
										data-feedback_phone="{{$list->feedback_phone}}"
									>Send Feedback By Whatsapp</button>

									<button type="button" class="btn btn-xs btn-info view-crm"
										data-toggle="modal"
										data-target="#view-crm"
										data-datetime_check_in="{{ date('d F Y H:i', strtotime($list->datetime_check_in)) }}"
										data-latitude_check_in="{{$list->latitude_check_in}}"
										data-longitude_check_in="{{$list->longitude_check_in}}"
										data-datetime_check_out="{{ date('d F Y H:i', strtotime($list->datetime_check_out)) }}"
										data-latitude_check_out="{{$list->latitude_check_out}}"
										data-longitude_check_out="{{$list->longitude_check_out}}"
										data-feedback_email="{{$list->feedback_email}}"
										data-feedback_phone="{{$list->feedback_phone}}"
									>View More Activity</button>
								@else
									<p><b>Comment</b>: {{ $list->comment }}</p>
									<p>Value: {{ implode(", ", explode("|", $list->option_performance)) }}</p>
									@if($list->recommendation)
									<p>Suggested to: {{ $list->recommendation_yes }}</p>
									@elseif($list->recommendation == 5)
									<p>Reason not recomment: {{ $list->recommendation_no }}</p>
									@endif

									<button type="button" class="btn btn-xs btn-info view-crm"
										data-toggle="modal"
										data-target="#view-crm"
										data-datetime_check_in="{{ date('d F Y H:i', strtotime($list->datetime_check_in)) }}"
										data-latitude_check_in="{{$list->latitude_check_in}}"
										data-longitude_check_in="{{$list->longitude_check_in}}"
										data-datetime_check_out="{{ date('d F Y H:i', strtotime($list->datetime_check_out)) }}"
										data-latitude_check_out="{{$list->latitude_check_out}}"
										data-longitude_check_out="{{$list->longitude_check_out}}"
										data-feedback_email="{{$list->feedback_email}}"
										data-feedback_phone="{{$list->feedback_phone}}"
									>View More Activity</button>
								@endif
							</div>
						</div>
					</li>
					@endforeach
				</ul>
			</div>
			<div class="tab-pane fade {{ $request->tab === 'calender-tab' ? 'active in' : ''}}" id="calender">
				
				<div class="x_panel" style="overflow: auto;">
					<div class="row">
						<div class="col-md-6">
							
						</div>
						<div class="col-md-6">
							
						</div>
					</div>
					
					<div class="ln_solid"></div>
					<div id="calendar2"></div>
				</div>
			</div>
			
		</div>
	</div>

	
@endsection