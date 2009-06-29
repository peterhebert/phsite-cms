<?php
class Content_type_model extends MY_Model {

	// type info
	var $type_id, $type_name_uri;

	// type metadata
	var $type_meta_id, $type_name, $type_lang;

	// type to field relationship
	var $rel_id, $field_id, $required, $order;
	
	// lists
	var $fields, $types;

    function Content_type_model()
    {
        // Call the Model constructor
        parent::MY_Model();
    }

    // get all content types
	function get_list()
    {

		$list = array();
		$query = $this->db->get('types');

		foreach ($query->result() as $row)
		{

			$temp =  array(
			'type_id' => $row->type_id,
			'type_name_uri' => $row->type_name_uri,
			'type_meta_id' => '',
			'type_name' => '',
			'type_lang' => ''
			);

			$this->db->where('type_id', $row->type_id);
			if($this->type_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->type_lang);
			}
			$this->db->limit(1);
			$query2 = $this->db->get('types_meta');
		   
			if ($query2->num_rows() > 0)
			{
				$row2 = $query2->row();
				$temp['type_meta_id'] = $row2->type_meta_id;
				$temp['type_name'] = $row2->type_name;
				$temp['type_lang'] = $row2->lang_ISO_639_3;
			}

		   $list[] = $temp;

		}

