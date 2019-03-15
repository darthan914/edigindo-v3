<!DOCTYPE html>
<html lang="en">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Tipe">
			<meta charset="utf-8">
				<meta content="IE=edge" http-equiv="X-UA-Compatible">
					<meta content="width=device-width, initial-scale=1" name="viewport">
						<meta content="{{ csrf_token() }}" name="csrf-token">
							<title>
								E DIGINDO | Reset Password
							</title>
							<link href="{{ asset('backend/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
								<link href="{{ asset('backend/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
									<link href="https://colorlib.com/polygon/gentelella/css/animate.min.css" rel="stylesheet">
										<link href="{{ asset('backend/css/custom.min.css') }}" rel="stylesheet">
										</link>
									</link>
								</link>
							</link>
						</meta>
					</meta>
				</meta>
			</meta>
		</meta>
	</head>
	<body class="login">
		<div>
			@include('backend.includes.messages')
			<div class="login_wrapper">
				<div class="animate form login_form">
					<section class="login_content">
						@if($index)
						<form action="{{ route('backend.updatePassword') }}" method="POST" enctype="multipart/form-data">
							<h1>
								Reset Password
							</h1>
							{{ csrf_field() }}
							<input type="hidden" name="token" value="{{ $token }}">

							<div class="form-group">
								<div>
									<input type="text" id="username" name="username" class="form-control {{$errors->first('username') != '' ? 'parsley-error' : ''}}" value="{{ old( 'username' ) }}" placeholder="Username">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('username') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="password" id="password" name="password" class="form-control {{$errors->first('password') != '' ? 'parsley-error' : ''}}" placeholder="Password">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('password') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="password" id="password_confirmation" name="password_confirmation" class="form-control {{$errors->first('password_confirmation') != '' ? 'parsley-error' : ''}}" placeholder="Password Confirmation">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('password_confirmation') }}</li>
									</ul>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12">
									<button class="btn btn-primary btn-block btn-flat" type="submit">
										Reset Password
									</button>
								</div>
							</div>
							<div class="clearfix">
							</div>
							<div class="separator">
								<div>
									<h1>
										EDigindo
									</h1>
									<p>
										©2017 All Rights Reserved.
									</p>
								</div>
							</div>
						</form>
						@else
							<H1>Sorry Your Token Has Been Expired</H1>
						@endif
					</section>
				</div>
			</div>
		</div>
	</body>
</html>