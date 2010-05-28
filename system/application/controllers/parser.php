<?php

class Parser extends MY_Controller {

	var $temp_uri;
	var $view;
	var $segments = array();
	var $breadcrumb_links = array();
	var $page;

	var $parent_title;
	
	function Parser()
	{
		parent::MY_Controller();
		$this->load->model('Content_model');
		$this->load->model('Media_model');
		$this->load->model('User_model');
		$this->load->helper(array('url', 'image'));

		$this->Content_model->list_langs();
	}
	
    function index()
    {

		$this->Content_model->link_mode = 0;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		// get all parameters from uri
		$this->_parse_uri();
		
		if(count($this->segments) > 0)
		{

			// if site preferences specify a vaild home page ID, then omit home page from menu
			if($this->Site_model->home_content_id > 0)
			{
				$this->Content_model->content_id = $this->Site_model->home_content_id;
				$this->Content_model->omit_id = $this->Site_model->home_content_id;
				
				if($this->Content_model->content_exists() === TRUE)
				{
					$this->Content_model->omit_id = $this->Site_model->home_content_id;
				}

			}

			$this->_find_content();
			$this->_prepare_view();
		}
		else
		{
			// if site preferences specify a vaild home page ID, then redirect and omit home page from menu, otherwise auto-generate home page
			if($this->Site_model->home_content_id > 0)
			{
				$this->Content_model->content_id = $this->Site_model->home_content_id;
				$this->Content_model->omit_id = $this->Site_model->home_content_id;
				
				if($this->Content_model->content_exists() === TRUE)
				{
					$this->Content_model->load_content();
					$this->_prepare_view();
				}
				else
				{
					$this->_home();
					
				}
				
			}
			else
			{
				$this->_home();
			}
		}

		if(is_array($this->breadcrumb_links) && count($this->breadcrumb_links) > 0)
		{
			$this->data['breadcrumb_links'] = $this->breadcrumb_links;
		}
		
		$header_view = 'themes/'.$this->Site_model->theme.'/inc_header';

		$header_view_full = APPPATH.'views/'.$header_view.'.php';
		if(file_exists($header_view_full))
		{
			$this->data['html_header'] = $this->load->view($header_view,$this->data,TRUE);
		}
		
		$this->load->view($this->view,$this->data);

    }

	function _parse_uri()
	{

		$uri_segments = array();
		$uri_array = $this->uri->segment_array();

		$params = array('page' => '','lang' => '','date' => '');
		$patterns = array(
		'lang' => '#^([a-z]{3})$#',
		'date' => '#^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{4}-[0-9]{2}|[0-9]{4})$#',
		'page' => '#^p([0-9]+)$#',
		);

		$segment_count = count($uri_array);
		$segment_current = 1;
		
		foreach($uri_array as $segment)
		{
			$match = FALSE;
			foreach($patterns as $key => $pattern)
			{
				if(preg_match($pattern,$segment))
				{
					
					if($params[$key] == '')
					{
						if($key == 'page')
						{
							if($segment_current == $segment_count)
							{
								$params[$key] = str_replace('p','',$segment);
								$match = TRUE;
								break;
							
							}
						}
						else
						{
							$params[$key] = $segment;
							$match = TRUE;
							break;
						}
						
					}

				}
	
			}

			if(!$match)
			{
				$uri_segments[] = $segment;
			}
			$segment_current += 1;

		}

		$this->segments = $uri_segments;

		if($params['date'] != '')
		{
			$this->Content_model->date_filter = $params['date'];
		}

		if($params['lang'] == '')
		{
			$params['lang'] = $this->Site_model->site_default_lang;
		}
		$this->Site_model->temp_lang = $params['lang'];
		$this->Content_model->temp_lang = $params['lang'];

		$this->Site_model->lang_load();
		$this->_load_site_metadata();
		$this->_load_cms_labels();

		if($params['page'] != '')
		{
			$this->page = $params['page'];
			$this->Content_model->page = $params['page'];
		}
	}
	
