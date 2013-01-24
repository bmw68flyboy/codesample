<?php
	
   //Start session
	
	session_start();
	
	//Connect to the database
	
	require_once('config.php');
	require_once('opendb.php');
	
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
	
	//Sanitize the POST values submitted through the login form to prvent SQL injection.
	
	$firstname = strtolower(mysql_real_escape_string($_POST['firstname']));
	$lastname = strtolower(mysql_real_escape_string($_POST['lastname']));
	$email = strtolower(mysql_real_escape_string($_POST['email']));
	
	//Create query
	
	$qry="SELECT * FROM customer WHERE LOWER(firstname)='$firstname' AND LOWER(lastname)='$lastname' AND LOWER(email)='$email' ";
	
	$result=mysql_query($qry);
	
	//Check whether the query was successful or not
	
	if($result) 
	{
	  if(mysql_num_rows($result) == 1) 
	  {
	    //Login Successful
		 $_SESSION['SESS_FIRST_NAME'] = ucfirst($firstname);
		 echo '<form id="auto" method="post" action="http://www.grandsononthego.com/member-index.php">';
       echo '<input name="firstname" type="hidden" id="firstname" value="'.$_POST['firstname'].'">';
       echo '<input name="lastname" type="hidden" id="lastname" value="'.$_POST['lastname'].'">';
       echo '<input name="email" type="hidden" id="email" value="'.$_POST['email'].'">';
       echo '</form>';
       echo '<script type="text/javascript"> document.forms.auto.submit(); </script>';
       exit();
	  }
	  else 
	  {
		 //Login failed, goto the login-failed page
		 header("location: http://www.grandsononthego.com/login-failed.php");
		 exit();
	  }
	}
	else 
	{
	  die("Query failed");
	}

?>