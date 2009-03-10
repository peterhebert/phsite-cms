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
		$params = $this->uri->uri_to_assoc(4);

		$data['title'] = 'Content';
		$content = $this->Content_model->get_list();

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

		$data['list'] = ul($list);
		$this->load->view('cms/content_list',$data);
	}

	function edit()
	{
		$this->load->model('User_model');
		$this->load->model('Field_model');
		$this->load->model('Language_model');
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
			//$form['values'] = $this->input->post('value');
			
			// set validation rules
			if($this->input->post('content_id') != '') {
				$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural_no_zero');
				$this->form_validation->set_rules('created', 'Date Created', 'required|is_date');
			}

			$this->form_validation->set_rules('object_id', 'Content Type', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('user_id', 'Author', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('parent_id', 'Parent', 'required|is_natural');
			$this->form_validation->set_rules('title', 'Title', 'required');

			foreach($form['field_ids'] as $index => $id) {
				$this->Field_model->field_id = $id;
				$this->Field_model->load();
				$validation = '';
				$treq = array();
				if($form['required'][$index] == 1) {
					$treq[] = "required";
				}
				if($form['valid'][$index] != '') {
					$treq[] = $form['valid'][$index];
				}
				if(count($treq) > 0 ) {
					$validation = implode('|',$treq);
					$this->form_validation->set_rules("value_{$id}", $this->Field_model->field_name, $validation);
				}
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
				$this->Content_model->title = $this->input->post('title');
				$this->Content_model->lang = $this->input->post('lang');

				if(!$this->Content_model->update_content()) {
					$success = FALSE;
					$msg_update = "<p>Content could not be updated</p>\n";
				} else {
					// update title
					if(!$this->Content_model->update_title()) {
						$success = FALSE;
						$msg_update = "<p>Content could not be updated</p>\n";
					}
					// update data
					foreach($form['field_ids'] as $index => $id) {
						$form_fd_name = 'value_'.$id;
						if(!$this->Content_model->update_data($id,$this->input->post($form_fd_name),$form['data_ids'][$index])) {
							$success = FALSE;
							$msg_update .= "<p>Field '$id' could not be updated</p>\n";
						}
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
			$this->Content_model->content_id = $params['id'];
		}
		if($this->Content_model->lang == '' && array_key_exists('lang',$params)) {
			$this->Content_model->lang = $params['lang'];
		} else {
			$this->Content_model->lang = '';
		}

		$this->Content_model->get_content();

		if($this->Content_model->object_id == '' && array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type']; 
		}
		$data['lang'] = $this->Content_model->lang;
		$data['content_id'] = $this->Content_model->content_id;
		$data['parent_id'] = $this->Content_model->parent_id;
		$data['object_id'] = $this->Content_model->object_id;
		$data['user_id'] = $this->Content_model->user_id;
		$data['created'] = $this->Content_model->created;
		$data['updated'] = $this->Content_model->updated;
		$data['title_id'] = $this->Content_model->title_id;
		$data['title'] = $this->Content_model->title;

		// get list of enabled languages
		$languages = $this->Language_model->get_list();
		foreach($languages as $row) {
			if($row['Native_Name'] != '') {
				$langArray[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$langArray[$row['ISO_639_3']] = $row['Inverted_Name'];
			}
		}
		$data['langArray'] = $langArray;

		// get list of object types
		$objects = $this->Object_model->get_list();
		foreach($objects as $row) {
			$objArray[$row['object_id']] = $row['name'];
		}
		$data['objArray'] = $objArray;

		// get list of possible parent content
		$parents = $this->Content_model->getParents();
		$parentArray[0] = "site root";
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
		
		if($this->Content_model->object_id != '') {
			$this->Object_model->object_id = $this->Content_model->object_id;
		} else {
			$this->Object_model->object_id = 1;
		}

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
