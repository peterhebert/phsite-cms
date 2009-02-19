<?php
class Field_model extends Model {

	var $field_id;
	var $field_name;
	var $input_type;

    function Field_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // get all fields
	function get_list()
    {
		$query = $this->db->get('field');
		return $query->result_array();
	}


    // load the field based upon id
	function load()
    {
		$query = $this->db->get_where('field', array('field_id' => $this->field_id));
		$row = $query->row_array();
		$this->field_id = $row['field_id'];
		$this->field_name = $row['field_name'];
		$this->input_type = $row['input_type'];
    }

	// delete the field and all related object instances of field
    function delete_field()
    {
        if($this->field_id != '') {

			$tables = array('field', 'r_object_field');
			$this->db->where('field_id', $this->field_id);
			$this->db->delete($tables);

		}
		
    }

    function new_field($name,$input_type,$validation)
    {

		$insert_data = array('field_name' => $name, 'input_type' => $input_type);

		if($this->db->insert('field', $insert_data)) {
			$this->field_id = $this->db->insert_id();
			return TRUE;
		} else {
			return FALSE;
		}
    }

    function update_field()
    {
		if( $this->db->update('field', $this, array('field_id' => $this->field_id)) ) {
			return TRUE;
		} else {
			return FALSE;
		}

    }

}

?>
