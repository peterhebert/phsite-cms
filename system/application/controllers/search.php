<?php
class Search extends Controller
{

	function Search()
	{
		parent::Controller();

		// load models
		$this->load->model('Content_model');
		$this->load->model('Object_model');
		$this->load->model('Site_model');

		$this->Site_model->load_prefs();
	}

	function index()
	{
		$this->data['term'] = $this->input->post('search-text');
		$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));

		$view = 'themes/'.$this->Site_model->theme.'/search_results';
		
		$this->load->view($view,$this->data);

	}

	function advanced()
	{
		$this->data['term'] = $this->input->post('search-text');
		$this->data['matches'] = $this->Content_model->search($this->input->post('search-text'));

		$view = 'themes/'.$this->Site_model->theme.'/search_advanced';
		
		$this->load->view($view,$this->data);
	}


}
?>
