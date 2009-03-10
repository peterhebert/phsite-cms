<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('form');

		// load objects
		$this->load->model('Field_model');
		$this->load->model('Object_model');
		$this->load->model('Content_model');
	}
	
	function index()
	{
		$params = $this->uri->uri_to_assoc(3);

		if(array_key_exists('lang',$params)) {
			$this->Content_model->lang = $params['lang'];
		}

		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data2['q'] = $this->Object_model->get_list();
		$index['new_objects'] = $data2['q'];
		$index['objects'] = $this->load->view('cms/object_list',$data2, true);

		// get recently added pages
		$contentdata['title'] = "Recently Added";
		$content = $this->Content_model->get_list(10,'desc');
		foreach($content as $row)
		{
			$this->Content_model->content_id = $row['content_id'];
			$this->Content_model->get_content();
			$link = "cms/content/edit/id/".$this->Content_model->content_id;
			if($this->Content_model->lang != '') {
				$link .= "/lang/".$this->Content_model->lang;
			}
			$list[$this->Content_model->content_id] = anchor($link, $this->Content_model->title, array('title' => "edit '{$this->Content_model->title}'"));
		}
		$contentdata['list'] = ul($list);
		$index['content'] = $this->load->view('cms/content_list',$contentdata, true);
	
		// now load view
		$this->load->view('cms/index',$index);
	}
	

}
?>