<?php

//Ensure user is truely logged in and connect to the database

//require_once("backauth.php");
require_once('config.php');
require_once('opendb.php');
define('FPDF_FONTPATH','font/');
require('fpdf.php');

//Function to sanitize values received from the form. Prevents SQL injection

function clean($str) 
{
  $str = @trim($str);
  if(get_magic_quotes_gpc()) 
  {
    $str = stripslashes($str);
  }
  return mysql_real_escape_string($str);
}

//Convert the entries from the login form to lowercase and clean them to prevent SQL injection	

$repid = $_POST['repid'];
$reppass = $_POST['reppass'];

$allowqry = "SELECT invoice FROM reps WHERE repid = '$repid'";
$allow=mysql_query($allowqry); 

if($allow) 
	{
	  $allowrep=mysql_result($allow,0,"invoice");
	  if($allowrep == "Y") 
	  {
	    
 if (!empty($_POST['Search'])) 
    {   
   
   
    if (!empty($_POST['Search'])) 
    {
      $errflg = 0;

        if ($_POST['lastdate']=='' || !is_numeric(substr(clean($_POST['lastdate']),0,2)) || substr(clean($_POST['lastdate']),2,1) !="/" || !is_numeric(substr(clean($_POST['lastdate']),3,2)) || substr(clean($_POST['lastdate']),5,1) !="/" || !is_numeric(substr(clean($_POST['lastdate']),6,4)))
        {
	       $errflg = 1;
		  }
		  if($_POST['firstdate']=='' || !is_numeric(substr(clean($_POST['firstdate']),0,2)) || substr(clean($_POST['firstdate']),2,1)!="/" || !is_numeric(substr(clean($_POST['firstdate']),3,2)) || substr(clean($_POST['firstdate']),5,1) !="/" || !is_numeric(substr(clean($_POST['firstdate']),6,4)))
        {
          $errflg = $errflg + 2;
        }

      //Convert the dates from the form into the proper format for a SQL query
      
      $fromdate = $_POST['firstdate'];
      $todate = $_POST['lastdate'];
          
      $firstdate = substr(clean($_POST['firstdate']),6,4).'-'.substr(clean($_POST['firstdate']),0,2).'-'. substr(clean($_POST['firstdate']),3,2);
	   $lastdate =substr(clean($_POST['lastdate']),6,4).'-'.substr(clean($_POST['lastdate']),0,2).'-'. substr(clean($_POST['lastdate']),3,2);
      $custnum = $_POST['custnum'];
      $today = date("m/d/Y");
      
      //Perform the SQL query depending on which radio button was selected                         

      $qry="SELECT * FROM inventory WHERE TRIM(Custnum) = '$custnum' AND SellDate !='' AND SellDate BETWEEN '$firstdate' AND '$lastdate'";
      $cust ="SELECT firstname,lastname,streetaddress,city,state,zip FROM customer WHERE TRIM(Custnum)='$custnum'";

      $result1=mysql_query($qry);
      $result2=mysql_query($cust);

      //Check whether the query was successful or not

      if($result1 && $result2)
      {
        if(mysql_num_rows($result1) >= 1 && mysql_num_rows($result2)>=1)
	     {
	       //Search Successful, determine the number of rows of data pulled from the query.

          

          // Assign inital values to the variables for the various prices.    
               
           $i=0;
           $f2sum = 0;
           $f3sum = 0;
           $f4sum = 0;
           $f5sum = 0;
           $num=mysql_numrows($result1);   

 
             $custfname=mysql_result($result2,0,"firstname");
             $custlname=mysql_result($result2,0,"lastname");
             $custstreet=mysql_result($result2,0,"streetaddress");
             $custcity=mysql_result($result2,0,"city");
             $custstate=mysql_result($result2,0,"state");
             $custzip=mysql_result($result2,0,"zip");
             
             
             // Create the PDF File
             
              class PDF extends FPDF
              {
                //Page header
                function Header()
                {
                  //Logo
                  $this->Image('Logo.jpg',0.1,0.1,4,1.36);
                  //Arial bold 20
                  $this->SetFont('Arial','B',20);
                  //Title
                  $this->Cell(0,0,'Receipt',0,0,'R');

                }

                //Page footer
                function Footer()
                {
                  //Position at 1.5 cm from bottom
                  $this->SetY(-0.75);
                  //Arial italic 8
                  $this->SetFont('Arial','',8);
                  //Page number
                  $this->Cell(0,0,'Page '.$this->PageNo().'/{nb}',0,0,'C');
                  $this->Ln(0.25);
                  $this->Cell(0,0,'GrandsonONtheGO, LLC.  13985 W 58th Place, Arvada, CO 80004  GrandsonONtheGO.com ',0,0,'C');
                }
                
                function FancyTable($header)
                {
                  global $i,$f1,$f2,$f3,$f4,$f5,$f2sum,$f3sum,$f4sum,$f5sum,$result1,$num;
                  
                  //Colors, line width and bold font
                  $this->SetFillColor(85,128,180);
                  $this->SetTextColor(255);
                  $this->SetDrawColor(0,0,153);
                  $this->SetLineWidth(.013);
                  $this->SetFont('Arial','B',9);
                  
                  //Header
                  $w=array(3.4,1.0,1.0,1.0,1.0);
                  for($j=0;$j<count($header);$j++)
                      $this->Cell($w[$j],0.20,$header[$j],1,0,'C',true);
                  $this->Ln(0.20);
                  
                  //Color and font restoration
                  $this->SetFillColor(224,235,255);
                  $this->SetTextColor(0);
                  $this->SetFont('Arial','',9);
                  
                  //Data
                 
                  $fill=false;
                  while ($i < $num) 
                  {
                    $f1=mysql_result($result1,$i,"Description");
                    $f2=mysql_result($result1,$i,"SellPrice");
                    $f3=mysql_result($result1,$i,"SellerFees");
                    $f4=mysql_result($result1,$i,"Commission");
                    $f5=mysql_result($result1,$i,"NetClient");
                      
                    // Keep a running summation of the various prices so the totals can be displayed
                
                    $f2sum = $f2sum + $f2;
                    $f3sum = $f3sum + $f3;
                    $f4sum = $f4sum + $f4;
                    $f5sum = $f5sum + $f5;                         
             
                    $this->Cell($w[0],0.20,$f1,'LR',0,'L',$fill);
                    $this->Cell($w[1],0.20,number_format($f2,2,'.',','),'LR',0,'R',$fill);
                    $this->Cell($w[2],0.20,number_format($f3,2,'.',','),'LR',0,'R',$fill);
                    $this->Cell($w[3],0.20,number_format($f4,2,'.',','),'LR',0,'R',$fill);
                    $this->Cell($w[4],0.20,number_format($f5,2,'.',','),'LR',0,'R',$fill);
                    $this->Ln(0.20);
                    $fill=!$fill;
                  
                    $i++;
                    
                  }   
                  
                  $this->SetFont('Arial','B',9);
                  $this->Cell($w[0],0.20,'TOTALS:','LR',0,'R',$fill);
                  $this->Cell($w[1],0.20,number_format($f2sum,2,'.',','),'LR',0,'R',$fill);
                  $this->Cell($w[2],0.20,number_format($f3sum,2,'.',','),'LR',0,'R',$fill);
                  $this->Cell($w[3],0.20,number_format($f4sum,2,'.',','),'LR',0,'R',$fill);
                  $this->Cell($w[4],0.20,number_format($f5sum,2,'.',','),'LR',0,'R',$fill);
                  $this->Ln(0.20);       
                  $this->Cell(array_sum($w),0,'','T');
                  $this->SetFont('Arial','',9);
                
                }
              }
  
              $pdf=new PDF('P','in','Letter');
              $header=array('Item Sold','Sold Amt','Seller Fees','Commissions','Net Client'); 
              $pdf->AliasNbPages();
              $pdf->AddPage();
              $pdf->SetMargins(0.5, 0.5 , 0.5);
              $pdf->SetFont('Arial','',9);
              $pdf->Ln(1.5);
              $pdf->Cell(0,0,'GrandsonONtheGO, LLC',0,1,'L');
              $pdf->Ln(0.15);
              $pdf->Cell(0,0,'13985 W 58th Place',0,1,'L');
              $pdf->Ln(0.15);
              $pdf->Cell(0,0,'Arvada, CO 80004',0,1,'L');
              $pdf->Ln(0.15);
              $pdf->Cell(0,0,'Phone (303) 484-8332',0,0,'L');
              $pdf->Cell(0,0,'Dates: ' .$fromdate.' to '.$todate,0,1,'R');
              $pdf->Ln(0.15);
              $pdf->Cell(0,0,'Email contact@grandsononthego.com',0,1,'L');
              $pdf->Cell(0,0,'Receipt Date: ' .$today,0,1,'R');
              $pdf->Ln(0.50);
              $pdf->Cell(0,0,'TO:',0,0,'L');
              $pdf->SetX(4.25);
              $pdf->Cell(0,0,'FOR:',0,1,'L');
              $pdf->Ln(0.15);
              $pdf->SetX(0.75);
              $pdf->Cell(0,0,$custfname.' '.$custlname,0,0,'L');
              $pdf->SetX(4.60);
              $pdf->Cell(0,0,'Online Sales',0,1,'L');
              $pdf->Ln(0.15);
              $pdf->SetX(0.75);
              $pdf->Cell(0,0,$custstreet,0,1,'L');
              $pdf->Ln(0.15);
              $pdf->SetX(0.75);
              $pdf->Cell(0,0,$custcity.' '.$custstate.' '.$custzip,0,1,'L');
              $pdf->Ln(1.0);
              $pdf->FancyTable($header);       
              $pdf->Output("receipt.pdf", "D");
               
                                 
		    }
          else 
          {
	         //No results returned
	           
	         echo '<P class="h1err">No Results Returned</P>';
?>
	         <!-- Display the footer -->
	               
	         <BR /><BR /><BR />
 	         <div id="footer">
		        <p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
            </div>
		    </div>
       </body>
       </html>
      
<?php	                
	    exit();  
      }
	 }
	 else 
	 {
	   die("Query failed");

?>
      <BR /><BR /><BR />
      <div id="footer">
        <p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
      </div>
      </div>
      </body>
      </html>
<?php	                
	   exit();		                    
	  }
   }    
   else
   {
     echo '<P class = "h1err"> Errors in the data entry</P>';

?>
	  <BR /><BR /><BR />
 	  <div id="footer">
		 <p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
     </div>
     </div>
     </body>
     </html>
<?php	                
	   exit();
  }  
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title>GrandsonONtheGO--Track Your Sales and Unsold Items</title>
<link rel="stylesheet" href="styles.css" type="text/css" />

<!-- Utilize JavaScript to display the GrandsonONtheGO format -->

<!-- Set the styles specific to this web page that are not included in the CSS stylesheet -->

<style type="text/css">

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

.datetext1 {
  padding-left: 145px;
  font: bold smaller sans-serif; size: 10px;
}
.datetext2{
padding-left: 35px;
  font: bold smaller sans-serif; size: 10px;
}
.h1err {
	color: #ff6600;
   text-align:center;
	margin: 0px 0px 5px;
	padding: 0px 0px 3px;
	font: bold 18px Verdana, Arial, Helvetica, sans-serif;
	border-bottom: 1px dashed #E6E8ED;
}
.outputdata{
   font-size: 0.9em;
}

</style>

</head>

<body>

<!-- Display the GrandsonONtheGO header bar -->

<div id="container">
  <div id="header">
    <h1>&nbsp;</h1>
  </div>
  <div id="menuwrapper"> 
<ul id="p7menubar"> 
<li><a href="index.html">Home</a></li> 
<li><a href="training.html" target="_blank" class="trigger">Training</a> 
<li><a href="http://mail.grandsononthego.com" target="_blank" class="trigger">Connect</a> 
<ul> 
<li><a href="deals.html" target="_blank" class="trigger">Weekly Deals</a> 
<li><a href="whatwevesold.html" target="_blank" class="trigger">What We've Sold</a> 
<li><a href="http://www.facebook.com/grandsononthego" target="_blank" class="trigger">Facebook</a>  
<li><a href="http://www.twitter.com/grandsononthego" target="_blank" class="trigger">Twitter</a> 
</ul> 
</li> 
<li><a class="trigger" href="rep-index.php">Rep-Index</a> 
<ul> 
<li><a href="DLContract.html" target="_blank" class="trigger">Contracts</a> 
<li><a href="https://mail.google.com/a/grandsononthego.com/" target="_blank" class="trigger">Mail</a>  
<li><a href="store.html" target="_blank" class="trigger">Store</a>  
</ul> 
</li> 
</ul> 
 <form name="back" method="post" action="rep-index.php">
	             <input name="repid" type="hidden" id="repid" value="<?php echo $repid; ?>"/>
	             <input name="reppass" type="hidden" id="reppass" value="<?php echo $reppass; ?>"/>
                 </p>
                 <input type="submit" name="returnbutton" value="Return to main Menu">
 </form>
</div> 

       
  <p>
  <?php
      
      // Create a variable to determine if there are errors and assign it a value based on the error.
      //Only validate if the Submit button was clicked.
      
    if (!empty($_POST['Search'])) 
    {
      $errflg = 0;

        if ($_POST['lastdate']=='' || !is_numeric(substr(clean($_POST['lastdate']),0,2)) || substr(clean($_POST['lastdate']),2,1) !="/" || !is_numeric(substr(clean($_POST['lastdate']),3,2)) || substr(clean($_POST['lastdate']),5,1) !="/" || !is_numeric(substr(clean($_POST['lastdate']),6,4)))
        {
	       $errflg = 1;
		  }
		  if($_POST['firstdate']=='' || !is_numeric(substr(clean($_POST['firstdate']),0,2)) || substr(clean($_POST['firstdate']),2,1)!="/" || !is_numeric(substr(clean($_POST['firstdate']),3,2)) || substr(clean($_POST['firstdate']),5,1) !="/" || !is_numeric(substr(clean($_POST['firstdate']),6,4)))
        {
          $errflg = $errflg + 2;
        }
      
?>
    
  <!--Re-display the form after the submit button is pressed and include the last entries made -->
  </p>
  <p>&nbsp;</p>
  <form name="searchform" method="post">

        <p>&nbsp;</p>
        <table width="550" height="56" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <th width="550" bgcolor="#CCCCCC" scope="col">
            <span class="style51">
            <P />
            <p align="left">     
               Customer ID# &nbsp;<input name="custnum" type="text" id="custnum" value="<?php if(isset($_POST['Search'])) echo $_POST['custnum']; ?>" ><BR />    
            </p>
            <p>
              <label for="firstdate" id="fdatelabel">Search Dates from</label>
              <input maxlength="10" size="10" name="firstdate" type="text" id="firstdate" value="<?php if(isset($_POST['Search'])) echo $_POST['firstdate']; ?>">
              <?php if ($errflg >= 2)  echo '<img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt="" id="fdateerror">'; ?>

              <label for="lastdate" id="ldatelabel">to</label>
              <input maxlength="10" size="10" name="lastdate" type="text" id="lastdate" value="<?php if(isset($_POST['Search'])) echo $_POST['lastdate']; ?>">
              <?php if ($errflg == 1 || $errflg == 3) echo '<img src="images/triangle_error.gif" width="16" height="16" hspace="5" alt="" id="ldateerror">'; ?>

              <BR />
              <span class="datetext1"><label id="dateformat">mm/dd/yyyy</span><span class="datetext2">mm/dd/yyyy</span></label>
            </p>
     
            <input name="repid" type="hidden" id="repid" value="<?php echo $_POST['repid']; ?>">
            <input name="reppass" type="hidden" id="reppass" value="<?php echo $_POST['reppass']; ?>">

            <P>    
              <input type="Submit" name="Search" value="Search"></P>
            </th>
        </tr>
      </table>
    <blockquote>
      <blockquote>
        <p><?php
          
      // If there are no errors present when the form was submitted, perform the query

       
?>
      </p>
      </blockquote>
        </blockquote>
      </form>
      <p><BR />
        <BR />
          </p><div id="container">
      <div id="footer">
    <p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
  </div>
</div></div>
  </body>
  </html>

<?php 
    exit();
}
?>

<!--Create the inital data entry form displayed prior to submitting.  Code is only run once at the beginning -->                 
                    
<form name="searchform" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);  ?>" >
  <table width="550" height="56" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <th width="550" bgcolor="#CCCCCC" scope="col">
        <span class="style51"><P />
                
        <p align="left">    
          Customer ID# &nbsp;<input name="custnum" type="text" id="custnum" value="<?php if(isset($_POST['Search'])) echo $_POST['custnum']; ?>" ><BR />    
          
        </p>
  
        <p>
          <label for="firstdate" id="fdatelabel">Search Dates from</label>
          <input maxlength="10" size="10" name="firstdate" type="text" id="firstdate" value="<?php if(isset($_POST['Search'])) echo $_POST['firstdate']; ?>">
    
          <label for="lastdate" id="ldatelabel">to</label>
          <input maxlength="10" size="10" name="lastdate" type="text" id="lastdate" value="<?php if(isset($_POST['Search'])) echo $_POST['lastdate']; ?>">
    
          <BR />
          <span class="datetext1">
          <label id="dateformat">mm/dd/yyyy</span><span class="datetext2">mm/dd/yyyy</span></label>
        </p>

          <input name="repid" type="hidden" id="repid" value="<?php echo $_POST['repid']; ?>">
          <input name="reppass" type="hidden" id="reppass" value="<?php echo $_POST['reppass']; ?>">

        <P>
          <input type="submit" name="Search" value="Search">
        </P>
      </th>
    </tr>
  </table>
</form>

<!-- Include the initial footer.  Code only ran once at the beginning before form submitted -->

<BR /><BR /><BR />
</div><div id="container">
<div id="footer">
  <p>&copy; 2009 <a href="contact.html">GrandsonONtheGO, LLC</a></p>
</div></div>
			
</div>
</body>
</html>

<?php

 }
	  else 
	  {
		  echo '<form id="auto" method="post" action="repaccess-failed.php">';
        echo '<input name="repid" type="hidden" id="repid" value="'.$repid.'">';
        echo '<input name="reppass" type="hidden" id="reppass" value="'.$reppass.'">';
        echo '</form>';
        echo '<script type="text/javascript"> document.forms.auto.submit(); </script>';
	  }
	}
	else 
	{
	  die("Query failed.  Unable to authorize access to this feature.");
	}
	
?>