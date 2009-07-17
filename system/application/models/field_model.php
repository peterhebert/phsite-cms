<?php
class Field_model extends MY_Model {

	// main field variables
	var $field_id, $field_ref_name, $field_input_type, $field_validation, $field_needs_lang;

	// field metadata (by languauge)
	var $field_meta_id, $field_name, $field_lang;

	// field validation rules
	var $validation_rules = array(
	'' => 'any value',
	'alpha ' => 'alphabetical characters',
	'alpha_numeric ' => 'alpha-numeric characters',
	'alpha_dash ' => 'alpha-numeric, underscores or dashes',
	'alpha_num_spaces' => 'alpha-numeric, spaces, underscores or dashes',
	'numeric ' => 'numeric characters',
	'integer ' => 'integer',
	'is_natural ' => 'natural number: 0, 1, 2, 3, etc.',
	'is_natural_no_zero ' => 'natural number, no zeroes: 1, 2, 3, etc.',
	'valid_email ' => 'email address',
	'valid_emails ' => 'comma separated list of valid email addresses',
	'valid_ip ' => 'Internet Protocol address',
	'path_dir' => 'directory',
	'path_file' => 'file',
	'is_date' => 'Date / time',
	'is_timezone' => 'Timezone',
	'is_lang' => 'ISO 639-3 language code',
	'is_uri' => 'URI/URL internet address'
	);

	var $validation_string, $validation_exact, $validation_min, $validation_max;

	// field input types
	var $input_types = array(
	'text' => 'text',
	'textarea' => 'textarea',
	'richtext' => 'rich text editor',
	'checkbox' => 'checkbox',
	'radio' => 'radio',
	'password' => 'password',
	'file' => 'file path',
	'directory' => 'directory path',
	);

    function Field_model()
    {
        // Call the Model constructor
        parent::MY_Model();
    }

    // get all fields
	function get_list()
    {

		$list = array();
		$query = $this->db->get('fields');

		foreach ($query->result() as $row)
		{

			$temp_field =  array(
			'field_id' => $row->field_id,
			'field_ref_name' => $row->field_ref_name,
			'field_input_type' => $row->field_input_type,
			'field_validation' => $row->field_validation,
			'field_needs_lang' => $row->field_needs_lang,
			'field_meta_id' => '',
			'field_name' => '',
			'field_lang' => ''
			);

			$this->db->where('field_id', $row->field_id);
			if($this->field_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->field_lang);
			}
			$this->db->limit(1);
			$query2 = $this->db->get('fields_meta');
		   
			if ($query2->num_rows() > 0)
			{
				$row2 = $query2->row();
				$temp_field['field_meta_id'] = $row2->field_meta_id;
				$temp_field['field_name'] = $row2->field_name;
				$temp_field['field_lang'] = $row2->lang_ISO_639_3;
			}

		   $list[] = $temp_field;

		}

		return $list;

	}

  // load the field based upon id
	function load()
    {
		$query = $this->db->get_where('fields', array('field_id' => $this->field_id));
		$row = $query->row_array();
		$this->field_ref_name = $row['field_ref_name'];
		$this->field_input_type = $row['field_input_type'];
		$this->field_validation = $row['field_validation'];
		$this->field_needs_lang = $row['field_needs_lang'];

		$this->parse_validation();
    }

	function load_meta($meta_lang='')
    {
		if($this->field_meta_id != '')
		{
			$this->db->where('field_meta_id', $this->field_meta_id);
		}


		if($meta_lang != '')
		{
			$this->db->where('lang_ISO_639_3', $meta_lang);
		}
		else if($this->field_lang != '')
		{
			$this->db->where('lang_ISO_639_3', $this->field_lang);
		}
		else
		{
		}
		
		$this->db->where('field_id', $this->field_id);
		$this->db->limit(1);

		$query = $this->db->get('fields_meta');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->field_meta_id = $row->field_meta_id;
			$this->field_name = $row->field_name;
			$this->field_lang = $row->lang_ISO_639_3;
		}

    }

    function update_field()
    {
		$this->merge_validation();
		
		$field_data = array(
		'field_ref_name' => $this->field_ref_name,
		'field_input_type' => $this->field_input_type,
		'field_validation' => $this->field_validation,
		'field_needs_lang' => $this->field_needs_lang
		);
		
		if( $this->field_id != '' )
		{
			// update
			$this->db->where('field_id', $this->field_id);
			if($this->db->update('fields', $field_data))
			{
				//$this->messages[] = "'{$this->field_ref_name}' field has been successfully updated";
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('fields', $field_data))
			{
				$this->field_id = $this->db->insert_id();
				//$this->messages[] = "'{$this->field_ref_name}' field has been successfully created";
				return TRUE;
			}
		}

		//$this->messages[] = "'{$this->field_ref_name}' field could not be updated";
		return FALSE;

    }

    function update_field_meta()
    {

		$field_data = array(
		'field_id' => $this->field_id,
		'field_name' => $this->field_name,
		'lang_ISO_639_3' => $this->field_lang
		);
		
		if( $this->field_meta_id != '' )
		{
			// update
			$this->db->where('field_meta_id', $this->field_meta_id);
			if($this->db->update('fields_meta', $field_data))
			{
				//$this->messages[] = "field name successfully updated";
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('fields_meta', $field_data))
			{
				$this->field_meta_id = $this->db->insert_id();
				//$this->messages[] = "field name has been successfully created";
				return TRUE;
			}
		}

		//$this->messages[] = "field name could not be updated";
		return FALSE;

    }

	function parse_validation()
	{

		$valid = explode('|',$this->field_validation);

		if ( is_array($valid) && count($valid > 0) ) {
		
			foreach($valid as $rule) {
			
				if(array_key_exists($rule,$this->validation_rules)){
					$this->validation_string = $rule;
				}
				if(ereg ( 'exact_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)exact_length\[([0-9]+)\](.*)$';
					$this->validation_exact = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'min_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)min_length\[([0-9]+)\](.*)$';
					$this->validation_min = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'max_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)max_length\[([0-9]+)\](.*)$';
					$this->validation_max = ereg_replace($pattern,'\\2',$rule);
				}
				
			}
		}

	}

	function merge_validation()
	{
		$validArray = array();

		if($this->validation_string != ''){
			$validArray[] = $this->validation_string;
		}
		if($this->validation_exact != ''){
			$validArray[] = 'exact_length['.$this->validation_string.']';
		}

		if($this->validation_min != ''){
			$validArray[] = 'min_length['.$this->validation_min.']';
		}
		if($this->validation_max != ''){
			$validArray[] = 'max_length['.$this->validation_max.']';
		}

		$this->field_validation = implode('|',$validArray);

	}


}

?>
