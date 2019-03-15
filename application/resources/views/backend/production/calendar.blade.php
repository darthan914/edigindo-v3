@extends('backend.layout.master')

@section('title')
	Production Calendar
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
<script>
	$(function(){
		$('#calendar').fullCalendar({
			header: {
				left   : 'prev,next today',
				center : 'title',
				right  : 'month,agendaWeek,agendaDay'
			},
			eventLimit: true,
	        eventSources: [
		        {
		            url: '{{ route('backend.production.ajaxCalendar') }}',
		            type: 'POST',
		            data: {
		                f_sales   : $('*[name=f_sales]').val(),
		                f_maindiv : $('*[name=f_maindiv]').val(),
		                f_prodiv  : $('*[name=f_prodiv]').val(),
		            },
		        }

		    ],
		    eventClick:  function(event, jsEvent, view) {
	            $('#calendar-production_name').html(event.production_name);
	            $('#calendar-description').html(event.description);
	            $('#calendar-main_division').html(event.main_division);
	            $('#calendar-prod_division').html(event.prod_division);
	            $('#calendar-spk').html(event.spk);
	            $('#calendar-spk_name').html(event.spk_name);
	            $('#calendar-sales_name').html(event.sales_name);
	            $('#calendar-start_date').html(event.start_date);
	            $('#calendar-deadline').html(event.deadline);
	            $('#calendar-status').html(event.status);
	            $('#view-calendar').modal("show");
	        },
	    })

	    $('select[name=f_sales]').select2({
			placeholder: "Filter Sales",
			allowClear : true,
		});
	});
</script>
@endsection
<link rel="stylesheet" type="text/css" href="{{ asset('backend/vendors/fullcalendar/dist/fullcalendar.min.css') }}">
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('backend/vendors/fullcalendar/dist/fullcalendar.print.css') }}"> --}}
@section('css')

@endsection

@section('content')
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
							<th>Production Name</th>
							<td id="calendar-production_name"></td>
						</tr>
						<tr>
							<th>Description</th>
							<td id="calendar-description"></td>
						</tr>
						<tr>
							<th>Main Division</th>
							<td id="calendar-main_division"></td>
						</tr>
						<tr>
							<th>Production Division</th>
							<td id="calendar-prod_division"></td>
						</tr>
						<tr>
							<th>SPK</th>
							<td id="calendar-spk"></td>
						</tr>
						<tr>
							<th>SPK Name</th>
							<td id="calendar-spk_name"></td>
						</tr>
						<tr>
							<th>Sales Name</th>
							<td id="calendar-sales_name"></td>
						</tr>
						<tr>
							<th>Create Date</th>
							<td id="calendar-start_date"></td>
						</tr>
						<tr>
							<th>Deadline</th>
							<td id="calendar-deadline"></td>
						</tr>
						<tr>
							<th>Status</th>
							<td id="calendar-status"></td>
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<h1>Production Calendar</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-12">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">All Sales</option>
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_maindiv" onchange="this.form.submit()">
						<option value="">All Main Division</option>
						@foreach($division as $list)
						<option value="{{ $list->code }}" {{ $request->f_maindiv == $list->code ? 'selected' : '' }}>{{ $list->name }}</option>
						@endforeach
					</select>
					<select class="form-control" name="f_prodiv" onchange="this.form.submit()">
						<option value="">All Production Division</option>
						@foreach($division as $list)
						<option value="{{ $list->code }}" {{ $request->f_prodiv == $list->code ? 'selected' : '' }}>{{ $list->name }}</option>
						@endforeach
					</select>
				</form>
			</div>
			<div class="col-md-6">
				
			</div>
		</div>
		
		<div class="ln_solid"></div>
		<div id="calendar"></div>
	</div>
@endsection