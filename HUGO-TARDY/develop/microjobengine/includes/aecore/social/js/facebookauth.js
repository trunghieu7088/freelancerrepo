window.fbAsyncInit = function() {
	// init the FB JS SDK
	FB.init({
		appId            : facebook_auth.appID,
		status			 : true,
		xfbml            : true,
		version          : 'v19.0'
	  });
};

(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "https://connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

(function($){
	$('.facebook_auth_btn').on("click", function(event){
		event.preventDefault();
		if ( FB ){
			FB.login(function(response) {
				if (response.authResponse) {
					access_token = response.authResponse.accessToken; //get access token
					user_id = response.authResponse.userID; //get FB UID

					FB.api('/me', function(response) {
						user_email = response.email; //get user email
						// you can store this data into your database
						var params = {
							url 	: ae_globals.ajaxURL,
							type 	: 'post',
							data 	: {
								action: 'et_facebook_auth',
								content: response,
								fb_token: access_token
							},
							beforeSend: function(){
							},
							success: function(resp){
								if ( resp.success && typeof resp.data.redirect_url != 'undefined' ){
									window.location = resp.data.redirect_url;
								}
								else if ( resp.success && typeof resp.data.user != 'undefined' ){
									if(!is_mobile){
										// assign current user
										var model = new AE.Models.User(resp.data.user);
										AE.App.currentUser = model;
										// trigger events
										var view 	= AE.App.authModal;
										if(typeof view != 'undefined'){
											view.trigger('response:login', resp);
											AE.pubsub.trigger('ae:response:login', model);
											AE.pubsub.trigger('ae:notification', {
												msg: resp.msg,
												notice_type: 'success',
											});

											view.$el.on('hidden.bs.modal', function(){
												AE.pubsub.trigger('ae:auth:afterLogin', model);
												view.trigger('afterLogin', model);
												// if ( view.options.enableRefresh == true){
													window.location.href = resp.redirect_url;
												// } else {
												// }
											});	

											view.closeModal();
										}
										else{
											AE.pubsub.trigger('ae:notification', {
												msg: resp.msg,
												notice_type: 'success',
											});
											window.location.href = resp.redirect_url;
										}
									}
									else{
										window.location.reload(true);
									}
								} else if ( resp.msg ) {
                                    AE.pubsub.trigger('ae:notification', {
                                        msg: resp.msg,
                                        notice_type: 'error',
                                    });
                                    alert(resp.msg);
								}
							},
							complete: function(){
								//$('#facebook_auth_btn').loader('unload');
								//this.blockUi.unblock();
							}
						}
						jQuery.ajax(params);

					});

				} else {
					//user hit cancel button
					alert('User cancelled login or did not fully authorize.');
				}
			}, {
				//scope: 'email,user_about_me' remove in 1.3.5.2
				scope: 'email'
			});
		}
	});
})(jQuery);