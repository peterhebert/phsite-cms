<?php
class Content_model extends Model {

	var $content_id;
	var $parent_id;
	var $object_id;
	var $object_name;
	var $user_id;
	var $created;
	var $updated;
	var $content_data;
	var $splash = 0; // make available to display on home page
	var $index = 1; // make available to menus, other indexes

	var $title_id;
	var $title;
	var $uri_title;
	var $content_lang;

    function Content_model()
    {
        // Call the Model constructor
        parent::Model();
    }

    // list content
	function get_list($limit=0,$order='',$parent_id = -1,$page=1)
    {

		$this->db->select('*');
		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');

		if($this->content_lang != '') {
			$this->db->where('content_lang', $this->content_lang); 
		}
		if($parent_id != -1 && is_numeric($parent_id)) {
			$this->db->where('parent_id', $parent_id); 
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

		$query = $this->db->get();
		return $query->result_array();

	}

    // list site map
	function site_map($parent_id=0,$mode=0,$my_lang='')
    {

		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$this->db->where('parent_id', $parent_id); 
		if($my_lang != '') {
			$this->db->where('content_lang', $my_lang); 
		}
		$query = $this->db->get();
		$pages = $query->result_array();
		
		$list = '';
		if(count($pages) > 0) {
			$list = "<ul>\r";
		}


		foreach($pages as $page) {
			$list .= "<li>";

			if($mode == 0) {
				$link = '/'.$this->get_uri_display($page['content_id'],$page['content_lang']);
				$list .= anchor($link, $page['title'], array('title' => "view '{$page['title']}'"));
			} else {
				$link = "cms/content/edit/id/".$page['content_id'];
				$link .= "/lang/".$page['content_lang'];
				$list .= anchor($link, $page['title'], array('title' => "edit '{$page['title']}'", 'class' => 'sitemap'));
			}

			$children = $this->site_map($page['content_id'],$mode,$page['content_lang']);

			if($children != "") {
				$list .=  $children;
			}
			$list .=  "</li>\r";
		}
		if(count($pages) > 0) {
			$list .= "</ul>\r";
		}
		
		return $list;
	}

    // list recent content
	function list_recent($iso_lang='eng',$object = -1,$object2 = -1,$not = FALSE,$splash=-1,$index=-1)
    {
		$this->db->select('*');
		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		if($object > 0)
		{
			if($not === TRUE)
			{
				$this->db->where('object_id !=', $object);
			}
			else
			{
				$this->db->where('object_id', $object);
			}
			if($object2 > 0)
			{
				if($not === TRUE)
				{
					$this->db->where('object_id !=', $object2);
				}
				else
				{
					$this->db->where('object_id', $object2);
				}
			}

		}

		if($splash > -1)
		{
			$this->db->where('splash',$splash);
		}
		if($index > -1)
		{
			$this->db->where('index',$index);
		}

		$this->db->where('content_lang', $iso_lang);
		$this->db->limit($this->config->item('limit_posts'));
		$this->db->order_by('created','desc');
		$query = $this->db->get();

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

    function get_content()
    {
		$this->get_content_object();
		$this->get_title();
		$this->get_content_data();
	}

    function get_content_object()
    {

		// first get data from 'content' table
		$query = $this->db->get_where('content', array('content_id' => $this->content_id));
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->parent_id = $row->parent_id;
			$this->object_id = $row->object_id;
			$this->updated = $row->updated;
			$this->created = $row->created;
			$this->user_id = $row->user_id;
			$this->splash = $row->splash;
			$this->index = $row->index;
		}

	}

    function get_title()
    {
		$this->db->select('*');
		$this->db->from('content_titles');

		if($this->title_id != '') {
			$this->db->where('title_id', $this->title_id);
		} else {
			$this->db->where('content_id', $this->content_id);
			if($this->content_lang != '') {
				$this->db->where('content_lang', $this->content_lang);
			}
		}
		
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		
		   $this->title_id = $row->title_id;
		   $this->title = $row->title;
		   $this->uri_title = $row->uri_title;
		   $this->content_lang = $row->content_lang;
		}
	}

