<?php


//  THIS FILE CONTAINS FUNCTIONS USED ON SOCIALENGINE.NET
//  FUNCTIONS IN THIS FILE:
//    href()
//    security()
//    license_generate()
//    random_string()
//    is_email_address()
//    parse_email_parts()
//    email_id_generate()
//    time_since()
//    get_search_phrase()
//    is_a_bot()
//    before()
//    after()
//    between()
//    make_summary()
//    get_source_from_email()
//    get_queryterms_from_email()
//    truncate()


function is_ssl() {
    return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
}


function link_scheme() {
    return is_ssl() ? 'https':'http';
}


// THIS FUNCTION BUILDS A SITE URL BASED ON PARAMETERS
// INPUT: $tag (OPTIONAL) REPRESENTING THE PAGE TAG - EMPTY TAG WILL GO TO HOMEPAGE
//	  $param (OPTIONAL) REPRESENTING THE ADDITIONAL QUERYSTRING TO APPEND TO THE URL
//	  $local_link (OPTIONAL) REPRESENTING THE ADDITIONAL #LINK FOR LOCAL LINKING
// OUTPUT: A STRING CONTAINING A URL TO THE DESIRED LOCATION
function href($tag = '', $param = '', $local_link = '') {

	$built_url = link_scheme() . "://".$_SERVER['SERVER_NAME']."/";
	if(array_key_exists($tag, $GLOBALS['urls'])) { $built_url .= $GLOBALS['urls'][$tag]; }
	if($param != '') { $built_url .= "?".$param; }
	if($local_link != '') { $built_url .= "#".$local_link; }

	return str_replace("&", "&amp;", $built_url);

} // END href() FUNCTION






// THIS FUNCTION ENSURES THAT ALL VALUES IN THE INPUT HAVE BEEN ESCAPED
// INPUT: $value REPRESENTING THE VALUE OR ARRAY OF VALUES TO ESCAPE
// OUTPUT: A STRING OR ARRAY OF ESCAPED VALUES
function security($value) {

	if(is_array($value)) {
	  $value = array_map('security', $value);
	} else {
	  if(!get_magic_quotes_gpc()) {
	    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	  } else {
	    $value = htmlspecialchars(stripslashes($value), ENT_QUOTES, 'UTF-8');
	  }
	  $value = str_replace("\\", "\\\\", $value);
	}
	return $value;

} // END security() FUNCTION






// THIS FUNCTION GENERATES A LICENSE KEY
// INPUT: $is_eval (OPTIONAL) REPRESENTING WHETHER THE LICENSE SHOULD BE AN EVAL OR TRUE LICENSE
// OUTPUT: A 16 DIGIT LICENSE KEY
function license_generate($is_eval = 0) {

	$license_1 = random_string(4, 1);
	$license_2 = random_string(4, 1);
	$license_3 = random_string(4, 1);

	if($is_eval == 1) {
	  $license_4 = substr($license_2, 0, 1)*substr($license_2, 1, 1)*substr($license_2, 2, 1)*substr($license_2, 3, 1);
	  $duplicate_query = "SELECT trial_id FROM trials WHERE trial_key=";
	} else {
	  $license_4 = substr($license_3, 0, 1)*substr($license_3, 1, 1)*substr($license_3, 2, 1)*substr($license_3, 3, 1);
	  $duplicate_query = "SELECT user_license_id FROM user_licenses WHERE user_license_key=";
	}

	$license_4 = sprintf("%04u", $license_4);
	$license = $license_1."-".$license_2."-".$license_3."-".$license_4;

	// CHECK FOR DUPLICATE
	if(mysql_num_rows(mysql_query($duplicate_query."'$license'")) != 0) {
	  $license = license_generate($is_eval);
	}

	return $license;

} // END license_generate() FUNCTION






// THIS FUNCTION GENERATES A RANDOM STRING
// INPUT: $len (OPTIONAL) REPRESENTING THE LENGTH OF THE STRING
//	  $alphanum (OPTIONAL) REPRESENTING 0 FOR AN ALPHANUMERIC AND 1 FOR A NUMERIC STRING
// OUTPUT: A RANDOMLY GENERATED STRING
function random_string($len = 8, $alphanum = 0) {

	$rand_string = "";
	if($alphanum == 1) { $string_match = "[0-9]"; } else { $string_match = "[a-z0-9]"; }
	for($i=0;$i<$len;$i++) {
	  $char = chr(rand(48,122));
	  while(!ereg($string_match, $char)) {
	    if($char == $lchar) { continue; }
	    $char = chr(rand(48,90));
	  }
	  $rand_string .= $char;
	  $lchar = $char;
	}
	return $rand_string;

} // END random_string() FUNCTION






