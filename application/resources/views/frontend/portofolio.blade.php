<div style="position: relative;">
	
	<div id="myCarousel" class="carousel slide" data-ride="carousel">
		<!-- Indicators -->
		<ol class="carousel-indicators">
			<li data-target="#myCarousel" data-slide-to="0" @if($division == 'signage') class="active" @endif></li>
			<li data-target="#myCarousel" data-slide-to="1" @if($division == 'print1') class="active" @endif></li>
			<li data-target="#myCarousel" data-slide-to="2" @if($division == 'print2') class="active" @endif></li>
			<li data-target="#myCarousel" data-slide-to="3" @if($division == 'booth') class="active" @endif></li>
			<li data-target="#myCarousel" data-slide-to="4" @if($division == 'pop') class="active" @endif></li>
		</ol>

		<!-- Wrapper for slides -->
		<div class="carousel-inner" role="listbox">
			<div class="item @if($division == 'signage') active @endif">
				<img src="{{ asset('frontend/source/portofolio/ionessence.jpg') }}" alt="DG-SIGNAGE" width="100%">
			</div>

			<div class="item @if($division == 'print1') active @endif">
				<img src="{{ asset('frontend/source/portofolio/extrajoss.jpg') }}" alt="DG-PRINT" width="100%">
			</div>

			<div class="item @if($division == 'print2') active @endif">
				<img src="{{ asset('frontend/source/portofolio/indomilkbanana.jpg') }}" alt="DG-PRINT" width="100%">
			</div>

			<div class="item @if($division == 'booth') active @endif">
				<img src="{{ asset('frontend/source/portofolio/bimoli.jpg') }}" alt="DG-BOOTH" width="100%">
			</div>

			<div class="item @if($division == 'pop') active @endif">
				<img src="{{ asset('frontend/source/portofolio/orangtua.jpg') }}" alt="DG-POP" width="100%">
			</div>
		</div>

		<!-- Left and right controls -->
		<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>

</div>

<style type="text/css">
.fa-times-circle-o 
{
	color: white !important;
	opacity: 0.5;
}
</style>