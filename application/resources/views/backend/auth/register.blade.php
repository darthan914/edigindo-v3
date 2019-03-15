<!DOCTYPE html>
<html lang="en">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Tipe">
			<meta charset="utf-8">
				<meta content="IE=edge" http-equiv="X-UA-Compatible">
					<meta content="width=device-width, initial-scale=1" name="viewport">
						<meta content="{{ csrf_token() }}" name="csrf-token">
							<title>
								E DIGINDO | Register
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
						<form action="{{ route('backend.updateRegister') }}" method="POST" enctype="multipart/form-data">
							<h1>
								Register Form
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

							<div class="form-group">
								<div>
									<input type="text" id="first_name" name="first_name" class="form-control {{$errors->first('first_name') != '' ? 'parsley-error' : ''}}" value="{{ old( 'first_name' ) }}" placeholder="First Name">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('first_name') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="text" id="last_name" name="last_name" class="form-control {{$errors->first('last_name') != '' ? 'parsley-error' : ''}}" value="{{ old( 'last_name' ) }}" placeholder="Last Name">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('last_name') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="text" id="phone" name="phone" class="form-control {{$errors->first('phone') != '' ? 'parsley-error' : ''}}" value="{{ old( 'phone' ) }}" placeholder="Phone">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('phone') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="file" id="signature" name="signature" class="form-control {{$errors->first('signature') != '' ? 'parsley-error' : ''}}" value="{{ old( 'signature' ) }}" placeholder="Signature">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('signature') }}</li>
									</ul>
								</div>
							</div>

							<div class="form-group">
								<div>
									<input type="file" id="photo" name="photo" class="form-control {{$errors->first('photo') != '' ? 'parsley-error' : ''}}" value="{{ old( 'photo' ) }}" placeholder="Photo">
									<ul class="parsley-errors-list filled">
										<li class="parsley-required">{{ $errors->first('photo') }}</li>
									</ul>
								</div>
							</div>


							<div class="row">
								<div class="col-xs-12">
									<button class="btn btn-primary btn-block btn-flat" type="submit">
										Sign In And Log In
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
					</section>
				</div>
			</div>
		</div>
	</body>
</html>