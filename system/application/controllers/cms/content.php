<?php
class Content extends MY_Controller
{

	function Content()
	{
		parent::Controller();
		$this->load->model('Content_model');
		$this->load->model('Content_type_model');
		$this->load->helper('image');
	}

	function index()
	{
		$this->site_map();
	}

	function site_map()
	{
		$this->_authenticate();
		$this->data['list'] = $this->Content_model->site_map(0,1,$this->Site_model->site_lang);
		$this->load->view('cms/site_map',$this->data);
	}

	function delete()
	{
		$this->_authenticate();

		$form_uri = $this->uri->uri_string();
		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Content_model->content_id = $params['id'];
			$this->data['msg'] = "<p>Are you sure you want to delete this content?</p>\r";
		}
		if(array_key_exists('lang',$params)) {
			$this->Content_model->content_lang = $params['lang'];
			$this->Language_model->load( $params['lang']);

			$this->data['msg'] = "<p>Are you sure you want to delete the '{$this->Language_model->Ref_Name}' version of this content?</p>\r";
		}

		if($this->Content_model->content_id != '') {
			$this->Content_model->get_content();
		}
		
		$temp_title = $this->Content_model->title;
		$this->data['title'] = $temp_title;

		$deleted = FALSE;

		// if confirmed, please delete appropriate content
		if($this->input->post('confirm') != '') {
			if(array_key_exists('lang',$params)) {
				$deleted = $this->Content_model->delete_lang();
			} else {
				$deleted = $this->Content_model->delete_content();
			}
		}

		if($deleted) {
			$this->data['msg'] = "The content '{$temp_title}' has been successfully deleted";
		} else {
			if($this->input->post('submit') != '') {
				$this->data['msg'] .= "<p>You must confirm the deletion</p>";
			}
			$body = "<form action=\"{$form_uri}\" method=\"post\" name=\"delete\">";
			$body .=  "<p><label><input name=\"confirm\" type=\"checkbox\" value=\"1\" /> yes</label></p>\r";
			$body .=  "<p><input name=\"submit\" type=\"submit\" value=\"Submit\" /></p>\r";
			$body .= "</form>\r";
			$this->data['body'] = $body;
		}

		$this->load->view('cms/content_delete',$this->data);

	}

	function edit()
	{
		$this->_authenticate();

		$this->load->model('User_model');
		$this->load->model('Field_model');
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

			$del_base = 'cms/content/delete/id/'.$this->Content_model->content_id;
			$del_lang = $del_base.'/lang/'.$this->Content_model->content_lang;
			
			$this->Language_model->load($this->Content_model->content_lang);

			$this->data['delete_links'] = "<p>Delete this content : ";
			$this->data['delete_links'] .= anchor($del_lang,$this->Language_model->Ref_Name, array('title' => 'Delete this language'));
			$this->data['delete_links'] .= " | ";
			$this->data['delete_links'] .= anchor($del_base,'All languages', array('title' => 'Delete this content in all languages'));
			$this->data['delete_links'] .= "</p>\n";

		}


		// generate content form

		if(array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type'];
		}

		if($this->Content_model->object_id != '') {
			$this->Content_type_model->object_id = $this->Content_model->object_id;
		} else {
			$this->Content_type_model->object_id = 1;
		}


		$this->Content_type_model->load_object();

		$obj_fields = $this->Content_type_model->fields;
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

			case 'directory':
				$this->load->model('Media_model');
				$this->Media_model->list_dirs();
				$media_root = $this->Media_model->dir_base;
				$dir_list_temp = $this->Media_model->dir_list;
				$dir_vals[''] = 'media root';
				
				foreach($dir_list_temp as $the_dir) {
					$temp_dir_val = str_replace($media_root,'',$the_dir);
					$dir_vals[$temp_dir_val] = $temp_dir_val;
				}
				
				$content .= form_dropdown($field_params['name'], $dir_vals, $fd['value']);
				break;
			case 'file':
				$this->load->model('Media_model');
				$this->Media_model->list_dirs();
				$media_root = $this->Media_model->dir_base;
				$dir_files = array('' => 'no file');
				foreach($this->Media_model->dir_list as $index => $dir)
				{
					$this->Media_model->dir_current = $dir;
					$dest_dir = str_replace($media_root,'',$dir);

					$this->Media_model->list_files();
					foreach($this->Media_model->file_list as $file_path) {
						$file_rel = str_replace($media_root,'',$file_path);
						$pinfo = pathinfo($file_path);
						$dir_files[$file_rel] = $pinfo['basename'];
					}
					
				}

				$content .= form_dropdown($field_params['name'], $dir_files, $fd['value']);
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

		$userArray = $this->User_model->get_list_dropdown();
		$typeArray = $this->Content_type_model->get_list_dropdown();
		$langArray = $this->Language_model->get_list_dropdown();
		$parentArray = $this->Content_model->get_parents_dropdown();

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
		$properties .=  form_dropdown('object_id', $typeArray, $this->Content_model->object_id);
		$properties .= "</p>\n";
		
		$properties .= "<p>";
		$properties .=  form_label('Parent', 'parent_id');
		$properties .=  form_dropdown('parent_id', $parentArray, $this->Content_model->parent_id);
		$properties .= "</p>\n";
	
		$field_params = array(1 => 'Yes', 0 => 'No');
		$properties .= "<p>";
		$properties .=  form_label('Display on home page', 'splash');
		$properties .=  form_dropdown('splash', $field_params, $this->Content_model->splash);
		$properties .= "</p>\n";

		$properties .= "<p>";
		$properties .=  form_label('Available to menus/indexes', 'index');
		$properties .=  form_dropdown('index', $field_params, $this->Content_model->index);
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

			$this->Content_type_model->object_id = $this->input->post('object_id');
			$this->Content_type_model->load_object();
			$this->Content_model->object_id = $this->input->post('object_id');
			$this->Content_model->object_name = $this->Content_type_model->name_uri;

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
				
				if($temp_label == 'keywords')
				{
					$temp_val = csv_normalize($temp_val);
				}
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
