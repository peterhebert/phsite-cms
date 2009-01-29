<?php
class Object extends Controller {

       function Object()
       {
            parent::Controller();

			$this->load->model('Object_model');

		}

		function view()
		{

			$this->Object_model->object_id = $this->uri->segment(3);
			
			if($this->Object_model->load_object()) {
				$data['msg'] = '<p>object "'.$this->Object_model->name.'" exists</p>';
				$data['msg'] .= '<p>object fields: </p>';

				$this->load->library('table');
				$data['msg'] .= $this->table->generate($this->Object_model->fields);
				
			} else {
				$data['msg'] = '<p>object does not exist</p>';
			}
	
			$this->load->view('object_view',$data);
		}

		function index()
		{
			$data['q'] = $this->Object_model->get_list();
			$this->load->helper('url');
	
			$this->load->view('object_list',$data);
		}

		function edit()
		{
			$this->load->model('Field_model');
			$this->load->helper('form');
			
			$field_list = $this->Field_model->get_list();

			// process form
			
			if($this->input->post('submit')) {
				
				$this->load->library('form_validation');
				// first do object
				
				/* if 'object_id' supplied, update */
				if($this->input->post('object_id') != '' and $this->input->post('object_id') > 0) {
					$this->form_validation->set_rules('object_id', '', 'required|is_natural_no_zero');
				}
				$this->form_validation->set_rules('name', 'Object name', 'required|xss_clean');

				if ($this->form_validation->run())
				{
					if($this->input->post('object_id') != '' and $this->input->post('object_id') > 0) {
						$this->Object_model->object_id = $this->input->post('object_id');
					}
					$this->Object_model->name = $this->input->post('name');
					$this->Object_model->update_object();

				}


				// now do related fields
				$form_array = $this->input->post('rel_id', TRUE);
				$form_array_exists = $this->input->post('fields', TRUE);
				$form_array_required = $this->input->post('required', TRUE);

				foreach ($field_list as $the_field) {
					$zz_exist = FALSE;
					$zz_required = FALSE;

					if(is_array($form_array_exists) and in_array($the_field['field_id'],$form_array_exists)) {
						$zz_exist = TRUE;
					}

					if(is_array($form_array_required) and in_array($the_field['field_id'],$form_array_required)) {
						$zz_exist = TRUE;
						$zz_required = TRUE;
					}

					// is the field related?
					if( is_array($form_array) and in_array($the_field['field_id'],$form_array)) {

						// is the field selected in form?
						if($zz_exist == TRUE) {
							// yes, update
							$this->Object_model->update_field($form_array[$the_field['field_id']],$the_field['field_id'],$zz_required);
						} else {
							// no, delete
							$this->Object_model->remove_field($form_array[$the_field['field_id']]);
						}

					} else { // field not related

						// is the field selected in form?
						if($zz_exist == TRUE) {
							// yes, insert
							$this->Object_model->insert_field($the_field['field_id'],$zz_required);
						}

					}
				
				}

			}

			$data['msg'] = '';
			$data['hidden'] = '';
			$data['action'] = 'object/edit';

			if(is_numeric($this->uri->segment(3)) and $this->uri->segment(3) > 0) {
				$obj_id = $this->uri->segment(3);
			
				$this->Object_model->object_id = $obj_id;
				$data['hidden'] = array('object_id' => $obj_id);
				$data['action'] .= '/'.$obj_id;
			}	

			$data['attributes'] = array('id' => 'editor');

			$this->Object_model->load_object();
			$data['name'] = $this->Object_model->name;
			
			$related_fields = $this->Object_model->fields;
			
			foreach ($field_list as $the_field) {
				$checked = $this->Object_model->field_in_object($the_field['field_id']);
				if($checked) {
					$exists = TRUE;
					$required = $checked['required'];
				} else {
					$exists = FALSE;
					$required = FALSE;
				}
				$data['fields'][$the_field['field_id']] = array(
				"rel_id" => $checked['id'],
				"name" => $the_field['name'], 
				"exists" => $exists,
				"required" => $required);
			}

			$this->load->view('object_edit',$data);
		}



}
?>