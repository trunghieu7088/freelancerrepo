(function ($) {
    $(document).ready(function () {
        $(".no-shipping-checkbox").click(function(){
            if($(this).is(':checked'))
            {
                $("#shipping-cost-area").css('display','none');
                $("#shipping_cost").removeAttr('required');
            }
            else
            {
                $("#shipping-cost-area").css('display','block');
                $("#shipping_cost").attr('required','required');
            }
        });
    });

})(jQuery);