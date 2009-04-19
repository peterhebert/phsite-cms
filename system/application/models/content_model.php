<?php
class Content_model extends Model {

	var $content_id;
	var $parent_id;
	var $object_id;
	var $user_id;
	var $created;
	var $updated;
	var $content_data;

	var $title_id;
	var $title;
	var $lang;

    function Content_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // list content
	function get_list($limit=0,$order='',$parent='',$page=1)
    {
		if($this->lang != '') {
			$this->db->where = array('lang' => $this->lang);
		}
		if($parent != '' && is_numeric($parent)) {
			$this->db->where = array('parent_id' => intval($parent));
		}
		if($limit > 0) {
			if($page > 1) {
				$start = ($page * $limit) -1;
				$this->db->limit($limit,$start);
			} else {
				$this->db->limit($limit);
			}
		}

		if($order == 'asc' or $order == 'desc') {
			$this->db->order_by('created',$order);
		}
		
		$query = $this->db->get('content');
		return $query->result_array();
	}

    // search for content
	function search($str)
    {
		$match = $this->input->xss_clean($str);

		$sql = "SELECT `content_id`, AVG( MATCH(`value`) AGAINST ('{$match}') ) AS `score` 
		FROM `content_data` 
		WHERE MATCH(value) AGAINST ('{$match}') 
		GROUP BY content_id 
		ORDER BY score desc
		";
		
		$query = $this->db->query($sql);
		return $query->result_array();
	}

    // return content (language param optional)
    function get_content()
    {

		// get title
		$title_where['content_id'] = $this->content_id;
		/*if($this->lang != '') {
			$title_where['lang'] = $this->lang;
		}*/
		
		$title_query = $this->db->get_where('content_titles', $title_where);
		if ($title_query->num_rows() > 0)
		{
		   $trow = $title_query->row();
		   $this->title_id = $trow->title_id;
		   $this->title = $trow->title;
		   $this->lang = $trow->lang;
		}

		// get content
		$query = $this->db->get_where('content', array('content_id' => $this->content_id));
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->parent_id = $row['parent_id'];
			$this->object_id = $row['object_id'];
			$this->user_id = $row['user_id'];
			$this->created = $row['created'];
			$this->updated = $row['updated'];

			// get content data
			$sql = "SELECT content_data.*, 
			r_object_field.required, 
			r_object_field.order,
			r_object_field.validation,
			field.field_name,
			field.input_type
			FROM ( 
			(content LEFT JOIN content_data ON content.content_id = content_data. content_id)
			LEFT JOIN r_object_field 
			ON content.object_id = r_object_field. object_id 
			AND content_data.field_id = r_object_field.field_id
			) 
			LEFT JOIN field ON content_data.field_id = field.field_id
			WHERE content.content_id = ".$this->content_id;

			if($this->lang != '') {
				$sql .= " AND content_data.lang = '{$this->lang}'";
			}

			$query = $this->db->query($sql);

			$this->content_data = $query->result_array();
		}

    }

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		if($this->lang == '') {
			$sql = "select count(data_id) as num, lang from content_data where lang != '' group by lang";
		} else {
			$sql = "select count(data_id) as num, lang from content_data where lang != '{$this->lang}' and lang != '' group by lang";
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
			'user_id' => $this->user_id,
			'created' => $this->created
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

    // update field data
    function update_data($field_id,$value,$data_id)
    {
		$success = TRUE;
		
		if($data_id == '') {

			if($value != '') {
				// insert
				$data = array(
				'content_id' => $this->content_id,
				'field_id' => $field_id,
				'value' => $value,
				'lang' => $this->lang
				);
				if($this->db->insert('content_data', $data)){
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return TRUE;
			}
			
		} else {

			if($value == '') {
				// delete
				$this->db->where('data_id', $data_id);
				if($this->db->delete('content_data')){
					return TRUE;
				}

			} else {
				// update
				$data = array(
				'content_id' => $this->content_id,
				'field_id' => $field_id,
				'value' => $value,
				'lang' => $this->lang
				);
		
				$this->db->where('data_id', $data_id);
				if($this->db->update('content_data', $data)){
					return TRUE;
				}
			}



		}

		return $success;

    }

    // search for content by title, parent
	function find_by_uri_parent($str,$parent_id,$lang='')
    {

		$uri_title = $this->input->xss_clean($str);
		
		$where['parent_id'] = $parent_id;
		$where['uri_title'] = $uri_title;
		if($lang != '') {
			$where['lang'] = $lang;
		}
		
		$this->db->select('*');
		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$this->db->where($where);
		$this->db->limit(1);
		$title_query = $this->db->get();

		if ($title_query->num_rows() > 0)
		{
		   $row = $title_query->row();
		   return $row;
		} 
		
		return FALSE;
	}

    // update title
    function update_title()
    {

		// try to find existing title id
		if($this->title_id == '') {

			$title_query = $this->db->get_where('content_titles', array('content_id' => $this->content_id, 'lang' => $this->lang));
			if ($title_query->num_rows() > 0)
			{
			   $trow = $title_query->row();
			   $this->title_id = $trow->title_id;
			} 

		}

		// update or insert title
		if($this->title_id == '') {
			// insert
			$data = array(
			'content_id' => $this->content_id,
			'title' => $this->title,
			'uri_title' => convert_uri($this->title),
			'lang' => $this->lang
			);
			if($this->db->insert('content_titles', $data)){
				$this->title_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$data = array(
			'content_id' => $this->content_id,
			'title' => $this->title,
			'uri_title' => convert_uri($this->title),
			'lang' => $this->lang
			);
	
			$this->db->where('title_id', $this->title_id);
			if($this->db->update('content_titles', $data)){
				return TRUE;
			}

		}
		return FALSE;

    }

    // return list of possible parents
    function getParents()
    {

		$sql = "SELECT * FROM content LEFT JOIN content_titles
		ON content.content_id = content_titles.content_id
		WHERE content_titles.lang = '{$this->lang}'";
		
		if ($this->content_id != '')
		{
			$sql .= " AND content.content_id != ".$this->content_id." 
			AND content.parent_id != ".$this->content_id;
		}
				
		$query = $this->db->query($sql);
		return $query->result_array();
	}


}

?>