		$this->types = $list;

	}

    // get list of active languages for form drop-down
	function get_list_dropdown()
    {
		$this->get_list();
		$list = array();
		
		foreach($this->types as $row)
		{
			if($row['type_name'] != '')
			{
				$list[$row['type_id']] = $row['type_name'];
			} else {
				$list[$row['type_id']] = $row['type_name_uri'];
			}
		}

		return $list;
	}

    // get list of content types for form drop-down 'create new content'
	function get_list_dropdown_create()
    {
		$this->get_list();
		$list = array();
		
		foreach($this->types as $row)
		{
			if($row['type_name'] != '')
			{
				$temp_name = $row['type_name'];
			} else {
				$temp_name = $row['type_name_uri'];
			}
			
			$my_uri = site_url("cms/content/edit/type/".$row['type_id']);
			$list[$my_uri] = $temp_name;
		}

		return $list;
	}

    // load the content type
	function load_type()
    {

		$query = $this->db->get_where('types', array('type_id' => $this->type_id));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->type_name_uri = $row['type_name_uri'];
			return TRUE;
		}
		return FALSE;
		
    }

    // load the metadata
	function load_meta()
    {

        if($this->type_meta_id != '') {
			$this->db->where('type_meta_id', $this->type_meta_id);
		} else {
			$this->db->where('type_id', $this->type_id);
			if($this->type_lang != '') {
				$this->db->where('lang_ISO_639_3', $this->type_lang);
			}
		}
		$this->db->limit(1);

		$query = $this->db->get('types_meta');

		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->type_meta_id = $row['type_meta_id'];
			$this->type_name = $row['type_name'];
			$this->type_lang = $row['lang_ISO_639_3'];
			return TRUE;
		}
		return FALSE;

    }

    // list the available fields and see if they are in this content type
	function list_fields()
    {

		$list = array();
		$query = $this->db->get('fields');

		foreach ($query->result() as $row)
		{

			$temp_field =  array(
			'field_id' => $row->field_id,
			'field_ref_name' => $row->field_ref_name,
			'field_name' => '',
			'rel_id' => '',
			'required' => '',
			'order' => ''
			);

			// get name if exists
			$this->db->where('field_id', $row->field_id);
			if($this->type_lang != '')
			{
				$this->db->where('lang_ISO_639_3', $this->type_lang);
			}
			$this->db->limit(1);
			$query2 = $this->db->get('fields_meta');
		   
			if ($query2->num_rows() > 0)
			{
				$row2 = $query2->row();
				$temp_field['field_name'] = $row2->field_name;
			}

			// get data if in this content type
			$this->db->where('type_id', $this->type_id);
			$this->db->where('field_id', $row->field_id);
			$this->db->limit(1);
			$query3 = $this->db->get('r_types_fields');
		   
			if ($query3->num_rows() > 0)
			{
				$row3 = $query3->row();
				$temp_field['rel_id'] = $row3->rel_id;
				$temp_field['required'] = $row3->required;
				$temp_field['order'] = $row3->order;
			}

		   $list[] = $temp_field;

		}

		$this->fields = $list;

	}

    // list only the fields for this content type
	function list_fields_in_type()
    {

		$list = array();

		// get fields in this content type
		$this->db->where('type_id', $this->type_id);
		$query = $this->db->get('r_types_fields');
	   
		if ($query->num_rows() > 0)
		{

			foreach ($query->result() as $row)
			{
	
				$temp_field =  array(
				'field_id' => $row->field_id,
				'field_ref_name' => '',
				'field_name' => '',
				'rel_id' => $row->rel_id,
				'required' => $row->required,
				'order' => $row->order,
	
				);
				
				// get reference name
				$this->db->where('field_id', $row->field_id);
				$query2 = $this->db->get('fields');
			   
				if ($query2->num_rows() > 0)
				{
					$row2 = $query2->row();
					$temp_field['field_ref_name'] = $row2->field_ref_name;
				}

				// get name if exists
				$this->db->where('field_id', $row->field_id);
				if($this->type_lang != '')
				{
					$this->db->where('lang_ISO_639_3', $this->type_lang);
				}
				$this->db->limit(1);
				$query2 = $this->db->get('fields_meta');
			   
				if ($query2->num_rows() > 0)
				{
					$row2 = $query2->row();
					$temp_field['field_name'] = $row2->field_name;
				}

				$list[] = $temp_field;
			
			}

		}

		$this->fields = $list;

	}

	// update the object 
    function update_type()
    {
		$data = array('type_name_uri' => convert_uri($this->type_name_uri));

        if($this->type_id != '') {

			$this->db->where('type_id', $this->type_id);
			if($this->db->update('types', $data)){
				return TRUE;
			}

		} else {

			if($this->db->insert('types', $data)) { 
				$this->type_id = $this->db->insert_id();
				return TRUE;
			}
		}
		return FALSE;
    }

	// update the object 
    function update_type_metadata()
    {
		$data = array(
		'type_id' => $this->type_id,
		'type_name' => $this->type_name,
		'lang_ISO_639_3' => $this->type_lang
		);

        if($this->type_meta_id != '') {

			$this->db->where('type_meta_id', $this->type_meta_id);
			if($this->db->update('types_meta', $data)){
				return TRUE;
			}

		} else {

			if($this->db->insert('types_meta', $data)) { 
				$this->type_meta_id = $this->db->insert_id();
				return TRUE;
			}
		}
		return FALSE;
    }

	// delete the content type and all related table info
    function delete_type()
    {
        if($this->type_id != '') {

			$tables = array('types','types_meta','r_types_fields');
			$this->db->where('type_id', $this->type_id);
			$this->db->delete($tables);

		}
		
    }

    function update_field()
    {

		$data = array(
		'type_id' => $this->type_id,
		'field_id' => $this->field_id,
		'required' => $this->required,
		'order' => $this->order
		);

		if($this->rel_id != '')
		{
			// update
			$this->db->where('rel_id', $this->rel_id);
			if($this->db->update('r_types_fields', $data)) {
				return TRUE;
			} 
		} else {
			// insert
			if($this->db->insert('r_types_fields', $data)) {
				$this->rel_id = $this->db->insert_id();
				return TRUE;
			} 
		}
		
		return FALSE;
   }

    // remove related field from content type
    function remove_field()
    {
		
		$this->db->where('rel_id', $this->rel_id);
 		if($this->db->delete('r_types_fields')) {
			return TRUE;
		} else {
			return FALSE;
		} 

    }

	// is field related to object?
	function field_in_object()
    {

		$this->db->where('type_id', $this->type_id);
		$this->db->where('field_id', $this->field_id);
		$query = $this->db->get('r_types_fields');
				
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		} else {
			return FALSE;
		}
		
    }

}

?>