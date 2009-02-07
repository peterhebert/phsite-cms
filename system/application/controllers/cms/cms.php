<?php
class Cms extends Controller {

	function Cms()
	{
		parent::Controller();
		$this->load->helper('url');
	}
	
	function index()
	{
	
		// load list of fields
		$this->load->model('Field_model');
		$data['list'] = $this->Field_model->get_list();
		$indexdata['fields'] = $this->load->view('cms/field_list',$data, true);
	
		// load list of objects
		$this->load->model('Object_model');
		$data2['q'] = $this->Object_model->get_list();
		$indexdata['objects'] = $this->load->view('cms/object_list',$data2, true);

		if($num > 0) {
			$recent = $this->Content_model->get_list($num);
			$data['title'] = "Recently Added";
		} else {
			$recent = $this->Content_model->get_list();
			$data['title'] = "All content";
		}

		if(count($recent) > 0) {
			$links = array();
		} else {
			$links[] = 'No content found';
		}

		foreach ($recent as $content) {
			$this->Content_model->load($content['content_id']);
	
			if($langContent = $this->Content_model->getLang($lang)) {
			
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
		
		$data['list'] = ul($links);

		$this->load->view('cms/content_list',$data);






	
		// now load view
		$this->load->view('cms/index',$indexdata);
	}
	

}
?>