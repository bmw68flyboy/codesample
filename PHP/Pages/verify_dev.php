<?php
include_once "misc/swiftmailer/lib/swift_required.php";
include_once "misc/swiftmailer/lib/SmtpApiHeader.php";

if(!defined('SE_PAGE')) { header("Location: ".href()); exit(); }
if(!defined('SE_PAGE')) { header("Location: ".href()); exit(); }

// GET VERIFIED EMAIL VARS 
$is_error = false;
$errors = array();
$hash = $_GET['verify_dev'];
$dev_id = $_GET['dev_id'];
$dev_title = $_GET['dev'];

//GET SENDGRID VARS
$sendgrid_query = mysql_fetch_row(mysql_query("SELECT sendgrid_username, sendgrid_password FROM sendgrid WHERE sendgrid_id = '1'"));
$sendgrid_username = $sendgrid_query[0];
$sendgrid_password = $sendgrid_query[1];

// CHECK IF REVIEW HAS BEEN VERIFIED
$dev_verified_query = mysql_query("SELECT user_developer_review_verified, user_developer_review_user_id, user_developer_review_title, user_developer_review_text FROM user_developer_reviews WHERE user_developer_review_hash='{$hash}' AND user_developer_review_developer_id='{$dev_id}'");
$verified = mysql_fetch_assoc($dev_verified_query);
$dev_verified = $verified["user_developer_review_verified"];
$dev_review_title = $verified["user_developer_review_title"];
$dev_review_text = $verified["user_developer_review_text"];
$reviewer_id = $verified["user_developer_review_user_id"];

$reviewer_query = mysql_query("SELECT user_fname, user_lname FROM users WHERE user_id='{$reviewer_id}'");
$reviewer = mysql_fetch_assoc($reviewer_query);
$reviewer_fname = $reviewer["user_fname"];
$reviewer_lname = $reviewer["user_lname"];

// VERIFY REVIEW AND EMAIL DEVELOPER
if ($dev_verified == 0){
mysql_query("UPDATE user_developer_reviews SET user_developer_review_verified='1' WHERE user_developer_review_hash='{$hash}' AND user_developer_review_developer_id='{$dev_id}'");
$dev_email_query = mysql_query("SELECT * FROM user_developers WHERE user_id='{$dev_id}'");
$update = mysql_fetch_assoc($dev_email_query);
$dev_email = $update["email"];
$notify = $update["notify_review"];

if($notify == 1) {
//$subject = "Your SocialEngine Developer Profile Has Been Rated";
$hdr = new SmtpApiHeader();
$hdr->setCategory("Community_Review");
$message = "Hello,\n\nYour developer profile has just been rated and reviewed by $reviewer_fname $reviewer_lname[0]. ! Your review: $dev_review_title : $dev_review_text . To fully read the review, please click the following link:\n\n".href('community-dev-profile', 'dev_id='.$dev_id."&dev=".ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $dev_title)).'#reviews')."\n\nBest Regards,\nThe SocialEngine Team\nSocialEngine.net\n\n--------------------------------\nDO NOT REPLY TO THIS EMAIL - Your reply will not be received. If you require assistance, please contact us here: ".href('contact-page');
$text = $message;
//$headers = "From: The SocialEngine Team <noreply@socialengine.net>";
//mail($dev_email, $subject, $message, $headers);

// This is your From email address
		$from = array('noreply@socialengine.net' => 'The SocialEngine Team');
		$to = $dev_email;
			
		// Email subject
		$subject = 'Your SocialEngine Developer Profile Has Been Rated';
			
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
			
				<h3>To view your review click here: <a href="http://www.socialengine.net/customize/developer?dev_id=<? echo $dev_id?>&dev=<?echo ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $dev_title))?>#reviews">Read Review</a><br><br>
				To update your review vist the review page here: <a href="http://www.socialengine.net/customize/write-review?dev_id=<? echo $dev_id?>&dev=<?echo ereg_replace("[^A-Za-z0-9-]", "", str_replace(" ", "-", $dev_title))?>">Update Review</a> </h3>

				<p><a href='<?=href('')?>'><b>&raquo; Back to SocialEngine Home</b></a></p>
				
				<img src="https://www.emjcd.com/u?CID=1521612&OID=<?php echo $order_number?>&TYPE=347820&ITEM1=SocialEngineCJ&AMT1=<?php echo $total?>&QTY1=1&CURRENCY=USD&METHOD=IMG" height="1" width="20">
		
		</div>

	</div>

<?php
  /* Footer */ 
  include SE_ROOT."/view/_templates/footer_tpl.php";
?>
