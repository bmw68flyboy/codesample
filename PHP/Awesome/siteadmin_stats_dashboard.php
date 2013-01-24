<?php
$page = "siteadmin_stats_dashboard";
include "siteadmin_header.php";

if(isset($_GET['m'])) { $m = (int)$_GET['m']; } else { $m = date('n'); }
if(isset($_GET['y'])) { $y = (int)$_GET['y']; } else { $y = date('Y'); }


// GET ALL PRODUCTS
$products = array();
$product_query = mysql_query("SELECT * FROM products ORDER BY product_id ASC");
while($product_info = mysql_fetch_assoc($product_query)) { $products[] = $product_info; }


// GET MONTH
$month_start = mktime(0, 0, 0, $m, 1, $y);
$month_end = mktime(0, 0, 0, $m+1, 1, $y);
$monthName= date("F", $month_start);
$dayOfMonth = date("j", $moth_start);
$dailyData = Array();

// GET TOTAL DAILY SALES
$sales = mysql_query("SELECT SUM(user_product_price) AS daily_sum, DAY(FROM_UNIXTIME(user_product_date_purchase)) AS day FROM user_products WHERE user_product_cancelled = '0'AND user_product_status = '1' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY DATE(FROM_UNIXTIME(user_product_date_purchase))");
while($sale = mysql_fetch_assoc($sales)) { $dailyData[$sale['day']]['salestotal'] = (int)$sale['daily_sum']; }

// GET DAILY SOCIALMEDIA POSTS
	//FACEBOOK POSTS
	$facebook_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='Facebook' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($facebook_post = mysql_fetch_assoc($facebook_post_query )) { $dailyData[$facebook_post['day']]['facebook_url'] = $facebook_post['socialmedia_post_url']; }

	//TWITTER POSTS
	$twitter_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='Twitter' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($twitter_post = mysql_fetch_assoc($twitter_post_query )) { $dailyData[$twitter_post['day']]['twitter_url'] = $twitter_post['socialmedia_post_url']; }
	
	//LINKEDIN POSTS
	$linkedin_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='LinkedIn' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($linkedin_post = mysql_fetch_assoc($linkedin_post_query )) { $dailyData[$linkedin_post['day']]['linkedin_url'] = $linkedin_post['socialmedia_post_url']; }
	
	//GOOGLEPLUS POSTS
	$googleplus_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='Google Plus' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($googleplus_post = mysql_fetch_assoc($googleplus_post_query )) { $dailyData[$googleplus_post['day']]['googleplus_url'] = $googleplus_post['socialmedia_post_url']; }
	
	//BLOG POSTS
	$blog_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='Blog' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($blog_post = mysql_fetch_assoc($blog_post_query )) { $dailyData[$blog_post['day']]['blog_url'] = $blog_post['socialmedia_post_url']; }
	
	//NEWSLETTER POSTS
	$newsletter_post_query = mysql_query("SELECT socialmedia_post_url, DAY(FROM_UNIXTIME(socialmedia_post_date)) AS day FROM socialmedia_posts WHERE socialmedia_post_type='Newsletter' AND socialmedia_post_date >= '{$month_start}' AND socialmedia_post_date < '{$month_end}' ORDER BY socialmedia_post_date DESC");
	while($newsletter_post = mysql_fetch_assoc($newsletter_post_query )) { $dailyData[$newsletter_post['day']]['newsletter_url'] = $newsletter_post['socialmedia_post_url']; }
	
// GET TOTAL GOOD TRANSACTIONS
$trans = mysql_query("SELECT count(DISTINCT user_product_date_purchase) AS total_transactions, DAY(FROM_UNIXTIME(user_product_date_purchase)) AS day FROM user_products WHERE user_product_cancelled='0' AND user_product_status='1' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY DATE(FROM_UNIXTIME(user_product_date_purchase))");
while($tran = mysql_fetch_assoc($trans)) { $dailyData[$tran['day']]['ordercount']['good'] = (int)$tran['total_transactions']; }

// GET TOTAL CANCELLED TRANSACTIONS
$trans = mysql_query("SELECT count(DISTINCT user_product_date_purchase) AS total_transactions, DAY(FROM_UNIXTIME(user_product_date_purchase)) AS day FROM user_products WHERE user_product_cancelled='1' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY DATE(FROM_UNIXTIME(user_product_date_purchase))");
while($tran = mysql_fetch_assoc($trans)) { $dailyData[$tran['day']]['ordercount']['cancelled'] = (int)$tran['total_transactions']; }

