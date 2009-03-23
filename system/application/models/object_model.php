<?php
class Object_model extends Model {

	var $object_id;
	var $name;
	var $name_uri;
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
			$this->name_uri = $row['name_uri'];
			
			$this->db->join('field', 'r_object_field.field_id = field.field_id', 'left');
			$this->db->order_by("r_object_field.order", "asc");

			$field_query = $this->db->get_where('r_object_field', array('object_id' => $this->object_id));
			$this->fields = $field_query->result_array();
			return TRUE;
		} else {
			$this->object_id = '';
			return FALSE;
		}

		
    }

	// update the object 
    function update_object()
    {
        if($this->name_uri == '') {
			$this->name_uri = convert_uri($this->name);
		}


        if($this->object_id != '') {

			$data = array('name' => $this->name,'name_uri' => $this->name_uri);
			$this->db->where('object_id', $this->object_id);
			$this->db->update('object', $data);

		} else {

			$data = array('name' => $this->name,'name_uri' => $this->name_uri);
			$this->db->insert('object', $data); 
			$this->object_id = $this->db->insert_id();
		}
		
    }


	// delete the object and all related fields info
    function delete_object()
    {
        if($this->object_id != '') {

			$tables = array('object', 'r_object_field');
			$this->db->where('object_id', $this->object_id);
			$this->db->delete($tables);

		}
		
    }


	// related fields functions

	// is field related to object?
	function field_in_object($id)
    {

		$query = $this->db->get_where('r_object_field', array('object_id' => $this->object_id, 'field_id' => $id));
				
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		} else {
			return FALSE;
		}

		
    }

    // add related field to object
	function insert_field($field_id,$required,$order=0,$validation='')
    {
		
		$data = array(
		'field_id' => $field_id,
		'object_id' => $this->object_id,
		'required' => $required,
		'order' => $order,
		'validation' => $validation
		);
		if($this->db->insert('r_object_field', $data)) {
			return TRUE;
		} else {
			return FALSE;
		} 
    }

    // update related field
    function update_field($id,$field_id,$required,$order=0,$validation='')
    {
		$data = array(
		'field_id' => $field_id,
		'object_id' => $this->object_id,
		'required' => $required,
		'order' => $order,
		'validation' => $validation
		);
		
		$this->db->where('id', $id);
 		if($this->db->update('r_object_field', $data)) {
			return TRUE;
		} else {
			return FALSE;
		} 
   }

    // remove related field
    function remove_field($rel_id)
    {
		
		$this->db->where('id', $rel_id);
 		if($this->db->delete('r_object_field')) {
			return TRUE;
		} else {
			return FALSE;
		} 

    }


}

?>