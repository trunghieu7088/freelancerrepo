<?php



add_action('admin_init', 'add_post_gallery_so_14445904');

add_action('add_meta_boxes_page', 'add_page_gallery_so_14445904');

add_action('admin_head-post.php', 'print_scripts_so_14445904');

add_action('admin_head-post-new.php', 'print_scripts_so_14445904');

add_action('save_post', 'update_post_gallery_so_14445904', 10, 2);



// Make it work only in selected templates

$rep_fields_templates = []; //array('page-aboutus.php');

$rep_fields_posts = array('mjob_post');



/**

 * Add custom Meta Box

 */



// Add meta box to custom posts

function add_post_gallery_so_14445904()

{

    global $rep_fields_posts;

    add_meta_box(

        'post_gallery',

        'Audio Gallery',

        'post_gallery_options_so_14445904',

        $rep_fields_posts,

        'normal',

        'core'

    );

}



// Add meta box to custom page templates

function add_page_gallery_so_14445904()

{

    global $post, $rep_fields_templates;

    if (in_array(get_post_meta($post->ID, '_wp_page_template', true), $rep_fields_templates)) {

        add_meta_box(

            'post_gallery',

            'Audio Gallery',

            'post_gallery_options_so_14445904',

            'page',

            'normal',

            'core'

        );

    }

}



/**

 * Print the Meta Box content

 */

function post_gallery_options_so_14445904()

{

    global $post;

    $gallery_data = get_post_meta($post->ID, 'gallery_data', true);



    // Use nonce for verification

    wp_nonce_field(plugin_basename(__FILE__), 'noncename_so_14445904');

    ?>



    <div id="dynamic_form">



        <div id="field_wrap">

        <?php

if (isset($gallery_data['audio_url'])) {

        for ($i = 0; $i < count($gallery_data['audio_url']); $i++) {

            ?>



            <div class="field_row">



              <div class="field_left">

                <div class="form_field">

                  <!--<label>Image URL</label>-->

                  <input type="hidden"

                         class="meta_audio_url"

                         name="gallery[audio_url][]"

                         value="<?php esc_html_e($gallery_data['audio_url'][$i]);?>"

                  />

                  <input type="hidden"

                         class="meta_audio_id"

                         name="gallery[audio_id][]"

                         value="<?php esc_html_e($gallery_data['audio_id'][$i]);?>"

                  />

                </div>

                <div class="form_field" style="margin-bottom: 20px">

                  <label>Description</label>

                  <textarea

                         class="meta_audio_desc"

                         name="gallery[audio_desc][]"

                         rows="3"

                         style="width: 100%"><?php esc_html_e($gallery_data['audio_desc'][$i]);?></textarea>

                </div>

                <input class="button" type="button" value="Choose File" onclick="add_image(this)" />&nbsp;&nbsp;&nbsp;

                <input class="button" type="button" value="Remove" onclick="remove_field(this)" />

              </div>



              <div class="field_right audio_wrap">

                <audio controls><source src="<?php esc_html_e($gallery_data['audio_url'][$i]);?>" type="audio/mpeg">Your browser does not support the audio element.</audio>



              </div>

              <div class="clear" /></div>

            </div>

            <?php

} // endif

    } // endforeach

    ?>

        </div>



        <div style="display:none" id="master-row">

        <div class="field_row">

            <div class="field_left">

                <div class="form_field">

                    <!--<label>Image URL</label>-->

                    <input class="meta_audio_url" value=""  name="gallery[audio_url][]" />

                    <input class="meta_audio_id" value=""  name="gallery[audio_id][]" />

                </div>

                <div class="form_field" style="margin-bottom: 20px">

                    <label>Description</label>

                    <textarea class="meta_audio_desc" name="gallery[audio_desc][]" rows="3" style="width: 100%"></textarea>

                </div>

                <input type="button" class="button" value="Choose File" onclick="add_image(this)" />&nbsp;&nbsp;&nbsp;

                <input class="button" type="button" value="Remove" onclick="remove_field(this)" />

            </div>

            <div class="field_right audio_wrap">

            </div>

            <div class="clear"></div>

        </div>

        </div>



        <div id="add_field_row">

          <input class="button" type="button" value="Add File" onclick="add_field_row();" />

        </div>

        <?php if ('trend' == get_post_type($post->ID)) {?>

		<p style="color: #a00;">Make sure the number if images you add is a <b>multiple of 5</b>.</p>

		<?php }?>

    </div>

  	<?php

}



