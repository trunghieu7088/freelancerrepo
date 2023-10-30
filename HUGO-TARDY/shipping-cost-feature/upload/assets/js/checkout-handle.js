(function($, Models, Collections, Views) {
    Views.HandleCheckout = Backbone.View.extend({
        el: '.mjob-order-page',
        events: {
                'click .extra-item input[type="checkbox"]': 'selectExtra',
                'click .shipping-cost-add input[type="checkbox"]': 'addShippingCost',
                'keypress #shipping_address': 'updateShippingAddress',
                'keyup #shipping_address': 'updateShippingAddress',
        },
        initialize: function () {
            // init checkout info
            if($('#mje-checkout-info').length > 0) {
                this.checkoutData = JSON.parse($('#mje-checkout-info').html());

                switch (this.checkoutData.post_type) {
                    case 'mjob_order':
                        this.handleExtra();
                        break;
                    default:
                    // Do nothing
                }
            }
        },
        handleExtra: function() {
            var view = this;
            if($('#mje-extra-ids').length > 0) {
                this.extraID = JSON.parse($('#mje-extra-ids').html());
            }
            this.$el.find('.extra-item').each(function () {
                var id = $(this).attr('data-id');
                var index = _.indexOf(view.extraID, id);
                if(index !== -1) {
                    $(this).find('input').prop('checked', true);
                    $(this).addClass('active');
                }
            });
        },
        //custom code here
        addShippingCost: function(event)
        {
            var view = this;
            var temp = window.AE.order;
            view.checkout = temp.checkoutModel;
            view.productData = temp.productData;
            $target = $(event.currentTarget);
            var customData = JSON.parse($('#mje-checkout-info').html());  
            
          

            
            if($target.is(':checked')) 
            {               
                
                AE.pubsub.trigger('mje:update:checkout:subtotal', Number($target.val()));                
                AE.pubsub.trigger('mje:update:checkout:total');
                //remove extra fee for shipping cost
                if(view.productData.ef_percent > 0)
                {
                    var shipping_cost_commission=(view.productData.ef_percent * view.productData.shipping_cost ) / 100;
                    view.productData.total-=shipping_cost_commission;
                    view.$el.find('.total-price').html(AE.App.mJobPriceFormat(view.productData.total));
                }
                console.log('shipping cost com :'+ shipping_cost_commission);
                console.log(  view.productData.total);

                //end

                $target.parents('.shipping-cost-add').addClass('active-shipping');
                $("#shipping_address").removeClass('disabled-shipping-address');
                $("#shipping_address").removeAttr('disabled');
                view.checkout.set('is_ship','true');
                view.productData.is_ship='true';

                
                var shipping_address=$("#shipping_address").val();              
                $(".mje-btn-checkout").attr('disabled','disabled');
                if(shipping_address==='' || shipping_address==='undefined' || shipping_address===null)
                {
                    $(".mjob-btn-checkout").attr('disabled','disabled');
                    $(".mjob-btn-checkout").addClass('disabled-btn-custom');
                    $(".text-shipping-alert").css('display','block');

                }
            }        
            else
            {
                AE.pubsub.trigger('mje:update:checkout:subtotal', - Number($target.val()));                
                AE.pubsub.trigger('mje:update:checkout:total');               
                $target.parents('.shipping-cost-add').removeClass('active-shipping');
                $("#shipping_address").addClass('disabled-shipping-address');
                $("#shipping_address").attr('disabled','disabled');
                view.checkout.set('is_ship','false');
                view.productData.is_ship='false';

                //hide shipping text alert and enable checkout button
                $(".text-shipping-alert").css('display','none');
                $(".mjob-btn-checkout").removeAttr('disabled','disabled');
                $(".mjob-btn-checkout").removeClass('disabled-btn-custom');
                
            }       
            //console.log( view.productData);                           
            //console.log( view.productData.is_ship);
        },
        updateShippingAddress: function(event)
        {   
            var view = this;    
            $target = $(event.currentTarget);
            view.checkout.set('shipping_address', $target.val());
            view.productData.shipping_address=$target.val();
            if($target.val())
            {
                $(".mjob-btn-checkout").removeAttr('disabled','disabled');
                $(".mjob-btn-checkout").removeClass('disabled-btn-custom');
                $(".text-shipping-alert").css('display','none');
            }
            if($target.val()==='' || $target.val()==='undefined' || $target.val()===null)
            {
                $(".mjob-btn-checkout").attr('disabled','disabled');
                $(".mjob-btn-checkout").addClass('disabled-btn-custom');
                $(".text-shipping-alert").css('display','block');
            }
        },
        //end 
        selectExtra: function(event) {
            console.log('Update Extra Item.');
            var view = this;
            var commission = ae_globals.fee_order_buyer;
            var fee_extra  = 0;
            $target = $(event.currentTarget);
            var extraBudget = parseFloat($target.val());
            var id = $target.attr('data-id');
            extraBudget = extraBudget;
            if($target.prop('checked')) {
                $target.parents('.extra-item').addClass('active');
                view.extraID.push(id);
                //fee_extra=commission*extraBudget/100;


                AE.pubsub.trigger('mje:update:checkout:product:data', view.extraID, 'extra_ids');
                AE.pubsub.trigger('mje:update:checkout:subtotal', extraBudget);
                AE.pubsub.trigger('mje:update:checkout:fee');
                AE.pubsub.trigger('mje:update:checkout:total');
            } else {
                $target.parents('.extra-item').removeClass('active');
                view.extraID = _.reject(view.extraID, function(num) { return num == id});

                AE.pubsub.trigger('mje:update:checkout:product:data', view.extraID, 'extra_ids');
               // fee_extra=commission*extraBudget/100;
                AE.pubsub.trigger('mje:update:checkout:subtotal', -extraBudget);
                AE.pubsub.trigger('mje:update:checkout:fee');
                AE.pubsub.trigger('mje:update:checkout:total');

            }
        }
    });

    $(document).ready(function () {
       new Views.HandleCheckout();
    });
})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);