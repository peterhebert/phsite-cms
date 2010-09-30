<?php
class Setup extends MY_Controller
{

	function Setup()
	{
		parent::MY_Controller();
		$this->load->helper('form');
		$this->load->model('Content_model');
		$this->load->model('File_model');
		$this->load->library('form_validation');		
	
		$this->_authenticate();
		$this->menu_order = array('setup','content','media');
	}
	
	function index()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['setup'];

		if($this->session->userdata('default_lang'))
		{
			$this->data['site_meta_lang'] = $this->session->userdata('default_lang');
		}
		else
		{
			$this->data['site_meta_lang'] = $this->Site_model->site_default_lang;
		}
		$this->Site_model->temp_value = 'cms/setup/site_metadata';
		$this->data['site_metadata'] =  $this->Site_model->lang_meta_menu();

		$this->load->view('cms/setup',$this->data);
	}


	function site_preferences()
    {
		$this->load->helper('image');
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['site'];
		$breadcrumb[] = $this->cms_labels['site_prefs'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['site_prefs'];
		$title_tag[] = $this->cms_labels['site'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['site_prefs'];

		$this->load->model('User_model');
		$this->_process_site_preferences();		

		$this->Site_model->load_site();
		$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		$this->data['form'] = $this->_form_site_preferences();

		$msg = $this->Site_model->_output_messages();

		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->_load_cms_menus();
		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function _form_site_preferences()
    {
		$action = 'cms/setup/site_preferences';
		$hidden = array('site_id' => $this->Site_model->site_id);
		$attributes = array('name' => 'site_prefs','id' => 'site_prefs');

		$html = form_open($action, $attributes);
		$html .= "<div class=\"align-labels label-wide\">";

		// get list of top level pages for home page
		$form_options = array();
		$form_options['0'] = $this->cms_labels['auto_generate'];

		$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		
		$this->Content_model->list_content(0, TRUE);
		
		$homePages = $this->Content_model->content_list;
		foreach($homePages as $row) {
			$form_options["{$row['content_id']}"] = $row['title'];
		}
		
		$html .= "<p>";
		$html .= form_label($this->cms_labels['home_page'], 'home_content_id');
		$html .= form_dropdown('home_content_id', $form_options, $this->Site_model->home_content_id);		
		$html .= "</p>\r";

		// list of content types for auto-generating home page 'recent content'
		// load list of content types
		$form_options = array();
		$form_options['0'] = $this->cms_labels['all_content_types'];

		$this->Site_model->scope = 'content';
		$this->Site_model->list_types();

		foreach($this->Site_model->type_list as $row)
		{
			$form_options["{$row['type_id']}"] = $row['label'];
		}

		$html .= "<p>";
		$html .= form_label($this->cms_labels['home_content_type'], 'home_content_type');
		$html .= form_dropdown('home_content_type', $form_options, $this->Site_model->home_content_type);
		$html .= "</p>\r";
		
		$html .= "<p class=\"indent-wide\">";
		$html .= $this->cms_labels['home_content_type_explain'];
		$html .= "</p>\r";

		// now list themes

		$this->Site_model->list_themes();
		
		$html .= "<div class=\"row\">";
		$html .= form_label($this->cms_labels['theme'], 'theme');
		
		$html .= "<span class=\"float w7\">";
		foreach($this->Site_model->site_themes as $uri => $theme_info)
		{
			$checked = FALSE;
			if($uri == $this->Site_model->theme)
			{
				$checked = TRUE;
			}
					
			$form_options = array(
			'name'        => 'theme',
			'value'       => $uri,
			'checked'     => $checked,
			);
		
			$html .= "<label class=\"column\" style=\"width: ".$this->Site_model->img_size_sm."px; text-align: left\">";
			$path_thumb = ROOT_REL.'/system/application/views/themes/'.$uri.'/_theme_info/'.$uri.'.jpg';
			
			if(file_exists($path_thumb))
			{
				$alt = sprintf($this->cms_labels['theme_screenshot'],$theme_info['title']);
				$html .= "<span class=\"image\">";
				$html .= view_image($path_thumb,$this->Site_model->img_size_sm,array('alt' => $alt));
				$html .= "</span><br />\r";
			}
			$html .= form_radio($form_options);
			$html .= ' <strong>'.anchor($theme_info['website'], $theme_info['title'], array('rel' => 'external'))."</strong><br />\r";
			$html .= $this->cms_labels['author'].' : '.mailto($theme_info['email'], $theme_info['author'], array('title' => $this->cms_labels['email_author']))."<br />\r";
			$html .= $theme_info['description']."</label> ";
			
		}
		$html .= "</span>\r";
		$html .= "</div>\r";

		$form_options = timezone_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['timezone'], 'site_timezone');
		$html .= form_dropdown('site_timezone', $form_options, $this->Site_model->site_timezone);		
		$html .= "</p>\r";

		$form_options = $this->Site_model->langs_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['default_lang'], 'site_default_lang');
		$html .= form_dropdown('site_default_lang', $form_options, $this->Site_model->site_default_lang);		
		$html .= "</p>\r";

		$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		$form_options = $this->Content_model->types_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['default_content_type'], 'default_content_type');
		$html .= form_dropdown('default_content_type', $form_options, $this->Site_model->default_content_type);		
		$html .= "</p>\r";

		$form_options = $this->User_model->get_list_dropdown(0,1);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['webmaster'], 'site_admin_id');
		$html .= form_dropdown('site_admin_id', $form_options, $this->Site_model->site_admin_id);		
		$html .= "</p>\r";

		$html .= "<p>";
		$html .= form_label($this->cms_labels['editor'], 'site_editor_id');
		$html .= form_dropdown('site_editor_id', $form_options, $this->Site_model->site_editor_id);		
		$html .= "</p>\r";

		$form_options = array(
		'class'	=> 'med_input',
		'name'        => 'site_date_created',
		'id'          => 'site_date_created',
		'value'       => $this->Site_model->site_date_created
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['site_date_created'], 'site_date_created');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_posts',
		'id'          => 'limit_posts',
		'value'       => $this->Site_model->limit_posts
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['limit_posts'], 'limit_posts');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'limit_thumbs',
		'id'          => 'limit_thumbs',
		'value'       => $this->Site_model->limit_thumbs
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['limit_thumbs'], 'limit_thumbs');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_th',
		'id'          => 'img_size_th',
		'value'       => $this->Site_model->img_size_th
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_th'], 'img_size_th');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_sm',
		'id'          => 'img_size_sm',
		'value'       => $this->Site_model->img_size_sm
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_sm'], 'img_size_sm');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_md',
		'id'          => 'img_size_md',
		'value'       => $this->Site_model->img_size_md
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_md'], 'img_size_md');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'tiny_input align-right',
		'name'        => 'img_size_lg',
		'id'          => 'img_size_lg',
		'value'       => $this->Site_model->img_size_lg
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['img_size_lg'], 'img_size_lg');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$html .= "</div>\r";

		$html .= "<p class=\"indent-wide\">";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";

		$html .= form_close();
		return $html;
	}

	function _process_site_preferences()
    {
		if( $this->input->post('submit') != '' )
		{
			// set validation rules
			$this->form_validation->set_rules('home_content_id', $this->cms_labels['home_page'], 'required|is_natural');
			$this->form_validation->set_rules('home_content_type', $this->cms_labels['home_content_type'], 'required|is_natural');
			$this->form_validation->set_rules('site_admin_id', $this->cms_labels['webmaster'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_editor_id', $this->cms_labels['editor'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('site_default_lang', $this->cms_labels['default_lang'], 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('default_content_type', $this->cms_labels['default_content_type'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('theme', $this->cms_labels['theme'], 'required|alpha_dash');
			$this->form_validation->set_rules('site_timezone', $this->cms_labels['timezone'], 'required');
			$this->form_validation->set_rules('limit_posts', $this->cms_labels['limit_posts'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('limit_thumbs', $this->cms_labels['limit_thumbs'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_th', $this->cms_labels['img_size_th'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_sm', $this->cms_labels['img_size_sm'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_md', $this->cms_labels['img_size_md'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('img_size_lg', $this->cms_labels['img_size_lg'], 'required|is_natural_no_zero');

			// validate
			if ($this->form_validation->run() == FALSE)
			{
				$this->Site_model->messages[] = validation_errors();
			} else {

				if($this->input->post('site_date_created') != '' && $this->input->post('site_date_created') != '0000-00-00 00:00:00')
				{
					$this->Site_model->site_date_created = $this->input->post('site_date_created');
				}
				else
				{
					$this->Site_model->site_date_created = date('Y-m-d H:i:s');
				}

				if($this->input->post('site_id') != '')
				{
					$this->Site_model->site_id = $this->input->post('site_id');
				}

				$this->Site_model->home_content_id = $this->input->post('home_content_id');
				$this->Site_model->home_content_type = $this->input->post('home_content_type');
				$this->Site_model->site_timezone = $this->input->post('site_timezone');
				$this->Site_model->theme = $this->input->post('theme');
				$this->Site_model->site_default_lang = $this->input->post('site_default_lang');
				$this->Site_model->default_content_type = $this->input->post('default_content_type');
				$this->Site_model->site_admin_id = $this->input->post('site_admin_id');
				$this->Site_model->site_editor_id = $this->input->post('site_editor_id');
				$this->Site_model->limit_posts = $this->input->post('limit_posts');
				$this->Site_model->limit_thumbs = $this->input->post('limit_thumbs');
				$this->Site_model->img_size_th = $this->input->post('img_size_th');
				$this->Site_model->img_size_sm = $this->input->post('img_size_sm');
				$this->Site_model->img_size_md = $this->input->post('img_size_md');
				$this->Site_model->img_size_lg = $this->input->post('img_size_lg');

				$success = $this->Site_model->update_site();
				if($success)
				{
					$this->Site_model->messages[] = $this->cms_labels['site_prefs_update_success'];
				}

			} // end validation
		} // end form processing
	}

	function site_metadata()
    {
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['site'];
		$breadcrumb[] = $this->cms_labels['site_metadata'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['site_metadata'];
		$title_tag[] = $this->cms_labels['site'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['site_metadata'];

		$this->_process_site_metadata();		
		$this->data['form'] = $this->_form_site_metadata();
		
		$msg = $this->Site_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->data['sidebar'] = $this->_site_meta_menu();
		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);
	}
	
	function _form_site_metadata()
	{
		// first determine lang
		if($this->uri->total_segments() == 5)
		{
			$this->Site_model->temp_lang = $this->uri->segment(5);
		}

		$this->Site_model->lang_load();
		$this->Site_model->load_site_metadata();

		$this->Site_model->clear_title();
		$this->Site_model->load_title();

		$action = $this->uri->uri_string();
		$hidden = array('site_id' => $this->Site_model->site_id, 'title_id' => $this->Site_model->title_id);
		$attributes = array('name' => 'site_metadata','id' => 'site_metadata');

		$html = form_open($action, $attributes, $hidden);

		$html .= "<div class=\"align-labels label-normal\">\r";

		$form_options = $this->Site_model->langs_dropdown();
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['language'], 'site_meta_lang');
		$html .= form_dropdown('site_meta_lang', $form_options, $this->Site_model->temp_lang);		
		$html .= "</p>\r";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'title',
		'id'          => 'title',
		'value'       => $this->Site_model->title
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['title'], 'title');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'class'        => 'text',
		'name'        => 'subtitle',
		'id'          => 'subtitle',
		'value'       => $this->Site_model->subtitle
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['subtitle'], 'subtitle');
		$html .= form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->Site_model->keywords
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['keywords'],'keywords');
		$html .=  form_textarea($form_options);
		$html .= "</p>\r";

/*		$fd_meta = $this->Site_model->metadata;

		foreach($fd_meta as $index => $fd)
		{
			// hidden fields
			$form_options = array(
			'field_id[]' =>  $index,
			'field_ref_name[]' =>  $fd['field_ref_name'],
			'meta_id[]' => $fd['meta_id'],
			'required[]' =>  $fd['required'],
			'field_needs_lang[]' => $fd['field_needs_lang']
			);
			$html .= form_hidden($form_options);

			$this->Site_model->label_scope = 'field';
			$this->Site_model->temp_id = $index;

			$tmp_label = $fd['field_ref_name'];
			if($this->Site_model->load_label())
			{
				$tmp_label = $this->Site_model->label;
			}
			$html .= "<p>\r";
			$html .= form_label($tmp_label, $fd['field_ref_name']);
			$form_options = array(
			'id' => $fd['field_ref_name'],
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $fd['field_input_type']
			);

			switch($fd['field_input_type']) {

			case 'directory':
				$this->load->model('File_model');
				$this->File_model->list_dirs();
				$media_root = $this->File_model->dir_base;
				$dir_list_temp = $this->File_model->dir_list;
				$dir_vals[''] = 'media root';
				
				foreach($dir_list_temp as $the_dir) {
					$temp_dir_val = str_replace($media_root,'',$the_dir);
					$dir_vals[$temp_dir_val] = $temp_dir_val;
				}
				
				$html .= form_dropdown($form_options['name'], $dir_vals, $fd['value']);
				break;
			case 'file':
				$this->load->model('File_model');
				$this->File_model->list_dirs();
				$media_root = $this->File_model->dir_base;
				$dir_files = array('' => 'no file');
				foreach($this->File_model->dir_list as $index => $dir)
				{
					$this->File_model->dir_current = $dir;
					$dest_dir = str_replace($media_root,'',$dir);

					$this->File_model->list_files();
					foreach($this->File_model->file_list as $file_path) {
						$file_rel = str_replace($media_root,'',$file_path);
						$pinfo = pathinfo($file_path);
						$dir_files[$file_rel] = $pinfo['basename'];
					}
					
				}

				$html .= form_dropdown($form_options['name'], $dir_files, $fd['value']);
				break;
			case 'textarea' || 'richtext':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
				break;
			default:
				$html .=  form_input($form_options);
			}

			$html .= "</p>\r";
		}
*/

		// fields
		foreach($this->Site_model->metadata as $index => $fd)
		{
			$this->Site_model->reset_label();
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
				$tmp_list_id = $fd['list_id'];
			}
			else
			{
				$this->Site_model->temp_id = $index;
				$this->Site_model->load_field();

				$tmp_ref_name = $this->Site_model->field_ref_name;
				$tmp_needs_lang = $this->Site_model->field_needs_lang;
				$tmp_input_type = $this->Site_model->field_input_type;
				$tmp_validation = $this->Site_model->field_validation;
				$tmp_list_id = $this->Site_model->list_id;
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
			$html .= form_hidden($form_options);

			$this->Site_model->reset_label();
			$this->Site_model->label_scope = 'field';
			$this->Site_model->temp_id = $index;
			$this->Site_model->temp_lang = $this->Site_model->temp_lang;

			if($this->Site_model->load_label())
			{
				$tmp_label = $this->Site_model->label;
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

			case 'directory':
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
			case 'richtext':
				$form_options['cols'] = 38;
				$html .= ph_form_textarea($form_options);
				break;

			case 'list':
				// load list values
				$this->Site_model->temp_id = $fd['list_id'];
				$this->Site_model->load_value_list();
				$listvals = prepare_list_values($this->Site_model->value_list);

				$html .= ph_form_list($form_options['name'], $listvals, $fd['value'], $fd['field_allow_multiple']);

				break;
			default:
				$html .=  form_input($form_options);
			}
			$html .= "</p>\r";
		}

		$html .= "</div>\r";
		
		$html .= "<p class=\"indent-normal\">";
		$html .= form_submit('submit', 'Save');
		$html .= "</p>\n";

		$html .= form_close();
		return $html;
	}
	
	function _site_meta_menu()
    {
		$menu = '';
		
		if($this->Site_model->site_id != '')
		{
		
			// list active languages
			$lang_links = array();
			$this->Site_model->list_langs();
			foreach($this->Site_model->lang_list as $llang)
			{

				$temp_link = 'cms/setup/site_metadata/lang/'.$llang['ISO_639_3'];
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
	
			$del_base = 'cms/setup/site_meta_delete/';
			if($this->Site_model->temp_lang != '')
			{
				$del_lang = $del_base.'/lang/'.$this->Site_model->temp_lang;
				$this->Site_model->lang_load();
				$del_links[] = anchor($del_lang,$this->Site_model->lang_Ref_Name, array('title' => $this->cms_labels['delete_this_lang']));
			}

			$del_links[] = anchor($del_base,$this->cms_labels['all_langs'], array('title' => $this->cms_labels['delete_site_meta_all_langs']));

			$menu .= "<li>".$this->cms_labels['delete'];
			$menu .= ul($del_links);
			$menu .= "</li>\r";

			$menu .=  "</ul>\r";

		}

		return $menu;
	}

	function _process_site_metadata()
    {

		// process form
		if( $this->input->post('submit') != '' )
		{

			// set validation rules
			$this->form_validation->set_rules('site_meta_lang', $this->cms_labels['language'], 'required|alpha|exact_length[3]');
			$this->form_validation->set_rules('title', $this->cms_labels['title'], 'required');

			// load custom field list and add validation rules

			$this->Site_model->scope = 'site';
			$this->Site_model->temp_id = $this->input->post('site_id');
			$this->Site_model->list_fields_related();
			
			$meta_fields = $this->Site_model->field_list;
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
	
				$this->Site_model->label_scope = 'field';
				$this->Site_model->temp_id = $fd['field_id'];
	
				if($this->Site_model->load_label())
				{
					$lbl_name = $this->Site_model->label;
				}

				$concat_valid = implode('|', $tmp_valid);

				if($concat_valid != '')
				{
					$this->form_validation->set_rules('value_'.$fd['field_id'], $lbl_name, $concat_valid);
				}

			}

			// run validation and load field values
			$form_is_valid = $this->form_validation->run();
			
			if ($form_is_valid)
			{
				$this->Site_model->site_id = $this->input->post('site_id');
				$this->Site_model->title_rel_id = $this->input->post('site_id');
				$this->Site_model->title_id = $this->input->post('title_id');
				$this->Site_model->temp_lang = $this->input->post('site_meta_lang');
				$this->Site_model->title = $this->input->post('title');
				$this->Site_model->subtitle = $this->input->post('subtitle');
				$this->Site_model->keywords = csv_normalize($this->input->post('keywords'));
	
				$tmp_field_id = $this->input->post('field_id');
				$tmp_field_name = $this->input->post('field_name');
				$tmp_meta_id = $this->input->post('meta_id');
				$tmp_required = $this->input->post('required');
				$tmp_field_needs_lang = $this->input->post('field_needs_lang');
			
				$this->Site_model->scope = 'site';
				$form_meta = array();
				foreach($tmp_field_id as $index => $fd_id)
				{
					$val_lbl = 'value_'.$fd_id;
					$value = html_entity_decode($this->input->post($val_lbl),ENT_COMPAT,"UTF-8");
					
					$this->Site_model->temp_id = $fd_id;
					$this->Site_model->load_field();
					
					$tmp_meta = array(
					'meta_id' => $tmp_meta_id[$index],
					'scope' => $this->Site_model->scope,
					'rel_id' => $this->Site_model->site_id,
					'field_id' => $this->Site_model->field_id,
					'field_ref_name' => $this->Site_model->field_ref_name,
					'value' => $value,
					'required' => $tmp_required[$index],
					'field_input_type' => $this->Site_model->field_input_type,
					'field_validation' => $this->Site_model->field_validation,
					'field_needs_lang' => $this->Site_model->field_needs_lang,
					);
	
					$form_meta["$fd_id"] = $tmp_meta;
	
				}
				
				$this->Site_model->metadata = $form_meta;

				// update site metadata 
				$this->Site_model->update_title();
				$this->Site_model->update_metadata();

				if(count($this->Site_model->messages) == 0)
				{
					$this->Site_model->messages[] = $this->cms_labels['site_meta_update_success'];
				}


			} else {
				$this->Site_model->messages[] = validation_errors();
			} // end validation

		} // end form processing

	} // end _process_meta()


	function fields()
	{

		$this->Content_model->list_fields();
		$temp_list = $this->Content_model->field_list;
		
		$fields = array();
		
		foreach($temp_list as $field)
		{
			$this->Content_model->reset_label();
			$this->Content_model->label_scope = 'field';

			$this->Content_model->temp_id = $field['field_id'];
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
			$lbl = $this->Content_model->load_label();

			$uri = "cms/setup/field_edit/id/".$field['field_id'];
		
			if($lbl) {
				$uri .= "/lang/".$this->Content_model->temp_lang;
				$text = $this->Content_model->label;
			} else {
				$text = $field['field_ref_name'];
			}
			$attr = array('title' => sprintf($this->cms_labels['edit_field'],$text));
			
			$fields[] = anchor($uri,$text,$attr);
		
		}

		$html = '<p>'. anchor("cms/setup/field_edit/",$this->cms_labels['new_field'])."</p>\r";
		$html .= ul($fields);		$this->data['form'] = $html;

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = $this->cms_labels['fields'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['fields'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag).' ';
		$this->data['title_h1'] = $this->cms_labels['fields'];
		
		$this->_load_cms_menus();

		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function field_edit()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = anchor("cms/setup/fields",$this->cms_labels['fields']);
		$breadcrumb[] = $this->cms_labels['field_editor'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag = array(
		$this->cms_labels['field_editor'],
		$this->cms_labels['fields'],
		$this->cms_labels['data_structure'],
		$this->cms_labels['setup']
		);
		$this->data['title_tag'] = implode(' | ',$title_tag).' ';
		$this->data['title_h1'] = $this->cms_labels['field_editor'];

		$this->_process_field_edit();
		$this->_load_field_data_types();

		$params = $this->uri->uri_to_assoc(4);

		if($this->Content_model->field_id != '')
		{
			$this->Content_model->temp_id = $this->Content_model->field_id;
		}
		else
		{
			if(array_key_exists('id',$params))
			{
				$this->Content_model->temp_id = $params['id'];
			}
		}

		$loaded = $this->Content_model->load_field();

		if(array_key_exists('lang',$params))
		{
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}

		$this->data['form'] = $this->_form_field_edit();

		$sidebar = '<h2>'.$this->cms_labels['edit_by_lang']."</h2>\r";
		$uri = 'cms/setup/field_edit';
		if($this->Content_model->field_id != '')
		{
			$uri .= '/id/'.$this->Content_model->field_id;
		}
		$this->Content_model->temp_value = $uri;
		
		$sidebar .= $this->Content_model->lang_meta_menu();
		$this->data['sidebar'] = $sidebar;

		$this->data['custom_js'] = "<script type=\"text/javascript\" src=\"".BASE_PATH."/assets/js/admin_ui/field_edit.js\"></script>\r";

		
		$msg = $this->Content_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);
	}

	function _load_field_data_types()
	{	
		$user_lang = $this->session->userdata('default_lang');

		if($user_lang)
		{
			$this->Site_model->temp_lang = $user_lang;
		}
		else
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}

		$this->Site_model->lang_load();
		
		// load validation rules and input types from language files
		$load_lang = $this->Site_model->lang_CI_Name;
		if($load_lang == '')
		{
			$load_lang = 'english';
		}

		$this->lang->load('form_input', $load_lang);
		$this->Content_model->data_types = $this->lang->line('data_types');
		$this->Content_model->input_types = $this->lang->line('input_types');
		$this->Content_model->validation_rules = array_keys($this->lang->line('data_types'));
    }

	function _form_field_edit()
	{
		$action = "cms/setup/field_edit/id/".$this->Content_model->field_id;
		$action .= "/lang/".$this->Content_model->temp_lang;
		
		$attributes = array('name' => "field_editor", 'id' => "field_editor");
		$hidden = array(
		'field_id'  => $this->Content_model->field_id,
		'label_id'  => $this->Content_model->label_id
		);

		$html = form_open($action,$attributes,$hidden)."\r";

		$html .= "<div class=\"align-labels label-wide\">\r";

		// main data
		$html .= form_fieldset($this->cms_labels['field_info']);
		$html .= "\r";
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['ref_name'], 'field_ref_name');
		$html .= form_input('field_ref_name',$this->Content_model->field_ref_name); 
		$html .= "</p>\r";
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['input_type'], 'field_input_type');
		$html .= form_dropdown('field_input_type', $this->Content_model->input_types, $this->Content_model->field_input_type); 
		$html .= "</p>\r";

		// get all possible value lists
		$this->Content_model->reset_label();
		$form_options = array(0 => $this->cms_labels['none']);
		$val_list = $this->Content_model->value_lists_all();
		foreach($val_list as $list)
		{
			$form_options[$list['list_id']] = $list['label'];
		}

		if($this->Content_model->list_id == '')
		{
			$list_id = 0;
		}
		else
		{
			$list_id = $this->Content_model->list_id;
		}

		$html .= "<p>";
		$html .= form_label($this->cms_labels['list'], 'list_id');
		$html .= form_dropdown('list_id', $form_options, $this->Content_model->list_id); 
		$html .= "</p>\r";

		$form_options = array(0 => $this->cms_labels['no'], 1 => $this->cms_labels['yes']);
		
		if($this->Content_model->field_allow_multiple == '')
		{
			$allow_multiple = 0;
		}
		else
		{
			$allow_multiple = $this->Content_model->field_allow_multiple;
		}

		$html .= "<p>";
		$html .= form_label($this->cms_labels['select_allow_multiple'], 'field_allow_multiple');
		$html .= ph_form_list('field_allow_multiple', $form_options, $allow_multiple);
		$html .= "</p>\r";

		if($this->Content_model->field_needs_lang == '')
		{
			$needs_lang = 1;
		}
		else
		{
			$needs_lang = $this->Content_model->field_needs_lang;
		}
		$html .= "<p>";
		$html .= form_label($this->cms_labels['field_needs_lang'], 'field_needs_lang');
		$html .= ph_form_list('field_needs_lang', $form_options, $needs_lang);
		$html .= "</p>\r";

		$html .= form_fieldset_close("\r");

		
		// metadata
		$html .= form_fieldset($this->cms_labels['field_metadata']);
		$html .= "\r";

		$form_options = $this->Content_model->langs_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['language'], 'field_lang');
		$html .= form_dropdown('field_lang', $form_options, $this->Content_model->temp_lang); 
		$html .= "</p>\r";

		$this->Content_model->reset_label();
		if($this->Content_model->field_id != '')
		{
			$this->Content_model->label_scope = 'field';
			$this->Content_model->temp_id = $this->Content_model->field_id;
			$this->Content_model->load_label();
		}
		$html .= form_hidden('label_id', $this->Content_model->label_id);

		$html .= "<p>";
		$html .= form_label($this->cms_labels['field_label'], 'field_label');
		$html .= form_input('field_label',$this->Content_model->label); 
		$html .= "</p>\r";
		$html .= form_fieldset_close("\r");

		// validation
		$html .= form_fieldset($this->cms_labels['validation']);
		$html .= "\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['data_type'], 'validation_string');
		$html .= form_dropdown('validation_string', $this->Content_model->data_types, $this->Content_model->validation_string); 
		$html .= "</p>\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['exact_length'], 'validation_exact');
		$html .= form_input('validation_exact',$this->Content_model->validation_exact); 
		$html .= "</p>\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['min_length'], 'validation_min');
		$html .= form_input('validation_min',$this->Content_model->validation_min); 
		$html .= "</p>\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['max_length'], 'validation_max');
		$html .= form_input('validation_max',$this->Content_model->validation_max); 
		$html .= "</p>\r";
		$html .= form_fieldset_close("\r");

		$html .= "</div>\r";

		$html .= "<p>\r";
		$submit_data = array(
		'name'  => 'submit',
		'id'  => 'submit'
		);
		$html .= form_submit($submit_data, $this->cms_labels['save']);
		$html .= "</p>\r";
		$html .= form_close("\r");

		return $html;
	}

	function _process_field_edit()
	{
		// do validation if form submitted
		if($this->input->post('submit') != '')
		{
			// set validation rules
			$this->form_validation->set_rules('field_ref_name', $this->cms_labels['ref_name'], 'required');
			$this->form_validation->set_rules('field_input_type', $this->cms_labels['input_type'], 'required|callback_valid_input');
			$this->form_validation->set_rules('validation_string', $this->cms_labels['data_type'], 'callback_valid_valid');
			$this->form_validation->set_rules('validation_exact', $this->cms_labels['exact_length'], 'is_natural_no_zero');
			$this->form_validation->set_rules('validation_min', $this->cms_labels['min_length'], 'is_natural_no_zero');
			$this->form_validation->set_rules('validation_max', $this->cms_labels['max_length'], 'is_natural_no_zero');

			$this->form_validation->set_rules('field_lang', $this->cms_labels['language'], 'required|is_lang');
			$this->form_validation->set_rules('field_label', $this->cms_labels['field_label'], 'required');

			$form_is_valid = $this->form_validation->run();
		
			// run validation rules
			if ($form_is_valid)
			{

				$this->Content_model->field_id = $this->input->post('field_id');
				$this->Content_model->field_ref_name = $this->input->post('field_ref_name');	
				$this->Content_model->field_input_type = $this->input->post('field_input_type');
				$this->Content_model->field_needs_lang = $this->input->post('field_needs_lang[]');
				$this->Content_model->field_allow_multiple = $this->input->post('field_allow_multiple[]');

				$this->Content_model->validation_string = $this->input->post('validation_string');	
				$this->Content_model->validation_exact = $this->input->post('validation_exact');	
				$this->Content_model->validation_min = $this->input->post('validation_min');	
				$this->Content_model->validation_max = $this->input->post('validation_max');	
				$this->Content_model->list_id = $this->input->post('list_id');	

				$this->Content_model->label_id = $this->input->post('label_id');	
				$this->Content_model->label = $this->input->post('field_label');
				$this->Content_model->label_scope = 'field';
				$this->Content_model->label_rel_id = $this->input->post('field_id');
				$this->Content_model->label_rel_name = $this->Content_model->field_ref_name;
				$this->Content_model->temp_lang = $this->input->post('field_lang');	

				$success_field = $this->Content_model->update_field();
				if($success_field)
				{
					$success_meta = $this->Content_model->update_label();
					if(!$success_meta)
					{
						$this->Content_model->messages[] = sprintf($this->cms_labels['update_failure'], $this->cms_labels['field_label']);
					}
				}
				else
				{
					$this->Content_model->messages[] = sprintf($this->cms_labels['update_failure'], $this->Content_model->field_ref_name);
				}

				if(count($this->Content_model->messages) == 0)
				{
					$this->Content_model->messages[] = sprintf($this->cms_labels['update_success'], $this->Content_model->field_ref_name);
				}

			}
			else
			{
				$this->Content_model->messages[] = validation_errors(); 
			}
		}

	}

	function site_meta_fields()
	{

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['site'];
		$breadcrumb[] = $this->cms_labels['site_meta_fields'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['site_meta_fields'];
		$title_tag[] = $this->cms_labels['site'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['site_meta_fields'];

		$this->_process_meta_fields();
		
		$this->data['form'] = $this->_form_meta_fields();

		$msg = $this->Site_model->_output_messages();
		
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		
		$this->load->view('cms/cms_form',$this->data);
	}

	function _form_meta_fields()
	{

		$action = "/cms/setup/site_meta_fields";
		$hidden = array('site_id' => $this->Site_model->site_id);
		$attributes = array('name' => 'site_meta_fields','id' => 'site_meta_fields');

		$html = form_open($action, $attributes, $hidden);

		// fields

		$this->Site_model->list_site_meta_fields();
		
		$fieldCount = count($this->Site_model->field_list);
		$fieldNo = 1;

		$html .= "<table id=\"properties\">\r";
		$html .= "<thead>\r";
		$html .= "<tr>\r";
		$html .= "<th id=\"fd_name\">".$this->cms_labels['field']."</th>\r";
		$html .= "<th id=\"fd_exists\">".$this->cms_labels['field_use_for_metadata']."</th>\r";
		$html .= "<th id=\"fd_req\">".$this->cms_labels['required']."</th>\r";
		$html .= "<th id=\"fd_order\">".$this->cms_labels['order']."</th>\r";
		$html .= "</tr>\r";
		$html .= "</thead>\r";
		
		$html .= "<tbody>\r";

		foreach($this->Site_model->field_list as $field)
		{
			$row_header = form_hidden('field_id[]', $field['field_id']);
			$row_header .= form_hidden('rf_id[]', $field['rf_id']);

			/* get label for field */
			$this->Site_model->reset_label();
			$this->Site_model->label_scope = 'field';
			$this->Site_model->temp_id = $field['field_id'];
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Site_model->load_label();

			if($this->Site_model->label_id != '') {
				$row_header .= $this->Site_model->label;
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
			
			$html .= "<tr>\r";
			$html .= "<th headers=\"fd_name\" id=\"row_{$fieldNo}\">".$row_header."</th>\r";
			$html .= "<td headers=\"fd_exists row_{$fieldNo}\">".$chk_exist."</td>\r";
			$html .= "<td headers=\"fd_req row_{$fieldNo}\">".$chk_req."</td>\r";
			$html .= "<td headers=\"fd_order row_{$fieldNo}\">".$input_order."</td>\r";
			$html .= "</tr>\r";

			$fieldNo += 1;

		}
		$html .= "</tbody>\r";
		$html .= "</table>\r";


		$html .= form_submit('submit', 'Save');
		$html .= form_close();
		return $html;
	}

	function _process_meta_fields()
	{

		if($this->input->post('submit') != '')
		{
			// get values from form post data
			$this->Site_model->site_id = $this->input->post('site_id');
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

			$this->Site_model->field_list = $field_updates;
			$this->Site_model->scope = 'site';
			$this->Site_model->update_fields_related();

		}
		
	}// end _process_meta_fields()

	function languages()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['languages'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['languages'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['languages'];

		$this->_process_languages();
		$this->data['form'] = $this->_form_languages();

		$msg = $this->Site_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->_load_cms_menus();

		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function _form_languages()
	{
		$this->Site_model->list_langs();
		$enabled = $this->Site_model->lang_list;

		$action = "cms/setup/languages";
		$attributes = array('name' => "search_langs", 'id' => "search_langs");
		
		// search for lang matches
		$html = form_open($action,$attributes)."\r";
		$html .= "<p class=\"align-labels\">";
		$html .= form_label($this->cms_labels['lang_find'], 'search');
		$params = array(
		'name'        => 'find',
		'id'          => 'find',
		);
		$html .= form_input($params);
		$html .= "</p>\n";

		$html .= "<p>";
		$html .= form_submit('search_submit', $this->cms_labels['search']);
		$html .= "</p>\n";
		$html .= form_close();
		
		// enable or disable - list of enabled languages
		$attributes = array('name' => "langform", 'id' => "langform");
		$active = array();

		foreach($enabled as $lang) {
			$active[] = $lang['ISO_639_3'];
		}

		$hidden_list = implode(',',$active);

		$hidden = array(
			'enabled_langs' => $hidden_list,
		);

		$html .= form_open($action, $attributes,$hidden);
		$matches = $this->Site_model->matches;
		if(is_array($matches) && count($matches) > 0)
		{
			$html .= "<p class=\"align-labels\">";
			$html .= form_label($this->Site_model->label);
			$html .= "<span class=\"radiogroup\">";
			
			foreach($matches as $lang)
			{
				$params = array(
				'name'        => "new_lang[]",
				'id'        => "new_lang[]",
				'value'       => $lang['ISO_639_3'],
				'checked'     => $lang['activated'],
				);
				$html .= "<label>";
				$html .= form_checkbox($params);
				$html .= ' '.$lang['Inverted_Name'];
				if($lang['Native_Name'] != '') {
					$html .= " ({$lang['Native_Name']})";
				}
				$html .= "</label> ";
			}
			
			$html .= "</span></p>\r";
		}

		if(count($active) > 0) {
			$html .= "<p class=\"align-labels\">";
			$html .= form_label($this->cms_labels['langs_activated']);
			$html .= "<span class=\"radiogroup\">";

			foreach($enabled as $lang) {
				$params = array(
				'name'        => "lang[]",
				'id'        => "lang[]",
				'value'       => $lang['ISO_639_3'],
				'checked'     => TRUE,
				);
				$html .= "<label>";
				$html .= form_checkbox($params);
				$html .= ' '.$lang['Inverted_Name'];
				if($lang['Native_Name'] != '') {
					$html .= " ({$lang['Native_Name']})";
				}
				$html .= "</label> ";
			}
			$html .= "</span></p>\n";
		} else {
			$html .= "<p>".$this->cms_labels['no_langs_activated']."</p>\n";
		}

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();
		return $html;
	}

	function _process_languages()
	{
		if($this->input->post('submit')) {
			$enabled_langs = explode(',',$this->input->post('enabled_langs'));
			$sel_lang = $this->input->post('lang');

			$new_lang = $this->input->post('new_lang');

			if(is_array($sel_lang)) {
				foreach($enabled_langs as $lang) {
					if(in_array($lang,$sel_lang)) {
						$activated = 1;
					} else {
						$activated = 0;
					}
					$this->Site_model->temp_lang = $lang;
					$this->Site_model->temp_value = $activated;
					$this->Site_model->lang_set_active();
				}
			}

			if(is_array($new_lang)) {
				foreach($new_lang as $lang) {
					$this->Site_model->temp_lang = $lang;
					$this->Site_model->temp_value = 1;
					$this->Site_model->lang_set_active();
				}
			}

		}
		if($this->input->post('search_submit')) {
			$this->Site_model->label = sprintf($this->cms_labels['matches'], $this->input->post('find'));
			$this->Site_model->temp_value = $this->input->post('find');
			$this->Site_model->lang_matches();
		}
	}

// content types

	function content_types()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = $this->cms_labels['content_types'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['content_types'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['content_types'];

		$this->Content_model->scope = 'content';
		$this->Content_model->list_types();
		$temp_list = $this->Content_model->type_list;
		
		$types = array();
		
		foreach($temp_list as $type)
		{
			$this->Content_model->label_id = '';
			$this->Content_model->label_scope = 'types';
			$this->Content_model->label_rel_id = '';
			$this->Content_model->label_rel_name = '';
			$this->Content_model->label = '';

			$this->Content_model->temp_id = $type['type_id'];
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
			$lbl = $this->Content_model->load_label();

			$uri = "cms/setup/content_type_edit/id/".$type['type_id'];
		
			if($lbl) {
				$uri .= "/lang/".$this->Content_model->temp_lang;
				$text = $this->Content_model->label;
			} else {
				$text = $type['type_uri_name'];
			}
			$attr = array('title' => sprintf($this->cms_labels['content_type_edit'],$text));
			
			$types[] = anchor($uri,$text,$attr);
		
		}

		$html = '<p>'. anchor("cms/setup/content_type_edit/",$this->cms_labels['content_type_new'])."</p>\r";
		$html .= ul($types);
		$this->data['form'] = $html;

		$this->_load_cms_menus();
		
		$this->load->view('cms/cms_form_2col',$this->data);
	}
	
	function content_type_edit()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = anchor("cms/setup/content_types",$this->cms_labels['content_types']);
		$breadcrumb[] = $this->cms_labels['content_type_editor'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['content_type_editor'];
		$title_tag[] = $this->cms_labels['content_types'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['content_type_editor'];

		$this->_process_content_type();

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Content_model->temp_id = $params['id'];
		}
		if(array_key_exists('lang',$params))
		{
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}

		$this->data['form'] = $this->_form_content_type();


		// construct sidebar lang links
		if($this->Content_model->type_id != '')
		{
			$sidebar = "<h3>".$this->cms_labels['edit_by_lang']."</h3>";
			$this->Site_model->list_langs();
			$enabled = $this->Site_model->lang_list;
			
			$lang_links = array();
			foreach($enabled as $tmp_lang)
			{
	
				$action = "/cms/setup/content_type_edit/id/".$this->Content_model->type_id."/lang/".$tmp_lang['ISO_639_3'];
	
				$lang_links[] = anchor($action,$tmp_lang['Ref_Name']);
			}

			$sidebar .= ul($lang_links);
			$sidebar .=  "\r";

			$this->data['sidebar'] = $sidebar;
		}

		$msg = $this->Content_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);
	}

	function _form_content_type()
	{
		if($this->Content_model->type_id != '')
		{
			$this->Content_model->temp_id = $this->Content_model->type_id;
		}
		$this->Content_model->load_type();

		$this->Content_model->list_type_meta_fields();

		if($this->Content_model->type_id != '') {
			$this->Content_model->reset_label();
			$this->Content_model->label_scope = "types";
			$this->Content_model->temp_id = $this->Content_model->type_id;
			$this->Content_model->load_label();
		}
		
		$langs = $this->Content_model->langs_dropdown();

		$action = "/cms/setup/content_type_edit";
		if($this->Content_model->type_id != '')
		{
			$action .= "/id/".$this->Content_model->type_id;
		}
		if($this->Content_model->temp_lang != '')
		{
			$action .= "/lang/".$this->Content_model->temp_lang;
		}

		$attributes = array( 'name' => "type_editor", 'id' => "type_editor");
		$hidden = array(
		'type_id' => $this->Content_model->type_id,
		'label_id' => $this->Content_model->label_id
		);
		
		$html = form_open($action,$attributes,$hidden)."\r";

		$html .= "<div class=\"align-labels label-wide\">\r";

		// main data
		$html .= form_fieldset($this->cms_labels['general_info']);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['type_uri_name'], 'type_uri_name');
		$html .= form_input('type_uri_name',$this->Content_model->type_uri_name); 
		$html .= "</p>\r";
		$html .= "<p class=\"indent-wide\">".$this->cms_labels['note_type_uri_name']."</p>\r";

		$form_options = array(
		'name'        => 'index_home',
		'id'          => 'index_home',
		'value'       => 1,
		'checked'     => FALSE,
		);

		if($this->Content_model->index_home == 1)
		{
			$form_options['checked'] = TRUE;
		}
		
		$html .= "<p>";
		$html .= form_label($this->cms_labels['content_type_home'], 'index_home');
		$html .= form_checkbox($form_options);
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'index_rss',
		'id'          => 'index_rss',
		'value'       => 1,
		'checked'     => FALSE,
		);
		
		if($this->Content_model->index_rss == 1)
		{
			$form_options['checked'] = TRUE;
		}
		
		$html .= "<p>";
		$html .= form_label($this->cms_labels['content_type_rss'], 'index_rss');
		$html .= form_checkbox($form_options);
		$html .= "</p>\r";

		$form_options = array(
		'name'        => 'menu_children',
		'id'          => 'menu_children',
		'value'       => 1,
		'checked'     => FALSE,
		);
		
		if($this->Content_model->menu_children == 1)
		{
			$form_options['checked'] = TRUE;
		}
		
		$html .= "<p>";
		$html .= form_label($this->cms_labels['menu_children'], 'menu_children');
		$html .= form_checkbox($form_options);
		$html .= "</p>\r";
		$html .= form_fieldset_close("\r");
		$html .= "</div>\r";

		$html .= "<div class=\"align-labels label-normal\">\r";
		// metadata
		$html .= form_fieldset($this->cms_labels['type_metadata']);
		$html .= "\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['language'], 'type_lang');
		$html .= form_dropdown('type_lang', $langs, $this->Content_model->temp_lang); 
		$html .= "</p>\r";
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['type_label'], 'label');
		$html .= form_input('label',$this->Content_model->label); 
		$html .= "</p>\r";
		$html .= form_fieldset_close("\r");

		$html .= "</div>\r";

		// fields
		$html .= form_fieldset($this->cms_labels['fields']);
		$html .= "\r";

		$html .= "<table class=\"properties\">\r";
		$html .= "<thead>\r";
		$html .= "<tr>\r";
		$html .= "<th id=\"hd_name\" class=\"align-left\">".$this->cms_labels['field']."</th>\r";
		$html .= "<th id=\"hd_exists\">".$this->cms_labels['field_use_for_type']."</th>\r";
		$html .= "<th id=\"hd_req\">".$this->cms_labels['required']."</th>\r";
		$html .= "<th id=\"hd_order\">".$this->cms_labels['order']."</th>\r";
		$html .= "</tr>\r";
		$html .= "</thead>\r";

		$fieldCount = count($this->Content_model->field_list);
		$fieldNo = 1;

		$html .= "<tbody>\r";

		foreach($this->Content_model->field_list as $field)
		{
			$row_header = form_hidden('field_id[]', $field['field_id']);
			$row_header .= form_hidden('rf_id[]', $field['rf_id']);

			// get label for field
			$this->Content_model->reset_label();
			$this->Content_model->label_scope = 'field';

			$this->Content_model->temp_id = $field['field_id'];
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Content_model->load_label();

			if($this->Content_model->label_id != '') {
				$row_header .= $this->Content_model->label;
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
			
			$html .= "<tr>\r";
			$html .= "<th headers=\"hd_name\" id=\"row_{$fieldNo}\" class=\"align-left\">".$row_header."</th>\r";
			$html .= "<td headers=\"hd_exists row_{$fieldNo}\">".$chk_exist."</td>\r";
			$html .= "<td headers=\"hd_req row_{$fieldNo}\">".$chk_req."</td>\r";
			$html .= "<td headers=\"hd_order row_{$fieldNo}\">".$input_order."</td>\r";
			$html .= "</tr>\r";

			$fieldNo += 1;

		}
		$html .= "</tbody>\r";
		$html .= "</table>\r";

		$html .= form_fieldset_close("\r");
		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();

		return $html;
	}

	function _process_content_type()
	{

		if($this->input->post('submit') != '')
		{
			// set validation rules
			$this->load->library('form_validation');

			$this->form_validation->set_rules('type_uri_name', $this->cms_labels['type_uri_name'], 'required|alpha_num_spaces');
			$this->form_validation->set_rules('label', $this->cms_labels['type_label'], 'required');

			$form_is_valid = $this->form_validation->run();
			
			// get values from form post data
			$this->Content_model->type_id = $this->input->post('type_id');
			$this->Content_model->type_uri_name = $this->input->post('type_uri_name');
			$this->Content_model->index_home = $this->input->post('index_home');
			$this->Content_model->index_rss = $this->input->post('index_rss');
			$this->Content_model->menu_children = $this->input->post('menu_children');

			if ($form_is_valid)
			{

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
					'rf_rel_id' => $this->Content_model->type_id,
					'rf_rel_ref_name' => $this->Content_model->type_uri_name,
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

				$this->Content_model->scope = 'content';
				if($this->Content_model->update_type())
				{
	
					$this->Content_model->field_list = $field_updates;
					$this->Content_model->scope = 'types';
					$this->Content_model->update_fields_related();

					// update type label
					$this->Content_model->label_scope = 'types';
					$this->Content_model->label_rel_id = $this->Content_model->type_id;
					$this->Content_model->label_rel_name = $this->Content_model->type_uri_name;
					$this->Content_model->label_id = $this->input->post('label_id');
					$this->Content_model->label = $this->input->post('label');
					$this->Content_model->temp_lang = $this->input->post('type_lang');

					if(!$this->Content_model->update_label())
					{
						$this->Content_model->messages[] = sprintf($this->cms_labels['update_failure'],$this->cms_labels['content_type_label']);
					}

					//$this->Content_model->temp_id = $this->Content_model->type_id;

				}
				else
				{
					$this->Content_model->messages[] = $this->cms_labels['content_type_update_failure'];
				}

			}
			else
			{
				$this->Content_model->messages[] = validation_errors();
			}
			
			if(count($this->Content_model->messages) == 0)
			{
				$this->Content_model->messages[] = $this->cms_labels['content_type_update_success'];
			}
			
		}
		
	}

// media types

	function media_types()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = $this->cms_labels['media_types'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_types'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_types'];

		$this->File_model->list_types();
		$temp_list = $this->File_model->type_list;

		$types = array();
		
		foreach($temp_list as $type)
		{
			$this->File_model->label_id = '';
			$this->File_model->label_scope = 'types';
			$this->File_model->label_rel_id = '';
			$this->File_model->label_rel_name = '';
			$this->File_model->label = '';

			$this->File_model->temp_id = $type['type_id'];
			$this->File_model->temp_lang = $this->Site_model->temp_lang;
			$lbl = $this->File_model->load_label();

			$uri = "cms/setup/media_type_edit/id/".$type['type_id'];
		
			if($lbl) {
				$uri .= "/lang/".$this->File_model->temp_lang;
				$text = $this->File_model->label;
			} else {
				$text = $type['type_uri_name'];
			}
			$attr = array('title' => sprintf($this->cms_labels['media_type_edit'],$text));
			
			$types[] = anchor($uri,$text,$attr);
		
		}

		$this->_load_cms_menus();

		$html = '<p>'. $this->cms_labels['media_type_mimes']."</p>\r";
		$html .= ul($types);		$this->data['form'] = $html;
		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function media_type_edit()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = anchor("cms/setup/media_types",$this->cms_labels['media_types']);
		$breadcrumb[] = $this->cms_labels['media_type_editor'];

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_type_editor'];
		$title_tag[] = $this->cms_labels['media_types'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_type_editor'];

		$this->_process_media_type();

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->File_model->temp_id = $params['id'];
		}
		if(array_key_exists('lang',$params)) {
			$this->File_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->File_model->temp_lang = $this->Site_model->temp_lang;
		}

		$this->data['form'] = $this->_form_media_type();

		// construct sidebar lang links
		if($this->File_model->type_id != '')
		{
			$sidebar = "<h2>".$this->cms_labels['edit_by_lang']."</h2>";

			$this->Site_model->list_langs();
			$enabled = $this->Site_model->lang_list;
			
			$lang_links = array();
			foreach($enabled as $tmp_lang)
			{
	
				$action = "/cms/setup/media_type_edit/id/".$this->File_model->type_id."/lang/".$tmp_lang['ISO_639_3'];
	
				$lang_links[] = anchor($action,$tmp_lang['Ref_Name']);
			}

			$sidebar .= ul($lang_links);

			$this->data['sidebar'] = $sidebar;
		}

		$msg = $this->File_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}

		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);
	}
	
	function _form_media_type()
	{
		if($this->File_model->type_id != '')
		{
			$this->File_model->temp_id = $this->File_model->type_id;
		}
		$this->File_model->load_type();

		if($this->File_model->type_id != '') {

			$this->File_model->list_type_meta_fields();

			$this->File_model->reset_label();
			$this->File_model->label_scope = "types";
			$this->File_model->temp_id = $this->File_model->type_id;
			$this->File_model->load_label();
		}

		$action = "/cms/setup/media_type_edit";
		if($this->File_model->type_id != '')
		{
			$action .= "/id/".$this->File_model->type_id;
		}
		if($this->File_model->temp_lang != '')
		{
			$action .= "/lang/".$this->File_model->temp_lang;
		}

		$attributes = array('name' => "type_editor", 'id' => "type_editor");
		$hidden = array(
		'type_id' => $this->File_model->type_id,
		'type_uri_name' => $this->File_model->type_uri_name,
		'label_id' => $this->File_model->label_id,
		);
		
		$html = form_open($action,$attributes,$hidden)."\r";

		// main data
		$html .= "<h2>\r";
		$tmp_editing = sprintf($this->cms_labels['media_type_editing'],$this->File_model->type_uri_name);
		$html .= $tmp_editing; 
		$html .= "</h2>\r";

		$html .= "<div class=\"align-labels label-normal\">\r";

		// metadata
		$options = $this->File_model->langs_dropdown();
		$html .= form_fieldset($this->cms_labels['type_metadata']);
		$html .= "\r";
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['language'], 'type_lang');
		$html .= form_dropdown('type_lang', $options, $this->File_model->temp_lang); 
		$html .= "</p>\r";
		
		$options = array(
		'name'        => 'label',
		'id'          => 'label',
		'value'       => $this->File_model->label,
		'class'   => 'text',
		);
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['type_label'], 'label');
		$html .= form_input($options); 
		$html .= "</p>\r";
		$html .= form_fieldset_close("\r");
		$html .= "</div>\r";

		// fields
		$html .= form_fieldset($this->cms_labels['fields']);
		$html .= "\r";


		$html .= "<table class=\"properties\">\r";
		$html .= "<thead>\r";
		$html .= "<tr>\r";
		$html .= "<th id=\"hd_name\" class=\"align-left\">".$this->cms_labels['field']."</th>\r";
		$html .= "<th id=\"hd_exists\">".$this->cms_labels['field_use_for_type']."</th>\r";
		$html .= "<th id=\"hd_req\">".$this->cms_labels['required']."</th>\r";
		$html .= "<th id=\"hd_order\">".$this->cms_labels['order']."</th>\r";
		$html .= "</tr>\r";
		$html .= "</thead>\r";

		$field_list = $this->File_model->field_list;
		$fieldCount = count($field_list);
		$fieldNo = 1;

		$html .= "<tbody>\r";

		$chk_exist = "&nbsp;";
		$chk_req = "&nbsp;";
		$input_order = "&nbsp;";

		foreach($this->File_model->field_list as $field)
		{

			$row_header = form_hidden('field_id[]', $field['field_id']);
			$row_header .= form_hidden('rf_id[]', $field['rf_id']);

			$tmp_label = $field['field_ref_name'];
			
			// get label for field
			$this->File_model->reset_label();
			$this->File_model->label_scope = 'field';

			$this->File_model->temp_id = $field['field_id'];
			$this->File_model->temp_lang = $this->Site_model->site_default_lang;
			$this->File_model->load_label();

			if($this->File_model->label_id != '') {
				$tmp_label = $this->File_model->label;
			}
			$row_header .= $tmp_label;


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
			
			$html .= "<tr>\r";
			$html .= "<th headers=\"hd_name\" id=\"row_{$fieldNo}\" class=\"align-left\">".$row_header."</th>\r";
			$html .= "<td headers=\"hd_exists row_{$fieldNo}\">".$chk_exist."</td>\r";
			$html .= "<td headers=\"hd_req row_{$fieldNo}\">".$chk_req."</td>\r";
			$html .= "<td headers=\"hd_order row_{$fieldNo}\">".$input_order."</td>\r";
			$html .= "</tr>\r";

			$fieldNo += 1;

		}
		$html .= "</tbody>\r";
		$html .= "</table>\r";
		$html .= form_fieldset_close("\r");

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();

		return $html;
	}

	function _process_media_type()
	{

		if($this->input->post('submit') != '')
		{
			// set validation rules
			$this->load->library('form_validation');

			$this->form_validation->set_rules('type_id', $this->cms_labels['media_type'], 'required|is_natural_no_zero');
			$this->form_validation->set_rules('label', $this->cms_labels['type_label'], 'required');

			$form_is_valid = $this->form_validation->run();
			
			// get values from form post data
			$this->File_model->type_id = $this->input->post('type_id');
			$this->File_model->type_uri_name = $this->input->post('type_uri_name');

			if ($form_is_valid)
			{

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
					'rf_rel_id' => $this->File_model->type_id,
					'rf_rel_ref_name' => $this->File_model->type_uri_name,
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

				$this->File_model->field_list = $field_updates;
				$this->File_model->scope = 'types';
				$this->File_model->update_fields_related();
				
				// update type label
				$this->File_model->label_scope = 'types';
				$this->File_model->label_rel_id = $this->File_model->type_id;
				$this->File_model->label_rel_name = $this->File_model->type_uri_name;
				$this->File_model->label_id = $this->input->post('label_id');
				$this->File_model->label = $this->input->post('label');
				$this->File_model->temp_lang = $this->input->post('type_lang');
				
				if(!$this->File_model->update_label())
				{
					$this->File_model->messages[] = sprintf($this->cms_labels['update_failure'],$this->cms_labels['type_label']);
				}


			}
			else
			{
				$this->File_model->messages[] = validation_errors();
			}
			
			if(count($this->File_model->messages) == 0)
			{
				$this->File_model->messages[] = $this->cms_labels['media_type_update_success'];
			}
			
		}
		
	}

	function lists()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = $this->cms_labels['lists'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['lists'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['lists'];
		
		$lists = $this->Content_model->value_lists_all();
		if($lists)
		{
			
			foreach($lists as $list)
			{
				$label = $list['list_ref_name'];
				$link = 'cms/setup/list_edit/id/'.$list['list_id'].'/lang/'.$this->Content_model->temp_lang;
				if(array_key_exists('label',$list))
				{
					$label = $list['label'];
				}
				$edit_links[] = anchor($link,$label);
				
			}
			$html = ul($edit_links);
			
		}
		else
		{
			$html = "<p>no lists found</p>\r";
		}
		
		$this->data['form'] = $html;
		$this->_load_cms_menus();

		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function list_edit()
	{
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['setup'];
		$breadcrumb[] = $this->cms_labels['data_structure'];
		$breadcrumb[] = anchor("cms/setup/lists",$this->cms_labels['lists']);
		$breadcrumb[] = $this->cms_labels['list_edit'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['list_edit'];
		$title_tag[] = $this->cms_labels['lists'];
		$title_tag[] = $this->cms_labels['data_structure'];
		$title_tag[] = $this->cms_labels['setup'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['list_edit'];
		
		$this->_process_list_edit();
		$this->_form_list_edit();

		$msg = $this->Content_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->_load_cms_menus();

		$this->load->view('cms/cms_form',$this->data);
	}
	
	function _form_list_edit()
	{
		$params = $this->uri->uri_to_assoc(4);

		if(array_key_exists('id',$params))
		{
			$this->Content_model->temp_id = $params['id'];
		}
		if(array_key_exists('lang',$params))
		{
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
		}
		$this->Content_model->lang_load();
		$this->Content_model->load_value_list();
		
		$action = $this->uri->uri_string();
		$attributes = array('name' => "list_editor", 'id' => "list_editor");
		$hidden = array('list_id' => $this->Content_model->list_id);
		$html = form_open($action,$attributes,$hidden)."\r";
		$html .= form_fieldset($this->cms_labels['general_info']);

		$html .= "<p class=\"align-labels label-normal\">\r";
		$html .= form_label($this->cms_labels['ref_name'], 'list_ref_name');
		$html .= form_input('list_ref_name',$this->Content_model->list_ref_name); 
		$html .= "</p>\r";

		$html .= form_fieldset_close("\r");

		// metadata
		$html .= form_fieldset($this->cms_labels['field_metadata'])."\r";

		$html .= "<div class=\"align-labels label-normal\">\r";

		$form_options = $this->Content_model->langs_dropdown();
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['language'], 'list_lang');
		$html .= form_dropdown('list_lang', $form_options, $this->Content_model->temp_lang); 
		$html .= "</p>\r";
		
		$html .= "<p>\r";
		$html .= form_label($this->cms_labels['label'], 'label');
		$html .= form_input('label',$this->Content_model->label); 
		$html .= "</p>\r";

		$html .= "<div>\r";
		$html .= form_label($this->cms_labels['list_values'], 'listvals');

		$html .= "<table id=\"listvals\">\r";

		$html .= "<thead>\r";
		$html .= "<tr>\r";
		$html .= "<th class=\"align-left\">".$this->cms_labels['values']."</th>\r";
		$html .= "<th class=\"align-left\">".$this->cms_labels['labels']."</th>\r";
		$html .= "</tr>\r";
		$html .= "</thead>\r";

		$html .= "<tbody>\r";
		// existing values
		foreach($this->Content_model->value_list as $value)
		{
			$html .= "<tr>\r";
			$html .= form_hidden('val_ids[]', $value['val_id']);
			
			$form_options = array(
			'size'        => '13',
			'name'        => 'list_values[]',
			'id'          => 'list_values[]',
			'value'       => $value['value'],
			);
			$html .= "<td>";
			$html .= form_input($form_options); 
			$html .= "</td>\r";

			$form_options = array(
			'size'        => '25',
			'name'        => 'list_labels[]',
			'id'          => 'list_labels[]',
			'value'       => $value['label'],
			);
			$html .= "<td>";
			$html .= form_input($form_options); 
			$html .= "</td>\r";

			$html .= "</tr>\r";
		}
		// new values (5 rows)
		$i = 1;
		while ($i <= 5) {
			$i++;

			$html .= "<tr>\r";
			
			$form_options = array(
			'size'        => '13',
			'name'        => 'new_values[]',
			'id'          => 'new_values[]',
			);
			$html .= "<td>";
			$html .= form_input($form_options); 
			$html .= "</td>\r";

			$form_options = array(
			'size'        => '25',
			'name'        => 'new_labels[]',
			'id'          => 'new_labels[]',
			);
			$html .= "<td>";
			$html .= form_input($form_options); 
			$html .= "</td>\r";

			$html .= "</tr>\r";

		}

		$html .= "</tbody>\r";

		$html .= "</table>\r";

		$html .= "</div>\r";

		$html .= "</div>\r";

		$html .= form_fieldset_close("\r");


		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();
		
		$this->data['form'] = $html;

		// construct sidebar lang links
		if($this->Content_model->list_id != '')
		{
			$sidebar = "<h2>".$this->cms_labels['edit_by_lang']."</h2>";

			$this->Site_model->list_langs();
			$enabled = $this->Site_model->lang_list;
			
			$lang_links = array();
			foreach($enabled as $tmp_lang)
			{
				$action = "/cms/setup/list_edit/id/".$this->Content_model->list_id."/lang/".$tmp_lang['ISO_639_3'];
				$lang_links[] = anchor($action,$tmp_lang['Ref_Name']);
			}
			$sidebar .= ul($lang_links);
			$this->data['sidebar'] = $sidebar;
		}

	}

	function _process_list_edit()
	{

		if($this->input->post('submit') != '')
		{
			// set validation rules
			$this->load->library('form_validation');

			$this->form_validation->set_rules('list_ref_name', $this->cms_labels['ref_name'], 'required');
			$this->form_validation->set_rules('label', $this->cms_labels['label'], 'required');
			$this->form_validation->set_rules('list_lang', $this->cms_labels['language'], 'required|is_lang');

			$form_is_valid = $this->form_validation->run();
			
			// get values from form post data
			$this->Content_model->list_id = $this->input->post('list_id');
			$this->Content_model->list_ref_name = $this->input->post('list_ref_name');
			$this->Content_model->temp_lang = $this->input->post('list_lang');
			$this->Content_model->label = $this->input->post('label');
			
			$val_ids = $this->input->post('val_ids');
			$list_values = $this->input->post('list_values');
			$list_labels = $this->input->post('list_labels');

			$new_values = $this->input->post('new_values');
			$new_labels = $this->input->post('new_labels');
			
			$list_values_final = array();

			if ($form_is_valid)
			{
				// update list 
				if($this->Content_model->update_list())
				{
					$this->Content_model->label_scope = 'list';
					$this->Content_model->label_rel_id = $this->Content_model->list_id;
					$this->Content_model->label_rel_name = $this->Content_model->list_ref_name;

					// update label 
					if($this->Content_model->update_label())
					{

						// update list values
						foreach($val_ids as $index => $v_id)
						{
							if($list_values[$index] == '' && $list_labels[$index] == '')
							{
								// delete this row
							}
							else if($list_values[$index] == '' || $list_labels[$index] == '')
							{
								if($list_values[$index] == '')
								{
									$this->Content_model->messages[] = 'missing value #'.$v_id;
								}
								if($list_labels[$index] == '')
								{
									$this->Content_model->messages[] = 'missing label #'.$v_id;
								}
							}
		
							$tmp_vals = array(
							'val_id' => $v_id,
							'value' => $list_values[$index],
							'label' => $list_labels[$index],
							);
							
							$list_values_final[] = $tmp_vals;
							
						}
						foreach($new_values as $index => $val)
						{
							if($val != '')
							{
								$tmp_vals = array(
								'val_id' => '',
								'value' => $val,
								'label' => $new_labels[$index],
								);
								
								$list_values_final[] = $tmp_vals;
							}
						}
						
		
						if(count($this->Content_model->messages) == 0)
						{
							$this->Content_model->value_list = $list_values_final;
							$this->Content_model->update_list_vals();
						}
						
					}
				
				}
				

			}
			else
			{
				$this->Content_model->messages[] = validation_errors();
			}
			
			if(count($this->Content_model->messages) == 0)
			{
				$tmp_msg = sprintf($this->cms_labels['update_success'],$this->Content_model->list_ref_name);
				$this->Content_model->messages[] = $tmp_msg;
			}
			
		}
		
	}

}
?>