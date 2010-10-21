<?php

class Parser extends MY_Controller {

	var $temp_uri;
	var $view;
	var $segments = array();
	var $segment_count = 0, $result_count = 0;

	var $breadcrumb_links = array(), $matches = array();
	var $ancestry = array();
	var $page = 1;
	var $media_view = FALSE;
	var $home = FALSE;

	var $parent_title;
	
	function Parser()
	{
		parent::MY_Controller();

		$this->load->model('Content_model');
		$this->load->model('File_model');
		$this->load->model('User_model');
		$this->load->helper(array('url', 'image'));
		$this->load->library('pagination');

		$this->data['mime_types'] = $this->File_model->mime_types;
		$this->data['image_types'] = $this->File_model->image_types;
		$this->data['playable_types'] = $this->File_model->playable_types;

		$this->Content_model->list_langs();
	}
	
    function index()
    {
		$this->data['parents'] = array();

		// get all parameters from uri
		$this->_parse_uri();

		$this->Content_model->link_mode = 0;
		$this->Content_model->lang_mode = $this->Content_model->lang_count;


		switch($this->segment_count)
		{
		case 0:
			// no segments - do home page
			$this->_home();
			break;
		case 1:
			$this->Content_model->temp_value = $this->segments[0];
			$this->Content_model->temp_lang = $this->Content_model->lang_ISO_639_3;
			$this->Content_model->temp_id = '';
			$results = $this->Content_model->find_by_uri();

			if($results)
			{
	
				$this->Content_model->content_id = $results[0]['content_id'];
				$this->Content_model->user_id = $results[0]['user_id'];
				$this->Content_model->parent_id = $results[0]['parent_id'];
				$this->Content_model->type_id = $results[0]['type_id'];
				$this->Content_model->type_uri_name = $results[0]['type_uri_name'];
				$this->Content_model->date_created = $results[0]['date_created'];
				$this->Content_model->date_end_expiry = $results[0]['date_end_expiry'];
				$this->Content_model->date_updated = $results[0]['date_updated'];
				$this->Content_model->publish = $results[0]['publish'];
				$this->Content_model->title_id = $results[0]['title_id'];
				$this->Content_model->title = $results[0]['title'];
				$this->Content_model->subtitle = $results[0]['subtitle'];
				$this->Content_model->uri_title = $results[0]['uri_title'];
				$this->Content_model->keywords = $results[0]['keywords'];
			
				// prepare breadcrumbs
				$this->temp_uri .= '/'.$results[0]['uri_title'];
				$this->breadcrumb_links[] = anchor('', $this->cms_labels['home']);
				$this->breadcrumb_links[] = anchor($this->temp_uri, $results[0]['title']);

				// load metadata
				$this->Content_model->temp_id = $results[0]['type_id'];
				$this->Content_model->scope = 'types';
				
				$this->Content_model->list_fields_related();
				$this->Content_model->scope = 'content';
				$this->Content_model->temp_id = $this->Content_model->content_id;
				$this->Content_model->load_metadata();

				$this->_prepare_view();
			}
			else
			{
				$this->output->set_status_header('404');
				$this->data['header'] = $this->cms_labels['server_error_404_header'];
				$html = "<p>".sprintf($this->cms_labels['server_error_404_message'],implode('/',$this->segments))."</p>";
				$html .= "<p>".$this->cms_labels['server_error_404_suggest']."</p>";
				$home_uri = '';
				if($this->Content_model->lang_ISO_639_3 != $this->Site_model->site_default_lang)
				{
					$home_uri .= $this->Content_model->lang_ISO_639_3;
				}
				$html .= '<p>'.anchor($home_uri,$this->cms_labels['home'])."</p>";

				// get top-level menu
				$this->Content_model->reset_vars();
				$this->Content_model->sort_order = array('order' => 'asc', 'title' => 'asc');
				$top_menu = $this->Content_model->get_descendant_content(0,FALSE);
				$this->data['top_menu'] = $top_menu;

				$this->data['message'] = $html;
				$this->view = 'themes/'.$this->Site_model->theme.'/error_404';
			}


			break;
		default:
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

			if($this->_find_content())
			{
				$this->_prepare_view();
			}
			else
			{
				$this->output->set_status_header('404');
				$this->data['header'] = $this->cms_labels['server_error_404_header'];

				$html = "<p>".sprintf($this->cms_labels['server_error_404_message'],implode('/',$this->segments))."</p>";
				if(count($this->matches) > 0)
				{
					$html .= "<p>{$this->cms_labels['server_error_404_matches']}</p>\r";
					$html .= ul($this->matches);
				}
				$html .= "<p>".$this->cms_labels['server_error_404_suggest']."</p>";
				$home_uri = '';
				if($this->Content_model->lang_ISO_639_3 != $this->Site_model->site_default_lang)
				{
					$home_uri .= $this->Content_model->lang_ISO_639_3;
				}
				$html .= '<p>'.anchor($home_uri,$this->cms_labels['home'])."</p>";
				
				$this->data['message'] = $html;

				$this->view = 'themes/'.$this->Site_model->theme.'/error_404';
			}

			break;
		}

		$this->data['resource_uri'] = $this->temp_uri;
		$zz = explode('/',trim($this->temp_uri,'/'));
		array_pop($zz);
		array_pop($zz);
		$this->data['parent_uri'] = implode('/',$zz);
		$this->data['parent_title'] = $this->parent_title;

		if(is_array($this->breadcrumb_links) && count($this->breadcrumb_links) > 0)
		{
			if($this->media_view)
			{
				$tmp_parent = array_pop($this->breadcrumb_links);
				$this->breadcrumb_links[] = anchor($this->temp_uri,$tmp_parent);
				$this->breadcrumb_links[] = $this->cms_labels['gallery'];
			}
			$this->data['breadcrumb_links'] = $this->breadcrumb_links;
		}
		
		$header_view = 'themes/'.$this->Site_model->theme.'/inc_header';
		if($this->media_view)
		{
			$header_view .= '_media';
		}

		$header_view_full = APPPATH.'views/'.$header_view.'.php';
		if(file_exists($header_view_full))
		{
			$this->data['html_header'] = $this->load->view($header_view,$this->data,TRUE);
		}

		$view_full = APPPATH.'views/'.$this->view.'.php';
		if(!file_exists($view_full))
		{
			$this->view = 'display';
		}
				
		$this->load->view($this->view,$this->data);		

    }

