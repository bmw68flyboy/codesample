<?php
$page = "siteadmin_trials_cron";
include "siteadmin_header.php";
include_once "misc/swiftmailer/lib/swift_required.php";
include_once "misc/swiftmailer/lib/SmtpApiHeader.php";

if(isset($_GET['return'])) { $return = (bool) $_GET['return'];  } else { $return = false; }
if(isset($_GET['output'])) { $output = (bool) $_GET['output'];  } else { $output = false;  }
if(isset($_GET['test']))   { $test   = (bool) $_GET['test'];    } else { $test   = false;  }
if(isset($_GET['nomail'])) { $nomail = (bool) $_GET['no-mail']; } else { $nomail = false;  }

$sendgrid_password_query = mysql_fetch_row(mysql_query("SELECT sendgrid_username, sendgrid_password FROM sendgrid WHERE sendgrid_id = '1'"));
$sendgrid_username = $sendgrid_password_query[0];
$sendgrid_password = $sendgrid_password_query[1];

if( $output ) {
	echo "<pre>";
}

set_time_limit(0);

$pageStart = microtime(true);


// Ignore users that purchased the trial a long time ago
$start = time() - (62 * 86400);
$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 20 WHERE `trial_user_id` > 0 && `trial_followup_state` = 0 && `trial_date_download` < %1$d && `trial_date_download` > 0', $start);
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

// Ignore trials that were downloaded a long time ago
$start = time() - (62 * 86400);
$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 21 WHERE `trial_user_id` <= 0 && `trial_followup_state` = 0 && `trial_date_download` < %1$d && `trial_date_download` > 0', $start);
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

// Ignore users that have already purchased (with different error codes?)
$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 11 WHERE `trial_user_id` > 0 && `trial_followup_state` = 0');
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 12 WHERE `trial_user_id` > 0 && `trial_followup_state` = 1');
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 13 WHERE `trial_user_id` > 0 && `trial_followup_state` = 2');
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = 14 WHERE `trial_user_id` > 0 && `trial_followup_state` = 3');
if( $test ) {
	echo $sql . PHP_EOL;
} else {
	if( !mysql_query($sql) && $output ) {
		echo mysql_error() . PHP_EOL;
	}
}

// Get candidates for email 1 - about 7 days after start of trial
$state = 0;
$start = time() - (8 * 86400);
$stop = time() - (7 * 86400);
$sql = sprintf('SELECT * FROM trials WHERE `trial_followup_state` <= %1$d && `trial_date_download` BETWEEN %2$d AND %3$d LIMIT 1000', $state, $start, $stop);
$resource = mysql_query($sql);
if( $test ) {
	echo $sql . PHP_EOL;
}
if( !$resource && $output ) {
	echo mysql_error() . PHP_EOL;
}

$i = 0;
$updateIds = array();
while( ($row = mysql_fetch_assoc($resource)) ) {

	$i++;
	
$hdr = new SmtpApiHeader();

$hdr->setCategory("Reengagement_Trial");

$text = "
Hello,\n\n
	
Thanks for downloading the SocialEngine Trial! \n
We'd love to hear how your trial is going. If you have any questions, please contact us here http://www.socialengine.net/contact.\n
If you're  ready to upgrade, you can purchase a full SocialEngine license here: http://www.socialengine.net/buy-social-engine.\n \n

Best Regards,\n
The SocialEngine Team\n
SocialEngine.net

";
		
	// Send email
	if( $test ) {
		if( $i <= 1 ) {
			echo 'TEST: ' . PHP_EOL;
			echo date('r', $row['trial_date_download']) . PHP_EOL;
			echo $to . PHP_EOL;
			echo $subject . PHP_EOL;
			echo $message . PHP_EOL;
			echo $headers . PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
		}
	} else {
		if( !$nomail ) {
			echo "email to " . $row['trial_email'] . PHP_EOL;
			
			// This is your From email address
			$from = array('noreply@socialengine.net' => 'SocialEngine Team');
			$to = $row['trial_email'];
			
			// Email subject
			$subject = 'Welcome to SocialEngine';
			
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
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			}
			
		} else if( $output || $test ) {
			echo "TEST: email to " . $row['trial_email'] . PHP_EOL;
		}
	}
	// Mark as sent
	$updateIds[] = $row['trial_id'];

	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` = %2$s', 1, (int) $row['trial_id']);
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
// Mark as sent
/*
if( !empty($updateIds) ) {
	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` IN(%2$s)', 1, join(',', $updateIds));
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
*/