// GET PRODUCT ROWS
$discount = mysql_fetch_assoc(mysql_query("SELECT p.* FROM (SELECT product_title, product_type, product_download, product_price, max(product_version) AS max_version FROM products WHERE product_type = 'discount' GROUP BY product_type) AS x INNER JOIN products AS p ON p.product_type=x.product_type AND p.product_version=x.max_version LIMIT 1"));
$license = mysql_fetch_assoc(mysql_query("SELECT p.* FROM (SELECT product_title, product_type, product_download, product_price, max(product_version) AS max_version FROM products WHERE product_type = 'license' GROUP BY product_type) AS x INNER JOIN products AS p ON p.product_type=x.product_type AND p.product_version=x.max_version LIMIT 1"));
$support = mysql_fetch_assoc(mysql_query("SELECT p.* FROM (SELECT product_title, product_type, product_download, product_price, max(product_version) AS max_version FROM products WHERE product_type = 'support' GROUP BY product_type) AS x INNER JOIN products AS p ON p.product_type=x.product_type AND p.product_version=x.max_version LIMIT 1"));
$install = mysql_fetch_assoc(mysql_query("SELECT p.* FROM (SELECT product_title, product_type, product_download, product_price, max(product_version) AS max_version FROM products WHERE product_type = 'install' GROUP BY product_type) AS x INNER JOIN products AS p ON p.product_type=x.product_type AND p.product_version=x.max_version LIMIT 1"));
$upgrade = mysql_fetch_assoc(mysql_query("SELECT p.* FROM (SELECT product_title, product_type, product_download, product_price, max(product_version) AS max_version FROM products WHERE product_type = 'upgrade' GROUP BY product_type) AS x INNER JOIN products AS p ON p.product_type=x.product_type AND p.product_version=x.max_version LIMIT 1"));

// GET TOTAL ONLY SE TRANSACTIONS


// GET TOTAL INCLUDE SE TRANSACTIONS
$trans = mysql_query("SELECT count(DISTINCT user_product_date_purchase) AS total, DAY(FROM_UNIXTIME(user_product_date_purchase)) AS day FROM user_products WHERE user_product_product_id='{$license['product_id']}' AND user_product_cancelled='0' AND user_product_status='1' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY DATE(FROM_UNIXTIME(user_product_date_purchase))");
while($tran = mysql_fetch_assoc($trans)) { $dailyData[$tran['day']]['totals']['license'] = (int)$tran['total']; }


// GET TOTAL ONLY PLUGIN TRANSACTIONS


// GET TOTAL SUPPORT TRANSACTIONS
$trans = mysql_query("SELECT count(DISTINCT user_product_date_purchase) AS total, DAY(FROM_UNIXTIME(user_product_date_purchase)) AS day FROM user_products WHERE user_product_product_id='{$support['product_id']}' AND user_product_cancelled='0' AND user_product_status='1' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY DATE(FROM_UNIXTIME(user_product_date_purchase))");
while($tran = mysql_fetch_assoc($trans)) { $dailyData[$tran['day']]['totals']['support'] = (int)$tran['total']; }


// GET SOURCES
$sources = array();
$source_query = mysql_query("SELECT count(user_id) AS total_source, user_source FROM users WHERE user_source<>'' AND user_date_signup >= '{$month_start}' AND user_date_signup < '{$month_end}' GROUP BY user_source ORDER BY total_source DESC");
while($source = mysql_fetch_assoc($source_query)) { $sources[] = $source; }

$sources_sum = array();
$source_sum_query = mysql_query("SELECT count(user_id) AS total_source, SUM(user_products.user_product_price) AS sum_source FROM users LEFT JOIN user_products ON users.user_id = user_products.user_product_user_id WHERE user_source<>'' AND user_date_signup >= '{$month_start}' AND user_date_signup < '{$month_end}' GROUP BY user_source ORDER BY total_source DESC");
while($source_sum = mysql_fetch_assoc($source_sum_query)) { $sources_sum[] = $source_sum; }

// GET COUPONS
$sources_coupon = array();
$source_coupon_query = mysql_query("SELECT count(user_product_id) AS total_source_coupon, user_product_extra FROM user_products WHERE user_product_extra<>'' AND user_product_date_purchase >= '{$month_start}' AND user_product_date_purchase < '{$month_end}' GROUP BY user_product_extra ORDER BY total_source_coupon DESC");
while($source_coupon = mysql_fetch_assoc($source_coupon_query)) { $sources_coupon[] = $source_coupon; }

// GET KEYWORDS
$sources_keyword = array();
$source_keyword_query = mysql_query("SELECT count(user_id) AS total_source_keyword, user_keyword FROM users WHERE user_keyword<>'' AND user_date_signup >= '{$month_start}' AND user_date_signup < '{$month_end}' GROUP BY user_keyword ORDER BY total_source_keyword DESC");
while($source_keyword = mysql_fetch_assoc($source_keyword_query)) { $sources_keyword[] = $source_keyword; }