	function _parse_uri()
	{
		
		$uri_segments = array();
		$uri_array = $this->uri->segment_array();

		$params = array('page' => '','lang' => '','date' => '','media'  => '');
		$patterns = array(
		'lang' => '#^([a-z]{3})$#',
		'date' => '#^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{4}-[0-9]{2}|[0-9]{4})$#',
		'page' => '#^p([0-9]+)$#',
		'media' => '#^view$#',
		);

		$segment_count = count($uri_array);
		$segment_current = 1;
		$tmp_segments = $uri_array;
		
		foreach($uri_array as $index => $segment)
		{
			$match = FALSE;
			foreach($patterns as $key => $pattern)
			{
				if(preg_match($pattern,$segment) && $params[$key] == '')
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
					else if($key == 'media')
					{
						$media_segments = array_slice($tmp_segments, $index);
						$params[$key] = '/'.implode('/',$media_segments);
						$match = TRUE;
						break;
						
					}
					else if($key == 'lang')
					{
						$this->Content_model->temp_lang = $segment;
						if($this->Content_model->lang_load())
						{
							$params[$key] = $segment;
							$match = TRUE;
						}
						else
						{
							$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
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

			if(!$match && $params['media'] == '')
			{
				$uri_segments[] = $segment;
			}
			$segment_current += 1;

		}

		$this->segments = $uri_segments;
		$this->segment_count = count($this->segments);

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
		$this->Content_model->lang_load();

		$this->_load_cms_labels();
		$this->_load_site_metadata();

		if($params['page'] != '')
		{
			$this->page = $params['page'];
			$this->Content_model->page = $params['page'];
		}
		
		if($params['media'] != '')
		{
			$this->File_model->temp_lang = $this->Content_model->lang_ISO_639_3;
			$this->File_model->file_uri = $params['media'];
			$this->media_view = $this->File_model->find_media_by_uri(TRUE);
		}

	}
	
	function _find_content()
	{

		$msg = '';
		
		$tree = array();


		foreach($this->segments as $index => $uri_segment)
		{
			$this->Content_model->reset_vars();
			$this->Content_model->temp_value = $uri_segment;
			$info = $this->Content_model->find_by_uri();
			
			if($info)
			{
				$this->Content_model->temp_id = $info[0]['content_id'];
				$tree[$index] = $info[0];
			}

		}

		$this->result_count = count($tree);

		$last_index = $this->result_count - 1;
		$this->temp_uri = '';
		
		if($this->result_count > 0 && $this->result_count == $this->segment_count)
		{
			// create parent hierarchy
			$parents = $tree;

			// create breadcrumb links
			$this->breadcrumb_links = array(anchor($this->temp_uri,$this->cms_labels['home']));

			foreach($tree as $index => $vals)
			{
				if(is_array($vals))
				{
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
		
					$this->Content_model->temp_id = $vals['content_id'];
					$this->temp_uri = $this->Content_model->generate_uri();
					$parents[$index]['uri'] = $this->temp_uri;
					
					if($index < $last_index)
					{
						$this->breadcrumb_links[] = anchor($this->temp_uri, $vals['title']);
						$this->parent_title = $vals['title'];
					}
					else
					{
						$this->breadcrumb_links[] = $vals['title'];
					}

				}
			}

			array_pop($parents);
			$parents = array_reverse($parents);
			$this->data['parents'] = $parents;

		}
		else
		{

			if($this->result_count > 0 )
			{
				// generate possible matches or parent list
				
				foreach($tree as $index => $vals)
				{
					if(is_array($vals))
					{
						$this->Content_model->temp_id = $vals['content_id'];
						$this->temp_uri = $this->Content_model->generate_uri();

						$this->matches[] = anchor($this->temp_uri, $vals['title']);
			
					}
				}

			}
			
			return FALSE;

		}

			return true;

	}

	function _prepare_view()
	{
		// initialize content array and field labels
		$content_vars = array();
		$field_labels = array();

		// load data for view
		$this->Content_model->temp_id = $this->Content_model->type_id;
		$this->Content_model->scope = 'content';
		$this->Content_model->load_type();
		$this->view = 'themes/'.$this->Site_model->theme.'/'.$this->Content_model->type_uri_name;
				
		// if user logged in, pass session values to view
		$this->load->library('session');
		if($this->session->userdata('user_id'))
		{
			$this->data['userdata'] = $this->session->userdata;
		}

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

		// see if we are viewing attached media, and if so, load media info and switch to 'media' view
		if($this->media_view)
		{
			$this->view = 'themes/'.$this->Site_model->theme.'/media';

			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->File_model->mime_type);
			$media_vars = array(
			'media_id' => $this->File_model->media_id,
			'file_uri' => $this->File_model->file_uri,
			'filename' => basename($this->File_model->file_uri),
			'external' => $this->File_model->external,
			'mime_type' => $this->File_model->mime_type,
			'mime_cat' => $mime_cat,
			'date_created' => $this->File_model->date_created,
			'publish' => $this->File_model->publish,
			'title_id' => $this->File_model->title_id,
			'title_rel_id' => $this->File_model->title_rel_id,
			'title' => $this->File_model->title,
			'uri_title' => $this->File_model->uri_title,
			);
			if($this->File_model->keywords != '') {
				$media_vars['keywords'] = $this->File_model->keywords;
				$media_vars['keyword_links'] = $this->File_model->keyword_links();
			}
			if($this->File_model->external == 0) {
				$media_vars['rel_path'] = MEDIA_PATH_ORIG.$this->File_model->file_uri;
				$fp = ROOT_REL.$media_vars['rel_path'];
				$media_vars['server_path'] = $fp;

				$size = filesize($fp);
				$this->File_model->lang_load();
				$this->lang->load('mime', $this->File_model->lang_CI_Name);
				$byte_labels = $this->lang->line('file_size_abbr');
				$size_formatted = resize_bytes_lang($size,$byte_labels);
				
				$media_vars['size_bytes'] = $size;
				$media_vars['size_formatted'] = $size_formatted;
			}
			if($this->File_model->subtitle != '') {
				$media_vars['subtitle'] = $this->File_model->subtitle;
			}

			$this->File_model->load_metadata();

			$m_meta = $this->File_model->metadata;
			
			foreach($m_meta as $id => $field) {
				$media_vars[$field['field_ref_name']] = $field['value'];
				
				if($field['date_updated'] > $this->File_model->date_updated) {
					$this->File_model->date_updated = $field['date_updated'];
				}
	
			}

			$media_vars['date_updated'] = convert_time($this->File_model->date_updated, $this->Site_model->site_timezone);
			$media_vars['date_updated_W3C'] = convert_time($this->File_model->date_updated, $this->Site_model->site_timezone, "Y-m-d");

			$this->data['media'] = $media_vars;
			// end media view setup
		}

		// get this resource's URI
		$this->Content_model->temp_id = $this->Content_model->content_id;
		$this->temp_uri = $this->Content_model->generate_uri();

		$content_vars['content_id'] = $this->Content_model->content_id;
		$content_vars['parent_id'] = $this->Content_model->parent_id;
		$content_vars['type_id'] = $this->Content_model->type_id;

		$content_vars['type_uri_name'] = $this->Content_model->type_uri_name;
		$content_vars['title'] = $this->Content_model->title;
		if($this->Content_model->subtitle != '') {
			$content_vars['subtitle'] = $this->Content_model->subtitle;
		}

		if($this->Content_model->keywords != '') {
			$content_vars['keywords'] = $this->Content_model->keywords;
			$content_vars['keyword_links'] = $this->Content_model->keyword_links();
		}

		//format dates
		$dates = $this->_format_dates($this->Content_model->date_created,$this->Content_model->date_end_expiry);
		$content_vars = array_merge($content_vars,$dates);

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

				$this->File_model->temp_lang = $this->Content_model->temp_lang;
				$this->File_model->lang_load();
				$this->File_model->dir_current = $dir;
				$this->File_model->list_files(FALSE,TRUE,TRUE);
				$content_vars['dir_listing'] = $this->File_model->file_list;
				$content_vars['subdirs'] = $this->File_model->dir_list;
			}
			// load field label
	
			$this->Content_model->label_scope = 'field';
			$this->Content_model->temp_id = $field['field_id'];
			$this->Content_model->label_id = '';
			$this->Content_model->load_label();

			$field_labels[$field['field_ref_name']] = $this->Content_model->label;

		}

		$content_vars['date_updated'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone);
		$content_vars['date_updated_W3C'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone, "Y-m-d");

		$this->Content_model->lang_load();
		$content_vars['lang_ISO_639_3'] = $this->Content_model->lang_ISO_639_3;

		$this->data['content'] = $content_vars;
		$this->data['field_labels'] = $field_labels;

		// list attachments
		$this->data['attachments'] = $this->_attachment_info();

		// build menu array
		if($this->Content_model->parent_id == 0)
		{
			$menu_title = $this->Site_model->title;
		}
		else
		{
			$menu_title = $this->parent_title;
		}

		$this->data['menu_title'] = $menu_title;

		$this->Content_model->temp_lang = $this->Content_model->lang_ISO_639_3;
		$this->Content_model->type_id = '';
		$this->Content_model->limit = '';

		$this->Content_model->sort_order = array('order' => 'asc', 'title' => 'asc');
		$menu = $this->Content_model->get_children(true,true);
		$this->data['menu'] = $menu;

		// children
		$this->Content_model->parent_id = $content_vars['content_id'];
		$this->Content_model->page = $this->page;
		$this->Content_model->limit = $this->Site_model->limit_posts;
		$this->Content_model->sort_order = array('date' => 'desc');
		$children = $this->Content_model->get_children(false,true);

		foreach($children as $index => $entry)
		{
			$zentry =& $entry;
			
			// load author user data
			$this->User_model->temp_id = $entry['user_id'];
			$this->User_model->load_user();
			if($this->User_model->name_first != '')
			{
				$zentry['author'] = array(
				'name_first' => $this->User_model->name_first,
				'name_last' => $this->User_model->name_last,
				'username' => $this->User_model->username,
				'email' => $this->User_model->email
				);
			}
			
			// format date info for entry
			$dates = $this->_format_dates($entry['date_created'],$entry['date_end_expiry']);
			$zentry = array_merge($zentry,$dates);
			
			// get attachments to entry
			$this->Content_model->content_id = $entry['content_id'];
			$zentry['attachments'] = $this->_attachment_info();
			
			$children[$index] = $zentry;
		}
		
		$this->data['children'] = $children;
		$this->data['total_children'] = $this->Content_model->content_count;

		$this->data['page'] = $this->Content_model->page;
		$this->data['limit'] = $this->Content_model->limit;
		
		// number of pages
		$this->data['pages'] = ceil($this->Content_model->content_count / $this->Content_model->limit);
		
		// recently added children
		if($this->page > 1)
		{
			$this->Content_model->page = 1;
			$recent = $this->Content_model->get_children(false,true);
		}
		else
		{
			$recent = $children;
		}
		$this->data['recent'] = $recent;

		// see if there are other languages for this content
		$this->Content_model->lang_load();
		$other_langs = $this->Content_model->get_other_lang_ids();
		if($other_langs)
		{
			$this->data['other_langs'] = $other_langs;
		}
		
		// get top-level menu
		$this->Content_model->reset_vars();
		$this->Content_model->sort_order = array('order' => 'asc', 'title' => 'asc');
		$top_menu = $this->Content_model->get_descendant_content(0,FALSE);
		$this->data['top_menu'] = $top_menu;
	}

