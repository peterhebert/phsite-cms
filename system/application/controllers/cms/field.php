<?php
class Field extends Controller {

   function Field()
   {
		parent::Controller();

		$this->load->model('Field_model');
		$this->load->helper('url');
		$this->input_types = array(
		'text' => 'text',
		'textarea' => 'textarea',
		'richtext' => 'rich text editor',
		'checkbox' => 'checkbox',
		'radio' => 'radio',
		'password' => 'password',
		'file' => 'file');
	}

	function index()
	{
		$data['list'] = $this->Field_model->get_list();

		$this->load->view('cms/field_list',$data);
	}

	function edit()
	{
		
		$data['msg'] = '';
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('field_id', 'Field ID', 'required|integer');
		$this->form_validation->set_rules('field_name', 'Field Name', 'required|xss_clean');
		$this->form_validation->set_rules('input', 'Input Type', 'required|callback_valid_input|xss_clean');

		if ($this->form_validation->run() == FALSE)
		{
			$data['msg'] = validation_errors(); 
		}
		else
		{
			$this->Field_model->field_id = $this->input->post('field_id');	
			$this->Field_model->field_name = $this->input->post('field_name');	
			$this->Field_model->input = $this->input->post('input');	
			
			$updated = $this->Field_model->update_field();
			if($updated == TRUE) {
				$data['msg'] .= "<p><strong>field has been updated</strong></p>";
			}
		}

		if($this->Field_model->field_id == '') {
			$this->Field_model->field_id = $this->uri->segment(4);
		}	

		$this->Field_model->load();
		$data['field_id'] = $this->Field_model->field_id;
		$data['field_name'] = $this->Field_model->field_name;
		$data['input'] = $this->Field_model->input;
		$data['types'] = $this->input_types;
		$this->load->view('cms/field_edit',$data);
	}

	function valid_input($value)
	{
		return array_key_exists($value,$this->input_types);
	}

}
?>