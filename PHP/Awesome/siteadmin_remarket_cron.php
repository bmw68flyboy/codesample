<?php
$page = "siteadmin_remarket_cron";
include "siteadmin_header.php";

if(isset($_GET['return'])) { $return = (bool) $_GET['return'];  } else { $return = false; }
if(isset($_GET['test']))   { $test   = (bool) $_GET['test'];    } else { $test   = false;  }
if(isset($_GET['nomail'])) { $nomail = (bool) $_GET['no-mail']; } else { $nomail = false;  }

set_time_limit(0);
$time_end = time()- 345600; 

$pageStart = microtime(true);

// TRUNCATE DATA IN TABLE 
$delete = "TRUNCATE TABLE `email_remarketing`";
mysql_query($delete);

// SEARCH FOR NEW RESULTS
$remarketing_query = "SELECT emails.email_ref_id, emails.email_address, email_posts.email_post_date, emails.email_spam, emails.email_flagged, emails.email_archive, emails.email_employee, emails.email_answered, emails.email_remarket, email_posts.email_post_address, email_posts.email_post_body, emails.email_date FROM emails LEFT JOIN email_posts ON emails.email_ref_id=email_posts.email_post_ref_id LEFT JOIN users ON users.user_email=emails.email_address WHERE email_post_address='SocialEngine Team <info@socialengine.net>' AND email_posts.email_post_date < {$time_end} AND emails.email_answered='1' AND emails.email_spam='0' AND emails.email_remarket='0' AND users.user_email IS NULL GROUP BY emails.email_address HAVING COUNT(*) = 1 ORDER BY email_posts.email_post_date DESC";
$result = mysql_query($remarketing_query) or die(mysql_error());

while($row = mysql_fetch_array($result)){
	$insert = "INSERT INTO email_remarketing (email_ref_id, email_address, email_date, email_post_date, email_spam, email_flagged, email_archive, email_employee, email_answered, email_remarket, email_post_address, email_post_body) VALUES ('$row[email_ref_id]', '$row[email_address]', '$row[email_date]', '$row[email_post_date]', '$row[email_spam]', '$row[email_flagged]', '$row[email_archive]', '$row[email_employee]', '$row[email_answered]', '$row[email_remarket]', '$row[email_post_address]', '$row[email_post_body]')";
	mysql_query($insert) OR die(mysql_error());	
}

$pageStop = microtime(true);
if( $output || $test ) {
	echo ($pageStop - $pageStart) . PHP_EOL . PHP_EOL;
}

if( $return && !$output ) {
  header("Location: siteadmin_emails.php");
  exit();
}
