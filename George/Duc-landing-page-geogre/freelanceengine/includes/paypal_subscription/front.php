<?php


function add_paypal_sdk_cript(){

	$allow_credit_btn = ae_get_option('allow_credit_btn', false);

	$disabled = '&disable-funding=credit,card';

	if($allow_credit_btn){
		$disabled = '';
	}
	if(is_page_template('page-submit-project.php')){ ?>
		<script src="https://www.paypal.com/sdk/js?client-id=<?php echo SB_CLIENT_ID.$disabled;?>&vault=true" data-sdk-integration-source="button-factory"></script>
		<!-- true == subscription !--><?php
	}

}
add_action('wp_head','add_paypal_sdk_cript');

function add_recruit_pack(){
	global $user_ID, $ae_post_factory;

	$args = array(
		'post_type' =>'recruit_pack',
		'post_status' => 'publish',
	);
    $packs =new WP_Query($args);

    if( $packs->have_posts() ){
	    while($packs->have_posts()){

	   		$packs->the_post();
	    	global $post;
	    	$package = $post;
	    	$disabled = '';
	    	$checked = '';
	    	$pack_description = $package->post_cotent;
	    	//var_dump($package);
	    	?>
	    	<li class="recruit-plan" data-sku="<?php echo trim($package->sku);?>" data-id="<?php echo $package->ID ?>" data-price="<?php echo $package->et_price; ?>" data-package-type="<?php echo $package->post_type; ?>" data-title="<?php echo $package->post_title ;?>">
                    <label class="fre-radio" for="package-<?php echo $package->ID?>">
                        <input id="package-<?php echo $package->ID?>" name="post-package" type="radio" <?php echo $disabled; echo ($checked) ? "checked='checked'" : '' ;?>>
                        <span><?php echo $package->post_title ; ?></span>
                    </label>
                    <span class="disc"><?php echo $pack_description;?> </span>
                </li>
	    	<?php
	    }
	}
}
add_action('after_list_pack','add_recruit_pack');
function add_html_div_only(){
	global $user_ID, $ae_post_factory;
    $ae_pack = $ae_post_factory->get('recruit_pack');
    $packs = $ae_pack->fetch('recruit_pack');
    $package_data = AE_Package::get_package_data( $user_ID );
    $cur_plan = array();


    if($packs){
    	echo '<li class="recruit-payment hide">';
        foreach ($packs as $key => $package) {
            $number_of_post =   $package->et_number_posts;
            $static_number = $number_of_post;
            $post_left = 0;
            $sku = $package->sku;

            $id_wrap_btn = "paypal-button-".$sku;
            ?>
            <div class="paypal-plan hide plan-<?php echo $sku;?>">

            	<span class="title-plan " >PayPal Auto Recruit Payment</span>

           		<span class="plan-description">Send your payment to our PayPal account</span>


        		<div class="wrap-pp-section" >
        			<a class="btn collapsed btn-submit-price-plan hide">SELECT</a>
	        		<div id="<?php echo $id_wrap_btn;?>"></div>

	        	</div>
	        	<script>
					paypal.Buttons({
					  	style: {
					      	shape: 'rect',
					      	color: 'gold',
					      	layout: 'horizontal',
					      	label: 'paypal',
					      	tagline: false,// hide the  text - the safer, easier to pay.
					      	fundingicons: 'true',
					      	size: 'responsive',

					  	},
					  	funding:{
							// allowed: [ paypal.FUNDING.CARD ],
							disallowed: [ paypal.FUNDING.CREDIT ]
						},
					  	createSubscription: function(data, actions) {
					  		console.log('createSubscription');
					  		console.log(data);
					  		console.log(actions);

					    	return actions.subscription.create({
					       		'plan_id': '<?php echo $sku;?>',
					       		'name': 'Plan name xxx - <?php echo $sku;?>',
					       		'metadata' : {
					                    'user_id' : 'myuserid'
					                },
					    	});
					  	},

					  	onApprove: function(data, actions) {

					    	jQuery.ajax({
					    		type:'POST',
	        		     		url: '<?php echo admin_url( 'admin-ajax.php' );?>',

	        					data: {
				                    data: data,
				                    action: 'pps-onApprove',
				                    sku: '<?php echo $sku;?>',
				                    curID: window.curID,
				                },

	        		           	cache: false,
	        		           	dataType: 'json',
	        		           	success: function(data, textStatus){
	        		           		if (data.status == "OK"){
	        		           			//document.location.href = "/continue-checkout.php";
	        		           			window.location.href = data.project_url;
	        		           		}else{
	        		           			// alert("Error: "+data.msg);
	        		           		}
	        		           	},
	        		           	error: function( jqXHR, textStatus, errorThrown ){
	        		           		//alert('Connection-Error: '+textStatus+' '+errorThrown);
	                		    }
	        		        });
					    },
					    onCancel: function (data) {
						    // Show a cancel page, or return to cart
						},

					}).render('#<?php echo $id_wrap_btn;?>');
				</script>
			</div>

            <?php
        }
        echo '<li>';
    }
	?>

<?php }

add_action('after_payment_list', 'add_html_div_only');


function add_paypal_btn_script(){
	if(is_page_template('page-submit-project.php')){ ?>
		<style type="text/css">
			.recruit-payment{
				display: hidden;
			}
			.wrap-pp-section{

			    width: 200px;
			    float: right;
			    position: absolute;
			    top: 0;
			    right: 0;
			}
			.fre-payment-list .paypal-plan span.title-plan {
			    display: block;
			    font-size: 16px;
			    font-weight: 700;
			    text-transform: uppercase;
			    padding-right: 170px;
			}
			.fre-payment-list .paypal-plan .plan-description{
			    display: block;
			    margin-top: 4px;
			    font-weight: 400;
			    line-height: 1.6em;
			    text-transform: initial;
			    font-size: 16px;
			}

		</style>
		<script type="text/javascript">
			(function($, Models, Collections, Views) {
				var curID = 0;
				$(document).ready(function(){

					Views.paypalSubscription = Backbone.View.extend({
						el: 'body',
						events: {
							//'change #custom_deposit_amount': 'autoCheck',

						},
						initialize: function(){
							//_.bindAll(this, 'choosePlan');
							AE.pubsub.on('ae:submitPost:choosePlan', this.choosePlan, this);
							AE.pubsub.on('ae:postSuccess', this.setCurPost, this);
						},
						setCurPost: function(model, res){
							console.log('setCurPost');
							console.log(model.id);
							console.log(res);
							window.curID = model.id;
						},
						choosePlan: function($step, $li, view){

							var sku = $li.attr('data-sku');
							var is_recruit_plan = $li.hasClass('recruit-plan');

							var cur_plan = '.plan-'+sku;
							$(cur_plan).removeClass('hide');
							if( is_recruit_plan){
								console.log('show only pps button');
								$(".fre-payment-list li").addClass('hide');
								$(".fre-payment-list li.recruit-payment").removeClass('hide');
							} else {
								$(".fre-payment-list li").removeClass('hide');
								$(".fre-payment-list li.recruit-payment").addClass('hide');
							}

						}
					});
					new Views.paypalSubscription ();

				});

			})(jQuery, window.AE.Models, window.AE.Collections, window.AE.Views);

		</script> <?php
	}
}
	add_action('wp_footer','add_paypal_btn_script',999);