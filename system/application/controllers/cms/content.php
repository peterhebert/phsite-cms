<?php
class Content extends Controller {

   function Content()
   {
		parent::Controller();

		$this->load->model('Content_model');
		$this->load->scaffolding('content');
		$this->load->helper('url');
		$this->load->helper('html');

	}

	function index()
	{
		if($this->uri->segment(3)) {
		}
		
		$recent = $this->Content_model->get_list(10);
		$data['title'] = "Recently Added";

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

		$data['list'] = ul($links);



		$this->load->view('cms/content_list',$data);
	}



	function edit()
	{
		$this->load->helper('form');

		$data['content_id'] = '';
		$data['object_id'] = '';
		$data['parent_id'] = '';
		$data['created'] = '';
		$data['updated'] = '';
		$data['content_data'] = array();
		
		$params = $this->uri->uri_to_assoc(4);
		
		if(array_key_exists('lang',$params) ) {
			$data['lang'] = $params['lang'];
		}

		if(array_key_exists('id',$params) ) {
			$this->Content_model->load($params['id']);
			$data['content_id'] = $this->Content_model->content_id;
			$data['parent_id'] = $this->Content_model->parent_id;
			$data['created'] = $this->Content_model->created;
			$data['updated'] = $this->Content_model->updated;
			$data['content_data'] = $this->Content_model->content_data;
			
		} else if ($this->uri->segment(4) == 'type') {
			$this->Content_model->object_id = $this->uri->segment(5);
		}
		
		$this->load->model('Object_model');
		if ($this->Content_model->object_id != '') {
			$data['object_id'] = $this->Content_model->object_id;
			$this->Object_model->object_id = $this->Content_model->object_id;
			$this->Object_model->load_object();
			
			$data['fields'] = $this->Object_model->fields;
			
		}	

		
		// get all needed fields for object type
		$objects = $this->Object_model->get_list();
		
		foreach($objects as $object) {
			$data['objects'][$object['object_id']] = $object['name'];
		}
		
		$this->load->view('cms/content_edit',$data);
	
	}
	
	function getFields() {
		//load fields for object content type, and current values
	}

	function search()
	{
		$data['term'] = $this->input->post('search-text');	
		$this->load->view('cms/search_results',$data);
	}

}
?>