/**

 * Print styles and scripts

 */

function print_scripts_so_14445904()

{

    // Check for correct post_type

    global $post, $rep_fields_templates, $rep_fields_posts;

    if (!in_array(get_post_meta($post->ID, '_wp_page_template', true), $rep_fields_templates) &&

        !in_array(get_post_type($post->ID), $rep_fields_posts)) {

        return;

    }



    ?>

    <style type="text/css">

      .field_left {

        float:left;

		padding-right: 20px;

		box-sizing:border-box;

      }

      .field_right {

        float:left;

      }

	  .audio_wrap img {

		  max-width: 100%;

	  }

      #dynamic_form input[type=text] {

        width:100%;

      }

      #dynamic_form .field_row {

        border:1px solid #cecece;

        margin-bottom:10px;

        padding:10px;

        display:flex;

      }

      #dynamic_form label {

        display: block;

		    margin-bottom: 5px;

      }

      .meta_audio_url,.meta_audio_id,.meta_audio_desc{

        width:100%;

        border: 1px solid #ccc;

        padding: 10px;

        margin: 2px;

        border-radius:0;

        max-height:150px;

      }

      </style>



    <script type="text/javascript">

		function add_image(obj) {



			var parent=jQuery(obj).parent().parent('div.field_row');

			var inputField = jQuery(parent).find("input.meta_audio_url");

			var inputFieldID = jQuery(parent).find("input.meta_audio_id");

			var fileFrame = wp.media.frames.file_frame = wp.media({

				multiple: false

			});

			fileFrame.on('select', function() {

				var selection = fileFrame.state().get('selection').first().toJSON();

				inputField.val(selection.url);

				inputFieldID.val(selection.id);

				jQuery(parent)

				.find("div.audio_wrap")

				.html('<audio controls><source src="'+selection.url+'" type="audio/mpeg">Your browser does not support the audio element.</audio>');

			});

			fileFrame.open();

		//});

		};



		function remove_field(obj) {

			var parent=jQuery(obj).parent().parent();

			parent.remove();

		}



		function add_field_row() {

			var row = jQuery('#master-row').html();

			jQuery(row).appendTo('#field_wrap');

		}

	</script>

    <?php

}



/**

 * Save post action, process fields

 */

function update_post_gallery_so_14445904($post_id, $post_object)

{

    // Doing revision, exit earlier **can be removed**

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {

        return;

    }



    // Doing revision, exit earlier

    if ('revision' == $post_object->post_type) {

        return;

    }



    // Verify authenticity

    if (!wp_verify_nonce($_POST['noncename_so_14445904'], plugin_basename(__FILE__))) {

        return;

    }



    global $rep_fields_templates, $rep_fields_posts;

    if (!in_array(get_post_meta($post_id, '_wp_page_template', true), $rep_fields_templates) &&

        !in_array(get_post_type($post_id), $rep_fields_posts)) {

        return;

    }



    if ($_POST['gallery']) {

        // Build array for saving post meta

        $gallery_data = array();

        for ($i = 0; $i < count($_POST['gallery']['audio_url']); $i++) {

            if ('' != $_POST['gallery']['audio_url'][$i]) {

                $gallery_data['audio_url'][] = $_POST['gallery']['audio_url'][$i];

                $gallery_data['audio_id'][] = $_POST['gallery']['audio_id'][$i];

                $gallery_data['audio_desc'][] = $_POST['gallery']['audio_desc'][$i];

            }

        }



        if ($gallery_data) {

            update_post_meta($post_id, 'gallery_data', $gallery_data);

        } else {

            delete_post_meta($post_id, 'gallery_data');

        }



    }

    // Nothing received, all fields are empty, delete option

    else {

            delete_post_meta($post_id, 'gallery_data');

        }

    }



    add_shortcode('audio_gallery', 'wg_audio_gallery');

    function wg_audio_gallery($atts)

