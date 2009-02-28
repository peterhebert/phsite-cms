<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('is_lang'))
{	
	function is_lang($value)
	{
		
		// read tab-delimited file of ISO 639 lang codes
		$iso_all = read_file('system/application/language/iso-639-3.tab');
		//Id	Part2B	Part2T	Part1	Scope	Language_Type	Ref_Name	Comment
		$iso_639 = preg_replace('/\r\n|\r/', "\n", $iso_all);
		$langs = explode("\n",$iso_639);
		
		foreach($langs as $line) {
			$temp = explode("\t",$line);

			if(in_array($value, $temp)) {
				return true;
			}
			
		}
		return false;
		
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

/* End of file phsite_helper.php */
/* Location: ./system/application/helpers/phsite_helper.php */