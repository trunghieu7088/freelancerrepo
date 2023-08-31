<?php


  add_action( 'admin_enqueue_scripts', 'load_custom_extra_script' ); 
  function load_custom_extra_script() {
      wp_enqueue_script('custom_extra_js_script', get_stylesheet_directory_uri().'/assets/customadminjs/extra_option.js', array('jquery'));
  }



function custom_extra_settings_init(){
    add_menu_page( 'Extra Settings', 'Extra Settings', 'manage_options', 'custom-extra-settings', 'custom_extra_callback','dashicons-schedule',10 );


}

add_action('admin_menu', 'custom_extra_settings_init');

function custom_extra_callback()
{
		$urgentlabel=get_option('urgentlabel');
		$urgentdescription=get_option('urgentdescription');
		$urgentprice=get_option('urgentprice');

		$privatelabel=get_option('privatelabel');
		$privatedescription=get_option('privatedescription');
		$privateprice=get_option('privateprice');

		$avaiblecredit=get_option('avaiblecredit');
		$totalcost=get_option('totalcost');
		$addcredits=get_option('addcredits');
		$yourbalance=get_option('yourbalance');

			$emailtitle=get_option('emailtitle');
		$emailcontent=get_option('emailcontent');
		$credithistory=get_option('credithistory');

?>
<div class="wrap">
		   <h1>Profile Popup</h1>
		   <form style="margin-top:10px;" id="extraform" action="post" name="extraform">
		   
		   <h3>Urgent Label</h3>

			<div class="options" style="width:50%;">						
				<input  type="text" name="urgentlabel" placeholder="Urgent" id="urgentlabel" value="<?php echo $urgentlabel; ?>" style="width:100%;"/>			
			</div>

			 <h3>Urgent Description</h3>
			<div class="options" style="width:50%;">
					<input  type="text" name="urgentdescription" placeholder="Make your project stand out and let the users know that your job is time sensitive" id="urgentdescription" value="<?php echo $urgentdescription; ?>" style="width:100%;"/>							
			</div>

			 <h3>Urgent Price</h3>
			<div class="options" style="width:50%;">
					<input  type="number" name="urgentprice" placeholder="20" id="urgentprice" value="<?php echo $urgentprice; ?>" style="width:100%;"/>							
			</div>

				 <h3>Private Label</h3>
			<div class="options" style="width:50%;">
					<input  type="text" name="privatelabel" placeholder="Private" id="privatelabel" value="<?php echo $privatelabel; ?>" style="width:100%;"/>							
			</div>

			 <h3>Private Description</h3>
			<div class="options" style="width:50%;">
					<input  type="text" name="privatedescription" placeholder="Hide project details from search engines and users that are not logged in, for projects that you need to keep confidential." id="privatedescription" value="<?php echo $privatedescription; ?>" style="width:100%;"/>							
			</div>

			<h3>Private Price</h3>
			<div class="options" style="width:50%;">
					<input  type="number" name="privateprice" placeholder="10" id="privateprice" value="<?php echo $privateprice; ?>" style="width:100%;"/>							
			</div>

			<h3>Available Credit</h3>
			<div class="options" style="width:50%;">
					<input  type="text" name="avaiblecredit" placeholder="Available Credit" id="avaiblecredit" value="<?php echo $avaiblecredit; ?>" style="width:100%;"/>							
			</div>

			<h3>Total Cost</h3>
			<div class="options" style="width:50%;">
					<input type="text" name="totalcost" placeholder="Total Cost" id="totalcost" value="<?php echo $totalcost; ?>" style="width:100%;"/>							
			</div>

			<h3>Your balance has not enough credit</h3>
			<div class="options" style="width:50%;">
				<input  type="text" name="yourbalance" placeholder="Your balance has not enough credit" id="yourbalance" value="<?php echo $yourbalance; ?>" style="width:100%;"/>							
			</div>

			<h3>Add credits</h3>
			<div class="options" style="width:50%;">
				<input  type="text" name="addcredits" placeholder="Add credits" id="addcredits" value="<?php echo $addcredits; ?>" style="width:100%;"/>							
			</div>

			<h2>Email template</h2>

			<h3>Email Title</h3>
			<div class="options" style="width:50%;">
				<input  type="text" name="emailtitle" placeholder="Purchase extra option Project" id="emailtitle" value="<?php echo $emailtitle; ?>" style="width:100%;"/>							
			</div>
			
			<h3>Email Text</h3>
			<div class="options" style="width:50%;">
				<input  type="text" name="emailcontent" placeholder="You have used credit to purchase extra option" id="emailcontent" value="<?php echo $emailcontent; ?>" style="width:100%;"/>							
			</div>

			<h3>Email Link Text</h3>
			<div class="options" style="width:50%;">
				<input  type="text" name="credithistory" placeholder="Credit history" id="credithistory" value="<?php echo $credithistory; ?>" style="width:100%;"/>							
			</div>


			 <input style="margin-top:30px;" type="submit" class="button button-secondary " id="save_extra_btn" name="save_extra_btn" value="Save"/>
		   </form>

		  <div class="flash-message" style="width: 50%;margin-top:20px;">

		</div>

</div>
<?php
}

add_action( 'wp_ajax_save_extra_options', 'save_extra_options_init' );

function save_extra_options_init()
{	
 	update_option('urgentlabel',$_POST['urgentlabel']);
 	update_option('urgentdescription',$_POST['urgentdescription']);
 	update_option('urgentprice',$_POST['urgentprice']);

 	update_option('privatelabel',$_POST['privatelabel']);
 	update_option('privatedescription',$_POST['privatedescription']);
 	update_option('privateprice',$_POST['privateprice']);

	update_option('avaiblecredit',$_POST['avaiblecredit']);
	update_option('totalcost',$_POST['totalcost']);
	update_option('yourbalance',$_POST['yourbalance']);
	update_option('addcredits',$_POST['addcredits']);


	update_option('emailtitle',$_POST['emailtitle']);
	update_option('emailcontent',$_POST['emailcontent']);
	update_option('credithistory',$_POST['credithistory']);
	

 	//$result['message']='Update extra options successfully';
 	$result['adminnotice']='<div class="notice notice-success is-dismissible"><p>Updated Successfully ( dismiss )</p></div>';
    wp_send_json_success($result);
    die();
}