// THIS FUNCTION CHECKS FOR THE VALIDITY OF AN EMAIL ADDRESS
// INPUT: $email REPRESENTING A POTENTIAL EMAIL ADDRESS
// OUTPUT: A BOOLEAN REPRESENTING WHETHER THE SPECIFIED EMAIL IS VALID OR NOT
function is_email_address($email) {

	$regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
	if(!preg_match($regexp, $email) ) { return false; }
	return true;

} // END is_email_address() FUNCTION






// THIS FUNCTION PARSES EMAIL PARTS
// INPUT: $mbox
//	  $message_id
//	  $structure
//	  $num
//	  $partsarray
// OUTPUT:
function parse_email_parts($mbox, $message_id, $structure, $num, &$partsarray) {
	$numparts = count($structure->parts);
	if($numparts > 1) {
	  if($num != "") { $num .= "."; }
	  foreach($structure->parts as $part_num=>$part) {
	    parse_email_parts($mbox, $message_id, $part, $num.($part_num+1), $partsarray);
	  }
	} else {
	  if($num == "") { $part = imap_body($mbox, $message_id); } else { $part = imap_fetchbody($mbox, $message_id, $num); }
	  if($structure->type != 0) {
	    if($structure->encoding == 4) {
	      $part = quoted_printable_decode($part);
	    } elseif($structure->encoding == 3) {
	      $part = base64_decode($part);
	    }
	    $filename="";
	    if(count($structure->dparameters) > 0) {
	      foreach($structure->dparameters as $dparam) {
	        if((strtoupper($dparam->attribute) == "NAME") || (strtoupper($dparam->attribute) == "FILENAME")) {
	          $filename = $dparam->value;
	        }
	      }
	    }
	    if($filename == "") {
	      if(count($structure->parameters) > 0) {
	        foreach($structure->parameters as $param) {
	          if((strtoupper($param->attribute)=='NAME') || (strtoupper($param->attribute)=='FILENAME')) {
	            $filename=$param->value;
	          }
	        }
	      }
	    }
	    if($filename != "") {
	      if($structure->type == "0") {
	        $type = "text";
	      } elseif($structure->type == "1") {
	        $type = "multipart";
	      } elseif($structure->type == "2") {
	        $type = "message";
	      } elseif($structure->type == "3") {
	        $type = "application";
	      } elseif($structure->type == "4") {
	        $type = "audio";
	      } elseif($structure->type == "5") {
	        $type = "image";
	      } elseif($structure->type == "6") {
	        $type = "video";
	      } else {
	        $type = "other";
	      }
	      $mime = $type."/".strtolower($structure->subtype);
	      $partsarray[$num] = array('type'=>'ATTACHMENT', 'mime'=>$mime, 'filename'=>$filename, 'binary'=>$part, 'id'=>$structure->id);
	    }
	  } else {
	    if($structure->encoding == 4) {
	      $part = quoted_printable_decode($part);
	    } elseif($structure->encoding == 3) {
	      $part = base64_decode($part);
	    }
	    if(strtoupper($structure->subtype) == "PLAIN") { $part = str_replace("\n", "<br>", $part); }
	    if(strtoupper($structure->subtype) == "HTML") {
	      $part = preg_replace("/(<body([^>]*)>)/i", "<body>", $part);
	      $part = preg_replace("/(<\/body>)/i", "</body>", $part);
	      if(strstr($part, "<body>") === false) { $part = "<body>".$part; }
	      if(strstr($part, "</body>") === false) { $part = $part."</body>"; }
	      $part = between("<body>", "</body>", $part);
	    }
	    $partsarray[$num] = array('type'=>$structure->subtype, 'string'=>$part);
	  }
	}
        unset($part);
	return;

} // END parse_email_parts() FUNCTION






// THIS FUNCTION GENERATES AN EMAIL REFERENCE ID
// INPUT:
// OUTPUT: A 9-DIGIT EMAIL REF ID
function email_id_generate() {

	$ref_id = random_string(9, 1);

	// CHECK FOR DUPLICATE
	if(mysql_num_rows(mysql_query("SELECT NULL FROM emails WHERE email_ref_id='$ref_id'")) != 0) {
	  $ref_id = email_id_generate();
	} elseif(substr($ref_id, 0, 1) == 0) {
	  $ref_id = email_id_generate();
	}

	return $ref_id;

} // END email_ref_generate() FUNCTION






