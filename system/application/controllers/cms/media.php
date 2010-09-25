<?php

class Media extends MY_Controller {
		
	var $whichform;
	var $selected;
	
	function Media()
	{
		parent::MY_Controller();
		$this->load->helper(array('form','url','file','image'));

		$this->load->model('File_model');
		$this->load->model('Content_model');
		$this->_authenticate();

		$this->File_model->temp_lang = $this->Site_model->site_default_lang;
		$this->whichform = 'list';
		$this->selected = array();
		
	}
	
	function index()
	{
		redirect('/cms/media/manage/', 'refresh');
	}
	
	function scan()
	{
		$html = "";
		$this->File_model->lang_load();
		$tmp_files = $this->File_model->get_new_files(TRUE);
		if($this->File_model->new_file_count > 0)
		{

			$new_files = $this->File_model->new_file_list;
			$html .= "<p>".sprintf($this->cms_labels['media_scan_x_results'],$this->File_model->file_count)."</p>\r";
		
			foreach($new_files as $media)
			{
				$html .= "<div class=\"block\">\r";
				// get mime category
				// get filename and extension
				$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');

				$filename = basename($media['file_uri']);
				$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$media['file_uri']));
				$mid = $media['media_id'];
	
				// determine edit uri
				$edit_uri = 'cms/media/edit/id/'.$media['media_id'];
				if($this->File_model->temp_lang != '')
				{
					$edit_uri .= '/lang/'.$this->File_model->temp_lang;
				}

				if($media['mime_cat'] == 'image')
				{
					// get thumbnail
					$img_path = $this->File_model->dir_base.$media['file_uri'];
					$thumb = view_image($img_path,$this->Site_model->img_size_th,array('alt' => sprintf($this->cms_labels['edit_this'],$filename)));
					if($thumb)
					{
						$html .= "<div class=\"image-left\">";
						$html .= anchor($edit_uri, $thumb, array('title' => sprintf($this->cms_labels['edit_this'],$filename)))."<br />\r";
						$html .= "</div>\r";
					}
				}
	
				if(in_array($ext,$mime_icon_exts))
				{
					$html .= "<span class=\"icon ext_{$ext}\">";
				}
				else
				{
					$html .= "<span class=\"icon icon_".$media['mime_cat']."\">";
				}
				if($media['title'] != '' && $media['title'] != $filename)
				{
					$html .= $media['title']."<br />\r";
				}
				$html .= anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
				$html .= "</span></p>\r";
				$html .= "</div>\r";
			}
	
		}
		else
		{
			$html .= "<p>";
			$html .= $this->cms_labels['media_scan_no_results'];
			$html .= "</p>\r";
		}

		$this->data['form'] = $html;

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media/manage",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_scan'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_scan'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_scan'];

		$this->load->view('cms/cms_form_2col', $this->data);
	}

	function manage()
	{	
		$this->File_model->lang_load();
		
		$this->_process_manage();

		if(count($this->selected) > 0 && $this->whichform == 'confirm')
		{
			$this->_form_delete();
		}
		else
		{
			$this->_form_manage();
		}


		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['media'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media'];

		$this->menu_order = array('media','content','setup');
		$this->_load_cms_menus();
		
		$this->custom_js[] = "<script type=\"text/javascript\" src=\"/assets/js/admin_ui/select_checkboxes.js\"></script>";
		
		$confirm_delete = "<script type=\"text/javascript\">\r";

		$confirm_delete .= "/* <![CDATA[ */\r";
		$confirm_delete .= "$().ready(function(){\r";
		$confirm_delete .= "\tvar deleteText = '".$this->cms_labels['delete_confirm_multiple']."';\r";
		
		$confirm_delete .= "\t$(\"input[name='submit']\").click(function(){\r";
		$confirm_delete .= "\t\tvar action = $(\"select[name='action']\").val();\r";
		$confirm_delete .= "\t\tif(action == 'delete') {\r";
		$confirm_delete .= "\t\t\tvar answer = confirm(deleteText);\r";

		$confirm_delete .= "if(answer == true) {\r";
		$confirm_delete .= "$(\"input[name='confirm']\").val(1);\r";
		$confirm_delete .= "}\r";

		$confirm_delete .= "\t\t\treturn answer // answer is a boolean\r";
		$confirm_delete .= "\t\t}\r";
		$confirm_delete .= "\t});\r";

		$confirm_delete .= "});\r";
		$confirm_delete .= "/* ]]> */\r";
		$confirm_delete .= "</script>";
		
		$this->custom_js[] = $confirm_delete;
		
		$this->data['custom_js'] = implode("\r",$this->custom_js);

		$this->load->view('cms/cms_form_2col', $this->data);
	}

	function _form_manage()
	{
		// get filter for list
		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('lang',$params) && preg_match('#^([a-z]{3})$#', $params['lang']))
		{
			$this->File_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->File_model->temp_lang = $this->Site_model->temp_lang;
		}
		$this->File_model->lang_load();

		$action = 'cms/media/manage';
		$attributes = array(
		'id' => 'process'
		);
		$html = form_open($action, $attributes);
		$html .= form_hidden('confirm', '')."\r";
		
		$base_url = '/cms/media/manage';
		$html .= $this->_media_form_table($params,$base_url,true,$this->cms_labels['select']);

		$form_options = array(''=>'','delete' => 'delete','publish'=>'publish','unpublish'=>'unpublish');
		$html .= "<p class=\"align-right\">";
		$html .=  form_label($this->cms_labels['with_selected'].' : ', 'action', array('class' => 'inline'));
		$html .=  form_dropdown('action', $form_options);
		$html .= "&nbsp; ";

		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\r";
		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _form_delete()
	{
		$action = 'cms/media/manage';
		$attributes = array(
		'id' => 'process'
		);
		$html = form_open($action, $attributes);
		
		foreach($this->selected as $cid)
		{
			$html .= form_hidden('select[]', $cid);
		}
		$html .= form_hidden('action', 'delete');

		$params = array(
		'name'        => "confirm",
		'value'       => 1,
		);
		
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['delete_confirm_multiple'], 'confirm',array('class' => 'inline'))."?<br />\r";
		$html .= form_checkbox($params);
		$html .= "</p>\r";

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\r";
		$html .= form_close();

		$this->data['form'] = $html;
	
	}

	function _process_manage()
	{
		$select = $this->input->post('select');

		if(is_array($select))
		{
			$this->selected = $select;
			$action = $this->input->post('action');
			if($action)
			{
				switch($action)
				{
				case 'delete':
					$confirm = $this->input->post('confirm');
					if($confirm == 1)
					{
						$this->File_model->temp_value = $select;
						$this->File_model->delete_batch();
					}
					else
					{
						$this->whichform = 'confirm';
					}
					break;
				case 'publish':
					$this->File_model->publish = 1;
					if($this->File_model->update_publish())
					{
						$this->File_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->File_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;
				case 'unpublish':
					$this->File_model->publish = 0;
					if($this->File_model->update_publish())
					{
						$this->File_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->File_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;
				default:
				}
			}
		}
		$this->File_model->temp_value = '';

	}

	function reset_cache()
	{	

		$this->File_model->remove_media_cache();
		
		$thumbs = array();
		
		$files = $this->File_model->list_flat();
		foreach($files as $uri => $media)
		{
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
			$path_full = $this->File_model->dir_base.$uri;
			if($mime_cat == 'image')
			{
				$thumbs[] = view_image($path_full,$this->Site_model->img_size_th,array('alt' => $media['title']));
			}
			else
			{
				$poster_image = preg_replace('#^(.*)\.([a-zA-Z0-9]{2,6})$#','$1.jpg',$path_full);
				if(file_exists($poster_image))
				{
					$thumbs[] = view_image($poster_image,$this->Site_model->img_size_th,array('alt' => $media['title']));
				}
			
			}
		}

		$html = "<h2>Thumbnails</h2>\r";
		$html .= "<div class=\"block\">\r";
		foreach($thumbs as $img)
		{
			$html .= "<div class=\"image-left\">{$img}</div>\r";
		}
		$html .= "</div>\r";
		$this->data['form'] = $html;

		
		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['reset_media_cache'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['reset_media_cache'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['reset_media_cache'];

		$this->load->view('cms/media', $this->data);
		
	}

	function edit()
	{
		$params = $this->uri->uri_to_assoc(4);
		if( array_key_exists('id',$params))
		{
			$this->File_model->temp_id = $params['id'];
		}
		if( array_key_exists('lang',$params) )
		{
			$this->File_model->temp_lang = $params['lang'];
		}
		
		$this->_process_edit();
		$this->_form_edit();

		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_edit'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_edit'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_edit'];

		$this->data['menu_media'] = $this->_media_meta_menu();

		$this->load->view('cms/cms_form_2col', $this->data);
	}

	function _form_edit()
	{
		$this->load->helper('form');

		$action = 'cms/media/edit/';
		$attributes = array(
		'name' => 'media_editor',
		'id' => 'media_editor'
		);
		$hidden = array();

		if( $this->File_model->media_id != '')
		{
			$this->File_model->temp_id = $this->File_model->media_id;
		}

		if( $this->File_model->temp_id != '')
		{
			if($this->File_model->load_media())
			{
				$action .= 'id/'.$this->File_model->media_id;
	
				$this->File_model->load_type_by_mime();
	
				$this->File_model->load_title();
	
				$this->File_model->scope = 'types';
				$this->File_model->temp_id = $this->File_model->type_id;
				$this->File_model->list_fields_related();
	
				$this->File_model->scope = 'media';
				$this->File_model->temp_id = $this->File_model->media_id;
				$this->File_model->load_metadata();
	
				$hidden['media_id'] = $this->File_model->media_id;
				$hidden['title_id'] = $this->File_model->title_id;
			}

		}

		$html = form_open($action, $attributes,$hidden);
		
		$html .= form_fieldset($this->cms_labels['general_info']);
		$html .= "<div class=\"align-labels label-normal\">\r";
		
		if($this->File_model->type_uri_name == 'image')
		{
			if($this->File_model->external == 0)
			{
				$file_path_full = $this->File_model->dir_base.$this->File_model->file_uri;
				$html .= "<p class=\"indent-normal\">";
				$html .= view_image($file_path_full,$this->Site_model->img_size_sm,array('alt' => $this->File_model->title));
				$html .= "</p>";
			}
			else
			{
				$em_size = $this->Site_model->img_size_md / 15;
				$form_options = array(
				'src' => $this->File_model->file_uri,
				'alt' => $this->File_model->title,
				'style' => "width: {$em_size}em",
				'title' => $this->File_model->title,
				);

				$html .= "<p>";
				$html .= img($form_options);
				$html .= "</p>";
			}
		}


		$html .= form_hidden('external',$this->File_model->external);

		if($this->File_model->external == 1)
		{

			$form_options = array(
			'name'        => 'file_uri',
			'id'          => 'file_uri',
			'class'	=> 'input',
			'value' => $this->File_model->file_uri,
			);
			$html .= "<p>";
			$html .=  form_label($this->cms_labels['file_external_uri'], 'file_uri');
			$html .=  form_input($form_options);
			$html .= "</p>\r";

		}
		else
		{
			$html .= form_hidden('file_uri',$this->File_model->file_uri);
			$html .= form_hidden('mime_type',$this->File_model->mime_type);
		
			$html .= "<div class=\"row\">\r";
			$html .= form_label($this->cms_labels['file']);
			$html .= "\t<span>".$this->File_model->file_uri."</span>\r";
			$html .= "</div>\r";

			$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->File_model->file_uri));
			
			$this->File_model->lang_load();
			$this->lang->load('mime', $this->File_model->lang_CI_Name);
			$mime_names = $this->lang->line('mime');
	
			$html .= "<p>";
			$html .= form_label($this->cms_labels['mime']);
			if(array_key_exists($ext,$mime_names))
			{
				$html .= $mime_names[$ext];
			}
			else
			{
				$html .= $this->File_model->mime_type;
			}
			$html .= "</p>";
		}

		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['publish'], 'publish');
		$html .=  form_dropdown('publish', $form_options, $this->File_model->publish);
		$html .= "</p>\r";

		// convert dates to user timezone
		$sys_zone = date_default_timezone_get();
		$usr_zone = $this->session->userdata('timezone');
		
		if($usr_zone !== FALSE && $usr_zone != $sys_zone)
		{
			if($this->File_model->date_created != '')
			{
				$this->File_model->date_created = convert_time($this->File_model->date_created, $usr_zone);
			}
	
		}

		if($this->File_model->date_created == '0000-00-00 00:00:00')
		{
			$this->File_model->date_created = '';
		}

		$form_options = array(
		'name'        => 'date_created',
		'id'          => 'date_created',
		'class'	=> 'med_input',
		'value' => $this->File_model->date_created,
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['date_created'], 'date_created');
		$html .=  form_input($form_options);
		$html .= "</p>\r";

		$html .= "</div>\r";

		$html .= form_fieldset_close();
		
		$html .= form_fieldset($this->cms_labels['metadata']);
		$html .= "<div class=\"align-labels label-normal\">\r";

		$form_options = $this->File_model->langs_dropdown();
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['language'], 'media_lang');
		$html .=  form_dropdown('media_lang', $form_options, $this->File_model->lang_ISO_639_3);
		$html .= "</p>\r";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->File_model->title
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['title'],'title');
		$html .= form_input($form_options);
		$html .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->File_model->subtitle
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['subtitle'],'subtitle');
		$html .= form_input($form_options);
		$html .= "</p>";

		// start meta fields
		foreach($this->File_model->metadata as $index => $fd)
		{
			// get field data if it does not exist
			if(array_key_exists('field_id',$fd) && $fd['field_id'] != '')
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
				$this->File_model->temp_id = $index;
				if($this->File_model->load_field())
				{
	
					$tmp_ref_name = $this->File_model->field_ref_name;
					$tmp_needs_lang = $this->File_model->field_needs_lang;
					$tmp_input_type = $this->File_model->field_input_type;
					$tmp_validation = $this->File_model->field_validation;
					$tmp_label = $tmp_ref_name;
					$tmp_meta_id = '';
					$tmp_required = '';
				}
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
			$html .= form_hidden($form_options);

			$this->File_model->reset_label();
			$this->File_model->temp_id = $index;
			$this->File_model->label_scope = 'field';
			$this->File_model->temp_lang = $this->Site_model->temp_lang;

			if($this->File_model->load_label())
			{
				$tmp_label = $this->File_model->label;
			}

			$html .= "<p>\r";
			$html .= form_label($tmp_label, $tmp_ref_name);

			$form_options = array(
			'id' => $tmp_ref_name,
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $tmp_input_type
			);

			
			switch($tmp_input_type) {
			case 'textarea':
			case 'richtext':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
				break;
			case 'list':
				// load list values
				$this->File_model->temp_id = $fd['list_id'];
				$this->File_model->load_value_list();
				$listvals = prepare_list_values($this->File_model->value_list);

				$html .= ph_form_list($form_options['name'], $listvals, $fd['value'], $fd['field_allow_multiple']);

				break;

			default:
				$html .=  form_input($form_options);
			}


			$html .= "</p>\r";
		}
		// end meta fields

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->File_model->keywords
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['keywords'],'keywords');
		$html .=  form_textarea($form_options);
		$html .= "</p>";

		$html .= "</div>\r";
		$html .= form_fieldset_close();

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\r";
		$html .= form_close();

		$this->data['form'] = $html;

	}

	function _process_edit()
	{

		// if form submitted, process data
		if($this->input->post('submit') != '')
		{

			$this->load->library('form_validation');
			
			// set validation rules for media 

			$this->form_validation->set_rules('external', $this->cms_labels['file_external'], 'required');
			if($this->input->post('external') != 1)
			{
				$this->form_validation->set_rules('file_uri', $this->cms_labels['file'], 'required|path_file');
			}
			else
			{
				$this->form_validation->set_rules('file_uri', $this->cms_labels['file'], 'required|uri_exists');
			}

			$this->form_validation->set_rules('mime_type', $this->cms_labels['mime'], 'required');
			$this->form_validation->set_rules('date_created', $this->cms_labels['date_created'], 'is_date');
			$this->form_validation->set_rules('publish', $this->cms_labels['publish'], 'required');

			$this->form_validation->set_rules('title', $this->cms_labels['title'], 'required');

			// run form validation
			$form_valid = $this->form_validation->run();

			// load validated POST data
			$this->File_model->media_id = $this->input->post('media_id');
			$this->File_model->title_rel_id = $this->input->post('media_id');
			$this->File_model->file_uri = $this->input->post('file_uri');
			$this->File_model->external = $this->input->post('external');
			$this->File_model->mime_type = $this->input->post('mime_type');
			$this->File_model->date_created = $this->input->post('date_created');
			$this->File_model->publish = $this->input->post('publish');

			$this->File_model->title_id = $this->input->post('title_id');
			$this->File_model->title = $this->input->post('title');
			$this->File_model->subtitle = $this->input->post('subtitle');
			$this->File_model->keywords = $this->input->post('keywords');
		
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
				$this->File_model->label_scope = 'field';
				$this->File_model->temp_id = $fd_id;
	
				if($this->File_model->load_label())
				{
					$lbl_name = $this->File_model->label;
				}

				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd_id, $lbl_name, $concat_valid);
				}

			}

			// build metadata

			$form_meta = array();
			foreach($tmp_field_id as $index => $fd_id)
			{
				$val_lbl = 'value_'.$fd_id;
				
				$value = $this->input->post($val_lbl);
				if(is_array($value))
				{
					$value = $value[0];
				}

				$value = decode_html_entities($value);
				
				$this->File_model->temp_id = $fd_id;
				$this->File_model->load_field();
				
				$tmp_meta = array(
				'meta_id' => $tmp_meta_id[$index],
				'scope' => 'media',
				'rel_id' => $this->input->post('media_id'),
				'field_id' => $this->File_model->field_id,
				'field_ref_name' => $this->File_model->field_ref_name,
				'value' => $value,
				'lang_ISO_639_3' => '',
				'required' => $tmp_required[$index],
				'field_input_type' => $this->File_model->field_input_type,
				'field_validation' => $this->File_model->field_validation,
				'field_needs_lang' => $this->File_model->field_needs_lang,
				);

				if($tmp_field_needs_lang[$index] == 1)
				{
					$tmp_meta["lang_ISO_639_3"] = $this->File_model->temp_lang;
				}

				$form_meta["$fd_id"] = $tmp_meta;

			}

			if ($form_valid)
			{
				// update user
				$updated = $this->File_model->update_media();
				if($updated)
				{

					$this->File_model->scope = 'media';
					$this->File_model->update_title();
					
					$this->File_model->metadata = $form_meta;
					$this->File_model->update_metadata();
					
					if(count($this->File_model->messages) == 0)
					{
						$this->File_model->messages[] = sprintf($this->cms_labels['update_success'],$this->File_model->file_uri);
					}

				}
						
			}
			else
			{
				$this->User_model->messages[] = validation_errors();
			}


		}		
	}

	function _media_meta_menu()
    {
		$menu = '';
		
		if($this->File_model->media_id != '')
		{
		
			// list active languages
			$lang_links = array();
			$this->File_model->list_langs();
			foreach($this->File_model->lang_list as $llang)
			{
				$temp_link = 'cms/media/edit/id/'.$this->File_model->media_id.'/lang/'.$llang['ISO_639_3'];
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
			
			$menu .=  "<ul>\r";
			$menu .=  "<li>".$this->cms_labels['edit_by_lang']."\r";
			$menu .= ul($lang_links);
			$menu .=  "</li>\r";
	
			$menu .=  "</ul>\r";

		}

		return $menu;
	}

	function upload()
	{
		$this->_process_upload();

		if($this->File_model->temp_value != '')
		{
			$this->data['form'] = $this->File_model->temp_value;
		}
		else
		{
			$this->_form_upload();
		}
		
		
		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media/manage",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_upload'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$this->menu_order = array('media','content','setup');
		$this->_load_cms_menus();

		$title_tag[] = $this->cms_labels['media_upload'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_upload'];

		$this->load->view('cms/cms_form', $this->data);
	}

	function _form_upload()
	{
		$html = '';

		$attributes = array('name' => "file_uploader", 'id' => "file_uploader");
		$html .= form_open_multipart('cms/media/upload',$attributes)."\r";
		$html .= "<div class=\"align-labels label-normal\">\r";
		$field_params = array(
		'name'        => 'userfile',
		'id'          => 'userfile',
		'alt'          => $this->cms_labels['file'],
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['file'], 'userfile');
		$html .=  form_upload($field_params);
		$html .= "</p>\r";
				
		$this->File_model->list_dirs_display();
				
		$field_params = array(
		'name'        => 'dest_path',
		'id'          => 'dest_path',
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['path_dest'], 'dest_path');
		$html .=  form_dropdown('dest_path',$this->File_model->dir_list_rel);
		$html .= "</p>\r";

		$field_params = array(
		'name'      => 'new_path',
		'id'        => 'new_path',
		'class'     => 'text',
		'value'		=> '',
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['path_or_new'], 'new_path');
		$html .=  form_input($field_params);
		$html .= "</p>\r";
		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\r";
		$html .= "</div>\r";
		$html .= form_close();

		$this->data['form'] = $html;

		$html = "<p>".sprintf($this->cms_labels['max_upload_size'],$this->File_model->max_upload_display)."</p>\r";
		$exts = implode(', ',array_keys($this->File_model->mime_types));
		$html .= "<p>".sprintf($this->cms_labels['media_allowed_types'],$exts)."</p>\r";

		$this->data['sidebar'] = $html;
		$this->data['sidebar_panel'] = FALSE;

	}

	function _process_upload()
	{
		$this->File_model->calculate_max_size();
		$exts = array_unique(array_keys($this->File_model->mime_types));
		sort($exts);

		$upload_config = array();
		$upload_config['max_size'] = $this->File_model->max_upload_bytes;
		$upload_config['allowed_types'] = implode('|',$exts);

		// process form if submitted
		if($this->input->post('submit')) {

			$base = '';
			if(!chdir($this->File_model->dir_base))
			{
				$base = $this->File_model->dir_base;
			}
			
			$new_path = $base.'/'.$this->input->post('new_path');
			$upload_path = $base.'/'.$this->input->post('dest_path');

			$new_path = trim(str_replace('//','/',$new_path),'/');
			$upload_path = trim(str_replace('//','/',$upload_path),'/');

			if($new_path != '')
			{

				$this->File_model->debugging[] = "verifying path '{$new_path}'";
				
				if(file_exists($new_path))
				{
					$this->File_model->debugging[] = "path'{$new_path}' exists";
					$upload_path = $new_path;
				}
				else
				{
					$created_dir = mkdir($new_path, 0777, true);

					if($created_dir)
					{
						$this->File_model->debugging[] = "created path '{$new_path}'";
						$upload_path = $new_path;
						
						// now change permissions of new path
						$paths = explode('/',$new_path);
						$tmp_path = '';
						if(is_array($paths))
						{
							foreach($paths as $lvl)
							{
								$tmp_path .= '/'.$lvl;
								$tmp_path = trim($tmp_path);
								if($tmp_path == '')
								{
									@chmod('./', 0777);
								} 
								if($tmp_path != '' && is_dir($tmp_path))
								{
									@chmod($tmp_path, 0777);
								}
							}
							
						}
						
					}
					else
					{
						$this->File_model->debugging[] = "cannot create'{$new_path}'";
						exit();
					}
				}
			}
			$this->File_model->debugging[] = "upload path '{$upload_path}'";
			$perms = octal_permissions(fileperms($upload_path));
			if($upload_path == '')
			{
				$perms = octal_permissions(fileperms('./'));
			}
			$this->File_model->debugging[] = "permissions: ".$perms;

			$upload_config['upload_path'] = $upload_path;

			$this->load->library('upload', $upload_config);

			$success = $this->upload->do_upload();

			$html = '';
			if (!$success)
			{
				$this->File_model->messages[] = $this->upload->display_errors();
			}	
			else
			{
				$upload_data = $this->upload->data();
				$this->File_model->reset_media_vars(); 
				$this->File_model->file_current = $upload_data['full_path'];
				$this->File_model->file_uri = str_replace($this->File_model->dir_base,'',$upload_data['full_path']);
				$this->File_model->read_file_info();

				// if file in db, then load; otherwise get info from file and insert to db
				$this->File_model->update_file();

				// set link to edit file
				if($this->File_model->media_id != '')
				{
					$edit_uri = "cms/media/edit/id/".$this->File_model->media_id;
					$edit_text = sprintf($this->cms_labels['edit_this'],$upload_data['file_name']);
					$html .= "<p>".anchor($edit_uri,$edit_text)."</p>\r";
				}
				
				// if image show thumbnail
				if($upload_data['is_image'] == 1)
				{
					$html .= "<p>".view_image($this->File_model->file_current,$this->Site_model->img_size_md)."</p>\r";
				}

				// file name
				$html .= "<p><strong>";
				$html .= $this->cms_labels['file_name'];
				$html .= "</strong>: ".$upload_data['file_name']."</p>\r";
	
				// mime type
				// $ext = preg_replace("#^(.*)\.([a-zA-Z0-9]+)$#","$2",$file);
				if(array_key_exists('file_ext',$upload_data))
				{
					$ext = strtolower(trim($upload_data['file_ext'],'.'));
					$mime = $this->lang->line('mime');
					if(array_key_exists($ext,$mime))
					{
						$type = $mime[$ext];
					}
					else
					{
						$type = $this->File_model->mime_types[$ext];
					}
					
				}
				else
				{
					$type = $upload_data['file_type'];
				}
				$html .= "<p><strong>".$this->cms_labels['media_type']."</strong>: ".$type."</p>\r";

				// show image dimensions				
				if(array_key_exists('image_width',$upload_data))
				{
					$html .= "<p><strong>{$this->cms_labels['media_dim_w']}</strong>: {$upload_data['image_width']}</p>";
				}
				if(array_key_exists('image_height',$upload_data))
				{
					$html .= "<p><strong>{$this->cms_labels['media_dim_h']}</strong>: {$upload_data['image_height']}</p>";
				}

			}
			$this->File_model->temp_value = $html;
			
		} // end form processing
	}

	function cache()
	{
		$emptied = FALSE;
		if($this->input->post('confirm') == 1)
		{
			$emptied = $this->File_model->cache_empty();
		}
		
		if($emptied)
		{
			$this->File_model->messages[] = $this->cms_labels['media_cache_delete_success'];
		}
		else
		{
			$this->_form_cache();
		}

		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media/manage",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_cache_delete'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$this->menu_order = array('media','content','setup');
		$this->_load_cms_menus();

		$title_tag[] = $this->cms_labels['media_cache_delete'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_cache_delete'];

		$this->load->view('cms/cms_form_2col', $this->data);
	}
	
	function _form_cache()
	{
		$action = 'cms/media/cache';
		$attributes = array(
		'id' => 'cache'
		);
		$html = form_open($action, $attributes);

		$params = array(
		'name'        => "confirm",
		'value'       => 1,
		'class'	=> ''
		);
		
		$html .= "<p>".$this->cms_labels['media_cache_delete_confirm']."</p>\r";
		
		$html .= "<p class=\"label-margins\">";
		$html .= form_checkbox($params);
		$html .= form_label($this->cms_labels['yes'], 'confirm',array('class'=>'inline'));
		$html .= "</p>\r";

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\r";
		$html .= form_close();

		$this->data['form'] = $html;
	}

}
?>
