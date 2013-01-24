<?php
$page = "siteadmin_trials";
include "siteadmin_header.php";

if(isset($_POST['p'])) { $p = $_POST['p']; } elseif(isset($_GET['p'])) { $p = $_GET['p']; } else { $p = 1; }
if(isset($_POST['search'])) { $search = $_POST['search']; } elseif(isset($_GET['search'])) { $search = $_GET['search']; } else { $search = ""; }
if(isset($_POST['followup'])) { $followupval = $_POST['followup']; } elseif(isset($_GET['followup'])) { $followupval = $_GET['followup']; } else { $followupval = null; }
if(isset($_POST['sort'])) { $sort = $_POST['sort']; } elseif(isset($_GET['sort'])) { $sort = $_GET['sort']; } else { $sort = "trial_id DESC"; }
if( $followupval === '' ) $followupval = null;


$trial_query = "SELECT trials.*, users.user_id, users.user_fname, users.user_lname FROM trials LEFT JOIN users ON trials.trial_user_id=users.user_id";

// IF SEARCH FILTER IS APPLIED
if( $search || null !== $followupval ) {
  $trial_query .= " WHERE ";
}
if($search != "") {
  $trial_query .= " (trial_key LIKE '%$search%' OR trial_email LIKE '%$search%' OR user_fname LIKE '%$search%' OR user_lname LIKE '%$search%') ";
}
if( $search && null !== $followupval ) {
  $trial_query .= " AND ";
}
if( null !== $followupval ) {
  $trial_query .= " trial_followup_state = " . (int) $followupval . " ";
}

// GET TOTAL TRIALS
$total_trials = mysql_num_rows(mysql_query($trial_query));

// MAKE TRIAL PAGES
$trials_per_page = 20;
if(($total_trials % $trials_per_page) != 0) { $maxpage = ($total_trials) / $trials_per_page + 1; } else { $maxpage = ($total_trials) / $trials_per_page; }
$maxpage = (int) $maxpage;
if($p > $maxpage) { $p = $maxpage; } elseif($p < 1) { $p = 1; }
$start = ($p - 1) * $trials_per_page;
if($start < 0) { $start = 0; }

// ADD LIMIT AND SORT TO TRIAL QUERY
$trial_query .= " ORDER BY $sort LIMIT $start, $trials_per_page";

$trials = mysql_query($trial_query);


$total = mysql_num_rows(mysql_query("SELECT NULL FROM trials"));

// SHOW HEADER
echo $head;

