<?php

class Feed extends MY_Controller {

	var $feed = array(), $items = array();
	var $params = array(
	'type' => '',
	'lang' => '',
	'date' => '',
	'id' => '',
	);
	
	function Feed()
	{
		parent::MY_Controller();
		
		$this->load->model('Content_model');
		$this->load->model('User_model');
		$this->load->model('File_model');

		$this->load->helper('timezone');
		$this->load->helper('xml');
		$this->load->helper('url');
	}
	
    function index()
    {
		$this->data['title'] = "feed class";
		$this->data['xml_declaration'] = '<?xml version="1.0" encoding="UTF-8"?>';
		$this->_parse_uri();

		// load Site info
		$this->Site_model->site_id = $this->config->item('site_id');
		$this->Site_model->load_site();
		
		// load editor info
		$this->User_model->temp_id = $this->Site_model->site_editor_id;
		$this->User_model->load_user();
		$this->feed['editor'] = array(
		'email' => $this->User_model->email,
		'name' => $this->User_model->name_first.' '.$this->User_model->name_last
		);
		$this->User_model->clear_user_data();
		
		// load webmaster info
		$this->User_model->temp_id = $this->Site_model->site_admin_id;
		$this->User_model->load_user();
		$this->feed['webmaster'] = array(
		'email' => $this->User_model->email,
		'name' => $this->User_model->name_first.' '.$this->User_model->name_last
		);
		
		// load feed (channel) info
		$attributes = array('description','copyright');
		
		if($this->params['id'] == '')
		{
			$this->feed['uri'] = site_url('');
			
			// load Site info into $feed array
			$this->feed['language'] = $this->Site_model->lang_ISO_639_1;
			
			// format date according to feed type
			if($this->params['type'] == 'atom')
			{
				$this->feed['date_created'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, DATE_ATOM);
			}
			else
			{
				// default to rss
				$this->feed['date_created'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, DATE_RSS);
			}

			$this->feed['language'] = $this->Site_model->lang_ISO_639_1;

			$this->Site_model->scope = 'site';
			$this->Site_model->temp_id = $this->Site_model->site_id;
			$this->Site_model->temp_lang = $this->params['lang'];
			$this->Site_model->load_title();
			$this->feed['title'] = $this->Site_model->title;
			$this->Site_model->load_site_metadata();
			
			foreach($this->Site_model->metadata as $field)
			{
				if($field['value'] != '' && in_array($field['field_ref_name'],$attributes))
				{
					$this->feed[$field['field_ref_name']] = $field['value'];
				}
			}

			$date_updated = $this->Site_model->site_date_updated;
		}
		else
		{
			// load content
			$this->Content_model->content_id = $this->params['id'];
			$this->Content_model->load_content();

			$this->Content_model->temp_id = $this->params['id'];
			$this->feed['uri'] = site_url($this->Content_model->generate_uri());
			
			$this->feed['title'] = $this->Content_model->title;
			
			foreach($this->Content_model->metadata as $field)
			{
				if($field['value'] != '' && in_array($field['field_ref_name'],$attributes))
				{
					$this->feed[$field['field_ref_name']] = $field['value'];
				}
			}
			$date_updated = $this->Content_model->date_updated;

			// load feed author info
			$this->User_model->clear_user_data();
			$this->User_model->temp_id = $this->Site_model->site_admin_id;
			$this->User_model->load_user();
			$this->feed['author'] = array(
			'email' => $this->User_model->email,
			'name' => $this->User_model->name_first.' '.$this->User_model->name_last
			);

		}
		
		// now get items
		$this->Content_model->reset_vars();
		if($this->params['id'] != '')
		{
			$this->Content_model->parent_id = $this->params['id'];
		}
		$this->Content_model->limit = $this->Site_model->limit_posts;

		// list types to omit from syndication
		$this->Content_model->list_types();
		$this->Content_model->omit_types = $this->Content_model->omit_types_rss;
		$this->Content_model->sort_by = 'date_created';
		$this->Content_model->sort_dir = 'desc';
		
		$this->Content_model->list_content(TRUE);
		foreach($this->Content_model->content_list as $result)
		{
			$item = array();
			$item['title'] = $result['title'];
			$this->Content_model->temp_id = $result['content_id'];
			$item['uri'] = site_url($this->Content_model->generate_uri());

			// load author info
			$this->User_model->clear_user_data();
			$this->User_model->temp_id = $this->Site_model->site_editor_id;
			$this->User_model->load_user();
			$item['author'] = array(
			'email' => $this->User_model->email,
			'name' => $this->User_model->name_first.' '.$this->User_model->name_last
			);

			// format item dates according to feed type
			if($this->params['type'] == 'atom')
			{
				$item['date_created'] = convert_time($result['date_created'], $this->Site_model->site_timezone, DATE_ATOM);
				$item['date_updated'] = convert_time($result['date_updated'], $this->Site_model->site_timezone, DATE_ATOM);
			}
			else
			{
				// default to rss
				$item['date_created'] = convert_time($result['date_created'], $this->Site_model->site_timezone, DATE_RSS);
				$item['date_updated'] = convert_time($result['date_updated'], $this->Site_model->site_timezone, DATE_RSS);
			}

			// updated date for feed
			if($result['date_updated'] > $date_updated) {
				$date_updated = $result['date_updated'];
			}
			
			// metadata
			foreach($result['metadata'] as $field)
			{
				if($field['value'] != '')
				{
					$item[$field['field_ref_name']] = $field['value'];
				}
			}

			// attachments become enclosures
			$this->Content_model->content_id = $result['content_id'];
			$this->Content_model->list_attachments();

			if(count($this->Content_model->attachments) > 0)
			{
				$attachments = array();
				foreach($this->Content_model->attachments as $media)
				{
					if($media['external'] == 0)
					{
	
						$fullpath = ROOT_REL.MEDIA_PATH_ORIG.$media['file_uri'];
	
						// get file size in bytes and formatted
						if(file_exists($fullpath))
						{
							$tmp_array = array();

							$tmp_array['uri'] = 'http://'.$_SERVER['HTTP_HOST'].BASE_PATH.MEDIA_PATH_ORIG.$media['file_uri'];
							$tmp_array['filesize_bytes'] = filesize($fullpath);
							$tmp_array['mime_type'] = $media['mime_type'];

							$attachments[] = $tmp_array;
						}
	
					}
				}
	
				$item['attachments'] = $attachments;
			}

			$this->items[] = $item;
		}

		// format updated date according to feed type
		if($this->params['type'] == 'atom')
		{
			$this->feed['date_updated'] = convert_time($date_updated, $this->Site_model->site_timezone, DATE_ATOM);
		}
		else
		{
			// default to rss
			$this->feed['date_updated'] = convert_time($date_updated, $this->Site_model->site_timezone, DATE_RSS);
		}


		$view = 'feeds/'.$this->params['type'];

		$this->data['params'] = $this->params;
		$this->data['feed'] = $this->feed;
		$this->data['items'] = $this->items;
		$content_type = 'Content-Type: application/'.$this->params['type'].'+xml';
 
		$this->output->set_header($content_type);
 		$this->load->view($view,$this->data);		
   }

