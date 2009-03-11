<?php
class Setup extends Controller {

	function Setup()
	{
		parent::Controller();
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
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data['list'] = $this->Object_model->get_list();
		$index['new_objects'] = $data['list'];
		$index['objects'] = $this->load->view('cms/object_list',$data, true);

		// load list of activated languages
		$data['list'] = $this->Language_model->get_list();
		$index['languages'] = $this->load->view('cms/lang_list',$data, true);

		// load list of users
		$build_list = array();
		$userlist = $this->User_model->get_list();
		foreach($userlist as $user) {
			$user_edit_link = "cms/user/edit/id/".$user['user_id'];
			$name = $user['name_last'].", ".$user['name_first'];
			$build_list[] = anchor($user_edit_link,$name);
		}

		$data['list'] = $build_list;
		$index['users'] = $this->load->view('cms/user_list',$data, true);

		// now load view
		$this->load->view('cms/setup',$index);
	}

	function structure()
	{
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data['list'] = $this->Object_model->get_list();
		$index['new_objects'] = $data['list'];
		$index['objects'] = $this->load->view('cms/object_list',$data, true);

		// now load view
		$this->load->view('cms/setup_structure',$index);
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
			$data['matches_label'] = "Matches for '".$this->input->post('find')."'";
			$data['matches'] = $this->Language_model->find_matches($this->input->post('find'));
		}

		$data['enabled'] = $this->Language_model->get_list();
		$data['action'] = site_url('cms/setup/languages');
		$data['attributes'] = array('name' => 'search_langs');
		
		$this->load->view('cms/lang_edit',$data);
	
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
				$data['msg'] = validation_errors();
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
		$data['home_page_id'] =  $this->Site_model->home_page_id;
		$data['theme'] = $this->Site_model->theme;
		$data['default_lang'] =  $this->Site_model->default_lang;
		$data['webmaster_id'] =  $this->Site_model->webmaster_id;

		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		$data['langArray'] = $langArray;

		// get list of admin users for webmaster contact
		$userArray = array();
		$users = $this->User_model->get_list(1);
		foreach($users as $row) {
			$userArray[$row['user_id']] = $row['name_last'].', '.$row['name_first'];
		}
		$data['userArray'] = $userArray;

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
		$data['homeArray'] = $homeArray;
		
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

		$data['themeArray'] = $themeArray;

		$data['action'] = site_url('cms/setup/site_prefs');
		$data['hidden'] = array();
		$data['attributes'] = array('name' => 'site_prefs','id' => 'site_prefs');

		$this->load->view('cms/site_prefs',$data);
	}
    // end site prefs

}
?>