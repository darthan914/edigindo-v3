@extends('frontend._layouts.master')

@section('title')
	Home Digindo
@endsection

@section('style')
<style type="text/css">
	.fileUpload {
	    position: relative;
	    overflow: hidden;
	    margin: 10px 0px;
	}
	.fileUpload input.upload {
	    position: absolute;
	    top: 0;
	    right: 0;
	    margin: 0;
	    padding: 0;
	    font-size: 20px;
	    cursor: pointer;
	    opacity: 0;
	    filter: alpha(opacity=0);
	}
</style>
@endsection

@section('script')
	<script type="text/javascript">
		$(document).ready(function(){
			var options = {
				animateThreshold: 50,
				scrollPollInterval: 0
			}
			$('.aniview').AniView(options);
				});
	</script>

@endsection

@section('content')
	
	<!-- header -->
	<nav id="navbarFixedTop" class="navbar navbar-default navbar-fixed-top" style="background-color: rgba(238,236,236,1); box-shadow: 0px 1px 6px 0px; display: none;">
		<div class="container" style="padding: 10px;">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<img class="visible-md visible-lg" src="{{ asset('frontend/assets/picture/webdigindologo.png') }}" height="20px" style="margin: 5px 0px 0px 20px;">
				<img class="visible-sm visible-xs" src="{{ asset('frontend/assets/picture/webdigindologo.png') }}" height="30px" style="margin:10px 0px 0px 20px;">
		    </div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
		        	<li><a href="#expertise"><b>OUR WORK</b></a></li>
					<li><a href="#our-work"><b>EXPERTISE</b></a></li>
					<li><a href="#about"><b>ABOUT</b></a></li>
					<li><a href="#career"><b>CAREER</b></a></li>
					<li><a href="/quotes" onclick="return false;" class="btn-popout"><b>QUOTES</b></a></li>
		        </ul>
			</div>
		  </div>
	</nav>
	@include("frontend._include.popout")
	@include("frontend._include.portofolio")

	<div id="header">
		<div class="container-fluid" style="padding:0;position:relative;min-height:665px;background-image:url('{{ asset('frontend/source/images/header-img.jpg') }}');background-size:cover;background-position: 50% 80%;background-attachment: fixed;">
			
			<!-- gradien white to background image -->
			<div class="gradient-transparent"></div>

			<div class="container">
				<div style="position:relative;" class="nav-fix-top">
					
					<nav class="navbar navbar-default visible-md visible-lg" style="font-family:'SourceSansPro';">
						<div class="container-fluid">
							<!-- Collect the nav links, forms, and other content for toggling -->
								<ul class="nav navbar-nav" id="navbar-menu">
									<li><a href="#expertise"><b>EXPERTISE</b></a></li>
									<li><a href="#our-work"><b>OUR WORK</b></a></li>
									<li><a href="#about"><b>ABOUT</b></a></li>
									<li><a href="#career"><b>CAREER</b></a></li>
									<li><a href="/quotes" onclick="return false;" class="btn-popout"><b>QUOTES</b></a></li>
								</ul>
						</div><!-- /.container-fluid -->
					</nav>

					<nav class="navbar navbar-default visible-sm visible-xs" style="font-family:'SourceSansPro';">
						<div class="container-fluid">
							<!-- Brand and toggle get grouped for better mobile display -->
							<div class="navbar-header">
								<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2" aria-expanded="false">
									<span class="sr-only">Toggle navigation</span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
								<img class="visible-sm visible-xs" src="{{ asset('frontend/assets/picture/webdigindologo.png') }}" height="30px" style="margin:10px 0px 0px 20px;">
							</div>

							<!-- Collect the nav links, forms, and other content for toggling -->
							<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">

								<ul class="nav navbar-nav">
									<li><a href="#expertise"><b>EXPERTISE</b></a></li>
									<li><a href="#our-work"><b>OUR WORK</b></a></li>
									<li><a href="#about"><b>ABOUT</b></a></li>
									<li><a href="#career"><b>CAREER</b></a></li>
									<!--<li><a href="#contact"><b>CONTACT</b></a></li>-->
									<li><a href="/quotes" onclick="return false;" class="btn-popout"><b>QUOTES</b></a></li>
								</ul>

							</div><!-- /.navbar-collapse -->
						</div><!-- /.container-fluid -->
					</nav>
				</div>

				<div class="container">
					<div class="row">
						<div class="col-md-6">
							<img src="{{ asset('frontend/assets/picture/webdigindologo.png') }}" width="255" class="hidden-xs hidden-sm">

							<h1 style="font-family:'PlayfairDisplay';font-size:30px;" class="header-title">
								Unique, Excellent & <br>
								Innovative is <br>
								our <i>true area of <br>
								expertise</i>
							</h1>
							<h2 style="font-family:'SourceSansPro'; line-height: 1.5;">
								We’re on a mission to make you feel awesome<br class="hidden-sm hidden-xs">
								in every project. Welcome to Digindo Group
							</h2>
							
							<div>
								<a class="btn btn-warning btn-popout" role="button" style="color:white;margin-top: 30px;" href="/quotes" onclick="return false">REQUEST A QUOTE</a>
								<a href="#" class="btn btn-default" id="btnVOW" role="button" style="margin-top: 30px;">VIEW OUR WORK</a>	
							</div>
						</div>
						<div class="col-md-6">
							<div class="container-fluid">
								<div class="row">
									<div class="col-md-12" style="padding-top: 50px">
										<img src="{{ asset('frontend/source/division/digindo-group.png') }}" width="100%">
									</div>
								</div>
								<div class="row">
									<div class="col-md-6" style="padding-top: 50px">
										<a href="{{ asset('frontend/source/compro/compro-marqs.pdf') }}"><img src="{{ asset('frontend/source/division/marqs.png') }}" width="100%"></a>
									</div>
									<div class="col-md-6" style="padding-top: 50px">
										<a href="http://amadeo.id/"><img src="{{ asset('frontend/source/division/amadeo.png') }}" width="100%"></a>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6" style="padding-top: 50px">
										<a href="{{ asset('frontend/source/compro/compro-dimensi.pdf') }}"><img src="{{ asset('frontend/source/division/dimensi.png') }}" width="100%"></a>
									</div>
									<div class="col-md-6" style="padding-top: 50px">
										<a href="{{ asset('frontend/source/compro/compro-tingkat.pdf') }}"><img src="{{ asset('frontend/source/division/tingkat.png') }}" width="100%"></a>
									</div>
								</div>
							</div>
						</div>
					</div>		
				</div>
				
				<div style="position: relative; margin: 50px 0px;" align="center" class="">
					<img class="scrollToNext hidden-xs hidden-sm" style="cursor: pointer;" src="{{ asset('frontend/source/images/mouse.png') }}" width="25px">
				</div>
			</div>
		</div>
	</div>

	<div id="expertise" class="spacing-expertise">
		<div class="anchor"></div>
		<div class="container aniview" av-animation="fadeInLeft" style="color:gray !important;">
			<h2>
				OUR EXPERTISE. THIS IS WHAT WE LOVE TO DO
			</h2>
		</div>

		<div class="container">
			<div class="row" class="spacing-expertise-box">
				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/design.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Design</h3>
								<h4>
									Our in-house design team can produce concept
									sketches and photo realistic visuals using the
									latest CAD and concept modelling software.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/bacground-design.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Design</h3>
										<h4>
										Our in-house design team can produce concept
										sketches and photo realistic visuals using the
										latest CAD and concept modelling software.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;" class="box-expertise"></div>
						</div>
					</div>
				</div>

				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/prototyping.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Prototyping</h3>
								<h4>
									We can produce full scale working design prototypes to aid visualization and successfully proven concept.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px; padding: 0px 20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/background-prototyping.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Prototyping</h3>
										<h4>
										We can produce full scale working prototypes of designs to aid visualisation and successfully prove concepts.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;" class="box-expertise"></div>
						</div>
					</div>
				</div>

				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/manufacture.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Manufacture</h3>
								<h4>
									We have the capability, skills and capacity to
									manufacture your display products in a wide
									range of different materials.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/background-manufacture.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Manufacture</h3>
										<h4>
										We have the capability, skills and capacity to
										manufacture your display products in a wide
										range of different materials.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;" class="box-expertise"></div>
						</div>
					</div>
				</div>

			</div>

			<div class="row" class="spacing-expertise-box">
				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/project-management.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Project Management</h3>
								<h4>
								We can provide a complete project
								management service including the design,
								manufacture, delivery, installation.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/background-project-management.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Project Management</h3>
										<h4>
										We can provide a complete project
										management service including the design,
										manufacture, delivery, installation.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;" class="box-expertise"></div>
						</div>
					</div>
				</div>

				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/creative-design-agency.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Creative Design Agency</h3>
								<h4>
									We will serve as a stand-alone Creative Agency
									that will serve your need for various concept,
									ideas, design and imagery requirements under
									the brand of MARQS.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/background-creative-design-agency.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Creative Design Agency</h3>
										<h4>
										We will serve as a stand-alone Creative Agency
										that will serve your need for various concept,
										ideas, design and imagery requirements under
										the brand of MARQS.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;"  class="box-expertise"></div>
						</div>
					</div>
				</div>

				<div class="animate-fade aniview col-md-4" av-animation="flipInX">
					<div class="hover-animate">
						<div class="hover-animateCF hidden-xs hidden-sm">
							<div id="icon">
								<img src="{{ asset('frontend/source/icon-image/web-development.png') }}" width="25%">
							</div>
							<div id="description">
								<h3>Web Development</h3>
								<h4>
									As in web developing needs, AMADEO will be in
									charge of delivering Information Technology
									based services.
								</h4>
							</div>
							<div style="background:#eec246; margin-top:50px; height:20px;"></div>
						</div>	

						<div class="hover-animateCL">
							<div style="background-image:url('{{ asset('frontend/source/icon-image/background-web-development.png') }}');background-size:cover;height:350px;margin:auto;position:relative;" class="box-expertise">
								<div id="wrapperCL">
									<div id="descriptionCL">
										<h3>Web Development</h3>
										<h4>
										As in web developing needs, AMADEO will be in
										charge of delivering Information Technology
										based services.
										</h4>
									</div>
								</div>
							</div>
							<div style="background:#eec246;height:40px;margin:auto;" class="box-expertise"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="our-work">
		
		<div class="visible-md visible-lg">
			<div class="anchor"></div>
			<div class="container aniview" av-animation="fadeInDown">
				<div class="row">
					<div class="col-md-5">
						<h1 style="font-family:'PlayfairDisplay';font-size:40px;">
							We ensure top notch quality from<br>
							initial prototype<br>
							through final production<br>
						</h1>
						<div style="background-color: rgb(238,194,70); width: 200px; height: 5px; margin-top: 15px;"></div>
					</div>
					<div class="col-md-7">
						<div style="display: block; background-color: rgba(255,255,25,0); height: 160px;">
						</div>
						<h2 style="font-family: 'SourceSansPro';">
							We bring fresh ideas & inspiration to all projects we work on, <br>
							which is why our clients love doing business with us.
						</h2>
					</div>
				</div>
			</div>

			<div class="container" style="margin-top: 20px;">
				<div class="row" style="height: 660px">
					<div class="fade-production col-md-6">
						<div class="tab-production col-md-12 aniview" av-animation="fadeInRight" >
							<div class="hover-animate-production btn-portofolio" style="width: 100%;height:360px;background-image:url('{{ asset('frontend/source/production/DG-SIGNAGE.png') }}');" href="{{ route('frontend.portofolio', ['division' => 'signage']) }}">
								<div>
									<div>
										<div>
											<h3 style="margin: 0px;">Otsuka</h3>
											<h3 style="margin: 0px; text-decoration: italic;">POP Display</h3>
											<br>
											<h4 style="margin: 0px;">View Project</h4>
											<hr align="left" width="50px;">
											<h4 style="margin: 0px;">View portfolio</h4>
										</div>
									</div>
								</div>
								<h2></h2>
							</div>
						</div>
						<div class="tab-production col-md-12 aniview" av-animation="fadeInRight" >
							<div class="hover-animate-production btn-portofolio" style="width: 100%; height:300px;background-image:url('{{ asset('frontend/source/production/DG-BOTH.png') }}');" href="{{ route('frontend.portofolio', ['division' => 'booth']) }}">
								<div>
									<div>
										<div>
											<h3 style="margin: 0px;">Bimoli</h3>
											<h3 style="margin: 0px; text-decoration: italic;">Island Booth</h3>
											<br>
											<h4 style="margin: 0px;">View Project</h4>
											<hr align="left" width="50px;">
											<h4 style="margin: 0px;">View portfolio</h4>
										</div>
									</div>
								</div>
								<h2></h2>
							</div>
						</div>
					</div>

					<div class="fade-production col-md-6">
						<div class="row">
							<div class="tab-production col-md-6 aniview" av-animation="fadeInLeft" >
								<div class="hover-animate-production btn-portofolio" style="width: 100%; height:300px;background-image:url('{{ asset('frontend/source/production/DG-PRINT1.png') }}');" href="{{ route('frontend.portofolio', ['division' => 'print1']) }}">
									<div>
										<div>
											<div>
												<h3 style="margin: 0px;">Extra Joss Blend</h3>
												<h3 style="margin: 0px; text-decoration: italic;">Trolley Display</h3>
												<br>
												<h4 style="margin: 0px;">View Project</h4>
												<hr align="left" width="50px;">
												<h4 style="margin: 0px;">View portfolio</h4>
											</div>
										</div>
									</div>
									<h2></h2>
								</div>
							</div>
	                        <div class="tab-production col-md-6 aniview" av-animation="fadeInLeft" >
								<div class="hover-animate-production btn-portofolio" style="width: 100%; height:300px;background-image:url('{{ asset('frontend/source/production/DG-PRINT2.png') }}');" href="{{ route('frontend.portofolio', ['division' => 'print2']) }}">
									<div>
										<div>
											<div>
												<h3 style="margin: 0px;">Indomilk Banana</h3>
												<h3 style="margin: 0px; text-decoration: italic;">Gondola Display</h3>
												<br>
												<h4 style="margin: 0px;">View Project</h4>
												<hr align="left" width="50px;">
												<h4 style="margin: 0px;">View portfolio</h4>
											</div>
										</div>
									</div>
									<h2></h2>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="tab-production col-md-12 aniview" av-animation="fadeInLeft" >
								<div class="hover-animate-production btn-portofolio" style="width: 100%; height:360px;background-image:url('{{ asset('frontend/source/production/DG-POP.png') }}');" href="{{ route('frontend.portofolio', ['division' => 'pop']) }}">
									<div>
										<div>
											<div>
												<h3 style="margin: 0px;">Orang Tua Group</h3>
												<h3 style="margin: 0px; text-decoration: italic;">Exhibition Booth</h3>
												<br>
												<h4 style="margin: 0px;">View Project</h4>
												<hr align="left" width="50px;">
												<h4 style="margin: 0px;">View portfolio</h4>
											</div>
										</div>
									</div>
									<h2></h2>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="visible-sm visible-xs">
			<div class="anchor"></div>
			<div class="container aniview" av-animation="slideInUp" style="position: relative;">
				<h1 style="font-family:'PlayfairDisplay';font-size:30px;">
					We ensuring that from initial prototype right through to final production
				</h1>
				<div style="background-color: rgb(238,194,70); width: 200px; height: 5px; margin-top: 15px;"></div>
				<h2 style="font-size:16px;font-weight: normal;font-family: 'SourceSansPro';">
					We bring an inspired approach to all projects we work on, which is why our clients love doing business with us and why they keep coming back.
				</h2>
			</div>
			<div class="container" style="margin-top: 20px; position: relative;">
				<div class="tab-production aniview" av-animation="slideInUp" style="width: 100%; margin: 10px 0px;">
					<div style="position: relative;width: 100%; height:100px;background-image:url('{{ asset('frontend/source/production/DG-SIGNAGE.png') }}');background-repeat:no-repeat;background-size:100%;background-position:center; display: table-cell; vertical-align: bottom; color: rgb(255,255,255);" href="/portofolio?division=signage" class="btn-portofolio">
						<h2 style="padding: 0 25px;"></h2>
					</div>
				</div>

				<div class="tab-production aniview" av-animation="slideInUp" style="width: 100%; margin: 10px 0px;">
					<div style="position: relative;width: 100%; height:100px;background-image:url('{{ asset('frontend/source/production/DG-BOTH.png') }}');background-repeat:no-repeat;background-size:100%;background-position:center; display: table-cell; vertical-align: bottom; color: rgb(255,255,255);" href="/portofolio?division=signage" class="btn-portofolio">
						<h2 style="padding: 0 25px;"></h2>
					</div>
				</div>

				<div class="tab-production aniview" av-animation="slideInUp" style="width: 100%; margin: 10px 0px;">
					<div style="position: relative;width: 100%; height:100px;background-image:url('{{ asset('frontend/source/production/DG-PRINT1.png') }}');background-repeat:no-repeat;background-size:100%;background-position:center; display: table-cell; vertical-align: bottom; color: rgb(255,255,255);" href="/portofolio?division=print1" class="btn-portofolio">
						<h2 style="padding: 0 25px;"></h2>
					</div>
				</div>

				<div class="tab-production aniview" av-animation="slideInUp" style="width: 100%; margin: 10px 0px;">
					<div style="position: relative;width: 100%; height:100px;background-image:url('{{ asset('frontend/source/production/DG-PRINT2.png') }}');background-repeat:no-repeat;background-size:100%;background-position:center; display: table-cell; vertical-align: bottom; color: rgb(255,255,255);" href="/portofolio?division=print2" class="btn-portofolio">
						<h2 style="padding: 0 25px;"></h2>
					</div>
				</div>

				<div class="tab-production aniview" av-animation="slideInUp" style="width: 100%; margin: 10px 0px;">
					<div style="position: relative;width: 100%; height:100px;background-image:url('{{ asset('frontend/source/production/DG-POP.png') }}');background-repeat:no-repeat;background-size:100%;background-position:center; display: table-cell; vertical-align: bottom; color: rgb(255,255,255);" href="/portofolio?division=pop" class="btn-portofolio">
						<h2 style="padding: 0 25px;"></h2>
					</div>
				</div>
			</div>
		</div>

		<div class="text-center aniview" av-animation="fadeInDown">
			<button href="{{ route('frontend.portofolio') }}" class="btn-portofolio" style="border: medium solid;background: transparent;padding: 10px;">BROWSE OUR PORTFOLIO</button>
		</div>
	</div>



	<div id="about">
		<div class="anchor"></div>
		<div style="margin: 80px 0px 0px; padding: 0px;position: relative;" class="visible-md visible-lg">
			<div class="center-block" style="max-width: 1360px;">
				<div class="container-fluid">
					<div class="row" style="position: relative;">
						<div style="width:500px;background:white;padding:10px 50px;float:left; margin-left: 50px;">
							<h1 style="font-family:'PlayfairDisplay';font-size:40px;">
								Digindo Group in a nutshell
							</h1>
							<span style="color: rgb(238,194,70);">
								_________________________
							</span>
							<h2 style="font-family:'SourceSansPro';line-height:25px;">
								Digindo, founded in 2009 by a group of creative
								people	that produces lasting results for their clients. 
								Digindo stands for style and class. Many national and
								international design are in the Digindo portfolio. 
								Because the language of quality design is universal, right?
								Together with you, our partner, we look forward to
								forging a unique identity, an exciting brand concept or an
								eye-catching products.
							</h2>
						</div>
						
						<div style="position:absolute;width:70%;right:0px;top:30px;z-index:-1;background-image:url('{{ asset('frontend/source/images/working-team1-img.jpg') }}');background-repeat:no-repeat;background-size:cover;background-position:center;height:130%;">
						</div>
					</div>
				</div>

				<div class="container-fluid">
					<div class="row" style="position: relative;">
						<div style="width:450px;height:370px;margin-left: 100px;z-index:-1;background-image:url('{{ asset('frontend/source/images/working-team2-img.jpg') }}');background-repeat:no-repeat;background-size:cover;background-position:center;">
						</div>

						<div style="position: absolute; left:550px; top:0px;	width:480px;height:450px;background:white;padding:10px 20px 10px 50px;">
							<h1 style="font-family:'PlayfairDisplay';font-size:40px;">
								"It's not only about
								beauty, it's about
								function."
							</h1>
							<span style="color: rgb(238,194,70);">_________________________</span>
							<br>
							<h2 style="font-family:'SourceSansPro';line-height:25px;">
								Digindo merges art, design, information & technology
								into an exciting new communication concept. We do this in
								close consultation with our partners – because brainstorming
								is what we are good at. Each project requires a detailed
								analysis and a fresh approach – the only way to stimulate
								those extra creative impulses!
							</h2>
						</div>
					</div>
				</div>

				<div class="container-fluid" style="position: relative; padding: 0px; margin: 0px;">			
					<div align="center"  style="width:450px;margin: 30px 0px 30px 100px;">
						<img src="{{ asset('frontend/source/images/ThanksForStoppingBy-img.png') }}" width="225px">
					</div>
					
					<div style="background:white;padding:10px 20px 10px 50px; margin: 50px 200px 0px;">
						<h1 style="font-family:'PlayfairDisplay';font-size:40px;">
							The talent behind the<br>
							reputation
						</h1>
						<span style="color: rgb(238,194,70);">_________________________</span>
						<br>
						<h2 style="font-family:'SourceSansPro';line-height:25px;">
							Digital Products is our business. We attach great importance to ‘craftsmanship’,
							but also to service and prompt delivery. Our past partners – whether they be the government,
							trend-setting companies or even private individuals - have always been appreciative of our
							professional dynamism. Our team is both close-knit and ultra-professional. And we are not
							merely interested in form – content and meaning are just as important. Everything that we
							do has a strategic purpose. You want an original design, tailored to your needs? The creative
							team Digindo pulls out all the stops for your project, with a unique design.
							The result? As we like to call: pixel perfect. 
							Sit back, relax and let our designer do all the work.
						</h2>
					</div>			
				</div>
			</div>

			<div style="position:absolute;width:100%;bottom:300px;background:#eec246;height:500px;z-index:-10;padding:0;"></div>
		</div>

		<div class="visible-sm visible-xs">
			<div class="container-fluid" style="padding: 0px; margin: 0px;">
				<div  style="width:100%;margin:0px;background-image:url('{{ asset('frontend/source/images/working-team1-img.jpg') }}');background-repeat:no-repeat;background-size:cover;background-position:center;">
					<div style="width: 100%; background-color:rgba(0,0,0,0.5); padding:10px 20px;">
						<h1 style="font-family:'PlayfairDisplay';font-size:25px;color: white;">
							Digindo Group in a nutshell
						</h1>
						<span style="color: rgb(238,194,70);">
							_________________________
						</span>
						<h2 style="font-family:'SourceSansPro';line-height:25px; text-align:justify; color: white; font-size:16px;">
							Digindo, founded in 2009 by a group of creative people  that produces lasting results for their clients. Digindo stands for style and class. Many national and international design are in the Digindo portfolio. Because the language of quality design is universal, right? Together with you, our partner, we look forward to forging a unique identity, an exciting brand concept or an eye-catching products.
						</h2>
					</div>
				</div>
				<div style="width:100%;margin:0px;background-image:url('{{ asset('frontend/source/images/working-team2-img.jpg') }}');background-repeat:no-repeat;background-size:cover;background-position:center;">
					<div style="background-color:rgba(0,0,0,0.5); padding:10px 20px; width: 100%">
						<h1 style="font-family:'PlayfairDisplay';font-size:25px;color: white;">
							"It's not only about beauty, it's about function."
						</h1>
						<span style="color: rgb(238,194,70);">
							_________________________
						</span>
						<h2 style="font-family:'SourceSansPro';line-height:25px; text-align:justify; color: white; font-size:16px;">
							Digindo merges art, design, information & technology into an exciting new communication concept. We do this in close consultation with our partners – because brainstorming is what we are good at. Each project requires a detailed analysis and a fresh approach – the only way to stimulate those extra creative impulses!
						</h2>
					</div>
				</div>
				<div  style="width:100%;margin:0px;background:white;">
					<div style="background-color:rgba(0,0,0,0); padding:10px 20px; width: 100%">
						<h1 style="font-family:'PlayfairDisplay';font-size:25px;color: black;">
							The talent behind the reputation
						</h1>
						<span style="color: rgb(238,194,70);">
							_________________________
						</span>
						<h2 style="font-family:'SourceSansPro';line-height:25px; text-align:justify; color: black; font-size:16px;">
							Digital Products is our business. We attach great importance to ‘craftsmanship’, but also to service and prompt delivery. Our past partners – whether they be the government, trend-setting companies or even private individuals - have always been appreciative of our professional dynamism. Our team is both close-knit and ultra-professional. And we are not merely interested in form – content and meaning are just as important. Everything that we do has a strategic purpose. You want an original design, tailored to your needs? The creative team Digindo pulls out all the stops for your project, with a unique design. The result? As we like to call: pixel perfect. Sit back, relax and let our designer do all the work.
						</h2>
					</div>
				</div>
			</div>
		</div>
	</div>
		
	<div id="client" style="color:gray !important;" class="spacing-client">
		<div class="container">
			<h1 style="text-align: center;">BRANDS WE’VE WORKED FOR</h1>
			<br>
			<br>
		
			<div class="row" style="margin: 0px 20px;">
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/danone-logo.png') }}" height="50">
				</div>
				<div class="col-md-1 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/mayora-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/indofood-logo.png') }}" height="50">
				</div>
				<div class="col-md-2 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/cimory-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/ifm-logo.png') }}" height="50">
				</div>

				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/wendy-logo.png') }}" height="50">
				</div>
				<div class="col-md-1 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/nestle-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/delfi-logo.png') }}" height="50">
				</div>
				<div class="col-md-2 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/wardah-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/kalbe-logo.png') }}" height="50">
				</div>

				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/baygon-logo.png') }}" height="50">
				</div>
				<div class="col-md-1 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/kiwi-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/sidomuncul-logo.png') }}" height="50">
				</div>
				<div class="col-md-2 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/tempo-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/citilink-logo.png') }}" height="50">
				</div>

				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/pepsi-logo.png') }}" height="50">
				</div>
				<div class="col-md-1 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/acc-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/astra-logo.png') }}" height="50">
				</div>
				<div class="col-md-2 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/bri-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/bintang-logo.png') }}" height="50">
				</div>

				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/lg-logo.png') }}" height="50">
				</div>
				<div class="col-md-1 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/unilever-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/siloam-logo.png') }}" height="50">
				</div>
				<div class="col-md-2 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/alfamart-logo.png') }}" height="50">
				</div>
				<div class="col-md-3 text-center img-grayscale aniview" av-animation="zoomIn" style="padding:20px 0px;">
					<img src="{{ asset('frontend/source/logo-client/jco-logo.png') }}" height="50">
				</div>

			</div>
		</div>
	</div>

	<div id="team" style="margin: 20px 0px 0px;">
		<div class="container">
			<h1 style="font-family:'PlayfairDisplay';font-size:40px;">
				<strong>Join our team</strong>
			</h1>
			<h2>
				We’re a big group working hard to create products people love
			</h2>
		</div>
		<div class="container-fluid hidden-sm hidden-xs" style="background-image:url('{{ asset('frontend/source/images/join-our-team.jpg') }}');background-repeat:no-repeat;background-size:cover;background-position:0px 50px;background-attachment: fixed; height: 665px; padding: 0px;">
		</div>
	</div>
		
	<div id="career" style="background-image:url('{{ asset('frontend/source/images/form-background.jpg') }}');background-size:100%;background-repeat: no-repeat; background-position: 0px 165px; position: relative;">
		<div class="anchor"></div>
		<div class="gradient-form">
			
		</div>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1 style="color:gray !important;">CURRENT AVAILABLE POSITIONS</h1>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									3D<br>
									Drafter
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								Lead design team to<br>
								created design solutions<br>
								for clients
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.triD') }}" onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									Finance<br>
									Administrator
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								Ensuring effective financial<br>
								administration, and<br>
								clerical operations
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.finance') }}" onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									Graphic<br>
									Designer
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								Conceptualise and oversee<br>
								the design production<br>
								process
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.graphic') }}" onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									Account<br>
									Executive
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								Maintain the beneficial<br>
								relationship with the<br>
								clients
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.account') }}" onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									Marketing
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								Oversee the market, create<br>
								& analyse relevant<br>
								promotional campaigns
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.marketing') }}"  onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="margin-top: 20px;">
					<div class="position-box aniview" av-animation="fadeInUp">
						<div id="title">
							<h1>
								<strong>
									Intern
								</strong>
							</h1>
						</div>
						<div id="content">
							<h2>
								We welcome all students<br>
								to join our various exciting<br>
								internship programs
							</h2>
							
						</div>
						<div id="footer">
							<a class="btn-portofolio learn-more" href="{{ route('frontend.intern') }}" onclick="return false">
								LEARN MORE
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="container" id="career">
			<div class="row">
				<div class="col-md-6">
					<h1 style="color:gray !important;">APPLICATION FORM</h1>
					@if (count($errors) > 0)
					    <div class="alert alert-danger">
					        <ul>
					            @foreach ($errors->all() as $error)
					                <li>{{ $error }}</li>
					            @endforeach
					        </ul>
					    </div>
					@endif
					@include('frontend._include.messages')
					<form method="post" action="{{ route('backend.jobApply.store') }}" enctype="multipart/form-data">
						{!! csrf_field() !!}
						<div class="form-group">
							<h2>Full Name</h2>
							<input type="text" class="form-control" id="" name="fullname" value="{{ old('fullname') }}">
						</div>
						<div class="form-group">
							<h2>Mobile Number</h2>
							<input type="text" class="form-control" id="" name="phone"  value="{{ old('phone') }}">
						</div>
						<div class="form-group">
							<h2>E-Mail Address</h2>
							<input type="text" class="form-control" id="" name="email" value="{{ old('email') }}">
						</div>
						<div class="form-group">
							<h2>Position</h2>
							<select class="form-control" id="position" name="position">
								<option value="Art Director" @if(old('position') == 'Art Director') selected="selected" @endif>Art Director</option>
								<option value="Finance Administrator" @if(old('position') == 'Finance Administrator') selected="selected" @endif>Finance Administrator</option>
								<option value="Graphic Designer" @if(old('position') == 'Graphic Designer') selected="selected" @endif>Graphic Designer</option>
								<option value="Account Executive" @if(old('position') == 'Account Executive') selected="selected" @endif>Account Executive</option>
								<option value="Marketing" @if(old('position') == 'Marketing') selected="selected" @endif>Marketing</option>
								<option value="Intern" @if(old('position') == 'Intern') selected="selected" @endif>Intern</option>
							</select>
						</div>
						<div class="form-group">
							<h2>Message</h2>
							<textarea class="form-control" id="" name="message" rows="10">{{ old('message') }}</textarea>
						</div>

						<div class="form-group">
							<h2>Upload your CV & Portfolio</h2>
							<input id="uploadFile" placeholder="Choose File" disabled="disabled" class="form-control" />
							<div class="fileUpload btn btn-warning">
							    <span>BROWSE FILE</span>
							    <input id="uploadBtn" type="file" class="upload" name="attachment"/>
							</div>
							<p>Please upload your CV and portfolio on .zip files, maximum file size 5 MB</p>
						</div>

						<div class="g-recaptcha" data-sitekey="6Ld6SA0UAAAAAON7641y3AMM-5mQxYwHIkv70Xf4"></div>

						<script type="text/javascript">
							document.getElementById("uploadBtn").onchange = function () {
							    document.getElementById("uploadFile").value = this.value;
							};
						</script>

						<div class="form-group" style="padding-top: 20px;">
							<input type="submit" id="" value="SUBMIT" style="background-color: rgb(238,194,70); border:0px; padding: 5px 20px; color: white; text-decoration: bold;">
							<input type="reset" id="" value="CANCEL" style="background-color: rgb(221,221,221); border:0px; padding: 5px 20px; color: white; text-decoration: bold;">
						</div>

					</form>
				</div>

				<div class="col-md-offset-1 col-md-5 form-right-panel">
					<div class="middle-window">
						<div>
							<div style="z-index:1;" class="form-img-company">
								<img src="{{ asset('frontend/assets/picture/webdigindologo.png') }}" width="255">
							</div>

							<div style="color:black;" class="form-text">
								<h3 style="font-family:'PlayfairDisplay';">Let’s make it work!</h3>
								<p>
									Ready to take it a step further?<br>
									Let’s start talking about your project or idea and find out<br>
									how Digindo can help your business grow.
								</p>
							</div>

							<div style="padding:10px 0px;">
								<a href="{{ route('frontend.quotes') }}" onclick="return false;" class="btn-popout btn btn-warning" role="button" style="color:black;font-family:'Gotham-Bold';">REQUEST A FREE QUOTE</a>
							</div>
							<!--
							<div style="padding:10px 0px;font-size:20px;">
								<a href="https://www.facebook.com/" style="color:black;"><i class="fa fa-facebook" aria-hidden="true"></i></a> 
								<a href="https://twitter.com/" style="color:black;"><i class="fa fa-twitter" aria-hidden="true"></i></a> 
								<a href="https://pinterest.com/" style="color:black;"><i class="fa fa-pinterest" aria-hidden="true"></i></a> 
							</div>
							-->
						</div>
					</div>
				</div>					
			</div>
		</div>
	</div>

	<div id="footer" style="font-family:'SourceSansPro';">
		<div class="container-fluid" style="padding:0;position:relative;background-image:url('{{ asset('frontend/source/images/footer-img.jpg') }}');background-size:cover;">

			<div class="container" style="padding:31px 15px 10px;color:white;">
				<div class="row">
					<div class="col-md-6">
						<p>
							<span style="color:#ec971f;">REGISTERED OFFICE</span><br>
							PT. DIGITAL INDONESIA<br>
							PANGERAN TUBAGUS ANGKE<br>
							KOMPLEK BNI 46. BLOK TT NO. 22-23A<br>
							INDONESIA
						</p>
					</div>
					<div class="col-md-6">
						<p>
							<span style="color:#ec971f;">A group company of :</span>
						</p>
						<div class="row">
							<div class="col-md-3 text-center">
								<a href="http://marqs.co.id/"><img src="{{ asset('frontend/source/images/marqs-white.png') }}" style="padding:10px 0px" width="100"></a>
							</div>
							<div class="col-md-3 text-center">
								<a href="http://amadeo.id/"><img src="{{ asset('frontend/source/images/amadeo-white.png') }}" style="padding:10px 0px" width="100"></a>
							</div>
							<div class="col-md-3 text-center">
								<a href="{{ asset('frontend/source/compro/compro-dimensi.pdf') }}"><img src="{{ asset('frontend/source/images/dimensi-white.png') }}" style="padding:10px 0px" width="100"></a>
							</div>
							<div class="col-md-3 text-center">
								<a href="{{ asset('frontend/source/compro/compro-tingkat.pdf') }}"><img src="{{ asset('frontend/source/images/tingkat-white.png') }}" style="padding:10px 0px" width="100"></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="container">
				<hr>
			</div>

			<div class="container text-center" style="padding-bottom: 18px;color:white;">
				<img src="{{ asset('frontend/source/images/digindo-white.png') }}" height="20px"> 2016 DIGINDO - ALL RIGHTS RESERVED &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			</div>
		</div>

	</div>
	
@endsection