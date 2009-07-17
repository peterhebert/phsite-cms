<?php
class User extends MY_Controller
{

	function User()
	{
		parent::MY_Controller();

		$this->load->model('User_model');
		$this->load->helper('form');

		//$this->_authenticate();
	} // end constructor

	function index()
	{

		$userlist = array();

//		if($this->session->userdata('admin') != 1)
//		{
//			$user_name = $this->session->userdata('name_last').", ".$this->session->userdata('name_first');
//			$user_url = "cms/user/edit/id/".$this->session->userdata('user_id');
//			$userlist[] = anchor($user_url, $user_name);
//		} else {

			$this->User_model->get_list();

			if(count($this->User_model->user_list) > 0)
			{
				foreach($this->User_model->user_list as $zy)
				{
					$user_name = $zy['name_last'].', '.$zy['name_first'];
					$user_url = 'cms/user/edit/id/'.$zy['user_id'];
					$userlist[] = anchor($user_url, $user_name);
				}
			}

//		}
		
		if(count($userlist) > 0)
		{
			$this->data['list'] = ul($userlist);
		} else {
			$this->data['msg'] = 'no users found';
		}

		// $this->load->view('cms/user_list',$this->data);

		$this->data['sidebar'] = $this->_meta_menu();
		
		$this->data['form'] = $this->_edit_form();
		$this->load->view('cms/cms_form',$this->data);


	} // end index()

	function edit()
	{

		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : ';
		$breadcrumb .= anchor("cms/user","Users").' : Edit user';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Edit user | Users | Setup ';
		$this->data['title_h1'] = 'Edit user';

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->User_model->user_id = $params['id'];
		}
		if(array_key_exists('lang',$params))
		{
			$this->User_model->meta_lang = $params['lang'];
		}

		$this->_process_form();
		$this->User_model->load();
		$this->User_model->load_meta();
		
		$msgs = $this->User_model->_output_messages();
		if($msgs)
		{
			$this->data['msg'] = $msgs;
		}

		// list active languages
		$lang_links = array();
		$langs = $this->Language_model->get_list();

		$usr_link = 'cms/user/edit/id/'.$this->User_model->user_id;

		foreach($langs as $llang)
		{

			$temp_link = $usr_link.'/lang/'.$llang['ISO_639_3'];
			if($llang['Native_Name'] != '')
			{
				$temp_txt = $llang['Native_Name'];
			}
			else
			{
				$temp_txt = $llang['Ref_Name'];
			}
			$lang_links[] = anchor($temp_link,$temp_txt);
		}
		
		
		$this->data['sidebar'] = $this->_meta_menu();
		
