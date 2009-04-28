<?php
class Content extends Controller
{

	function Content()
	{
		parent::Controller();

		// load models
		$this->load->model('Content_model');
		$this->load->model('Object_model');

	}

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
		}

	}

	function index()
	{
		$this->authenticate();

		$params = $this->uri->uri_to_assoc(4);

		$this->data['title'] = 'Content';
		$content = $this->Content_model->get_list();

		foreach($content as $row)
		{

			$this->Content_model->content_id = $row['content_id'];
			$this->Content_model->get_content();
			$link = "cms/content/edit/id/".$this->Content_model->content_id;
			if($this->Content_model->content_lang != '') {
				$link .= "/lang/".$this->Content_model->content_lang;
			}

			$list[$this->Content_model->content_id] = anchor($link, $this->Content_model->title, array('title' => "edit '{$this->Content_model->title}'"));

		}

		$this->data['list'] = ul($list);
		$this->load->view('cms/content_list',$this->data);
	}

	function get_langs()
	{
		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		return $langArray;

	}

	function get_objects()
	{
		// get list of object types
		$objects = $this->Object_model->get_list();
		foreach($objects as $row) {
			$objArray[$row['object_id']] = $row['name'];
		}
		return $objArray;

	}

	function get_users()
	{
		// get list of users
		$userArray = array();
		$users = $this->User_model->get_list();
		foreach($users as $row) {
			$userArray[$row['user_id']] = $row['name_last'].', '.$row['name_first'];
		}
		return $userArray;
	}

	function get_parents()
	{
		// get list of possible parent content
		$parents = $this->Content_model->getParents();
		$parentArray[0] = "site root";
		if(is_array($parents) && count($parents) > 0) {
			foreach($parents as $row) {
				if(array_key_exists('title',$row) && $row['title'] != '') {
					$pname = $row['title'];
				} else {
					$pname = $row['content_id'];
				}

				$parentArray[$row['content_id']] = $pname;
			}
		}

		return $parentArray;
	}

	function edit()
	{
		$this->authenticate();

		$this->load->model('User_model');
		$this->load->model('Field_model');
		$this->load->model('Language_model');
		$this->load->library('form_validation');		

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Content_model->content_id = $params['id'];
		}

		// if form submitted, process data
		if($this->input->post('submit') != '') {
			$this->process_form();
		}

		//load from database
		if(array_key_exists('id',$params)) {
			$this->Content_model->content_id = $params['id'];
		}
		
		if(array_key_exists('lang',$params)) {
			if($this->Content_model->content_lang == '') {
				$this->Content_model->content_lang = $params['lang'];
			}
		}
		if($this->Content_model->content_id != '') {
			$this->Content_model->get_content();
		}

		// generate content form

		if(array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type'];
		}

		if($this->Content_model->object_id != '') {
			$this->Object_model->object_id = $this->Content_model->object_id;
		} else {
			$this->Object_model->object_id = 1;
		}


		$this->Object_model->load_object();

		$obj_fields = $this->Object_model->fields;
		$content_data = $this->Content_model->content_data;

		$fields = array();
		
		// populate field array
		foreach($obj_fields as $dt) {
			$fields[$dt['field_id']]['validation'] 	= $dt['validation'];
			$fields[$dt['field_id']]['required'] 	= $dt['required'];
			$fields[$dt['field_id']]['order'] 		= $dt['order'];
			$fields[$dt['field_id']]['field_name'] 	= $dt['field_name'];
			$fields[$dt['field_id']]['input_type'] 	= $dt['input_type'];

			$fields[$dt['field_id']]['value'] 		= '';
			$fields[$dt['field_id']]['data_id'] 	= '';
		}

		if(is_array($content_data) && count($content_data) > 0) {

			foreach($content_data as $dt) {
				$fields[$dt['field_id']]['validation'] 	= $dt['validation'];
				$fields[$dt['field_id']]['required'] 	= $dt['required'];
				$fields[$dt['field_id']]['order'] 		= $dt['order'];
				$fields[$dt['field_id']]['value'] 		= $dt['value'];
				$fields[$dt['field_id']]['data_id'] 	= $dt['data_id'];
				$fields[$dt['field_id']]['field_name'] 	= $dt['field_name'];
				$fields[$dt['field_id']]['input_type'] 	= $dt['input_type'];
			}
		}

		// hidden fields
		$content = form_hidden('content_id', $this->Content_model->content_id);
		$content .= form_hidden('title_id', $this->Content_model->title_id);

		$field_params = array(
			'id' => 'title',
			'name' => 'title',
			'class' => 'text',
			'value' => $this->Content_model->title
		);

		$content .=  "<p>";
		$content .=  form_label('Title','title');
		$content .=  form_input($field_params);
		$content .=  "</p>";


		// now generate form input fields
		foreach($fields as $fd_id => $fd) {
			$field_params = array(
				'id' => $fd['field_name'],
				'name' => 'value_'.$fd_id,
				'value' => $fd['value'],
			);

			// hidden fields
			$content .=  form_hidden('field_id[]', $fd_id);
			$content .=  form_hidden('field_name[]', $fd['field_name']);
			$content .=  form_hidden('required[]', $fd['required']);
			$content .=  form_hidden('validation[]', $fd['validation']);
			$content .=  form_hidden('data_id[]', $fd['data_id']);

			$content .=  "<p>";
			$content .=  form_label($fd['field_name']);


			switch($fd['input_type']) {
			case 'file':
				$content .=  form_upload($field_params);
				$content .=  "</p>\n<p class=\"indent\">Maximum file size: ";
				break;
			case 'text':
				$field_params['class'] = $fd['input_type'];
				$content .=  form_input($field_params);
				break;
			case 'textarea':
				$field_params['class'] = $fd['input_type'];
				$field_params['cols'] = 38;
				$content .=  form_textarea($field_params);
				break;
			case 'richtext':
				$field_params['class'] = $fd['input_type'];
				$field_params['cols'] = 38;
				$content .=  form_textarea($field_params);
				break;
			default:
				$content .=  form_input($field_params);
			}

			$content .=  "</p>\n";

		}
		

		// generate meta form

		$userArray = $this->get_users();
		$objArray = $this->get_objects();
		$parentArray = $this->get_parents();
		$langArray = $this->get_langs();





		$meta =	"<p>";
		$meta .= form_label('Author', 'user_id');
		$meta .=  form_dropdown('user_id', $userArray, $this->Content_model->user_id);
		$meta .= "</p>\n";
		
		$field_params = array(
		'name'        => 'created',
		'id'          => 'created',
		'class'	=> 'small_input',
		'value' => $this->Content_model->created,
		);
		$meta .= "<p>";
		$meta .=  form_label('Created', 'created');
		$meta .=  form_input($field_params);
		$meta .= "</p>\n";

		$meta .= "<p>";
		$meta .=  form_label('Language', 'content_lang');
		$meta .=  form_dropdown('content_lang', $langArray, $this->Content_model->content_lang);
		$meta .= "</p>\n";


		if($this->Content_model->object_id == '' && array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type']; 
		}
		$properties = "<p>";
		$properties .=  form_label('Content type', 'object_id');
		$properties .=  form_dropdown('object_id', $objArray, $this->Content_model->object_id);
		$properties .= "</p>\n";
		
		$properties .= "<p>";
		$properties .=  form_label('Parent', 'parent_id');
		$properties .=  form_dropdown('parent_id', $parentArray, $this->Content_model->parent_id);
		$properties .= "</p>\n";
	
		$field_params = array(1 => 'Yes', 0 => 'No');
		$properties .= "<p>";
		$properties .=  form_label('Available to home page', 'splash');
		$properties .=  form_dropdown('splash', $field_params, $this->Content_model->splash);
		$properties .= "</p>\n";




		if($this->Content_model->content_id != '') {
			$this->data['action'] = site_url("cms/content/edit/id/".$this->Content_model->content_id);
		} else {
			$this->data['action'] = site_url("cms/content/edit/");
		}

		$this->data['content_id'] = $this->Content_model->content_id;
		$this->data['title_id'] = $this->Content_model->title_id;
		$this->data['content'] = $content;
		$this->data['meta'] = $meta;
		$this->data['properties'] = $properties;
		$this->data['uri'] = $this->Content_model->get_uri();

		$this->load->view('cms/content_edit',$this->data);

	}
	
	function process_form()
	{

		$form = array(
		'content_id' => $this->input->post('content_id'),
		'parent_id' => $this->input->post('parent_id'),
		'object_id' => $this->input->post('object_id'),
		'created' => $this->input->post('created'),
		'user_id' => $this->input->post('user_id'),
		'splash' => $this->input->post('splash'),
		'data_id' => $this->input->post('data_id'),
		'field_id' => $this->input->post('field_id'),
		'field_name' => $this->input->post('field_name'),
		'content_lang' => $this->input->post('content_lang'),
		'title' => $this->input->post('title'),
		'title_id' => $this->input->post('title_id'),
		'validation' => $this->input->post('validation'),
		'required' => $this->input->post('required'),
		'my_url' => $this->input->post('my_url'),
		);
		
		// set validation rules
		if($this->input->post('content_id') != '') {
			$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('title_id', 'Title ID', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('created', 'Date Created', 'required|is_date');
		}

		$this->form_validation->set_rules('object_id', 'Content Type', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('user_id', 'Author', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('parent_id', 'Parent', 'required|is_natural');

		$this->form_validation->set_rules('title', 'Title', 'required');

		foreach($form['field_id'] as $index => $id) {
			$this->Field_model->field_id = $id;
			$this->Field_model->load();
			$validation = '';
			$treq = array();
			if($form['required'][$index] == 1) {
				$treq[] = "required";
			}
			if($form['validation'][$index] != '') {
				$treq[] = $form['validation'][$index];
			}
			if(count($treq) > 0 ) {
				$validation = implode('|',$treq);
				$this->form_validation->set_rules("value_{$id}", $this->Field_model->field_name, $validation);
			}
		}

		// validate and process if all valid
		if ($this->form_validation->run() == FALSE)
		{
			$this->data['msg'] = validation_errors();
		} else {
			$success = TRUE;
			$msg_update = '';

			$this->Content_model->content_id = $this->input->post('content_id');
			$this->Content_model->parent_id = $this->input->post('parent_id');
			$this->Content_model->object_id = $this->input->post('object_id');
			if($this->input->post('created') == '' || $this->input->post('created') == '0000-00-00 00:00:00') {
				$this->Content_model->created = date('Y-m-d H:i:s');
			} else {
				$this->Content_model->created = $this->input->post('created');
			}
			$this->Content_model->user_id = $this->input->post('user_id');
			$this->Content_model->splash = $this->input->post('splash');
			$this->Content_model->content_lang = $this->input->post('content_lang');
			$this->Content_model->title = $this->input->post('title');
			$this->Content_model->title_id = $this->input->post('title_id');

			// update Content object
			if($this->Content_model->update_content() === FALSE) {
				$success = FALSE;
				$msg_update = "<p>Content could not be updated</p>\n";
			}
			
			// update title
			if($this->Content_model->update_title() === FALSE) {
				$success = FALSE;
				$msg_update = "<p>Title could not be updated</p>\n";
			}

			// update data
			foreach($form['field_id'] as $index => $fd_id) {
				$form_fd_name = 'value_'.$fd_id;
				
				$temp_label = $form['field_name'][$index];
				$temp_val = $this->input->post($form_fd_name);
				$temp_data_id = $form['data_id'][$index];

				if($this->Content_model->update_data($fd_id,$temp_val,$temp_data_id) === FALSE)
				{
					$success = FALSE;
					$msg_update .= "<p>Field '{$temp_label}' could not be updated</p>\n";
				}
			}
			
			if($success === TRUE) {
				$this->data['msg'] = "content updated successfully";
			} else {
				$this->data['msg'] = $msg_update;
			}

		} // end validation
	
	}
	
	function search()
	{
		$this->data['term'] = $this->input->post('search-text');
		$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));

		if($this->uri->segment(4) == 'admin') {
			$view = 'cms/search_results';
		} else {
			$this->load->model('Site_model');
			$this->Site_model->load_prefs();
			$view = 'themes/'.$this->Site_model->theme.'/search_results';
			
			$this->load->view('cms/search_results',$this->data);
		}

		$this->load->view($view,$this->data);
	}


}
?>
