<?php

class Media extends MY_Controller {
		
	function Media()
	{
		parent::MY_Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Media_model');
		$this->load->model('Content_model');
		$this->_load_cms_labels();
		$this->_load_site_metadata();

		$this->Media_model->temp_lang = $this->Site_model->site_default_lang;
	}
	
	function manage()
	{	
		$this->Media_model->lang_load();

		// get filter for list
		$params = $this->uri->uri_to_assoc(3);

		$limit = $this->input->post('limit');
		if(array_key_exists('limit',$params))
		{
			$limit = $params['limit'];
		}
		if(!$limit || $limit == '')
		{
			$limit = 10;
		}

		$this->Media_model->limit = $limit;

		$media_type = $this->input->post('media_type');
		if(array_key_exists('type',$params))
		{
			$media_type = $params['type'];
		}
		if ($media_type !== FALSE && $media_type != '')
		{
			$this->Media_model->temp_value = $media_type;
		}

		$this->Media_model->page = 1;
		if(array_key_exists('page',$params))
		{
			$this->Media_model->page = $params['page'];
		}
		
		$total = $this->Media_model->count_media_all();
		
		$this->Media_model->list_media();
		
		$this->lang->load('mime', $this->Media_model->lang_CI_Name);
		$mime_cat_labels = $this->lang->line('mime_cat');
		$mime_labels = $this->lang->line('mime');

		$links[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
		$links[] = anchor("cms/media/upload",$this->cms_labels['media_upload']);

		$html = ul($links);

		$this->data['sidebar'] = $html;

		$end = $this->Media_model->page * $limit;
		$start = $end - $limit + 1;
		
		if($end > $total || $end == 0)
		{
			$end = $total;
		}
		if($total > 0)
		{
			$results = sprintf($this->cms_labels['showing_results'],$start,$end,$total);
		}
		else
		{
			$results = $this->cms_labels['no_results'];
		}

		$action = 'cms/media/manage';
		$attributes = array(
		'id' => 'process'
		);
		$html = form_open($action, $attributes);
		
		$form_options[''] = 'any';
		foreach($this->Media_model->mime_categories as $cat)
		{
			$form_options[$cat] = $mime_cat_labels[$cat];
		}	

		$html .= "<div class=\"form-filter\">\r";
		$html .=  form_label($this->cms_labels['media_type'], 'media_type');
		$html .=  form_dropdown('media_type', $form_options, $media_type);

		$form_options = array(10 => 10,20 => 20,30=>30,40=>40,50=>50);
		$html .=  form_label($this->cms_labels['results_per_page'], 'limit');
		$html .=  form_dropdown('limit', $form_options, $limit);
		$html .= form_submit('filter', $this->cms_labels['results_filter']);
		$html .= "</div>\r";


		$html .= "<p class=\"align-center\">\r";
		$html .= "{$results}<br />\r";
		$this->load->library('pagination');
		$base_url = '/cms/media';
		$base_url .= '/limit/'.$limit;
		if ($media_type !== FALSE && $media_type != '')
		{
			$base_url .= '/type/'.$media_type;
		}
		$base_url .= '/page/';

		$pconfig['base_url'] = $base_url;
		$pconfig['total_rows'] = $total;
		$pconfig['per_page'] = $limit;
		$pconfig['cur_page'] = $this->Media_model->page;
		$this->pagination->initialize($pconfig);

		$html .= $this->pagination->create_links_proper();
		$html .= "</p>\r";

		$html .= "<table class=\"borders full\">\r";
		$html .= "\t<thead>\r";
		$html .= "\t\t<tr>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"files\">{$this->cms_labels['file']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"attached\">{$this->cms_labels['media_attached_to']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"published\">{$this->cms_labels['published']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"actions\">{$this->cms_labels['select']}\r";
		$html .= "<ul class=\"option-list\">\r";
		//$html .= "<li><a href=\"#\" id=\"select-toggle\">{$this->cms_labels['toggle']}</a></li>\r";
		$html .= "<li><a href=\"#\" id=\"select-all\">{$this->cms_labels['all']}</a></li>\r";
		$html .= "<li class=\"hide\"><a href=\"#\" id=\"select-none\">{$this->cms_labels['none']}</a></li>\r";
		$html .= "</ul>\r";
		$html .= "</th>\r";
		$html .= "\t\t</tr>\r";
		$html .= "\t</thead>\r";
		$html .= "\t<tbody>\r";

		foreach($this->Media_model->media_list as $media)
		{

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

			$html .= "\t\t\t<th id=\"file-{$mid}\" headers=\"files\">";
			if($mime_cat == 'image' && $media['external'] == 0)
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
			$html .= "</span>";
			$html .= "<br />\r".$mime_labels[$ext];

			$html .= "</th>\r";

			$html .= "\t\t\t<td headers=\"file-{$mid} attached\">";

			if(count($media['attached_to']) > 0)
			{
				$this->Content_model->temp_lang = $this->Media_model->temp_lang;
				$this->Content_model->link_mode = 1;
				$attachment_links = array();
				foreach($media['attached_to'] as $page)
				{
					$this->Content_model->temp_id = $page['content_id'];
					$this->Content_model->load_title();
					$uri = $this->Content_model->generate_uri();
					$attachment_links[] = anchor($uri,$this->Content_model->title, array('title' => sprintf($this->cms_labels['edit_this'],$this->Content_model->title)));
				}
				$html .= ul($attachment_links);
			}
			else
			{
				$html .= $this->cms_labels['none'];
			}

			$html .= "</td>\r";

			$options = array($this->cms_labels['no'],$this->cms_labels['yes']);
			$html .= "\t\t\t<td headers=\"file-{$mid} published\">";
			$html .= $options[$media['publish']];
			$html .= "</td>\r";

			$html .= "\t\t\t<td headers=\"file-{$mid} actions\">";

			$options = array(
			'name'        => "select[]",
			'id'          => "select[]",
			'value'       => $media['media_id'],
			);
			
			$html .= form_checkbox($options);
			$html .= "</td>\r";
			$html .= "\t\t</tr>\r";
		}
		$html .= "\t</tbody>\r";
		$html .= "</table>\r";

		$form_options = array(''=>'','delete' => 'delete','publish'=>'publish','unpublish'=>'unpublish');
		$html .= "<p class=\"align-right\">";
		$html .=  form_label($this->cms_labels['with_selected'].' : ', 'action', array('class' => 'inline'));
		$html .=  form_dropdown('acion', $form_options);
		$html .= "&nbsp; ";

		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\n";
		$html .= form_close();

		$this->data['form'] = $html;
		
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['media'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media'];

		$this->data['custom_js'] = "<script type=\"text/javascript\" src=\"/assets/js/jquery_media_filetree.js\"></script>\r";

		$this->load->view('cms/media', $this->data);
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

		$this->data['sidebar'] = $this->_media_meta_menu();

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
		'class'	=> 'small_input',
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
			case 'textarea':
				$form_options['cols'] = 38;
				$html .=  form_textarea($form_options);
				break;
			case 'richtext':
				$form_options['cols'] = 38;
				$html .=  form_textarea($form_options);
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

/*				$this->Media_model->temp_id = $this->input->post('type_id');
				$this->Media_model->scope = 'media';
				$this->Media_model->load_type();
*/	
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

	function build_filetree($files=array())
	{
		$filetree = "<ul>\r";
		$base_uri = 'cms/media/edit/id/';
		foreach($files as $path_rel => $file)
		{

			$paths = explode('/',$path_rel);
			$filename = array_pop($paths); 

			if($file['type'] == 'file')
			{

				$ext = strtolower(preg_replace('/^.*\./', '', $filename));
				$classes = array();
				
				if(array_key_exists($ext,$this->Media_model->mime_types))
				{
					$type = explode('/',$this->Media_model->mime_types[$ext]);
					if($type[0] != 'application')
					{
						$classes[] = 'icon_'.$type[0];
					}
				}
				if(count($classes) <= 0)
				{
					$classes[] = 'file ext_'.$ext;
				}
/*
				$uri = $base_uri.$file['media_id'];
				if($this->Media_model->temp_lang != '')
				{
					$uri .= '/lang/'.$this->Media_model->temp_lang;
				}
*/


				$info_uri = 'cms/media/info/'.$file['media_id'];

				$class = implode(' ',$classes);			
				
/*				if($file['title'] != '')
				{
					//$li = anchor($uri, $file['title'], array('rel' => $path_rel));
					$li = "<a href=\"#\" rel=\"" . $info_uri . "/\">" . htmlentities($file['title']) . "</a>";
				}
				else
				{
*/					//$li = anchor($uri, $filename, array('rel' => $path_rel));
					$li = "<a href=\"#\" rel=\"" . $info_uri . "/\">" . htmlentities($filename) . "</a>";
				//}

			}
			else
			{
				$class = 'directory collapsed';			
				$li = "<a href=\"#\">" . htmlentities($filename) . "</a>";
				$li .= $this->build_filetree($file['files']);
			}

				$filetree .= "<li class=\"{$class}\">{$li}</li>\r";

		}
		$filetree .= "</ul>\r";

		return $filetree;
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

}
?>
