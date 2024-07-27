(function($, Models, Views) {
	
	var claim_item = Backbone.Model.extend({
		defaults: {
			mjob_link:'',
			mjob_src:'',
			mjob_title:'',
			claim_link:'',
			claim_stt:'',
			claim_name:'',
			claim_icon:'',
			claim_tooltip:'',
			is_admin:false,
			author:'',
		}
	});
	
	var ClaimsCollection = Backbone.Collection.extend({
		model: claim_item
	});
	
	var ClaimView = Backbone.View.extend({
		template: _.template( jQuery('#ae_list_claim_template').html()),
	
		initialize: function(){
			this.render();
			//this.model.bind("change", this.render);
       		//this.model.fetch();
		},
	
		render: function(){
			this.$el.html( this.template(this.model.toJSON()));
		}
	});
	
	var ClaimsView = Backbone.Marionette.View.extend({
		el: 'body',
		events: {
			'click .click_filter_claim' : 'click_filter_claim',
		},
		initialize: function(){
			this.render();
			this.collection.bind('sync', this.render, this);
		},
	
		render: function(){
			this.$el.find(".list-item").html('');
			this.collection.each(function(claim_item){
				var claimView = new ClaimView({ model: claim_item });
				this.$el.find(".list-item").append(claimView.el);
			}, this);
		},
		
		click_filter_claim: function(e){
				$this = (e.currentTarget);
				$page=jQuery($this).attr('data-page');
				$status=jQuery($this).attr('data-status');
				var blockUi = new AE.Views.BlockUi();
				blockUi.block(".list-content-claim");
				this.collection.fetch({
					data: {
						action: 'mje_listclaim_fetch',
						page: $page,
						status: $status,
					},
					remove:true,
					success: function(res,obj_claim) {
						if(jQuery("#wpadminbar").length){
							jQuery('html,body').animate({scrollTop: (jQuery(".left-title").offset().top-60)},'slow');
						}
						else{
								jQuery('html,body').animate({scrollTop: (jQuery(".left-title").offset().top-20)},'slow');
						}
						
						str_pav="";
						pav=obj_claim.pav;
						pav_num=parseInt(pav.num);
						pav_current=parseInt(pav.current);
						if(pav_num>1){
							str_pav+=(pav_current>1)?'<li><a class="click_filter_claim page-numbers" data-page="'+(pav_current-1)+'" data-status="'+pav.stt+'"><i class="fa fa-angle-double-left"></i></a></li>':'';
							for(i=1;i<=pav_num;i++){
								act=(pav_current==i)?'active':'click_filter_claim';
								str_pav+='<li><a class="'+act+'" data-page="'+i+'" data-status="'+pav.stt+'">'+i+'</a></li>';		
							}
							str_pav+=(pav_current<pav_num)?'<li><a class="click_filter_claim " data-page="'+(pav_current+1)+'" data-status="'+pav.stt+'"><i class="fa fa-angle-double-right"></i></a></li>':'';
						}
						jQuery(".claim_pav").html(str_pav);
						arrs=obj_claim.data;
						if(arrs.length == 0){
							jQuery(".nothing-found-claim").show();	
						}
						else{
							jQuery(".nothing-found-claim").hide();	
						}
						blockUi.unblock();
					}
				},this)
		}
		
	});
	
	if (jQuery('#claim_item_template_data').length > 0) {
		var data_json=JSON.parse(jQuery("#claim_item_template_data").html());
 		var ClaimsCollection = new ClaimsCollection(data_json);
	}
	else{
		var ClaimsCollection = new ClaimsCollection(data_json);
	}
	new ClaimsView({collection: ClaimsCollection});
	
})(jQuery, AE.Models, AE.Views);
