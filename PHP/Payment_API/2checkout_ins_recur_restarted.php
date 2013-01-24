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
$secret_word = "secret";
$vendor_number = 12345;
$vendor_id = $_POST['vendor_id'];
$sale_id = $_POST['sale_id'];
$invoice_id = $_POST['invoice_id'];
$md5_hash = $_POST['md5_hash'];
$message_type = $_POST['message_type'];
$item_rec_date_next_1 = "2007-02-01";
list($year, $month, $day) = split('-', $item_rec_date_next_1);
$recur_expire = mktime(0, 0, 0, $month, $day, $year);

// IF THIS IS NOT A RECURRING ORDER, EXIT
if($message_type != "RECURRING_RESTARTED") { exit(); }


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

// CHECK IF NO SUPPORT USER ORDERED SUPPORT
$support_query = mysql_fetch_assoc(mysql_query("SELECT * FROM products WHERE product_type='recur_support' ORDER BY product_id DESC LIMIT 1"));
if($user_info['user_nosupport'] == 1 && (mysql_num_rows(mysql_query("SELECT NULL FROM user_products WHERE user_product_cancelled='0' AND user_product_gateway='2checkout' AND user_product_transID='{$sale_id}' AND user_product_product_id='{$support_query['product_id']}'")) || mysql_num_rows(mysql_query("SELECT NULL FROM user_products WHERE user_product_cancelled='0' AND user_product_gateway='2checkout' AND user_product_transID='{$sale_id}' AND user_product_product_id='{$recur_support_query['product_id']}'")))) {
  $is_error = true;
  $error[] = "A no-support user has attempted to purchase support.";

  // ADD NOTE
  $note = "No-support user recurring support renewed: 2checkout {$sale_id}";
  mysql_query("INSERT INTO user_notes (user_note_user_id, user_note_text) VALUES ('{$user_info['user_id']}', '{$note}')");
}

// IF NO ERROR, AND CORRECT MESSAGE TYPE, CONTINUE
if($message_type == "RECURRING_RESTARTED" && !$is_error) {
  $error[] = "No error!";
  
  // UPDATE TRANSACTIONS
  $cur_date = time();
  mysql_query("INSERT INTO user_products (user_product_user_id, user_product_product_id, user_product_date_purchase, user_product_price, user_product_gateway, user_product_transID, user_product_status, user_product_extra, user_product_invoiceID) VALUES ('{$user_info['user_id']}', '19', '{$cur_date}', '{$support_query['product_price']}', '2checkout', '{$sale_id}', '1', 'renew', '{$invoice_id}')");
  // UPDATE SUPPORT TIME IF RECURRING
    mysql_query("UPDATE users SET user_date_supportexpires='{$recur_expire}' WHERE user_id='{$user_info['user_id']}'"); 
    
    // ADD NOTE
      $note = "Ongoing support restarted, renews: {$recur_expire}";
      mysql_query("INSERT INTO user_notes (user_note_user_id, user_note_text) VALUES ('{$user_info['user_id']}', '{$note}')");
  } 

  // CHECK THAT THEIR PURCHASE HASN'T ALREADY BEEN CANCELLED
  $purchase = mysql_num_rows(mysql_query("SELECT NULL FROM user_products WHERE user_product_cancelled='0' AND user_product_gateway='2checkout' AND user_product_transID='{$sale_id}'"));
  $cancelled = mysql_num_rows(mysql_query("SELECT NULL FROM users WHERE user_id='{$user_info['user_id']}' AND user_recur_cancel='0'"));
  if($purchase != 0 || $cancelled != 0) {

      // SEND NOTIFICATION EMAIL TO USER
      $subject = "Order Completed";
      $date = date("F j, Y", $recur_expire);
      $message = "Hello,\n\nThanks again for your ongoing support purchase. Your support has been restarted and will renew again automatically on $date . To submit a support ticket login to the client area at: ".href('client-index')."\n\nPlease let us know if you have any questions or concerns!\n\nBest Regards,\nThe Team\nemail.net\n\n--------------------------------\nDO NOT REPLY TO THIS EMAIL - Your reply will not be received. If you require assistance, please contact us here: ".href('contact-page');
      $headers = "From: The Team <noreply@email.net>";
      mail('$user_info['user_email']', $subject, $message, $headers);

  } else {

    // ADD NOTE
    $note = "Ongoing support renewed although support was cancelled: 2checkout $sale_id";
    mysql_query("INSERT INTO user_notes (user_note_user_id, user_note_text) VALUES ('{$user_info['user_id']}', '{$note}')");

  }

// SEND EMAIL IF THERE WAS AN ERROR
if($is_error) {

  // SEND EMAIL ABOUT PAYMENT
  mail("2checkout@email.com, matt@email.com, richard@email.com", "2Checkout Order Error", "There was an error (2checkout_ins.php) for the 2checkout transaction {$sale_id} : {$invoice_id} :\n\n".implode("\n\n", $error), "From: The Team <noreply@email.net>");

}


?>