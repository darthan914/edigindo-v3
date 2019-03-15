<template>
	<div class="row notification">
		<div class="col-md-12 col-sm-12 col-xs-12" v-for="list in notifcontent">
			<div class="alert alert-info alert-dismissible fade in" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
				</button>
				<div class="row">
					<div class="col-xs-2">
						<i class="fa fa-info-circle" aria-hidden="true" style="color: white;font-size: 30px;"></i>
					</div>
					<div class="col-xs-8">
						<p><strong>From : {{ list.from }}</strong></p>
						<p v-html="list.messages"></p>
					</div>
					<div class="col-xs-2">
						<strong><a :href="'/edigindo/notification/'+list.id+'/view'" ><i class="fa fa-arrow-right" aria-hidden="true" style="    color: white;font-size: 20px;"></i></a></strong>
					</div>
					
					
				</div>
				
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		props : ['notifcontent'],
		methods: {
			call: function (event) {
				// $(".dropdown-menu.list-unstyled.msg_list").html('');
				// $.post( "/edigindo/notification/ajaxNavbar", function( data ) {
				// 	$(".dropdown-menu.list-unstyled.msg_list").html(data);
				// });
			}
		}
	}
</script>