echo "
<table cellpadding='0' cellspacing='0' align='center'>
<tr>
<td style='font-weight: bold; padding-right: 30px;'>
  <span id='searchlink'"; if($search != "") { echo " style='display: none;'"; } echo ">[ <a href='javascript:void(0);' onClick=\"$('#searchbox').toggle();\">Search</a> ] &nbsp;&nbsp;</span>
  <a href='siteadmin_trials.php'>Total Trials: {$total}</a>
</td>
<td>
  [ <a href='siteadmin_trials_cron.php?return=1'>Run Cron Now</a> ]
</td>
</tr>
</table>

<div style='"; if( $search == "" && $followupval == "" ) { echo "display: none; "; } echo "padding: 15px 0px 0px 5px;' id='searchbox'>
  <form action='siteadmin_trials.php' method='post'>
  <table cellpadding='0' cellspacing='0' align='center'>
  <tr>
  <td>Search:&nbsp;</td>
  <td><input type='text' class='text' name='search' value='{$search}'>&nbsp;</td>
  <td>
	<select name='followup'>
		<option value=''>(Follow-Up State)</option>
		<option value='0'" . ( $followupval === '0' ? ' selected="selected"' : '' ) . ">Email not yet sent</option>
		<option value='1'" . ( $followupval === '1' ? ' selected="selected"' : '' ) . ">Email 1 Sent</option>
		<option value='2'" . ( $followupval === '2' ? ' selected="selected"' : '' ) . ">Email 2 Sent</option>
		<option value='3'" . ( $followupval === '3' ? ' selected="selected"' : '' ) . ">Email 3 Sent</option>
		<option value='11'" . ( $followupval === '11' ? ' selected="selected"' : '' ) . ">Purchased before Email 1</option>
		<option value='12'" . ( $followupval === '12' ? ' selected="selected"' : '' ) . ">Purchased between email 1 & 2</option>
		<option value='13'" . ( $followupval === '13' ? ' selected="selected"' : '' ) . ">Purchased between email 2 & 3</option>
		<option value='14'" . ( $followupval === '14' ? ' selected="selected"' : '' ) . ">Purchased after email 3</option>
		<option value='20'" . ( $followupval === '20' ? ' selected="selected"' : '' ) . ">Email was never sent (purchased)</option>
		<option value='21'" . ( $followupval === '21' ? ' selected="selected"' : '' ) . ">Email was never sent (not purchased)</option>
	</select>
  <td><input type='submit' class='button' value='Filter'></td>
  </tr>
  </table>
  <input type='hidden' name='task' value='{$task}'>
  </form>
</div>
";

if($maxpage > 1) {
  echo "
  <br>
  <div style='text-align: center;'>
  ";
  if($p != 1) { echo "<a href='siteadmin_trials.php?search={$search}&followup={$followupval}&p=".($p-1)."'>&#171; Last Page</a>"; } else { echo "<font class='disabled'>&#171; Last Page</font>"; }
  if($start+$trials_per_page > $total_trials) { $last = $total_trials; } else { $last = $start+$trials_per_page; }
  echo "&nbsp;|&nbsp; viewing trials ".($start+1)."-{$last} of {$total_trials} &nbsp;|&nbsp; ";
  if($p != $maxpage) { echo "<a href='siteadmin_trials.php?search={$search}&followup={$followupval}&p=".($p+1)."'>Next Page &#187;</a>"; } else { echo "<font class='disabled'>Next Page &#187;</font>"; }
  echo "
  </div>
  ";
}

echo "


<br>

<table cellpadding='0' cellspacing='0' width='97%' class='list' align='center'>
<tr>
<td class='header' width='10'>ID</td>
<td class='header'><a href='siteadmin_trials.php?task={$task}&search={$search}&sort=trial_id%20DESC'>Trial Key</a></td>
<td class='header'>Email</td>
<td class='header'>Request Date</td>
<td class='header'>Download Date</td>
<td class='header'><a href='siteadmin_trials.php?task={$task}&search={$search}&sort=trial_date_purchase%20DESC'>Purchase Date</a></td>
<td class='header'><a href='siteadmin_trials.php?task={$task}&search={$search}&sort=trial_user_id%20DESC'>Client</a></td>
<td class='header'>Followup State</td>
</tr>
";

//$dateFormat = 'r';
$dateFormat = 'D M d Y g:i a';
$count = 0;
while($trial = mysql_fetch_assoc($trials)) {

  if($count % 2) { $x = 2; } else { $x = ""; }

  $request_date = date($dateFormat, $trial['trial_date_request']);
  if($trial[trial_date_download] != 0) {
    $download_date = date($dateFormat, $trial['trial_date_download']);
  } else {
    $download_date = "";
  }
  if($trial[trial_date_purchase] != 0) {
    $purchase_date = date($dateFormat, $trial['trial_date_purchase']);
  } else {
    $purchase_date = "";
  }

  	$followup = '';
	switch( (int) $trial['trial_followup_state'] ) {
		case 0:
			$followup = 'Email not yet sent';
			break;
		case 1:
			$followup = 'Email 1 Sent';
			break;
		case 2:
			$followup = 'Email 2 Sent';
			break;
		case 3:
			$followup = 'Email 3 Sent';
			break;
		case 11:
			$followup = 'Purchased before Email 1';
			break;
		case 12:
			$followup = 'Purchased between email 1 & 2';
			break;
		case 13:
			$followup = 'Purchased between email 2 & 3';
			break;
		case 14:
			$followup = 'Purchased after email 3';
			break;
		case 20:
			$followup = 'Email was never sent (purchased)';
			break;
		case 21:
			$followup = 'Email was never sent (not purchased)';
			break;
		default:
			$followup = 'Unknown state';
			break;
}

  echo "
  <tr>
  <td class='row$x'>{$trial['trial_id']}</td>
  <td class='row$x'>{$trial['trial_key']}&nbsp;</td>
  <td class='row$x'><a href='mailto:{$trial['trial_email']}'>{$trial['trial_email']}</a>&nbsp;</td>
  <td class='row$x'>{$request_date}&nbsp;</td>
  <td class='row$x'>{$download_date}&nbsp;</td>
  <td class='row$x'>{$purchase_date}&nbsp;</td>
  <td class='row$x'><a href='siteadmin_clients_edit.php?user_id={$trial['trial_user_id']}'>{$trial['user_fname']} {$trial['user_lname']}</a>&nbsp;</td>
  <td class='row$x'>{$followup}</td>
  </tr>
  ";

  $count++;
}

echo "
</table>
";


if($maxpage > 1) {
  echo "
  <br>
  <div style='text-align: center;'>
  ";
  if($p != 1) { echo "<a href='siteadmin_trials.php?search={$search}&followup={$followupval}&p=".($p-1)."'>&#171; Last Page</a>"; } else { echo "<font class='disabled'>&#171; Last Page</font>"; }
  if($start+$trials_per_page > $total_trials) { $last = $total_trials; } else { $last = $start+$trials_per_page; }
  echo "&nbsp;|&nbsp; viewing trials ".($start+1)."-{$last} of {$total_trials} &nbsp;|&nbsp; ";
  if($p != $maxpage) { echo "<a href='siteadmin_trials.php?search={$search}&followup={$followupval}&p=".($p+1)."'>Next Page &#187;</a>"; } else { echo "<font class='disabled'>Next Page &#187;</font>"; }
  echo "
  </div>
  <br>
  <br>
  ";
}

// SHOW FOOTER
echo $foot;
?>