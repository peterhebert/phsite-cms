<?php

class Media extends MY_Controller {
		
	var $whichform;
	var $selected;
	
	function Media()
	{
		parent::MY_Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Media_model');
		$this->load->model('Content_model');
		$this->_authenticate();

		$this->Media_model->temp_lang = $this->Site_model->site_default_lang;
		$this->whichform = 'list';
		$this->selected = array();
	}
	
	// dialog for jquery file tree
	function filetree()
	{

		$root = '/'.trim(FCPATH,'/');

		$dir = urldecode($this->input->post('dir'));

		$filepath = $root . $dir;
		
		
		$filetree = '';
		
		if( file_exists($filepath) )
		{
			$dir_handle = @opendir($filepath) or die("Unable to open $filepath");
			while ($file = readdir($dir_handle))
			{
				$newpath = str_replace('//','/',$filepath.'/'.$file);
				
				// ignore all hidden files and directory navigation (.*)
				$ignore = preg_match("#^\..*$#",$file);
				if($ignore <= 0)
				{
					$rel = $dir .'/'. $file;
					$rel = str_replace('//','/',$rel);
					$rel = htmlentities($rel) ;

					if(is_dir($newpath))
					{
						$filetree .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"{$rel}/\">" . htmlentities($file) . "</a></li>\r";
					}
					else
					{
						$class = $this->get_class_by_extension($newpath);
						$filetree .= "<li class=\"{$class}\"><a href=\"#\" rel=\"{$rel}\">" . htmlentities($file) . "</a></li>\r";
					}
					
				}
			}
		}
		if($filetree != '')
		{
			$filetree = "<ul class=\"jqueryFileTree\">{$filetree}</ul>\r";
		}
		echo $filetree;

	}
	
	function get_class_by_extension($file)
	{
		
		$path_parts = @pathinfo($file);
		$ext = strtolower($path_parts['extension']);
		
		$this->load->config('mime_info');
		
		$mime_icons = $this->config->item('mime_icons');
		$mime_types = $this->config->item('mime_types');
		
		if(in_array($ext,$mime_icons))
		{
			$class = 'file ext_'.$ext;
		}
		else if(array_key_exists($ext,$mime_types))
		{
			$icon = preg_replace("#^([a-z]+)/.*$#","$1",$mime_types[$ext]);
			$class = 'icon_'.$icon;
		}
		else
		{
			$class = 'file';
		}
		
		return $class;
	}

	// end jquery file tree

	function scan()
	{
		$html = "";
		$this->Media_model->lang_load();
		$tmp_files = $this->Media_model->list_media_recursive();
		
		if($this->Media_model->file_count > 0)
		{

			$new_files = $this->Media_model->file_list;
			$html .= "<p>".sprintf($this->cms_labels['media_scan_x_results'],$this->Media_model->file_count)."</p>\r";
		
			foreach($new_files as $media)
			{

				$html .= "<p>";
				// get mime category
				// get filename and extension
				$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');
				$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
				$filename = basename($media['file_uri']);
				$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$media['file_uri']));
				$mid = $media['media_id'];
	
				// determine edit uri
				$edit_uri = 'cms/media/edit/id/'.$media['media_id'];
				if($this->Media_model->temp_lang != '')
				{
					$edit_uri .= '/lang/'.$this->Media_model->temp_lang;
				}

				if($mime_cat == 'image')
				{
					$html .= "<div class=\"image-left\">";
					// get thumbnail
					$img_path = $this->Media_model->dir_base.$media['file_uri'];
					$thumb = view_image($img_path,$this->Site_model->img_size_th,array('alt' => sprintf($this->cms_labels['edit_this'],$filename)));
					$html .= anchor($edit_uri, $thumb, array('title' => sprintf($this->cms_labels['edit_this'],$filename)))."<br />\r";
					$html .= "</div>";
				}
	
				if(in_array($ext,$mime_icon_exts))
				{
					$html .= "<span class=\"icon ext_{$ext}\">";
				}
				else
				{
					$html .= "<span class=\"icon icon_{$mime_cat}\">";
				}
				if($media['title'] != '' && $media['title'] != $filename)
				{
					$html .= $media['title']."<br />\r";
				}
				$html .= anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
				$html .= "</span></p>\r";

				
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
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_scan'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_scan'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_scan'];

		$this->load->view('cms/media', $this->data);
	}

