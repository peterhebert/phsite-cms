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

    // return other languages than current language
    function get_other_langs($lang = 'eng')
    {
		$mylang = $this->db->escape($lang);
		
		$otherlangs = array();

		if($lang == '') {
			$sql = "select count(data_id) as num, lang from content_data where lang != '' group by lang";
		} else {
			$sql = "select count(data_id) as num, lang from content_data where lang != $mylang and lang != '' group by lang";
		}
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
			   $otherlangs[] = $row['lang'];
			}
			return $otherlangs;
		}
		return FALSE;
    }

    // insert new content
    function insert()
    {
		$data = array(
		'parent_id' => $this->parent_id,
		'object_id' => $this->object_id
		);
        $this->db->insert('content', $data);
		$this->content_id = $this->db->insert_id();

		// insert field data
		foreach ($this->content_data as $field) {
		}


    }

    // insert new field data
    function insert_data($field_id,$value,$lang='eng')
    {
		$data = array(
		'field_id' => $field_id,
		'value' => $value,
		'lang' => $lang
		);
        $this->db->insert('content_data', $data);
    }

    // update field data
    function update_data($data_id,$field_id,$value,$lang='eng')
    {
		$data = array(
		'content_id' => $this->content_id,
		'field_id' => $field_id,
		'value' => $value,
		'lang' => $lang
		);

		$this->db->where('data_id', $data_id);
		$this->db->update('mytable', $data); 
    }

}

?>