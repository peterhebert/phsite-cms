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
	
}
// END Form Validation Class

/* End of file MY_Form_validation.php */
/* Location: ./system/application/libraries/MY_Form_validation.php */