	function manage()
	{	
		$this->Media_model->lang_load();
		
		$this->_process_manage();
		if(count($this->selected) > 0 && $this->whichform == 'confirm')
		{
			$this->_form_delete();
		}
		else
		{
			$this->_form_manage();
		}

		$msg = $this->Media_model->_output_messages();
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

		$this->load->view('cms/media', $this->data);
	}

	function _form_manage()
	{
		// get filter for list
		$params = $this->uri->uri_to_assoc(3);

		$links[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
		$links[] = anchor("cms/media/scan",$this->cms_labels['media_scan']);
		$links[] = anchor("cms/media/upload",$this->cms_labels['media_upload']);
		$html = ul($links);
		$this->data['sidebar'] = $html;

		$action = 'cms/media/manage';
		$attributes = array(
		'id' => 'process'
		);
		$html = form_open($action, $attributes);
		$html .= form_hidden('confirm', '')."\r";
		
		$base_url = '/cms/media';
		$html .= $this->_media_form_table($params,$base_url,true,$this->cms_labels['select']);

		$form_options = array(''=>'','delete' => 'delete','publish'=>'publish','unpublish'=>'unpublish');
		$html .= "<p class=\"align-right\">";
		$html .=  form_label($this->cms_labels['with_selected'].' : ', 'action', array('class' => 'inline'));
		$html .=  form_dropdown('action', $form_options);
		$html .= "&nbsp; ";

		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\n";
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
		$html .= "</p>\n";

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\n";
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
						$this->Media_model->temp_value = $select;
						$this->Media_model->delete_batch();
					}
					else
					{
						$this->whichform = 'confirm';
					}
					break;
				case 'publish':
					$this->Media_model->publish = 1;
					if($this->Media_model->update_publish())
					{
						$this->Media_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->Media_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;
				case 'unpublish':
					$this->Media_model->publish = 0;
					if($this->Media_model->update_publish())
					{
						$this->Media_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->Media_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;
				default:
				}
			}
		}
		$this->Media_model->temp_value = '';

	}

	function reset_cache()
	{	

		$this->Media_model->remove_media_cache();
		
		$thumbs = array();
		
		$files = $this->Media_model->list_flat();
		foreach($files as $uri => $media)
		{
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
			$path_full = $this->Media_model->dir_base.$uri;
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

		
		$msg = $this->Media_model->_output_messages();
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
		if( array_key_exists('id',$params) && $this->Media_model->media_id == '')
		{
			$this->Media_model->media_id = $params['id'];
		}
		if( array_key_exists('lang',$params) )
		{
			$this->Media_model->temp_lang = $params['lang'];
		}
		
		$this->_process_edit();
		$this->_form_edit();

		$msg = $this->Media_model->_output_messages();
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

		$html = $this->_media_meta_menu();

		$this->data['sidebar'] = $html;

		$this->load->view('cms/cms_form', $this->data);
	}

	function _form_edit()
	{
		$this->load->helper('form');

		$action = 'cms/media/edit/id/'.$this->Media_model->media_id;
		$attributes = array(
		'name' => 'media_editor',
		'id' => 'media_editor'
		);

		if( $this->Media_model->media_id != '')
		{
			$this->Media_model->temp_id = $this->Media_model->media_id;
			$this->Media_model->load_media();
			$this->Media_model->load_type_by_mime();
			$this->Media_model->load_media();

			$this->Media_model->load_title();
			$this->Media_model->scope = 'types';
			$this->Media_model->temp_id = $this->Media_model->type_id;
			$this->Media_model->list_fields_related();

			$this->Media_model->scope = 'media';
			$this->Media_model->temp_id = $this->Media_model->media_id;
			$this->Media_model->load_metadata();

		}

		$html = form_open($action, $attributes);
		$html .= form_hidden('media_id',$this->Media_model->media_id);
		$html .= form_hidden('title_id',$this->Media_model->title_id);
		
		$html .= form_fieldset($this->cms_labels['general_info']);
		
		if($this->Media_model->type_uri_name == 'image')
		{
			if($this->Media_model->external == 0)
			{
				$file_path_full = $this->Media_model->dir_base.$this->Media_model->file_uri;
				$html .= "<p>";
				$html .= view_image($file_path_full,$this->Site_model->img_size_md,array('alt' => $this->Media_model->title));
				$html .= "</p>";
			}
			else
			{
				$em_size = $this->Site_model->img_size_md / 15;
				$form_options = array(
				'src' => $this->Media_model->file_uri,
				'alt' => $this->Media_model->title,
				'style' => "width: {$em_size}em",
				'title' => $this->Media_model->title,
				);

				$html .= "<p>";
				$html .= img($form_options);
				$html .= "</p>";
			}
		}


		$html .= form_hidden('external',$this->Media_model->external);
		if($this->Media_model->external == 1)
		{

			$form_options = array(
			'name'        => 'file_uri',
			'id'          => 'file_uri',
			'class'	=> 'input',
			'value' => $this->Media_model->file_uri,
			);
			$html .= "<p>";
			$html .=  form_label($this->cms_labels['file_external_uri'], 'file_uri');
			$html .=  form_input($form_options);
			$html .= "</p>\n";

		}
		else
		{
			$html .= form_hidden('file_uri',$this->Media_model->file_uri);
			$html .= form_hidden('mime_type',$this->Media_model->mime_type);
		
			$html .= "<div class=\"row\">";
			$html .= form_label($this->cms_labels['file']);
			$html .= "<div class=\"filename\">";
			$filename = str_replace('/','/ ',$this->Media_model->file_uri);
			$filename = str_replace('/ / ','//',$filename);
			$html .= $filename;
			$html .= "</div>";
			$html .= "</div>";

			$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->Media_model->file_uri));
			
			$this->Media_model->lang_load();
			$this->lang->load('mime', $this->Media_model->lang_CI_Name);
			$mime_names = $this->lang->line('mime');
	
			$html .= "<p>";
			$html .= form_label($this->cms_labels['mime']);
			if(array_key_exists($ext,$mime_names))
			{
				$html .= $mime_names[$ext];
			}
			else
			{
				$html .= $this->Media_model->mime_type;
			}
			$html .= "</p>";
		}

		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['publish'], 'publish');
		$html .=  form_dropdown('publish', $form_options, $this->Media_model->publish);
		$html .= "</p>\n";

		// convert dates to user timezone
		$sys_zone = date_default_timezone_get();
		$usr_zone = $this->session->userdata('timezone');
		
		if($usr_zone !== FALSE && $usr_zone != $sys_zone)
		{
			if($this->Media_model->date_created != '')
			{
				$this->Media_model->date_created = convert_time($this->Media_model->date_created, $usr_zone);
			}
	
		}

		if($this->Media_model->date_created == '0000-00-00 00:00:00')
		{
			$this->Media_model->date_created = '';
		}

		$form_options = array(
		'name'        => 'date_created',
		'id'          => 'date_created',
		'class'	=> 'med_input',
		'value' => $this->Media_model->date_created,
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['date_created'], 'date_created');
		$html .=  form_input($form_options);
		$html .= "</p>\n";

		$html .= form_fieldset_close();
		
		$html .= form_fieldset($this->cms_labels['metadata']);

		$form_options = $this->Media_model->langs_dropdown();
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['language'], 'media_lang');
		$html .=  form_dropdown('media_lang', $form_options, $this->Media_model->temp_lang);
		$html .= "</p>\n";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->Media_model->title
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['title'],'title');
		$html .= form_input($form_options);
		$html .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->Media_model->subtitle
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['subtitle'],'subtitle');
		$html .= form_input($form_options);
		$html .= "</p>";

		// start meta fields
		foreach($this->Media_model->metadata as $index => $fd)
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
				$this->Media_model->temp_id = $index;
				if($this->Media_model->load_field())
				{
	
					$tmp_ref_name = $this->Media_model->field_ref_name;
					$tmp_needs_lang = $this->Media_model->field_needs_lang;
					$tmp_input_type = $this->Media_model->field_input_type;
					$tmp_validation = $this->Media_model->field_validation;
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

			$this->Media_model->reset_label();
			$this->Media_model->temp_id = $index;
			$this->Media_model->label_scope = 'field';
			$this->Media_model->temp_lang = $this->Site_model->temp_lang;

			if($this->Media_model->load_label())
			{
				$tmp_label = $this->Media_model->label;
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
			case 'textarea' || 'richtext':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
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
		'value' => $this->Media_model->keywords
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['keywords'],'keywords');
		$html .=  form_textarea($form_options);
		$html .= "</p>";

		$html .= form_fieldset_close();

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();

		$this->data['form'] = $html;

	}

	function _process_edit()
	{
		// if form submitted, process data
		if($this->input->post('submit') != '')
		{
			$this->load->library('form_validation');

			// set validation rules
			$this->form_validation->set_rules('media_id', $this->cms_labels['id'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('title', $this->cms_labels['title'], 'required');
			
			if($this->input->post('external') == 1)
			{
				$this->form_validation->set_rules('file_uri', $this->cms_labels['file'], 'required|uri_exists');
			}
			else
			{
				$this->form_validation->set_rules('file_uri', $this->cms_labels['file'], 'required|path_file');
			}
			

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
	
				$this->Media_model->label_scope = 'field';
				$this->Media_model->temp_id = $fd_id;
	
				if($this->Media_model->load_label())
				{
					$lbl_name = $this->Media_model->label;
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
				$this->Media_model->media_id = $this->input->post('media_id');
				$this->Media_model->file_uri = $this->input->post('file_uri');
				$this->Media_model->external = $this->input->post('external');
				$this->Media_model->mime_type = $this->input->post('mime_type');
				$this->Media_model->date_created = $this->input->post('date_created');
				$this->Media_model->publish = $this->input->post('publish');

				$this->Media_model->title_id = $this->input->post('title_id');
				$this->Media_model->temp_lang = $this->input->post('media_lang');
				$this->Media_model->title = $this->input->post('title');
				$this->Media_model->subtitle = $this->input->post('subtitle');
				$this->Media_model->keywords = $this->input->post('keywords');

			//	$this->Media_model->temp_id = $this->input->post('type_id');
			//	$this->Media_model->scope = 'media';
			//	$this->Media_model->load_type();

				$tmp_field_id = $this->input->post('field_id');
				$tmp_field_name = $this->input->post('field_name');
				$tmp_meta_id = $this->input->post('meta_id');
				$tmp_required = $this->input->post('required');
				$tmp_field_needs_lang = $this->input->post('field_needs_lang');

				$this->Media_model->messages[] = 'form is valid';
			}
			else
			{
				$this->Media_model->messages[] = validation_errors();
			}
		}		
	}

	function _media_meta_menu()
    {
		$menu = '';
		
		if($this->Media_model->media_id != '')
		{
		
			// list active languages
			$lang_links = array();
			$this->Media_model->list_langs();
			foreach($this->Media_model->lang_list as $llang)
			{
				$temp_link = 'cms/media/edit/id/'.$this->Media_model->media_id.'/lang/'.$llang['ISO_639_3'];
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
		if($this->Media_model->temp_value != '')
		{
			$this->data['form'] = $this->Media_model->temp_value;
		}
		else
		{
			$this->_form_upload();
		}
		
		$msg = $this->Media_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_upload'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

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
		$field_params = array(
		'name'        => 'userfile',
		'id'          => 'userfile',
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['file'], 'userfile');
		$html .=  form_upload($field_params);
		$html .= "</p>\r";
		
		$this->Media_model->list_dirs_display();
				
		$field_params = array(
		'name'        => 'dest_path',
		'id'          => 'dest_path',
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['path_dest'], 'dest_path');
		$html .=  form_dropdown('dest_path',$this->Media_model->dir_list_rel);
		$html .= "</p>\r";

		$field_params = array(
		'name'        => 'new_path',
		'id'          => 'new_path',
		'class'          => 'text',
		);

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['path_or_new'], 'new_path');
		$html .=  form_input($field_params);
		$html .= "</p>\r";
		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();

		$this->data['form'] = $html;

		$exts = array_keys($this->Media_model->mime_types);

		$html = "<p>".$this->cms_labels['max_upload_size'].' : '.$this->Media_model->max_upload_display."</p>\r";
		$this->data['sidebar'] = $html;

	}

	function _process_upload()
	{
		$this->Media_model->calculate_max_size();
		$config['max_size'] = $this->Media_model->max_upload_bytes;

		$exts = array_keys($this->Media_model->mime_types);
		$config['allowed_types'] = implode('|',$exts);

		// process form if submitted
		if($this->input->post('submit') != '') {

			$new_path = $this->input->post('new_path');
			$upload_path = $this->Media_model->dir_base.$this->input->post('dest_path');
			if($new_path != '')
			{
				$new_path = $this->Media_model->dir_base.'/'.$new_path;
				$new_path = str_replace('//','/',$upload_path);
				
				if(!file_exists($new_path))
				{
					if(mkdir($new_path))
					{
						$upload_path = $new_path;
					}
				}
				else
				{
					$upload_path = $new_path;
				}
			}

			$config['upload_path'] = $upload_path;
			$this->load->library('upload', $config);
			
			$success = $this->upload->do_upload();
			
			if ($success)
			{
				$this->Media_model->messages[] = $this->cms_labels['media_upload_success'];
				$html = '';
				// get upload data
				$upload_data = $this->upload->data();
				
				$this->Media_model->file_current = $upload_data['full_path'];
				$this->Media_model->file_uri = str_replace($this->Media_model->dir_base,'',$upload_data['full_path']);
				
				$this->Media_model->update_media_from_file();

				echo "<pre>";
				print_r($this->Media_model->metadata);
				echo "</pre>\r";

				
				$edit_uri = "cms/media/edit/id/".$this->Media_model->media_id;
				$edit_text = sprintf($this->cms_labels['edit_this'],$upload_data['file_name']);

				$html .= "<p>";
				$html .= anchor($edit_uri,$edit_text);
				$html .= "</p>\r";
				
				if($upload_data['is_image'] == 1)
				{
					$html .= "<p>";
					$html .= view_image($this->Media_model->file_current,$this->Site_model->img_size_md);
					$html .= "</p>\r";
				}

				$html .= "<p><strong>";
				$html .= $this->cms_labels['file_name'];
				$html .= "</strong>: ".$upload_data['file_name']."</p>\r";
	
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
						$type = $this->Media_model->mime_types[$ext];
					}
					
					$html .= "<p><strong>".$this->cms_labels['media_type']."</strong>: ".$type."</p>\r";
				}
				
				if(array_key_exists('image_width',$upload_data))
				{
					$html .= "<p><strong>".$this->cms_labels['media_dim_w']."</strong>: ".$upload_data['image_width']."</p>";
				}
				if(array_key_exists('image_height',$upload_data))
				{
					$html .= "<p><strong>".$this->cms_labels['media_dim_h']."</strong>: ".$upload_data['image_height']."</p>";
				}

				$this->Media_model->temp_value = $html;

			}	
			else
			{
				$this->Media_model->temp_value = '';
				$this->Media_model->messages[] = $this->upload->display_errors('<p>', '</p>');
			}
				
		} // end form processing
	}

}
?>
