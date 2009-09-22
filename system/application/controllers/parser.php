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
		$this->load->model('User_model');
		$this->load->helper('url');

		$this->_load_site_metadata();
	}
	
    function index()
    {
		$this->Content_model->link_mode = 0;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;

		// check if language parameter is present
		$this->segments = $this->uri->segment_array();

		if(count($this->segments) > 0)
		{
			$this->Content_model->temp_lang = $this->segments[1];
			if($this->Content_model->lang_load())
			{
				$remove_me = array_shift($this->segments);
			} else
			{
				$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
				$this->Content_model->lang_load();
			}
		}

		$this->temp_uri = implode('/',$this->segments);

		if($this->temp_uri != '')
		{
			$this->_process_uri();
		}
		else
		{
			$this->_home();
		}

		$this->data['html_header'] = $this->load->view('embed_html_header',$this->data,true);
		

		$this->load->view($this->view,$this->data);
    }

	function _process_uri()
	{
		// check uri for page variable
		$last_segment = count($this->segments) - 1;
		$params = $this->uri->uri_to_assoc($last_segment);
		if( array_key_exists('p',$params) && is_numeric($params['p']) )
		{
			$this->page = array_pop($this->segments);
			$remove_me  = array_pop($this->segments);
		}
		else
		{
			$this->page = 1;
		}

		$this->_get_content_by_uri();

		$this->data['breadcrumb'] = implode(' : ', $this->breadcrumb_links);
		$this->data['content_id'] = $this->Content_model->content_id;
		$this->data['user_id'] = $this->Content_model->user_id;
		$this->data['parent_id'] = $this->Content_model->parent_id;
		$this->data['type_id'] = $this->Content_model->type_id;
		$this->Content_model->temp_id = $this->Content_model->type_id;
	
		$this->Content_model->scope = 'content';
		$this->Content_model->load_type();
		$this->view = 'themes/'.$this->Site_model->theme.'/'.$this->Content_model->type_uri_name;

		$this->data['type_uri_name'] = $this->Content_model->type_uri_name;
		$this->data['title'] = $this->Content_model->title;
		$this->data['subtitle'] = $this->Content_model->subtitle;

		$this->data['content_date_created'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone);
		$this->data['content_date_created_W3C'] = convert_time($this->Content_model->date_created, $this->Site_model->site_timezone, "Y-m-d");

		if($this->Content_model->date_end_expiry != '0000-00-00 00:00:00')
		{
			$this->data['content_date_expiry'] = convert_time($this->Content_model->date_end_expiry, $this->Site_model->site_timezone,DATE_RFC1123);
			$this->data['content_date_expiry_RFC850'] = convert_time($this->Content_model->date_end_expiry, 'UTC',DATE_RFC850);
		}

		foreach($this->Content_model->metadata as $id => $field) {
		
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

			$this->data[$tmp_field_label] = $field['value'];
			
			if($tmp_field_name == 'keywords') {
				$this->data['content_keyword_links'] = $this->Content_model->keyword_links($field['value']);
			}

			if($field['date_updated'] > $this->Content_model->date_updated) {
				$this->Content_model->date_updated = $field['date_updated'];
			}

		}

		$this->data['content_date_updated'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone);
		$this->data['content_date_updated_W3C'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone, "Y-m-d");

		$menu = '';
		// menu options by content type
		switch ($this->Content_model->type_uri_name)
		{
			case '':
				break;

			case 'blog_entry':
				$entries = $this->_blog_menu($this->Content_model->parent_id);
				if(count($entries) > 0)
				{
					$menu .= "<h3>";
					if($this->Content_model->parent_id > 0)
					{
						$menu .=  sprintf($this->cms_labels['recently_posted_to'],$this->Content_model->parent_title);
					}
					else
					{
						$menu .= $this->cms_labels['recent_content'];
					}

					$menu .= "</h3>\r";
					$menu .= ul($entries);
				}
				break;
			default:
				$menu = "<h3>";
				if($this->Content_model->parent_id == 0)
				{
					$menu .= $this->Site_model->title;
				}
				else
				{
					$menu .= $this->parent_title;
				}
				$menu .= "</h3>\r";
				$this->Content_model->temp_id = $this->Content_model->parent_id;
				$this->Content_model->current_level = 1;
				$this->Content_model->levels = 2;
				$menu .= $this->Content_model->site_map();
		}
		$this->data['menu'] = $menu;

		
		// children options by content type
		switch ($this->Content_model->type_uri_name) {
			case 'blog':
				$this->data['blog'] = $this->_blog($this->Content_model->content_id);

				break;
			case 'static_page':
				break;
			default:
		}
		$this->data['view'] = $this->view;

	}
	
	function _home()
	{
		$this->view = 'themes/'.$this->Site_model->theme.'/home';
		
		if($this->Content_model->lang_ISO_639_3 == '')
		{
			$this->Content_model->temp_lang = $this->Site_model->temp_lang;
		}


		$menu = $this->Content_model->site_map();

		$this->data['menu'] = $menu;
		$this->data['breadcrumb'] = $this->cms_labels['home'];
		
		$this->data['recent'] = $this->_blog();

	}

	function _blog($parent=NULL)
	{
		$temp_view = 'themes/'.$this->Site_model->theme.'/embed_blog_excerpt';

		$this->Content_model->type_id = 2;

		if(isset($parent))
		{
			$this->Content_model->list_content($parent,TRUE,TRUE);
		}
		else
		{
			$this->Content_model->list_content(NULL,TRUE,TRUE);
		}

		$recent = $this->Content_model->content_list;
		$blog = '';
		
		if(count($recent) > 0)
		{
			foreach($recent as $index => $post)
			{
				$blog_data = array();
				$blog_data['metadata'] = $post['metadata'];
				
				$this->User_model->temp_id = $post['user_id'];
				$this->User_model->load_user();
				$user_name_formatted = $this->User_model->name_first.' '.$this->User_model->name_last;
				$date_created = convert_time($post['date_created'], $this->Site_model->site_timezone);
	
				$blog_data['byline'] = sprintf($this->cms_labels['posted_by'],$date_created,$user_name_formatted);
	
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
					
					if($tmp_field_name == 'keywords') {
						$blog_data['content_keyword_links'] = $this->cms_labels['keywords'].' : '.$this->Content_model->keyword_links($field['value']);
					}
					
					if($field['date_updated'] > $date_updated) {
						$date_updated = $field['date_updated'];
					}
		
				}
	
				$blog_data['date_updated'] = convert_time($date_updated, $this->Site_model->site_timezone);
				
				// get uri to full post
				$this->Content_model->scope = 'content';
				$this->Content_model->temp_id = $post['content_id'];
				$this->Content_model->temp_lang = $post['lang_ISO_639_3'];
				$permalink = $this->Content_model->generate_uri();
				$pl_title = sprintf($this->cms_labels['permalink_title'],$post['title']);
				
				$blog_data['permalink'] = anchor($permalink, $this->cms_labels['read_more'], array('title' => $pl_title));
				$blog_data['title'] = anchor($permalink, $post['title'], array('title' => $pl_title));
				
				$blog .= $this->load->view($temp_view,$blog_data,true);
			
			}
		}
		else
		{
			$blog = $this->cms_labels['no_content'];
		}

		return $blog;

	}
	
	function _blog_menu($parent=NULL)
	{
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
				if($post['content_id'] != $this->Content_model->content_id)
				{
					$blog_data = array();
					$blog_data['title'] = $post['title'];
	
					// get uri to full post
					$this->Content_model->scope = 'content';
					$this->Content_model->temp_id = $post['content_id'];
					$this->Content_model->temp_lang = $post['lang_ISO_639_3'];
					$permalink = $this->Content_model->generate_uri();
					$pl_title = sprintf($this->cms_labels['permalink_title'],$post['title']);
					
					$blog_menu[] = anchor($permalink, $post['title'], array('title' => $pl_title));
				}
				
			
			}
		}

		return $blog_menu;

	}
	

	function _get_content_by_uri()
	{
		$this->temp_uri = '';
		$this->breadcrumb_links = array(anchor($this->temp_uri,$this->cms_labels['home']));
		$current_path = 1;

		$pathcount = count($this->segments);

		$this->temp_id = '';
		foreach ($this->segments as $uri_title) {
		
			$this->Content_model->temp_value = $uri_title;
			
			$info = $this->Content_model->find_by_uri();
			
			if($info) {
			
				$info_count = count($info);
				$parent_index = $info_count - 1;

				if($info_count == 1)
				{
					$this->Content_model->temp_id = $info[0]['content_id'];
				}

				$this->temp_uri .= '/'.$uri_title;
				
				if($this->Content_model->temp_lang != '')
				{
					if(count($info) == 1)
					{
						$this->Content_model->temp_lang = $info[0]['lang_ISO_639_3'];
					}
				}

				if ($current_path < $pathcount) {
	
					if(count($info) == 1)
					{
						$this->Content_model->parent_id = $info[0]['content_id'];
						$this->breadcrumb_links[] = anchor($this->temp_uri, $info[0]['title']);
						$this->parent_title = $info[0]['title'];
					}
					
				} else {

					if(count($info) == 1)
					{
						$this->Content_model->content_id = $info[0]['content_id'];
						$this->Content_model->user_id = $info[0]['user_id'];
						$this->Content_model->parent_id = $info[0]['parent_id'];
						$this->Content_model->type_id = $info[0]['type_id'];
						$this->Content_model->type_uri_name = $info[0]['type_uri_name'];
						$this->Content_model->date_created = $info[0]['date_created'];
						$this->Content_model->date_end_expiry = $info[0]['date_end_expiry'];
						$this->Content_model->date_updated = $info[0]['date_updated'];
						$this->Content_model->publish = $info[0]['publish'];
						$this->Content_model->title_id = $info[0]['title_id'];
						$this->Content_model->title = $info[0]['title'];
						$this->Content_model->subtitle = $info[0]['subtitle'];
						$this->Content_model->uri_title = $info[0]['uri_title'];
						$this->Content_model->title_updated = $info[0]['title_updated'];

						$this->breadcrumb_links[] = $info[0]['title'];
					}
				}
				
				if(count($info) > 1)
				{
					$this->Content_model->parent_id = $info[$parent_index]['content_id'];
					$this->parent_title = $info[$parent_index]['title'];
				}

			}
			$current_path = $current_path + 1;
		}


		if($this->Content_model->content_id != '')
		{
			$this->Content_model->temp_id = $this->Content_model->type_id;
			$this->Content_model->scope = 'types';
			$this->Content_model->list_fields_related();
	
			$this->Content_model->scope = 'content';
			$this->Content_model->temp_id = $this->Content_model->content_id;
			$this->Content_model->load_metadata();
		}
	
	}

}
?>