{

        ob_start();



        if ('' != get_post_meta(get_the_ID(), 'gallery_data', true)) {$gallery = get_post_meta(get_the_ID(), 'gallery_data', true);}



        if (isset($gallery['audio_id'])):

            echo '<div class="audio_gallery">';

            for ($i = 0; $i < count($gallery['audio_id']); $i++) {

                if ('' != $gallery['audio_id'][$i]) {



                    echo '<div class="audio_item"><audio class="audio_player" controls><source src="' . wp_get_attachment_url($gallery['audio_id'][$i]) . '" type="audio/mpeg">Your browser does not support the audio element.</audio>';



                    if (isset($gallery['audio_desc'][$i])) {

                        echo "<p>".$gallery['audio_desc'][$i]."</p>";

                    }

                    echo "</div>";

                }

            }

            echo '</div>';?>

		      <style>



            .audio_gallery {

              display: flex;

              flex-wrap:wrap;

              background: #11a3ef7d;

              border-radius: 10px;

              margin-bottom: 20px;

              padding: 20px 0;

          

            }

            .audio_item {

              padding: 10px;

              width:50%;

            }

            @media only screen and (max-width:680px){

              .audio_item p {

                top: 8px !important;

                border-radius: 0 10px 10px 10px;

              }

            }

            .audio_item p {

                text-align: center;

                background: #545353;

                max-width: 80%;

                margin: auto;

                top: -10px;

                position: relative;

                color: white;

                border-radius: 0 0 10px 10px;

                font-size: 12px;

                margin-bottom:0 !important;

            }

            

            audio.audio_player {

                padding: 10px;

                outline: none;

                display: flex;

                width: 100%;

                z-index:99;

                position:relative;

            }

		      </style>



		    <?php

    endif;



        $output = ob_get_contents();

        ob_end_clean();



        return $output;

    }



    function embed_audio_gallery($content)

{

        $custom_content = do_shortcode('[audio_gallery]');

        $custom_content .= $content;

        return $custom_content;

    }

    add_filter('the_content', 'embed_audio_gallery');

add_action('wp_enqueue_scripts', 'add_custom_js', 20120207);
function add_custom_js()
{
    wp_dequeue_script('custom-order');
    wp_enqueue_script('custom-order-offer', get_stylesheet_directory_uri().'/assets/js/custom-order.js', array('jquery'),20151110,true);
}

