(function($){
AE.Views.SocialAuth = Backbone.View.extend({
	el: 'body',
	events: {
		'submit #form_auth' 	: 'authenticate',
		'submit #form_username' : 'confirm_username',
	},
	initialize: function(){
		var view = this;
		view.user_pass = ''; // some time cookie can not save user_pass so has to re-call it again by this variable.
		this.blockUi = new AE.Views.BlockUi();
	},
	lkinDoLogin: function(e){
		var view = this;
		$.ajax({
			url : ae_globals.ajaxURL,
			type : "get",
			data :{
				action: "ae_linked_auth",
				state : "click"
			},
			beforeSend: function() {
                view.blockUi.block('.lkin');
            },
			success:function(resp){
				if( resp.success ){
					window.location.href = resp.redirect;
				}
				else{
					AE.pubsub.trigger('ae:notification', {
						msg: resp.msg,
						notice_type: 'error',
					});
				}
			}
		});
	},
	authenticate: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;
		view.user_pass = form.find('input[name="user_pass"]').val();
		var params = {
			url: 	ae_globals.ajaxURL,
			type: 	'post',
			xhrFields: {
			  withCredentials: true // Send session cookies
			},
			data: {
				action: ae_auth.action_auth,
				content: form.serializeObject(),
			},
			beforeSend: function(){
				//submit
				  var button = form.find('input[type=submit]')
				  view.blockUi.block(button);
			},
			success: function(resp){
				if ( resp.success ){
					if ( resp.data.status == 'wait' ){
						view.$('.social-auth-step1').fadeOut('fast', function(){
							view.$('.social-auth-step2').fadeIn();
						});
					} else if ( resp.data.status == 'linked' ){
						AE.pubsub.trigger('ae:notification', {
							msg: resp.msg,
							notice_type: 'success',
						});
						setTimeout(function() {
							window.location = resp.data.redirect_url;
						}, 3000);
						//window.location.reload();
					}
				}
				else{
					msg = 'ERROR!';
					if(resp != 0){
						msg = resp.msg;
					}
					AE.pubsub.trigger('ae:notification', {
						msg: msg,
						notice_type: 'error',
					});
				}
			},
			complete: function(){
				view.blockUi.unblock();
			}
		}
		$.ajax(params);
	},

	confirm_username: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;

		var params = {
			url: 	ae_globals.ajaxURL,
			type: 	'post',
			xhrFields: {
			  withCredentials: true // Send session cookies
			},
			data: {
				action: ae_auth.action_confirm,
				content: form.serializeObject(),
				user_pass: view.user_pass,
			},
			beforeSend: function(){
				//form.find('input[type=submit]').loader('load');
				var button = form.find('input[type=submit]');
				view.blockUi.block(button);
			},
			success: function(resp){
				if ( resp.success == true ){
					AE.pubsub.trigger('ae:notification', {
						msg: resp.msg,
						notice_type: 'success'
					});
					setTimeout(function() {
						window.location = resp.data.redirect_url;
					}, 3000);
				} else {
					AE.pubsub.trigger('ae:notification', {
						msg: resp.msg,
						notice_type: 'error'
					});
				}
			},
			complete: function(){
				//form.find('input[type=submit]').loader('unload');
				view.blockUi.unblock();
			}
		}
		$.ajax(params);
	}
});

$(function(){
	new AE.Views.SocialAuth();
});
})(jQuery);