<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex,nofollow">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="userId" content="{{ Auth::id() }}">
		<title>@yield('title')</title>
		@include('backend.includes.head')

	</head>

	<body class="nav-md">
		<div id="app">
			<div class="container body">
				<div class="main_container">

					@include('backend.includes.sidebar')

					@include('backend.includes.header')

					<!-- page content -->
					<div class="right_col" role="main">
					@include('backend.includes.messages')
					@yield('content')
					</div>

					<footer>
						@include('backend.includes.footer')
					</footer>
				</div>
			</div>
		</div>

		@include('backend.includes.bottomscript')
		@yield('script')
	
	</body>
</html>