// RETURNS THE TIME SINCE SPECIFIED TIMESTAMP
// INPUT: $time REPRESENTING A TIMESTAMPE
// OUTPUT: A STRING CONTAINGIN THE TIME SINCE THE TIMESTAMP IN ENGLISH
function time_since($time) {

	$now = time();
	$now_day = date("j", $now);
	$now_month = date("n", $now);
	$now_year = date("Y", $now);

	$time_day = date("j", $time);
	$time_month = date("n", $time);
	$time_year = date("Y", $time);
	$time_since = "";

	switch(TRUE) {

	  case ($now-$time < 60):
	    // RETURNS SECONDS
	    $seconds = $now-$time;
	    $time_since = "$seconds seconds ago";
	    break;
	  case ($now-$time < 3600):
	    // RETURNS MINUTES
	    $minutes = round(($now-$time)/60);
	    $time_since = "$minutes minutes ago";
	    break;
	  case ($now-$time < 86400):
	    // RETURNS HOURS
	    $hours = round(($now-$time)/3600);
	    $time_since = "$hours hours ago";
	    break;
	  case ($now-$time < 1209600):
	    // RETURNS DAYS
	    $days = round(($now-$time)/86400);
	    $time_since = "$days days ago";
	    break;
	  case (mktime(0, 0, 0, $now_month-1, $now_day, $now_year) < mktime(0, 0, 0, $time_month, $time_day, $time_year)):
	    // RETURNS WEEKS
	    $weeks = round(($now-$time)/604800);
	    $time_since = "$weeks weeks ago";
	    break;
	  case (mktime(0, 0, 0, $now_month, $now_day, $now_year-1) < mktime(0, 0, 0, $time_month, $time_day, $time_year)):
	    // RETURNS MONTHS
	    if($now_year == $time_year) { $subtract = 0; } else { $subtract = 12; }
	    $months = round($now_month-$time_month+$subtract);
	    $time_since = "$months months ago";
	    break;
	  default:
	    // RETURNS YEARS
	    if($now_month < $time_month) {
	      $subtract = 1;
	    } elseif($now_month == $time_month) {
	      if($now_day < $time_day) { $subtract = 1; } else { $subtract = 0; }
	    } else {
	      $subtract = 0;
	    }
	    $years = $now_year-$time_year-$subtract;
	    $time_since = "$years years ago";
	    break;
	}

	if($time_since == "0 years ago") {
	  $time_since = "";
	}

	return $time_since;

} // END time_since() FUNCTION






// GETS THE KEYWORD PHRASE FROM ANY SEARCH ENGINE REFERRAL URL
// INPUT: $referer REPRESENTING THE REFERRAL URL
// OUTPUT: A STRING REPRESENTING THE SEARCH KEYWORD PHRASE USED
function get_search_phrase($referer) {

	$key_start = 0;
	$search_phrase = "";

	// used by dogpile, excite, webcrawler, metacrawler
	if (strpos($referer, '/search/web/') !== false) $key_start = strpos($referer, '/search/web/') + 12;

	// used by chubba
	if (strpos($referer, 'arg=') !== false) $key_start = strpos($referer, 'arg=') + 4;

	// used by dmoz
	if (strpos($referer, 'search=') !== false) $key_start = strpos($referer, 'query=') + 7;

	// used by looksmart
	if (strpos($referer, 'qt=') !== false) $key_start = strpos($referer, 'qt=') + 3;

	// used by scrub the web
	if (strpos($referer, 'keyword=') !== false) $key_start = strpos($referer, 'keyword=') + 8;

	// used by overture, hogsearch
	if (strpos($referer, 'keywords=') !== false) $key_start = strpos($referer, 'keywords=') + 9;

	// used by mamma, lycos, kanoodle, snap, whatuseek
	if (strpos($referer, 'query=') !== false) $key_start = strpos($referer, 'query=') + 6;

	// don't allow encrypted key words by aol
	if (strpos($referer, 'encquery=') !== false) $key_start = 0;

	// used by ixquick
	if (strpos($referer, '&query=') !== false) $key_start = strpos($referer, '&query=') + 7;

	// used by aol
	if (strpos($referer, 'qry=') !== false) $key_start = strpos($referer, 'qry=') + 4;

	// used by yahoo, hotbot
	if (strpos($referer, 'p=') !== false) $key_start = strpos($referer, 'p=') + 2;

	// used by google, msn, alta vista, ask jeeves, all the web, teoma, wisenut, search.com
	if (strpos($referer, 'q=') !==  false) $key_start = strpos($referer, 'q=') + 2;

	// if present, get the search phrase from the referer
	if ($key_start > 0) {
	  if (strpos($referer, '&', $key_start) !== false) {
	    $search_phrase = substr($referer, $key_start, (strpos($referer, '&', $key_start) - $key_start));

    	  } elseif (strpos($referer, '/search/web/') !== false) {

	    if (strpos($referer, '/', $key_start) !== false) {
	      $search_phrase = urldecode(substr($referer, $key_start, (strpos($referer, '/', $key_start) - $key_start)));
	    } else {
              $search_phrase = urldecode(substr($referer, $key_start));
	    }

	  } else {
	    $search_phrase = substr($referer, $key_start);
	  }
	}

	$search_phrase = urldecode($search_phrase);
	return $search_phrase;

} // END get_search_phrase() FUNCTION






