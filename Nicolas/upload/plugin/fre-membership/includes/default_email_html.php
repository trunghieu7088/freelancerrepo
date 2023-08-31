<?php




function get_df_expired_soon_email_template(){
  $message = "<p>Hi [user_login],</p>
  <p>Your subscription will expire soon and it will be renewed at [expiration_date]. </p>
   Here are your subscription info.<br />
        <ul>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li><span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>

        <p>You can check detail of your subscription in <a href='[profile_link]'>My Profile</a>.</p>
  <p>Regards,<br />[blogname]</p>";
  return $message;
}
function get_df_auto_renew_success_email_template(){
     $message = "<p>Hi [user_login],</p>
  <p>Your subscription has been auto renew.</p>

        Here are your subscription info.<br />
        <ul>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li><span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>

     <p>You can check your subscription detail in <a href='[profile_link]'>My Profile </a>.</p>

  <p>Regards,<br />[blogname]</p>";
  return $message;
}
function get_df_auto_renew_fail_email_template(){
    $message = "<p>Hi [user_login],</p>
  <p>Out system could not auto renew your subscription.<br />
   Here are your subscription info.<br />
        <ul>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li><span>Expired at:</span><strong>[expiration_date]</strong>. </li>
        </ul>
  <p> You can  check your subscription detail in <a href='[profile_link]'> My Profile </a> and update.</p>
  <p>Regards,<br />[blogname]</p>";
  return $message;
}

function get_df_auto_renew_fail_email_no_pack($subscriber){
    $message = "<p>Hi [user_login],</p>
  <p>Out system could not auto renew your subscription.<br />
        <ul>
            <li><span>Reason: </span><strong>Your subscription plan <i>{$subscriber->plan_sku}</i>  is not available in system.</strong>. </li>
        </ul>
  <p> You can  check your subscription detail in <a href='[profile_link]'> My Profile </a> and update.</p>
  <p>Regards,<br />[blogname]</p>";
  return $message;
}

function get_cancel_membership_admin(){
   $message = "<p>Hi Administrator,</p>
  <p>Member has disabeld auto-renewal in your website.</p>


        Here are detail of this subscriber:<br />
        <ul>
            <li><span> User Name: </span><strong>[user_login]</strong>. </li>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li> <span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>


  <p>Regards,<br />[blogname]</p>";
  return $message;
}

function get_cancel_membership(){
    $message = "<p>Hi [user_login],</p>

      <p>Your subscription has been disabeld auto-renewal.</p>
      <p>
        Here are your subscription info.<br />
        <ul>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li> <span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>
      </p>
      <p>You can check your subscription detail in <a href='[profile_link]'>My Profile </a>.</p>
      <p>Regards,<br />[blogname]</p>";
      return $message;
}


function get_df_subscriber_successful_mail_template(){
    $message = "<p>Hi [user_login],</p>

      <p>Thank you for your subscription.</p>
      <p>
        Here are your subscription detail.<br />
        <ul>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li> <span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>
      </p>
      <p>You can check your subscription detail in <a href='[profile_link]'>My Profile </a>.</p>
      <p>Regards,<br />[blogname]</p>";
      return $message;
}
function subscriber_successful_notify_admin_mail(){
    $link = admin_url("admin.php?page=membership-view.php");
    $message = "<p>Hi Administrator,</p>

      <p>There is a new subscriber on your website.</p>
      <p>
        Here are the detail.<br />
        <ul>
            <li><span>User Name: </span><strong>[user_login]</strong>. </li>
            <li><span>Plan name: </span><strong>[plan_name]</strong>. </li>
            <li><span>Price:</span> <strong>[plan_price]</strong>. </li>
            <li> <span>Expiration date:</span><strong>[expiration_date]</strong>. </li>
        </ul>
      </p>
      <p>You can check <a href='{$link}'>Membership List</a> in WordPress dashboard.</p>
      <p>Regards,<br />[blogname]</p>";
      return $message;
}
