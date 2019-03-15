@extends('backend.layout.master')

@section('title')
	Designer Dashboard
@endsection

@section('script')
<script src="{{ asset('backend/vendors/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript">
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

	Number.prototype.padLeft = function(base,chr){
	   var  len = (String(base || 10).length - String(this).length)+1;
	   return len > 0? new Array(len).join(chr || '0')+this : this;
	}


	function datetime_format(date)
	{
		if(date != null)
		{
			var d = new Date(date),
	        dformat = [ d.getDate().padLeft(),
	        			(d.getMonth()+1).padLeft(),
	                    d.getFullYear()].join('/')+
	                    ' ' +
	                  [ d.getHours().padLeft(),
	                    d.getMinutes().padLeft(),
	                    d.getSeconds().padLeft()].join(':');

	        return dformat;
		}
		else
		{
			return '';
		}
		
	}

	
	
	$(function() {
		$.ajax({
			url: "{{ route('backend.designer.ajaxDashboard') }}",
			type: "POST",
			data: {
				f_year  : $('*[name=f_year]').val(),
				f_month : $('*[name=f_month]').val(),
				f_day : $('*[name=f_day]').val(),
			},
			success: function(result){
				$("span#totalAll").html(number_format(result.all));
				$("span#totalPending").html(number_format(result.pending));
				$("span#totalProgress").html(number_format(result.progress));
				$("span#totalFinish").html(number_format(result.finish));
				$("span#totalWaiting").html(number_format(result.waiting));
				$("span#totalGreat").html(number_format(result.great));
				$("span#totalGood").html(number_format(result.good));
				$("span#totalBad").html(number_format(result.bad));
				$("span#totalSuccess").html(number_format(result.success));
				$("span#totalFailed").html(number_format(result.failed));


				var table = $('#datatable').DataTable({
					data: result.data,
					columns: [
						{data: 'fullname', sClass: 'nowrap-cell'},

						{data: 'all', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="ALL">'+oData.all+'</button>');
					        }
					    },
					    {data: 'pending', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="PENDING">'+oData.pending+'</button>');
					        }
					    },
					    {data: 'progress', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="PROGRESS">'+oData.progress+'</button>');
					        }
					    },
					    {data: 'finish', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="FINISH">'+oData.finish+'</button>');
					        }
					    },
					    {data: 'waiting', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="WAITING">'+oData.waiting+'</button>');
					        }
					    },
					    {data: 'great', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="GREAT">'+oData.great+'</button>');
					        }
					    },
					    {data: 'good', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="GOOD">'+oData.good+'</button>');
					        }
					    },
					    {data: 'bad', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="BAD">'+oData.bad+'</button>');
					        }
					    },
					    {data: 'success', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="SUCCESS">'+oData.success+'</button>');
					        }
					    },
					    {data: 'failed', sClass: 'number-format', 
							fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
					            $(nTd).html('<button class="btn btn-primary btn-xs data-designer" data-toggle="modal" data-target="#data-designer" data-id="'+oData.id+'" data-type="FAILED">'+oData.failed+'</button>');
					        }
					    },
					    {data: 'avgCreateToStart'},
					    {data: 'avgStartToFinish'},

					],
					// scrollX: true,
					scrollY: "400px",
				});

			    $('#datatable').on('click', '.data-designer', function(){
			    	$('.data-list').empty();
					$.post('{{ route('backend.designer.getData') }}', {
							id: $(this).data('id'),
							type: $(this).data('type'),
							f_year: $('*[name=f_year]').val(),
							f_month: $('*[name=f_month]').val()
						},
						function(data) {
							$.each(data, function(i, field) {
								$('.data-list').append('<li>'+field.fullname+' : '+field.project+'</li>');
						});
					});
					
				});

				$('#datatable').on('click', '.data-designer', function(){
			    	$('#datatables-data').DataTable().clear();
			    	$('#datatables-data').DataTable().destroy();
			    	$.post('{{ route('backend.designer.getData') }}', {
			    		id: $(this).data('id'),
						type: $(this).data('type'),
						f_year: $('*[name=f_year]').val(),
						f_month: $('*[name=f_month]').val()
			    	},
			    	function(data) {
						$('#datatables-data').DataTable({
							data: data,
							columns: [
								{data: 'fullname'},
								{data: 'project'},
								{data: 'start_project', 
									fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
							            $(nTd).html(datetime_format(oData.start_project));
							        }
							    },
								{data: 'end_project', 
									fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
							            $(nTd).html(datetime_format(oData.end_project));
							        }
							    },
								{data: 'date_finish_project', 
									fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
							            $(nTd).html(datetime_format(oData.date_finish_project));
							        }
							    },
								{data: 'approved_sales'},
								{data: 'result_project'},
							],
						});
					});
			    });
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


	{{-- Data Designer --}}
	<div id="data-designer" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Data List</h4>
					</div>
					<div class="modal-body">
						<table class="table table-striped" id="datatables-data">
							<thead>
							    <tr>
							        <th>Sales</th>
							        <th>Name Project</th>
							        <th>Start</th>
							        <th>Deadline</th>
							        <th>Finish</th>
							        <th>Status Sales</th>
							        <th>Result Project</th>
							        
							    </tr>
						    </thead>
						    <tbody>
						    	
						    </tbody>
						</table>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<h1>Designer Dashboard</h1>
	<div class="x_panel" style="overflow: auto;">
		<form class="form-inline" method="get">
			<select class="form-control" name="f_year" onchange="this.form.submit()">
				<option value="">This Year</option>
				<option value="all" {{ $request->f_year == 'all' ? 'selected' : '' }}>All Year</option>
				@foreach($year as $list)
				<option value="{{ $list->year }}" {{ $request->f_year == $list->year ? 'selected' : '' }}>{{ $list->year }}</option>
				@endforeach
			</select>
			<select class="form-control" name="f_month" onchange="this.form.submit()">
				<option value="">All Month</option>
				@php $numMonth = 1; @endphp
				@foreach($month as $list)
				<option value="{{ $numMonth }}" {{ $request->f_month == $numMonth++ ? 'selected' : '' }}>{{ $list }}</option>
				@endforeach
			</select>
			<select class="form-control" name="f_day" onchange="this.form.submit()">
				<option value="">All Day</option>
				@for($i = 0; $i < 31; $i++)
				<option value="{{ $i + 1 }}" {{ $request->f_day == $i + 1 ? 'selected' : '' }}>{{ $i + 1 }}</option>
				@endfor
			</select>
		</form>
	</div>


	<div class="x_panel" style="overflow: auto;">

		<table class="table table-striped" id="datatable">
			<thead>
				<tr>
					<th>Designer</th>

					<th>All</th>

					<th>Pending</th>
					<th>Progress</th>
					<th>Finish</th>
					<th>Waiting</th>

					<th>Great</th>
					<th>Good</th>
					<th>Bad</th>

					<th>Success</th>
					<th>Failed</th>

					<th>Avg. Create to Start</th>
			        <th>Avg. Start to Finish</th>
				</tr>
			</thead>
			<tfoot>
				<th></th>

				<th><span id="totalAll"></span></th>

				<th><span id="totalPending"></span></th>
				<th><span id="totalProgress"></span></th>
				<th><span id="totalFinish"></span></th>
				<th><span id="totalWaiting"></span></th>

				<th><span id="totalGreat"></span></th>
				<th><span id="totalGood"></span></th>
				<th><span id="totalBad"></span></th>

				<th><span id="totalSuccess"></span></th>
				<th><span id="totalFailed"></span></th>

				<th></th>
				<th></th>
			</tfoot>
		</table>

	</div>

@endsection