// GET TICKETS
$source_ticket_open_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_open = '1' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$ticket_open_result = mysql_fetch_array($source_ticket_open_query);
$num_ticket_open = $ticket_open_result['COUNT(ticket_id)'];

$source_ticket_flagged_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_flagged = '1' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$num_ticket_flagged = mysql_fetch_array($source_ticket_flagged_query);

$source_ticket_install_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_install = '1' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$num_ticket_install = mysql_fetch_array($source_ticket_install_query);

$source_ticket_answered_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_answered = '1' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$num_ticket_answered = mysql_fetch_array($source_ticket_answered_query);

$source_ticket_read_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_read = '1' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$num_ticket_read = mysql_fetch_array($source_ticket_read_query);

$source_ticket_closed_query = mysql_query("SELECT COUNT(ticket_id) FROM tickets WHERE ticket_date_closed != '0' AND ticket_date_created >= '$month_start' AND ticket_date_created < '$month_end'");
$num_ticket_closed = mysql_fetch_array($source_ticket_closed_query);

// GET DETAIL SALES
$product_sales_query = mysql_query("SELECT user_products.user_product_product_id, SUM(user_products.user_product_price), products.product_type FROM user_products LEFT JOIN products ON user_products.user_product_product_id = products.product_id  WHERE user_product_date_purchase >= '$month_start' AND user_product_date_purchase < '$month_end' AND user_product_cancelled = '0' GROUP BY user_product_product_id ORDER BY SUM(user_products.user_product_price) DESC");
$support_cost_query = mysql_query("SELECT SUM(user_products.user_product_price) FROM user_products LEFT JOIN products ON user_products.user_product_product_id = products.product_id  WHERE user_product_date_purchase >= '$month_start' AND user_product_date_purchase < '$month_end' AND user_product_cancelled = '0' AND product_type = 'support'");
$support_cost_result = mysql_fetch_array($support_cost_query);
$support_cost = $support_cost_result['SUM(user_products.user_product_price)'];
$install_cost_query = mysql_query("SELECT SUM(user_products.user_product_price) FROM user_products LEFT JOIN products ON user_products.user_product_product_id = products.product_id  WHERE user_product_date_purchase >= '$month_start' AND user_product_date_purchase < '$month_end' AND user_product_cancelled = '0' AND product_type = 'install'");
$install_cost_result = mysql_fetch_array($install_cost_query);
$install_cost = $install_cost_result['SUM(user_products.user_product_price)'];
$upgrade_cost_query = mysql_query("SELECT SUM(user_products.user_product_price) FROM user_products LEFT JOIN products ON user_products.user_product_product_id = products.product_id  WHERE user_product_date_purchase >= '$month_start' AND user_product_date_purchase < '$month_end' AND user_product_cancelled = '0' AND product_type = 'upgrade'");
$upgrade_cost_result = mysql_fetch_array($upgrade_cost_query);
$upgrade_cost = $upgrade_cost_result['SUM(user_products.user_product_price)'];

// GET REMARKETIG SALES
$remarketing_sales_total_query = mysql_query("SELECT * FROM users LEFT JOIN emails ON users.user_email = emails.email_address LEFT JOIN user_products ON users.user_id =user_products.user_product_user_id WHERE emails.email_remarket=1 AND user_products.user_product_date_purchase >= '$month_start' AND user_products.user_product_date_purchase < '$month_end' GROUP BY users.user_id"); 
$remarketing_sales_total = mysql_num_rows($remarketing_sales_total_query);

$remarketing_emails_total_query = mysql_query("SELECT COUNT(*) FROM emails WHERE email_remarket='1' AND email_date >='$month_start' AND email_date < '$month_end'"); 
$remarketing_emails_total_reult = mysql_fetch_array($remarketing_emails_total_query);
$remarketing_emails_total = $remarketing_emails_total_reult['COUNT(*)'];

$remarketing_sales_query = mysql_query("SELECT SUM(user_products.user_product_price) FROM users LEFT JOIN emails ON users.user_email = emails.email_address LEFT JOIN user_products ON users.user_id =user_products.user_product_user_id WHERE emails.email_remarket=1 AND user_products.user_product_date_purchase >= '$month_start' AND user_products.user_product_date_purchase < '$month_end'");
$remarketing_sales_result = mysql_fetch_array($remarketing_sales_query);
$remarketing_sales = $remarketing_sales_result['SUM(user_products.user_product_price)'];