	function _find_content()
	{

		$msg = '';
		
		$tree = array();

		foreach($this->segments as $index => $uri_segment)
		{

			$this->Content_model->temp_value = $uri_segment;
			$info = $this->Content_model->find_by_uri();
			
			if($info)
			{
				$this->Content_model->temp_id = $info[0]['content_id'];
				$tree[$index] = $info[0];
			}

		}

		$result_count = count($tree);
		$last_index = $result_count - 1;

		$this->temp_uri = '';
		if($result_count > 0)
		{
			
			// create parent hierarchy
			$parents = $tree;
			array_pop($parents);
			array_reverse($parents);
			$this->data['parents'] = $parents;

			// create breadcrumb links
			$this->breadcrumb_links = array(anchor($this->temp_uri,$this->cms_labels['home']));

			foreach($tree as $index => $vals)
			{
				if(is_array($vals))
				{
					$this->temp_uri .= '/'.$vals['uri_title'];
					if($index < $last_index)
					{
						$this->breadcrumb_links[] = anchor($this->temp_uri, $vals['title']);
						$this->parent_title = $vals['title'];
					}
					else
					{
						$this->breadcrumb_links[] = $vals['title'];
					}
										
					$this->Content_model->content_id = $vals['content_id'];
					$this->Content_model->user_id = $vals['user_id'];
					$this->Content_model->parent_id = $vals['parent_id'];
					$this->Content_model->type_id = $vals['type_id'];
					$this->Content_model->type_uri_name = $vals['type_uri_name'];
					$this->Content_model->date_created = $vals['date_created'];
					$this->Content_model->date_end_expiry = $vals['date_end_expiry'];
					$this->Content_model->date_updated = $vals['date_updated'];
					$this->Content_model->publish = $vals['publish'];
					$this->Content_model->title_id = $vals['title_id'];
					$this->Content_model->title = $vals['title'];
					$this->Content_model->subtitle = $vals['subtitle'];
					$this->Content_model->uri_title = $vals['uri_title'];
					$this->Content_model->keywords = $vals['keywords'];
					$this->Content_model->title_updated = $vals['title_updated'];
		
				}
			}

		}

	}

	function _prepare_view()
	{
		
		// initialize content array
		$content_vars = array();

		// load author user data

		$this->User_model->temp_id = $this->Content_model->user_id;
		$this->User_model->load_user();
		if($this->User_model->name_first != '')
		{
			$content_vars['author'] = array(
			'name_first' => $this->User_model->name_first,
			'name_last' => $this->User_model->name_last,
			'username' => $this->User_model->username,
			'email' => $this->User_model->email
			);
		}

		// load data for view
		$this->Content_model->temp_id = $this->Content_model->type_id;
		$this->Content_model->scope = 'content';
		$this->Content_model->load_type();
		$this->view = 'themes/'.$this->Site_model->theme.'/'.$this->Content_model->type_uri_name;


		$content_vars['content_id'] = $this->Content_model->content_id;

		$content_vars['type_uri_name'] = $this->Content_model->type_uri_name;
		$content_vars['title'] = $this->Content_model->title;
		if($this->Content_model->subtitle != '') {
			$content_vars['subtitle'] = $this->Content_model->subtitle;
		}

		if($this->Content_model->keywords != '') {
			$content_vars['keywords'] = $this->Content_model->keywords;
			$content_vars['keyword_links'] = $this->Content_model->keyword_links();
		}

		$content_vars['date_created'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone);
		$content_vars['date_created_W3C'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone, "Y-m-d");

		if($this->Content_model->date_end_expiry != '0000-00-00 00:00:00')
		{
			$content_vars['date_expiry'] = convert_time($this->Content_model->date_end_expiry, $this->Site_model->site_timezone,DATE_RFC1123);
			$content_vars['date_expiry_RFC850'] = convert_time($this->Content_model->date_end_expiry, 'UTC',DATE_RFC850);
		}

		$this->Content_model->temp_id = $this->Content_model->type_id;
		$this->Content_model->scope = 'types';
		$this->Content_model->list_fields_related();

		$this->Content_model->scope = 'content';
		$this->Content_model->temp_id = $this->Content_model->content_id;
		$this->Content_model->load_metadata();

		$c_meta = $this->Content_model->metadata;
		
		foreach($c_meta as $id => $field) {
			$content_vars[$field['field_ref_name']] = $field['value'];
			
			if($field['date_updated'] > $this->Content_model->date_updated) {
				$this->Content_model->date_updated = $field['date_updated'];
			}

			if($field['field_input_type'] == 'directory') {
				
				$dir = ROOT_REL.MEDIA_PATH_ORIG.$field['value'];

				$this->Media_model->temp_lang = $this->Content_model->temp_lang;
				$this->Media_model->lang_load();
				$content_vars['dir_listing'] = $this->Media_model->list_media_recursive();

//				$this->Media_model->dir_current = ROOT_REL.MEDIA_PATH_ORIG.$field['value'];
//				$this->Media_model->list_files_unique();
//				$content_vars['dir_listing'] = $this->Media_model->file_list;
			}

		}

		$content_vars['date_updated'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone);
		$content_vars['date_updated_W3C'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone, "Y-m-d");

		$this->data['content'] = $content_vars;


		// list attachments
		$this->_attachment_info();

		// build menu array

		if($this->Content_model->parent_id == 0)
		{
			$menu_title = $this->Site_model->title;
			$menu_parent = 0;
		}
		else
		{
			$menu_title = $this->parent_title;
			$menu_parent = $this->Content_model->parent_id;
		}

		$menu = $this->Content_model->get_descendant_content($menu_parent);

		$this->data['menu'] = $menu;
		
		// build descendant array
		$this->data['children'] = $this->Content_model->get_descendant_content($content_vars['content_id']);

		// see if there are other languages for this content

		$other_langs = $this->Content_model->get_other_lang_ids();
		if($other_langs)
		{
			$this->data['other_langs'] = $other_langs;
		}

	}

