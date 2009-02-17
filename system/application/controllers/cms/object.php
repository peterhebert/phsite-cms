<?php
class Object extends Controller {

   function Object()
   {
		parent::Controller();

		$this->load->model('Object_model');
		$this->load->helper('url');

	}

	function index()
	{
		$data['q'] = $this->Object_model->get_list();

		$this->load->view('cms/object_list',$data);
	}

	function edit()
	{
		$this->load->model('Field_model');
		$this->load->helper('form');
		
		$data['msg'] = '';

		// process form
		
		if($this->input->post('submit')) {
			$success = TRUE;
			
			$this->load->library('form_validation');
			// first do object

			/* if 'object_id' supplied, update */
			if($this->input->post('object_id') != '' and $this->input->post('object_id') > 0) {
				$this->form_validation->set_rules('object_id', '', 'required|is_natural_no_zero');
			}
			$this->form_validation->set_rules('name', 'Object name', 'required|xss_clean');

			if ($this->form_validation->run())
			{
				if($this->input->post('object_id') != '') {
					$this->Object_model->object_id = $this->input->post('object_id');
				}
				$this->Object_model->name = $this->input->post('name');
				$this->Object_model->update_object();

			} else {
				$success = FALSE;
			} // end object update
			
			// now update field relationships
			
			// get data from form
			$my_fields = $this->input->post('fields', TRUE);
			$my_data = $this->input->post('data_id', TRUE);
			$my_required = $this->input->post('required', TRUE);
			$my_exists = $this->input->post('exists', TRUE);
			$my_order = $this->input->post('order', TRUE);
			
			foreach ($my_fields as $index => $field_id) {
				$zz_exist = FALSE;
				$zz_required = FALSE;
				$data_id = '';
				$order = '';

				if(is_array($my_exists) and in_array($field_id,$my_exists)) {
					$zz_exist = TRUE;
				}

				if(is_array($my_required) and in_array($field_id,$my_required)) {
					$zz_exist = TRUE;
					$zz_required = TRUE;
				}

				$data_id = $my_data[$index];
				$order = $my_order[$index];

				if($zz_exist) {
					//exists, now insert or update
					if($data_id != '') {
						if(!$this->Object_model->update_field($data_id,$field_id,$zz_required,$order)) {
							$success = FALSE;
						}
					} else {
						if(!$this->Object_model->insert_field($field_id,$zz_required,$order)) {
							$success = FALSE;
						}
					}
				} else {
					// does not exist, please remove all instances of related field
					if(!$this->Object_model->remove_field($field_id)) {
						$success = FALSE;
					}
				}


			}
			// end update field relationships

			if($success == true) {
				$data['msg'] .= "<p>Object updated successfully</p>";
			} else {
				$data['msg'] .= "<p>Object not updated</p>";
			}

		} // end form processing

		// get all available fields
		$complete_field_list = $this->Field_model->get_list();
		foreach ($complete_field_list as $the_field) {
			$obj_fields[$the_field['field_id']] = array(
			'field_name' => $the_field['field_name'],
			'input_type' => $the_field['input_type'],
			'validation' => $the_field['validation'],

			'data_id' => '',
			'object_id' => $this->Object_model->object_id,
			'required' => '',
			'order' => '',
			'fieldset' => '',
			);
		}


		// get list of related fields
		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$this->Object_model->object_id = $params['id'];
			$this->Object_model->load_object();
			$data['action'] = 'cms/object/edit/id/'.$params['id'];
			$data['hidden'] = array('object_id' => $params['id']);

			foreach ($this->Object_model->fields as $the_field) {
				$obj_fields[$the_field['field_id']] = array(
				'field_name' => $the_field['field_name'],
				'input_type' => $the_field['input_type'],
				'validation' => $the_field['validation'],

				'data_id' => $the_field['id'],
				'object_id' => $this->Object_model->object_id,
				'required' => $the_field['required'],
				'order' => $the_field['order'],
				'fieldset' => $the_field['fieldset']
				);
			}

			$data['name'] = $this->Object_model->name;

		}

		$data['attributes'] = array('id' => 'editor');
		$data['fields'] = $obj_fields;

		$this->load->view('cms/object_edit',$data);

	} // end edit()



}
?>