// GET TICKET TIME
$ticket_views_time_query = mysql_query("SELECT SUM(ticket_view_session_delta) FROM ticket_view_sessions WHERE ticket_view_session_start >= $month_start AND ticket_view_session_start < $month_end AND ticket_view_session_ticket_post_time = '0' AND ticket_view_session_delta > '0'");
$ticket_views_result = mysql_fetch_array($ticket_views_time_query);
$ticket_views_time = ($ticket_views_result['SUM(ticket_view_session_delta)'])/3600;

$ticket_replies_time_query = mysql_query("SELECT SUM(ticket_view_session_ticket_post_time), SUM(ticket_view_session_start), SUM(ticket_view_session_penalty)  FROM ticket_view_sessions WHERE ticket_view_session_start >= $month_start AND ticket_view_session_start < $month_end AND ticket_view_session_ticket_post_time != '0'");
$ticket_replies_result = mysql_fetch_array($ticket_replies_time_query);
$ticket_replies_post_time = ($ticket_replies_result['SUM(ticket_view_session_ticket_post_time)'])/3600;
$ticket_replies_start = ($ticket_replies_result['SUM(ticket_view_session_start)'])/3600;
$ticket_replies_penalty = ($ticket_replies_result['SUM(ticket_view_session_penalty)'])/3600;
$ticket_replies_time = $ticket_replies_post_time - $ticket_replies_start - $ticket_replies_penalty;

$ticket_total_time = $ticket_views_time + $ticket_replies_time;

$ticket_hour = round(($support_cost+$install_cost+$upgrade_cost)/$ticket_total_time, 2);
$ticket_cost = round(($support_cost+$install_cost+$upgrade_cost)/$num_ticket_open, 2);

//GET PERCENTAGE TOTAL USERS OPEN TICKET
$user_opened_ticket_query = mysql_query("SELECT ticket_user_id AS total_clients FROM tickets WHERE ticket_date_created > $month_start AND ticket_date_created <= $month_end GROUP BY ticket_user_id");
$user_opened_ticket = mysql_fetch_array($user_opened_ticket_query);
$num_user_opened = mysql_num_rows($user_opened_ticket_query);

$user_opened_ticket_all_query = mysql_query("SELECT ticket_user_id AS total_clients FROM tickets GROUP BY ticket_user_id");
$user_opened_ticket_all = mysql_fetch_array($user_opened_ticket_all_query);
$num_user_opened_all = mysql_num_rows($user_opened_ticket_all_query);

$total_users_query = mysql_query("SELECT COUNT(*) FROM users WHERE user_enabled = '1'");
$total_users = mysql_fetch_array($total_users_query);

$percentage_clients = ($num_user_opened/$total_users[0])*100;
$percentage_clients_all = ($num_user_opened_all/$total_users[0])*100;

// GET AFFILIATE SALES
$affs = array();
$aff_query = mysql_query("SELECT affiliates.*, affiliate_orders.affiliate_order_status, affiliate_orders.affiliate_order_cancelled, count(affiliate_order_id) AS total_sales FROM affiliate_orders LEFT JOIN affiliates ON affiliate_orders.affiliate_order_affiliate_id=affiliates.affiliate_id WHERE affiliate_order_date >= '{$month_start}' AND affiliate_order_date <= '{$month_end}' GROUP BY affiliate_order_affiliate_id ORDER BY total_sales DESC");
while($aff = mysql_fetch_assoc($aff_query)) { $affs[] = $aff; }

// GET RESELLERS
$reseller = mysql_fetch_assoc(mysql_query("SELECT count(user_license_id) AS total FROM user_licenses LEFT JOIN users ON user_licenses.user_license_user_id=users.user_id WHERE user_reseller='1' AND user_license_date_purchase >= '{$month_start}' AND user_license_date_purchase < '{$month_end}' AND user_license_cancelled='0'"));


// AGGREGATE DERIVED CALCULATIONS
$earlyInDayAndMonth = (date("G") < 16 && date("j") < 10 && date("n") == $m && date("Y") == $y);
$salesSum = array_reduce($dailyData, create_function('$v,$w', 'return $v+$w[\'salestotal\'];'), 0);
$orderSum = array_reduce($dailyData, create_function('$v,$w', 'return $v+$w[\'ordercount\'][\'good\'];'), 0);
$orderCancelledSum = array_reduce($dailyData, create_function('$v,$w', 'return $v+$w[\'ordercount\'][\'cancelled\'];'), 0);
$orderWithSESum = array_reduce($dailyData, create_function('$v,$w', 'return $v+$w[\'totals\'][\'license\'];'), 0);

