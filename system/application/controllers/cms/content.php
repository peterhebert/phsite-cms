<?php
class Content extends MY_Controller
{

	function Content()
	{
		parent::Controller();
		$this->load->model('Content_model');
		$this->load->model('Content_type_model');
		$this->load->model('User_model');
		$this->load->model('Field_model');
		$this->load->model('Language_model');
		$this->load->helper('image');

		//$this->_authenticate();
		$this->_load_site_metadata();
	}

	function index()
	{

		$this->Content_model->link_mode = 1;
		$this->Content_model->lang_mode = $this->Language_model->lang_count;
		$this->Content_model->content_lang = $this->Site_model->site_default_lang;
		
		$body = $this->Content_model->site_map();

		$this->data['form'] = $body;

		$breadcrumb = anchor("cms","phSite CMS").' : Content';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Content ';
		$this->data['title_h1'] = 'Content';
		
		//$this->data['form'] = $form;

		$this->load->view('cms/cms_form',$this->data);
	}

	function edit()
	{
		$this->load->helper('timezone');
		$this->load->library('session');

		$breadcrumb = anchor("cms","phSite CMS").' : ';
		$breadcrumb .= anchor("cms/content","Content").' : ';
		$breadcrumb .= 'Content editor';
		$this->data['breadcrumb'] = $breadcrumb;

		$this->data['title_tag'] = 'Content editor ';
		$this->data['title_h1'] = 'Content editor';
		
		$params = $this->uri->uri_to_assoc(4);
		if( array_key_exists('id',$params) ) {
			$this->Content_model->content_id = $params['id'];
		}
		if( array_key_exists('lang',$params) ) {
			$this->Content_model->content_lang = $params['lang'];
		}
		if( array_key_exists('type',$params) ) {
			$this->Content_model->type_id = $params['type'];
		}

		$this->_process_form();

		$this->Content_model->get_content();
		
		$this->data['form'] = $this->_form_edit();

		$msg = $this->Content_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->load->view('cms/cms_form',$this->data);

	}

	function _form_edit()
	{
		$this->load->helper('form');

		$action = $this->uri->uri_string();
		$attributes = array(
		'name' => 'content_editor',
		'id' => 'content_editor'
		);

		$form = form_open($action, $attributes);
		if($this->Content_model->content_id != '')
		{
			$form .= form_hidden('content_id', $this->Content_model->content_id);

			$del_base = 'cms/content/delete/id/'.$this->Content_model->content_id;
			if($this->Content_model->content_lang != '')
			{
				$del_lang = $del_base.'/lang/'.$this->Content_model->content_lang;
				$this->Language_model->ISO_639_3 = $this->Content_model->content_lang;
				$this->Language_model->load();
				$del_links[] = anchor($del_lang,$this->Language_model->Ref_Name, array('title' => 'Delete this language'));
			}

			$del_links[] = anchor($del_base,'all languages', array('title' => 'Delete this content in all languages'));

			$delete = "<p>Delete : ";
			$delete .= implode(" | ",$del_links);
			$delete .= "</p>\r";
		}

		if($this->Content_model->content_title_id != '')
		{
			$form .= form_hidden('content_title_id', $this->Content_model->content_title_id);
		}

		$form .= form_fieldset('General information');

		$form_options = $this->Content_type_model->get_list_dropdown();
		$form .= "<p>";
		$form .=  form_label('Content type', 'type_id');
		$form .=  form_dropdown('type_id', $form_options, $this->Content_model->type_id);
		$form .= "</p>\n";

		$form_options = $this->Content_model->get_parents_dropdown();
		$form .= "<p>";
		$form .=  form_label('Parent', 'parent_id');
		$form .=  form_dropdown('parent_id', $form_options, $this->Content_model->parent_id);
		$form .= "</p>\n";
	
		$form_options = array(1 => 'Yes', 0 => 'No');

		$form .= "<p>";
		$form .=  form_label('Publish', 'publish_status');
		$form .=  form_dropdown('publish_status', $form_options, $this->Content_model->publish_status);
		$form .= "</p>\n";

		$form .= "<p>";
		$form .=  form_label('Display on home page', 'splash');
		$form .=  form_dropdown('splash', $form_options, $this->Content_model->splash);
		$form .= "</p>\n";

		$form .= "<p>";
		$form .=  form_label('Available to menus/indexes', 'index');
		$form .=  form_dropdown('index', $form_options, $this->Content_model->index);
		$form .= "</p>\n";

		$form_options = $this->User_model->get_list_dropdown();
		$form .= "<p>";
		$form .= form_label('Author', 'user_id');
		$form .=  form_dropdown('user_id', $form_options, $this->Content_model->user_id);
		$form .= "</p>\n";
		
		// convert dates to user timezone
		$sys_zone = date_default_timezone_get();
		$usr_zone = $this->session->userdata('timezone');
		
		if($usr_zone !== FALSE && $usr_zone != $sys_zone)
		{
			if($this->Content_model->date_1 != '')
			{
				$this->Content_model->date_1 = convert_time($this->Content_model->date_1, $usr_zone);
			}
			if($this->Content_model->date_2 != '')
			{
				$this->Content_model->date_2 = convert_time($this->Content_model->date_2, $usr_zone);
			}
	
		}

		if($this->Content_model->date_1 == '0000-00-00 00:00:00')
		{
			$this->Content_model->date_1 = '';
		}
		if($this->Content_model->date_2 == '0000-00-00 00:00:00')
		{
			$this->Content_model->date_2 = '';
		}

		$form_options = array(
		'name'        => 'date_1',
		'id'          => 'date_1',
		'class'	=> 'small_input',
		'value' => $this->Content_model->date_1,
		);
		$form .= "<p>";
		$form .=  form_label('Created / Start Date', 'date_1');
		$form .=  form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'name'        => 'date_2',
		'id'          => 'date_2',
		'class'	=> 'small_input',
		'value' => $this->Content_model->date_2,
		);
		$form .= "<p>";
		$form .=  form_label('End / Expiry Date', 'date_2');
		$form .=  form_input($form_options);
		$form .= "</p>\n";

