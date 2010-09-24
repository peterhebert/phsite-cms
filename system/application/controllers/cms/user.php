<?php
class User extends MY_Controller
{

	var $form_success = 0;
	// 0 => not submitted, 1 => success, -1 (or other vale) => not successful
	
	function User()
	{
		parent::MY_Controller();

		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->helper('form');
		$this->load->library('session');

		$this->_authenticate();
	} // end constructor

	function index()
	{

		$userlist = array();
		
		$this->User_model->clear_user_data();
		$this->User_model->temp_id = $this->session->userdata('user_id');
		$this->User_model->load_user();

		if($this->User_model->admin != 1)
		{
			echo "not admin";
			// if logged in user not an admin, show link to edit to their user account
			$user_name = $this->session->userdata('name_last').", ".$this->session->userdata('name_first');
			$user_url = "cms/user/edit/id/".$this->session->userdata('user_id');
			$userlist[] = anchor($user_url, $user_name);
		} else {

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

		}
		
		if(count($userlist) > 0)
		{
			$this->data['list'] = ul($userlist);
		} else {
			$this->data['msg'] = $this->cms_labels['user_none'];
		}

		$this->data['form'] = ul($userlist);

		$this->data['sidebar'] = $this->_meta_menu();

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['users'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$this->data['title_tag'] = $this->cms_labels['users'];
		$this->data['title_h1'] = $this->cms_labels['users'];

		$this->_load_cms_menus();
		
		$this->load->view('cms/cms_form',$this->data);


	} // end index()

	function edit()
	{

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/user",$this->cms_labels['users']);
		$breadcrumb[] = $this->cms_labels['user_edit'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['user_edit'];
		$title_tag[] = $this->cms_labels['users'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['user_edit'];

		$this->User_model->clear_user_data();

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->User_model->temp_id = $params['id'];
		}
		else if($this->input->post('user_id'))
		{
			$this->User_model->temp_id = $this->input->post('user_id');
		}
		else
		{
		}
		
		if(array_key_exists('lang',$params))
		{
			$this->User_model->temp_lang = $params['lang'];
		}

		$this->_process_edit();

		if($this->User_model->user_id != '')
		{
			$this->User_model->temp_id = $this->User_model->user_id;
		}

		if($this->User_model->temp_id != '')
		{
			$this->User_model->load_user();
		}

		if(!is_array($this->User_model->metadata) || count($this->User_model->metadata) < 1)
		{

			$this->User_model->scope = 'users';
	
			if ($this->User_model->temp_lang = '')
			{
				$this->User_model->temp_lang = $this->User_model->default_lang;
			}
			if ($this->User_model->temp_lang = '')
			{
				$this->User_model->temp_lang = $this->Site_model->site_default_lang;
			}

			$this->User_model->load_metadata();
		}

		$this->data['sidebar'] = $this->_meta_menu();
		
		$this->data['form'] = $this->_form_edit();

		$msg = $this->User_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);

	} // end edit()


	function _meta_menu()
    {
		// list active languages
		$this->User_model->temp_value = 'cms/user/edit/id/'.$this->User_model->user_id;

		$html = "<h2>".$this->cms_labels['users']."</h2>\r";
		$html .=  "<ul>\r";
		$html .=  "<li>".anchor("cms/user/",$this->cms_labels['users_edit'])."</li>\r";
		if($this->session->userdata('admin') == 1)
		{
			$html .=  "<li>".anchor("cms/user/edit",$this->cms_labels['user_new'])."</li>\r";
			$html .=  "<li>".anchor("cms/user/user_meta_fields",$this->cms_labels['users_meta_fields'])."</li>\r";
		}
		if($this->User_model->user_id != '')
		{
			$html .= "<li>".$this->cms_labels['user_profile']."\r";
			$html .= $this->User_model->lang_meta_menu();
			$html .= "</li>\r";
		}
		$html .=  "</li>\r";
		$html .=  "</ul>\r";

		return $html;
	}

	function _form_edit()
	{
		$this->load->helper('timezone');

		$hidden = array();
		$action = 'cms/user/edit';

		if($this->User_model->user_id != '')
		{
			$action .= '/id/'.$this->User_model->user_id;
			$hidden['user_id'] = $this->User_model->user_id;
		}
		
		if($this->User_model->temp_lang != '')
		{
			$action .= '/lang/'.$this->User_model->temp_lang;
		}

		if($this->User_model->user_rel_id != '')
		{
			$hidden['user_rel_id'] = $this->User_model->user_rel_id;
		}
		$attributes = array('id' => 'user_editor', 'name' => 'user_editor');

		$html = form_open($action, $attributes, $hidden)."\r";

		$html .= form_fieldset($this->cms_labels['user_info']);
		$html .= "<div class=\"align-labels label-normal\">\r";
		$html .= "<p>".$this->cms_labels['user_info_fields_req']."</p>\r";

		if($this->session->userdata('admin') == 1)
		{
			$options = array(0 => $this->cms_labels['basic_user'], 1  => $this->cms_labels['admin']);

			$html .= "<p>\r";
			$html .= form_label($this->cms_labels['user_type'], 'admin');
			$html .= form_dropdown('admin', $options, $this->User_model->admin);		
			$html .= "</p>\r";

		} else if ($this->User_model->admin != '') {
			$html .= "<input type=\"hidden\" name=\"admin\" id=\"admin\" value=\"{$this->User_model->admin}\" />\r";
		}
		else
		{
			$html .= "<input type=\"hidden\" name=\"admin\" id=\"admin\" value=\"0\" />\r";
		}
		
		$params = array(
		'name'        => 'name_first',
		'id'          => 'name_first',
		'value'       => $this->User_model->name_first,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['name_first'], 'name_first');
		$html .= form_input($params);
		$html .= "</p>\r";

		$params = array(
		'name'        => 'name_last',
		'id'          => 'name_last',
		'value'       => $this->User_model->name_last,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['name_last'], 'name_last');
		$html .= form_input($params);
		$html .= "</p>\r";

		$params = array(
		'name'        => 'username',
		'id'          => 'username',
		'value'       => $this->User_model->username,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['username'], 'username');
		$html .= form_input($params);
		$html .= "</p>\r";

		$params = array(
		'name'        => 'email',
		'id'          => 'email',
		'value'       => $this->User_model->email,
		);
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['email'], 'email');
		$html .= form_input($params);
		$html .= "</p>\r";

		$zones = timezone_dropdown();
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['timezone'], '');
		$html .= form_dropdown('timezone', $zones, $this->User_model->timezone);		
		$html .= "</p>\r";