// AVERAGES
$avgDailySales = round($salesSum/count($dailyData), 0);
$avgDailyOrders = round($orderSum/count($dailyData));
if(date("n") == $m && date("Y") == $y) {
  $projectedSales = $salesSum + ((date("t") - date("j")) * $avgDailySales);
  $confidence = round(date("j")/date("t")*100, 0);
} else {
  $projectedSales = $salesSum;
  $confidence = 100;
}

// LOOP OVER ARRAY
$salesSumsX = array();
$salesSumsY = array();
foreach($dailyData as $dayNum=>$dayStats) {
  $salesSumsX[] = $dayNum;
  $salesSumsY[] = $salesSumsY[count($salesSumsY)-1]+$dayStats['salestotal'];
}

// LINEAR REGRESSION
if(date("n") == $m && date("Y") == $y) {
  $regression = linear_regression($salesSumsX, $salesSumsY);
  $linearProjection = round($regression['m'] * date("t") + $regression['b']);
} else {
  $linearProjection = $salesSum;
}

/*

   $orderWithSESum=0;
   $orderPluginOnlySum=0;
   $orderSupportSum=0;
   $orderSEOnlySum=0;
   $paypalOrdersSum=0;
   $_2checkoutOrdersSum=0;
   
   $blogsOrderSum=0;
   $albumsOrderSum=0;
   $eventsOrderSum=0;
   $chatOrderSum=0;
   $classifiedsOrderSum=0;
   $pollsOrderSum=0;
   $musicOrderSum=0;
   $videoOrderSum=0;
   $forumOrderSum=0;
   
   $salesSumsX = Array();
   $salesSumsY = Array();

   $reversedArray = array_reverse($dailyData, TRUE);
   foreach ($reversedArray as $dayNum=>$dayStats) {
      $salesSum += $dayStats['salestotal'];
      $orderSum += $dayStats['ordercount'];
      $orderWithSESum += $dayStats['orderswithse'];
      $orderPluginOnlySum += $dayStats['orderswithpluginsonly'];
      $orderSEOnlySum += $dayStats['orderswithseonly'];
      $orderCancelledSum += $dayStats['cancelledorders'];
      $orderSupportSum += $dayStats['extendedsupportorders'];
	  $paypalOrdersSum += $dayStats['paypalorders'];
      $_2checkoutOrdersSum += $dayStats['2checkoutorders'];
      // For the linear regression, we need a series of (x,y) points      
      $salesSumsX[] = $dayNum;
      $salesSumsY[] = $salesSum;
	  // Plugin order totals
      $blogsOrderSum += $dayStats['blogsordercount'];
	  $groupsOrderSum += $dayStats['groupsordercount'];
	  $albumsOrderSum += $dayStats['albumsordercount'];
	  $eventsOrderSum += $dayStats['eventsordercount'];
	  $chatOrderSum += $dayStats['chatordercount'];
	  $classifiedsOrderSum += $dayStats['classifiedsordercount'];
	  $pollsOrderSum += $dayStats['pollsordercount'];
	  $musicOrderSum += $dayStats['musicordercount'];
	  $videoOrderSum += $dayStats['videoordercount'];
	  $forumOrderSum += $dayStats['forumordercount'];
   }

   // SIMPLE AVERAGE METHOD


   
   // LINEAR REGRESSION METHOD
   // y = m*x + b


   // ORDERS AVERAGE
*/


// SET PREV MONTH AND YEAR
if($m == 1) {
  $prev_m = 12;
  $prev_y = $y-1;
  $next_m = $m+1;
  $next_y = $y;
} elseif($m == 12) {
  $prev_m = $m-1;
  $prev_y = $y;
  $next_m = 1;
  $next_y = $y+1;
} else {
  $prev_m = $m-1;
  $prev_y = $y;
  $next_m = $m+1;
  $next_y = $y;
}

// DISPLAY HEADER
echo $head;

// DISPLAY HEADING
echo "
<div style='margin-left:50px; margin-right:50px;'>
<h1>Reporting for {$monthName} ".date("Y", mktime(0, 0, 0, $m, 1, $y))."</h1>
<a href='siteadmin_stats_dashboard.php?m={$prev_m}&y={$prev_y}'>previous</a> | <a href='siteadmin_stats_dashboard.php?m={$next_m}&y={$next_y}'>next</a><br />
<span style='font-size:10px'>".date("D M j G:i:s T Y")."</span></h1>";

// AGGREGATE DATA
echo "
<h2><u>Aggregate Data</u></h2>
<table cellspacing='0' cellpadding='0'>
<tr><td valign='top'>