	function _home()
	{
		$this->home = TRUE;
		// if site preferences specify a vaild home page ID, then redirect and omit home page from menu, otherwise auto-generate home page
		if($this->Site_model->home_content_id > 0)
		{
			$this->Content_model->content_id = $this->Site_model->home_content_id;
			$this->Content_model->omit_id = $this->Site_model->home_content_id;
			
			if($this->Content_model->content_exists())
			{
				$this->Content_model->load_content();
				$this->_prepare_view();
				$this->home = FALSE;
			}
		}
			
		if($this->home)
		{
			$this->view = 'themes/'.$this->Site_model->theme.'/home';
			$this->data['breadcrumb_links'] = array($this->cms_labels['home']);

			$this->Content_model->sort_order = array('order' => 'asc', 'title' => 'asc');
			$this->Content_model->limit = '';
			$menu = $this->Content_model->get_descendant_content();
			$this->data['menu'] = $menu;

			$this->Site_model->count_site_langs();
			$other_langs = array();
			foreach($this->Site_model->lang_list as $lang)
			{
				if($lang != $this->Site_model->lang_ISO_639_3)
				{
					$this->Content_model->temp_lang = $lang;
					$this->Content_model->lang_load();
					$lang_name = $this->Content_model->lang_Ref_Name;
					if($this->Content_model->lang_Native_Name != '')
					{
						$lang_name = $this->Content_model->lang_Native_Name;
					}
					$other_langs[] = anchor($lang,$lang_name);
				}
			}

			if(count($other_langs) > 0)
			{
				$this->data['site_other_langs'] = $other_langs;
			}

			// recently added content
			$this->Content_model->reset_vars();
			$this->Content_model->temp_lang = $this->Site_model->lang_ISO_639_3;
			$this->Content_model->lang_load();
			$this->Content_model->limit = $this->Site_model->limit_posts;

			$this->data['recent'] = $this->_recent();
			$this->data['recent_total'] = $this->Content_model->content_count;
			$this->data['limit'] = $this->Site_model->limit_posts;
			$this->data['page'] = $this->page;
		}

	}

