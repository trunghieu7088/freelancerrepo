(function($, Models, Views) {
	jQuery.validator.addMethod("notEqualEmail", function(value, element, param) {
	 return this.optional(element) || value != $(param).val();
	}, "The primary and alternative email must be different.");

	jQuery('.input_claim').focus(
		function(){
			jQuery(this).parent('.item-claim').addClass('focus');
		}).blur(
		function(){
			jQuery(this).parent('.item-claim').removeClass('focus');
    });

	jQuery(".click_checkbox_claim").click(function(){
		if(jQuery('.check_box_claim').is(':checked')){
			jQuery(this).find('.span_check').addClass('checked').removeClass('uncheck');
		}
		else{
			jQuery(this).find('.span_check').addClass('uncheck').removeClass('checked');
		}
	});

	jQuery(".click_claim_btn").click(function(){
		var id=jQuery(this).attr('data-id');
		postbyurl('hide_me',wnm_th.ajax + '?action=update_price_claim','id='+id);
	});



	jQuery(".popop_reason_textarea").keydown(function(event){
		var countchr=jQuery(this).val().length;
		jQuery(".left_char").html(500-countchr);
	});

	jQuery(".popop_reason_textarea").on('paste', function () {
	  var element = this;
	  setTimeout(function () {
		var countchr= jQuery(element).val().length;
			jQuery(".left_char").html(500-countchr);
	  }, 100);
	});


	Claim_decision = Backbone.View.extend({
            el: 'body',
            events: {
                'click .claim_decision' : 'claim_decision_submit',
            },
			initialize: function() {
				 this.initValidator();
			},
			initValidator: function() {
                var view = this;
                view.form_validator = view.$el.find('form.form_claim_descision').validate({
					ignore: "",
                    rules: {
                        decline_reason_meta: 'required',
                    },
                    errorElement: "p",
                    highlight:function(element, errorClass, validClass){
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.addClass('has-error');
                        $target.addClass('has-visited');
                    },
                    unhighlight:function(element, errorClass, validClass){
                        // position error label after generated textarea
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.removeClass('has-error');
                        $target.removeClass('has-visited');
                    }
                })
            },
			claim_decision_submit:function(e){

				var view = this;
				$this=e.currentTarget;
				var id=jQuery($this).attr('data-id');
				var status=jQuery($this).attr('data-status');
				var reason =jQuery("#decline_reason_meta").val();
				if(jQuery($this).attr('data-status')=="mje_declined"){
					if(view.form_validator.form()){
						blockUi = new AE.Views.BlockUi();
						blockUi.block(e.currentTarget);
						postbyurl('hide_me',wnm_th.ajax + '?action=decision_claim','id='+id+'&status='+status+'&reason='+reason);
					}
				}
				else{
					blockUi = new AE.Views.BlockUi();
					blockUi.block(e.currentTarget);
					postbyurl('hide_me',wnm_th.ajax + '?action=decision_claim','id='+id+'&status='+status+'&reason='+reason);
				}

			}
	})

	new Claim_decision();

	Models.Claim = Backbone.Model.extend({
		action: 'mje_claim_sync',
		defaults: {
			mjob_id:'',
			new_name_meta: '',
			skype_meta: '',
			pri_email_meta: '',
			alt_email_meta: '',
			photo_meta:'',
		},
	});

	Claim_step = Backbone.View.extend({
            el: 'body',
            events: {
                'click .claim_step .next-step' : 'open_last_step',
				'click .claim_step .back-step-claim' : 'open_first_step',
				'click .del_photo_claim' : 'del_photo_claim',
				'click .submit-step-claim': 'submit_step_claim',
            },
			initialize: function() {
				 this.initValidator();

				 this.claimModel = new Models.Claim();

				 this.carousels = new Views.Carousel({
						el: $('.claim_upload_button'),
                        uploaderID:'claim_photo',
						model: this.claimModel,
						carouselTemplate: '#ae_carousel_claim_template',
						multiple: false,
						filters: {
							resolution_limit: {
								min: { width: 600, height: 412 }
							},
						}

                    });

				 AE.pubsub.on('Upload:Success', this.uploadSuccess, this);
			},
			open_last_step: function(event) {
				var view = this;
				if(view.form_validator.form()) {

					$arrs=['new_name_meta','skype_meta','pri_email_meta','alt_email_meta'];
					jQuery.each($arrs,function(i,sel){
						jQuery('.'+sel).html(jQuery('#'+sel).val());
					})

					jQuery("#claim_first_step").modal('hide');
					jQuery("#claim_last_step").modal('show');

				}
				else{
					//AE.pubsub.trigger('ae:notification', { msg: 'Some fields are not valid!', notice_type: 'error'	});
					jQuery('#claim_first_step').animate({ scrollTop: 0 }, 'slow');
				}

            },
			open_first_step: function(event) {

				jQuery("#claim_last_step").modal('hide');
				jQuery("#claim_first_step").modal('show');

            },
			submit_step_claim: function(){
				var blockUi = new AE.Views.BlockUi();
				blockUi.block(".submit-step-claim");

				$default={};
				$arrs=['mjob_id','new_name_meta','skype_meta','pri_email_meta','alt_email_meta','photo_meta'];
				jQuery.each($arrs, function(i,arr){
					$default[arr]=jQuery("#"+arr).val();
				}),
				this.claimModel.save($default, {
					success: function (res,data) {
						if(data.success){
							jQuery("#new_name_meta,#skype_meta,#pri_email_meta,#alt_email_meta,#photo_meta").each(function(i,$this){
								jQuery($this).val('');
							})
							jQuery(".check_box_claim").prop("checked", false);
							jQuery(".span_check").removeClass('checked').addClass('uncheck');
							if(data.fee==0){
								AE.pubsub.trigger('ae:notification', { msg: data.msg, notice_type: 'success'	});
							}
							window.location=data.link_redirect;
						}
						else{
							AE.pubsub.trigger('ae:notification', { msg: data.msg, notice_type: 'error'	});
						}
						blockUi.unblock();
					},
					error: function () {
						AE.pubsub.trigger('ae:notification', { msg: 'System error. Please try again later!', notice_type: 'error'	});
						blockUi.unblock();
					}
				});
			},
			del_photo_claim: function(e){
				$this = (e.currentTarget);
				var blockUi = new AE.Views.BlockUi();
				blockUi.block($this);
				var id=jQuery($this).parents(".image-item").attr('id');
				jQuery.ajax({
                    type: 'post',
                    url: wnm_th.ajax,
                    data: {
                        action: 'ae_remove_carousel',
                        id: id
                    },
                    success: function () {
                        jQuery("#photo_meta").val('');
						jQuery(".show_photo_claim").attr('src',wnm_th.url+'assets/images/scan_id.png');
						jQuery('.del_photo_claim').removeClass('show');
						jQuery($this).parents(".image-item").remove();
						blockUi.unblock()
                    }
                });
			},
			initValidator: function() {
                var view = this;
                view.form_validator = view.$el.find('form.form_claim').validate({
					ignore: "",
                    rules: {
                        new_name_meta: 'required',

						skype_meta: 'required',
						pri_email_meta:  {
                            required: true,
                            email: true,
							notEqualEmail: "#alt_email_meta",
                        },
						alt_email_meta:  {
                            required: true,
                            email: true,
							notEqualEmail: "#pri_email_meta",
                        },
						photo_meta: 'required',
						mjob_extra: 'required',
                    },
                    errorElement: "p",
                    highlight:function(element, errorClass, validClass){
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.addClass('has-error');
                        $target.addClass('has-visited');
                    },
                    unhighlight:function(element, errorClass, validClass){
                        // position error label after generated textarea
                        var $target = $(element );
                        var $parent = $(element ).parent();
                        $parent.removeClass('has-error');
                        $target.removeClass('has-visited');
                    }
                })
            },
			uploadSuccess: function(res) {
				if(res.success){
						if(res.data.file_id=="claim_photo"){
							if(jQuery(".item-claim .image-item").length){
									jQuery(".item-claim .image-item").each(function(){
										var id=jQuery(this).attr('id');
										$this=this;
										jQuery.ajax({
											type: 'post',
											url: wnm_th.ajax,
											data: {	action: 'ae_remove_carousel',id: id},
											success: function () {
												jQuery($this).remove();
											}
										});
									})
							}
							jQuery("#photo_meta").val(res.data.attach_id);
							jQuery(".show_photo_claim").attr('src',res.data.full[0]);
							jQuery('p[for="photo_meta"]').remove();
							jQuery('.append-claim-del').append('<span class="image-item" id="'+res.data.attach_id+'"><span class="del_photo_claim delete-img delete"><i class="fa fa-times" aria-hidden="true"></i></span></span>');
							jQuery('.del_photo_claim').addClass('show');
							jQuery("#claim_photo_container").find('input[type="file"]').removeAttr("disabled");
							jQuery("#claim_photo_browse_button").removeClass("disable-browse");
						}
				}
				else{
						AE.pubsub.trigger('ae:notification', { msg: res.msg, notice_type: 'error'	});
				}
			}
	})

	 new Claim_step();

})(jQuery, AE.Models, AE.Views);
