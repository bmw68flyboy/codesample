<?php
include_once "misc/swiftmailer/lib/swift_required.php";
include_once "misc/swiftmailer/lib/SmtpApiHeader.php";


if(!defined('SE_PAGE')) { header("Location: ".href()); exit(); }
if(!defined('SE_PAGE')) { header("Location: ".href()); exit(); }

// GET VERIFIED EMAIL VARS 
$is_error = false;
$errors = array();
$hash = $_GET['verify'];
$mod_id = $_GET['mod_id'];
$mod_title = $_GET['mod'];

//GET SENDGRID VARS
$sendgrid_query = mysql_fetch_row(mysql_query("SELECT sendgrid_username, sendgrid_password FROM sendgrid WHERE sendgrid_id = '1'"));
$sendgrid_username = $sendgrid_query[0];
$sendgrid_password = $sendgrid_query[1];

// CHECK IF REVIEW HAS BEEN VERIFIED
$mod_verified_query = mysql_query("SELECT user_mod_review_verified, user_mod_review_user_id, user_mod_review_title, user_mod_review_text FROM user_mod_reviews WHERE user_mod_review_hash='{$hash}' AND user_mod_review_mod_id ='{$mod_id}'");
$verified = mysql_fetch_assoc($mod_verified_query);
$mod_verified = $verified["user_mod_review_verified"];
$mod_review_title = $verified["user_mod_review_title"];
$mod_review_text = $verified["user_mod_review_text"];
$reviewer_id = $verified["user_mod_review_user_id"];

$reviewer_query = mysql_query("SELECT user_fname, user_lname FROM users WHERE user_id='{$reviewer_id}'");
$reviewer = mysql_fetch_assoc($reviewer_query);
$reviewer_fname = $reviewer["user_fname"];
$reviewer_lname = $reviewer["user_lname"];

// VERIFY REVIEW AND EMAIL DEVELOPER
if ($mod_verified == 0){
mysql_query("UPDATE user_mod_reviews SET user_mod_review_verified='1' WHERE user_mod_review_hash='{$hash}' AND user_mod_review_mod_id ='{$mod_id}'");
$mod_user_id_query = mysql_query("SELECT user_mod_user_id FROM user_mods WHERE user_mod_id='{$mod_id}'");
$update = mysql_fetch_assoc($mod_user_id_query);
$mod_user_id = $update["user_mod_user_id"];
$dev_email_query = mysql_query("SELECT * FROM user_developers WHERE user_id='{$mod_user_id}'");
$update = mysql_fetch_assoc($dev_email_query);
$dev_email = $update["email"];
$notify = $update["notify_review"];

if($notify == 1) {
//$subject = "Your SocialEngine Mod Has Been Rated";
$hdr = new SmtpApiHeader();
$hdr->setCategory("Community_Review");
$message = "Hello,\n\nYour mod $mod_title has just been rated and reviewed by $reviewer_fname $reviewer_lname[0]. ! Your review: $mod_review_title : $mod_review_text . To read the review, please click the following link:\n\n".href('community-mod-page', 'mod_id='.$mod_id."&mod=".ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $mod_title)).'#reviews')."\n\nBest Regards,\nThe SocialEngine Team\nSocialEngine.net\n\n--------------------------------\nDO NOT REPLY TO THIS EMAIL - Your reply will not be received. If you require assistance, please contact us here: ".href('contact-page');
$text = $message;
//$headers = "From: The SocialEngine Team <noreply@socialengine.net>";
//mail($dev_email, $subject, $message, $headers);

// This is your From email address
		$from = array('noreply@socialengine.net' => 'The SocialEngine Team');
		$to = $dev_email;
			
		// Email subject
		$subject = 'Your SocialEngine Mod Has Been Rated';
			
		// Login credentials
		$username = $sendgrid_username;
	    $password = $sendgrid_password;
			
		// Setup Swift mailer parameters
		$transport = Swift_SmtpTransport::newInstance('smtp.sendgrid.net', 587);
		$transport->setUsername($username);
		$transport->setPassword($password);
		$swift = Swift_Mailer::newInstance($transport);
			
		// Create a message (subject)
		$message = new Swift_Message($subject);
		
		$headers = $message->getHeaders();
		$headers->addTextHeader('X-SMTPAPI', $hdr->asJSON());
			 
		// attach the body of the email
		$message->setFrom($from);
		$message->setBody($html, 'text/html');
		$message->setTo($to);
		$message->addPart($text, 'text/plain');
			
			
		// send message 
		if ($recipients = $swift->send($message, $failures))
		{
		}

}
}

  /* HEADER */ 
  include SE_ROOT."/view/_templates/header_tpl.php";

?>

	<div class="s-main-heading-wrapper">
		<div class="s-main-heading"></div>
		<div class="clear"></div>
	</div>

	<div class="wrapper-top-bg-solid "></div>

	<div class="s-wrapper-main-solid">
		
		<div class="f-content-wrapper">
			<div class="f-content thank-you-content">
				<img src="/view/images/green_check.png" alt="Thank you">
				<h2>Thanks, for your review!</h2>
			
				<h3>To view your review click here: <a href="http://www.socialengine.net/customize/mod-page?mod_id=<? echo $mod_id?>&mod=<?echo ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $mod_title))?>#reviews">Read Review</a><br><br>
				To update your review vist the review page here: <a href="http://www.socialengine.net/customize/write-review?mod_id=<? echo $mod_id?>&mod=<?echo ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $mod_title))?>">Update Review</a> </h3>

				<p><a href='<?=href('')?>'><b>&raquo; Back to SocialEngine Home</b></a></p>
				
				<img src="https://www.emjcd.com/u?CID=1521612&OID=<?php echo $order_number?>&TYPE=347820&ITEM1=SocialEngineCJ&AMT1=<?php echo $total?>&QTY1=1&CURRENCY=USD&METHOD=IMG" height="1" width="20">
		
		</div>

	</div>

<?php
  /* Footer */ 
  include SE_ROOT."/view/_templates/footer_tpl.php";
?>
