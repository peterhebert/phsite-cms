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
		
		if($this->Content_model->content_id != '')
		{
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

		$this->segments = $this->uri->segment_array();
		$seg_count = count($this->segments);
		
		$params = array();
		$uri_patterns = array(
		'date' => "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/",
		'month' => "/^([0-9]{4})-([0-9]{2})$/",
		'year' => "/^([0-9]{4})$/",
		'page' => "/^([0-9]+)$/",
		);
		
		if($seg_count > 0)
		{
			$current_path = 1;
			
			foreach($this->segments as $segment)
			{
				$match = FALSE;

				foreach($uri_patterns as $key => $pattern)
				{
					// test for lang match
					if($current_path == 1 && preg_match("/^([a-z]{3})$/", $segment) )
					{
						$params['lang'] = $segment;
						$match = TRUE;
					}
					
					// test for pattern match
					if (preg_match($pattern, $segment) && $match === FALSE && !array_key_exists($key,$params))
					{
						$params[$key] = $segment;
						$match = TRUE;
					}
					
				}

				if ($match === FALSE)
				{
					$params['titles'][] = $segment;
				}
				
				$current_path = $current_path + 1;
			}
			
		}
		$this->data['params'] = $params;

		$this->segments = array();

		if(array_key_exists('lang',$params))
		{
			$this->Content_model->temp_lang = $params['lang'];
			$this->Site_model->temp_lang = $params['lang'];
		} else {
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}

		$this->_load_cms_labels();
		$this->_load_site_metadata();

		if(array_key_exists('year',$params))
		{
			$this->Content_model->date_filter = $params['year'];
		}
		if(array_key_exists('month',$params))
		{
			$this->Content_model->date_filter = $params['month'];
		}
		if(array_key_exists('date',$params))
		{
			$this->Content_model->date_filter = $params['date'];
		}
		if( array_key_exists('page',$params))
		{
			$this->Content_model->page = $params['page'];
		}
		else
		{
			$this->Content_model->page = 1;
		}
		
		if( array_key_exists('titles',$params))
		{
			$this->segments = $params['titles'];
		}
		$seg_count = count($this->segments);
		$msg = '';
		
		$tree = array();

		if($seg_count > 0)
		{
		
			foreach($this->segments as $index => $uri_segment)
			{

				$this->Content_model->temp_value = $uri_segment;
				$info = $this->Content_model->find_by_uri();
				if($info)
				{
					$this->Content_model->temp_id = $info[0]['content_id'];
					$tree[$index] = $info[0];
				}
				else
				{
					$tree[$index] = NULL;
				}

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
					$this->breadcrumb_links[] = anchor($this->temp_uri, $info[0]['title']);
					
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

		$menu = '';
		// menu options by content type
		switch ($this->Content_model->type_uri_name)
		{

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

	}

	function _home()
	{
		$this->view = 'themes/'.$this->Site_model->theme.'/home';

		$menu = $this->Content_model->site_map();

		$this->data['menu'] = $menu;
		$this->data['breadcrumb'] = $this->cms_labels['home'];
		
		$this->data['recent'] = $this->_blog();

	}

	function _blog($parent=NULL,$mode='excerpt')
	{
		$temp_view = 'themes/'.$this->Site_model->theme.'/embed_blog_entry';

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
				$blog_data['mode'] = $mode;
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


}
?>
