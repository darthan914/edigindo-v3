<template>
    <li role="presentation" class="dropdown">
        <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false" v-on:click="call">
            <i class="fa fa-bell-o"></i>
            <span class="badge bg-green" v-html="htmlunreadcount"></span>
        </a>
        <ul class="dropdown-menu list-unstyled msg_list" role="menu">

            
        </ul>
    </li>
</template>

<script>
    export default {
        props : ['htmlunreadcount'],
        methods: {
            call: function (event) {
                $(".dropdown-menu.list-unstyled.msg_list").html('');
                $.post( "/edigindo/notification/ajaxNavbar", function( data ) {
                    $(".dropdown-menu.list-unstyled.msg_list").html(data);
                });
            }
        }
    }
</script>