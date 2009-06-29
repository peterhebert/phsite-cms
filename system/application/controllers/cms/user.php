<?php
class User extends MY_Controller
{

	function User()
	{
		parent::MY_Controller();

		$this->load->model('User_model');

		//$this->_authenticate();
	} // end constructor

	function index()
	{
		$this->User_model->get_list();
		if(count($this->User_model->user_list) > 0)
		{
			$this->data['list'] = ul($this->User_model->user_list);
		} else {
			$this->data['msg'] = 'no users found';
		}
		
		$this->load->view('cms/user_list',$this->data);
	} // end index()

	function edit()
	{
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : ';
		$breadcrumb .= anchor("cms/setup/user","Users").' : User editor';
		$this->data['breadcrumb'] = $breadcrumb;

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->User_model->user_id = $params['id'];
		}
		if(array_key_exists('lang',$params))
		{
			if(is_numeric($params['lang']))
			{
				$this->User_model->user_meta_id = $params['lang'];
			} else {
				$this->User_model->meta_lang = $params['lang'];
			}
		}

		$this->_process_form();
		$this->User_model->load();
		$this->User_model->load_meta();
		
		$this->data['form'] = $this->_edit_form();
		$this->load->view('cms/user_edit',$this->data);

	} // end edit()


	function _timezone_list()
	{
		$timezones = timezone_identifiers_list();
		$continents = array('Africa','America','Antarctica','Arctic','Asia','Atlantic','Australia','Europe','Indian','Pacific');
		$locations = array();
		
		foreach ($timezones as $zone)
		{
			$parts = explode('/', $zone);
		   
			// Only use "friendly" continent names
			if (in_array($parts[0],$continents,true) )
			{       
	
					$locations[$zone] = str_replace('_',' ',$zone);
				
			}
		}

		return $locations;
	}

	function _edit_form()
	{
		$this->load->helper('form');
		$this->load->model('Field_model');

		$hidden = array();
		$action = 'cms/user/edit';
		if($this->User_model->user_id != '')
		{
			$action .= '/id/'.$this->User_model->user_id;
			$hidden['user_id'] = $this->User_model->user_id;
		}
		$attributes = array('id' => 'user_editor', 'name' => 'user_editor', 'title' => 'User editing form');

		$form = form_open($action, $attributes, $hidden)."\r";

		$form .= form_fieldset('User Info');
		$form .= "<p>All user info fields are required</p>\r";

		$this->load->library('session');
		if($this->session->userdata('admin') === 1)
		{
			$options = array(0 => 'Basic User', 1  => 'Administrator');
			$form .= "<p>\r";
			$form .= form_label('User type', 'admin');
			$form .= form_dropdown('admin', $options, $this->User_model->admin);		
			$form .= "</p>\r";

		} else {
			$form .= "<input type=\"hidden\" name=\"admin\" id=\"admin\" value=\"0\" />\r";
		}

		$params = array(
		'name'        => 'name_first',
		'id'          => 'name_first',
		'value'       => $this->User_model->name_first,
		);
		$form .= "<p>\r";
		$form .= form_label('First Name', 'name_first');
		$form .= form_input($params);
		$form .= "</p>\r";

		$params = array(
		'name'        => 'name_last',
		'id'          => 'name_last',
		'value'       => $this->User_model->name_last,
		);
		$form .= "<p>\r";
		$form .= form_label('Last Name', 'name_last');
		$form .= form_input($params);
		$form .= "</p>\r";

		$params = array(
		'name'        => 'username',
		'id'          => 'username',
		'value'       => $this->User_model->username,
		);
		$form .= "<p>\r";
		$form .= form_label('Username', 'username');
		$form .= form_input($params);
		$form .= "</p>\r";

		$params = array(
		'name'        => 'email',
		'id'          => 'email',
		'value'       => $this->User_model->email,
		);
		$form .= "<p>\r";
		$form .= form_label('E-mail', 'email');
		$form .= form_input($params);
		$form .= "</p>\r";

		$zones = $this->_timezone_list();
		
		$form .= "<p>\r";
		$form .= form_label('Time Zone', 'timezone');
		$form .= form_dropdown('timezone', $zones, $this->User_model->timezone);		
		$form .= "</p>\r";

		$options = $this->Language_model->get_list_dropdown();
		$form .= "<p>\r";
		$form .= form_label('Default language', 'default_lang');
		$form .= form_dropdown('default_lang', $options, $this->User_model->default_lang);		
		$form .= "</p>\r";

		$form .= form_fieldset_close("\r"); 

		$form .= form_fieldset('Password');

		if ($this->User_model->user_id != '') {
			$lbl_pwd = 'Choose new password';
			$lbl_conf = 'Confirm new password';
		} else {
			$lbl_pwd = 'Choose password';
			$lbl_conf = 'Confirm password';
		}
		$params = array(
		'name'        => 'password',
		'id'          => 'password',
		'value'          => ''
		);
		
		$form .= "<p>\r";
		$form .= form_label($lbl_pwd, 'password');
		$form .= form_password($params);
		$form .= "</p>\r";
		
		$params = array(
		'name'        => 'password_confirm',
		'id'          => 'password_confirm',
		'value'          => ''
		);
		$form .= "<p>\r";
		$form .= form_label($lbl_conf, 'password_confirm');
		$form .= form_password($params);
		$form .= "</p>\r";

		$form .= form_fieldset_close("\r"); 

		$form .= form_fieldset('User Metadata');
		
		$this->User_model->load_meta();
		$meta_fields = $this->User_model->metadata;

		foreach($meta_fields as $fd)
		{
			$tmp_label = $fd['field_ref_name'];
			if($fd['field_name'] != '')
			{
				$tmp_label = $fd['field_name'];
			}

			$td = array(
			'id' => $fd['field_ref_name'],
			'name' => 'value_'.$fd['field_id'],
			'value' => $fd['value'],
			'class' => $fd['field_input_type']
			);

			$form .= "<input type=\"hidden\" name=\"user_meta_id[]\" id=\"user_meta_id[]\" value=\"{$fd['user_meta_id']}\" />\r";
			$form .= "<input type=\"hidden\" name=\"fields[]\" id=\"fields[]\" value=\"{$fd['field_id']}\" />\r";
	
			$form .= "<p>\r";
			$form .= form_label($tmp_label, $fd['field_ref_name']);

			switch($fd['field_input_type']) {

			case 'textarea':
				$td['cols'] = 38;
				$form .=  form_textarea($td);
				break;
			case 'richtext':
				$td['cols'] = 38;
				$form .=  form_textarea($td);
				break;
			default:
				$form .=  form_input($td);
			}

			$form .= "</p>\r";
		}
		$form .= form_fieldset_close("\r"); 

		$form .= form_submit('submit', 'Save')."\r";

		$form .= form_close("\r");

		return $form;		
	} // end _edit_form()
			
	function _process_form()
	{
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$this->load->library('form_validation');

			$this->data['msg'] = 'form submitted';

			// set validation rules
			$this->form_validation->set_rules('name_first', 'First Name', 'required');
			$this->form_validation->set_rules('name_last', 'Last Name', 'required');
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[8]|alpha_dash');
			$this->form_validation->set_rules('timezone', 'Time Zone', 'required|is_timezone');
			$this->form_validation->set_rules('admin', 'User Type', 'required|is_natural');
			$this->form_validation->set_rules('default_lang', 'Default language', 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('email', 'Email address', 'required|valid_email');
			if($this->input->post('password') != '' || $this->User_model->user_id == '') {
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|alpha_dash');
				$this->form_validation->set_rules('password_confirm', 'Password confirm', 'required|min_length[8]|alpha_dash|matches[password]');
			}

			// load custom field list and add validation rules
			$this->User_model->list_user_fields();
			$meta_fields = $this->User_model->field_list;
			foreach($meta_fields as $fd)
			{
				$tmp_valid = array();

				if($fd['required'] == 1)
				{
					$tmp_valid[] = 'required';
				}

				if($fd['field_validation'] != '')
				{
					$tmp_valid[] = $fd['field_validation'];
				}
				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd['field_id'], $fd['field_name'], $concat_valid);
				}

			}


			// validate and process if all valid
			$form_valid = $this->form_validation->run();
			// validate and process if all valid
			if ($form_valid)
			{
				$this->data['msg'] = 'form data is valid';
			} else {
				$this->data['msg'] = validation_errors();
			}

		} // end from processing

	} // end _process_form()

/*
	
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			if($this->User_model->user_id == '' && $this->input->post('user_id') != '') {
				$this->User_model->user_id = $this->input->post('user_id');
			}
	
	
	
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
	
					if($this->input->post('user_id') == '') {
						if($this->email_user_info($this->input->post('password'),'new')) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
						
					} else if ($this->input->post('user_id') != '') {
						if($this->email_user_info($this->input->post('password'))) {
							$data['msg'] .= "<p>Confirmation email sent</p>\n";
						}
					}
				} else {
					$this->data['msg'] = "User could not be updated";
				}
			}
	
		}
	
*/


/*

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
*/

}
?>