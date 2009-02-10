<?php
class Content_model extends Model {

	var $content_id;
	var $parent_id;
	var $object_id;
	var $created;
	var $updated;
	var $content_data;

    function Content_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // list content
	function get_list($limit='',$order='')
    {
		if($limit != '' && intval($limit) > 0) {
			$this->db->limit(intval($limit));
		}

		if($order == 'asc' or $order == 'desc') {
			$this->db->order_by('created',$order);
		}
		
		$query = $this->db->get('content');
		return $query->result_array();
	}

    // return content of specific language and with no assigned language
    function getContent($id,$lang = 'eng')
    {
		$query = $this->db->get_where('content', array('content_id' => $id));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->content_id = $row['content_id'];
			$this->parent_id = $row['parent_id'];
			$this->object_id = $row['object_id'];
			$this->created = $row['created'];
			$this->updated = $row['updated'];
			
			$this->db->join('field', 'content_data.field_id = field.field_id', 'left');
			
			$where = "content_id = {$this->content_id} AND (lang = '' OR lang = '{$lang}')";
			$query = $this->db->get_where('content_data',$where);

			$this->content_data = $query->result_array();
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

    // perform content search
	function search()
    {
	}

}

?>