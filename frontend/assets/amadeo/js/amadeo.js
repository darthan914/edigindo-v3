// Hover effect

$(function(){
	$(".hover-slide-up").css({overflow: 'hidden', position: 'relative'});
	$(".hover-slide-up").children().css({position: 'absolute', top : '100%', width: '100%', height: '100%'});
	$(".hover-slide-up").hover(
		function(){
			$(this).children().animate({top: "0%"},'fast');
		},
		function(){
			$(this).children().animate({top: "100%"},'fast');
		});
});

$(function(){
	$(".hover-slide-down").css({overflow: 'hidden', position: 'relative'});
	$(".hover-slide-down").children().css({position: 'absolute', bottom : '100%', width: '100%', height: '100%'});
	$(".hover-slide-down").hover(
		function(){
			$(this).children().animate({bottom: "0%"},'fast');
		},
		function(){
			$(this).children().animate({bottom: "100%"},'fast');
		});
});

$(function(){
	$(".hover-slide-left").css({overflow: 'hidden', position: 'relative'});
	$(".hover-slide-left").children().css({position: 'absolute', top : '0%', left : '100%', width: '100%', height: '100%'});
	$(".hover-slide-left").hover(
		function(){
			$(this).children().animate({left: "0%"},'fast');
		},
		function(){
			$(this).children().animate({left: "100%"},'fast');
		});
});

$(function(){
	$(".hover-slide-right").css({overflow: 'hidden', position: 'relative'});
	$(".hover-slide-right").children().css({position: 'absolute', top : '0%', right : '100%', width: '100%', height: '100%'});
	$(".hover-slide-right").hover(
		function(){
			$(this).children().animate({right: "0%"},'fast');
		},
		function(){
			$(this).children().animate({right: "100%"},'fast');
		});
});

$(function(){
	$(".hover-fade").css({overflow: 'hidden', position: 'relative'});
	$(".hover-fade").children().css({position: 'absolute', top : '0%', width: '100%', height: '100%', display: 'none'});
	$(".hover-fade").hover(
		function(){
			$(this).children().fadeIn('fast');
		},
		function(){
			$(this).children().fadeOut('fast');
		});
});

$(function(){
		
		//add class in for open by default
		if($(".toggleSlideCanvas").hasClass("in"))
		{
			$(".mainCanvas").css({
				"position":"fixed",
				"width":"80%",
				"top":"0%",
				"left":"20%",
				"z-index":"1"
			});
			$(".menuCanvas").css({
				"position":"fixed",
				"width":"20%",
				"top":"0%",
				"left":"0%"
			});
			
		}
		else
		{
			$(".mainCanvas").css({
				"position":"fixed",
				"width":"100%",
				"top":"0%",
				"left":"0%",
				"background":"white",
				"z-index":"1"
			});
			$(".menuCanvas").css({
				"position":"fixed",
				"width":"20%",
				"top":"0%",
				"left":"0%"
				
			});
		}
		
		//toggle slide in
		$(".toggleSlideCanvas").click(function(){
			if($(".toggleSlideCanvas").hasClass("in"))
			{
				$(".mainCanvas").animate({left: '0%', width: '100%'},'fast');
				$(".menuCanvas").animate({},'fast');
				$(".toggleSlideCanvas").removeClass("in");
			}
			else
			{
				$(".mainCanvas").animate({left: '20%', width: '80%'},'fast');
				$(".menuCanvas").animate({},'fast');
				$(".toggleSlideCanvas").addClass("in");
			}
		});
	});




