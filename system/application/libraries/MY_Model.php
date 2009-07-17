<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* EXTENDED Model Class
* @author		Peter Hebert
* @link		http://www.peterhebert.com/

*/
class MY_Model extends Model {

	var $messages = array();
	var $install_path;
	
	/**
	 * Constructor
	 *
	 * Calls the initialize() function
	 */
	function MY_Model()
	{	
		parent::Model();

		$docroot = realpath($_SERVER['DOCUMENT_ROOT']);
		$script = $_SERVER['SCRIPT_NAME'];
		$basedir = preg_replace('/(.*)index\.php(.*)/','\1',$script);

		$this->install_path = $docroot.$basedir;
	}

	function _output_messages()
	{
		if(count($this->messages) > 0)
		{
			$tmp_msg = "<p>";
			$tmp_msg .= implode("</p>\r<p>",$this->messages);
			$tmp_msg .= "</p>";
			return $tmp_msg;
		}
		return FALSE;
	}

}
// END MY_Model class

/* End of file MY_Model.php */
/* Location: ./system/application/libraries/MY_Model.php */