
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('notification', require('./components/Notification.vue'));
Vue.component('info', require('./components/Info.vue'));

const app = new Vue({
    el: '#app',
    data: {
        htmlunreadcount: '',
        unreadCount: 0,
        notifcontent: [],

    },
    created() {
    	axios.post('/edigindo/notification/get').then(response => {
            if(response.data.unreadCount > 0)
            {
                this.htmlunreadcount = response.data.unreadCount;
            }
            this.unreadCount = response.data.unreadCount;
    	});

        var userId = $('meta[name="userId"]').attr('content');

        Echo.private('App.User.' + userId).notification((notification) => {
            // console.log(notification);
            if(this.unreadCount > 0)
            {
                this.unreadCount++;
                this.htmlunreadcount = this.unreadCount;
            }
            else
            {
                this.htmlunreadcount = 1;
                this.unreadCount = 1;
            }

            this.notifcontent.push(notification);

            var from = notification.from;
            var title = notification.title;
            var messages = notification.messages;
            var id = notification.id;

            Notification.requestPermission( permission => {
              let notification = new Notification('Notification from ' + from + '!', {
                body: messages, // content for the alert
                // icon: "https://pusher.com/static_logos/320x320.png" // optional image url
              });

              // link to page on clicking the notification
              notification.onclick = () => {
                window.open('/edigindo/notification/'+id+'/view');
              };
            });
        });

    }
});
