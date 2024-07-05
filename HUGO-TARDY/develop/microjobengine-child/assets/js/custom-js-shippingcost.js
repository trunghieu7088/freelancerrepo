(function ($) {
    $(document).ready(function () {
        $(".no-shipping-checkbox").click(function(){
            if($(this).is(':checked'))
            {
                $("#shipping-cost-area").css('display','none');
                $("#shipping_cost").val('');
                $("#shipping_cost").removeAttr('required');

            }
            else
            {
                $("#shipping-cost-area").css('display','block');
                $("#shipping_cost").attr('required','required');
            }
        });

        function hideShippingCost()
        {
            if($("#shipping_cost").val() <= 0 || $("#shipping_cost").val()=='' || $("#shipping_cost").val()==='undefined')
            {
                $("#no-shipping-option").trigger('click');
            }
        }
        $(".edit-mjob-action").click(function(){

            setTimeout(function(){ hideShippingCost() }, 2000); 
        });
    });

})(jQuery);