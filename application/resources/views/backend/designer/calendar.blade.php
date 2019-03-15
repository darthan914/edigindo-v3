@extends('backend.layout.master')

@section('title')
	Designer Calendar
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
		            url: '{{ route('backend.designer.ajaxCalendar') }}',
		            type: 'POST',
		            data: {
		                f_sales    : $('*[name=f_sales]').val(),
		                f_designer : $('*[name=f_designer]').val(),
		            },
		        }

		    ],
		    eventClick:  function(event, jsEvent, view) {
	            $('#calendar-designer_name').html(event.designer_name);
	            $('#calendar-sales_name').html(event.sales_name);
	            $('#calendar-project').html(event.project);
	            $('#calendar-description').html(event.description);
	            $('#calendar-start_project').html(event.start_project);
	            $('#calendar-end_project').html(event.end_project);
	            $('#calendar-status').html(event.status);
	            $('#view-calendar').modal("show");
	        },
	    })

	    $('select[name=f_sales]').select2({
			placeholder: "Filter Sales",
			allowClear : true,
		});

		$('select[name=f_designer]').select2({
			placeholder: "Filter Designer",
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
							<th>Designer Name</th>
							<td id="calendar-designer_name"></td>
						</tr>
						<tr>
							<th>Sales Name</th>
							<td id="calendar-sales_name"></td>
						</tr>
						<tr>
							<th>Project</th>
							<td id="calendar-project"></td>
						</tr>
						<tr>
							<th>Description</th>
							<td id="calendar-description"></td>
						</tr>
						<tr>
							<th>Start Project</th>
							<td id="calendar-start_project"></td>
						</tr>
						<tr>
							<th>End Project</th>
							<td id="calendar-end_project"></td>
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

	<h1>Designer Calendar</h1>
	<div class="x_panel" style="overflow: auto;">
		<div class="row">
			<div class="col-md-6">
				<form class="form-inline" method="get">
					<select class="form-control" name="f_designer" onchange="this.form.submit()">
						<option value="">All Designer</option>
						@foreach($designer as $list)
						<option value="{{ $list->id }}" {{ $request->f_designer == $list->id ? 'selected' : '' }}>{{ $list->fullname }}</option>
						@endforeach
					</select>

					<select class="form-control" name="f_sales" onchange="this.form.submit()">
						<option value="">All Sales</option>
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