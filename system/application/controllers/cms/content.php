<?php
class Content extends MY_Controller
{
	var $whichform;
	var $selected;
	
	function Content()
	{
		parent::Controller();

		$this->load->database();
		$this->load->model('Site_model');
		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->model('File_model');
		$this->load->helper('image');
		$this->load->helper('form');
		$this->load->helper('timezone');
		$this->load->library('session');

		$this->_authenticate();

		$this->whichform = 'list';
		$this->selected = array();
		$this->data['sidebar_panel'] = TRUE;
	}

	function index()
	{
		$this->_process_manage();
		if(count($this->selected) > 0 && $this->whichform == 'confirm')
		{
			$this->_form_delete();
		}
		else
		{
			$this->_form_list();
		}

		$this->data['title_h1'] = $this->cms_labels['content'];
		$this->data['title_tag'] = $this->cms_labels['content'];

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['content'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

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
		$msg = $this->Content_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}
		$this->_load_cms_menus();
	
		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function _form_list()
	{
		$this->Content_model->levels = -1;
		$this->Content_model->link_mode = 1;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		if($this->Content_model->lang_ISO_639_3 == '')
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}

		// get filter for list
		$params = $this->uri->uri_to_assoc();
		$limit = $this->input->post('limit');
		if(array_key_exists('limit',$params))
		{
			$limit = $params['limit'];
		}
		if(!$limit || $limit == '')
		{
			$limit = 10;
		}

		$this->Content_model->limit = $limit;

		$type = $this->input->post('type_id');
		if(array_key_exists('type',$params))
		{
			$type = $params['type'];
		}
		if ($type !== FALSE)
		{
			$this->Content_model->type_id = $type;
		}

		$this->Content_model->page = 1;
		if(array_key_exists('page',$params))
		{
			$this->Content_model->page = $params['page'];
		}

		$this->Content_model->list_content();
		$total = $this->Content_model->content_count;

		$end = $this->Content_model->page * $limit;
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

		$action = 'cms/content';
		$attributes = array(
		'id' => 'content_list'
		);
		$html = form_open($action, $attributes);
		$html .= form_hidden('confirm', '')."\r";
		
		// get list of content types
		$form_options[''] = $this->cms_labels['all'];
		$this->Content_model->scope = 'content';
		$this->Content_model->list_types();
		$list = array();
		foreach($this->Content_model->type_list as $row)
		{
			if($row['label'] != '')
			{
				$form_options[$row['type_id']] = $row['label'];
			} else {
				$form_options[$row['type_id']] = $row['type_uri_name'];
			}
		}

		$html .= "<div class=\"form-filter\">\r";
		$html .=  form_label($this->cms_labels['content_type'], 'type_id');
		$html .=  form_dropdown('type_id', $form_options, $this->Content_model->type_id);

		$form_options = array(10 => 10,20 => 20,30=>30,40=>40,50=>50);
		$html .=  form_label($this->cms_labels['results_per_page'], 'limit');
		$html .=  form_dropdown('limit', $form_options, $limit);
		$html .= form_submit('filter', $this->cms_labels['results_filter']);
		$html .= "</div>\r";


		$html .= "<p class=\"align-center\">\r";
		$html .= "{$results}<br />\r";
		$this->load->library('pagination');
		$base_url = '/cms/content/find';
		$base_url .= '/limit/'.$limit;
		if ($this->Content_model->type_id != '')
		{
			$base_url .= '/type/'.$this->Content_model->type_id;
		}
		$base_url .= '/page/';

		$pconfig['base_url'] = $base_url;
		$pconfig['total_rows'] = $total;
		$pconfig['per_page'] = $limit;
		$pconfig['cur_page'] = $this->Content_model->page;
		$this->pagination->initialize($pconfig);

		$html .= $this->pagination->create_links_proper();
		$html .= "</p>\r";

		// generate result table
		$html .= "<table class=\"borders full\">\r";
		$html .= "\t<thead>\r";
		$html .= "\t\t<tr>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"content-ids\">{$this->cms_labels['title']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"types\">{$this->cms_labels['content_type']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"authors\">{$this->cms_labels['author']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"created\">{$this->cms_labels['date_created']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"published\">{$this->cms_labels['published']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"navved\">{$this->cms_labels['show_in_nav']}</th>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"actions\">{$this->cms_labels['select']}\r";
		$html .= "<ul class=\"option-list\">\r";
		$html .= "<li class=\"hide\"><a href=\"#\" id=\"select-all\">{$this->cms_labels['all']}</a></li>\r";
		$html .= "<li class=\"hide\"><a href=\"#\" id=\"select-none\">{$this->cms_labels['none']}</a></li>\r";
		$html .= "</ul>\r";
		$html .= "</th>\r";
		$html .= "\t\t</tr>\r";
		$html .= "\t</thead>\r";
		$html .= "\t<tbody>\r";
		
		foreach($this->Content_model->content_list as $item)
		{

			// determine edit uri
			$edit_uri = 'cms/content/edit/id/'.$item['content_id'];
			if($this->Content_model->temp_lang != '')
			{
				$edit_uri .= '/lang/'.$this->Content_model->temp_lang;
			}
			$cid = $item['content_id'];

			$html .= "\t\t\t<th id=\"content-{$cid}\" headers=\"content-ids\">";
			$html .= anchor($edit_uri, $item['title'], array('title' => sprintf($this->cms_labels['edit_this'],$item['title'])));
			$html .= "</th>\r";

			$type_name = $item['type_uri_name'];
			if($item['type_label'] != '')
			{
				$type_name = $item['type_label'];
			}
			$html .= "\t\t\t<td headers=\"content-{$cid} types\">".$type_name."</td>\r";

			// author info
			$this->User_model->temp_id = $item['user_id'];
			$this->User_model->load_user();
			$name = $this->User_model->name_last.', '.$this->User_model->name_first;

			if($this->session->userdata('admin') == 1)
			{
				$html .= "\t\t\t<td headers=\"content-{$cid} authors\">".anchor('cms/user/edit/id/'.$item['user_id'],$name,array('title' => $this->cms_labels['user_edit']))."</td>\r";
			}
			else
			{
				$html .= "\t\t\t<td headers=\"content-{$cid} authors\">".$name."</td>\r";
			}

			$html .= "\t\t\t<td headers=\"content-{$cid} created\">".$item['date_created']."</td>\r";

			$options = array($this->cms_labels['no'],$this->cms_labels['yes']);
			$html .= "\t\t\t<td headers=\"content-{$cid} published\">";
			$html .= $options[$item['publish']];
			$html .= "</td>\r";

			$html .= "\t\t\t<td headers=\"content-{$cid} navved\">";
			$html .= $options[$item['show_in_nav']];
			$html .= "</td>\r";

			$html .= "\t\t\t<td headers=\"content-{$cid} actions\">";

			$options = array(
			'name'        => "select[]",
			'value'       => $item['content_id'],
			);
			
			$html .= form_checkbox($options);
			$html .= "</td>\r";
			$html .= "\t\t</tr>\r";
		}

		$html .= "\t</tbody>\r";
		$html .= "</table>\r";

		$form_options = array(
		''=>'',
		'delete' => $this->cms_labels['delete'],
		'publish'=> $this->cms_labels['publish'],
		'unpublish'=> $this->cms_labels['unpublish'],
		'show'=> $this->cms_labels['show_in_nav'],
		'hide'=> $this->cms_labels['hide_from_nav']
		);
		$html .= "<p class=\"align-right\">";
		$html .=  form_label($this->cms_labels['with_selected'].' : ', 'action', array('class' => 'inline'));
		$html .=  form_dropdown('action', $form_options);
		$html .= "&nbsp; ";

		$html .= form_submit('submit', $this->cms_labels['submit']);
		$html .= "</p>\n";

		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _form_comfirm_delete()
	{
		$action = 'cms/content';
		$attributes = array(
		'id' => 'confirm_delete'
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
		
		if($select !== FALSE && is_array($select))
		{
			$this->selected = $select;
			$this->Content_model->temp_value = $select;
			$action = $this->input->post('action');
			if($action)
			{
				switch($action)
				{
				case 'delete':
					$confirm = $this->input->post('confirm');
					if($confirm == 1)
					{
						$this->Content_model->delete_batch();
					}
					else
					{
						$this->whichform = 'confirm';
					}

					break;
				case 'publish':
					$this->Content_model->publish = 1;
					if($this->Content_model->update_publish())
					{
						$this->Content_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->Content_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;
				case 'unpublish':
					$this->Content_model->publish = 0;
					if($this->Content_model->update_publish())
					{
						$this->Content_model->messages[] = $this->cms_labels['publish_update_success'];
					}
					else
					{
						$this->Content_model->messages[] = $this->cms_labels['publish_update_failure'];
					}
					break;

				case 'show':
					$this->Content_model->show_in_nav = 1;
					if($this->Content_model->update_navigation())
					{
						$this->Content_model->messages[] = $this->cms_labels['nav_update_success'];
					}
					else
					{
						$this->Content_model->messages[] = $this->cms_labels['nav_update_failure'];
					}
					break;
				case 'hide':
					$this->Content_model->show_in_nav = 0;
					if($this->Content_model->update_navigation())
					{
						$this->Content_model->messages[] = $this->cms_labels['nav_update_success'];
					}
					else
					{
						$this->Content_model->messages[] = $this->cms_labels['nav_update_failure'];
					}
					break;

				default:
				}
			}
		}
		$this->File_model->temp_value = '';

	}

	function site_map()
	{

		$this->Content_model->levels = -1;
		$this->Content_model->link_mode = 1;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		if($this->Content_model->lang_ISO_639_3 == '')
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}

		$body = "<p>".$this->cms_labels['click_to_edit']."</p>\r";
		$body .= $this->Content_model->site_map();

		$this->data['form'] = $body;

		$this->data['title_h1'] = $this->cms_labels['site_map'];
		$this->data['title_tag'] = $this->cms_labels['site_map'];

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['site_map'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		
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
		if( array_key_exists('lang',$params) )
		{
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
		}
		if( array_key_exists('type',$params) )
		{
			$this->Content_model->type_id = $params['type'];
		}

		$this->Content_model->temp_value = $this->Content_model->temp_lang;

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
		$this->_load_cms_menus();

		$this->load->view('cms/cms_form_2col',$this->data);

	}
	function _form_edit()
	{
		$this->load->helper('form');

		$action = 'cms/content/edit';
		$my_url = $action;
		$form_options = array(
		'name' => 'content_editor',
		'id' => 'content_editor',
		'class' => 'collapsible'
		);

		if($this->Content_model->content_id != '')
		{
			$action .= '/id/'.$this->Content_model->content_id;
		}
		if($this->Content_model->temp_lang != '')
		{
			$action .= '/lang/'.$this->Content_model->temp_lang;
		}

		$html = form_open($action, $form_options);
		$html .= form_hidden('content_id',$this->Content_model->content_id);
		$html .= form_hidden('title_id',$this->Content_model->title_id);
		$html .= form_hidden('my_url',site_url($my_url));

		$html .= "<div class=\"align-labels label-normal\">\r";
		
		// CONTENT fieldset

		$html .= form_fieldset('Content');
		
		$form_options = $this->Content_model->langs_dropdown();
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['language'], 'content_lang');
		$html .=  form_dropdown('content_lang', $form_options, $this->Content_model->temp_lang);
		$html .= "</p>\n";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->Content_model->title
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['title'],'title');
		$html .= form_input($form_options);
		$html .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->Content_model->subtitle
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['subtitle'],'subtitle');
		$html .= form_input($form_options);
		$html .= "</p>";

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
				$tmp_list_id = $fd['list_id'];
			}
			else
			{
				$this->Content_model->temp_id = $index;
				$this->Content_model->load_field();

				$tmp_ref_name = $this->Content_model->field_ref_name;
				$tmp_needs_lang = $this->Content_model->field_needs_lang;
				$tmp_input_type = $this->Content_model->field_input_type;
				$tmp_validation = $this->Content_model->field_validation;
				$tmp_list_id = $this->Content_model->list_id;
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

			$this->Content_model->reset_label();
			$this->Content_model->label_scope = 'field';
			$this->Content_model->temp_id = $index;
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;

			if($this->Content_model->load_label())
			{
				$tmp_label = $this->Content_model->label;
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
				$this->File_model->list_files(TRUE);

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
				$this->Content_model->list_attachments();
				$this->Content_model->lang_load();

				$dir_files = array('' => $this->cms_labels['none']);

				if(count($this->Content_model->attachments) > 0)
				{
					foreach($this->Content_model->attachments as $index => $media)
					{
						$this->File_model->reset_media_vars();
						$this->File_model->temp_id = $media['media_id'];
						$this->File_model->load_media();
						$this->File_model->load_title();
						$dir_files[$this->File_model->media_id] = $this->File_model->title;
					}
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
				$this->Content_model->temp_id = $fd['list_id'];
				$this->Content_model->load_value_list();
				$listvals = prepare_list_values($this->Content_model->value_list);

				$html .= ph_form_list($form_options['name'], $listvals, $fd['value'], $fd['field_allow_multiple']);

				break;
			default:
				$html .=  form_input($form_options);
			}
			$html .= "</p>\r";
		}

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->Content_model->keywords
		);
		$html .= "<p>";
		$html .= form_label($this->cms_labels['keywords'],'keywords');
		$html .=  form_textarea($form_options);
		$html .= "</p>\r";