if( $output || $test ) {
	echo sprintf('Email #1 - %d records checked, %2$d emails sent' . PHP_EOL, $i, count($updateIds));
}
// Get candidates for email 2 - about 3 days before end of trial
$state = 0;
$start = time() - (28 * 86400);
$stop = time() - (27 * 86400);
$sql = sprintf('SELECT * FROM trials WHERE `trial_followup_state` <= %1$d && `trial_date_download` BETWEEN %2$d AND %3$d LIMIT 1000', $state, $start, $stop);
$resource = mysql_query($sql);
if( $test ) {
	echo $sql . PHP_EOL;
}
if( !$resource && $output ) {
	echo mysql_error() . PHP_EOL;
}

$i = 0;
$updateIds = array();
while( ($row = mysql_fetch_assoc($resource)) ) {
	$i++;

	$name = TrialEmail2;
	$code = random_string(5);
	$date_issue = time();
	$date_expire = time() + (15 * 86400);
	$product_id = 0;
	$discount_percent = 0;
	$discount_dollars = -30;

	mysql_query("INSERT INTO product_coupons (product_coupon_name, product_coupon_code, product_coupon_date_issue, product_coupon_date_expire, product_coupon_product_id, product_coupon_discount_percent, product_coupon_discount_dollars) VALUES ('{$name}', '{$code}', '{$date_issue}', '{$date_expire}', '{$product_id}', '{$discount_percent}', '{$discount_dollars}')");

	// Make email
	
	$hdr = new SmtpApiHeader();

	$hdr->setCategory("Reengagement_Trial");

	$text ="
Hello,\n \n

Thanks for trying out the SocialEngine Trial. Just to let you know, your 30 days are almost up - just three left!\n
If you're ready to upgrade to the full version, you can purchase a license here: http://www.socialengine.net/buy-social-engine. For a limited time, we would like \n
to offer you a special $30 discount if you purchase SocialEngine using this coupon code: $code ! Please note: this coupon is only valid for 15 days.  \n \n

Please note that you can also save an additional $40 if you purchase all plugins. \n \n

Best Regards,\n
The SocialEngine Team\n
SocialEngine.net

";
	
	// Send email
	if( $test ) {
		if( $i <= 1 ) {
			echo 'TEST: ' . PHP_EOL;
			echo date('r', $row['trial_date_download']) . PHP_EOL;
			echo $to . PHP_EOL;
			echo $subject . PHP_EOL;
			echo $message . PHP_EOL;
			echo $headers . PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
		}
	} else {
		if( !$nomail ) {
			echo "email to " . $row['trial_email'] . PHP_EOL;
			
			// This is your From email address
			$from = array('noreply@socialengine.net' => 'SocialEngine Team');
			$to = $row['trial_email'];
			
			// Email subject
			$subject = 'Welcome to SocialEngine';
			
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
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			}
			
		} else if( $output || $test ) {
			echo "TEST: email to " . $row['trial_email'] . PHP_EOL;
		}
	}

	// Mark as sent
	$updateIds[] = $row['trial_id'];

	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` = %2$s', 1, (int) $row['trial_id']);
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}

// Mark as sent
/*
if( !empty($updateIds) ) {
	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` IN(%2$s)', 1, join(',', $updateIds));
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
*/
if( $output || $test ) {
	echo sprintf('Email #2 - %d records checked, %2$d emails sent' . PHP_EOL, $i, count($updateIds));
}

// Get candidates for email 3 - about 7 days before end of trial
$state = 0;
$start = time() - (24 * 86400);
$stop = time() - (23 * 86400);
$sql = sprintf('SELECT * FROM trials WHERE `trial_followup_state` <= %1$d && `trial_date_download` BETWEEN %2$d AND %3$d LIMIT 1000', $state, $start, $stop);
$resource = mysql_query($sql);
if( $test ) {
	echo $sql . PHP_EOL;
}
if( !$resource && $output ) {
	echo mysql_error() . PHP_EOL;
}

