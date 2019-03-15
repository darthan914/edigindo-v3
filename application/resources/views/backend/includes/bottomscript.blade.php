<script src="{{ asset('application/public/js/app.js') }}"></script>

{{-- <script src="{{ asset('backend/vendors/jquery/dist/jquery.min.js') }}"z></script> --}}
<script src="{{ asset('backend/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/vendors/fastclick/lib/fastclick.js') }}"></script>
{{-- <script src="{{ asset('backend/vendors/nprogress/nprogress.js') }}"></script> --}}
{{-- <script src="{{ asset('backend/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js') }}"></script> --}}
<script src="{{ asset('backend/vendors/select2/dist/js/select2.min.js')}}"></script>
<script src="{{ asset('backend/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')}}"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>

<!-- Custom Theme Scripts -->


<script src="{{ asset('backend/js/custom.min.js') }}"></script>



<script type="text/javascript">
	// function timeSince(date) {

	//   var seconds = Math.floor((new Date() - date) / 1000);

	//   var interval = Math.floor(seconds / 31536000);

	//   if (interval > 1) {
	//     return interval + " years";
	//   }
	//   interval = Math.floor(seconds / 2592000);
	//   if (interval > 1) {
	//     return interval + " months";
	//   }
	//   interval = Math.floor(seconds / 86400);
	//   if (interval > 1) {
	//     return interval + " days";
	//   }
	//   interval = Math.floor(seconds / 3600);
	//   if (interval > 1) {
	//     return interval + " hours";
	//   }
	//   interval = Math.floor(seconds / 60);
	//   if (interval > 1) {
	//     return interval + " minutes";
	//   }
	//   return Math.floor(seconds) + " seconds";
	// }

	$(function() {
		$('select.select2full').select2({ width: '100%' });
		$('select.select2').select2();
	});

	function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
</script>
