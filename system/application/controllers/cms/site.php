<?php
class Site extends Controller {

	function Site()
	{
		parent::Controller();
		$this->load->model('Site_model');
	
	} // end constructor

	function index()
    {
		echo $this->Site_model->install_path;
	}
	

    // update site prefs
	function edit_prefs()
    {
		$this->load->model('User_model');
		$this->load->model('Content_model');
		$this->load->model('Language_model');
		$this->load->helper('form');

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

		$data['action'] = site_url('cms/site/edit_prefs');
		$data['hidden'] = array();
		$data['attributes'] = array('name' => 'site_prefs','id' => 'site_prefs');

		$this->load->view('cms/site_prefs',$data);
	}

}

?>
