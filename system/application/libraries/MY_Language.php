<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Language Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Language
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/language.html
 */
class MY_Language extends CI_Language {

	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function MY_Language()
    {
        parent::CI_Language();
    }

	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch a single line of text from the language array
	 *
	 * @access	public
	 * @param	string	$line 	the language line
	 * @return	string
	 */
	function is_lang($value,$format = 'ISO-639-3')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		
		// read tab-delimited file of ISO 639 lang codes
		

	}

}
// END MY_Language Class

/* End of file MY_Language.php */
/* Location: ./system/application/libraries/MY_Language.php */