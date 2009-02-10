<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('url');
		$this->load->helper('html');
		// load objects
		$this->load->model('Field_model');
		$this->load->model('Object_model');
		$this->load->model('Content_model');
	}
	
	function index()
	{
		// load list of fields
		$data['list'] = $this->Field_model->get_list();
		$index['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$data2['q'] = $this->Object_model->get_list();
		$index['objects'] = $this->load->view('cms/object_list',$data2, true);

		// get recently added pages
		$recent = $this->Content_model->get_list(10);
		$contentdata['title'] = "Recently Added";

		if(count($recent) > 0) {
			$links = array();
		} else {
			$links[] = 'No content found';
		}

		foreach ($recent as $content) {
			$this->Content_model->load($content['content_id']);
	
			if($langContent = $this->Content_model->getLang('eng')) {
			
				$uri = "cms/content/edit/".$content['content_id'];
				if(array_key_exists  ('title', $langContent)) {
					$text = $langContent['title'];
				} else {
					$text = 'ID '.$content['field_id'];
				}
				$attr = array('title' => "edit '{$text}'");
			
				$links[] = anchor($uri,$text,$attr);
			}

		}
		
		$contentdata['list'] = ul($links);

		$index['content'] = $this->load->view('cms/content_list',$contentdata, true);

	
		// now load view
		$this->load->view('cms/index',$index);
	}
	

}
?>