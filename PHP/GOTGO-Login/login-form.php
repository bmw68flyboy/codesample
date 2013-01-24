<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-15182270-1");
pageTracker._trackPageview();
} catch(err) {}</script>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /><meta name="keywords" content="ebay, e bay, seller, internet consignment help, denver metro craigslist, arvada, consignment, stuff, sell, cash"/>
<title>GrandsonONtheGO- Track Your Sales- eBay, craigslist, online consignment help</title>
<link rel="stylesheet" href="styles.css" type="text/css" />
<style type="text/css">
<!--
.style45 {color: #4173A6; font-family: "Book Antiqua"; font-size: 24px; font-weight: bold; }
body,td,th {
	font-size: 1em;
}
body {
	background-color: #F3F3F3;
}
.style2 {color: #365F91; font-weight: bold; }
.style79 {font-family: Geneva, Arial, Helvetica, sans-serif}
.style102 {
	color: #000000;
	font-size: 18px;
}
.style62 {font-size: 24px; color: #365F91; font-family: Geneva, Arial, Helvetica, sans-serif; }
.style63 {color: #009900; }
.style44 {font-family: "Book Antiqua"; color: #333333; }
.style107 {font-size: 24px}
.style108 {color: #000000; font-family: Geneva, Arial, Helvetica, sans-serif; font-size: 18px; font-weight: bold; }
.style50 {font-family: Geneva, Arial, Helvetica, sans-serif; color: #333333; }
-->
</style>
</head>

<body>
<table width="900" border="0" align="center" bgcolor="#FFFFFF">
  <tr>
    <td><table width="875" border="0" align="center">
      <tr>
        <td><table width="850" border="0" align="center">
          <tr>
            <td><table width="850" border="0">
              <tr>
                <td width="338" rowspan="2" valign="top" bgcolor="#FFFFFF"><p><img src="Logo1.jpg" alt="GrandsonONtheGO Logo" width="338" height="100" /></p>
                  <table width="216" border="0">
                    <tr>
                      <td width="210"><h4 align="center">Coming Soon </h4>
                          <p>Animations  on <a href="http://www.youtube.com/grandsononthego" target="_blank">youtube</a></p>
                        <p>GrandsonONtheGO Ringtone </p></td>
                    </tr>
                  </table>
                  <table width="338" border="0">
                    <tr>                    </tr>
                  </table>  <script type="text/javascript" src="http://static.ak.connect.facebook.com/connect.php/en_US"></script><script type="text/javascript">FB.init("4f4bc4d80e76dc8d798457d4b8184801");</script><fb:fan profile_id="111899112170167" stream="0" connections="10" logobar="1" width="300"></fb:fan>
              <div style="font-size:8px; padding-left:10px"><a href="http://www.facebook.com/pages/GrandsonONtheGO/111899112170167">GrandsonONtheGO</a> on Facebook</div>                </td>
                <td height="30" colspan="3" valign="top"><div id="menuwrapper"> 
			<ul id="p7menubar"> 
			<li><a href="index.html">Home</a></li> 
			<li><a class="trigger" href="sellyourstuff.html">Sell Your Stuff </a> 
 			 <ul> 
			<li><a href="whatwevesold.html">What We've Sold</a></li> 
			<li><a href="wherewepost.html">Where We Post</a></li> 
			<li><a href="estimate.html">Free Estimate</a></li>
			</ul> 
			</li> 
			<li><a class="trigger" href="http://www.grandsononthego.com/login-form.php">Track Your Sales</a> 
			<ul> 
			<li><a href="whatwevesold.html">Leave a Testimonial</a></li> 
			<li><a href="http://www.facebook.com/grandsononthego">Connect with others</a></li> 
			<li><a href="refer.html">Refer a Friend</a></li> 
			</ul> 
			</li> 
			<li><a class="trigger" href="store.html">Store</a> 
			<ul> 
			<li><a href="deals.html">Weekly Deals</a></li> 
			<li><a href="http://www.facebook.com/grandsononthego">Connect with others</a></li> 
			</ul> 
			</li> 
			<li><a class="trigger" href="contact.html">Contact Us</a> 
			<ul> 
			<li><a href="aboutus.html">About Us</a></li> 
			<li><a href="links.html">Links</a></li>  
			</ul> 
			</li> 
			</ul> 
			<br class="clearit"></div> </td>
              </tr>
              <tr>
                <td width="258" height="207" valign="top"><p class="style62"><span class="style107">Your Internet Sales/Auction Provider </span></p>
                  <table width="550" border="0">
                    <tr>
                      <td height="356" align="center" valign="top" bgcolor="#FFFFC4"><p align="left" class="style50">Track Your Sales  :
                      <?php

        

        // Create an empty array to hold the error messages.



        $arrErrors = array();



        //Only validate if the Submit button was clicked.



        if (!empty($_POST['Login'])) 

        {

          // Each time there's an error, add an error message to the error array

          // using the field name as the key.

         

          if ($_POST['firstname']=='')

            $arrErrors['firstname'] = 'Please provide your first name.';

          if ($_POST['lastname']=='')

            $arrErrors['lastname'] = 'Please provide your last name.';

          if ($_POST['email']=='')

            $arrErrors['email'] = 'Please provide your e-mail.';

         

          //If the error array is empty, there were no errors, continue processing.

         

          if (count($arrErrors) == 0) 

          {

            echo '<form id="auto" method="post" action="http://www.grandsononthego.com/login-exec.php">';

            echo '<input name="firstname" type="hidden" id="firstname" value="'.$_POST['firstname'].'">';

            echo '<input name="lastname" type="hidden" id="lastname" value="'.$_POST['lastname'].'">';

            echo '<input name="email" type="hidden" id="email" value="'.$_POST['email'].'">';

            echo '</form>';

            echo '<script type="text/javascript"> document.forms.auto.submit(); </script>';

		      exit();

          } 

          else 

          {

            // The error array had something in it. There was an error.

            // Start adding error text to an error string.

        

            $strError = '<div class="formerror"><p><img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt="">Please check the following and try again:</p><ul>';

        

            // Get each error and add it to the error string 

            // as a list item.

        

            foreach ($arrErrors as $error) 

            {

              $strError .= "<li>$error</li>";

            }

            $strError .= '</ul></div>';

          }

        }

?>
	     
                        <blockquote>
	       <table width="488" border="1">
            <tr>
              <td width="225" bgcolor="#FFFFFF"><!--

For every form field, we do the following...



Check to see if there's an error message for this form field. If there is, 

add the formerror class to the surrounding paragraph block. The formerror

class contains the highlighted box.



Insert the contents of what the user submitted back into the form field.



Check again to see if this field has an error message. If it does, show

the error icon and the error message next to the field.

-->

<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);  ?>">

          <table width="225" height="56" border="0" align="center" cellpadding="0" cellspacing="0">

            <tr>

              <th width="225" bgcolor="#CCCCCC" scope="col"><p class="style44"> Login below or <a href="contact.html">contact us</a></p>
                <p class="style44">
                  <?php if(isset($_POST['Login'])) echo $strError,'<P />'; ?>
                </p>
                <p<?php if (!empty($arrErrors['firstname'])) echo ' class="formerror"'; ?>>    

                  <label for="firstname">First Name:</label>

                  <input size="30" name="firstname" type="text" id="firstname" value="<?php if(isset($_POST['Login'])) echo $_POST['firstname']; ?>">

                  <?php if (!empty($arrErrors['firstname'])) echo '<img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt=""><br /><span class="errortext">'.$arrErrors['firstname'].'</span>'; ?>
                </p>



                <p<?php if (!empty($arrErrors['lastname'])) echo ' class="formerror"'; ?>>

                  <label for="lastname">Last Name:</label>

                  <input size="30" name="lastname" type="text" id="lastname" value="<?php if(isset($_POST['Login'])) echo $_POST['lastname']; ?>">

                  <?php if (!empty($arrErrors['lastname'])) echo '<img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt=""><br /><span class="errortext">'.$arrErrors['lastname'].'</span>'; ?>
                </p>



                <p<?php if (!empty($arrErrors['email'])) echo ' class="formerror"'; ?>>

                  <label for="email">E-mail:</label>

                  <input size="30" name="email" type="text" id="email" value="<?php if(isset($_POST['Login'])) echo $_POST['email']; ?>">

                  <?php if (!empty($arrErrors['email'])) echo '<img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt=""><br /><span class="errortext">'.$arrErrors['email'].'</span>'; ?>
                </p>

                

                <p>

                   <input type="submit" name="Login" value="Login">
                </p>                </th>
            </tr>
          </table>

          <div align="left"></div>
</form></td>
              <td width="247" valign="top" bgcolor="#FFFFFF"><h4 align="center">Features</h4>
                <p>Automatic Emails are here! Know what has sold fast! <span class="style53 style63">New Feature </span><span class="p7hvr"><a href="automaticemails.html">Having Trouble?</a> </span></p>
                <p>Photo Links - Updated with multiple views! <span class="style53 style63">New Feature</span> </p>
                <p>&quot;Click to SHARE&quot; <a href="refer.html">Lean more </a></p></td>
            </tr>
          </table>
        </blockquote>        </tr>
                  </table>                  </td>
                <td colspan="2" valign="middle" bgcolor="#FFFFFF"><p class="style2">&nbsp;</p>                  </td>
              </tr>
              <tr>
              <tr>
              <tr>
                <td colspan="4"><table width="850" border="0">
                  <tr>
                    <td width="212"></td>
                    <td width="624" align="left"><p><span class="style45 style93 style102"><span class="style93  style79"><span class="style108">News</span>:</span></span>
                        </fb:like>
                    </p></td>
                    <td width="0" align="right"><div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: 'your app id', status: true, cookie: true,
             xfbml: true});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
</script>
	<fb:like show_faces="false">
	<div align="right"></div></td>
                  </tr>
                  <tr>
                    <td align="left" valign="top"><p align="center"><script type="text/javascript"><!--
google_ad_client = "pub-8592012199740574";
/* GOTGOTestimonial2 */
google_ad_slot = "0599502899";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<script type="text/javascript"><!--
google_ad_client = "pub-8592012199740574";
/* GOTGOTestimonial2 */
google_ad_slot = "0599502899";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></p>
                     
			</td>
                    <td colspan="2" align="left" valign="top"><p align="left" class="style44">
                      <script type="text/javascript" src="http://www.send2page.com/script/Grandsononthego-news"></script></p>                      </td>
                  </tr>
                </table></td>
              </tr>
            </table><div id="footer">
		<p>&copy; 2009-2010 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
		</div></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>