    function _parse_uri()
    {
		// parse uri parameters
		$segments = $this->uri->segment_array();

		$patterns = array(
		'type' => '#^(rss|atom)$#',
		'lang' => '#^([a-z]{3})$#',
		'date' => '#^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{4}-[0-9]{2}|[0-9]{4})$#',
		'id' => '#^id$#',
		);

		foreach($segments as $index => $segment)
		{
			$match = FALSE;
			foreach($patterns as $key => $pattern)
			{
				if(preg_match($pattern,$segment) && $this->params[$key] == '')
				{
					if($key == 'id' && array_key_exists($index+1,$segments) && preg_match('#^([0-9]+)$#',$segments[$index+1]))
					{
						$this->params[$key] = $segments[$index+1];
					}
					else
					{
						$this->params[$key] = $segment;
					}
					break;
				}
	
			}

		}

		if($this->params['date'] != '')
		{
			$this->Content_model->date_filter = $this->params['date'];
		}

		if($this->params['lang'] != '')
		{
			$this->Content_model->temp_lang = $this->params['lang'];
			$this->Content_model->lang_load();
			$this->Site_model->temp_lang = $this->params['lang'];
		}
		if($this->Content_model->lang_ISO_639_3 == '')
		{
			$this->Content_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
			$this->Content_model->lang_load();
			$this->params['lang'] = $this->Content_model->lang_ISO_639_3;
		}
		$this->Site_model->lang_load();

		if($this->params['type'] == '')
		{
			$this->params['type'] = 'rss';
		}

   }
	

}
?>
