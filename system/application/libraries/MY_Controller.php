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

	function _authenticate()
	{
		// if not logged in, redirect to login form
		$this->load->library('session');
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
			// get site metadata
			$this->Site_model->site_current_lang = $this->session->userdata('default_lang');
			$this->Site_model->load_site();
			$this->Site_model->load_site_title();

			$this->data['site_title'] = $this->Site_model->title;
		}

	}

	function _load_site() {

		$base_raw = base_url();
		$base_array = explode('/',$base_raw);
		$base_array = removeEmptyArrayElements($base_array);	
		array_shift($base_array);	
		array_shift($base_array);	
		$base = implode('/',$base_array);
		
		if($base != '') {
			$base = '/'.$base;
		}

		$this->Site_model->load_prefs();
		$this->data['base_path'] = $base;
		$this->data['theme_path'] = $base.'/system/application/views/themes/'.$this->Site_model->theme.'/';

		// get site title
		$this->Site_model->load_site_title();
		$this->data['site_meta_title'] = $this->Site_model->title;
		$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;

		// get site metadata
		$this->Site_model->load_meta();
		
		foreach($this->Site_model->metadata as $meta)
		{
			$key = 'site_meta_'.$meta['field_ref_name'];
			$this->data[$key] = $meta['value'];
		}

		$this->data['site_meta_updated'] = $this->Site_model->site_date_updated;
		$this->data['site_meta_updated_W3C'] = substr($this->Site_model->site_date_updated,0,10);
		$this->data['site_meta_lang'] = $this->Site_model->site_current_lang;

	}
}
// END _Controller class

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */