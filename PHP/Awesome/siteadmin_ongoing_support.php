<?php
$page = "siteadmin_ongoing_support";
include "siteadmin_header.php";

if(isset($_POST['p'])) { $p = $_POST['p']; } elseif(isset($_GET['p'])) { $p = $_GET['p']; } else { $p = 1; }
if(isset($_POST['search'])) { $search = $_POST['search']; } elseif(isset($_GET['search'])) { $search = $_GET['search']; } else { $search = ""; }
if(isset($_POST['license_id'])) { $license_id = $_POST['license_id']; } elseif(isset($_GET['license_id'])) { $license_id = $_GET['license_id']; } else { $license_id = 0; }
if(isset($_POST['sort'])) { $sort = $_POST['sort']; } elseif(isset($_GET['sort'])) { $sort = $_GET['sort']; } else { $sort = "cid"; }
if(isset($_POST['cat_id'])) { $cat_id = $_POST['cat_id']; } elseif(isset($_GET['cat_id'])) { $cat_id = $_GET['cat_id']; } else { $cat_id = ""; }

// BUILD QUERY

$sql = "SELECT *, count(DISTINCT user_licenses.user_license_id) AS total_licenses, count(DISTINCT user_mods.user_mod_id) AS total_mods 
		FROM users 
		LEFT JOIN user_products ON users.user_id=user_products.user_product_user_id 
		LEFT JOIN user_licenses ON users.user_id=user_licenses.user_license_user_id 
		LEFT JOIN user_mods ON users.user_id=user_mods.user_mod_user_id 
		WHERE user_products.user_product_product_id='19' AND users.user_recur_support='1' AND users.user_recur_cancel='0'
		GROUP BY users.user_id";

// IF SEARCH FILTER
if($search != "" || $cat_id != "") {
  $sql .= " LEFT JOIN user_licenses s ON users.user_id=s.user_license_user_id";
}

// CAT ID
if($cat_id != "") {
  $sql .= " WHERE s.user_license_cat_id='{$cat_id}'";
  if($search != "") { $sql .= " AND"; }

// GET WHERE QUERY?
} else {
  if($search != "") { $sql .= " WHERE"; }
}


// APPLY SEARCH FILTER
if($search != "") {
  $sql .= " (s.user_license_key LIKE '%{$search}%' OR s.user_license_website LIKE '%{$search}%' OR user_email LIKE '%{$search}%' OR CONCAT(user_fname, ' ', user_lname) LIKE '%{$search}%' OR user_company LIKE '%{$search}%' OR user_company_website LIKE '%{$search}%')";
}

//$sql .= " GROUP BY users.user_id";

// GET TOTAL RESULTS
$total_results = mysql_num_rows(mysql_query($sql));

// MAKE PAGES
$results_per_page = 20;
if(($total_results % $results_per_page) != 0) { $maxpage = ($total_results) / $results_per_page + 1; } else { $maxpage = ($total_results) / $results_per_page; }
$maxpage = (int) $maxpage;
if($p > $maxpage) { $p = $maxpage; } elseif($p < 1) { $p = 1; }
$start = ($p - 1) * $results_per_page;
if($start < 0) { $start = 0; }


// ADD SORT
switch($sort) {
  case "ma": $sortby = "total_mods"; break;
  case "m": $sortby = "total_mods DESC"; break;
  case "la": $sortby = "total_licenses"; break;
  case "l": $sortby = "total_licenses DESC"; break;
  case "ia": $sortby = "user_id"; break;
  default:
  case "i": $sortby = "user_id DESC"; break;
}


// ADD LIMIT AND SORT TO QUERY
$sql .= " ORDER BY $sortby LIMIT $start, $results_per_page";

// GET USERS
$users = mysql_query($sql);


// GET TOTALS
$total = mysql_num_rows(mysql_query("SELECT NULL FROM user_products WHERE user_product_product_id='19' GROUP BY user_product_user_id"));

// BEGIN PAGE OUTPUT
echo $head;


// OUTPUT JAVASCRIPT
echo "
<img src='../images/trans.gif' id='ajax' border='0' style='display: none;'>

<script type='text/javascript'>
<!--
function starit(id1) {
  var star = 'star' + id1;
  if($('#star'+id1).attr('rel') == 0) {
    $('#ajax').attr('src', 'siteadmin_ogoing_support.php?task=star&license_id=' + id1);
    $('#star'+id1).attr('src', './misc/images/heart2.gif');
    $('#star'+id1).attr('rel', 1);
    $('#star'+id1).blur();
    $('#starred').html(parseInt($('#starred').html())+1);
  } else {
    $('#ajax').attr('src', 'siteadmin_ongoing_support.php?task=unstar&license_id=' + id1);
    $('#star'+id1).attr('src', './misc/images/heart1.gif');
    $('#star'+id1).attr('rel', 0);
    $('#star'+id1).blur();
    $('#starred').html(parseInt($('#starred').html())-1);
  }
}
//-->
</script>
";


