@extends('backend.layout.master')

@section('title')
	To Do Calendar
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
<script>
	$(function(){
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			eventLimit: true,
	        eventSources: [
		        {
		            url: '{{ route('backend.todo.ajaxCalendar') }}',
		            type: 'POST',
		            data: {
		                f_sales: $('*[name=f_sales]').val(),
		            },
		        }

		    ],
		    eventClick:  function(event, jsEvent, view) {
	            $('#calendar-sales_name').html(event.sales_name);
	            $('#calendar-event').html(event.event);
	            $('#calendar-date').html(event.date);
	            $('#calendar-company').html(event.company);
	            $('#calendar-brand').html(event.brand);
	            $('#calendar-status').html(event.status);
	            $('#view-calendar').modal("show");
	        },
	    });

	    $('select[name=f_sales]').select2({
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
						<tr>
							<th>Company</th>
							<td id="calendar-company"></td>
						</tr>
						<tr>
							<th>Brand</th>
							<td id="calendar-brand"></td>
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

	<h1>To Do Calendar</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">My Project</option>
						<option value="staff" {{ $request->f_sales == 'staff' ? 'selected' : '' }}>My Staff</option>
						@can('allSales-todo')
							<option value="all" {{ $request->f_sales == 'all' ? 'selected' : '' }}>All Sales</option>
						@endcan
						
						@foreach($sales as $list)
						<option value="{{ $list->id }}" {{ $request->f_sales == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
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