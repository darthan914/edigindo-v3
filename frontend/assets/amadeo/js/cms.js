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
    $('[data-toggle="tooltip"]').tooltip(); 
});