	function _recent($parent=NULL)
	{
		$this->Content_model->type_id = '';
		$this->Content_model->list_types();
		if($this->home)
		{
			$this->Content_model->omit_types = $this->Content_model->omit_types_home;
		}
		if(isset($parent))
		{
			$this->Content_model->parent_id = $parent;
		}
		$this->Content_model->content_list = array();
		$this->Content_model->sort_order = array('date' => 'desc');

		$this->Content_model->list_content(TRUE);

		$recent = $this->Content_model->content_list;
		$recent_entries = array();
		
		if(count($recent) > 0)
		{
			
			foreach($recent as $index => $post)
			{
				$entry =& $post;

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

				$date_updated = $post['date_updated'];
				$dates = $this->_format_dates($post['date_created'],$post['date_end_expiry']);
				$entry = array_merge($entry,$dates);

				if(array_key_exists('metadata',$post))
				{
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
				$entry['uri'] = $this->Content_model->generate_uri();
				
				// get parent info
				if($post['parent_id'] > 0)
				{
					$this->Content_model->temp_id = $post['parent_id'];
					$this->Content_model->load_title();
					$entry['parent_title'] = $this->Content_model->title;
					$puri = explode('/',$entry['uri']);
					array_pop($puri);
					$entry['parent_uri'] = implode('/',$puri);
				}

				// get attachments to entry
				$this->Content_model->content_id = $post['content_id'];
				$entry['attachments'] = $this->_attachment_info();
				
				$recent_entries[] = $entry;
				$this->Content_model->reset_vars();

			}

		}

		return $recent_entries;

	}
	
	function _attachment_info()
	{

		$attachments = array();

		// load mime type labels
		$this->lang->load('mime', $this->Content_model->lang_CI_Name);
		$this->data['mime_labels'] = $this->lang->line('mime');
		$ll = $this->lang->line('file_size_abbr');

		$this->Content_model->list_attachments();
		if(count($this->Content_model->attachments) > 0)
		{

			foreach($this->Content_model->attachments as $media)
			{
				$this->File_model->reset_media_vars();
				$tmp_array = $media;
				$this->File_model->temp_id = $media['media_id'];
				$this->File_model->load_media();
				$tmp_array['file_uri'] = $this->File_model->file_uri;
				$tmp_array['external'] = $this->File_model->external;
				$tmp_array['mime_type'] = $this->File_model->mime_type;
				$tmp_array['mime_cat'] = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->File_model->mime_type);
				$tmp_array['extension'] = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$this->File_model->file_uri));
				$tmp_array['filename'] = basename($this->File_model->file_uri);

				$tmp_array['date_created'] = $this->File_model->date_created;
				$tmp_array['publish'] = $this->File_model->publish;
				$this->File_model->temp_lang = $this->Content_model->temp_lang;
				$this->File_model->load_title();
				$tmp_array['title'] = $this->File_model->title;
				$tmp_array['uri_title'] = $this->File_model->uri_title;
				$tmp_array['subtitle'] = $this->File_model->subtitle;
				$tmp_array['keywords'] = $this->File_model->keywords;

				if($this->File_model->external == 0)
				{
					$fullpath = ROOT_REL.MEDIA_PATH_ORIG.$this->File_model->file_uri;
					$tmp_array['path_basename'] = ROOT_REL.MEDIA_PATH_ORIG.basename($this->File_model->file_uri);
					$tmp_array['server_path'] = $fullpath;
					$tmp_array['server_directory'] = ROOT_REL.MEDIA_PATH_ORIG.dirname($this->File_model->file_uri);
					// get file size in bytes and formatted
					if(file_exists($fullpath))
					{
						$bytes = filesize($fullpath);
						$tmp_array['filesize_bytes'] = $bytes;
						$tmp_array['filesize_formatted'] = resize_bytes_lang($bytes,$ll);
					}
					
				}

				$this->File_model->scope = 'media';
				$this->File_model->temp_lang = $this->Content_model->temp_lang;
				$this->File_model->temp_id = $media['media_id'];
				$this->File_model->load_metadata();
				$file_meta = $this->File_model->metadata;
				if(count($file_meta) > 0)
				{
					foreach($file_meta as $file_info)
					{
						if($file_info['value'] != '')
						{
							$tmp_array[$file_info['field_ref_name']] = $file_info['value'];
						}
					}
				}

				$attachments[$this->File_model->file_uri] = $tmp_array;
				unset($tmp_array);
			}
		}
		return $attachments;
	}
	
	function _format_dates($created,$end)
	{
		$dates = array();

		$dates['date_created'] = convert_time($created, $this->Site_model->site_timezone);
		$dates['date_created_W3C'] = convert_time($created, $this->Site_model->site_timezone, "Y-m-d");

		if($end != '0000-00-00 00:00:00')
		{
			$dates['date_end_expiry'] = convert_time($end, $this->Site_model->site_timezone);
			$dates['date_expiry_RFC850'] = convert_time($end, 'UTC',DATE_RFC850);
		}

		// load date formatting
		$this->lang->load('calendar', $this->Content_model->lang_CI_Name);
		
		$calendar_labels = array(
		'cal_day_sm' => $this->lang->line('cal_day_sm'),
		'cal_day_md' => $this->lang->line('cal_day_md'),
		'cal_day_lg' => $this->lang->line('cal_day_lg'),
		'cal_month_sm' => $this->lang->line('cal_month_sm'),
		'cal_month_lg' => $this->lang->line('cal_month_lg'),
		);
		
		$date_info = array(
		'day' => convert_time($created, $this->Site_model->site_timezone, "j"),
		'day_of_week' => strtolower(convert_time($created, $this->Site_model->site_timezone, "D")),
		'month_num' => convert_time($created, $this->Site_model->site_timezone, "m"),
		'year' => convert_time($created, $this->Site_model->site_timezone, "Y"),
		);

		$date_info['day_sm'] = $calendar_labels['cal_day_sm'][$date_info['day_of_week']];
		$date_info['day_md'] = $calendar_labels['cal_day_md'][$date_info['day_of_week']];
		$date_info['day_lg'] = $calendar_labels['cal_day_lg'][$date_info['day_of_week']];
		$date_info['month_sm'] = $calendar_labels['cal_month_sm'][$date_info['month_num']];
		$date_info['month_lg'] = $calendar_labels['cal_month_lg'][$date_info['month_num']];
		
		// long format date according to the content language
		$order_date_long = $this->lang->line('order_date_long');
		$date_long = '';
		foreach($order_date_long as $dd)
		{
			if(array_key_exists($dd,$date_info))
			{
				$date_long .= $date_info[$dd];
			}
			else
			{
				$date_long .= $dd;
			}
		}
		$dates['date_created_long'] = $date_long;

		// short format date according to the content language
		$order_date_short = $this->lang->line('order_date_short');
		$date_short = '';
		foreach($order_date_short as $dd)
		{
			if(array_key_exists($dd,$date_info))
			{
				$date_short .= $date_info[$dd];
			}
			else
			{
				$date_short .= $dd;
			}
		}
		$dates['date_created_short'] = $date_short;

		// month and year date according to the content language
		$order_date_mmm_yyyy = $this->lang->line('order_date_mmm_yyyy');
		$date_mmm_yyyy = '';
		foreach($order_date_mmm_yyyy as $dd)
		{
			if(array_key_exists($dd,$date_info))
			{
				$date_mmm_yyyy .= $date_info[$dd];
			}
			else
			{
				$date_mmm_yyyy .= $dd;
			}
		}
		$dates['date_created_mmm_yyyy'] = $date_mmm_yyyy;

		$dates['date_created_parts'] = $date_info;
		return $dates;
		
	}

}
?>
