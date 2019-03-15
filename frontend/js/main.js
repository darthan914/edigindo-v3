	// Hover Button in Header
	 
		$(function(){$("#btnVOW").hover(
			function(){
			$("#popup-firstButtonHeader").removeClass("btn-warning").addClass("btn-default").css("color","black");
			$("#btnVOW").removeClass("btn-default").addClass("btn-warning").css("color","white");
			},
			function(){
			$("#popup-firstButtonHeader").removeClass("btn-default").addClass("btn-warning").css("color","white");
			$("#btnVOW").removeClass("btn-warning").addClass("btn-default").css("color","black");
			}
		)
	})
	 

	// Scroll down to 
	 
		$(document).ready(function() {
			$(".scrollToNext").click(function(event){
				$('html, body').animate({scrollTop: $("#expertise").offset().top}, 800);
			});
		});

	// scroll fadeIn
		$(function () {
            var win = $(window);
            var initNavbar = 100;

            win.scroll(function () {
                 
                if (win.scrollTop() >= initNavbar) {
					$("#navbarFixedTop").fadeIn("slow");
                 }

                else if (win.scrollTop() <= initNavbar) {
					$("#navbarFixedTop").fadeOut("slow");
                 }

             });
         });
	 

	 
		$(function(){
			$(".hover-animate").hover(
				function(){
					$(this).children().first().animate({top: "-100%"},'slow');
					$(this).children().first().css("opacity", "0");
					$(this).children().last().animate({top: "-100%"},'slow');
				},
				function(){
					$(this).children().first().css("opacity", "1");
					$(this).children().first().animate({top: "0%"},'slow');
					$(this).children().last().animate({top: "0%"},'slow');
			});
		})
	 

	 
		$(function(){
		$(".hover-animate-production").css({overflow: 'hidden', position: 'relative'});
		$(".hover-animate-production").children().css({position: 'relative'});
		$(".hover-animate-production").hover(
		function(){
			$(this).children().first().fadeIn('slow');
			$(this).children().last().hide('slow');
		},
		function(){
			$(this).children().first().fadeOut('slow');
			$(this).children().last().show('slow');
		});
		});
	
$(document).ready(function(){
	$(".btn-popout").click(function(){
		var getURL = $(this).attr("href");
		$(".popout").fadeIn();
		$.ajax({url: getURL,
			success: function(result){
				$(".content").html(result);
			}
		});
	});
});

$(document).ready(function(){
	$(".btn-portofolio").click(function(){
		var getURL = $(this).attr("href");
		$(".popout-portofolio").fadeIn();
		$.ajax({url: getURL,
			success: function(result){
				$(".content-portofolio").html(result);
			}
		});
	});
});