<table cellspacing='0' cellpadding='2'>
<tr><td><b>Current Sales</b>:</td><td>\$".number_format($salesSum)."</td></tr>
<tr><td><b>Projected final (Simple average)</b>:</td><td>\$".number_format($projectedSales)." ({$confidence}% completed)</td></tr>
<tr><td><b>Projected final (Linear regression):</b></td><td>\$".number_format($linearProjection)."</td></tr>
".($earlyInDayAndMonth?"<tr><td colspan=2><span style=\"font-size: 10px;\">Note: numbers might be skewed due to small sample size</span></td></tr>":"")."
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Average sales / day</b></td><td>\$".number_format($avgDailySales)."</td></tr>
<tr><td><b>Average orders / day</b></td><td>{$avgDailyOrders}</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Current Orders:</b></td><td>".($orderSum+$orderCancelledSum)." ({$orderCancelledSum} cancelled)</td></tr>
<tr><td>include SocialEngine:</td><td>{$orderWithSESum} (".round($orderWithSESum/$orderSum*100, 0)."%)</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Reseller Licenses</b></td><td>{$reseller['total']} reseller orders</td></tr>
<tr><td valign='top'><b>Affiliate Orders</b></td><td>
";
for($x=0;$x<count($affs);$x++) {
  echo "<a href='siteadmin_affiliates_edit.php?affiliate_id={$affs[$x]['affiliate_id']}'>{$affs[$x]['affiliate_username']}</a> - {$affs[$x]['total_sales']}";
  if( !$affs[$x]['affiliate_order_status'] ) {
	echo ' (pending)';
  } else if( $affs[$x]['affiliate_order_cancelled'] ) {
	echo ' (cancelled)';
  }
  echo "<br />";
}
echo "
</td></tr>
</table>

</td><td style='padding-left: 20px;' valign='top'>

<table cellspacing='0' cellpadding='2'>
<tr><td valign='top'><b>Order Sources:</b></td><td>
";
for($x=0;$x<count($sources);$x++) {
  echo "{$sources[$x]['user_source']} - {$sources[$x]['total_source']} new clients - $ ".round($sources_sum[$x]['sum_source'], 2)." <br />";
}
echo "</td></tr>
<tr><td valign='top'><b>Email Sources:</b></td><td>
";

// GET EMAIL SOURCES FROM THIS MONTH
$email_query = mysql_query("SELECT email_post_body FROM email_posts WHERE email_post_date >= '{$month_start}' AND email_post_date < '{$month_end}' AND email_post_body LIKE '%Source:%' AND email_post_address <> 'SocialEngine Team <info@socialengine.net>'") or die(mysql_error());
while($email = mysql_fetch_assoc($email_query)) {
  $emails[] = $email;
}
$source_type_count = Array();
foreach ($emails as $email) {
    $source = htmlentities(get_source_from_email(html_entity_decode($email['email_post_body'])));
    if (!isset($source_type_count[$source])) {
        $source_type_count[$source] = 1;
    }
    else {
        $source_type_count[$source]++;
    }
}
arsort($source_type_count);

foreach ($source_type_count as $source=>$count) {
    if(!strstr($source, "http://www.socialengine.net") && $source != "") {
      echo "$source: $count <br />";
    }
}

echo "</td></tr>";

echo "
</td></tr>
</table>

</td><td style='padding-left: 20px;' valign='top'>

<table cellspacing='0' cellpadding='2'>
";
echo "
<tr><td valign='top'><b>Coupons Used:</b></td><td>
";

// GET COUPONS USED FROM THIS MONTH
for($x=0;$x<count($sources_coupon);$x++) {
  echo "{$sources_coupon[$x]['user_product_extra']} - {$sources_coupon[$x]['total_source_coupon']} <br />";
}

echo "</td></tr>";
echo "</table></td><td style='padding-left: 20px;' valign='top'>

<table cellspacing='0' cellpadding='2'>
";
echo "
<tr><td valign='top'><b>Order Keywords:</b></td><td>
";

// GET ORDER KEYWORDS FROM THIS MONTH
for($x=0;$x<count($sources_keyword);$x++) {
  echo "{$sources_keyword[$x]['user_keyword']} - {$sources_keyword[$x]['total_source_keyword']} <br />";
}

echo "</td></tr>";
echo "</table></td></tr></table>";

echo "
<tr><td valign='top'><br /><b>Support Tickets:</b></td><td>
";

