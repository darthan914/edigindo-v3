@extends('backend.layout.master')

@section('title')
	Attandance Test
@endsection

@section('script')
	<script type="text/javascript">
		$(function() {
			$('.action-check_in').click(function(event) {
				$('.check_in').show();
				$('.check_out').hide();
			});

			$('.action-check_out').click(function(event) {
				$('.check_out').show();
				$('.check_in').hide();
			});
		});
	</script>
@endsection

@section('content')

	<h1>Attandance Test</h1>
	<div class="x_panel">
		<div class="x_content">
			<div class="row">
				<div class="col-md-4 col-sm-4 col-xs-12 profile_details check_in" style="display: none;">
					<div class="well profile_view">
						<div class="col-sm-12">
							<h4 class="brief"><i>Digital Strategist</i></h4>
							<div class="left col-xs-7">
								<h2>Nicole Pearson</h2>
								{{-- <p><strong>About: </strong> Web Designer / UX / Graphic Artist / Coffee Lover </p>
								<ul class="list-unstyled">
									<li><i class="fa fa-building"></i> Address: </li>
									<li><i class="fa fa-phone"></i> Phone #: </li>
								</ul> --}}
							</div>
							<div class="right col-xs-5 text-center">
								<img src="{{ asset('backend/test/check_in.jpg') }}" alt="" class="img-circle img-responsive">
							</div>
						</div>
						<div class="col-xs-12 bottom text-center">
							<div class="col-xs-12 col-sm-6 emphasis">
								{{-- <p class="ratings">
									<a>4.0</a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star-o"></span></a>
								</p> --}}
							</div>
							<div class="col-xs-12 col-sm-6 emphasis">
								<button type="button" class="btn btn-drak btn-xs action-check_out">
									<i class="fa fa-times"> </i> Check Out
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4 col-sm-4 col-xs-12 profile_details check_out">
					<div class="well profile_view">
						<div class="col-sm-12">
							<h4 class="brief"><i>Digital Strategist</i></h4>
							<div class="left col-xs-7">
								<h2>Nicole Pearson</h2>
								{{-- <p><strong>About: </strong> Web Designer / UI. </p>
								<ul class="list-unstyled">
									<li><i class="fa fa-building"></i> Address: </li>
									<li><i class="fa fa-phone"></i> Phone #: </li>
								</ul> --}}
							</div>
							<div class="right col-xs-5 text-center">
								<img src="{{ asset('backend/test/check_out.jpg') }}" alt="" class="img-circle img-responsive">
							</div>
						</div>
						<div class="col-xs-12 bottom text-center">
							<div class="col-xs-12 col-sm-6 emphasis">
								{{-- <p class="ratings">
									<a>4.0</a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star"></span></a>
									<a href="#"><span class="fa fa-star-o"></span></a>
								</p> --}}
							</div>
							<div class="col-xs-12 col-sm-6 emphasis">
								<button type="button" class="btn btn-success btn-xs action-check_in">
									<i class="fa fa-check"> </i> Check In
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
	</div>
	

@endsection