<?php
class Content_model extends Model {

	var $content_id;
	var $parent_id;
	var $object_id;
	var $created;
	var $updated;
	var $content_data;
	var $content_text;

    function Content_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // list all content
	function get_list()
    {
		$query = $this->db->get('content');
		return $query->result_array();
	}

    // load the content and related data
	function load()
    {

		$query = $this->db->get_where('content', array('content_id' => $this->content_id));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->parent_id = $row['parent_id'];
			$this->object_id = $row['object_id'];
			$this->updated = $row['updated'];

			$this->content_data = $this->db->get_where('content-data', array('content_id' => $this->content_id));
			return TRUE;
		} else {
			$this->content_id = '';
			$this->object_id = '';
			$this->parent_id = '';
			$this->updated = '';
			return FALSE;
		}
		
    }

    // insert new content
    function insert()
    {
		$data = array(
		'parent_id' => $parent_id,
		'object_id' => $object_id
		);
        $this->db->insert('field', $data);
    }

}

?>