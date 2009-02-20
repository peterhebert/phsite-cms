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
		$data['hidden'] = array();
		$data['fields'] = array();
		$data['string_rules'] = array(
		'' => 'any value',
		'alpha' => 'Alphabetical',
		'alpha_numeric' => 'Alpha-numeric',
		'alpha_dash' => 'Alpha-numeric &amp; dashes',
		'alpha_num_spaces' => 'Alpha-numeric, dashes &amp; spaces',
		'is_date' => 'Date',
		'valid_email' => 'Email address',
		'integer' => 'Integer',
		'numeric' => 'Numeric',
		'is_natural' => 'Natural numbers (0, 1, 2, 3, etc.)',
		'is_natural_no_zero' => 'Natural numbers/no zeroes (1, 2, 3, etc.)',
		'file_path' => 'file path',
		'valid_ip' => 'Internet Protocol (IP) address'
		);

		$params = $this->uri->uri_to_assoc(4);
		if(array_key_exists('id',$params)) {
			$data['action'] = 'cms/object/edit/id/'.$params['id'];
		} else {
			$data['action'] = 'cms/object/edit/';
		}

		// process form
		
		if($this->input->post('submit')) {
			$this->load->library('form_validation');

			// get post data for object
			$this->Object_model->object_id = $this->input->post('object_id');
			$this->Object_model->name = $this->input->post('name');

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

				// put post field data into arrays
				$field_id = $this->input->post('field_id');
				$rel_id = $this->input->post('rel_id');
				$exists = $this->input->post('exists');
				$required = $this->input->post('required');
				$order = $this->input->post('order');
				$string = $this->input->post('string');
				$exact_length = $this->input->post('exact_length');
				$min_length = $this->input->post('min_length');
				$max_length = $this->input->post('max_length');
	
				// loop through arrays
				foreach($field_id as $index => $my_id) {
				
				
					$validArray = array();
	
					if($string[$index] != '') {
						$validArray[] = $string[$index];
					}
				
					if($exact_length[$my_id] != '') {
						$validArray[] = 'exact_length['.$exact_length[$index].']';
					} else {
	
						$min = intval($min_length[$my_id]);
						$max = intval($max_length[$my_id]);
	
						if($min > 0 && $max > 0 && $min === $max) {
							$validArray[] = 'exact_length['.$min_length[$my_id].']';
						} else {
						
							if($min > 0 && ($min < $max  || $max == 0) ) {
								$validArray[] = 'min_length['.$min_length[$my_id].']';
							}
	
							if($max > 0 && ($max > $min || $min == 0) ) {
								$validArray[] = 'max_length['.$max_length[$my_id].']';
							}
						
						}
	
					}
					$val_for_db = implode('|',$validArray);
	
					if($rel_id[$index] != '') {
	
						if(in_array($my_id,$exists)) {
							// exists, update
							$this->Object_model->update_field($rel_id[$index],$my_id,$required[$index],$order[$my_id],$val_for_db);
						} else {
							// does not exist, delete
							$this->Object_model->remove_field($rel_id[$index]);
						}
	
					} else {
	
						if(in_array($my_id,$exists)) {
							// insert
							$this->Object_model->insert_field($my_id,$required[$index],$order[$my_id],$val_for_db);
						}
	
					}
				}


			}

		} // end form processing

		// get all available fields
		$complete_field_list = $this->Field_model->get_list();
		foreach ($complete_field_list as $row) {
			$obj_fields[$row['field_id']] = array(
			'field_id' => $row['field_id'],
			'field_name' => $row['field_name'],
			'input_type' => $row['input_type'],

			'rel_id' => '',
			'object_id' => $this->Object_model->object_id,
			'required' => '',
			'order' => '',

			'string' => '',
			'exact_length' => '',
			'min_length' => '',
			'max_length' => '',
			);

		}

		// get list of related fields, with current values
		if(array_key_exists('id',$params)) {
			$this->Object_model->object_id = $params['id'];
			$this->Object_model->load_object();

			$data['name'] = $this->Object_model->name;
			$data['hidden'] = array('object_id' => $params['id']);

			foreach ($this->Object_model->fields as $row) {
				$obj_fields[$row['field_id']]['rel_id'] = $row['id'];
				$obj_fields[$row['field_id']]['required'] = $row['required'];
				$obj_fields[$row['field_id']]['order'] = $row['order'];
				
				// process validation
				$validation = $this->parseValidation($row['validation']);
				
				$obj_fields[$row['field_id']]['string'] = $validation['string'];
				$obj_fields[$row['field_id']]['exact_length'] = $validation['exact'];
				$obj_fields[$row['field_id']]['min_length'] = $validation['min'];
				$obj_fields[$row['field_id']]['max_length'] = $validation['max'];
			}

		}

		$data['attributes'] = array('id' => 'editor');
		$data['fields'] = $obj_fields;

		$this->load->view('cms/object_edit',$data);

	} // end edit()

	function parseValidation($raw)
	{
		$data = array( 'string' => '', 'exact' => '', 'min' => '', 'max' => '' );

		$string_rules = array(
		'alpha',
		'alpha_numeric',
		'alpha_dash',
		'alpha_num_spaces',
		'is_date',
		'valid_email',
		'integer',
		'numeric',
		'is_natural',
		'is_natural_no_zero',
		'file_path',
		'valid_ip'
		);
		
		$valid = explode('|',$raw);
		if ( is_array($valid) && count($valid > 0) ) {
			foreach($valid as $rule) {
				if(in_array($rule,$string_rules,true)){
					$data['string'] = $rule;
				}
				if(ereg ( 'exact_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)exact_length\[([0-9]+)\](.*)$';
					$data['exact'] = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'min_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)min_length\[([0-9]+)\](.*)$';
					$data['min'] = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'max_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)max_length\[([0-9]+)\](.*)$';
					$data['max'] = ereg_replace($pattern,'\\2',$rule);
				}
				
			}
		}
		

		return $data;
	}

}
?>