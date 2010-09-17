<?php

class Search extends MY_Controller {

	function Search()
	{
		parent::MY_Controller();	
		$this->load->model('Content_model');
		$this->load->model('File_model');
		$this->load->helper('url');

		$this->_load_site_metadata();
		$this->_load_cms_labels();
		$this->data['cms_labels'] = $this->cms_labels;
	}
	
    function index()
    {

		if($this->input->post('search-text') != '')
		{
			$search_string = $this->input->post('search-text');
			$this->data['term'] = sprintf($this->cms_labels['search_term'],$search_string);
			$this->Content_model->temp_value = $search_string;

			$matches = $this->Content_model->search_content();

			// content matches
			if(count($matches) > 0)
			{
				$this->data['content_results'] = $matches;
			}

			// media matches
		}

		$this->view = 'themes/'.$this->Site_model->theme.'/search_results';

		$this->load->view($this->view,$this->data);
	}

	function k()
	{
		$kwd = $this->uri->segment(3);
		
		if($kwd != '')
		{
			$this->data['term'] = sprintf($this->cms_labels['search_matching_keywords'],$kwd);
			$this->Content_model->temp_value = $kwd;
			$content = $this->Content_model->search_content_keywords();

			$this->File_model->temp_value = $kwd;
			$media = $this->File_model->media_search_keywords();
	
			// content matches
			if(count($content) > 0)
			{
				$this->data['content_results'] = $content;
			}

			// media matches
			if(count($media) > 0)
			{
				$this->data['media_results'] = $media;
			}

		}

		$bclinks[] = anchor("",$this->cms_labels['home']);
		$bclinks[] = $this->cms_labels['search_results'];
		$this->data['breadcrumb'] = $bclinks;

		$this->view = 'themes/'.$this->Site_model->theme.'/search_results';

		$this->load->view($this->view,$this->data);

	}
	

}
?>