    function get_content_data()
    {
		// get content data
		$sql = "SELECT content_data.*,
		r_object_field.validation, r_object_field.required, r_object_field.order,
		field.field_name, field.input_type
		FROM content_data LEFT JOIN
		(r_object_field LEFT JOIN field ON r_object_field.field_id = field.field_id)
		ON content_data.field_id = r_object_field.field_id
		WHERE r_object_field.object_id = {$this->object_id}
		AND content_data.content_id = ".$this->content_id;
		if($this->content_lang != '') {
			$sql .= " AND content_data.content_lang = '".$this->content_lang."'";
		}
		$sql .= "ORDER BY r_object_field.order";

	
		$query = $this->db->query($sql);
	
		$this->content_data = $query->result_array();
    }

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		if($this->content_lang == '') {
			$sql = "select count(data_id) as num, content_lang from content_data where content_lang != '' group by content_lang";
		} else {
			$sql = "select count(data_id) as num, content_lang from content_data where content_lang != '{$this->content_lang}' and content_lang != '' group by content_lang";
		}
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
			   $otherlangs[] = $row['content_lang'];
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
			'object_name' => $this->object_name,
			'user_id' => $this->user_id,
			'created' => $this->created,
			'splash' => $this->splash,
			'index' => $this->index
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
			'object_name' => $this->object_name,
			'user_id' => $this->user_id,
			'created' => $this->created,
			'splash' => $this->splash,
			'index' => $this->index
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
				'content_lang' => $this->content_lang
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
				'content_lang' => $this->content_lang
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
	function find_by_uri_parent($str,$parent_id,$zlang='')
    {

		$uri_title = $this->input->xss_clean($str);
		
		$where['parent_id'] = $parent_id;
		$where['uri_title'] = $uri_title;
		if($zlang != '') {
			$where['content_lang'] = $zlang;
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

			$title_query = $this->db->get_where('content_titles', array('content_id' => $this->content_id, 'content_lang' => $this->content_lang));
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
			'content_lang' => $this->content_lang
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
			'content_lang' => $this->content_lang
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

		if($this->content_id == '')
		{
			$sql = "SELECT * FROM content LEFT JOIN content_titles ON content.content_id = content_titles.content_id WHERE parent_id = '0'";
		} else {

			$sql = "SELECT * FROM content LEFT JOIN content_titles ON content.content_id = content_titles.content_id WHERE content_titles.content_lang = '{$this->content_lang}' AND content.content_id != ".$this->content_id." AND content.parent_id != ".$this->content_id;

		}



				
		$query = $this->db->query($sql);
		return $query->result_array();
	}


    function get_uri_display($content_id,$content_lang='')
    {

		// first get data about supplied id
		
		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$this->db->where('content.content_id', $content_id); 
		if($content_lang != '') {
			$this->db->where('content_lang', $content_lang); 
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$uriArray[] = $row->uri_title;
			$content_id = $row->parent_id;
			$content_lang = $row->content_lang;

		} 

		// now get uri path
		while ($content_id != 0)
		{

		$this->db->from('content');
		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$this->db->where('content.content_id', $content_id); 
		if($content_lang != '') {
			$this->db->where('content_lang', $content_lang); 
		}
		$query = $this->db->get();

			if ($query->num_rows() > 0)
			{
			   $row = $query->row();
			
			   $uriArray[] = $row->uri_title;
			   $content_id = $row->parent_id;
			} 

		} 
		
		$uriArray2 = array_reverse($uriArray);
		$uri = implode('/',$uriArray2);

		return $uri;

    }

    function get_uri()
    {
		
		$uriArray[] = $this->uri_title;

		$content_id = $this->parent_id;
		$content_lang = $this->content_lang;

		while ($content_id != 0)
		{

			$sql = "SELECT * FROM content LEFT JOIN content_titles ON content.content_id = content_titles.content_id 
			WHERE content.content_id = ".$content_id;
			if($content_lang != '') {
				$sql .= " AND content_lang = '{$content_lang}'";
			}
			$query = $this->db->query($sql);

			if ($query->num_rows() > 0)
			{
			   $row = $query->row();
			
			   $uriArray[] = $row->uri_title;
			   $content_id = $row->parent_id;
			} 

		} 
		
		$uriArray2 = array_reverse($uriArray);
		$uri = implode('/',$uriArray2);

		return $uri;

    }

	function get_site_creation_date()
    {

		$sql = "SELECT MIN(created) as 'site_created'  FROM content";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->site_created;
		} else {
			return FALSE;
		}

	}

}

?>