		$html .= form_fieldset_close();
		$html .= "</div>\r";

		// general info fieldset
		$html .= "<div class=\"align-labels label-wide\">\r";

		$html .= form_fieldset($this->cms_labels['general_info']);

		$this->Content_model->scope = 'content';
		$form_options = $this->Content_model->types_dropdown();
		
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['content_type'], 'type_id');
		$html .=  form_dropdown('type_id', $form_options, $this->Content_model->type_id);
		$html .= "</p>\n";

		$form_options = $this->Content_model->get_parents_dropdown();
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['parent'], 'parent_id');
		$html .=  form_dropdown('parent_id', $form_options, $this->Content_model->parent_id);
		$html .= "</p>\n";
	
		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['publish'], 'publish');
		$html .=  form_dropdown('publish', $form_options, $this->Content_model->publish);
		$html .= "</p>\n";

		$html .= "<p>";
		$html .=  form_label($this->cms_labels['show_in_nav'], 'show_in_nav');
		$html .=  form_dropdown('show_in_nav', $form_options, $this->Content_model->show_in_nav);
		$html .= "</p>\n";

		$form_options = $this->User_model->get_list_dropdown();
		$html .= "<p>";
		$html .= form_label($this->cms_labels['author'], 'user_id');
		$html .=  form_dropdown('user_id', $form_options, $this->Content_model->user_id);
		$html .= "</p>\n";

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
		'class'	=> 'med_input',
		'value' => $this->Content_model->date_created,
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['date_created_start'], 'date_created');
		$html .=  form_input($form_options);
		$html .= "</p>\n";

		$form_options = array(
		'name'        => 'date_end_expiry',
		'id'          => 'date_end_expiry',
		'class'	=> 'med_input',
		'value' => $this->Content_model->date_end_expiry,
		);
		$html .= "<p>";
		$html .=  form_label($this->cms_labels['date_end_expiry'], 'date_end_expiry');
		$html .=  form_input($form_options);
		$html .= "</p>\n";

		$html .= form_fieldset_close();

		// attachments fieldset (only display if content ID exists)
		if($this->Content_model->content_id != '')
		{

			$this->Content_model->list_attachments();
			$this->Content_model->lang_load();
	
			$html .= form_fieldset($this->cms_labels['attachments']);
			if(count($this->Content_model->attachments) > 0)
			{
				$tmp_txt = $this->cms_labels['attachments_manage'];
			}
			else
			{
				$tmp_txt = $this->cms_labels['attachments_add'];
			}
	
			$html .= "<p>";
			$html .= anchor('cms/content/attachments/'.$this->Content_model->content_id, $tmp_txt);
			$html .= "</p>\r";
	
			$this->load->config('mime_info');
			$this->lang->load('mime', $this->Content_model->lang_CI_Name);
			$mime_labels = $this->lang->line('mime');
			$mime_icons = $this->config->item('mime_icons');
			
			if(count($this->Content_model->attachments) > 0)
			{
				$html .= "<div class=\"block margins\">\r";
				foreach($this->Content_model->attachments as $att)
				{
					$html .= "<div class=\"column\">\r";
					$this->File_model->reset_media_vars();
					$this->File_model->temp_id = $att['media_id'];
	
					if($this->File_model->load_media())
					{
						$this->File_model->load_title();
						
						// determine edit uri
						$edit_uri = 'cms/media/edit/id/'.$this->File_model->media_id;
						if($this->File_model->temp_lang != '')
						{
							$edit_uri .= '/lang/'.$this->File_model->temp_lang;
						}
	
						$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->File_model->mime_type);
						$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->File_model->file_uri));
	
						$filename = basename($this->File_model->file_uri);
	
						// if local image, do thumbnail
						if($mime_cat == 'image' && $this->File_model->external == 0)
						{
							$html .= "<div class=\"image-left\">";
							$img_path = $this->File_model->dir_base.$this->File_model->file_uri;
							$thumb = view_image($img_path,$this->Site_model->img_size_th,array('alt' => sprintf($this->cms_labels['edit_this'],$filename)));
							$html .= anchor($edit_uri, $thumb, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
							$html .= "</div>";
						}
	
						if(in_array($ext,$mime_icons))
						{
							$html .= "<span class=\"file ext_{$ext}\">";
						}
						else if($mime_cat != 'application')
						{
							$html .= "<span class=\"icon icon_{$mime_cat}\">";
						}
						else
						{
							$html .= "<span class=\"file\">";
						}
	
						if($this->File_model->title != '' && $this->File_model->title != $filename)
						{
							$filename = $this->File_model->title;
						}
	
						$html .= anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
						$html .= "</span><br />\r";
	
						if(array_key_exists($ext,$mime_labels))
						{
							$html .= $mime_labels[$ext];
						}
						else
						{
							$html .= $this->File_model->mime_type;
						}
	
					}
					$html .= "</div>";
	
				}
				$html .= "</div>";
	
			}
			else
			{
				$html .= "<p>";
				$html .= $this->cms_labels['attachments_none'];
				$html .= "</p>\r";
			}
	
			$html .= form_fieldset_close();


		}
		
		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= "</div>";

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
				$this->Content_model->show_in_nav = $this->input->post('show_in_nav');

				$this->Content_model->title_id = $this->input->post('title_id');
				$this->Content_model->temp_lang = $this->input->post('content_lang');
				$this->Content_model->title = $this->input->post('title');
				$this->Content_model->subtitle = $this->input->post('subtitle');
				$this->Content_model->keywords = csv_normalize($this->input->post('keywords'));

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

						$value = decode_html_entities($this->input->post($val_lbl));
						
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
				$this->Content_model->clear_title();
				$this->Content_model->temp_lang = $llang['ISO_639_3'];
				$this->Content_model->temp_id = $this->Content_model->content_id;
				$this->Content_model->load_title();

				$temp_link = 'cms/content/edit/id/'.$this->Content_model->content_id.'/lang/'.$llang['ISO_639_3'];

				if($llang['Native_Name'] != '')
				{
					$temp_txt = $llang['Native_Name'];
				}
				else
				{
					$temp_txt = $llang['Ref_Name'];
				}
				

				if($this->Content_model->title != '')
				{
					$temp_txt = $this->Content_model->title." ($temp_txt)";
				}

				
				$lang_links[] = anchor($temp_link,$temp_txt);
			}

			$this->Content_model->scope = 'content';
			$this->Content_model->temp_id = $this->Content_model->content_id;
			$this->Content_model->temp_lang = $this->Content_model->temp_value;			
			$my_uri = $this->Content_model->generate_uri();

			$my_link = anchor($my_uri,$this->cms_labels['view_content'], array('title' => $this->cms_labels['view_content_title'], 'rel' => 'external'));
			
			$menu .= '<h3>'.sprintf($this->cms_labels['options'],$this->cms_labels['editor'])."</h3>\r";
			$menu .=  "<ul>\r";
			$menu .=  "<li>".$my_link."\r";
			$menu .=  "<li>".$this->cms_labels['edit_by_lang']."\r";
			$menu .= ul($lang_links);
			$menu .=  "</li>\r";
	
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

	function attachments()
	{
		$this->_process_attachments();
		$this->_form_attachments();

		$this->data['title_h1'] = $this->cms_labels['attachments_manage'];
		$this->data['title_tag'] = $this->cms_labels['attachments_manage'];

		$width = round(($this->Site_model->img_size_sm/15)+2.5,3);
		$height = round(($this->Site_model->img_size_th/15)+2.5,3);
		
		$custom_header = "<style type=\"text/css\">\r";
		$custom_header .= "/* <![CDATA[ */\r";
		$custom_header .= "ul.thumbnails li { min-height: {$height}em; }\r";
		$custom_header .= "ul.thumbnails li .thumb { width: {$width}em; height: {$height}em; }";
		$custom_header .= "/* ]]> */\r";
		$custom_header .= "</style>\r";
		$custom_header .= "\r";
		$this->data['custom_header'] = $custom_header;
		
		$this->load->view('cms/cms_form_2col',$this->data);
	}

	function _form_attachments()
	{
		$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');

		$this->Content_model->content_id = $this->uri->segment(4);
		$this->Content_model->load_content();
		$this->File_model->temp_lang = $this->Content_model->temp_lang;
		$this->File_model->lang_load();
		$this->lang->load('mime', $this->File_model->lang_CI_Name);
		$mime_cat_labels = $this->lang->line('mime_cat');
		$mime_labels = $this->lang->line('mime');
		
		$return_uri = 'cms/content/edit/id/'.$this->Content_model->content_id;

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/content",$this->cms_labels['content']);
		$breadcrumb[] = anchor($return_uri,$this->Content_model->title);
		$breadcrumb[] = $this->cms_labels['attachments_manage'];
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$html = "<p>";
		$html .=  anchor($return_uri,sprintf($this->cms_labels['return_to'],$this->Content_model->title));
		$html .= "</p>\r";
		
		$this->File_model->list_files(TRUE,TRUE,TRUE);

		$this->Content_model->list_attachments();

		$action = '/cms/content/attachments/'.$this->Content_model->content_id;
		$attributes = array(
		'id' => 'manage_attachments'
		);
		$html .= form_open($action, $attributes)."\r";
		$html .= form_hidden('content_id', $this->Content_model->content_id)."\r";
		$html .= form_fieldset($this->cms_labels['attachments']);
		

		$now_attached = array();

		if(count($this->Content_model->attachments) > 0)
		{
			$html .= "<table class=\"borders full\">\r";
			$html .= "\t<thead>\r";
			$html .= "\t\t<tr>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"files\">{$this->cms_labels['file']}</th>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"removal\">{$this->cms_labels['remove']}</th>\r";
			$html .= "\t\t</tr>\r";
			$html .= "\t</thead>\r";
			$html .= "\t<tbody>\r";

			$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');
			foreach ($this->Content_model->attachments as $file)
			{
				$this->File_model->reset_media_vars();

				$now_attached[] = $file['media_id'];
				$this->File_model->temp_id = $file['media_id'];
				$this->File_model->load_media();
				$this->File_model->load_title();

				$html .= "\t\t<tr>\r";

				// get mime category
				// get filename and extension
				$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->File_model->mime_type);
				$filename = basename($this->File_model->file_uri);
				$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->File_model->file_uri));
				$mid = $this->File_model->media_id;
	
				// determine edit uri
				$edit_uri = 'cms/media/edit/id/'.$this->File_model->media_id;
				if($this->File_model->temp_lang != '')
				{
					$edit_uri .= '/lang/'.$this->File_model->temp_lang;
				}
	
				$html .= "\t\t\t<th id=\"file-{$mid}\" headers=\"files\">";
				if($mime_cat == 'image' && $this->File_model->external == 0)
				{
					$html .= "<div class=\"image-left\">";
					// get thumbnail
					$img_path = $this->File_model->dir_base.$this->File_model->file_uri;
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
				if($this->File_model->title != '' && $this->File_model->title != $filename)
				{
					$html .= $this->File_model->title."<br />\r";
				}
				$html .= anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
				$html .= "</span><br />\r".$mime_labels[$ext];
				$html .= "</th>\r";

				$html .= "\t\t\t<td headers=\"att-{$file['media_id']} removal\">";

				$options = array(
				'name'        => "remove[]",
				'value'       => $file['a_id'],
				);
				
				$html .= form_checkbox($options);

				$html .= "</td>\r";
				$html .= "\t\t</tr>\r";
			}
			$html .= "\t</tbody>\r";
			$html .= "</table>\r";
		}
		$html .= form_fieldset_close();
		$this->File_model->media_list = $now_attached;
		// media browser
		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";

		// get filter for list
		$params = $this->uri->uri_to_assoc(3);
		$html .= form_fieldset($this->cms_labels['media']);

		$base_url = '/cms/content/attachments/'.$this->Content_model->content_id;
		$html .= $this->_media_form_table($params,$base_url,false,$this->cms_labels['attach']);

		$html .= form_fieldset_close();

		$html .= "<p>";
		$html .= form_submit('submit', $this->cms_labels['save']);
		$html .= "</p>\n";
		$html .= form_close();

		$this->data['form'] = $html;
	}

	function _process_attachments()
	{
		$this->Content_model->content_id = $this->input->post('content_id');
		// first remove any marked for removal
		$remove = $this->input->post('remove');
		if($remove)
		{
			$this->Content_model->temp_value = $remove;
			if(!$this->Content_model->remove_attachments())
			{
				$this->Content_model->messages[] = $this->cms_labels['attachments_remove_failure'];
			}
		}
		// then attach any selected items
		$this->selected = $this->input->post('select');
		if(is_array($this->selected) && count($this->selected) > 0)
		{
			$this->Content_model->temp_value = $this->selected;
			if(!$this->Content_model->add_attachments())
			{
				$this->Content_model->messages[] = $this->cms_labels['attachments_add_failure'];
			}
		}
		if(count($this->Content_model->messages) == 0)
		{
			$this->Content_model->messages[] = $this->cms_labels['attachments_update_success'];
		}
	}
}
?>