// OUTPUT MAIN PAGE
echo "

<table cellpadding='0' cellspacing='0' align='center'>
<tr>
<td style='font-weight: bold; padding-right: 30px;'>
[ <a href='siteadmin_clients_add.php'>Add Client</a>] &nbsp;&nbsp;
  <a href='siteadmin_ongoing_support.php?sort={$sort}'>Total Clients: {$total}</a>
</td>
</tr>
</table>


<div style='padding: 15px 0px 0px 5px;' id='searchbox'>
  <form action='siteadmin_ongoing_support.php' method='post'>
  <table cellpadding='0' cellspacing='0' align='center'>
  <tr>
  ";
  //<td>Search:&nbsp;</td>
  //<td><input type='text' class='text' name='search' value='{$search}'>&nbsp;</td>
  //<td><select name='cat_id'><option value=''".(($cat_id == "")?" selected='selected'":"").">All</option><option value='0'".(($cat_id == "0")?" selected='selected'":"").">Uncategorized</option>";
  
  $license_cats = mysql_query("SELECT * FROM user_license_cats ORDER BY user_license_cat_name");
  while($cat = mysql_fetch_assoc($license_cats)) {
    echo "<option value='{$cat['user_license_cat_id']}'".(($cat_id == $cat['user_license_cat_id'])?" selected='selected'":"").">{$cat['user_license_cat_name']}</option>";
    
  }
  echo "
  </select>
  ";
  //<td><input type='submit' class='button' value='Filter'></td>
  echo "
  </tr>
  </table>
  <input type='hidden' name='task' value='{$task}'>
  <input type='hidden' name='sort' value='{$sort}'>
  </form>
</div>

";


// IF MORE THAN ONE PAGE OF CLIENTS
if($maxpage > 1) {
  echo "<br /><br />";
  $url_base_params = "siteadmin_ongoing_support.php?task={$task}&sort={$sort}&cat_id={$cat_id}";
  if($search) $url_base_params .= "&search=".urlencode($search);

  echo ( $p != 1 ? "<a href='{$url_base_params}&p=1'>&lt;&lt; First Page</a>" : "<font class='disabled'>&lt;&lt; First Page</font>" )."&nbsp;";
  echo ( $p != 1 ? "<a href='{$url_base_params}&p=".($p-1)."'>&lt; Previous Page</a>" : "<font class='disabled'>&lt; Previous Page</font>" )."&nbsp;";

  if($start+$results_per_page > $total_results) { $last = $total_results; } else { $last = $start+$results_per_page; }
  echo "&nbsp;|&nbsp; viewing clients ".($start+1)."-$last of $total_results &nbsp;|&nbsp; ";

  echo ( $p != $maxpage ? "<a href='{$url_base_params}&p=".($p+1)."'>Next Page &gt;</a>" : "<font class='disabled'>Next Page &gt;</font>" )."&nbsp;";
  echo ( $p != $maxpage ? "<a href='{$url_base_params}&p=".($maxpage)."'>Last Page &gt;&gt;</a>" : "<font class='disabled'>Last Page &gt;&gt;</font>" );

  echo "<br /><br />";
}


echo "


<br>

<table cellpadding='0' cellspacing='0' width='97%' style='border-top: 1px solid #AAAAAA;' align='center'>
<tr>
<td class='header' width='10' style='border-left: 1px solid #AAAAAA;'><a href='siteadmin_ongoing_support.php?task={$task}&search={$search}&cat_id={$cat_id}&sort=".(($sort == "i")?"ia":"i")."'>ID</a></td>
<td class='header'>Name</td>
<td class='header'>Email</td>
<td class='header' width='100' align='center'><a href='siteadmin_ongoing_support.php?task={$task}&search={$search}&cat_id={$cat_id}&sort=".(($sort == "l")?"la":"l")."'>Total Licenses</a></td>
<td class='header' width='100' align='center'><a href='siteadmin_ongoing_support.php?task={$task}&search={$search}&cat_id={$cat_id}&sort=".(($sort == "m")?"ma":"m")."'>Total Mods</a></td>
<td class='header' width='200'>Last Login</td>
<td class='header' width='200' style='border-right: 1px solid #AAAAAA;'>Support Expires</td>
</tr>
";

