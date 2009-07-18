<?php

class Parser extends MY_Controller {

	var $temp_uri;
	var $view;
	var $segments = array();
	var $breadcrumb_links = array();
	
	function Parser()
	{
		parent::MY_Controller();	
		$this->load->model('Content_model');
		$this->load->model('Content_type_model');
		
		$this->load->helper('url');

		$this->_load_site_metadata();
	}
	
    function index()
    {

		
		$this->temp_uri = $this->uri->uri_string();

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
		
		$body = "<p>Your requested URI is: ".$this->temp_uri."</p>\r";

		$this->segments = $this->uri->segment_array();

		// check if language parameter is present
		$this->Language_model->ISO_639_3 = $this->uri->segment(1);
		if($this->Language_model->load())
		{
			$body .= "<p>Language: ".$this->Language_model->Ref_Name."</p>\r";
			$this->Content_model->content_lang = array_shift($this->segments);
		}

		$this->_uri_parent_loop();
		
		$this->data['breadcrumb'] = implode(' : ', $this->breadcrumb_links);

		$this->data['content_id'] = $this->Content_model->content_id;
		$this->data['user_id'] = $this->Content_model->user_id;
		$this->data['parent_id'] = $this->Content_model->parent_id;
		$this->data['type_id'] = $this->Content_model->type_id;
		$this->data['type_name'] = $this->Content_model->type_name;
		$this->data['title'] = $this->Content_model->title;
		$this->data['subtitle'] = $this->Content_model->subtitle;

		$this->view = 'themes/'.$this->Site_model->theme.'/'.$this->Content_model->type_name;

		$this->data['content_date_created'] = convert_time($this->Content_model->date_1, $this->Site_model->site_timezone);
		if($this->Content_model->date_2 != '0000-00-00 00:00:00')
		{
			$this->data['content_date_expiry'] = convert_time($this->Content_model->date_2, $this->Site_model->site_timezone,DATE_RFC1123);
		}

		foreach($this->Content_model->content_metadata as $id => $field) {
			$tmp_field_label = 'content_'.$field['field_ref_name'];
			$this->data[$tmp_field_label] = $field['value'];
			
			if($field['date_updated'] > $this->Content_model->date_updated) {
				$this->Content_model->date_updated = $field['date_updated'];
			}
		}

		$this->data['content_date_updated'] = convert_time($this->Content_model->date_updated, $this->Site_model->site_timezone);

	}
	
	function _home()
	{
		$this->view = 'themes/'.$this->Site_model->theme.'/home';
		
		// list content
		$this->Content_model->link_mode = 0;
		$this->Content_model->lang_mode = $this->Language_model->lang_count;

		$body = $this->Content_model->site_map(0,1,2);

		if($body == '')
		{
			$body = 'no content';
		}
		
		$this->data['body'] = $body;
	}

	function _uri_parent_loop()
	{
		$this->temp_uri = '';
		$this->breadcrumb_links = array(anchor($this->temp_uri,'Home'));
		$current_path = 1;

		$pathcount = count($this->segments);

		foreach ($this->segments as $path) {

			$this->Content_model->uri_title = $path;
			$info = $this->Content_model->find_by_uri();

			if($info) {
				$this->temp_uri .= '/'.$path;
				
				if($this->Content_model->content_lang != '')
				{
					$this->Content_model->content_lang = $info->lang_ISO_639_3;
				}

				if ($current_path < $pathcount) {
					$this->breadcrumb_links[] = anchor($this->temp_uri, $info->title);
					$this->Content_model->parent_id = $info->content_id;
					
				} else {
					$this->breadcrumb_links[] = $info->title;
					$this->Content_model->content_id = $info->content_id;
				}
				$current_path = $current_path + 1;

			}


		}
		
		$this->Content_model->get_content();

	}


	function search()
	{

		if($this->input->post('search-text') != '')
		{
			$term = "your search term was: <strong>".$this->input->post('search-text')."</strong>";
			$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));
		}
		else
		{
			$term = 'no search term entered';
		}
		$this->data['term'] = $term;

		$this->view = 'themes/'.$this->Site_model->theme.'/search_results';

		$this->load->view($this->view,$this->data);
	}

// parsing models
/*


hierarchical:


	if only one language:
	
		/uri_title/uri_title/uri_title
		
	multiple languages:
	
		/lang/uri_title/uri_title/uri_title


no hierarchy

	if only one language:
	
		/uri_title
		
	multiple languages:
	
		/lang/uri_title


date based

	yyyy/mm/dd/uri_title

tags:

uri_title	sanitized version of title
hierarchy	hierarchical path based upon uri_title
yyyy
yyyy-mm
yyyy-mm-dd	posted date values
lang		ISO 639-3 3-digit language code


*/

}
?>