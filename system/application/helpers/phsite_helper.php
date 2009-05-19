<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('removeEmptyArrayElements'))
{	
	function removeEmptyArrayElements($array) {
		settype($cleanArray,"array");
	
		foreach ($array as $value) {
			if (!empty($value)) {
				$cleanArray[] = $value;
			}
		}
		return $cleanArray;
	}
}	

if ( ! function_exists('convert_date'))
{	
	function convert_date($str)
	{
		$delim = array('\\', '/');
		$newstr = str_replace($delim, '-', $str);

		if ($timestamp = strtotime($newstr))
		{
			$date = date('Y-m-d H:i:s', $timestamp);
			return $date;
		} else {
			return FALSE;
		}		
	}

}

if ( ! function_exists('csv_normalize'))
{	
	function csv_normalize($str)
	{
		$valueArray = explode(',',$str);
		$cleanVals = array();
		foreach ($valueArray as $val)
		{
			if($val != '') {
				$cleanVals[] = trim($val,' ');
			}
		}
		natcasesort($cleanVals);
		$csv_return = implode(', ',$cleanVals);
		return $csv_return;
	}

}

if ( ! function_exists('sort_compare'))
{	
	function sort_compare($a, $b) {
		if ($a == $b)
		{
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
}

if ( ! function_exists('checkPassword'))
{
	function checkPassword($plainText,$pwd)
	{
	
		//extract the salt from the password
		$salt = substr ( $pwd, 0, SALT_LENGTH );
	
		$checkPwd = $salt.sha1($salt.$plainText);
	
		if ($checkPwd == $pwd)
		{
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists('generateHash'))
{
	function generateHash($plainText, $salt = NULL)
	{
		if ($salt === NULL)
		{
			$salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
		}
		else
		{
			$salt = substr($salt, 0, SALT_LENGTH);
		}
	
		return $salt . sha1($salt . $plainText);
	}
}

if ( ! function_exists('gen_md5_password'))
{
	function gen_md5_password($len = 8)
	{
		// function calculates 32-digit hexadecimal md5 hash
		// of some random data
		return substr(md5(rand().rand()), 0, $len);
	}
}

if ( ! function_exists('listTimeZones'))
{
	function listTimeZones()
	{
		$zones = timezone_identifiers_list();
		
		$continents = array('Africa','America','Antarctica','Arctic','Asia','Atlantic','Australia','Europe','Indian','Pacific');
		
		foreach ($zones as $zone)
		{
		
			$parts = explode('/', $zone);
		   
			// Only use "friendly" continent names
			if (in_array($parts[0],$continents,true) )
			{       
		
				if(array_key_exists(2,$parts)) {
					$country = $parts[0].'/'.$parts[1];
					$locations[$parts[0]][$country][$zone] = $parts[2];
				
				} else {
		
					if (isset($parts[1])) {
						$locations[$parts[0]][$zone] = $parts[1];
					}
		
				}
				
			}
		}
		return $locations;
	}
}

if ( ! function_exists('convert_uri'))
{
	function convert_uri($string){
	
		$newstring = strtolower($string);
	
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
	
		$newstring = str_replace($search, $replace, $newstring);
	
		$findRegEx = "([^A-Za-z0-9]+)";
	
		$newstring = preg_replace($findRegEx, '_', $newstring);
		
		$newstring = trim ( $newstring, "_" );
	
		return $newstring;
	}
}

if ( ! function_exists('checkEncoding'))
{
	function checkEncoding ( $string, $string_encoding )
	{
		$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
	
		$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
	
		return $string === mb_convert_encoding ( mb_convert_encoding ( $string, $fs, $ts ), $ts, $fs );
	}
}

if ( ! function_exists('convert_entities'))
{
	function convert_entities($string,$safetags="") {
		if(!checkEncoding($string, "UTF-8")) {
			$text = mb_convert_encoding($string, "UTF-8");
		} else {
			$text = $string;
		}
		$text = htmlentities($text,ENT_QUOTES,'UTF-8');
		
		// do the basic tags first
		$basicTags = "b,strong,em,i";
	
		if ($safetags = "") {
			$safetags = $basicTags;
		} else {
			$safetags = $basicTags.",".$safetags;
		}
	
		$allowableTags = explode(',',$safetags);
		
		foreach ($allowableTags as $tag) {
			$find[] = "/&lt;$tag&gt;/";
			$replace[] = "<$tag>";
			$find[] = "/&lt;\/$tag&gt;/";
			$replace[] = "<$tag>";
		}
	
		// now do the tags with attributes
		$complexTags = array('a','div','span','img');
	
		foreach ($complexTags as $tag) {
			$find[] = "/&lt;$tag /";
			$replace[] = "<$tag ";
		
			$find[] = "/&lt;\/$tag&gt;/";
			$replace[] = "</$tag>";
		}
		$find[] = "/\"&gt;/";
		$replace[] = "\">";
	
		$find[] = "/\" \/&gt;/";
		$replace[] = "\" />";
	
		// check for attributes
		$apattern = "(href|name|title|class|id|src|width|height|alt){1}=&quot;([^&]*)&quot;";
		$areplace = "\\1=\"\\2\"";
		$text = ereg_replace($apattern,$areplace,$text);
	
		// now do the preg_replace
		$text = preg_replace($find,$replace,$text);
	
		return $text;
	}
}

if ( ! function_exists('get_max_upload_size'))
{
	function get_max_upload_size() {
		// get max upload size from php.ini settings
		$max = ini_get('upload_max_filesize');
		$max = preg_replace("/^([0-9]+)([KMG]*)/", "$1/$2", $max);
		$max_size_array = explode('/',$max);
		
		switch($max_size_array[1]) {
		case 'K':
			$max_size = $max_size_array[0]* 1024 ;
			break;
		case 'M':
			$max_size = $max_size_array[0] * 1024 * 1024;
			break;
		case 'G':
			$max_size = $max_size_array[0] * 1024 * 1024 * 1024 ;
			break;
		default:
			$max_size = $max_size_array[0];
			break;
		}
		// return max upload size in bytes
		return $max_size;
	}
}

if ( ! function_exists('resize_bytes'))
{
	function resize_bytes($size)
	{
	   $count = 0;
	   global $labels;
	   $bytesText = 'bytes';
	   $bytesInit = 'b';
	   
	   $format = array($bytesText,"K".$bytesInit,"M".$bytesInit,"G".$bytesInit,"T".$bytesInit,"P".$bytesInit,"E".$bytesInit,"Z".$bytesInit,"Y".$bytesInit);
	   while(($size/1024)>1 && $count<8)
	   {
		   $size=$size/1024;
		   $count++;
	   }
	   $formatted = number_format($size,0,'','.')." ".$format[$count];
	   return $formatted;
	}
}

/* End of file phsite_helper.php */
/* Location: ./system/application/helpers/phsite_helper.php */