$count = 0;
while($user = mysql_fetch_assoc($users)) {
  $count++;

  $x = 2;
  if($user['user_enabled'] == 0) { $x = "_disabled"; }
  if($user['user_pirated'] == 1) { $x = "_pirated"; }
  if($user['user_reseller'] == 1) { $x = "_reseller"; }

  if($user['user_date_lastlogin'] > 0) {
    $lastlogin = date('M d, g:i a', $user['user_date_lastlogin']);
  } else {
    $lastlogin = "";
  }

  $support_expires = date('M d, g:i a', $user['user_date_supportexpires']);

  echo "
  <tr>
  <td class='row{$x}' style='border-left: 1px solid #AAAAAA;'>{$user['user_id']}</td>
  <td class='row{$x}'><a href='siteadmin_clients_edit.php?user_id={$user['user_id']}'>".(($user['user_fname'] != "" || $user['user_lname'] != "")?"{$user['user_fname']} {$user['user_lname']}":"(not yet entered)")."</a>&nbsp;</td>
  <td class='row{$x}'><a href='mailto:{$user['user_email']}'>{$user['user_email']}</a>&nbsp;</td>
  <td class='row{$x}' align='center'>{$user['total_licenses']}</td>
  <td class='row{$x}' align='center'>{$user['total_mods']}</td>
  <td class='row{$x}'>{$lastlogin}&nbsp;</td>
  <td class='row{$x}' style='border-right: 1px solid #AAAAAA;'>{$support_expires}&nbsp;</td>
  </tr>
  ";


  // GET LICENSES
  $licenses = 0;
  $sql = "SELECT * FROM user_licenses LEFT JOIN user_license_cats ON user_licenses.user_license_cat_id=user_license_cats.user_license_cat_id WHERE user_license_user_id='{$user['user_id']}'".(($task == "starred")?" AND user_license_favorite='1'":"").(($task == "donated")?" AND user_license_donated='1'":"")." ORDER BY user_license_id";
  $license_query = mysql_query($sql);
  $total_licenses = mysql_num_rows($license_query);
  while($license = mysql_fetch_assoc($license_query)) {
    $licenses++;

    if(substr($license['user_license_website'], 0, 4) == "www.") {
      $license['user_license_website'] = str_replace("www.", "http://www.", $license['user_license_website']);
    } elseif(substr($license['user_license_website'], 0, 7) != "http://") {
      $license['user_license_website'] = "http://".$license['user_license_website'];
    }

    $buydate = date('M d Y, g:i a', $license['user_license_date_purchase']);

    if($license['user_license_cancelled'] == 1) { $y = "_disabled"; } elseif($license['user_license_donated'] == 1) { $y = "_donated"; } elseif($license['user_license_status'] == 0) { $y = "_pending"; } else { $y = ""; }

    echo "
      <tr>
      <td class='row_license'".(($licenses != $total_licenses || $count == $total_results)?" style='border-bottom:none;'":"").">&nbsp;</td>
      <td class='row{$y}' style='border-left: 1px solid #AAAAAA;'>
      ";
      echo "
      </td>
      <td class='row{$y}' colspan='2'>{$license['user_license_key']} (<a href='{$license['user_license_website']}' target='_blank'>{$license['user_license_website']}</a>)</td>
      <td class='row{$y}' colspan='2'>{$license['user_license_cat_name']}</td>
      <td class='row{$y}' style='border-right: 1px solid #AAAAAA;'>Purchased: {$buydate}</td>
      </tr>
    ";
  }


}

echo "</table>";

// IF MORE THAN ONE PAGE OF CLIENTS
if($maxpage > 1) {
  echo "<br /><br />";
  $url_base_params = "siteadmin_ongoing_support.php?task={$task}&cat_id={$cat_id}&sort={$sort}";
  if($search) $url_base_params .= "&search=".urlencode($search);

  echo ( $p != 1 ? "<a href='{$url_base_params}&p=1'>&lt;&lt; First Page</a>" : "<font class='disabled'>&lt;&lt; First Page</font>" )."&nbsp;";
  echo ( $p != 1 ? "<a href='{$url_base_params}&p=".($p-1)."'>&lt; Previous Page</a>" : "<font class='disabled'>&lt; Previous Page</font>" )."&nbsp;";

  if($start+$results_per_page > $total_results) { $last = $total_results; } else { $last = $start+$results_per_page; }
  echo "&nbsp;|&nbsp; viewing clients ".($start+1)."-$last of $total_results &nbsp;|&nbsp; ";

  echo ( $p != $maxpage ? "<a href='{$url_base_params}&p=".($p+1)."'>Next Page &gt;</a>" : "<font class='disabled'>Next Page &gt;</font>" )."&nbsp;";
  echo ( $p != $maxpage ? "<a href='{$url_base_params}&p=".($maxpage)."'>Last Page &gt;&gt;</a>" : "<font class='disabled'>Last Page &gt;&gt;</font>" );

  echo "<br /><br />";
}


// OUTPUT FOOTER
echo $foot;

?>