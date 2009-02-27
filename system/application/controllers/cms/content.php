<?php
class Content extends Controller {

   function Content()
   {
		parent::Controller();

		// load models
		$this->load->model('Content_model');
		$this->load->model('Object_model');
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
		$this->load->model('User_model');
		$this->load->model('Field_model');
		$this->load->library('form_validation');		

		$params = $this->uri->uri_to_assoc(4);

		// if form submitted, process data
		if($this->input->post('submit') != '') {

			$form['content_id'] = $this->input->post('content_id');
			$form['parent_id'] = $this->input->post('parent_id');
			$form['object_id'] = $this->input->post('object_id');
			$form['user_id'] = $this->input->post('user_id');
			$form['created'] = $this->input->post('created');

			$form['field_ids'] = $this->input->post('field_id');
			$form['valid'] = $this->input->post('valid');
			$form['required'] = $this->input->post('required');
			$form['data_ids'] = $this->input->post('data_id');
			$form['values'] = $this->input->post('value');
			
			// set validation rules
			$this->form_validation->set_rules('object_id', 'Content Type', 'required|is_natural_no_zero');
			//$this->form_validation->set_rules('user_id', 'Author', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('parent_id', 'Parent', 'required|is_natural');
			$this->form_validation->set_rules('created', 'Date Created', 'required|is_date');
			foreach($form['field_ids'] as $index => $id) {
				$this->Field_model->field_id = $id;
				$this->Field_model->load();
				$validation = '';
				$treq = array();
				if($form['required'][$index] == 1) {
					$treq[] = "required";
				}
				$treq[] = $form['valid'][$index];
				$validation = implode('|',$treq);
				$this->form_validation->set_rules("value[{$index}]", $this->Field_model->field_name, $validation);
			}

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors();
			} else {
				$success = 1;
				$msg_update = '';

				$this->Content_model->content_id = $this->input->post('content_id');
				$this->Content_model->parent_id = $this->input->post('parent_id');
				$this->Content_model->object_id = $this->input->post('object_id');
				$this->Content_model->user_id = $this->input->post('user_id');
				$this->Content_model->created = $this->input->post('created');

				if(!$this->Content_model->update_content()) {
					$success = FALSE;
					$msg_update = "<p>Content model could not be updated</p>\n";
				}

				foreach($form['field_ids'] as $index => $id) {
					if(!$this->Content_model->update_data($id,$form['values'][$index],$form['data_ids'][$index])) {
						$success = FALSE;
						$msg_update .= "<p>Field '$id' could not be updated</p>\n";
					}
				}
				
				if($success == 1) {
					$data['msg'] = "content updated successfully";
				} else {
					$data['msg'] = $msg_update;
				}

			} // end validation
					
		}
		// end form processing


		// read database and populate form
		if(array_key_exists('id',$params)) {
			$this->Content_model->getContent($params['id']);
		}

		if(array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type']; 
		}

		$data['content_id'] = $this->Content_model->content_id;
		$data['parent_id'] = $this->Content_model->parent_id;
		$data['object_id'] = $this->Content_model->object_id;
		$data['user_id'] = $this->Content_model->user_id;
		$data['created'] = $this->Content_model->created;
		$data['updated'] = $this->Content_model->updated;

		// get list of object types
		$objects = $this->Object_model->get_list();
		foreach($objects as $row) {
			$objArray[$row['object_id']] = $row['name'];
		}
		$data['objArray'] = $objArray;

		// get list of possible parent content
		$parents = $this->Content_model->getParents();
		$parentArray[0] = "none - top level content";
		if(is_array($parents) && count($parents) > 0) {
			foreach($parents as $row) {
				if(array_key_exists('title',$row) && $row['title'] != '') {
					$pname = $row['title'];
				} else {
					$pname = $row['content_id'];
				}

				$parentArray[$row['content_id']] = $pname;
			}
		}
		$data['parentArray'] = $parentArray;

		// get list of users
		$userArray = array();
		$users = $this->User_model->get_list();
		foreach($users as $row) {
			$userArray[$row['user_id']] = $row['name_last'].', '.$row['name_first'];
		}
		$data['userArray'] = $userArray;

		if($this->Content_model->content_id != '') {
			$data['action'] = site_url("cms/content/edit/id/".$this->Content_model->content_id);
		} else {
			$data['action'] = site_url("cms/content/edit/");
		}

		$data['content_data'] = $this->Content_model->content_data;
		
		$this->Object_model->object_id = $this->Content_model->object_id;
		$this->Object_model->load_object();
		$data['fields'] = $this->Object_model->fields;
		
		$this->load->view('cms/content_edit',$data);
	
	}
	
	function search()
	{

		$data['term'] = $this->input->post('search-text');
		$data['matches'] = 	$this->Content_model->search($this->input->post('search-text'));
		$this->load->view('cms/search_results',$data);
	}


}
?>
