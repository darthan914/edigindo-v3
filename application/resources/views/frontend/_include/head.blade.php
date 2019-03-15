<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	@yield('style')
	
	<link rel="icon" type="image/png" href="{{ asset('frontend/source/images/DIGINDO.png') }}" />

	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/assets/bootstrap-3.3.6-dist/css/bootstrap.min.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/Roboto.css') }}"/>
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/Champagne & Limousines.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/DroidSerif.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/Gotham-Bold.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/SourceSansPro.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/font/PlayfairDisplay.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/assets/amadeo/css/amadeo.css') }}">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="{{ asset('frontend/assets/owl-carousel/owl.carousel.css') }}">
	<link rel="stylesheet" href="{{ asset('frontend/assets/owl-carousel/owl.theme.css') }}">
	<link rel="stylesheet" href="{{ asset('frontend/source/font-icon/css/Digindo.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/main.css') }}">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/animate.css/3.2.0/animate.min.css">
	
	@yield('style')
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script src="{{ asset('frontend/assets/jquery-1.12.2/jquery.min.js') }}"></script>
	<script src="{{ asset('frontend/assets/bootstrap-3.3.6-dist/js/bootstrap.min.js') }}"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<script src="{{ asset('frontend/assets/amadeo/js/amadeo.js') }}"></script>
	<script src="{{ asset('frontend/assets/owl-carousel/owl.carousel.js') }}"></script>
	<script src="{{ asset('frontend/assets/ckeditor-full/ckeditor.js') }}"></script>
	<script src="{{ asset('frontend/assets/jquery-aniview-master/jquery.aniview.js') }}"></script>
	<script src="{{ asset('frontend/js/main.js') }}"></script>
	@yield('script')

	<title>@yield('title')</title>
</head>