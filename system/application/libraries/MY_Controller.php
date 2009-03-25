<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * EXTENDED Application Controller Class
 *
 * This class object is the super class the every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Peter Hebert
 * @link		http://www.peterhebert.com/
 
 only change is to add $data variable (array) to store data that goes into views
 */
class MY_Controller extends Controller {

	var $data = array();
	
	/**
	 * Constructor
	 *
	 * Calls the initialize() function
	 */
	function MY_Controller()
	{	
		parent::Controller();
	}
	
}
// END _Controller class

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */