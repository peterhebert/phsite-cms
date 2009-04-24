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

/* End of file phsite_helper.php */
/* Location: ./system/application/helpers/phsite_helper.php */