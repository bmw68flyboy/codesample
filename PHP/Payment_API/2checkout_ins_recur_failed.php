<?php

error_reporting(E_ALL ^ E_NOTICE);
define('SE_ROOT', $_SERVER['DOCUMENT_ROOT']);

include SE_ROOT."/include/database_config.php";
include SE_ROOT."/include/versions.php";
include SE_ROOT."/include/functions.php";

// RUN SECURITY ON ARRAYS
$_POST = security($_POST);
$_GET = security($_GET);

// RUN FUNCTION TO GET ARRAY OF ALL STATIC URL PAGES
$GLOBALS['urls'] = Array();
$urls_query = mysql_query("SELECT url_tag, url_path FROM urls");
while($urls_info = mysql_fetch_assoc($urls_query)) { $GLOBALS['urls'][$urls_info['url_tag']] = $urls_info['url_path']; }

// GET 2CHECKOUT VARS
$is_error = false;
$error = array();
$secret_word = "secret_word";
$vendor_number = 12345;
$vendor_id = $_POST['vendor_id'];
echo $vendor_id;
$sale_id = $_POST['sale_id'];
$invoice_id = $_POST['invoice_id'];
$md5_hash = $_POST['md5_hash'];
$message_type = $_POST['message_type'];

// IF THIS IS NOT A RECURRING ORDER, EXIT
if($message_type != "RECURRING_INSTALLMENT_FAILED") { exit(); }


// ATTEMPT TO GET USER FROM SALE ID
$user_query = mysql_query("SELECT users.*, user_products.user_product_date_purchase FROM user_products LEFT JOIN users ON user_products.user_product_user_id=users.user_id WHERE user_product_gateway='2checkout' AND user_product_transID='{$sale_id}' ORDER BY user_product_date_purchase DESC LIMIT 1");
if(mysql_num_rows($user_query) == 1) {
  $user_info = mysql_fetch_assoc($user_query);
} else {
  $is_error = true;
  $error[] = "No transaction (user_products table) exists for Sale ID: {$sale_id}";
}

// COMPARE VENDOR IDS
if($vendor_id != $vendor_number) {
  $is_error = true;
  $error[] = "Posted vendor ID does not match our vendor ID.";
}

// COMPARE MD5 HASH
if($md5_hash != strtoupper(md5($sale_id.$vendor_id.$invoice_id.$secret_word))) {
  $is_error = true;
  $error[] = "Posted md5 hash doesn't match our hashed string.";
}

// IF NO ERROR, AND CORRECT MESSAGE TYPE, CONTINUE
if($message_type == "RECURRING_INSTALLMENT_FAILED" && !$is_error) {
  $error[] = "No error!";

      // SEND NOTIFICATION EMAIL TO USER
      $subject = "Payment Not Recieved";
      $message = "Hello,\n\nYour ongoing support payment did not process. If you have recently changed your billing information you will need to update your billing information on 2checkout. Please let us know if you have any questions or concerns!\n\nBest Regards,\nThe Team\n.net\n\n--------------------------------\nDO NOT REPLY TO THIS EMAIL - Your reply will not be received. If you require assistance, please contact us here: ".href('contact-page');
      $headers = "From: The Team <noreply@email.net>";
      mail('$user_info['user_email']', $subject, $message, $headers);

      // ADD NOTE
      $note = "Ongoing support renew failed: 2checkout $sale_id";
      mysql_query("INSERT INTO user_notes (user_note_user_id, user_note_text) VALUES ('{$user_info['user_id']}', '{$note}')");
      
} 

// SEND EMAIL IF THERE WAS AN ERROR
if($is_error) {

  // SEND EMAIL TO WEBLIGO ABOUT PAYMENT
  mail("2checkout@email.com, matt@email.com, richard@email.com", "2Checkout Order Error", "There was an error (2checkout_ins.php) for the 2checkout transaction {$sale_id} : {$invoice_id} :\n\n".implode("\n\n", $error), "From: The Team <noreply@email.net>");
}

?>