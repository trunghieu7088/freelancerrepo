// $("a.select-allpay-payment").click(function(e){
			// 	 e.preventDefault();
			// 	var p_data = JSON.parse($('#mje-checkout-info').html());
			// 		p_data['payment_type'] = 'allpay';

   //              var $target = $(e.currentTarget),
   //                  paymentType = $target.attr('data-type'),
   //                  view = this;

			// 	$.ajax({
   //                  type: 'POST',
   //                  url: ae_globals.ajaxURL,
   //                  data: {
   //                      action: 'mje_checkout_product',
   //                      method: 'create',
   //                      p_data: p_data,
   //                      p_type : 'mjob_order',
   //                      p_total : p_data.total,
   //                      p_payment:'allpay',
   //                      p_nonce: $('#_wpnonce').val(),
   //                  },
   //                  beforeSend: function() {
   //                     // view.blockUi.block(target);
   //                  },
   //                  success: function(resp, status, jqXHR) {
   //                  	var api = resp.data.api_ap;
   //                  	var form_submit = wp.template("form_submit");
			// 			$("#checkout-step2").html(form_submit(api));
			// 			var t = form_submit(api);

			// 			$("#allpay_payment_form").submit();
   //                  }
   //              });
			// });
		//})(jQuery);