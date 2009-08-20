<?php
class Field_model extends MY_Model {

	// main field variables
	var $field_id, $field_ref_name, $field_input_type, $field_validation, $field_needs_lang;

	// field metadata (by languauge)
	var $field_meta_id, $field_name, $field_lang;

	// field data types
	var $data_types = array();
	var $validation_rules = array();
	
	// field validation rules
	var $validation_string, $validation_exact, $validation_min, $validation_max;

	// field input types
	var $input_types = array();

    function Field_model()
    {
        // Call the Model constructor
        parent::MY_Model();

		$this->field_lang = 'eng';
		$this->load_values();
	}
	
	function load_values()
	{	

		// load validation rules and input types from language files

		$this->db->where('ISO_639_3', $this->field_lang);
		$query = $this->db->get('ISO_639_LANG');
 
 		$load_lang = 'english';
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			if($row['ci_lang_name'] != '')
			{
				$load_lang = $row['ci_lang_name'];
			}
		}

		$this->lang->load('form_input', $load_lang);
		$this->data_types = $this->lang->line('data_types');
		$this->input_types = $this->lang->line('input_types');
		$this->validation_rules = array_keys($this->lang->line('data_types'));
		
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
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('fields', $field_data))
			{
				$this->field_id = $this->db->insert_id();
				return TRUE;
			}
		}

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
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('fields_meta', $field_data))
			{
				$this->field_meta_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

    }



}

?>