$i = 0;
$updateIds = array();
while( ($row = mysql_fetch_assoc($resource)) ) {
	$i++;

	// Make email
	
	$hdr = new SmtpApiHeader();

	$hdr->setCategory("Reengagement_Trial");

	$text ="
Hello, \n \n

Thank you for trying out our SocialEngine trial. Your trial will expire in 7 days; \n
if you have any questions related to installation and features please contact \n
us here: \n
http://www.socialengine.net/contact \n \n

We are eager to hear your feedback and to assist you with any issues you've \n
encountered so far. Here are some installation tips for those of you who have \n 
not yet installed the trial:\n
http://www.socialengine.net/support/documentation/article?q=135&question=Package-and-Plugin-Installation\n \n

Feel free to contact us at any time! If you're ready to upgrade to the full version, you can purchase a license here: http://www.socialengine.net/buy-social-engine.\n \n

Best Regards,\n
The SocialEngine Team\n
SocialEngine.net
";

	// Send email
	if( $test ) {
		if( $i <= 1 ) {
			echo 'TEST: ' . PHP_EOL;
			echo date('r', $row['trial_date_download']) . PHP_EOL;
			echo $to . PHP_EOL;
			echo $subject . PHP_EOL;
			echo $message . PHP_EOL;
			echo $headers . PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
		}
	} else {
		if( !$nomail ) {
			echo "email to " . $row['trial_email'] . PHP_EOL;
			
			// This is your From email address
			$from = array('noreply@socialengine.net' => 'SocialEngine Team');
			$to = $row['trial_email'];
			
			// Email subject
			$subject = 'Welcome to SocialEngine';
			
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
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			}
			
		} else if( $output || $test ) {
			echo "TEST: email to " . $row['trial_email'] . PHP_EOL;
		}
	}

	// Mark as sent
	$updateIds[] = $row['trial_id'];

	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` = %2$s', 1, (int) $row['trial_id']);
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}

// Mark as sent
/*
if( !empty($updateIds) ) {
	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` IN(%2$s)', 1, join(',', $updateIds));
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
*/
if( $output || $test ) {
	echo sprintf('Email #3 - %d records checked, %2$d emails sent' . PHP_EOL, $i, count($updateIds));
}



// Get candidates for email 4 - about 7 days after end of trial
$state = 1;
$start = time() - (37 * 86400);
$stop = time() - (36 * 86400);
$resource = mysql_query(sprintf('SELECT * FROM trials WHERE `trial_followup_state` <= %1$d && `trial_date_download` BETWEEN %2$d AND %3$d LIMIT 1000',
	$state, $start, $stop));
if( !$resource && $output ) {
	echo mysql_error() . PHP_EOL;
}

$i = 0;
$updateIds = array();
while( ($row = mysql_fetch_assoc($resource)) ) {
	$i++;

	// Make email
	
	$hdr = new SmtpApiHeader();

	$hdr->setCategory("Reengagement_Trial");

	$text ="
Hello,\n \n

Thank you for testing out SocialEngine for the last thirty days. Your trial has\n
now ended, but you can continue where you left off by installing the full\n
version of SocialEngine over your trial. If you haven't contacted us\n 
already, please shoot us an email regarding your experience. We appreciate \n
all the responses of our trial users!\n \n

If you are ready to purchase you can do so easily here:\n 
http://www.socialengine.net/buy-social-engine\n \n

Best Regards,\n
The SocialEngine Team\n
SocialEngine.net
";
	
	// Send email
	if( $test ) {
		if( $i <= 1 ) {
			echo 'TEST: ' . PHP_EOL;
			echo date('r', $row['trial_date_download']) . PHP_EOL;
			echo $to . PHP_EOL;
			echo $subject . PHP_EOL;
			echo $message . PHP_EOL;
			echo $headers . PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
		}
	} else {
		if( !$nomail ) {
			echo "email to " . $row['trial_email'] . PHP_EOL;
			
			// This is your From email address
			$from = array('noreply@socialengine.net' => 'SocialEngine Team');
			$to = $row['trial_email'];
			
			// Email subject
			$subject = 'SocialEngine Trial Expired';
			
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
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			}
			
		} else if( $output || $test ) {
			echo "TEST: email to " . $row['trial_email'] . PHP_EOL;
		}
	}

	// Mark as sent
	$updateIds[] = $row['trial_id'];

	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` = %2$s', 2, (int) $row['trial_id']);
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}

// Mark as sent
/*
if( !empty($updateIds) ) {
	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` IN(%2$s)', 2, join(',', $updateIds));
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
*/
if( $output || $test ) {
	echo sprintf('Email #4 - %d records checked, %2$d emails sent' . PHP_EOL, $i, count($updateIds));
}



