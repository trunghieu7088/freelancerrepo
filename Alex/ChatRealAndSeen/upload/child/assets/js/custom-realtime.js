(function ($) {   
  $(document).ready(function () { 
    //Pusher.logToConsole = true;
    pusher=  new Pusher("6d999a97908fc45a5632", { cluster: 'ap1', channelAuthorization: { endpoint: "/realtime-page/", transport: "ajax"} });
    presencechannel = pusher.subscribe('presence-my-channel');	
  });   
})(jQuery);

