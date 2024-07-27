(function($, Models, Views) {
	 jQuery(window).load(function(){
		if(jQuery('.check_integer_validate').length){
			jQuery.validator.addMethod("isInteger", function(value, element, param) {
					 return this.optional(element) || (value >0 && value == parseInt(value, 10)) ;
					}, "This field must be integer and > 0");
			jQuery.validator.addClassRules({
					check_integer_validate: { 
						required: true,
						min: 1,
						digits: true,
						 
					},
			 });
		}
		
		if(jQuery('.check_integer_validate_positive').length){
			/*
			jQuery.validator.addMethod("isInteger2", function(value, element, param) {
					 return this.optional(element) || (value >=0 && value == parseInt(value, 10)) ;
					}, "This field must be a positive integer.");
			*/
			jQuery.validator.addClassRules({
					check_integer_validate_positive: { 
						required: true,
						min: 0,
						max:100,
						digits: true,
					},
			 });
		}
		
		
	})
	
    jQuery(".choosen_status").on('click', function () {
        old_stt = jQuery(this).val();
    }).change(function() {
        var id=jQuery(this).attr('data-id');
		var stt=jQuery(this).val();
		if(stt=="mje_declined"){
			rs=prompt("Please give a decline reason", ""); 
			if (rs != null && rs != "") {
				blockUi = new AE.Views.BlockUi();
				blockUi.block(this);
				postbyurl('hide_me',wnm_th.a_url + '?action=mje_change_status_claim_backend','id='+id+'&stt='+stt+'&rs='+rs);
			}else{
				jQuery(this).val(old_stt);
				if(rs!=null){
					alert('Decline reason required');
				}
			}
		}
		else{
			blockUi = new AE.Views.BlockUi();
			blockUi.block(this);
			postbyurl('hide_me',wnm_th.a_url + '?action=mje_change_status_claim_backend','id='+id+'&stt='+stt+'&rs=');
		}    
	});
	
	jQuery(document).delegate(".claim_notice_del","click",function(event){
		var id=jQuery(this).attr('data-id');
		blockUi = new AE.Views.BlockUi();
		blockUi.block(jQuery(this).parents(".contain_claim_notice"));
		
		postbyurl('hide_me',wnm_th.a_url + '?action=mje_remove_notice_claim_backend','id='+id);
	});
	
	jQuery(document).delegate(".undo_notice","click",function(event){
		var id=jQuery(this).attr('data-id');
		blockUi = new AE.Views.BlockUi();
		blockUi.block(jQuery(this).parents(".contain_claim_notice"));
		postbyurl('hide_me',wnm_th.a_url + '?action=mje_undo_notice_claim_backend','id='+id);
	});
	
	jQuery('.class_claim_status_meta').change(function(){
		if(jQuery(this).val()=="mje_declined"){
			jQuery('.class_decline_reason_meta').focus();	
		}														   
	})
	
	jQuery("#publishing-action #publish").click(function(e){
		if(jQuery('.class_decline_reason_meta').length){
			if(jQuery('.class_decline_reason_meta').val()=="" && jQuery(".class_claim_status_meta").val()=="mje_declined"){
				e.preventDefault();
				alert('Please give a decline reason');
				jQuery('.class_decline_reason_meta').focus();
			}
		}
	})
	
	
	jQuery(document).ready(function(){
		jQuery('[data-toggle="tooltip"]').tooltip();
	})
	
})(jQuery, AE.Models, AE.Views);