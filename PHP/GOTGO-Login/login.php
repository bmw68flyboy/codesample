<?php // *** require_once('../../../../../AppData/Roaming/Macromedia/Dreamweaver 
// *** 8/Configuration/ServerConnections/GOTGOLogin/Connections/GOTGOLogin.php'); 
ob_start();
$host="-"; // Host name
$username="-"; // Mysql username
$password="-"; // Mysql password
$db_name="-"; // Database name
$tbl_name="customer"; // Table name

// Connect to server and select databse.
mysql_connect("$host", "$username", "$password")or die("cannot connect");
mysql_select_db("$db_name")or die("cannot select DB");

//$sql="SELECT * FROM $tbl_name WHERE username='$username' and password='$password'";
//$result=mysql_query($sql);

// Mysql_num_row is counting table row
//$count=mysql_num_rows($result);
// If result matched $myusername and $mypassword, table row must be 1 row

//if($count==1){
// Register $myusername, $mypassword and redirect to file "login_success.php"
session_register("username");
session_register("password");
//}
//else {
//echo "Wrong Username or Password";
//}

ob_end_flush();
?>



?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['username'])) {
  $loginUsername=$_POST['username'];
  $password=$_POST['password'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "http://www.grandsononthego.com/sales.php";
  $MM_redirectLoginFailed = "http://www.grandsononthego.com/loginfail.html";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_GOTGOLogin, $GOTGOLogin);
  
  $LoginRS__query=sprintf("SELECT email, lastname FROM customer WHERE email='%s' AND lastname='%s'",
    get_magic_quotes_gpc() ? $loginUsername : addslashes($loginUsername), get_magic_quotes_gpc() ? $password : addslashes($password)); 
   
  $LoginRS = mysql_query($LoginRS__query, $GOTGOLogin) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>GrandsonONtheGO- Track Your Sales</title>
<link rel="stylesheet" href="styles.css" type="text/css" />
<script type="text/JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
<style type="text/css">
<!--
.style44 {font-family: "Book Antiqua"; color: #333333; }
.style45 {color: #4173A6; font-family: "Book Antiqua"; font-size: 18px; font-weight: bold; }
body,td,th {
	font-size: 1em;
}
.style50 {
	color: #00CC00;
	font-weight: bold;
}
.style51 {color: #365F91}
-->
</style>
</head>
<body onload="MM_preloadImages('images/StuffHome2.png','images/ForSaleHome2.png','images/RecordFinal2.jpg')">
<div id="container">
	<div id="header">
		<h1>&nbsp;</h1>
  </div>
	<div id="nav">
		<ul>
			<li><a href="index.html" class="selected" title="home page">Home</a></li>
			<li><a href="http://www.fortune3.com/GrandsonONtheGO/cart.cgi" title="CSS and XHTML web templates">FOR SALE </a></li>
			<li><a href="links.html" title="web scripts">Links</a></li>
			<li><a href="contact.html" title="php, mysql, css and javascript code snippets">Contact</a></li>
		</ul>
  </div>
	<div id="content">
	  <div id="page">
	    <p class="style45">Track Your Sales  :</p>
	    <blockquote>
	      <p class="style44"><span class="style50">Coming soon</span>- You will be able to see what has sold of yours in REAL TIME. Receive email notifications ans login to see a history of what has sold. We will send you a check for your items once a month if something does sell. </p>
	      <p class="style44"><a href="contact.html">Contact Us</a> if youd like to sell your stuff of for any other questions. </p>
	      <form id="login" method="POST" action="<?php echo $loginFormAction; ?>">
	        <label></label>
	        <table width="339" height="56" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <th width="339" bgcolor="#CCCCCC" scope="col"><label><br />
                    <span class="style51">Username
                    <input type="text" name="username" />
</span></label>
                  <span class="style51">
                  <label><br />
                  <br />
                  </label>
                  <label>Password</label>
                  </span>
                  <label>
                  <input type="text" name="password" />
                  <br />
                   
                  <br />
                  <input type="submit" name="login" value="Login" />
                  <br />
                </label></th>
              </tr>
            </table>
	        <label><br />
	        </label>
	        <p>&nbsp;</p>
	      </form>
	      <p class="style44">&nbsp;</p>
	    </blockquote>
	    <blockquote>&nbsp;</blockquote>
	    <p>&nbsp;</p>
      </div>
		<div id="sidebar">
			<h4>Login Page </h4>
			<p>Coming Soon </p>
			<h4>&nbsp;</h4>
			<p>&nbsp;</p>
			<h4>&nbsp;</h4>
			<ul>
			  <p>&nbsp;</p>
			  <p>&nbsp; </p>
			  <li></li>
			</ul>
		</div>
		<div class="clear"></div>
  </div>
	<div id="footer">
		<p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
  </div>
			
</div>
</body>
</html>