		$this->data['form'] = $this->_edit_form();
		$this->load->view('cms/cms_form',$this->data);

	} // end edit()


	function _meta_menu()
    {
		// list active languages
		$site_meta_links = array();
		$usr_links = array();
		$langs = $this->Language_model->get_list();

		$usr_link = 'cms/user/edit/id/'.$this->User_model->user_id;

		foreach($langs as $llang)
		{
			$site_link = 'cms/setup/site_meta/lang/'.$llang['ISO_639_3'];
			$usr_tmp_link = $usr_link.'/lang/'.$llang['ISO_639_3'];
			if($llang['Native_Name'] != '')
			{
				$temp_txt = $llang['Native_Name'];
			}
			else
			{
				$temp_txt = $llang['Ref_Name'];
			}
			$site_meta_links[] = anchor($site_link,$temp_txt);
			$usr_links[] = anchor($usr_tmp_link,$temp_txt);
		}

		$menu = "<h2>Setup</h2>\r";
		$menu .=  "<ul>\r";
		$menu .=  "<li>".anchor("cms/setup/site_prefs","Site preferences")."</li>\r";
		$menu .=  "<li>Site metadata\r";
		$menu .=  "<ul>\r";
		$menu .=  "<li>".anchor("cms/setup/site_meta_fields","Edit site metadata fields")."</li>\r";
		$menu .=  "<li>Edit site metadata\r";
		$menu .= ul($site_meta_links);
		$menu .=  "</li>\r";
		$menu .=  "</ul>\r";
		$menu .=  "</li>\r";
		$menu .= "<li>".anchor("cms/setup/languages","Languages")."</li>\r";
		$menu .= "<li>".anchor("cms/setup/structure","Data Structure")."</li>\r";
		$menu .= "<li>Users\r";
		$menu .= "<ul>\r";
		$menu .=  "<li>".anchor("cms/user/","Edit Users")."</li>\r";
		$menu .=  "<li>".anchor("cms/user/user_meta_fields","Edit user metadata fields")."</li>\r";
		if($this->User_model->user_id != '')
		{
		$menu .= "<li>User Metadata\r";
		$menu .= ul($usr_links);
		$menu .= "</li>\r";
		}
		$menu .= "</ul>\r";
		$menu .=  "</li>\r";
		$menu .=  "</ul>\r";

		return $menu;
	}



	function _edit_form()
	{
		$this->load->helper('timezone');
		$this->load->model('Field_model');

		$hidden = array();
		$action = 'cms/user/edit';
		if($this->User_model->user_id != '')
		{
			$action .= '/id/'.$this->User_model->user_id;
			$hidden['user_id'] = $this->User_model->user_id;
		}
		if($this->User_model->site_rel_id != '')
		{
			$hidden['site_rel_id'] = $this->User_model->site_rel_id;
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
			$form .= "<input type=\"hidden\" name=\"admin\" id=\"admin\" value=\"{$this->User_model->admin}\" />\r";
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

		$zones = timezone_dropdown();
		
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
		
		$options = $this->Language_model->get_list_dropdown();
		$form .= "<p>\r";
		$form .= form_label('Language', 'meta_lang');
		$form .= form_dropdown('meta_lang', $options, $this->User_model->meta_lang);		
		$form .= "</p>\r";


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
			$this->form_validation->set_rules('meta_lang', 'Metadata language', 'required|alpha|exact_length[3]');

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
				
				// UPDATE USER TABLE
				if($this->input->post('user_id') != '')
				{
					$this->User_model->user_id = $this->input->post('user_id');
				}
				if($this->input->post('password') != '')
				{
					$this->User_model->tmp_pwd = $this->input->post('password');
				}
				if($this->input->post('password_confirm') != '')
				{
					$this->User_model->tmp_pwd_conf = $this->input->post('password_confirm');
				}
				$this->User_model->name_first = $this->input->post('name_first');
				$this->User_model->name_last = $this->input->post('name_last');
				$this->User_model->username = $this->input->post('username');
				$this->User_model->timezone = $this->input->post('timezone');
				$this->User_model->default_lang = $this->input->post('default_lang');
				$this->User_model->email = $this->input->post('email');

				if($this->input->post('site_rel_id') != '')
				{
					$this->User_model->site_rel_id = $this->input->post('site_rel_id');
				}
				$this->User_model->admin = $this->input->post('admin');


				if($this->User_model->update_user() === TRUE)
				{
					// update site users table
					$this->User_model->update_site_user();
	
					// update metadata
					$fields = $this->input->post('fields');
					$metas = $this->input->post('user_meta_id');
					foreach($fields as $key => $zf)
					{
						$val_name = 'value_'.$zf;
						
						$this->User_model->user_meta_id = $metas[$key];
						$this->User_model->field_id = $zf;
						$this->User_model->value = $this->input->post($val_name);
						$this->User_model->meta_lang = $this->input->post('meta_lang');

						$this->User_model->update_meta();

					}
				}

				if(count($this->User_model->messages) === 0)
				{
					$this->User_model->messages[] = 'User info has been saved successfully';
				}

			} else {
				$this->User_model->messages[] = validation_errors();
			}

		} // end from processing

	} // end _process_form()


	function user_meta_fields()
	{
		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/setup","Setup").' : ';
		$breadcrumb .= anchor("cms/user","Users").' : User metadata fields';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'User metadata fields | Users | Setup ';
		$this->data['title_h1'] = 'User metadata fields';

		$this->_process_meta_fields();
		
		$this->data['form'] = $this->_form_meta_fields();

		if(count($this->User_model->messages) > 0)
		{
			$this->data['msg'] = "<p>";
			$this->data['msg'] .= implode("</p>\r<p>",$this->User_model->messages);
			$this->data['msg'] .= "</p>";
		}

		$this->data['sidebar'] = $this->_meta_menu();
		$this->load->view('cms/cms_form',$this->data);
	}

	function _form_meta_fields()
	{

		$action = "/cms/user/user_meta_fields";
		$hidden = array('site_id' => $this->User_model->site_id);
		$attributes = array('name' => 'user_meta_fields','id' => 'user_meta_fields');

		$form = form_open($action, $attributes, $hidden);

		// fields

		$form .= "<table id=\"properties\">\r";
		$form .= "<thead>\r";
		$form .= "<tr>\r";
		$form .= "<th scope=\"col\" id=\"hd_name\">field name</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_exists\">use for user metadata?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_req\">required?</th>\r";
		$form .= "<th scope=\"col\" id=\"hd_order\">order</th>\r";
		$form .= "</tr>\r";
		$form .= "</thead>\r";
		$form .= "<tbody>\r";

		$this->User_model->list_fields();
		
		$fieldCount = count($this->User_model->field_list);
		$fieldNo = 1;

		foreach($this->User_model->field_list as $field)
		{

			if($field['field_name'] != '') {
				$temp_name = $field['field_name'];
			} else {
				$temp_name = $field['field_ref_name'];
			}

			if($fieldNo < $fieldCount) {
			$form .= "<tr>\n";
			} else {
			$form .= "<tr class=\"last\">\n";
			}
			$fieldNo = $fieldNo + 1;
	
			$form .= form_hidden('field_id[]', $field['field_id']);
	
			if($field['rel_id'] != '') {
				$exists = true;
			} else {
				$exists = false;
			}
			$form .= form_hidden('rel_id[]', $field['rel_id']);

			$params = array(
			'name'        => 'exists[]',
			'id'          => 'exists[]',
			'value'       => $field['field_id'],
			'checked'     => $exists
			);
	
			$form .= "<th headers=\"hd_name\" id=\"{$temp_name}\" scope=\"row\">{$temp_name}</th>\n";
			$form .= "<td headers=\"hd_exists\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $field['field_id'],
			'checked'     => $field['required']
			);

			$form .= "<td headers=\"{$temp_name} hd_req\" class=\"align-center\">".form_checkbox($params)."</td>\n";

			$params = array(
			'name'        => "order[]",
			'id'          => "order[]",
			'value'       => $field['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			$form .= "<td headers=\"{$temp_name} hd_order\">".form_input($params)."</td>\n";


			$form .= "</tr>\n";

		}


		$form .= "</tbody></table>\n";
		$form .= form_submit('submit', 'Save');
		$form .= form_close();
		return $form;
	}


	function _process_meta_fields()
	{

		if($this->input->post('submit') != '')
		{
			// get values from form post data
			$this->User_model->site_id = $this->input->post('site_id');
			
			$temp_fields = $this->input->post('field_id');
			$temp_rels = $this->input->post('rel_id');
			$temp_exists = $this->input->post('exists');
			$temp_required = $this->input->post('required');
			$temp_order = $this->input->post('order');

			$field_updates = array();
			foreach($temp_fields as $index => $id)
			{
				$temp_field = array(
				'field_id' => $id,
				'rel_id' => $temp_rels[$index],
				'exists' => FALSE,
				'required' => 0,
				'order' => $temp_order[$index],
				);
				
				if(is_array($temp_exists) && in_array($id,$temp_exists)){
					$temp_field['exists'] = TRUE;
				}

				if(is_array($temp_required) && in_array($id,$temp_required)){
					$temp_field['required'] = 1;
				}
				
				$field_updates[] = $temp_field;
			}
			$this->User_model->field_list = $field_updates;
			$this->User_model->update_meta_fields();

		}
		
	}// end _process_meta_fields()




























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