// CHECKS TO SEE IF A VISITOR IS JUST A SEARCH ROBOT
// INPUT:
// OUTPUT: A STRING REPRESENTING WHICH ROBOT THE VISITOR IS
function is_a_bot() {

	$bot = "";
	if(eregi("google", $_SERVER['HTTP_USER_AGENT'])) {
	  $bot = "googlebot";
	} elseif(eregi("msn", $_SERVER['HTTP_USER_AGENT'])) {
	  $bot = "msnbot";
	} elseif(eregi("yahoo", $_SERVER['HTTP_USER_AGENT'])) {
	  $bot = "yahoobot";
	}
	return $bot;

} // END is_a_bot() FUNCTION






// RETURNS BEFORE, AFTER, AND BETWEEN FOR STRINGS
function before($this, $inthat) {
	return substr($inthat, 0, strpos($inthat, $this));
}
function after($this, $inthat) {
	if(!is_bool(strpos($inthat, $this))) {
	  return substr($inthat, strpos($inthat,$this)+strlen($this));
	} else {
	  return false;
	}
}
function between($this, $that, $inthat) {
	return before($that, after($this, $inthat));
}
// END before(), after(), and between() FUNCTIONS






// CREATES A SUMMARY FROM AN EMAIL MESSAGE
// INPUT: $body REPRESENTING THE BODY OF THE EMAIL
// OUTPUT: A 200 CHARACTER OR LESS SUMMARY OF THE EMAIL
function make_summary($body) {
	$body = trim(strip_tags($body));
	$startPos = 0;

	// IF THIS IS AN INITIAL EMAIL, GET RID OF HEADER DATA
	if(strpos($body, "Name:") !== false && strpos($body, "Email:") !== false && strpos($body, "IP Address") !== false && strpos($body, "Referrer") !== false && strpos($body, "--------------------") !== false) {
	  $startPos = strpos($body, "--------------------") + 20;
	}elseif(strpos($body, "Name:") !== false && strpos($body, "Email:") !== false) {
      $startPos = strpos($body, "\r\r");
    }


	// REMOVE CARRIAGE RETURNS
	$body = str_replace(chr(13), " ",$body);

	// TRUNCATE
	if(strlen($body) > 200) { $body = substr($body, $startPos, 200)." ... "; }
    else { $body = substr($body, $startPos); }

	return $body;
} // END make_summary() FUNCTION

// PULLS THE SOURCE FROM AN EMAIL THAT CONTAINS THE STRING "Source: abcedfg"
function get_source_from_email($body) {
    $body = strip_tags($body);

    // find start
    $start = strpos ($body,  "Source: ");
    if ($start === false) return "";
    $start += 8;

    // find end
    $end = strpos ($body, "Search", $start);
    if ($end === false) return "";

//    if(strpos(strtolower(substr($body, $start, $end-$start)), "<br>") !== false) return "";

    $source = trim(substr($body, $start,  $end - $start));

    if(substr($source, 0, 9) == "affiliate") { $source = "affiliate"; }
    if(substr($source, 0, 7) == "organic" && strlen($source) > 7) { return ""; }

    return $source;
}

function get_queryterms_from_email($body) {
    // find start
    $start = strpos ($body,  "Referrer: ");
    if ($start === false) return "";
    $start += 10;

    // find end
    $end = strpos ($body, "\r", $start);
    if ($end === false) return "";

    return get_search_phrase(substr($body, $start,  $end - $start));

}


// SHORTENS A STRING OF TEXT AFTER GIVEN LENGTH AT NEAREST WHITESPACE
// INPUT: $text REPRESENTING THE STRING TO TRUNCATE
// INPUT: $length REPRESENTING THE NUMBER OF CHARACTERS ALLOWED IN NEW STRING
// OUTPUT: A 150 CHARACTER OR LESS SUMMARY OF THE EMAIL
function truncate($text, $length) {
   $length = abs((int)$length);
   if(strlen($text) > $length) {
      $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
   }
   return($text);
}



?>
