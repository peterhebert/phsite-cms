<?php
class Search extends MY_Controller
{

	function Search()
	{
		parent::MY_Controller();

		// load models
		$this->load->model('Content_model');
		$this->Site_model->_load_site_metadata();
	}

	function search()
	{

		$this->Content_model->link_mode = 1;
		$this->data['term'] = $this->input->post('search-text');
		$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));
		$this->data['query'] = $this->db->last_query();

		$view = 'themes/'.$this->Site_model->theme.'/search_results';
		
		echo $view;
		
		//$this->load->view($view,$this->data);

	}



}
?>
