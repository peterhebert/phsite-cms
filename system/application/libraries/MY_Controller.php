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
		$this->load->library('session');
		$this->Language_model->count_active();
	}

	function _authenticate()
	{
		// if not logged in, redirect to login form
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;

			// get site metadata
			$this->Site_model->site_meta_lang = $this->session->userdata('default_lang');
			$this->_load_site_metadata();

		}

	}

	function _load_site_metadata() {

		$base_raw = base_url();
		$base_array = explode('/',$base_raw);
		$base_array = removeEmptyArrayElements($base_array);	
		array_shift($base_array);	
		array_shift($base_array);	
		$base = implode('/',$base_array);
		
		if($base != '') {
			$base = '/'.$base;
		}
		$this->data['base_path'] = $base;

		$this->Site_model->load_site();
		if($this->Site_model->site_meta_lang == '')
		{
			$this->Site_model->site_meta_lang = $this->Site_model->site_default_lang;
		}
		$this->data['site_meta_lang'] = $this->Site_model->site_meta_lang;

		$this->data['theme_path'] = $base.'/system/application/views/themes/'.$this->Site_model->theme.'/';

		// get site title
		$this->Site_model->load_site_title();
		$this->data['site_meta_title'] = $this->Site_model->title;
		$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;

		// format the date according to site timezone

		$this->data['site_meta_created'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone);
		$this->data['site_meta_created_W3C'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y-m-d");

		$tmp_cr_yr = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y");
		$tmp_mod_yr = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y");

		$tmp_site_dates = $tmp_cr_yr.'-'.$tmp_mod_yr;

		$this->data['site_meta_updated'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone);
		$this->data['site_meta_updated_W3C'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y-m-d");

		// get site metadata
		$this->Site_model->load_site_meta();
		
		foreach($this->Site_model->site_metadata as $fd_id => $meta)
		{
			$key = 'site_meta_'.$meta['field_ref_name'];
			
			if($meta['field_ref_name'] == 'copyright')
			{
				$val = '&copy; '.$tmp_site_dates.' '.$meta['value'];
			}
			else
			{
				$val = $meta['value'];
			}
			
			$this->data[$key] = $val;
			
		}


	}
}
// END _Controller class

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */