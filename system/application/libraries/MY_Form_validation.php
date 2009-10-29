<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * EXTENDED Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		Peter Hebert
 * @link		http://www.peterhebert.com/
 */
class MY_Form_validation extends CI_Form_validation {

	/**
	 * Constructor
	 *
	 */	
	function MY_Form_validation($rules = array())
	{	
		parent::CI_Form_validation($rules);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Alpha-numeric with spaces, underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function alpha_num_spaces($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-\s])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * directory exists
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function path_dir($str)
	{
		$valid = FALSE;
		$file_full = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG.$str;
		if(file_exists($file_full) && is_dir($file_full))
		{
			$valid = TRUE;
		}
		
		return $valid;
	}

	/**
	 * file exists
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function path_file($str)
	{
		$valid = FALSE;
		$file_full = realpath($_SERVER["DOCUMENT_ROOT"]).MEDIA_PATH_ORIG.$str;
		if(file_exists($file_full) && !is_dir($file_full))
		{
			$valid = TRUE;
		}
		
		return $valid;
	}

	/**
	 * external URL exists
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function uri_exists($uri)
	{
		$valid = FALSE;
		if(preg_match('#^(http|https){1}://(www\.)?(.*)\.([a-z0-9]{2,6})$#',$uri))
		{
			$uri_clean = str_replace(' ','%20',$uri);
			$headers = get_headers($uri_clean);
			if (is_array($headers) && preg_match("|200|", $headers[0])) {
				$valid = TRUE;
			}
			else
			{
				$valid = FALSE;
			}
		}
		
		return $valid;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Valid date format
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function is_date($str)
	{
		$delim = array('\\', '/');
		$newstr = str_replace($delim, '-', $str);

		if ($timestamp = strtotime($newstr)) {
			return TRUE;
		} else {
			return FALSE;
		}		
	}

	// --------------------------------------------------------------------
	
	/**
	 * Convert to MySQL date format
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function convert_date($str)
	{
		$delim = array('\\', '/');
		$newstr = str_replace($delim, '-', $str);

		if ($timestamp = strtotime($newstr)) {
			$date = date('Y-m-d H:i:s', $timestamp);
			return $date;
		} else {
			return FALSE;
		}		
	}

	// --------------------------------------------------------------------
	
	/**
	 * is a valid timezone?
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function is_timezone($str)
	{
		$timezone_identifiers = DateTimeZone::listIdentifiers();
		$valid = in_array($str, $timezone_identifiers);
		return $valid;
	}

	// --------------------------------------------------------------------
	
	/**
	 * is a valid iso 639-3 language?
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	

	function is_lang($str)
	{
		return ( ! preg_match('/^([a-z]){3}$/', $str)) ? FALSE : TRUE;
	}

	// --------------------
	// test for uri
	// --------------------
	/*
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function is_uri($str)
	{
		$pattern = "/^(http|https|ftp){0,1}(\:){0,1}(//){0,1}[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s]$/";
		
		return ( ! preg_match($pattern, $str)) ? FALSE : TRUE;
	}

	// --------------------
	// valid phone numbers (just checks valid characters, should work for all international formats)
	// --------------------
	/*
	 * @access	public
	 * @param	string
	 * @return	bool
	 */	
	function is_phone($str)
	{
		$pattern = "/^[\(\)\+0-9A-Z\.[:blank:]-]+(ext)?[\(\)\+0-9A-Z\.[:blank:]-]+$/";
		return preg_match( $pattern  ,  $str );	
	}
}

// END Form Validation Class

/* End of file MY_Form_validation.php */
/* Location: ./system/application/libraries/MY_Form_validation.php */