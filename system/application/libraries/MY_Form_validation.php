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

	// --------------------------------------------------------------------
	
	/**
	 * Valid date format
	 *
	 * @access	public
	 * @param	string
	 * @return	string
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
		return ( ! ereg('^([a-z]){3}$', $str)) ? FALSE : TRUE;
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
		$pattern = "^(http|https|ftp){0,1}(\:){0,1}(//){0,1}[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s]$";
		
		$valid = eregi($pattern, $str);
		return $valid;
	}

}

// END Form Validation Class

/* End of file MY_Form_validation.php */
/* Location: ./system/application/libraries/MY_Form_validation.php */