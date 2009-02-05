<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('url');
	}
	
	function index()
	{
	
		$this->load->model('Field_model');
		$data['list'] = $this->Field_model->get_list();
		$indexdata['fields'] = $this->load->view('cms/field_list',$data, true);
	
		$this->load->model('Object_model');
		$data2['q'] = $this->Object_model->get_list();
		$indexdata['objects'] = $this->load->view('cms/object_list',$data2, true);
	
		$this->load->view('cms/index',$indexdata);
	}
	

}
?>