// Get candidates for email 5 - about 30 after before end of trial
$state = 0;
$start = time() - (60 * 86400);
$stop = time() - (59 * 86400);
$sql = sprintf('SELECT * FROM trials WHERE `trial_followup_state` <= %1$d && `trial_date_download` BETWEEN %2$d AND %3$d LIMIT 1000', $state, $start, $stop);
$resource = mysql_query($sql);
if( $test ) {
	echo $sql . PHP_EOL;
}
if( !$resource && $output ) {
	echo mysql_error() . PHP_EOL;
}

$i = 0;
$updateIds = array();
while( ($row = mysql_fetch_assoc($resource)) ) {
	$i++;

	$name = TrialEmail5;
	$code = random_string(5);
	$date_issue = time();
	$date_expire = time() + (15 * 86400);
	$product_id = 0;
	$discount_percent = -.15;
	$discount_dollars = 0;

	mysql_query("INSERT INTO product_coupons (product_coupon_name, product_coupon_code, product_coupon_date_issue, product_coupon_date_expire, product_coupon_product_id, product_coupon_discount_percent, product_coupon_discount_dollars) VALUES ('{$name}', '{$code}', '{$date_issue}', '{$date_expire}', '{$product_id}', '{$discount_percent}', '{$discount_dollars}')");

	// Make email
	
	$hdr = new SmtpApiHeader();

	$hdr->setCategory("Reengagement_Trial");

	$text ="
Hello,\n \n

It has been thirty days since your SocialEngine trial expired. Your community is still safely stored so you can continue where you left off. Please contact\n 
us with any questions or concerns you may have about moving forward. We'd also like to offer you a 15 percent discount on your entire purchase using this coupon code: $code ! Please note: this coupon is only valid for 15 days, so act now!\n \n

Please note that you can also save an additional $40 if you purchase all plugins. If you are ready to purchase you can do so easily here: \n
http://www.socialengine.net/buy-social-engine\n \n

Best Regards,\n
The SocialEngine Team\n
SocialEngine.net
";

	// Send email
	if( $test ) {
		if( $i <= 1 ) {
			echo 'TEST: ' . PHP_EOL;
			echo date('r', $row['trial_date_download']) . PHP_EOL;
			echo $to . PHP_EOL;
			echo $subject . PHP_EOL;
			echo $message . PHP_EOL;
			echo $headers . PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
		}
	} else {
		if( !$nomail ) {
			echo "email to " . $row['trial_email'] . PHP_EOL;
			
			// This is your From email address
			$from = array('noreply@socialengine.net' => 'SocialEngine Team');
			$to = $row['trial_email'];
			
			// Email subject
			$subject = 'SocialEngine Limited Offer!';
			
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
			// something went wrong =(
			else
			{
			  echo "Something went wrong - ";
			  print_r($failures);
			}
			
		} else if( $output || $test ) {
			echo "TEST: email to " . $row['trial_email'] . PHP_EOL;
		}
	}

	// Mark as sent
	$updateIds[] = $row['trial_id'];

	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` = %2$s', 1, (int) $row['trial_id']);
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}

// Mark as sent
/*
if( !empty($updateIds) ) {
	$sql = sprintf('UPDATE `trials` SET `trial_followup_state` = %1$d WHERE `trial_id` IN(%2$s)', 1, join(',', $updateIds));
	if( $test ) {
		echo "TEST: " . $sql  . PHP_EOL;
	} else {
		if( !mysql_query($sql) && $output ) {
			echo mysql_error() . PHP_EOL;
		}
	}
}
*/
if( $output || $test ) {
	echo sprintf('Email #5 - %d records checked, %2$d emails sent' . PHP_EOL, $i, count($updateIds));
}

$pageStop = microtime(true);
if( $output || $test ) {
	echo ($pageStop - $pageStart) . PHP_EOL . PHP_EOL;
}

if( $return && !$output ) {
  header("Location: siteadmin_trials.php");
  exit();
}
