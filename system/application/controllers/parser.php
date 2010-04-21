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
		//$this->_load_site_metadata();
	}
	
    function index()
    {
		$this->view = 'test';
		$this->Content_model->link_mode = 0;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		// get all parameters from uri
		$this->_parse_uri();
		
		if(count($this->segments) > 0)
		{
			$this->_find_content();
			$this->_prepare_view();
		}
		else
		{
			$this->_home();
		}

		if(is_array($this->breadcrumb_links) && count($this->breadcrumb_links) > 0)
		{
			$this->data['breadcrumb_links'] = $this->breadcrumb_links;
		}
		
		$this->data['html_header'] = $this->load->view('embed_html_header',$this->data,true);
		
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
		if(preg_match('|^([a-z]{3})$|',$params['lang']))
		{
			$this->Site_model->temp_lang = $params['lang'];
			$this->Content_model->temp_lang = $params['lang'];
		}
		else
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
		}
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
		// see if there are other languages for this content
		$other_langs = $this->Content_model->get_other_lang_ids();
		if($other_langs)
		{
			$this->data['other_langs'] = $other_langs;
		}
		// load author user data

		$this->User_model->temp_id = $this->Content_model->user_id;
		$this->User_model->load_user();
		if($this->User_model->name_first != '')
		{
			$this->data['author'] = array(
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

		$this->data['type_uri_name'] = $this->Content_model->type_uri_name;
		$this->data['title'] = $this->Content_model->title;
		if($this->Content_model->subtitle != '') {
			$this->data['subtitle'] = $this->Content_model->subtitle;
		}

		if($this->Content_model->keywords != '') {
			$this->data['keywords'] = $this->Content_model->keywords;
			$this->data['content_keyword_links'] = $this->Content_model->keyword_links();
		}

		$this->data['content_date_created'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone);
		$this->data['content_date_created_W3C'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone, "Y-m-d");

		if($this->Content_model->date_end_expiry != '0000-00-00 00:00:00')
		{
			$this->data['content_date_expiry'] = convert_time($this->Content_model->date_end_expiry, $this->Site_model->site_timezone,DATE_RFC1123);
			$this->data['content_date_expiry_RFC850'] = convert_time($this->Content_model->date_end_expiry, 'UTC',DATE_RFC850);
		}

		$this->Content_model->temp_id = $this->Content_model->type_id;
		$this->Content_model->scope = 'types';
		$this->Content_model->list_fields_related();

		$this->Content_model->scope = 'content';
		$this->Content_model->temp_id = $this->Content_model->content_id;
		$this->Content_model->load_metadata();

		$c_meta = $this->Content_model->metadata;
		
		foreach($c_meta as $id => $field) {
			$tmp_field_label = 'content_'.$field['field_ref_name'];

			$this->data["$tmp_field_label"] = $field['value'];
			
			if($field['date_updated'] > $this->Content_model->date_updated) {
				$this->Content_model->date_updated = $field['date_updated'];
			}

		}

		$this->data['content_date_updated'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone);
		$this->data['content_date_updated_W3C'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone, "Y-m-d");

		// list attachments
		$this->_attachment_info();

		// menu options by content type
		$menu = '';
		$menu_title = 'menu';
		switch ($this->Content_model->type_uri_name)
		{

			case 'blog_entry':
				$entries = $this->_blog_menu($this->Content_model->parent_id);

				if(count($entries) > 0)
				{
					if($this->Content_model->parent_id > 0 && $this->parent_title != '')
					{
						$menu_title = sprintf($this->cms_labels['recently_posted_to'],$this->parent_title);
					}
					else
					{
						$menu_title = $this->cms_labels['recent_content'];
					}

					$tmp_view = 'themes/'.$this->Site_model->theme.'/embed_menu';
					$tmp_blog = array('entries' => $entries,'cms_labels' => $this->cms_labels);
					
					$menu = $this->load->view($tmp_view,$tmp_blog,true);
				}
				else
				{
					$menu_title = $this->Site_model->title;
	
					$this->Content_model->temp_id = 0;
					$this->Content_model->current_level = 1;
					$this->Content_model->levels = 1;
					$menu = $this->Content_model->site_map();
				}

				break;
			default:
				if($this->Content_model->parent_id == 0)
				{
					$menu_title = $this->Site_model->title;
				}
				else
				{
					$menu_title = $this->parent_title;
				}

				$this->Content_model->temp_id = $this->Content_model->parent_id;
				$this->Content_model->current_level = 1;
				$this->Content_model->levels = 2;
				$menu = $this->Content_model->site_map();
		}
		$this->data['menu_title'] = $menu_title;
		$this->data['menu'] = $menu;

		
		// children options by content type
		switch ($this->Content_model->type_uri_name) {
			case 'blog':
				$tmp_view = 'themes/'.$this->Site_model->theme.'/embed_blog';
				$blog_entries = $this->_blog($this->Content_model->content_id);
				$tmp_blog = array('blog' => $blog_entries,'cms_labels' => $this->cms_labels);
				$this->data['blog'] = $this->load->view($tmp_view,$tmp_blog,true);
				break;
			case 'static_page':
				break;
			default:
		}

	}

	function _home()
	{
		$this->view = 'themes/'.$this->Site_model->theme.'/home';

		$menu = $this->Content_model->site_map();

		$this->data['menu'] = $menu;
		$this->data['breadcrumb_links'] = array($this->cms_labels['home']);
		
		// recent posts
		$tmp_view = 'themes/'.$this->Site_model->theme.'/embed_blog';
		$this->Content_model->limit = 5;
		$blog_entries = $this->_blog();
		$tmp_blog = array('blog' => $blog_entries,'cms_labels' => $this->cms_labels);
		$this->data['recent'] = $this->load->view($tmp_view,$tmp_blog,true);

	}

	function _blog($parent=NULL,$mode='excerpt')
	{

		$this->Content_model->type_id = 2;

		if(isset($parent))
		{
			$this->Content_model->parent_id = $parent;
		}
		$this->Content_model->list_content(TRUE);

		$recent = $this->Content_model->content_list;
		$blog = array();

		
		if(count($recent) > 0)
		{
			foreach($recent as $index => $post)
			{
				$blog_data = array();
				$blog_data['mode'] = $mode;
				$blog_data['title'] = $post['title'];
				$blog_data['subtitle'] = $post['subtitle'];
				
				// get author info
				$this->User_model->temp_id = $post['user_id'];
				if($this->User_model->load_user())
				{
					$blog_data['author'] = array(
					'name_first' => $this->User_model->name_first,
					'name_last' => $this->User_model->name_last,
					'username' => $this->User_model->username,
					'email' => $this->User_model->email
					);
				}

				$blog_data['date_created'] = convert_time($post['date_created'], $this->Site_model->site_timezone);
	
				$date_updated = $post['date_updated'];
	
				foreach($post['metadata'] as $id => $field) {
				
					if(array_key_exists('field_ref_name',$field))
					{
						$tmp_field_label = 'content_'.$field['field_ref_name'];
						$tmp_field_name = $field['field_ref_name'];
					}
					else
					{
						$this->Content_model->temp_id = $id;
						$this->Content_model->load_field();
						$tmp_field_label = 'content_'.$this->Content_model->field_ref_name;;
						$tmp_field_name = $this->Content_model->field_ref_name;;
					}
		
					$blog_data[$tmp_field_label] = $field['value'];
					
					if($field['date_updated'] > $date_updated) {
						$date_updated = $field['date_updated'];
					}
		
				}
	
				$blog_data['date_updated'] = convert_time($date_updated, $this->Site_model->site_timezone);
				
				// keyword links
				if($post['keywords']  != '') {
					$this->Content_model->keywords = $post['keywords'];
					$blog_data['content_keyword_links'] = $this->Content_model->keyword_links();
				}

				// get uri to full post
				$this->Content_model->scope = 'content';
				$this->Content_model->temp_id = $post['content_id'];
				$this->Content_model->temp_lang = $post['lang_ISO_639_3'];
				$blog_data['permalink'] = $this->Content_model->generate_uri();
				
				$blog[] = $blog_data;
			
			}
		}

		return $blog;

	}
	
	function _blog_menu($parent=NULL)
	{
		$my_id = $this->Content_model->content_id;		
		$this->Content_model->type_id = 2;
		if(isset($parent))
		{
			$this->Content_model->list_content($parent,TRUE);
		}
		else
		{
			$this->Content_model->list_content(NULL,TRUE);
		}
		
		$recent = $this->Content_model->content_list;

		$blog_menu = array();

		if(count($recent) > 0)
		{
			foreach($recent as $index => $post)
			{

				if($post['content_id'] != $my_id)
				{
					$tmp_entry = $post;
	
					// get uri to full post
					$this->Content_model->scope = 'content';
					$this->Content_model->temp_id = $post['content_id'];
					$this->Content_model->temp_lang = $post['lang_ISO_639_3'];
					$tmp_entry['permalink'] = $this->Content_model->generate_uri();
					
					$blog_menu[] = $tmp_entry;
				}
			}
		}
		return $blog_menu;
	}

	function _attachment_info()
	{
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
