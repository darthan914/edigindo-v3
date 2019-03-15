<div class="popout-portofolio">	
	<div class="middle-window">
		<div>
			<div class="content-portofolio">
			</div>
			<a href="#close" class="popoutClose" onclick="return false"><i class="fa fa-times-circle-o fa-3x" aria-hidden="true" style="color: red;"></i></a>
		</div>
	</div>
</div>

<script type="text/javascript">
		$(".popoutClose").click(function(){
			$(".popout-portofolio").fadeOut( function(){
				$(".content-portofolio").html('');
			});
		});
</script>