		$form .= form_fieldset_close();

		$form .= form_fieldset('Content');
		$form .= $delete;

		$form_options = $this->Language_model->get_list_dropdown();
		$form .= "<p>";
		$form .=  form_label('Language', 'content_lang');
		$form .=  form_dropdown('content_lang', $form_options, $this->Content_model->content_lang);
		$form .= "</p>\n";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->Content_model->title
		);
		$form .= "<p>";
		$form .= form_label('Title','title');
		$form .= form_input($form_options);
		$form .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->Content_model->subtitle
		);
		$form .= "<p>";
		$form .= form_label('Subitle','subtitle');
		$form .= form_input($form_options);
		$form .= "</p>";

		$fd_meta = $this->Content_model->content_metadata;

		foreach($fd_meta as $index => $fd)
		{

			// hidden fields
			$form_options = array(
			'field_id[]' =>  $index,
			'field_name[]' =>  $fd['field_name'],
			'content_meta_id[]' => $fd['content_meta_id'],
			'required[]' =>  $fd['required'],
			'field_needs_lang[]' => $fd['field_needs_lang']
			);
			
			$form .= form_hidden($form_options);

			$tmp_label = $fd['field_ref_name'];
			if($fd['field_name'] != '')
			{
				$tmp_label = $fd['field_name'];
			}
			$form .= "<p>\r";
			$form .= form_label($tmp_label, $fd['field_ref_name']);
			$form_options = array(
			'id' => $fd['field_ref_name'],
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $fd['field_input_type']
			);
			switch($fd['field_input_type']) {

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
				
				$form .= form_dropdown($form_options['name'], $dir_vals, $fd['value']);
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

				$form .= form_dropdown($form_options['name'], $dir_files, $fd['value']);
				break;
			case 'textarea':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			case 'richtext':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			default:
				$form .=  form_input($form_options);
			}
			$form .= "</p>\r";
		}
		$form .= form_fieldset_close();
		
		$form .= "<p>";
		$form .= form_submit('submit', 'Save');
		$form .= "</p>\n";

		$form .= form_close();
		return $form;

	}

	function _process_form()
	{
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$this->load->library('form_validation');		

			// set validation rules
			$this->form_validation->set_rules('type_id', 'Content Type', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('user_id', 'Author', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('parent_id', 'Parent', 'required|is_natural');
			$this->form_validation->set_rules('title', 'Title', 'required');

			$this->Content_model->get_content_meta_fields();
			$meta_fields = $this->Content_model->content_meta_fields;
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
				$lbl_name = $fd['field_ref_name'];
				if($fd['field_name'] != '')
				{
					$lbl_name = $fd['field_name'];
				}

				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd['field_id'], $lbl_name, $concat_valid);
				}

			}
			
			// run validation and load field values
			$form_is_valid = $this->form_validation->run();
			
			$this->Content_model->content_id = $this->input->post('content_id');
			$this->Content_model->user_id = $this->input->post('user_id');
			$this->Content_model->parent_id = $this->input->post('parent_id');
			$this->Content_model->type_id = $this->input->post('type_id');
			
			// load type info
			$this->Content_type_model->type_id = $this->input->post('type_id');
			$this->Content_type_model->load_type();
			$this->Content_model->type_name = $this->Content_type_model->type_name_uri;
			
			$this->Content_model->publish_status = $this->input->post('publish_status');
			$this->Content_model->date_1 = $this->input->post('date_1');
			$this->Content_model->date_2 = $this->input->post('date_2');
			$this->Content_model->splash = $this->input->post('splash');
			$this->Content_model->index = $this->input->post('index');
			
			$this->Content_model->content_title_id = $this->input->post('content_title_id');
			$this->Content_model->content_lang = $this->input->post('content_lang');
			$this->Content_model->title = $this->input->post('title');
			$this->Content_model->subtitle = $this->input->post('subtitle');

			$tmp_field_id = $this->input->post('field_id');
			$tmp_field_name = $this->input->post('field_name');
			$tmp_meta_id = $this->input->post('content_meta_id');
			$tmp_required = $this->input->post('required');
			$tmp_field_needs_lang = $this->input->post('field_needs_lang');
			
			$form_meta = array();
			foreach($tmp_field_id as $index => $tmp_id)
			{
				$val_lbl = 'value_'.$tmp_id;
				$value = $this->input->post($val_lbl);
				
				$this->Field_model->field_id = $tmp_id;
				$this->Field_model->load();
				
				if($this->Field_model->field_ref_name == 'keywords')
				{
					$value = csv_normalize($value);
				}
				
				$tmp_meta = array(
				'content_meta_id' => $tmp_meta_id[$index],
				'field_id' => $tmp_id,
				'field_name' => $tmp_field_name[$index],
				'value' => $value,
				'required' => $tmp_required[$index],
				'rel_id' => '',
				'order' => '',
				'field_ref_name' => $this->Field_model->field_ref_name,
				'field_input_type' => $this->Field_model->field_input_type,
				'field_validation' => $this->Field_model->field_validation,
				'field_needs_lang' => $this->Field_model->field_needs_lang,
				);
				
				$form_meta["$tmp_id"] = $tmp_meta;

			}
			$this->Content_model->content_metadata = $form_meta;

			// validate and process if all valid
			if ($form_is_valid)
			{

				// convert dates to system timezone
				$sys_zone = date_default_timezone_get();
				$usr_zone = $this->session->userdata('timezone');

				if($usr_zone !== FALSE && $usr_zone != $sys_zone)
				{
					if($this->Content_model->date_1 != '')
					{
						$this->Content_model->date_1 = convert_time($this->Content_model->date_1, $sys_zone, "Y-m-d H:i:s", $usr_zone);
					}
					if($this->Content_model->date_2 != '')
					{
						$this->Content_model->date_2 = convert_time($this->Content_model->date_2, $sys_zone, "Y-m-d H:i:s", $usr_zone);
					}
			
				}
				if($this->Content_model->date_1 == '')
				{
					$this->Content_model->date_1 = date('Y-m-d H:i:s');
				}

				// update Content 
				$this->Content_model->update_content();
				$this->Content_model->update_title();
				$this->Content_model->update_metadata();

				if(count($this->Content_model->messages) == 0)
				{
					$this->Content_model->messages[] = "content updated successfully";
				}
			}
			else
			{
				$this->Content_model->messages[] = validation_errors();
			}


		}

	}

/*	function delete()
	{

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

	
	

*/
	function search()
	{
		$this->Content_model->link_mode = 1;
		$this->data['term'] = $this->input->post('search-text');
		$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));
		$this->data['query'] = $this->db->last_query();

		$view = 'cms/search_results';

		$this->load->view($view,$this->data);
	}
}
?>
