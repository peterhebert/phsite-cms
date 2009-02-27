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
		$this->load->model('Field_model');
		$this->load->library('form_validation');		

		$params = $this->uri->uri_to_assoc(4);

/*		// if form submitted, process data
		if($this->input->post('submit') != '') {
			$form_ids = $this->input->post('field_id');
			$form_validation = $this->input->post('valid');
			$form_data_ids = $this->input->post('data_id');
			$form_values = $this->input->post('value');
			$data['content'] = $form_validation;
			
			// set validation rules
			$this->form_validation->set_rules('object_id', 'Content Type', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('parent_id', 'Parent', 'required|is_natural');
			$this->form_validation->set_rules('created', 'Date Created', 'required|is_date');
			foreach($form_ids as $index => $id) {
				$this->Field_model->field_id = $id;
				$this->Field_model->load();
				$this->form_validation->set_rules("value[{$index}]", $this->Field_model->field_name, $form_validation[$index]);
			}

			// validate and process if all valid
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors();
			} else {
				$success = TRUE;

				$this->Content_model->parent_id = $this->input->post('parent_id');
				$this->Content_model->object_id = $this->input->post('object_id');
				$this->Content_model->created = $this->input->post('created');

				if($this->input->post('content_id') != '') {
					$this->Content_model->content_id = $this->input->post('content_id');
				} else if (array_key_exists('id',$params) ) {
					$this->Content_model->content_id = intval($params['id']);
				}

				if(!$this->Content_model->update_content()) {
					$success = FALSE;
				}

				foreach($form_ids as $index => $id) {
					if(!$this->Content_model->update_data($id, $form_values[$index],$form_data_ids[$index])) {
						$success = FALSE;
					}
				}
				
				if($success === TRUE) {
					$data['msg'] = "content updated successfully";
				} else {
					$data['msg'] = "content could not be updated";
				}

			} // end validation
					
		}
		// end form processing

*/
		if(array_key_exists('id',$params)) {
			$this->Content_model->getContent($params['id']);
		}

		if($this->Content_model->object_id == '' && array_key_exists('type',$params)) {
			$this->Content_model->object_id = $params['type']; 
		}

		$data['form'] = $this->render_form('eng');
		$this->load->view('cms/content_edit',$data);
	
	}
	
	function render_form($my_lang) {
		$this->load->helper('form');
		
		// get list of object types
		$objects = $this->Object_model->get_list();
		foreach($objects as $row) {
			$objArray[$row['object_id']] = $row['name'];
		}

		/* initiate form attributes */
		$hidden = array();
		$data_fields = array();

		if($this->Content_model->content_id != '') {
			$my_url = site_url("cms/content/edit/id/".$this->Content_model->content_id);
			$hidden['content_id'] = $this->Content_model->content_id;
		} else {
			$my_url = site_url("cms/content/edit/");
		}
		$hidden['my_lang'] = $my_lang;
		$hidden['my_url'] = $my_url;
		$attributes = array('id' => 'content_editor');
		
		// open form
		$form_html =  form_open($my_url, $attributes, $hidden);

		// do main content fields
		$form_html .=  "<p>";
		$form_html .=  form_label('Content type', 'object_id');
		$form_html .=  form_dropdown('object_id', $objArray, $this->Content_model->object_id);
		$form_html .=  "</p>\n";
	
		$field_params = array(
		'name'        => 'parent_id',
		'id'          => 'parent_id',
		'value'       => $this->Content_model->parent_id
		);
		$form_html .=  "<p>";
		$form_html .=  form_label('Parent', 'parent_id');
		$form_html .=  form_input($field_params);
		$form_html .=  "</p>\n";
	
		$field_params = array(
		'name'        => 'created',
		'id'          => 'created',
		'value'       => $this->Content_model->created
		);
		$form_html .=  "<p>";
		$form_html .=  form_label('Created', 'created');
		$form_html .=  form_input($field_params);
		$form_html .=  "</p>\n";
		
		// check if content_data exists, if not load object template
		if(is_array($this->Content_model->content_data) and count($this->Content_model->content_data) > 0) {
			$form_fields = $this->Content_model->content_data;
		} else {
			$params = $this->uri->uri_to_assoc(4);
	
			if(array_key_exists('type',$params)) {
				$this->Object_model->object_id = $params['type'];
			} else {
				$this->Object_model->object_id = 1;
			}
			$this->Object_model->load_object();
			
			$form_fields = $this->Object_model->fields;

		}
	
		// now do fields for content data
		foreach($form_fields as $row) {
			$form_html .=  "<p>";

			if(array_key_exists('field_id',$row)) {
				$field_id =  $row['field_id'];
			} else {
				$field_id =  '';
			}
			if(array_key_exists('data_id',$row)) {
				$data_id =  $row['data_id'];
			} else {
				$data_id =  '';
			}

			$form_html .=  form_hidden('field_id[]', $field_id);
			$form_html .=  form_hidden('data_id[]', $data_id);

			$val = array();

			if($row['required'] == 1) {
				$val[] = 'required';
			}
			if($row['validation'] != '') {
				$val[] = $row['validation'];
			}

			$validation = implode('|',$val);
			$form_html .=  form_hidden('valid[]', $validation);

			$form_html .=  form_label($row['field_name']);
	
			$field_params = array(
			'name'        => "value[]",
			'id'          => "value[]",
			);

			if(array_key_exists('value',$row)) {
				$field_params['value'] = form_prep($row['value']);
			} else {
				$field_params['value'] = '';
			}


			switch($row['input_type']) {
			case 'text':
				$field_params['class'] = 'cms_input';
				$form_html .=  form_input($field_params);
				break;
			case 'textarea':
				$field_params['cols'] = 40;
				$field_params['class'] = 'cms_textarea';
				$form_html .=  form_textarea($field_params);
				break;
			case 'richtext':
				$field_params['cols'] = 40;
				$field_params['class'] = 'cms_rte';
				$form_html .=  form_textarea($field_params);
				break;
			default:
				$form_html .=  form_input($field_params);
			}
			
			$form_html .=  "</p>\n";
		}

		// close form
		$form_html .=  "<p>";
		$form_html .=  form_submit('submit', 'Save');
		$form_html .=  "</p>\n";
		$form_html .=  "</form>\n";

		// return form 
		return $form_html;

	}

	function search()
	{

		$data['term'] = $this->input->post('search-text');
		$data['matches'] = 	$this->Content_model->search($this->input->post('search-text'));
		$this->load->view('cms/search_results',$data);
	}


}
?>
