<link href="{{ asset('backend/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css') }}" rel="stylesheet">
<link href="{{ asset('backend/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('backend/vendors/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
@yield('css')
<link href="{{ asset('backend/css/custom.min.css') }}" rel="stylesheet">
<style type="text/css">
	.menu_section h3 {
	    font-size: 18px;
	}
</style>
@yield('headscript')

<meta name="csrf-token" content="{{ csrf_token() }}" />
{{-- <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> --}}
