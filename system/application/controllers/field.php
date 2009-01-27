<?php
class Field extends Controller {

       function Field()
       {
            parent::Controller();

			$this->load->model('Field_model');
			//$this->load->scaffolding('field');
		}

		function listing()
		{
			$data['q'] = $this->Field_model->get_list();
			$this->load->library('table');
			$this->table->set_heading('ID', 'Field name');
			$data['table'] = $this->table->generate($data['q']);
	
			$this->load->view('field_list',$data);
		}

		function edit()
		{
			$data['msg'] = '';
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('field_id', 'Field ID', 'required|integer');
			$this->form_validation->set_rules('name', 'Field Name', 'required|xss_clean');
	
			if ($this->form_validation->run() == FALSE)
			{
				$data['msg'] = validation_errors(); 
			}
			else
			{
				$this->Field_model->field_id = $this->input->post('field_id');	
				$this->Field_model->name = $this->input->post('name');	
				
				$updated = $this->Field_model->update_field();
				if($updated == TRUE) {
					$data['msg'] .= "<p><strong>field has been updated</strong></p>";
				}
			}

			if($this->Field_model->field_id == '') {
				$this->Field_model->field_id = $this->uri->segment(3);
			}	

			$this->Field_model->load();
			$data['field_id'] = $this->Field_model->field_id;
			$data['name'] = $this->Field_model->name;
			$this->load->view('field_edit',$data);
		}



}
?>