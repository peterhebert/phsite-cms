<?php
class Setup extends Controller
{

	function Setup()
	{
		parent::Controller();

		// if not logged in, redirect to login form
		$this->load->library('session');
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;
		}

		$this->load->helper('form');

		// load objects
		$this->load->model('Content_model');
		$this->load->model('Field_model');
		$this->load->model('Language_model');
		$this->load->model('Object_model');
		$this->load->model('Site_model');
		$this->load->model('User_model');
	}
	
	function index()
	{
		$this->load->view('cms/setup',$this->data);
	}

	function structure()
	{
		// load list of fields
		$d2['list'] = $this->Field_model->get_list();
		$this->data['fields'] = $this->load->view('cms/field_list',$d2, true);
	
		// load list of objects
		$d2['list'] = $this->Object_model->get_list();
		$this->data['new_objects'] = $d2['list'];
		$this->data['objects'] = $this->load->view('cms/object_list',$d2, true);

		// now load view
		$this->load->view('cms/setup_structure',$this->data);
	}
	
	function languages()
	{

		if($this->input->post('submit')) {
			$enabled_langs = explode(',',$this->input->post('enabled_langs'));
			$sel_lang = $this->input->post('lang');

			$new_lang = $this->input->post('new_lang');

			if(is_array($sel_lang)) {
				foreach($enabled_langs as $lang) {
					if(in_array($lang,$sel_lang)) {
						$activated = 1;
					} else {
						$activated = 0;
					}
					$this->Language_model->ISO_639_3 = $lang;
					$this->Language_model->set_active($activated);
				}
			}

			if(is_array($new_lang)) {
				foreach($new_lang as $lang) {
					$this->Language_model->ISO_639_3 = $lang;
					$this->Language_model->set_active(1);
				}
			}

		}
		if($this->input->post('search_submit')) {
			$this->data['matches_label'] = "Matches for '".$this->input->post('find')."'";
			$this->data['matches'] = $this->Language_model->find_matches($this->input->post('find'));
		}

		$this->data['enabled'] = $this->Language_model->get_list();
		$this->data['action'] = site_url('cms/setup/languages');
		$this->data['attributes'] = array('name' => 'search_langs');
		
		$this->load->view('cms/lang_edit',$this->data);
	
	} // end languages()

    // update site prefs
	function site_prefs()
    {

		// process form
		if( $this->input->post('submit') != '' )
		{
			// validate
			$this->load->library('form_validation');		

			// set validation rules
			$this->form_validation->set_rules('home_page_id', 'Home Page', 'required|is_natural');
			$this->form_validation->set_rules('webmaster_id', 'Webmaster', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('default_lang', 'Default Language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('theme', 'Theme', 'required|alpha_dash');

			// on valid, write config file
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {
				$this->Site_model->home_page_id = $this->input->post('home_page_id');
				$this->Site_model->theme = $this->input->post('theme');
				$this->Site_model->default_lang = $this->input->post('default_lang');
				$this->Site_model->webmaster_id = $this->input->post('webmaster_id');
				$this->Site_model->update_prefs();

			} // end validation

		} // end form processing

		// now load config
		$this->Site_model->load_prefs();
		$this->data['home_page_id'] =  $this->Site_model->home_page_id;
		$this->data['theme'] = $this->Site_model->theme;
		$this->data['default_lang'] =  $this->Site_model->default_lang;
		$this->data['webmaster_id'] =  $this->Site_model->webmaster_id;

		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		$this->data['langArray'] = $langArray;

		// get list of admin users for webmaster contact
		$userArray = array();
		$users = $this->User_model->get_list(1);
		foreach($users as $row) {
			$userArray[$row['user_id']] = $row['name_last'].', '.$row['name_first'];
		}
		$this->data['userArray'] = $userArray;

		// get list of top level pages for home page
		$homeArray = array();
		$homeArray[0] = 'Auto-generate';
		$homePages = $this->Content_model->get_list('','asc',0);
		foreach($homePages as $row) {
			$this->Content_model->content_id = $row['content_id'];
			$this->Content_model->lang = $this->Site_model->default_lang;
			$this->Content_model->get_content();
			$homeArray[$row['content_id']] = $this->Content_model->title;
		}
		$this->data['homeArray'] = $homeArray;
		
		// get list of themes
		$themeArray = array();
		// get document directory of index.php
		$theme_dir = $this->Site_model->install_path.'system/application/views/themes';

		if (is_dir($theme_dir)) {

			if ($dh = opendir($theme_dir)) {
				while (($file = readdir($dh)) !== false) {
					if($file != '.' && $file != '..' && is_dir($theme_dir.'/'.$file)) {
						$themeArray[$file] = str_replace('_',' ',$file);
					}
				}
				closedir($dh);
			}
		}
		asort($themeArray);

		$this->data['themeArray'] = $themeArray;

		$this->data['action'] = site_url('cms/setup/site_prefs');
		$this->data['hidden'] = array();
		$this->data['attributes'] = array('name' => 'site_prefs','id' => 'site_prefs');

		$this->load->view('cms/site_prefs',$this->data);
	}
    // end site prefs

    // list users
	function users()
	{

		$build_list = array();

		if($this->session->userdata('admin') != 1)
		{
			$this->data['msg'] = "You are not an admin user and cannot access other user accounts or create new users";
			$user_edit_link = "cms/user/edit/id/".$this->session->userdata('user_id');
			$name = $this->session->userdata('name_last').", ".$this->session->userdata('name_first');
			$build_list[] = anchor($user_edit_link,$name);
		} else {

			$userlist = $this->User_model->get_list();
			foreach($userlist as $user) {
				$user_edit_link = "cms/user/edit/id/".$user['user_id'];
				$name = $user['name_last'].", ".$user['name_first'];
				$build_list[] = anchor($user_edit_link,$name);
			}
		}

		$this->data['list'] = $build_list;

		$this->load->view('cms/user_list',$this->data);
	} // end users()

    // update site metadata
	function site_meta()
    {

		// process form
		if( $this->input->post('submit') != '' )
		{
			// validate
			$this->load->library('form_validation');		

			// set validation rules
			$this->form_validation->set_rules('site_meta_id', 'ID', 'is_natural_no_zero');
			$this->form_validation->set_rules('site_lang', 'Language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('title', 'Site Title', 'required');
			$this->form_validation->set_rules('description', 'Description', 'required');

			// on valid, write metadata
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {
				$this->Site_model->site_meta_id = $this->input->post('site_meta_id');
				$this->Site_model->title = $this->input->post('title');
				$this->Site_model->subtitle = $this->input->post('subtitle');
				$this->Site_model->description = $this->input->post('description');
				$this->Site_model->keywords = $this->input->post('keywords');
				$this->Site_model->copyright = $this->input->post('copyright');
				$this->Site_model->site_lang = $this->input->post('site_lang');

				$this->Site_model->update_meta();

			} // end validation

		} // end form processing

		// now load config
		$this->Site_model->load_meta();
		$this->data['site_meta_id'] =  $this->Site_model->site_meta_id;
		$this->data['title'] =  $this->Site_model->title;
		$this->data['subtitle'] =  $this->Site_model->subtitle;
		$this->data['description'] =  $this->Site_model->description;
		$this->data['keywords'] =  $this->Site_model->keywords;
		$this->data['copyright'] =  $this->Site_model->copyright;
		$this->data['site_lang'] =  $this->Site_model->site_lang;

		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		$this->data['langArray'] = $langArray;

		$this->data['action'] = site_url('cms/setup/site_meta');
		$this->data['attributes'] = array('name' => 'site_metadata','id' => 'site_metadata');

		$this->load->view('cms/site_meta',$this->data);
	}
    // end site metadata


}
?>