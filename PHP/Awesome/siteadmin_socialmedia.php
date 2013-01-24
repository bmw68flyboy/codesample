<?php
$page = "siteadmin_socialmedia.php";
include "siteadmin_header.php";

// GET VARS
$last_post_query = mysql_query("SELECT socialmedia_post_url, socialmedia_post_type, socialmedia_post_employee, socialmedia_post_date, socialmedia_post_id FROM socialmedia_posts ORDER BY socialmedia_post_id DESC LIMIT 1");
$last_post_result = mysql_fetch_array($last_post_query);
$last_post_url = $last_post_result[0];
$last_post_type =  $last_post_result[1];
$last_post_employee =  $last_post_result[2];
 
// ADD SOCIALMEDIA POST
if (!empty($_POST['socialmedia-submit'])) {
if($task == "add_post") {
  $post_date = $_POST['post_date'];
  $post_url = $_POST['post_url'];
  $post_type_value = $_POST['post_type'];
  if ($post_type_value == 1) {
  $post_type ='Facebook';
  }
  if ($post_type_value == 2) {
  $post_type ='Twitter';
  }
  if ($post_type_value == 3) {
  $post_type ='LinkedIn';
  }
  if ($post_type_value == 4) {
  $post_type ='Google Plus';
  }
  if ($post_type_value == 5) {
  $post_type ='Blog';
  }
  if ($post_type_value == 6) {
  $post_type ='Newsletter';
  }
  mysql_query("INSERT INTO socialmedia_posts (socialmedia_post_date, socialmedia_post_type, socialmedia_post_url, socialmedia_post_employee) VALUES ('{$post_date}', '{$post_type}', '{$post_url}', '".ucwords($employee_username)."')");  
  header("Location: siteadmin_socialmedia.php");
  }
}

// BEGIN PAGE OUTPUT
echo $head;

// OUTPUT JAVASCRIPT
?>
<script type='text/javascript'>
<!--

	function setCursorPosition(oInput,oStart,oEnd) {
	    if( oInput.setSelectionRange ) {
    	        oInput.setSelectionRange(oStart,oEnd);
            } 
            else if( oInput.createTextRange ) {
                var range = oInput.createTextRange();
                range.collapse(true);
                range.moveEnd('character',oEnd);
                range.moveStart('character',oStart);
                range.select();
            }
	}
	
  jQuery(function($){
    $('a[href^=http:\/\/]').click(function(){
        var url = '/outbound_link.php?to='+encodeURI($(this).attr('href'));
        window.open(url, '_blank');
        return false;
    });
  });
  
  function nl2br(target) {
    return target.replace(/\r\n|\r|\n/g,'<br>');
  }
    
  function insertText(ins,el) {
	    if (el.setSelectionRange) {
	        el.value = el.value.substring(0,el.selectionStart) + ins + el.value.substring(el.selectionStart,el.selectionEnd) + el.value.substring(el.selectionEnd,el.value.length);
			el.focus();
	    }
	    else if (document.selection && document.selection.createRange) {
	        el.focus();
	        var range = document.selection.createRange();
	        range.text = ins + range.text;
	    }
	}
	function setCursorPosition(oInput,oStart,oEnd) {
	    if( oInput.setSelectionRange ) {
    	        oInput.setSelectionRange(oStart,oEnd);
            } 
            else if( oInput.createTextRange ) {
                var range = oInput.createTextRange();
                range.collapse(true);
                range.moveEnd('character',oEnd);
                range.moveStart('character',oStart);
                range.select();
            }
	}
  
//-->
</script>

<style type="text/css">
  .reply-area { 
    background:#efefef;
    border:1px solid #ccc;
    padding:10px;
    margin-bottom:10px
  }
  .reply-container {
    overflow:hidden;
    width:775px;
    position:relative;
  }
  .reply-container .input-area {
    width:770px;
    float:left;
  }
  .tag-checkboxes {

    margin-bottom:10px
  }
  .attach {
    display:block;
    display:none;
  }
  .attach.show {
    display:block;
  }
  .attachments {
    margin-bottom:10px;
    border-top:1px solid #ccc;

  }
  .submit-area input{
   margin-left:15px; 
  }
  .submit-area {
    background:#F1F1F1;
    padding:10px;
    border:1px solid #ccc;
  }
</style>

<div class="reply-container" style="text-align: left;">
	
	<div class="input-area">
		<form action='<?php echo $_SERVER['REQUEST_URI'] ?>' method='post' name='info' id='info' onsubmit='return validate()' style='overflow:hidden'>
						
			<br /><br />
			<div class="submit-area">
			<table cellpadding='0' cellspacing='0' width='75%'>
				<tr>
					<td class='box'>
						<div style='font-size:14pt;'>SocialMedia Post </div>
						<br />
						<form action='siteadmin_socialmedia.php' method='POST'>
						<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;Date<br /><input type='text' name='post_date' size='30' class='text' maxlength='100' value='<? echo time()?>'><a href="http://www.onlineconversion.com/unix_time.htm">Time Converter</a>	
						<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;URL<br /><input type='text' name='post_url' size='30' class='text' maxlength='100'> 
						<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;Platform Type<br />&nbsp;&nbsp;&nbsp;&nbsp;<select name="post_type"> 
							<option value="">Select Message</option>
							<option value="1">Facebook </option>
							<option value="2">Twitter</option>
							<option value="3">LinkedIn</option>
							<option value="4">Google Plus</option>
							<option value="5">Blog</option>
							<option value="6">Newsletter</option>
						</select>
						<br /><br /><a href="<? echo $last_post_url?>" target="blank">Last Post: <? echo $last_post_type?> by <? echo $last_post_employee?></a>
						<br /><br /><input type='submit' name="socialmedia-submit" value='Add Post' class='button'>
						<input type='hidden' name='task' value='add_post'>					
						</form>
						</div>
							
					</td>
				</tr>
			</table>
			</div>
		</form>
	</div>
	
</div>