//------------------







	
	// Shrink Grow for header logo and header menu
	/*

	var sg, animation;
	var offsetSG = 20;
	$(window).on("load resize", function(){
	    
		if(window.outerWidth >= 992)
		{
			animation = 1;
			$(function(){
				if(window.pageYOffset <= offsetSG && window.outerWidth >= 992)
				{
					$(".navbarHeaderSG").css({height: '400%'});
					$(".navbarFontSG").css({fontSize: '24px', height:'80px'});
					$(".navbarFontSG>li>a").css({lineHeight: '48px'});	
					sg = 1;
				}
				else if (window.pageYOffset > offsetSG && window.outerWidth >= 992)
				{
					$(".navbarHeaderSG").css({height: '200%'});
					$(".navbarFontSG").css({fontSize: '15px', height:'60px'});
					$(".navbarFontSG>li>a").css({lineHeight: '30px'});	
					sg = 0;
				}
			});
		}
		else
		{
			animation = 0;
			$(function(){
					$(".navbarHeaderSG").css({height: '100%'},100);
			});
		}
	});

	$(window).scroll(function(){
		if(window.pageYOffset <= offsetSG && !sg && animation)
		{
			$(".navbarHeaderSG").animate({height: '400%'},100);
			$(".navbarFontSG").animate({fontSize: '24px', height:'80px'},100);
			$(".navbarFontSG>li>a").animate({lineHeight: '48px'},100);	
			sg = 1;
		}
		else if (window.pageYOffset > offsetSG && sg && animation)
		{
			$(".navbarHeaderSG").animate({height: '200%'},100);
			$(".navbarFontSG").animate({fontSize: '15px', height:'60px'},100);
			$(".navbarFontSG>li>a").animate({lineHeight: '30px'},100);	
			sg = 0;
		}
	});
	*/

	// Smooth Scroll (add class tragetScroll of child body element)
	/*
	$(function(){
		var currentOffset = window.pageYOffset;
		var incrementOffset = window.innerHeight;
		
		// Check if class targetScroll is exist
		try {
			// Set element of class targetScroll by id name and offset
			var currentElement = $("body").children(".targetScroll").attr("id");
			var currentElementOffset =  $("#" + currentElement).offset().top;
		}
		catch (err) {
			var currentElement = '';
			var currentElementOffset =  0;
		}
		
		// collect number of class targetScroll for loop
		var totalElement = $("body").children(".targetScroll").length;
		
		var x = 0;
		
		
		do{
			//check for top page to current offset scroll
			if(currentElementOffset < currentOffset)
			{
				// Check if next element class targetScroll is exist
				try {
					currentElement = $("#" + currentElement).next().attr("id");
					currentElementOffset = $("#" + currentElement).offset().top;
				}
				catch (err) {
					currentElement = $("body").children(".targetScroll").last().attr("id");
					currentElementOffset =  $("#" + currentElement).offset().top;
				}
			}
			else
			{
				//if false break loop
				break;
			}
			x++;
			
		}while(x < totalElement)
		
		//Update every scroll
		$(window).scroll(function(){ 
			currentOffset = window.pageYOffset;
			try {
				currentElement = $("body").children(".targetScroll").attr("id");
				currentElementOffset =  $("#" + currentElement).offset().top;
			}
			catch (err) {
				currentElement = '';
				currentElementOffset =  0;
			}
			x = 0;
		
			do{
				if(currentElementOffset + 50 < currentOffset)
				{
					try {
						currentElement = $("#" + currentElement).next().attr("id");
						currentElementOffset = $("#" + currentElement).offset().top;
					}
					catch (err) {
						currentElement = $("body").children(".targetScroll").last().attr("id");
						currentElementOffset =  $("#" + currentElement).offset().top;
					}
				}
				else
				{
					break;
				}
				x++;
				
			}while(x < totalElement)
		});
		
		//scroll by class targetScroll
		$(".scrollToElement").click(function() {
			$('html,body').animate({scrollTop: $("#"+currentElement).next().offset().top - 50},'slow');
		});
		
		//scroll by height window
		$(".scrollWindow").click(function() {
			currentOffset = currentOffset + incrementOffset;
			$('html,body').animate({scrollTop: currentOffset},'slow');
		});
		
		//scroll to top
		$(".scrollTop").click(function() {
			$('html,body').animate({scrollTop: 0},'slow');
		});
		
		//scroll by name id
		$(".scrollContact").click(function() {
			$('html,body').animate({scrollTop: $("#contact").offset().top},'slow');
		});
		
	});
	*/

	//Hover info
	/*
	$(function(){
		// set css overflow hidden
		$(".hoverInfo").css({overflow: 'hidden'});

		// effect every mouse over and mouse exit
		$(".hoverInfo").hover(
			//hover
			function(){$(this).children(".overlayInfo").animate({top: "0%"},'fast');},
			//exit
			function(){$(this).children(".overlayInfo").animate({top: "100%"},'fast');}
		);
	});
	*/
	
	//popupWindow
	/*
	$(function(){
		$(".popupOpen").click(function(){
			//optional
			$(".popupOpen").removeClass("active");
			$(this).addClass("active");

			// set to popupWindow
			$(".imageContent").attr("src",$(this).children().children(".GetImage").html());
			$(".titleContent").html($(this).children().children(".GetTitle").html());
			$(".descriptionContent").html($(this).children().children(".GetDescription").html());
			$(".nameContent").html($(this).children().children(".GetName").html());

			//required
			$(".popupWindow").fadeIn();
		});
		$(".popupClose").click(function(){
			$(".popupWindow").fadeOut();
		});
	});
	*/
	
	//cms toggle Slide Canvas
	/*

	//add style
	
	*/
	