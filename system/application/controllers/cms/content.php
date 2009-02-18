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
		$this->load->helper('file');

		$data['otherlangs'] = array();
		$my_lang = '';
		$repop_from_post = FALSE;
		$params = $this->uri->uri_to_assoc(4);

		// if form submitted, process data
		if($this->input->post('submit') != '') {
			$my_lang = $this->input->post('my_lang');

			$post_field_ids = $this->input->post('field_id');
			$post_data_ids = $this->input->post('data_id');
			$post_field_vals = $this->input->post('field');
			$post_field_rules = $this->input->post('valid');
			
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
			foreach($post_field_rules as $index => $rules) {
				$ruleParams = explode('/',$rules);
				$this->form_validation->set_rules('field[{$index}]', $ruleParams[0], $ruleParams[1]);
			}
		
			// run validation
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = "<p><strong>The following errors were found:</strong></p>";
				$data['msg'] .= validation_errors();
				//repopulate form
				$repop_from_post = TRUE;
			} else {
				
				// update database
				
				$updated = TRUE;
				
				if($this->input->post('content_id') != '') {
					$this->Content_model->content_id = $this->input->post('content_id');
				}
				$this->Content_model->object_id = $this->input->post('object_id');
				$this->Content_model->parent_id = $this->input->post('parent_id');
				$this->Content_model->created = $this->input->post('created');
				if(!$this->Content_model->update_content()) {
					$updated = FALSE;
				}

				if(is_array($post_field_ids)) {
					foreach($post_field_ids as $index => $field) {
	
						if($this->input->post('content_id') != '') {
							$this->Content_model->content_id = $this->input->post('content_id');
							if(!$this->Content_model->update_data($post_data_ids[$index],$field,$post_field_vals[$field],$my_lang)) {
								$updated = FALSE;
							}
							
						} else {
							if(!$this->Content_model->insert_data($field,$post_field_vals[$field],$my_lang)) {
								$updated = FALSE;
							}
						}
					}
					if($updated) {
						$data['msg'] = "<p>content successfully updated</p>";
					}
				}

				$this->Content_model->content_data = array();
			}
		
		} // end form processing

		if ($repop_from_post == TRUE)
		{
			// populate form from POST data
			$this->Content_model->content_id = $this->input->post('content_id');
			$this->Content_model->object_id = $this->input->post('object_id');
			$this->Content_model->parent_id = $this->input->post('parent_id');
			$this->Content_model->created = $this->input->post('created');
			$this->Content_model->content_data = array();

			$post_field_ids = $this->input->post('field_id[]');
			$post_data_ids = $this->input->post('data_id[]');
			$post_field_vals = $this->input->post('field[]');
			$my_lang = $this->input->post('my_lang');

			$this->load->model('Field_model');

			foreach($post_field_ids as $index => $field) {
				$this->Field_model->id = $field;
				$this->Field_model->load();
				
				$content_data[] = array(
				'field_id' => $field,
				'input_type' => $this->Field_model->input_type,
				'field_name' => $this->Field_model->field_name,
				'data_id' => $post_data_ids[$index],
				'value' => $post_field_vals[$index]
				);
			}
			$this->Content_model->content_data = $content_data;

		} else {
			// populate form from database

			// if id supplied, load info
			if(array_key_exists('id',$params)) {
				if(array_key_exists('lang',$params) && is_lang($params['lang'])) {
					$this->Content_model->getContent($params['id'],$params['lang']);
					$my_lang = $params['lang'];
				} else {
					$this->Content_model->getContent($params['id']);
				}
				
				// get other langs
				$data['otherlangs'] = $this->Content_model->get_other_langs();
				
			}
			// END populate form from database
		}
		
		// get object template
		if (array_key_exists('type',$params)) {
			$this->Object_model->object_id = $params['type'];
		} else if ($this->Content_model->object_id != '') {
			$this->Object_model->object_id = $this->Content_model->object_id;
		} else {
			$this->Object_model->object_id = 1;
		}
		// get related fields for this object type
		$this->Object_model->load_object();

		echo  "<pre>";
		print_r($this->Object_model->fields);
		echo "</pre>\n";

		$data['form'] = $this->render_form($my_lang);
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
	

		// get object fields
		foreach($this->Object_model->fields as $row) {
			if($row['validation'] != '') {
				$validArray = explode('|',$row['validation']);
			} else {
				$validArray = array();
			}
			if($row['required'] == 1) {
				array_unshift($validArray, 'required');
			}
			$validation = implode('|',$validArray);
		
			$data_fields[$row['field_id']] = $row;
			$data_fields[$row['field_id']]['value'] = '';
			$data_fields[$row['field_id']]['data_id'] = '';
		}
			
		// get current values
		foreach($this->Content_model->content_data as $row) {
			$data_fields[$row['field_id']]['value'] = $row['value'];
			$data_fields[$row['field_id']]['input_type'] = $row['input_type'];
			$data_fields[$row['field_id']]['field_name'] = $row['field_name'];
			$data_fields[$row['field_id']]['data_id'] = $row['data_id'];
		}
	
		// generate content data fields
		foreach($data_fields as $key => $row) {
			$form_html .=  "<p>";
			$form_html .=  form_hidden('field_id[]', $key);
			$form_html .=  form_hidden('data_id[]', $row['data_id']);
			$validation = $row['field_name'].'/'.$row['validation'];
			$form_html .=  form_hidden('valid[]', $validation);

			$form_html .=  form_label($row['field_name']);
	
			$field_params = array(
			'name'        => "field[{$key}]",
			'id'          => "field[{$key}]"
			);

			if($row['value'] != '') {
				$field_params['value'] = form_prep($row['value']);
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
		$this->load->view('cms/search_results',$data);
	}


}
?>