// GET TICKETS STATISTICS FROM THIS MONTH
  $tab ='&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
  $tab2 ='&nbsp&nbsp&nbsp&nbsp&nbsp';
  echo "Opened - {$num_ticket_open}".$tab2."    Total Ticket Time  - ".round($ticket_total_time, 2)." hours".$tab2."$".$ticket_hour."/hr".$tab2."$".$ticket_cost."/ticket"."<br />";
  echo "{$tab}Closed - {$num_ticket_closed['COUNT(ticket_id)']}".$tab2."&nbsp"."&nbsp"."&nbsp"."&nbsp"."Time Working  - ".round($ticket_replies_time, 2)." hours"."<br />";
  echo "{$tab}Read - {$num_ticket_read['COUNT(ticket_id)']}".$tab2.$tab2."Time Viewing  - ".round($ticket_views_time, 2)." hours"."<br />";
  echo "{$tab}Answered - {$num_ticket_answered['COUNT(ticket_id)']} <br />";
  echo "{$tab}Install - {$num_ticket_install['COUNT(ticket_id)']}".$tab2.$tab2."Clients Opened  - ".round($percentage_clients, 2)."%"."<br />";
  echo "{$tab}Flagged - {$num_ticket_flagged['COUNT(ticket_id)']}".$tab2.$tab2."Clients Opened All Time  - ".round($percentage_clients_all, 2)."%"."<br /><br />";
  
echo "</td></tr>";
echo "</table></td></tr></table>";

echo "
<tr><td valign='top'><b>Detail Sales:</b><br /></td><td>
";

// GET SALES DETAIL
  $tab ='&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
  while($product_row = mysql_fetch_array($product_sales_query)){
	echo "{$tab}". $product_row['product_type']. " - $". number_format($product_row['SUM(user_products.user_product_price)']);
	echo "<br />";
}

   echo "<br />"."{$tab}Follow-Up - {$remarketing_sales_total}/{$remarketing_emails_total} -$ {$remarketing_sales}".$tab2."<br />";

echo "</td></tr>";
echo "</table></td></tr></table>";

// DAILY DATA
echo "
<h2><u>Daily Data</u></h2>
<ul style='list-style-type: none;'>
";

foreach ($dailyData as $dayNum=>$dayStats) {
  $dayStats['orderdateunix'] = mktime(0, 0, 0, $m, $dayNum, $y);

  // CALCULATE AVG DAILY
  $dayStats['ordercount']['total'] = $dayStats['ordercount']['good']+$dayStats['ordercount']['cancelled'];
  $dayStats['avgsale'] = ($dayStats['ordercount']['good'] == 0) ? 0 : round($dayStats['salestotal']/$dayStats['ordercount']['good'], 2);

   $ordersWithSEtext = ($dayStats['ordercount']['good'] == 0) ? 0 : round($dayStats['totals']['license']/$dayStats['ordercount']['good']*100)."%";
//   $ordersWithSEOnlytext = getPercent($dayStats['orderswithseonly'], $dayStats['netorders']) . "%";
//   $ordersWithPluginsOnlytext = ($dayStats['ordercount']['good'] == 0) ? 0 : round($dayStats['totals']['plugin']/$dayStats['ordercount']['good']*100)."%";
   $extendedSupporttext = ($dayStats['ordercount']['good'] == 0) ? 0 : round($dayStats['totals']['support']/$dayStats['ordercount']['good']*100)."%";

  echo "
  <li style='float: left; border: 1px solid #DDDDDD; padding: 5px; width: 200px; height: 250px;'>
    <div style='float: left;'><span style='font-size:20px; font-weight: bold;'>$monthName $dayNum </span> ". date("l", $dayStats['orderdateunix']) . "</div>
    <div style='text-align: right;'><span style='font-size:14px; font-weight: bold;'>\$" . number_format($dayStats['salestotal']) . "</span></div>
    <br /><br />
    Total Orders: {$dayStats['ordercount']['total']} ".(($dayStats['ordercount']['cancelled'] > 0)?"({$dayStats['ordercount']['cancelled']} cancelled)":"")."<br />
    Avg Sale: \${$dayStats['avgsale']}<br />
    Breakdown:<br />
	<blockquote style='margin-top: 0px; margin-left: 20px;'>
        Only SE:<br />
        Include SE: {$ordersWithSEtext}<br />
        Only Plugins: <br />
	Include Support: {$extendedSupporttext}<br />
	</blockquote>
	SocialMedia:<br />
	<blockquote style='margin-top: 0px; margin-left: 20px;'>
	"; if($dayStats['facebook_url']) { echo "<a href='{$dayStats['facebook_url']}' target='_blank'>Facebook</a><br /> "; }
	if ($dayStats['twitter_url']) { echo " <a href='{$dayStats['twitter_url']}' target='_blank'>Twitter</a><br /> "; }
	if ($dayStats['linkedin_url']) { echo " <a href='{$dayStats['linkedin_url']}' target='_blank'>LinkedIn</a><br /> "; }
	if ($dayStats['googleplus_url']) { echo " <a href='{$dayStats['googleplus_url']}' target='_blank'>Google Plus</a><br /> "; }
	if ($dayStats['blog_url']) { echo " <a href='{$dayStats['blog_url']}' target='_blank'>Blog</a><br /> "; }
	if ($dayStats['newsletter_url']) { echo " <a href='{$dayStats['newsletter_url']}' target='_blank'>Newsletter</a><br /> "; } echo "
	</blockquote>
  </li>";
}
echo "</ul>";
echo "</div>";

