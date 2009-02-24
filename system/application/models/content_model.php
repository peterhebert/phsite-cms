<?php
class Content_model extends Model {

	var $content_id;
	var $parent_id;
	var $object_id;
	var $user_id;
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
			$this->user_id = $row['user_id'];
			$this->created = $row['created'];
			$this->updated = $row['updated'];
			
			$sql = "SELECT content.content_id, r_object_field.*, 
			content_data.data_id, content_data.value, content_data.lang, content_data.updated, 
			field.field_name, field.input_type
			FROM (
			(r_object_field LEFT JOIN content ON r_object_field.object_id = content.object_id ) 
			LEFT JOIN content_data ON r_object_field.field_id = content_data.field_id
			)
			LEFT JOIN field ON r_object_field.field_id = field.field_id
			WHERE r_object_field.object_id = ".$this->object_id." AND content.content_id = ".$this->content_id;

			$query = $this->db->query($sql);

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

    // update main content info
    function update_content()
    {

		if($this->content_id == '') {
			// insert
			$data = array(
			'parent_id' => $this->parent_id,
			'object_id' => $this->object_id,
			'user_id' => $this->user_id
			);
			if($this->db->insert('content', $data)){
				$this->content_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
			$data = array(
			'parent_id' => $this->parent_id,
			'object_id' => $this->object_id,
			'user_id' => $this->user_id,
			'created' => $this->created
			);
			$this->db->where('content_id', $this->content_id);
			if($this->db->update('content', $data)){
				return TRUE;
			}
		}

		return FALSE;

    }

    // insert new field data
    function insert_data($field_id,$value,$lang='eng')
    {
    }

    // update field data
    function update_data($field_id,$value,$data_id = '',$lang='eng')
    {
		if($data_id != '') {

			if($value != '') {
				// update
				$data = array(
				'content_id' => $this->content_id,
				'field_id' => $field_id,
				'value' => $value,
				'lang' => $lang
				);
		
				$this->db->where('data_id', $data_id);
				if($this->db->update('content_data', $data)){
					return TRUE;
				}
			} else {
				// delete
				$this->db->where('data_id', $data_id);
				if($this->db->delete('content_data')){
					return TRUE;
				}
			}
			
		} else {
			// insert
			$data = array(
			'field_id' => $field_id,
			'value' => $value,
			'lang' => $lang
			);
			if($this->db->insert('content_data', $data)){
				return TRUE;
			}
		}

		return FALSE;

    }


}

?>