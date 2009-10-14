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
 
 */
class MY_Controller extends Controller {

	var $data = array();
	var $cms_labels = array();
	
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
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;

			// get site metadata
			$this->Site_model->temp_lang = $this->session->userdata('default_lang');
			$this->_load_site_metadata();
			$this->_load_cms_labels();

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
		if($this->Site_model->temp_lang == '')
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}
		$this->data['site_meta_lang'] = $this->Site_model->temp_lang;

		$this->data['theme_path'] = $base.'/system/application/views/themes/'.$this->Site_model->theme.'/';

		// get site title
		$this->Site_model->temp_id = $this->Site_model->site_id;
		$this->Site_model->scope = 'site';
		$this->Site_model->load_title();

		$this->data['site_meta_title'] = $this->Site_model->title;
		$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;
		$this->data['site_meta_keywords'] = $this->Site_model->keywords;

		// format the date according to site timezone

		$this->data['site_meta_created'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone);
		$this->data['site_meta_created_W3C'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y-m-d");
		$this->data['site_meta_created_W3C'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y-m-d");

		$tmp_cr_yr = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y");
		$tmp_mod_yr = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y");

		$tmp_site_dates = $tmp_cr_yr.'-'.$tmp_mod_yr;

		$this->data['site_meta_updated'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone);
		$this->data['site_meta_updated_W3C'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y-m-d");

		// get site metadata
		$this->Site_model->load_site_metadata();
		
		foreach($this->Site_model->metadata as $fd_id => $meta)
		{
			$key = 'site_meta_'.$meta['field_ref_name'];
			
			if($meta['field_ref_name'] == 'copyright')
			{
				$val = '&#169; '.$tmp_site_dates.' '.$meta['value'];
				
				$val_xml = '&#x00A9;'.$tmp_site_dates.' '.$meta['value'];
				$this->data['site_meta_copyright_xml'] = $val_xml;
			}
			else
			{
				$val = $meta['value'];
			}
			
			$this->data[$key] = $val;
			
		}

	}


	function _load_cms_labels($user=FALSE)
	{	
		$this->load->library('session');

		if($user)
		{
			$user_lang = $this->session->userdata('default_lang');
			if($user_lang)
			{
				$this->Site_model->temp_lang = $user_lang;
			}
		}
		
		if($this->Site_model->temp_lang == '')
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}
		$this->Site_model->lang_load();
		
		// load validation rules and input types from language files
		$load_lang = $this->Site_model->lang_CI_Name;
		if($load_lang == '')
		{
			$load_lang = 'english';
		}

		$this->lang->load('phsite', $load_lang);
		$this->cms_labels = $this->lang->line('cms_labels');
		$this->data['cms_labels'] = $this->lang->line('cms_labels');

    }

}
// END _Controller class

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */