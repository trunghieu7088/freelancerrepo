(function($, Models, Collections, Views) {
	$(function() {
		Models.Message = Backbone.Model.extend({
			action: 'ae-ae_message-sync',
			defaults:{
				post_type:'ae_message'
			},
			initialize: function() {}
		});
		/**
		 * mjob collections
		 */
		Collections.Message = Backbone.Collection.extend({
			model: Models.Message,
			action: 'ae-fetch-ae_message',
			initialize: function() {
				this.paged = 1;
			},
			// message collection should always be sorted by posted time
			// using `id` since WP auto increase it for later posts 
			comparator: 'id' 
		});
	});
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);