function add_page_for_download_invoice()
{
$PageGuid = site_url() . "/download-invoice";
$check_exist=get_page_by_title('Download Invoice');
      if(empty($check_exist))
      {
        $download_page = array( 'post_title'     => 'Download Invoice',
                         'post_type'      => 'page',
                         'post_name'      => 'download-invoice',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $download_page_id=wp_insert_post( $download_page, FALSE ); 
      add_post_meta($download_page_id,'_wp_page_template','page-download-invoice-pdf.php');
      }

}

add_action( 'init', 'add_page_for_download_invoice' );

function convert_month_to_french($date)
{
  $date_convert=explode(" ",$date);
  $date_french=$date_convert[1];
  switch ($date_french) 
  {
  
    case 'January':
    $date_french='janvier';
    break;
    
    case 'February':
    $date_french='février';
    break;
    
    case 'March':
    $date_french='mars';
    break;
   
    case 'April':
    $date_french='avril';
    break;    

    case 'May':
    $date_french='mai';
    break;

    case 'June':
    $date_french='juin';
    break;

    case 'July':
    $date_french='juillet';
    break;

    case 'August':
     $date_french='août';
    break;

    case 'September':
     $date_french='septembre';
    break;
    
      case 'October':
     $date_french='octobre';
    break;

    case 'November':
     $date_french='novembre';
    break;

    case 'December':
     $date_french='décembre';
    break;
    
}
  $date_after_convert=$date_convert[0].' '.$date_french.' '.$date_convert[2];
 return $date_after_convert;

}

function download_invoice_pdf($invoiceid)
{
	require('fpdf/fpdf.php');
	
  $invoice_info=get_post_meta($invoiceid);
  $invoice_order=get_post($invoiceid);
  $invoice_client=get_user_by('id',$invoice_order->post_author);
  $invoice_client_info=get_user_meta($invoice_client->id);
  $invoice_client_profile=get_post_meta($invoice_client_info['user_profile_id'][0]);


 //var_dump($extra_ids);
//exit;
  $invoice_order_number='#'.$invoiceid;
  $invoice_client_name=$invoice_client->display_name;
  $invoice_client_address= $invoice_client_profile['billing_full_address'][0];
  $invoice_client_bs_full_name=$invoice_client_profile['billing_full_name'][0];
  $invoice_created_time=date( get_option( 'date_format' ), $invoice_info['et_order_created_time'][0] );
  $invoice_payment_method=get_post_meta($invoice_info['et_order_product_id'][0],'payment_type',true);
 
 
 

  $Frais_bancaires=get_post_meta($invoice_info['et_order_product_id'][0],'extra_fee_fixed',true);
  $commission_percent=get_post_meta($invoice_info['et_order_product_id'][0],'fee_commission',true);  
  $extra_ids=get_post_meta($invoice_info['et_order_product_id'][0],'extra_ids',true);
  $total_price_extra_services=0;



  if(!empty($extra_ids))
  {
    foreach($extra_ids as $value)
    {
      $total_price_extra_services=$total_price_extra_services + get_post_meta($value,'et_budget',true);
    }    
    
  }
  $commission_of_ex_service=(float)$total_price_extra_services * $commission_percent / 100;
  $price_of_service=get_post_meta($invoice_info['et_order_product_id'][0],'mjob_price',true);
  
  // check neu co offer id 
  $custom_offer_id=get_post_meta($invoice_info['et_order_product_id'][0],'custom_offer_id',true);
  if(!empty($custom_offer_id))
  {
    $price_of_service=get_post_meta($custom_offer_id,'custom_offer_budget',true);
  }

  if ($commission_percent > 0) 
  {
    $commission = (float)$price_of_service * $commission_percent  / 100;
  }
  else
  {
     $commission =0;
  }

  if(!empty($commission_of_ex_service))
  {
    $commission=$commission + $commission_of_ex_service;
  }

  $total_ht_client=$Frais_bancaires + $commission;


  $freelancer_id=get_post_meta($invoice_info['et_order_product_id'][0],'seller_id',true);
  $freelancer_info=get_user_by('id',$freelancer_id);
  $freelancer_name=$freelancer_info->display_name;
  $freelancer_profile_id=get_user_meta($freelancer_id,'user_profile_id',true);
  $freelancer_address=get_post_meta($freelancer_profile_id,'billing_full_address',true);
  $freelancer_bs_full_name=get_post_meta($freelancer_profile_id,'billing_full_name',true);
  $freelancer_tax_number=get_post_meta($freelancer_profile_id,'billing_vat',true);



  $total_freelancer=get_post_meta($invoice_info['et_order_product_id'][0],'amount',true);

  $order_post=get_post($invoice_info['et_order_product_id'][0]);
  $service_name=str_replace('Order for','',$order_post->post_title);


  $image1 = get_stylesheet_directory_uri().'/pdfimg/logoimg.png';


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica','',10);


//$pdf->Cell( 20, 20, $pdf->Image($image1, $pdf->GetX(), $pdf->GetY(), 33.78), 0, 0, 'L', false );
$pdf->Cell( 20, 20, $pdf->Image($image1, $pdf->GetX(), $pdf->GetY(), 20.40), 0, 0, 'L', false );



$pdf->SetFont('Helvetica','',10);
$pdf->SetTextColor(65, 65, 65);
$pdf->SetY(15);
$pdf->SetX(140);

$pdf->Cell( 60, 10, 'ALEXIS DANZI', 0, 0, 'R',false ); 


 $pdf->Ln(7);
 $pdf->SetX(140);
 
 $pdf->Cell( 60, 10, 'AUTO-ENTREPRENEUR', 0, 0, 'R',false ); 

$pdf->Ln(7);
$pdf->SetX(140);

$pdf->Cell( 60, 10, ' SIRET: 888 194 735 00013', 0, 0, 'R',false ); 



$pdf->SetFont('helvetica','B',18);
$pdf->SetTextColor(93, 93, 103);
$pdf->SetY(17);
$pdf->Cell(80, 10,'voixoffmaster.com', 0, 0, 'R',false ); 

$pdf->SetFont('helvetica','B',20);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(75);
$pdf->Cell(100, 10,'Facture Voix Off Master', 0, 0, 'L',false ); 

$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(75);
$pdf->SetX(93);
$pdf->Cell(100, 10,utf8_decode($invoice_order_number), 0, 0, 'L',false ); 

// objet:
$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(90);
$pdf->Cell(100, 10,'Objet : ', 0, 0, 'L',false ); 

// order number
$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(90);
$pdf->SetX(25);
$pdf->Cell(100, 10,'Commande '.utf8_decode($invoice_order_number), 0, 0, 'L',false ); 

// pour :
$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(100);
$pdf->Cell(100, 10,'Pour :', 0, 0, 'L',false ); 

// client name
$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(100);
$pdf->SetX(25);
$pdf->Cell(100, 10,utf8_decode($invoice_client_name), 0, 0, 'L',false ); 

// address of the client


$pdf->SetY(110);
$pdf->SetX(25);
$pdf->Cell(100, 10,utf8_decode($invoice_client_address), 0, 0, 'L',false ); 



$pdf->SetFont('helvetica','B',12);

$pdf->SetY(120);
$pdf->SetX(10);
$pdf->Cell(100, 10,'Entreprise: ', 0, 0, 'L',false ); 

$pdf->SetFont('helvetica','',12);
$pdf->SetY(120);
$pdf->SetX(35);
$pdf->Cell(100, 10,utf8_decode($invoice_client_bs_full_name), 0, 0, 'L',false ); 

//#
$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->setFillColor(201, 229, 243); 
$pdf->SetY(140);
$pdf->SetX(10);
$pdf->Cell(15, 10,'#', 0, 0, 'C',true ); 


//Description


$pdf->SetY(140);
$pdf->SetX(28);
$pdf->Cell(40, 10,'Description ', 0, 0, 'C',true ); 


//Quantity


$pdf->SetY(140);
$pdf->SetX(70);
$pdf->Cell(35, 10,utf8_decode('Quantité'), 0, 0, 'C',true ); 


//TVA

$pdf->SetY(140);
$pdf->SetX(107);
$pdf->Cell(20, 10,'TVA', 0, 0, 'C',true ); 


//Prix unitaire


$pdf->SetY(140);
$pdf->SetX(130);
$pdf->Cell(30, 10,'Prix unitaire', 0, 0, 'C',true ); 

//Total HT


$pdf->SetY(140);
$pdf->SetX(162);
$pdf->Cell(36, 10,'Total HT', 0, 0, 'C',true ); 

$pdf->SetFont('helvetica','',10);
$pdf->SetTextColor(0, 0, 0);

//text 
$pdf->SetY(150); 
$pdf->SetX(10); 
$pdf->Cell(15, 10,'1', 0, 0, 'C',false ); 

// commission
$pdf->SetY(150); 
$pdf->SetX(28); 
$pdf->Cell(40, 10,'Commission', 0, 0, 'C',false ); 

// quantity text 
$pdf->SetY(150);
$pdf->SetX(70);
$pdf->Cell(35, 10,'1', 0, 0, 'C',false ); 

// 20%
$pdf->SetY(150);
$pdf->SetX(107);
$pdf->Cell(20, 10,'0%', 0, 0, 'C',false ); 


$pdf->SetY(150);
$pdf->SetX(130);
$pdf->Cell(30, 10,number_format($commission,2,",",".").' '.chr(128), 0, 0, 'C',false ); 

$pdf->SetY(150);
$pdf->SetX(162);
$pdf->Cell(36, 10,number_format($commission,2,",",".").' '.chr(128), 0, 0, 'C',false ); 


//text 
$pdf->SetY(160); 
$pdf->SetX(10); 
$pdf->Cell(15, 10,'2', 0, 0, 'C',false ); 

// line 2
$pdf->SetY(160); 
$pdf->SetX(28); 
$pdf->Cell(40, 10,'Frais bancaires', 0, 0, 'C',false ); 

// line 2
$pdf->SetY(160);
$pdf->SetX(70);
$pdf->Cell(35, 10,'1', 0, 0, 'C',false ); 

// line 2
$pdf->SetY(160);
$pdf->SetX(107);
$pdf->Cell(20, 10,'0%', 0, 0, 'C',false ); 


$pdf->SetY(160);
$pdf->SetX(130);
$pdf->Cell(30, 10,number_format($Frais_bancaires,2,",",".").' '.chr(128), 0, 0, 'C',false ); 

$pdf->SetY(160);
$pdf->SetX(162);
$pdf->Cell(36, 10,number_format($Frais_bancaires,2,",",".").' '.chr(128), 0, 0, 'C',false ); 

$pdf->SetFont('helvetica','B',10);
$pdf->SetY(170);
$pdf->SetX(130);
$pdf->Cell(30, 10,'Total HT', 0, 0, 'C',false ); 

$pdf->SetY(180);
$pdf->SetX(130);
$pdf->Cell(30, 10,'TVA', 0, 0, 'C',false ); 

$pdf->SetY(190);
$pdf->SetX(130);
$pdf->Cell(30, 10,'Total TTC', 0, 0, 'C',false ); 

$pdf->SetDrawColor(255, 255, 255);

$pdf->SetY(170);
$pdf->SetX(162);
$pdf->Cell(36, 10,number_format($total_ht_client,2,",",".").' '.chr(128), 1, 0, 'C',true ); 

$pdf->SetY(180);
$pdf->SetX(162);
$pdf->Cell(36, 10,'0 '.chr(128), 1, 0, 'C',true ); 

$pdf->SetY(190);
$pdf->SetX(162);
$pdf->Cell(36, 10,number_format($total_ht_client,2,",",".").' '.chr(128), 1, 0, 'C',true ); 


$pdf->SetY(210);
$pdf->SetX(18);
$pdf->Cell(50, 10,'T.V.A non applicable, article 293 B du CGI', 0, 0, 'C',false ); 

$pdf->SetFont('helvetica','B',10);

$pdf->SetY(220);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Date de facture : ', 0, 0, 'L',false ); 


$pdf->SetY(230);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Date de paiement : ', 0, 0, 'L',false ); 


$pdf->SetY(240);
$pdf->SetX(8);
$pdf->Cell(50, 10,utf8_decode('Conditions de paiement: '), 0, 0, 'L',false ); 


/*
$pdf->SetY(250);
$pdf->SetX(8);
$pdf->Cell(50, 10,utf8_decode('Référence de la transaction : '), 0, 0, 'L',false ); 

*/

$pdf->SetFont('helvetica','',10);

$pdf->SetY(240);
$pdf->SetX(50);
$pdf->Cell(50, 10,utf8_decode('payé par'), 0, 0, 'L',false ); 

$pdf->SetY(220);
$pdf->SetX(38);
$pdf->Cell(50, 10,utf8_decode(convert_month_to_french($invoice_created_time)), 0, 0, 'L',false ); 

$pdf->SetY(230);
$pdf->SetX(42);
$pdf->Cell(50, 10,utf8_decode(convert_month_to_french($invoice_created_time)), 0, 0, 'L',false ); 

$pdf->SetY(240);
$pdf->SetX(65);
$pdf->Cell(50, 10,utf8_decode($invoice_payment_method), 0, 0, 'L',false ); 
/*
$pdf->SetY(250);
$pdf->SetX(58);
$pdf->Cell(50, 10,utf8_decode('83f907762D234'), 0, 0, 'L',false ); */

//$pdf->SetLineWidth(1);
$pdf->SetDrawColor(93, 93, 103);
$pdf->Line(10, 265, 200, 265);

$pdf->SetY(265);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Pour toute question concernant cette facture, veuillez vous rendre sur', 0, 0, 'L',false ); 

$pdf->SetY(265);
$pdf->SetX(118);

 $pdf->SetTextColor( 2, 155, 235 );
$pdf->Cell(25, 10,'notre FAQ', 0, 0, 'L',false,'https://voixoffmaster.com/faq/'); 

// new page ( second page ) :

$pdf->AddPage('p','a4');

$pdf->Cell( 20, 20, $pdf->Image($image1, $pdf->GetX(), $pdf->GetY(), 20.40), 0, 0, 'L', false );


$pdf->SetFont('Helvetica','',10);
$pdf->SetTextColor(65, 65, 65);
$pdf->SetY(15);
$pdf->SetX(140);

$pdf->Cell( 60, 10, 'ALEXIS DANZI', 0, 0, 'R',false ); 


 $pdf->Ln(7);
 $pdf->SetX(140);
 
 $pdf->Cell( 60, 10, 'AUTO-ENTREPRENEUR', 0, 0, 'R',false ); 

$pdf->Ln(7);
$pdf->SetX(140);

$pdf->Cell( 60, 10, ' SIRET: 888 194 735 00013', 0, 0, 'R',false ); 

$pdf->SetFont('helvetica','B',18);
$pdf->SetTextColor(93, 93, 103);
$pdf->SetY(17);
$pdf->Cell(80, 10,'voixoffmaster.com', 0, 0, 'R',false ); 

$pdf->SetFont('helvetica','B',20);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(75);
$pdf->Cell(100, 10,'Facture vendeur '.$invoice_order_number, 0, 0, 'L',false ); 


// objet:
$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(90);
$pdf->Cell(100, 10,'Objet : ', 0, 0, 'L',false ); 

$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(90);
$pdf->SetX(25);
$pdf->Cell(100, 10,'Commande '.$invoice_order_number, 0, 0, 'L',false ); 


$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(100);
$pdf->Cell(100, 10,'Pour :', 0, 0, 'L',false ); 


$pdf->SetY(100);
$pdf->SetX(120);
$pdf->Cell(100, 10,'De :', 0, 0, 'L',false ); 

// client name
$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(100);
$pdf->SetX(25);
$pdf->Cell(100, 10,utf8_decode($invoice_client_name), 0, 0, 'L',false ); 

// the freelaner name

$pdf->SetY(100);
$pdf->SetX(130);
$pdf->Cell(100, 10,utf8_decode($freelancer_name), 0, 0, 'L',false ); 

// address of the client
$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetY(110);
$pdf->SetX(25);
$pdf->Cell(100, 10,utf8_decode($invoice_client_address), 0, 0, 'L',false ); 

// address of the freelancer
$pdf->SetY(110);
$pdf->SetX(130);
$pdf->Cell(100, 10,utf8_decode($freelancer_address), 0, 0, 'L',false ); 

// siret of the freelancer
$pdf->SetY(120);
$pdf->SetX(130);
$pdf->Cell(100, 10,'SIRET : '.utf8_decode($freelancer_tax_number), 0, 0, 'L',false ); 

// business full name

$pdf->SetFont('helvetica','B',12);
$pdf->SetY(120);
$pdf->SetX(10);
$pdf->Cell(100, 10,'Entreprise: ', 0, 0, 'L',false ); 

$pdf->SetFont('helvetica','',12);
$pdf->SetY(120);
$pdf->SetX(35);
$pdf->Cell(100, 10,utf8_decode($invoice_client_bs_full_name), 0, 0, 'L',false ); 

$pdf->SetFont('helvetica','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->setFillColor(201, 229, 243); 
$pdf->SetY(140);
$pdf->SetX(10);
$pdf->Cell(15, 10,'#', 0, 0, 'C',true ); 


//Description


$pdf->SetY(140);
$pdf->SetX(26);
$pdf->Cell(110, 10,'Description', 0, 0, 'C',true ); 


//Quantity


$pdf->SetY(140);
$pdf->SetX(137);
$pdf->Cell(35, 10,utf8_decode('Quantité'), 0, 0, 'C',true ); 


//Total


$pdf->SetY(140);
$pdf->SetX(173);
$pdf->Cell(30, 10,'Total', 0, 0, 'C',true ); 

// line 1

$pdf->SetFont('helvetica','',10);

$num_row=1;
$y_position=150;
$pdf->SetY(150);
$pdf->SetX(10);
$pdf->Cell(15, 10,$num_row, 0, 0, 'C',false ); 



$pdf->SetY(150);
$pdf->SetX(26);
$pdf->Cell(110, 10,utf8_decode($service_name), 0, 0, 'C',false ); 


$pdf->SetY(150);
$pdf->SetX(137);
$pdf->Cell(35, 10,'1', 0, 0, 'C',false ); 



$pdf->SetY(150);
$pdf->SetX(173);
$pdf->Cell(30, 10,number_format($price_of_service,2,",",".").' '.chr(128), 0, 0, 'C',false ); 

if(!empty($extra_ids))
{
  foreach($extra_ids as $extra_id)
  {
      $y_position=$y_position+10;
      $num_row=$num_row+1;
      $pdf->SetY($y_position);
      $pdf->SetX(10);
      $pdf->Cell(15, 10,$num_row, 0, 0, 'C',false ); 
      
      $pdf->SetY($y_position);
      $pdf->SetX(26);
      $extra_sv1_info=get_post($extra_id);
      $pdf->Cell(110, 10,utf8_decode($extra_sv1_info->post_title), 0, 0, 'C',false ); 

      $pdf->SetY($y_position);
      $pdf->SetX(137);
      $pdf->Cell(35, 10,'1', 0, 0, 'C',false ); 

      $pdf->SetY($y_position);
      $pdf->SetX(173);
      $pdf->Cell(30, 10,number_format(get_post_meta($extra_id,'et_budget',true),2,",",".").' '.chr(128), 0, 0, 'C',false ); 
  }
}

$pdf->SetFont('helvetica','B',10);


$pdf->SetY($y_position+10);
$pdf->SetX(137);
$pdf->Cell(35, 10,'Total TTC', 0, 0, 'C',false ); 

$pdf->SetFont('helvetica','',10);

$pdf->SetY($y_position+10);
$pdf->SetX(173);
$pdf->Cell(30, 10,number_format($price_of_service+$total_price_extra_services,2,",",".").' '.chr(128), 0, 0, 'C',true ); 


$pdf->SetFont('helvetica','B',10);

$pdf->SetY(220);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Date de facture : ', 0, 0, 'L',false ); 


$pdf->SetY(230);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Date de paiement : ', 0, 0, 'L',false ); 


$pdf->SetY(240);
$pdf->SetX(8);
$pdf->Cell(50, 10,utf8_decode('Conditions de paiement: '), 0, 0, 'L',false ); 


/*
$pdf->SetY(250);
$pdf->SetX(8);
$pdf->Cell(50, 10,utf8_decode('Référence de la transaction : '), 0, 0, 'L',false ); 
*/

$pdf->SetFont('helvetica','',10);

$pdf->SetY(240);
$pdf->SetX(50);
$pdf->Cell(50, 10,utf8_decode('payé par'), 0, 0, 'L',false ); 

$pdf->SetY(220);
$pdf->SetX(38);
$pdf->Cell(50, 10,utf8_decode(convert_month_to_french($invoice_created_time)), 0, 0, 'L',false ); 

$pdf->SetY(230);
$pdf->SetX(42);
$pdf->Cell(50, 10,utf8_decode(convert_month_to_french($invoice_created_time)), 0, 0, 'L',false ); 

$pdf->SetY(240);
$pdf->SetX(65);
$pdf->Cell(50, 10,utf8_decode($invoice_payment_method), 0, 0, 'L',false ); 

/*
$pdf->SetY(250);
$pdf->SetX(58);
$pdf->Cell(50, 10,utf8_decode('83f907762D234'), 0, 0, 'L',false ); */

//$pdf->SetLineWidth(1);
$pdf->SetDrawColor(93, 93, 103);
$pdf->Line(10, 265, 200, 265);

$pdf->SetY(265);
$pdf->SetX(8);
$pdf->Cell(50, 10,'Pour toute question concernant cette facture, veuillez vous rendre sur', 0, 0, 'L',false ); 

$pdf->SetY(265);
$pdf->SetX(118);

 $pdf->SetTextColor( 2, 155, 235 );
$pdf->Cell(25, 10,'notre FAQ', 0, 0, 'L',false,'https://voixoffmaster.com/faq/'); 

//$pdf->Output();

   $pdf->Output('Facture_VoixOffMaster_#'.$invoiceid.'.pdf','D');

}
add_action( 'download_invoice_client_pdf', 'download_invoice_pdf',100,1 );

require('function_download_pdf_freelancer.php');

function add_page_for_income()
{
$PageGuid = site_url() . "/income-page";
$check_exist=get_page_by_title('Income Page');
      if(empty($check_exist))
      {
        $income_page = array( 'post_title'     => 'Income Page',
                         'post_type'      => 'page',
                         'post_name'      => 'income-page',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $download_page_id=wp_insert_post( $income_page, FALSE ); 
      add_post_meta($download_page_id,'_wp_page_template','page-my-income.php');
      }

}

add_action( 'init', 'add_page_for_income' );


function add_page_for_detail_income()
{
$PageGuid = site_url() . "/detail-income";
$check_exist=get_page_by_title('Detail Income');
      if(empty($check_exist))
      {
        $detail_income_page = array( 'post_title'     => 'Detail Income',
                         'post_type'      => 'page',
                         'post_name'      => 'detail-income',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $detail_income_page_id=wp_insert_post( $detail_income_page, FALSE ); 
      add_post_meta($detail_income_page_id,'_wp_page_template','page-detail-income.php');
      }

}

add_action( 'init', 'add_page_for_detail_income' );


add_action('wp_enqueue_scripts', 'override_heartbeatjs');
function override_heartbeatjs()
{    
    wp_deregister_script('mje-heartbeat');
    wp_enqueue_script('mje-heartbeat', get_stylesheet_directory_uri().'/assets/js/heartbeat.js', array(
                'front'
            ), ET_VERSION, true);
	
	  wp_deregister_script('notification');
   wp_enqueue_script('notification', get_stylesheet_directory_uri().'/assets/js/notification.js', array(
               'front'
            ), ET_VERSION, true);
	
}

require('order_message_function.php');

require('suggest-message-function.php');

require('review-function.php');

require('online-functions.php');

require('realtime-functions.php');
    ?>