		$options = $this->User_model->langs_dropdown();
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['default_lang'], 'default_lang');
		$html .= form_dropdown('default_lang', $options, $this->User_model->default_lang);		
		$html .= "</p>\r";

		$html .= "</div>\r";

		$html .= form_fieldset_close("\r"); 

		$html .= form_fieldset($this->cms_labels['password']);
		$html .= "<div class=\"align-labels label-wide\">\r";

		if ($this->User_model->user_id != '') {
			$lbl_pwd = $this->cms_labels['password_choose_new'];
			$lbl_conf = $this->cms_labels['password_confirm_new'];
		} else {
			$lbl_pwd = $this->cms_labels['password_choose'];
			$lbl_conf = $this->cms_labels['password_confirm'];
		}
		$params = array(
		'name'        => 'password',
		'id'          => 'password',
		'value'          => ''
		);
		
		$html .= "<p>\r";
		$html .= form_label($lbl_pwd, 'password');
		$html .= form_password($params);
		$html .= "</p>\r";
		
		$params = array(
		'name'        => 'password_confirm',
		'id'          => 'password_confirm',
		'value'          => ''
		);
		$html .= "<p>\r";
		$html .= form_label($lbl_conf, 'password_confirm');
		$html .= form_password($params);
		$html .= "</p>\r";

		$html .= "</div>\r";
		$html .= form_fieldset_close("\r"); 

		$html .= form_fieldset($this->cms_labels['user_profile']);
		$html .= "<div class=\"align-labels label-normal\">\r";

		$options = $this->User_model->langs_dropdown();
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['language'], 'meta_lang');
		$html .= form_dropdown('meta_lang', $options, $this->User_model->temp_lang);		
		$html .= "</p>\r";
		
		$this->User_model->scope = 'users';
		$this->User_model->list_fields_related();
		$meta_fields = $this->User_model->field_list;
		
		if($this->form_success > -1)
		{
			if($this->User_model->temp_lang == '' && $this->input->post('meta_lang') != '')
			{
				$this->User_model->temp_lang = $this->input->post('meta_lang');
			}
			if($this->User_model->temp_lang == '')
			{
				$this->User_model->temp_lang = $this->Site_model->site_default_lang;
			}
			$this->User_model->load_user_metadata();
		}

		$tmp_meta = $this->User_model->metadata;

		foreach($meta_fields as $fd_id => $fd)
		{
			if(!array_key_exists($fd_id,$tmp_meta))
			{
				$tmp_meta[$fd_id] = $meta_fields[$fd_id];
			}
		}
		$this->User_model->metadata = $tmp_meta;

		// now create fields
		foreach($this->User_model->metadata as $index => $fd)
		{
			$this->User_model->reset_label();

			// hidden fields
			$form_options = array(
			'field_id[]' =>  $index,
			'field_ref_name[]' =>  $fd['field_ref_name'] ,
			'meta_id[]' => $fd['meta_id'],
			'required[]' =>  $fd['required'],
			'field_validation[]' =>  $fd['field_validation'],
			'field_needs_lang[]' => $fd['field_needs_lang']
			);
			$html .= form_hidden($form_options);

			$this->User_model->label_scope = 'field';
			$this->User_model->temp_id = $index;

			$tmp_label = $fd['field_ref_name'];
			if($this->User_model->load_label())
			{
				$tmp_label = $this->User_model->label;
			}

			$html .= "<p>\r";
			$html .= form_label($tmp_label,  $fd['field_ref_name']);

			$form_options = array(
			'id' =>  $fd['field_ref_name'],
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $fd['field_input_type']
			);

			switch($fd['field_input_type']) {
			case 'list':

				// load list values

				$this->User_model->temp_id = $fd['list_id'];
				$this->User_model->load_value_list();
				$listvals = prepare_list_values($this->User_model->value_list);

				$html .= ph_form_list($form_options['name'], $listvals, $fd['value'], $fd['field_allow_multiple']);

				break;

			case 'directory':
				$this->load->model('File_model');
				$this->File_model->list_files(TRUE,TRUE,TRUE);

				$media_root = $this->File_model->dir_base;
				$dir_list_temp = $this->File_model->dir_list;

				$dir_vals['/'] = $this->cms_labels['media_root'];

				if(count($dir_list_temp) > 0)
				{
					foreach($dir_list_temp as $the_dir) {
						$dir_vals[$the_dir['file_uri']] = $the_dir['file_uri'];
					}
				}
				
				$html .= form_dropdown($form_options['name'], $dir_vals, $fd['value']);
				unset($dir_vals);
				break;
			case 'file':
				$this->load->model('File_model');
				$this->File_model->temp_value = '';
				$this->File_model->list_media();

				$media_root = $this->File_model->dir_base;
				$dir_files = array('' => 'no file');

				foreach($this->File_model->media_list as $path => $info)
				{
					$dir_files[$path] = $info['title'];
				}
				$html .= form_dropdown($form_options['name'], $dir_files, $fd['value']);
				unset($dir_files);
				break;
			case 'textarea':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
				break;
			case 'richtext':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
				break;
			default:
				$html .=  form_input($form_options);
			}

			$html .= "</p>\r";
			unset($form_options);

		}
		
		$html .= "</div>\r";
		$html .= form_fieldset_close("\r"); 

		$html .= form_submit('submit', $this->cms_labels['save'])."\r";

		$html .= form_close("\r");

		return $html;		
	}
	
			
	function _process_edit()
	{
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{

			$this->load->library('form_validation');

			// set validation rules for user

			$this->form_validation->set_rules('name_first', $this->cms_labels['name_first'], 'required');
			$this->form_validation->set_rules('name_last', $this->cms_labels['name_last'], 'required');
			$this->form_validation->set_rules('username', $this->cms_labels['username'], 'required|min_length[8]|alpha_dash|callback_username_unique');
			$this->form_validation->set_rules('timezone', $this->cms_labels['timezone'], 'required|is_timezone');
			$this->form_validation->set_rules('admin', $this->cms_labels['user_type'], 'required|is_natural');
			$this->form_validation->set_rules('default_lang', $this->cms_labels['default_lang'], 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('email', $this->cms_labels['email'], 'required|valid_email');

			if($this->input->post('password') != '' || $this->input->post('user_id') == '') {
				$this->form_validation->set_rules('password', $this->cms_labels['password'], 'required|min_length[8]|alpha_dash');
				$this->form_validation->set_rules('password_confirm', $this->cms_labels['password_confirm'], 'required|min_length[8]|alpha_dash|matches[password]');
			}
			
			// set validation rules for user metadata
			$this->User_model->scope = 'users';
			$this->User_model->list_fields_related();

			$meta_fields = $this->User_model->field_list;
			
			foreach($meta_fields as $fd)
			{
				// reset validation array
				$tmp_valid = array();

				// required?
				if($fd['required'] == 1)
				{
					$tmp_valid[] = 'required';
				}

				// stored validation rules
				if($fd['field_validation'] != '')
				{
					$tmp_valid[] = $fd['field_validation'];
				}
				
				// get field label
				$lbl_name = $fd['field_ref_name'];
				$this->User_model->label_scope = 'field';
				$this->User_model->temp_id = $fd['field_id'];
				if($this->User_model->load_label())
				{
					$lbl_name = $this->User_model->label;
				}

				// set rules
				$concat_valid = implode('|', $tmp_valid);
				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd['field_id'], $lbl_name, $concat_valid);
				}

			}

			// run form validation
			$form_valid = $this->form_validation->run();
			
			// load user info from validated POST data
			if($this->input->post('site_rel_id') != '')
			{
				$this->User_model->site_rel_id = $this->input->post('site_rel_id');
			}
			else
			{
				$this->User_model->site_rel_id = $this->Site_model->site_id;
			}
			
			$this->User_model->user_id = $this->input->post('user_id');
			$this->User_model->user_rel_id = $this->input->post('user_rel_id');
			$this->User_model->admin = $this->input->post('admin');
			$this->User_model->name_first = $this->input->post('name_first');
			$this->User_model->name_last = $this->input->post('name_last');
			$this->User_model->username = $this->input->post('username');
			$this->User_model->timezone = $this->input->post('timezone');
			$this->User_model->default_lang = $this->input->post('default_lang');
			$this->User_model->email = $this->input->post('email');
			$this->User_model->temp_lang = $this->input->post('meta_lang');

			// load user metadata from validated POST data
			$meta_ids = $this->input->post('meta_id');
			$field_names = $this->input->post('field_ref_name');
			$needs_lang = $this->input->post('field_needs_lang');
			$required_fds = $this->input->post('required');
			$field_validation = $this->input->post('field_validation');
			
			$fields = $this->input->post('field_id');

			foreach($fields as $index => $fd_id)
			{
				$row_key = strval($fd_id);
				
				$meta_fields[$row_key]['rf_rel_ref_name'] = $this->input->post('username');
				$meta_fields[$row_key]['field_validation'] = $field_validation[$index];
				$meta_fields[$row_key]['meta_id'] = $meta_ids[$index];
				$meta_fields[$row_key]['rel_id'] = $this->input->post('user_id');

				$val_lbl = 'value_'.$fd_id;
				$meta_fields[$row_key]['value'] = html_entity_decode($this->input->post($val_lbl),ENT_COMPAT,"UTF-8");

				$tmp_lang = '';
				if($needs_lang[$index] == 1)
				{
					$tmp_lang = $this->input->post('meta_lang');
				}
				$meta_fields[$row_key]['lang_ISO_639_3'] = $tmp_lang;
			}

			$this->User_model->metadata = $meta_fields;
			
			if ($form_valid)
			{
				// update user
				$updated = $this->User_model->update_user();
				if($updated)
				{
					$this->User_model->update_site_user();

					$this->User_model->scope = 'users';
					$this->User_model->update_metadata();
					
					if(count($this->User_model->messages) == 0)
					{
						$this->form_success = 1;
						$this->User_model->messages[] = sprintf($this->cms_labels['update_success'],$this->User_model->username);
					}

				}
						
			}
			else
			{
				$this->User_model->messages[] = validation_errors();
				$this->form_success = -1;
			}


		} // end from processing

	} // end _process_form()


	function user_meta_fields()
	{

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/user",$this->cms_labels['users']);
		$breadcrumb[] = $this->cms_labels['users_meta_fields'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['users_meta_fields'];
		$title_tag[] = $this->cms_labels['users'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['users_meta_fields'];

		$this->_process_meta_fields();
		
		$this->data['form'] = $this->_form_meta_fields();

		$msg = $this->User_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
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

		$this->User_model->list_user_meta_fields();
		
		$fieldCount = count($this->User_model->field_list);
		$fieldNo = 1;

		$form .= "<table id=\"properties\" class=\"dotted\">\r";
		$form .= "<thead>\r";
		$form .= "<tr>\r";
		$form .= "<th id=\"fd_name\" class=\"align-left\">".$this->cms_labels['field']."</th>\r";
		$form .= "<th id=\"fd_exists\">".$this->cms_labels['field_user_profile']."</th>\r";
		$form .= "<th id=\"fd_req\">".$this->cms_labels['required']."</th>\r";
		$form .= "<th id=\"fd_order\">".$this->cms_labels['order']."</th>\r";
		$form .= "</tr>\r";
		$form .= "</thead>\r";
		
		$form .= "<tbody>\r";

		foreach($this->User_model->field_list as $field)
		{
			$row_header = form_hidden('field_id[]', $field['field_id']);
			$row_header .= form_hidden('rf_id[]', $field['rf_id']);

			/* get label for field */
			$this->User_model->reset_label();
			$this->User_model->label_scope = 'field';
			$this->User_model->temp_id = $field['field_id'];
			$this->User_model->temp_lang = $this->Site_model->site_default_lang;
			$this->User_model->load_label();

			if($this->User_model->label_id != '') {
				$row_header .= $this->User_model->label;
			} else {
				$row_header .= $field['field_ref_name'];
			}

			if($field['rf_id'] != '') {
				$exists = true;
			} else {
				$exists = false;
			}

			$params = array(
			'name'        => 'exists[]',
			'id'          => 'exists[]',
			'value'       => $field['field_id'],
			'checked'     => $exists
			);
	
			$chk_exist = form_checkbox($params);

			$params = array(
			'name'        => 'required[]',
			'id'          => 'required[]',
			'value'       => $field['field_id'],
			'checked'     => $field['required']
			);

			$chk_req = form_checkbox($params);

			$params = array(
			'name'        => "order[]",
			'id'          => "order[]",
			'value'       => $field['order'],
			'maxlength'   => '3',
			'size'        => '3'
			);

			$input_order = form_input($params);
			
			$form .= "<tr>\r";
			$form .= "<th headers=\"fd_name\" id=\"row_{$fieldNo}\" class=\"align-left\">".$row_header."</th>\r";
			$form .= "<td headers=\"fd_exists row_{$fieldNo}\">".$chk_exist."</td>\r";
			$form .= "<td headers=\"fd_req row_{$fieldNo}\">".$chk_req."</td>\r";
			$form .= "<td headers=\"fd_order row_{$fieldNo}\">".$input_order."</td>\r";
			$form .= "</tr>\r";

			$fieldNo += 1;

		}
		$form .= "</tbody>\r";
		$form .= "</table>\r";


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
			$temp_rels = $this->input->post('rf_id');
			$temp_exists = $this->input->post('exists');
			$temp_required = $this->input->post('required');
			$temp_order = $this->input->post('order');

			$field_updates = array();
			foreach($temp_fields as $index => $id)
			{
				$temp_field = array(
				'field_id' => $id,
				'rf_id' => $temp_rels[$index],
				'rf_rel_id' => $this->Site_model->site_id,
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
			$this->User_model->scope = 'users';
			$this->User_model->update_fields_related();

		}
		
	}// end _process_meta_fields()

	function username_unique($str)
	{
		$this->User_model->temp_value = $str;
		$this->User_model->temp_id = $this->input->post('user_id');
		$unique = $this->User_model->username_unique();
		if ($unique)
		{
			return TRUE;
		}
		else
		{
			$msg = sprintf($this->cms_labels['username_taken'],$str);
			$this->form_validation->set_message('username_unique', $msg);
			return FALSE;
		}
	}

/*


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