<?php
$page = "siteadmin_coupon_mobile_cron_email";
include "siteadmin_header.php";
include_once "misc/swiftmailer/lib/swift_required.php";
include_once "misc/swiftmailer/lib/SmtpApiHeader.php";

if(isset($_GET['return'])) { $return = (bool) $_GET['return'];  } else { $return = false; }
if(isset($_GET['test']))   { $test   = (bool) $_GET['test'];    } else { $test   = false;  }
if(isset($_GET['nomail'])) { $nomail = (bool) $_GET['no-mail']; } else { $nomail = false;  }

set_time_limit(0);
$time_end = time()- 345600; 
$range = time() - (90 * 86400);

$pageStart = microtime(true);

// SEARCH FOR NEW RESULTS
$remarketing_email = "SELECT users.user_email FROM users LEFT JOIN user_products ON (user_products.user_product_user_id=users.user_id && user_product_product_id = 18)  WHERE users.user_remarket != '2' AND user_date_signup < $range AND user_notify_newsletter = '1' AND user_product_id IS NULL ORDER BY user_date_signup ASC LIMIT 100";
$result_email = mysql_query($remarketing_email) or die(mysql_error());

//GET SENDGRID LOGIN
$sendgrid_password_query = mysql_fetch_row(mysql_query("SELECT sendgrid_username, sendgrid_password FROM sendgrid WHERE sendgrid_id = '1'"));
$sendgrid_username = $sendgrid_password_query[0];
$sendgrid_password = $sendgrid_password_query[1];

while($row = mysql_fetch_array($result_email)){

			//CREATE COUPON
			$code = random_string(8);
			$name = "MobileEngagement";
			$date_issue = time();
			$date_expire = time() + (15 * 86400);
			$product_id = 18;
			$discount_percent = 0;
			$discount_dollars = -5;
			mysql_query("INSERT INTO product_coupons (product_coupon_name, product_coupon_code, product_coupon_date_issue, product_coupon_date_expire, product_coupon_product_id, product_coupon_discount_percent, product_coupon_discount_dollars) VALUES ('{$name}', '{$code}', '{$date_issue}', '{$date_expire}', '{$product_id}', '{$discount_percent}', '{$discount_dollars}')");
			
			/*
			* Create the body of the message (a plain-text and an HTML version).
			 * $text is your plain-text email
			 * $html is your html version of the email
			 * If the reciever is able to view html emails then only the html
			 * email will be displayed
			 */ 
			$hdr = new SmtpApiHeader();

			$hdr->setCategory("Reengagement_Mobile_Coupon");
			 
			$text = "Hello!\n \n The future is mobile, and optimizing your community for mobile activity is essential as people from all demographics spend more time on mobile devices each year.  http://www.socialengine.net/blog/article?id=228&article=Tips-and-Tutorials-Mobile-Engagement . For a limited time, we'd like to offer you a $5 discount on your Mobile plugin purchase (use code $code ). Our representatives are ready to help, so please let us know if you have any questions!\n \n \n Best regards, \n The SocialEngine Team \n SocialEngine.net \n \n";
			$html = <<<EOM
			<html>
			  <head></head>
			  <body>
				<p>Hello!<br><br>
				   The future is mobile, and optimizing your community for mobile activity is essential as people from all demographics spend more time on mobile devices each year. http://www.socialengine.net/blog/article?id=228&article=Tips-and-Tutorials-Mobile-Engagement . For a limited time, we'd like to offer you a $5 discount on your Mobile plugin purchase (use code $code ). Our representatives are ready to help, so please let us know if you have any questions!<br><br>
				   Best Regards,<br>
				   The Team<br>
				   email.net<br>
				</p>
			  </body>
			</html>
EOM;

			// This is your From email address
			$from = array('info@email.net' => 'Team');
			$to = $row[user_email];
			
			// Email subject
			$subject = 'Mobile Limited Time Offer';
			
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
			//MARK EMAIL AS REMARKETED TO
			mysql_query("UPDATE users SET user_remarket='2' WHERE user_email='{$row['user_email']}'");
			}
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			  mysql_query("UPDATE users SET user_remarket='2' WHERE user_email='{$row['user_email']}'");
			}
					
}
		
$pageStop = microtime(true);
if( $output || $test ) {
	echo ($pageStop - $pageStart) . PHP_EOL . PHP_EOL;
}

if( $return && !$output ) {
  header("Location: siteadmin_emails.php");
  exit();
}
