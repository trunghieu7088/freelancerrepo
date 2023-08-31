(function ($) {   
  $(document).ready(function () { 
   // Pusher.logToConsole = true;
    pusher=  new Pusher("79f2750396f1ce73fcd0", { cluster: 'eu', channelAuthorization: { endpoint: "/realtime-page/", transport: "ajax"} });
    presencechannel = pusher.subscribe('presence-my-channel');	
  });   
})(jQuery);

