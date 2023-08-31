(function ($) {   
  $(document).ready(function () { 
    //Pusher.logToConsole = true;
    pusher=  new Pusher("648b4b78f093044403e3", { cluster: 'eu', channelAuthorization: { endpoint: "/realtime-page/", transport: "ajax"} });
    presencechannel = pusher.subscribe('presence-my-channel');	
  });   
})(jQuery);

