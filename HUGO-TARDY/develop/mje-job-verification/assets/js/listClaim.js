(function($, Models, Views) {
	
	Models.listClaim = Backbone.Model.extend({
		action: 'mje_listclaim_sync',
	});
	
	Claim_list = Backbone.View.extend({
            el: 'body',
            events: {
                'click .click_filter_claim' : 'click_filter_claim',
            },
			initialize: function() {				 
				 this.claimModel = new Models.listClaim();
			},
			click_filter_claim: function(e){
				$this = (e.currentTarget);
				$page=jQuery($this).attr('data-page');
				$status=jQuery($this).attr('data-status');
				var blockUi = new AE.Views.BlockUi();
				blockUi.block(".list-content-claim");
				this.claimModel.fetch({
					data: {
						action: 'mje_listclaim_fetch',
						page: $page,
						status: $status,
					},
					success: function(res,obj_claim) {
						if(jQuery("#wpadminbar").length){
							jQuery('html,body').animate({scrollTop: (jQuery(".left-title").offset().top-60)},'slow');
						}
						else{
								jQuery('html,body').animate({scrollTop: (jQuery(".left-title").offset().top-20)},'slow');
						}
						
						arrs=obj_claim.datas;
						var str="";
						var template = _.template(jQuery("#ae_list_claim_template").html());
						jQuery.each(arrs,function(i,arr){
							str+=template({arr:arr});
						})
						jQuery(".list-item").html(str);
						// pav
						str_pav="";
						pav=obj_claim.pav;
						pav_num=parseInt(pav.num);
						pav_current=parseInt(pav.current);
						if(pav_num>1){
							str_pav+=(pav_current>1)?'<li><a class="click_filter_claim " data-page="'+(pav_current-1)+'" data-status="'+pav.stt+'"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></li>':'';
							for(i=1;i<=pav_num;i++){
								act=(pav_current==i)?'active':'';
								str_pav+='<li><a class="click_filter_claim '+act+'" data-page="'+i+'" data-status="'+pav.stt+'">'+i+'</a></li>';		
							}
							str_pav+=(pav_current<pav_num)?'<li><a class="click_filter_claim " data-page="'+(pav_current+1)+'" data-status="'+pav.stt+'"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></li>':'';
						}
						jQuery(".claim_pav").html(str_pav);
						if(arrs.length == 0){
							jQuery(".nothing-found-claim").show();	
						}
						else{
							jQuery(".nothing-found-claim").hide();	
						}
						blockUi.unblock();
						
					}
				});
			}
			
	})
	
	 new Claim_list();
	
})(jQuery, AE.Models, AE.Views);
