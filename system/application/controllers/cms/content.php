<?php
class Content extends MY_Controller
{
	
	function Content()
	{
		parent::Controller();
		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->helper('image');

		$this->load->helper('timezone');
		$this->load->library('session');

		$this->_authenticate();
//		$this->_load_site_metadata();
//		$this->_load_cms_labels();
	}

	function index()
	{

		$this->Content_model->levels = -1;
		$this->Content_model->link_mode = 1;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		if($this->Content_model->lang_ISO_639_3 == '')
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}

		$body = $this->Content_model->site_map();

		$this->data['form'] = $body;

		$this->data['title_h1'] = $this->cms_labels['content'];

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['content'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$this->data['title_tag'] = $this->cms_labels['content'].' ';
		
		$this->load->view('cms/cms_form',$this->data);
	}

	function edit()
	{
		if($this->session->userdata('message'))
		{
			$this->Content_model->messages[] = $this->session->userdata('message');
			$this->session->unset_userdata('message');
		}
		

		$this->_process_edit();
		
		$params = $this->uri->uri_to_assoc(4);
		if( array_key_exists('id',$params) && $this->Content_model->content_id == '')
		{
			$this->Content_model->content_id = $params['id'];
		}
		if( array_key_exists('type',$params) )
		{
			$this->Content_model->temp_value = $params['type'];
		}
		if( array_key_exists('lang',$params) )
		{
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
		}

		$this->Content_model->load_content_form();
		
		$this->_form_edit();

		$msg = $this->Content_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/content",$this->cms_labels['content']);
		$breadcrumb[] = $this->cms_labels['content_editor'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);



		$this->data['title_tag'] = $this->cms_labels['content_editor'];
		$this->data['title_h1'] = $this->cms_labels['content_editor'];
		
		$this->data['sidebar'] = $this->_content_meta_menu();

		$this->load->view('cms/content_edit',$this->data);

	}
	function _form_edit()
	{
		$this->load->helper('form');

		$action = 'cms/content/edit';
		$my_url = $action;
		$attributes = array(
		'name' => 'content_editor',
		'id' => 'content_editor'
		);

		if($this->Content_model->content_id != '')
		{
			$action .= '/id/'.$this->Content_model->content_id;
		}
		if($this->Content_model->temp_lang != '')
		{
			$action .= '/lang/'.$this->Content_model->temp_lang;
		}

		$form = form_open($action, $attributes);
		$form .= form_hidden('content_id',$this->Content_model->content_id);
		$form .= form_hidden('title_id',$this->Content_model->title_id);
		
		$form .= form_hidden('my_url',site_url($my_url));
		
		$this->data['form_open'] = $form;
		
		$form = form_fieldset('Content');

		$form_options = $this->Content_model->langs_dropdown();
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['language'], 'content_lang');
		$form .=  form_dropdown('content_lang', $form_options, $this->Content_model->temp_lang);
		$form .= "</p>\n";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->Content_model->title
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['title'],'title');
		$form .= form_input($form_options);
		$form .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->Content_model->subtitle
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['subtitle'],'subtitle');
		$form .= form_input($form_options);
		$form .= "</p>";

		// fields
		foreach($this->Content_model->metadata as $index => $fd)
		{
			$this->Content_model->reset_label();
			// get field data if it does not exist
			if(array_key_exists('field_id',$fd))
			{
				$tmp_ref_name = $fd['field_ref_name'];
				$tmp_label = $fd['field_ref_name'];
				$tmp_needs_lang = $fd['field_needs_lang'];
				$tmp_meta_id = $fd['meta_id'];
				$tmp_required = $fd['required'];
				$tmp_input_type = $fd['field_input_type'];
				$tmp_validation = $fd['field_validation'];
			}
			else
			{
				$this->Content_model->temp_id = $index;
				$this->Content_model->load_field();

				$tmp_ref_name = $this->Content_model->field_ref_name;
				$tmp_needs_lang = $this->Content_model->field_needs_lang;
				$tmp_input_type = $this->Content_model->field_input_type;
				$tmp_validation = $this->Content_model->field_validation;
				$tmp_label = $tmp_ref_name;
				$tmp_meta_id = '';
				$tmp_required = '';
			}

			// hidden fields
			$form_options = array(
			'field_id[]' =>  $index,
			'field_ref_name[]' =>  $tmp_ref_name,
			'meta_id[]' => $tmp_meta_id,
			'required[]' =>  $tmp_required,
			'field_validation[]' =>  $tmp_validation,
			'field_needs_lang[]' => $tmp_needs_lang
			);
			$form .= form_hidden($form_options);

			$this->Content_model->reset_label();
			$this->Content_model->label_scope = 'field';
			$this->Content_model->temp_id = $index;
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;

			if($this->Content_model->load_label())
			{
				$tmp_label = $this->Content_model->label;
			}

			$form .= "<p>\r";
			$form .= form_label($tmp_label, $tmp_ref_name);

			$form_options = array(
			'id' => $tmp_ref_name,
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $tmp_input_type
			);

			switch($tmp_input_type) {

			case 'directory':
				$this->load->model('Media_model');
				$this->Media_model->list_dirs();
				$media_root = $this->Media_model->dir_base;
				$dir_list_temp = $this->Media_model->dir_list;
				$dir_vals['/'] = $this->cms_labels['media_root'];
				
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
				$dir_files = array('' => $this->cms_labels['media_no_file']);
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

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->Content_model->keywords
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['keywords'],'keywords');
		$form .=  form_textarea($form_options);
		$form .= "</p>";

		$form .= form_fieldset_close();

		$this->data['form'] = $form;

		$form = form_fieldset($this->cms_labels['general_info']);

		$this->Content_model->scope = 'content';
		$form_options = $this->Content_model->types_dropdown();
		
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['content_type'], 'type_id');
		$form .=  form_dropdown('type_id', $form_options, $this->Content_model->type_id);
		$form .= "</p>\n";

		$form_options = $this->Content_model->get_parents_dropdown();
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['parent'], 'parent_id');
		$form .=  form_dropdown('parent_id', $form_options, $this->Content_model->parent_id);
		$form .= "</p>\n";
	
		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['publish'], 'publish');
		$form .=  form_dropdown('publish', $form_options, $this->Content_model->publish);
		$form .= "</p>\n";

		$form_options = $this->User_model->get_list_dropdown();
		$form .= "<p>";
		$form .= form_label($this->cms_labels['author'], 'user_id');
		$form .=  form_dropdown('user_id', $form_options, $this->Content_model->user_id);
		$form .= "</p>\n";
		

		// convert dates to user timezone
		$sys_zone = date_default_timezone_get();
		$usr_zone = $this->session->userdata('timezone');
		
		if($usr_zone !== FALSE && $usr_zone != $sys_zone)
		{
			if($this->Content_model->date_created != '')
			{
				$this->Content_model->date_created = convert_time($this->Content_model->date_created, $usr_zone);
			}
			if($this->Content_model->date_end_expiry != '')
			{
				$this->Content_model->date_end_expiry = convert_time($this->Content_model->date_end_expiry, $usr_zone);
			}
	
		}

		if($this->Content_model->date_created == '0000-00-00 00:00:00')
		{
			$this->Content_model->date_created = '';
		}
		if($this->Content_model->date_end_expiry == '0000-00-00 00:00:00')
		{
			$this->Content_model->date_end_expiry = '';
		}

		$form_options = array(
		'name'        => 'date_created',
		'id'          => 'date_created',
		'class'	=> 'small_input',
		'value' => $this->Content_model->date_created,
		);
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['date_created_start'], 'date_created');
		$form .=  form_input($form_options);
		$form .= "</p>\n";

		$form_options = array(
		'name'        => 'date_end_expiry',
		'id'          => 'date_end_expiry',
		'class'	=> 'small_input',
		'value' => $this->Content_model->date_end_expiry,
		);
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['date_end_expiry'], 'date_end_expiry');
		$form .=  form_input($form_options);
		$form .= "</p>\n";

		$form .= form_fieldset_close();
		$this->data['form_meta'] = $form;


		$form = "<p>";
		$form .= form_submit('submit', $this->cms_labels['save']);
		$form .= "</p>\n";
		$form .= form_close();

		$this->data['form_close'] = $form;

	}

	function _process_edit()
	{
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$this->load->library('form_validation');		

			// set validation rules
			$this->form_validation->set_rules('type_id', $this->cms_labels['content_type'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('user_id', $this->cms_labels['author'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('parent_id', $this->cms_labels['parent'], 'required|is_natural');
			$this->form_validation->set_rules('title', $this->cms_labels['title'], 'required');

			$tmp_field_id = $this->input->post('field_id');
			$tmp_field_ref_name = $this->input->post('field_ref_name');
			$tmp_meta_id = $this->input->post('meta_id');
			$tmp_required = $this->input->post('required');
			$tmp_field_needs_lang = $this->input->post('field_needs_lang');
			$tmp_validation = $this->input->post('field_validation');

			// set validation of field data
			foreach($tmp_field_id as $index => $fd_id)
			{
				$tmp_valid = array();

				//is the field required?
				if($tmp_required[$index] == 1)
				{
					$tmp_valid[] = 'required';
				}

				// get field validation
				if($tmp_validation[$index] != '')
				{
					$tmp_valid[] = $tmp_validation[$index];
				}
				
				$lbl_name = $tmp_field_ref_name[$index];
				
				// load label
	
				$this->Content_model->label_scope = 'field';
				$this->Content_model->temp_id = $fd_id;
	
				if($this->Content_model->load_label())
				{
					$lbl_name = $this->Content_model->label;
				}

				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd_id, $lbl_name, $concat_valid);
				}

			}

			// validate and process if all valid
			$form_is_valid = $this->form_validation->run();

			if ($form_is_valid)
			{

				$this->Content_model->content_id = $this->input->post('content_id');
				$this->Content_model->user_id = $this->input->post('user_id');
				$this->Content_model->parent_id = $this->input->post('parent_id');
				$this->Content_model->date_created = $this->input->post('date_created');
				$this->Content_model->date_end_expiry = $this->input->post('date_end_expiry');
				$this->Content_model->publish = $this->input->post('publish');

				$this->Content_model->title_id = $this->input->post('title_id');
				$this->Content_model->temp_lang = $this->input->post('content_lang');
				$this->Content_model->title = $this->input->post('title');
				$this->Content_model->subtitle = $this->input->post('subtitle');
				$this->Content_model->keywords = $this->input->post('keywords');

				$this->Content_model->temp_id = $this->input->post('type_id');
				$this->Content_model->scope = 'content';
				$this->Content_model->load_type();
	
				$tmp_field_id = $this->input->post('field_id');
				$tmp_field_name = $this->input->post('field_name');
				$tmp_meta_id = $this->input->post('meta_id');
				$tmp_required = $this->input->post('required');
				$tmp_field_needs_lang = $this->input->post('field_needs_lang');

				
				// first update content object, then title, then field metadata
				if($this->Content_model->update_content())
				{
					$this->Content_model->title_rel_id = $this->Content_model->content_id;
					$this->Content_model->scope = 'content';

					if(!$this->Content_model->update_title())
					{
						$this->Content_model->messages[] = $this->cms_labels['title_update_failure'];
					}
					
					// build metadata

					$form_meta = array();
					foreach($tmp_field_id as $index => $fd_id)
					{
						$val_lbl = 'value_'.$fd_id;
						$value = $this->input->post($val_lbl);
						
						$this->Content_model->temp_id = $fd_id;
						$this->Content_model->load_field();
						
						$tmp_meta = array(
						'meta_id' => $tmp_meta_id[$index],
						'scope' => $this->Content_model->scope,
						'rel_id' => $this->Content_model->content_id,
						'field_id' => $this->Content_model->field_id,
						'field_ref_name' => $this->Content_model->field_ref_name,
						'value' => $value,
						'lang_ISO_639_3' => '',
						'required' => $tmp_required[$index],
						'field_input_type' => $this->Content_model->field_input_type,
						'field_validation' => $this->Content_model->field_validation,
						'field_needs_lang' => $this->Content_model->field_needs_lang,
						);

						if($tmp_field_needs_lang[$index] == 1)
						{
							$tmp_meta["lang_ISO_639_3"] = $this->Content_model->temp_lang;
						}

						$form_meta["$fd_id"] = $tmp_meta;
		
					}

					$this->Content_model->metadata = $form_meta;

					$this->Content_model->update_metadata();

					if(count($this->Content_model->messages) == 0)
					{
						$this->session->set_userdata('message', $this->Content_model->cms_labels['content_update_success']);
						$my_url = 'cms/content/edit/id/'.$this->Content_model->content_id.'/lang/'.$this->Content_model->temp_lang;
						redirect($my_url);
					}
					
				}

				
			}
			else
			{
				$this->Content_model->messages[] = validation_errors();
			}

		}

	}

	function _content_meta_menu()
    {
		$menu = '';
		
		if($this->Content_model->content_id != '')
		{
		
			// list active languages
			$lang_links = array();
			$this->Content_model->list_langs();
			foreach($this->Content_model->lang_list as $llang)
			{
				$temp_link = 'cms/content/edit/id/'.$this->Content_model->content_id.'/lang/'.$llang['ISO_639_3'];
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
	
			$this->Content_model->temp_id = $this->Content_model->content_id;
			$my_uri = $this->Content_model->generate_uri();
			$my_link = anchor($my_uri,$this->cms_labels['view_content'], array('title' => $this->cms_labels['view_content_title'], 'rel' => 'external'));
			
			$menu .=  "<ul>\r";
			$menu .=  "<li>".$my_link."\r";
			$menu .=  "<li>".$this->cms_labels['edit_by_lang']."\r";
			$menu .= ul($lang_links);
			$menu .=  "</li>\r";
	
/*			$del_base = 'cms/content/delete/id/'.$this->Content_model->content_id;
			if($this->Content_model->temp_lang != '')
			{
				$del_lang = $del_base.'/lang/'.$this->Content_model->temp_lang;
				$this->Content_model->lang_load();
				$del_links[] = anchor($del_lang,$this->Content_model->lang_Ref_Name, array('title' => $this->cms_labels['delete_this_lang']));
			}

			$del_links[] = anchor($del_base,$this->cms_labels['all_langs'], array('title' => $this->cms_labels['delete_content_all_langs']));

			$menu .= "<li>".$this->cms_labels['delete'];
			$menu .= ul($del_links);
			$menu .= "</li>\r";
*/
			$menu .=  "</ul>\r";

		}

		return $menu;
	}

	function search()
	{

		if($this->input->post('search-text') != '')
		{
			$search_string = $this->input->post('search-text');
			$this->data['term'] = sprintf($this->cms_labels['search_term'],$search_string);
			$this->Content_model->temp_value = $search_string;
			$this->Content_model->link_mode = 1;

			$matches = $this->Content_model->search_content();

			// content matches
			if(count($matches) > 0)
			{
				$this->data['content_results'] = $matches;
			}

			// media matches
		}

		$view = 'cms/search_results';

		$this->load->view($view,$this->data);
	}
}
?>
