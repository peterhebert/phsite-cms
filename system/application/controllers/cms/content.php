<?php
class Content extends Controller {

   function Content()
   {
		parent::Controller();

		// load models
		$this->load->model('Content_model');
		$this->load->model('Object_model');
		
		// load helpers
		$this->load->helper('url');
		$this->load->helper('html');
		$this->load->helper('test');

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
		$this->load->helper('file');

		$data['content_id'] = '';
		$data['object_id'] = '';
		$data['parent_id'] = '';
		$data['created'] = '';
		$data['updated'] = '';
		$data['content_data'] = array();
		$mylang = '';


		// if form submitted, process data
		if($this->input->post('submit') != '') {
			
			// load form validation library
			$this->load->library('form_validation');

			// set rules for content
			if($this->input->post('content_id') != '') {
				$this->form_validation->set_rules('content_id', 'Content ID', 'required|integer');
			}
			$this->form_validation->set_rules('object_id', 'Content Type', 'required|integer');
			$this->form_validation->set_rules('parent_id', 'Parent', 'required|integer');
			$this->form_validation->set_rules('created', 'Date Created', 'is_date|convert_date');

			// set rules for content data based upon object template

				// load object template
				if ($this->input->post('object_id') != '') {
					$this->Object_model->object_id = $this->input->post('object_id');
					$this->Object_model->load_object();
					
					$myfields = $this->Object_model->fields;

					// set field validation rules
					foreach($myfields as $row) {
						
						$validArray = explode('|',$row['validation']);
						
						// add rule if required
						if($row['required'] == 1) {
							array_unshift($validArray, 'required');
						}
						$newValid = array_unique($validArray);
						$validation = implode('|',$validArray);
						
						$field_name = "field[{$row['field_id']}]";
						$field_title = $row['field_name'];
						$this->form_validation->set_rules($field_name, $field_title, $validation);
					}
					
				}	
		
			// run validation
			if ($this->form_validation->run() == FALSE)
			{
				$this->load->view('myform');
			}
			else
			{
				$this->load->view('formsuccess');
			}


		
		}







		
		$params = $this->uri->uri_to_assoc(4);
		
		// if id supplied, load info
		if(array_key_exists('id',$params)) {
			if(array_key_exists('lang',$params) && is_lang($params['lang'])) {
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
