<!DOCTYPE html>
<html lang="en">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Tipe">
		<meta charset="utf-8">
		<meta content="IE=edge" http-equiv="X-UA-Compatible">
		<meta content="width=device-width, initial-scale=1" name="viewport">
		<meta content="{{ csrf_token() }}" name="csrf-token">
		<title>
			E DIGINDO | Feedback Form
		</title>
		<link href="{{ asset('backend/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
		<link href="{{ asset('backend/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
		<link href="https://colorlib.com/polygon/gentelella/css/animate.min.css" rel="stylesheet">
		<link href="{{ asset('backend/css/custom.min.css') }}" rel="stylesheet">
		<link href="{{ asset('backend/vendors/starrr/dist/starrr.css') }}" rel="stylesheet">
		<style type="text/css">
			div.starrr > a
			{
				font-size: 40px !important;
			}
			.form-group
			{
			    text-align: left;
			}
			.background-image
			{
				background-size: cover !important;
				width: 10em !important;
				height: 10em !important;
				margin: 0 !important;
				display: inline-block;
			    border: 3px solid rgba(52,73,94,.44) !important;
			}
		</style>
	</head>
	<body class="login">
		<div>
			<div class="login_wrapper" style="max-width: 600px;">
				<div class="form login_form">
					@if($index)
					<section class="login_content">
						<form class="form-horizontal form-label-left" action="{{ route('backend.crm.storeFeedback') }}" method="POST" enctype="multipart/form-data">
							<h1>
								Feedback Form
							</h1>
							{{ csrf_field() }}

							<input type="hidden" name="token" value="{{ $index->feedback_token }}">

							<div class="form-group">
								<div class="col-md-12 col-sm-12 col-xs-12 text-center">
									<div class="img-circle profile_img mCS_img_loaded background-image" style="background-image: url('{{ asset($sales->photo != '' ? $sales->photo : 'backend/images/user.png') }}');"> </div>
								</div>

								<div class="col-md-12 col-sm-12 col-xs-12 text-center">
									<h2> {{ $sales->fullname }} </h2>
								</div>
							</div>


							<div class="ln_solid">
							</div>

							<div class="form-group">
								<label for="rating" class="control-label col-md-3 col-sm-3 col-xs-12">Rating <span class="required">*</span>
								</label>

								<div class="col-md-9 col-sm-9 col-xs-12" >
									<div class="starrr stars"></div>
									<input type="hidden" name="rating" value="{{ old('rating', 0) }}">
								</div>

								
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('rating') }}</li>
								</ul>
							</div>
							<div class="ln_solid">
							</div>

							<div class="form-group input-comment" style="display: none;">
								<label for="comment" class="control-label col-md-3 col-sm-3 col-xs-12">Comment</label>
								<div class="col-md-9 col-sm-9 col-xs-12" >
									<textarea name="comment" class="form-control {{$errors->first('comment') != '' ? 'parsley-error' : ''}}" placeholder="">{{ old('comment') }}</textarea>
								</div>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('comment') }}</li>
								</ul>
							</div>
							<div class="ln_solid">
							</div>

							<div class="form-group input-option_performance" style="display: none;">
								<label for="option_performance" class="control-label col-md-3 col-sm-3 col-xs-12">Value</label>
								<div class="col-md-9 col-sm-9 col-xs-12" >
									<label class="checkbox-inline"><input type="checkbox" name="option_performance[]" value="COMMUNICATION" @if(is_array(old('option_performance')) && in_array("COMMUNICATION", old('option_performance'))) checked @endif>Communication</label>
									<label class="checkbox-inline"><input type="checkbox" name="option_performance[]" value="ATTITUDE" @if(is_array(old('option_performance')) && in_array("ATTITUDE", old('option_performance'))) checked @endif>Attitude</label>
									<label class="checkbox-inline"><input type="checkbox" name="option_performance[]" value="HYGIENE" @if(is_array(old('option_performance')) && in_array("HYGIENE", old('option_performance'))) checked @endif>Hygiene</label>
									<label class="checkbox-inline"><input type="checkbox" name="option_performance[]" value="ON_TIME" @if(is_array(old('option_performance')) && in_array("ON_TIME", old('option_performance'))) checked @endif>On Time</label>
									<label class="checkbox-inline"><input type="checkbox" name="option_performance[]" value="SERVICE" @if(is_array(old('option_performance')) && in_array("SERVICE", old('option_performance'))) checked @endif>Service</label>
								</div>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('option_performance') }}</li>
								</ul>
							</div>
							<div class="ln_solid">
							</div>

							<div class="form-group input-recommendation" style="display: none;">
								<label for="recommendation" class="control-label col-md-3 col-sm-3 col-xs-12">Recommendation <span class="required">*</span></label>
								<div class="col-md-9 col-sm-9 col-xs-12" >
									<label class="radio-inline"><input type="radio" name="recommendation" value="1" @if(old('recommendation') === '1') checked @endif>Yes</label>
									<label class="radio-inline"><input type="radio" name="recommendation" value="0" @if(old('recommendation') === '0') checked @endif>No</label>
								</div>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('recommendation') }}</li>
								</ul>
							</div>
							<div class="ln_solid">
							</div>

							<div class="form-group input-recommendation_yes" style="display: none;">
								<label for="recommendation_yes" class="control-label col-md-3 col-sm-3 col-xs-12">To whom? </label>
								<div class="col-md-9 col-sm-9 col-xs-12" >
									<textarea name="recommendation_yes" class="form-control {{$errors->first('recommendation_yes') != '' ? 'parsley-error' : ''}}" placeholder="">{{ old('recommendation_yes') }}</textarea>
								</div>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('recommendation_yes') }}</li>
								</ul>
							</div>

							<div class="form-group input-recommendation_no" style="display: none;">
								<label for="recommendation_no" class="control-label col-md-3 col-sm-3 col-xs-12">Tell us the reason?</label>
								<div class="col-md-9 col-sm-9 col-xs-12" >
									<textarea name="recommendation_no" class="form-control {{$errors->first('recommendation_no') != '' ? 'parsley-error' : ''}}" placeholder="">{{ old('recommendation_no') }}</textarea>
								</div>
								<ul class="parsley-errors-list filled">
									<li class="parsley-required">{{ $errors->first('recommendation_no') }}</li>
								</ul>
							</div>

							<div class="ln_solid">
							</div>


							<div class="row">
								<div class="col-xs-12">
									<button class="btn btn-primary btn-block btn-flat" type="submit">
										Submit
									</button>
								</div>
							</div>
							<div class="clearfix">
							</div>
							<div class="separator">
								<div>
									<h1>
										PT. Digtal Indonesia
									</h1>
									<p>
										©2018 All Rights Reserved.
									</p>
								</div>
							</div>
						</form>
					</section>
					@else
					<section class="login_content">
						<form action="#" method="POST" enctype="multipart/form-data">
							<h1>
								Thank You for time
							</h1>
							<a href="http://digindo.co.id" class="btn btn-primary btn-block">Visit Our Website</a>
							<div class="separator">
								<div>
									<h1>
										PT. Digtal Indonesia
									</h1>
									<p>
										©2018 All Rights Reserved.
									</p>
								</div>
							</div>
						</form>
					</section>
					@endif
				</div>
			</div>
		</div>
	</body>

	<script src="{{ asset('backend/vendors/jquery/dist/jquery.min.js') }}"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
	<script src="{{ asset('backend/vendors/starrr/dist/starrr.js') }}"></script>
	<script type="text/javascript">
		$('.starrr').starrr({
		  rating: {{ old('rating', 0) }}
		})

		$('.starrr').on('starrr:change', function(e, value){
			$('input[name=rating]').val(value);

			if(value < 5)
			{
				$('.input-comment').slideDown();
				$('.input-option_performance').slideDown();
				$('.input-recommendation').slideUp();
				$('input[name=recommendation]').prop( "checked", false );
				$('.input-recommendation_yes').slideUp();
				$('.input-recommendation_no').slideUp();
			}

			if(value == 5)
			{
				$('.input-comment').slideUp();
				$('.input-option_performance').slideUp();
				$('.input-recommendation').slideDown();

				if($('input[name=recommendation]').val() === 1)
				{
					$('.input-recommendation_yes').slideDown();
					$('.input-recommendation_no').slideUp();
				}
				else if($('input[name=recommendation]').val() === 0)
				{
					$('.input-recommendation_yes').slideUp();
					$('.input-recommendation_no').slideDown();
				}
				else
				{
					$('.input-recommendation_yes').slideUp();
					$('.input-recommendation_no').slideUp();
				}
			}
			
		})

		
		$('body').on('change', 'input[name=recommendation]', function(){
			if($(this).val() == "1")
			{
				$('.input-recommendation_yes').slideDown();
				$('.input-recommendation_no').slideUp();
			}
			else if($(this).val() == "0")
			{
				$('.input-recommendation_yes').slideUp();
				$('.input-recommendation_no').slideDown();
			}
			else
			{
				$('.input-recommendation_yes').slideUp();
				$('.input-recommendation_no').slideUp();
			}
		});
	</script>
</html>