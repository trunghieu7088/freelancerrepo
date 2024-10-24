(function($, Models, Collections, Views) {
	$(document).ready(function() {
		/**
		 * model withdraw
		 */
		Models.withdraw = Backbone.Model.extend({
			action: 'fre-admin-withdraw-sync',
			initialize: function() {}
		});
		Collections.withdraw = Backbone.Collection.extend({
			model: Models.withdraw,
			action: 'fre-admin-fetch-withdraw',
			initialize: function() {
				this.paged = 1;
			}
		});
		var withdrawItem = Views.PostItem.extend({
			tagName: 'li',
			className: 'withdraw-item',
			template: _.template($('#fre-credit-withdraw-loop').html()),
			onItemBeforeRender: function() {
				// before render view
			},
			onItemRendered: function() {
				// after render view
			}
		});
		ListWithdraw = Views.ListPost.extend({
			tagName: 'li',
			itemView: withdrawItem,
			itemClass: 'withdraw-item'
		});
		// notification list control
		if( $('.fre-credit-withdraw-container').length > 0 ){

			if( $('.fre-credit-withdraw-container').find('.fre_credit_withdraw_dta').length > 0 ){
				var postsdata = JSON.parse($('.fre-credit-withdraw-container').find('.fre_credit_withdraw_dta').html()),
					posts = new Collections.withdraw(postsdata);
			} else {
				var posts = new Collections.withdraw();
			}
			/**
			 * init list blog view
			 */
			new ListWithdraw({
				itemView: withdrawItem,
				collection: posts,
				el: $('.fre-credit-withdraw-container').find('.list-withdraws')
			});
			/**
			 * init block control list blog
			 */
			new Views.BlockControl({
				collection: posts,
				el: $('.fre-credit-withdraw-container'),
				onAfterFetch: function(result, res, target) {
					$('.fre-credit-withdraw-container').find('.list-withdraws .no-items').remove();
					if(res.success == false) {
						$('.fre-credit-withdraw-container').find('.list-withdraws').html('<p class="no-items">'+ ae_globals.withdraw_not_found_text +'</p>');
					}
				}
			});



			// Withdraw container view
			Views.WithdrawContainer = Backbone.View.extend({
				el: '.fre-credit-withdraw-container',
				initialize: function() {
					AE.pubsub.on('ae:model:ondecline-withdraw', this.declineWithdraw, this);
				},

				declineWithdraw: function(model) {
					this.model = model;
					var message = prompt("Please give a decline reason.", "");
                    this.model.set('reject_message', message);
                    this.model.save('post_status', 'draft', {
                        beforeSend: function() {

                        },
                        success: function(status, resp, jqXHR) {
                            if(resp.success == true) {

                            } else {

                            }
                        }
                    })
				}
			});

			new Views.WithdrawContainer();
		}
	});
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);