	function _home()
	{
		$this->view = 'themes/'.$this->Site_model->theme.'/home';

		$menu = $this->Content_model->get_descendant_content();

		$this->data['menu'] = $menu;
		$this->data['breadcrumb_links'] = array($this->cms_labels['home']);
		
		// recently added content
		$this->Content_model->limit = 5;
		$this->data['recent'] = $this->_recent();
	}

	function _recent($parent=NULL)
	{

		if($this->Site_model->home_content_type > 0)
		{
			$this->Content_model->type_id = $this->Site_model->home_content_type;
		}

		if(isset($parent))
		{
			$this->Content_model->parent_id = $parent;
		}
		$this->Content_model->list_content(TRUE);

		$recent = $this->Content_model->content_list;
		$recent_entries = array();

		
		if(count($recent) > 0)
		{
			foreach($recent as $index => $post)
			{
				$entry = array(
				'content_id' => $post['content_id'],
				'user_id' => $post['user_id'],
				'parent_id' => $post['parent_id'],
				'type_id' => $post['type_id'],
				'type_uri_name' => $post['type_uri_name'],
				'type_label' => $post['type_label'],
				'title_id' => $post['title_id'],
				'title' => $post['title'],
				'uri_title' => $post['uri_title'],
				'subtitle' => $post['subtitle'],
				'keywords' => $post['keywords'],
				'lang_ISO_639_3' => $post['lang_ISO_639_3']
				);

				// get author info
				$this->User_model->temp_id = $post['user_id'];
				if($this->User_model->load_user())
				{
					$entry['author'] = array(
					'name_first' => $this->User_model->name_first,
					'name_last' => $this->User_model->name_last,
					'username' => $this->User_model->username,
					'email' => $this->User_model->email
					);
				}

				$entry['date_created'] = convert_time($post['date_created'], $this->Site_model->site_timezone);
	
				$date_updated = $post['date_updated'];

				if($post['date_end_expiry'] != '0000-00-00 00:00:00')
				{
					$entry['date_end_expiry'] = convert_time($post['date_end_expiry'], $this->Site_model->site_timezone);
				}
	
				foreach($post['metadata'] as $id => $field) {
				
					if(array_key_exists('field_ref_name',$field))
					{
						$tmp_field_name = $field['field_ref_name'];
					}
					else
					{
						$this->Content_model->temp_id = $id;
						$this->Content_model->load_field();
						$tmp_field_name = $this->Content_model->field_ref_name;;
					}
		
					$entry[$tmp_field_name] = encode_entities($field['value']);
					
					if($field['date_updated'] > $date_updated) {
						$date_updated = $field['date_updated'];
					}
		
				}
	
				$entry['date_updated'] = convert_time($date_updated, $this->Site_model->site_timezone);
				
				// keyword links
				if($post['keywords']  != '') {
					$this->Content_model->keywords = $post['keywords'];
					$entry['keyword_links'] = $this->Content_model->keyword_links();
				}

				// get uri of content
				$this->Content_model->scope = 'content';
				$this->Content_model->temp_id = $post['content_id'];
				$this->Content_model->temp_lang = $post['lang_ISO_639_3'];
				$entry['uri'] = $this->Content_model->generate_uri();
				
				$recent_entries[] = $entry;
				$this->Content_model->reset_vars();
			
			}
		}

		return $recent_entries;

	}
	
	function _attachment_info()
	{

		// load mime type labels
		$this->lang->load('mime', $this->Content_model->lang_CI_Name);
		$this->data['mime_labels'] = $this->lang->line('mime');

		$this->Content_model->list_attachments();
		if(count($this->Content_model->attachments) > 0)
		{
			$attachments = array();

			foreach($this->Content_model->attachments as $media)
			{
				$this->Media_model->reset_media_vars();
				$tmp_array = $media;
				$this->Media_model->temp_id = $media['media_id'];
				$this->Media_model->load_media();
				$tmp_array['file_uri'] = $this->Media_model->file_uri;
				$tmp_array['external'] = $this->Media_model->external;
				$tmp_array['mime_type'] = $this->Media_model->mime_type;
				$tmp_array['mime_cat'] = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->Media_model->mime_type);
				$tmp_array['ext'] = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->Media_model->file_uri));
				$tmp_array['filename'] = basename($this->Media_model->file_uri);

				$tmp_array['date_created'] = $this->Media_model->date_created;
				$tmp_array['publish'] = $this->Media_model->publish;
				$this->Media_model->temp_lang = $this->Content_model->temp_lang;
				$this->Media_model->load_title();
				$tmp_array['title'] = $this->Media_model->title;
				$tmp_array['uri_title'] = $this->Media_model->uri_title;
				$tmp_array['subtitle'] = $this->Media_model->subtitle;
				$tmp_array['keywords'] = $this->Media_model->keywords;

				$attachments[] = $tmp_array;
				unset($tmp_array);
			}
			$this->data['attachments'] = $attachments;
		}
	}

}
?>
