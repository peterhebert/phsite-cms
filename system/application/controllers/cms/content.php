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
		$data['title'] = 'Content';
		$content = $this->Content_model->get_list();

		foreach($content as $row)
		{

			$this->Content_model->getContent($row['content_id']);

			$details = array();
			$content_data = $this->Content_model->content_data;
			foreach($content_data as $field)
			{
				$details[$field['field_name']] = $field['value'];
			}

			if(array_key_exists('title', $details)) {
				$list[$row['content_id']] = anchor("cms/content/edit/id/".$row['content_id'], $details['title'], array('title' => "edit '{$details['title']}'"));
			} else {
				$list[$row['content_id']] = anchor("cms/content/edit/id/".$row['content_id'], 'ID '.$row['content_id'], array('title' => "edit ID ".$row['content_id']));
			}

		}

		$data['list'] = ul($list);
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
		$mylang = '';
		
		$params = $this->uri->uri_to_assoc(4);
		
		// if id supplied, load info
		if(array_key_exists('id',$params)) {
			if(array_key_exists('lang',$params)) {
				$this->Content_model->getContent($params['id'],$params['lang']);
				$mylang = $params['lang'];
			} else {
				$this->Content_model->getContent($params['id']);
			}
			
			$data['content_id'] = $this->Content_model->content_id;
			$data['object_id'] = $this->Content_model->object_id;
			$data['parent_id'] = $this->Content_model->parent_id;
			$data['created'] = $this->Content_model->created;
			$data['updated'] = $this->Content_model->updated;
			$data['content_data'] = $this->Content_model->content_data;

			// get other langs
			$data['otherlangs'] = $this->Content_model->get_other_langs();
			
		}

		// load object template
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