// OUTPUT FOOTER
echo $foot;

/**
 * linear regression function
 * @param $x array x-coords
 * @param $y array y-coords
 * @returns array() m=>slope, b=>intercept
 */
function linear_regression($x, $y) {
  // calculate number points
  $n = count($x);

  // ensure both arrays of points are the same size
  if ($n != count($y)) {
    trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
  }

  // calculate sums
  $x_sum = array_sum($x);
  $y_sum = array_sum($y);

  $xx_sum = 0;
  $xy_sum = 0;

  for($i = 0; $i < $n; $i++) {
    $xy_sum+=($x[$i]*$y[$i]);
    $xx_sum+=($x[$i]*$x[$i]);
  }

  // calculate slope
  @$m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

  // calculate intercept
  $b = ($y_sum - ($m * $x_sum)) / $n;

  // return result
  return array("m"=>$m, "b"=>$b);
}


?>

<!--



// START DISPLAY DATA

echo "$head 

<div style=\"margin-left:50px; margin-right:50px;\">

<h1>Reporting for $monthName " . date("Y", $dailyData[1]['orderdateunix']) . "<br/> <span style=\"font-size:10px\">" . date("D M j G:i:s T Y") . "</span></h1>";

// If it's before the 10th of the month, and earlier than 4pm in the day
$earlyInDayAndMonth = (date("G") < 16 && date("j") < 10);

// AGGREGATE DATA
echo "
<h2><u> Aggregate Data </u></h2>
<table>
<tr><td><b>Current Sales</b>:</td> <td>\$" . number_format($salesSum) . "</td></tr>
<tr><td><b>Projected final (Simple average)</b>:</td> <td>\$" . number_format($projectedSales) . " ($confidence% completed)</td></tr>
<tr><td><b>Projected final (Linear regression):</b></td><td>\$" . number_format($linearProjection) . "</td></tr>
<tr><td colspan=2><span style=\"font-size: 10px;\">" . ($earlyInDayAndMonth?"Note: numbers might be skewed due to small sample size":"") . "</span></td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Average sales / day</b></td><td>\$" . number_format($avgDailySales) . "</td></tr>
<tr><td><b>Average orders / day</b></td><td>$avgDailyOrders</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Current Orders:</b></td><td>$orderSum ($orderCancelledSum cancelled)</td></tr>
<tr><td>include SocialEngine:</td><td>$orderWithSESum (". getPercent($orderWithSESum,$orderSum) . "%)</td></tr>
<tr><td>only SocialEngine:</td><td>$orderSEOnlySum (". getPercent($orderSEOnlySum,$orderSum) . "%)</td></tr>
<tr><td>only plugins:</td><td>$orderPluginOnlySum (". getPercent($orderPluginOnlySum,$orderSum) . "%)</td></tr>
<tr><td>extended support:</td><td>$orderSupportSum (". getPercent($orderSupportSum,$orderSum) . "%)</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2><u>Plugin orders</u></td></tr>
<tr><td>Blogs:</td><td>$blogsOrderSum </td></tr>
<tr><td>Albums :</td><td> $albumsOrderSum </td></tr>
<tr><td>Groups:</td><td> $groupsOrderSum </td></tr>
<tr><td>Events:</td><td> $eventsOrderSum </td></tr>
<tr><td>Chat:</td><td> $chatOrderSum </td></tr>
<tr><td>Classifieds:</td><td> $classifiedsOrderSum </td></tr>
<tr><td>Polls:</td><td> $pollsOrderSum </td></tr>
<tr><td>Music:</td><td> $musicOrderSum </td></tr>
<tr><td>Video:</td><td> $videoOrderSum </td></tr>
<tr><td>Forum:</td><td> $forumOrderSum </td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><b>Via Paypal:</b></td><td>". getPercent($paypalOrdersSum,$orderSum) . "%</td></tr>
<tr><td><b>Via 2checkout:</b></td><td>". getPercent($_2checkoutOrdersSum,$orderSum) . "%</td></tr>
</table>";



-->