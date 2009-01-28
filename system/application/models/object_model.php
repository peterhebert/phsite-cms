<?php
class Object_model extends Model {

	var $object_id;
	var $name;
	var $fields;

    function Object_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // get all objects
	function get_list()
    {
		$query = $this->db->get('object');
		return $query->result_array();
	}


    // load the object and related fields
	function load_object()
    {

		$query = $this->db->get_where('object', array('object_id' => $this->object_id));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->name = $row['name'];
			$this->fields = $this->db->get_where('r-object-field', array('object_id' => $this->object_id));
			return TRUE;
		} else {
			$this->object_id = '';
			return FALSE;
		}

		
    }

	// update the object 
    function update_object()
    {
        if($this->object_id != '') {

			$this->db->where('object_id', $this->object_id);
			$this->db->update('object', $this);

		}
		
    }


	// delete the object and all related fields info
    function delete_object()
    {
        if($this->object_id != '') {

			$tables = array('object', 'r-object-field');
			$this->db->where('object_id', $this->object_id);
			$this->db->delete($tables);

		}
		
    }





	// related fields functions
	function field_in_object($id)
    {

		$query = $this->db->get_where('r-object-field', array('object_id' => $this->object_id, 'field_id' => $id));
				
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		} else {
			return FALSE;
		}

		
    }

    function add_field($field_id,$required)
    {
		$this->db->set('field_id', $field_id);
		$this->db->set('object_id', $this->object_id);
		$this->db->set('required', $required);
        $this->db->insert('r-object-field', $this);
    }

    function update_field($id,$field_id,$required)
    {
		$this->db->set('field_id', $field_id);
		$this->db->set('object_id', $this->object_id);
		$this->db->set('required', $required);
		
		$this->db->where('id', $id);
        $this->db->update('r-object-field', $this);
    }


}

?>