<!DOCTYPE html>
<html>
<head>
	<meta name="title" property="og:title" content="{!! getConfigValue('website_name') !!}">
	<meta name="geo.position" property="og:title" content="{!! getConfigValue('geo_position') !!}">
	<meta name="geo.placename" property="og:title" content="{!! getConfigValue('geo_placename') !!}">
	<meta name="geo.region" property="og:title" content="{!! getConfigValue('geo_region') !!}">
	<meta name="keywords" content="{!! getConfigValue('keywords') !!}">

	@include('frontend._include.head')
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
	  ga('create', 'UA-83016466-3', 'auto');
	  ga('send', 'pageview');
	</script>
	
	<title>@yield('title')</title>
</head>
<body style="margin: 0px; padding: 0px;">

@yield('content')
</body>
</html>