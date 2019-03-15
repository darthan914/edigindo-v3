@include('frontend._include.head')


<form method="post" action="{{ route('backend.quote.store') }}" name="form">
	{!! csrf_field() !!}
	<div class="container-fluid" style="position: relative;">
		<div style="position: absolute;left: 0%;top: 0px;width: 100%;margin-right: 20px;" class="left-side">
			<div class="text-center"><h3><i class="fa fa-users" aria-hidden="true"></i> Step 1. Who are you?</h3></div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="firstname">Firstname*</label>
						<input type="text" class="form-control input-underline" id="firstname" name="firstname">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="lastname">Lastname*</label>
						<input type="text" class="form-control input-underline" id="lastname" name="lastname">
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="email">E-mail*</label>
						<input type="email" class="form-control input-underline" id="email" name="email">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="phone">Phone*</label>
						<input type="text" class="form-control input-underline" id="phone" name="phone">
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="company">Company*</label>
						<input type="text" class="form-control input-underline" id="company" name="company">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="region">Region*</label>
						<input type="text" class="form-control input-underline" id="region" name="region">
					</div>
				</div>
			</div>

			<a href="#" class="btn btn-default next" onclick="return false">Next <i class="fa fa-hand-o-right" aria-hidden="true"></i></a>
		</div>

		<div style="position: absolute;left: 100%;top: 0px;width: 100%;margin: 20px;" class="right-side">
			<div class="text-center"><h3><i class="fa fa-users" aria-hidden="true"></i> Step 2. What are you looking for?</h3></div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="interested">What are you interested in?</label>
						<input type="text" class="form-control input-underline" id="interested" name="interested">
					</div>
					<div class="form-group">
						<label for="services">Do you need additional services?</label>
						<input type="text" class="form-control input-underline" id="services" name="services">
					</div>
					<div class="form-group">
						<label for="deadline">What’s your deadline?</label>
						<input type="text" class="form-control input-underline" id="deadline" name="deadline">
					</div>
					<div class="form-group">
						<label for="budget">What is your budget?</label>
						<input type="text" class="form-control input-underline" id="budget" name="budget">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="project_description">Additional information (brief description of your project)</label>
						<textarea class="form-control" name="project_description" id="project_description" rows="12"></textarea>
					</div>
				</div>
			</div>
			<div class="text-right">
				<a href="#" class="btn btn-default prev" onclick="return false">Prev <i class="fa fa-hand-o-left" aria-hidden="true"></i></a>
				<button class="btn btn-default">Send <i class="fa fa-paper-plane" aria-hidden="true"></i></button>
			</div>
		</div>
	</div>
</form>

<script>
		function validateFirstForm() {
			var invalid = 0;
			var massage ="";
			
			var firstname = document.forms["form"]["firstname"].value;
			var lastname = document.forms["form"]["lastname"].value;
			var email = document.forms["form"]["email"].value;
			var phone = document.forms["form"]["phone"].value;
			var company = document.forms["form"]["company"].value;
			var region = document.forms["form"]["region"].value;

			if (firstname == null || firstname == "") {
				invalid++;
				massage = massage + "Firstname Required\n";
			}
			if (lastname == null || lastname == "") {
				invalid++;
				massage = massage + "Lastname Required\n";
			}
			if (email == null || email == "") {
				invalid++;
				massage = massage + "E-mail Required\n";
			}
			if (phone == null || phone == "") {
				invalid++;
				massage = massage + "Phone Required\n";
			}
			if (company == null || company == "") {
				invalid++;
				massage = massage + "Company Required\n";
			}
			if (region == null || region == "") {
				invalid++;
				massage = massage + "Region Required\n";
			}
			
			if(invalid != 0)
			{
				//alert("Name must be filled out");
				alert(massage);
		        return false;
			}
			else
			{
				return true;
			}
		}

		$(document).ready(function(){
		    $(".next").click(function(){
		    	if(validateFirstForm())
		    	{
		    		 $(".left-side").animate({left: '-110%'});
		    		 $(".right-side").animate({left: '-2%'});
		    	}
		    });

		    $(".prev").click(function(){
		    	if(validateFirstForm())
		    	{
		    		 $(".left-side").animate({left: '0%'});
		    		 $(".right-side").animate({left: '100%'});
		    	}
		    });
		});
</script>
