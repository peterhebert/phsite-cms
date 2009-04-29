<?php
class User extends Controller
{

	function User()
	{
		parent::Controller();

		$this->load->model('Site_model');
		$this->load->model('User_model');

		// get site prefs & meta
		$this->Site_model->load_prefs();

		$this->authenticate();
	} // end constructor

	function authenticate()
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
			$this->Site_model->site_lang = $this->session->userdata('default_lang');
			$this->Site_model->load_meta();
			$this->data['site_title'] = $this->Site_model->title;
		}

	}

	function index()
	{

		$build_list = array();

		if($this->session->userdata('admin') != 1)
		{
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
	} // end index()

	function load()
	{
		if($this->User_model->id != '') {
			if($this->User_model->load()) {
				return TRUE;
			}
		}
		return FALSE;

	} // end load()


	function edit()
	{

		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->data['action'] = 'cms/user/edit/';
		$this->data['hidden'] = '';
		$this->data['user_id'] = '';
		$this->data['name_first'] = '';
		$this->data['name_last'] = '';
		$this->data['username'] = '';
		$this->data['timezone'] = '';
		$this->data['admin'] = '';
		$this->data['default_lang'] = '';
		$this->data['email'] = '';

		$params = $this->uri->uri_to_assoc(4);
		if($this->User_model->user_id == '' && array_key_exists('id',$params)) {
			$this->User_model->user_id = $params['id'];
		}

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			if($this->User_model->user_id == '' && $this->input->post('user_id') != '') {
				$this->User_model->user_id = $this->input->post('user_id');
			}

			// set validation rules
			$this->form_validation->set_rules('name_first', 'First Name', 'required');
			$this->form_validation->set_rules('name_last', 'Last Name', 'required');
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');

			if($this->input->post('password') != '' || $this->User_model->user_id == '') {
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|alpha_dash');
				$this->form_validation->set_rules('password_confirm', 'Password confirm', 'required|min_length[8]|alpha_dash|matches[password]');
			}

			$this->form_validation->set_rules('timezone', 'Time Zone', 'required|is_timezone');
			$this->form_validation->set_rules('admin', 'User Type', 'required|is_natural');
			$this->form_validation->set_rules('default_lang', 'Default language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('email', 'Email address', 'required|valid_email');

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {

				if($this->input->post('user_id') != '') {
					$this->User_model->user_id = $this->input->post('user_id');
				}
				$this->User_model->name_first = $this->input->post('name_first');
				$this->User_model->name_last = $this->input->post('name_last');
				$this->User_model->username = $this->input->post('username');
				$this->User_model->timezone = $this->input->post('timezone');
				$this->User_model->admin = $this->input->post('admin');
				$this->User_model->default_lang = $this->input->post('default_lang');
				$this->User_model->email = $this->input->post('email');

				if($this->input->post('password') != '') {
					$success = $this->User_model->update_user($this->input->post('password'));
				} else {
					$success = $this->User_model->update_user();
				}

				if($success) {
					$this->data['msg'] = "<p>User updated successfully</p>\n";

					/*if($this->input->post('user_id') == '') {
						if($this->email_user_info($this->input->post('password'),'new')) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
						
					} else if ($this->input->post('user_id') != '') {
						if($this->email_user_info($this->input->post('password'))) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
					}
*/
				} else {
					$this->data['msg'] = "User could not be updated";
				}
			}

		}


		if($this->User_model->user_id != '') {
			$this->User_model->load();
			
			$this->data['action'] = 'cms/user/edit/id/'.$params['id'];
			$this->data['hidden'] = array('user_id' => $this->User_model->user_id);
			$this->data['user_id'] = $this->User_model->user_id;
			$this->data['name_first'] = $this->User_model->name_first;
			$this->data['name_last'] = $this->User_model->name_last;
			$this->data['username'] = $this->User_model->username;
			$this->data['timezone'] = $this->User_model->timezone;
			$this->data['admin'] = $this->User_model->admin;
			$this->data['default_lang'] = $this->User_model->default_lang;
			$this->data['email'] = $this->User_model->email;

		}

		$zones = timezone_identifiers_list();
		
		$continents = array('Africa','America','Antarctica','Arctic','Asia','Atlantic','Australia','Europe','Indian','Pacific');

		foreach ($zones as $zone)
		{

			$parts = explode('/', $zone);
		   
			// Only use "friendly" continent names
			if (in_array($parts[0],$continents,true) )
			{       
	
					$locations[$zone] = str_replace('_',' ',$zone);
				
			}
		}

		$this->data['zones'] = $locations;


		$this->load->view('cms/user_edit',$this->data);


	} // end edit()

	function forgot_password()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->data['username'] = '';
		$this->data['email'] = '';
		$this->data['showform'] = TRUE;

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$this->data['username'] = $this->input->post('username');
			$this->data['email'] = $this->input->post('email');
			
			// set validation rules
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('email', 'Email address', 'required|valid_email');

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$this->data['msg'] = validation_errors();
			} else {

				$this->User_model->username = $this->input->post('username');
				$this->User_model->email = $this->input->post('email');
				if($this->User_model->reset_password()) {
					$this->data['msg'] = "Your reset password has been emailed to you";
					$this->data['showform'] = FALSE;
				} else {
					$this->data['msg'] = "Could not find a user matching the info provided";
				}
			}

		}

		$this->load->view('cms/user_forgot_pwd',$this->data);

	} // end forgot_password()

	function match_zones($str)
	{
		$zones = timezone_identifiers_list();
		$locations =  array();
		
		foreach ($zones as $zone)
		{

			if (preg_match($str, $zone)) {
				$locations[] = $zone;
			}
		
		}
		return $locations;
	} // end list_zones()

    function email_user_info($pwd,$action='') {
		$this->load->library('email');
		
		switch ($action) {
		case 'new':
			$subject = "New phSite account";
			$message = "You have created a new account for phSite at the following address:\n";
			$message .= $this->config->item('base_url')."\n";
			$message .= "Your login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		case 'reset':
			$subject = "Password reset for phSite installation";
			$message = "Your password has been reset for phSite at the following address:\n";
			$message .= $this->config->item('base_url')."\n";
			$message .= "Your new login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		default:
			$subject = "Your login info for phSite";
			$message = "You have changed your login password for phSite at the following address:\n";
			$message .= $this->config->item('base_url')."\n";
			$message .= "Your new login info is:\n";
			$message .= "Username: ".$this->User_model->username."\n";
			$message .= "Password: ".$pwd."\n";
			break;
		}
		$host = str_replace('http://','',$this->config->item('base_url'));

		$this->email->from("phsite@{$host}", 'phSite CMS');
		$this->email->to($this->User_model->email); 
		$this->email->subject($subject);
		$this->email->message($message);	
				
		if($this->email->send()) {
			return TRUE;
		} else {
			echo $this->email->print_debugger();
			return FALSE;
		}

	} // end email_user_info()


}
?>