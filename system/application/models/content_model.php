<?php
class Content_model extends MY_Model {

	// 'content' variables
	var $content_id, $user_id, $parent_id, $type_id, $type_name, $publish_status, $splash, $index, $date_1, $date_2, $date_updated;
	
	// 'content_meta' content by language variables
	var $content_meta_id, $field_id, $field_name, $value, $content_lang, $meta_updated;
	
	// 'content_titles' title variables
	var $content_title_id, $title, $uri_title, $subtitle, $title_updated;

	// misc utility variables
	var $content_list;

    function Content_model()
    {
        // Call the Model constructor
        parent::MY_Model();
    }

    // list content
	function list_content($limit=0,$order='',$dir='desc',$parent_id = -1,$page=1)
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
		if($order != '') {
			$this->db->order_by($order,$dir);
		}

		$query = $this->db->get();
		$this->content_list = $query->result_array();

	}

    // list site map
	function site_map($mode=1,$limit=0,$parent_id = -1)
    {

		$this->list_content($limit,'title','asc',$parent_id);
		$pages = $this->content_list;

		$list = "";
		if(count($pages) > 0) {
			$list .= "<ul>\r";
		}
		foreach($pages as $page) {
			$list .= "<li>";

			if($mode != 1) {
				$link = '/'.$this->get_uri_display($page['content_id'],$page['content_lang']);
				$list .= anchor($link, $page['title'], array('title' => "view '{$page['title']}'"));
			} else {
				$link = "cms/content/edit/id/".$page['content_id'];
				$link .= "/lang/".$page['content_lang'];
				$list .= anchor($link, $page['title'], array('title' => "edit '{$page['title']}'", 'class' => 'sitemap'));
			}

			$children = $this->site_map($mode,$limit,$page['content_id']);

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

    // search for content
	function search($str)
    {
		$match = $this->input->xss_clean($str);

		$sql = "SELECT `content_id`, AVG( MATCH(`value`) AGAINST ('{$match}') ) AS `score` 
		FROM `content_meta` 
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
			$this->user_id = $row->user_id;
			$this->parent_id = $row->parent_id;
			$this->type_id = $row->type_id;
			$this->type_name = $row->type_name;
			$this->publish_status = $row->publish_status;
			$this->splash = $row->splash;
			$this->index = $row->index;
			$this->date_1 = $row->date_1;
			$this->date_2 = $row->date_2;
			$this->date_updated = $row->date_updated;
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
		   $this->subtitle = $row->subtitle;
		   $this->content_lang = $row->lang_ISO_639_3;
		   $this->title_updated = $row->date_updated;
		}
	}

    function get_content_data()
    {
		// get content data
		$sql = "SELECT content_meta.*,
		r_types_fields.validation, r_types_fields.required, r_types_fields.order,
		field.field_name, field.input_type
		FROM content_meta LEFT JOIN
		(r_types_fields LEFT JOIN field ON r_types_fields.field_id = fields.field_id)
		ON content_meta.field_id = r_types_fields.field_id
		WHERE r_types_fields.type_id = {$this->type_id}
		AND content_meta.content_id = ".$this->content_id;
		if($this->content_lang != '') {
			$sql .= " AND content_meta.content_lang = '".$this->content_lang."'";
		}
		$sql .= "ORDER BY r_types_fields.order";

	
		$query = $this->db->query($sql);
	
		$this->content_meta = $query->result_array();
    }

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		if($this->content_lang == '') {
			$sql = "select count(data_id) as num, content_lang from content_meta where content_lang != '' group by content_lang";
		} else {
			$sql = "select count(data_id) as num, content_lang from content_meta where content_lang != '{$this->content_lang}' and content_lang != '' group by content_lang";
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
		$data = array(
		'user_id' => $this->user_id,
		'parent_id' => $this->parent_id,
		'type_id' => $this->type_id,
		'type_name' => $this->type_name,
		'publish_status' => $this->publish_status,
		'splash' => $this->splash,
		'index' => $this->index,
		'date_1' => $this->date_1,
		'date_2' => $this->date_2,
		'date_updated' => $this->date_updated
		);

		if($this->content_id == '') {
			// insert
			if($this->db->insert('content', $data)){
				$this->content_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
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
		$data = array(
		'content_id' => $this->content_id,
		'field_id' => $this->field_id,
		'value' => $this->value,
		'lang_ISO_639_3' => $this->content_lang
		);

		if($content_meta_id == '') {

			if($value != '') {
				// insert
				if($this->db->insert('content_meta', $data)){
					return TRUE;
				}
			} else {
				return TRUE;
			}
			
		} else {

			$this->db->where('content_meta_id', $this->content_meta_id);
			if($value == '') {
				// delete
				if($this->db->delete('content_meta')){
					return TRUE;
				}

			} else {
				// update
				if($this->db->update('content_meta', $data)){
					return TRUE;
				}
			}

		}

		return FALSE;

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

		$data = array(
		'content_id' => $this->content_id,
		'title' => $this->title,
		'uri_title' => convert_uri($this->title),
		'subtitle' => $this->subtitle,
		'lang_ISO_639_3' => $this->content_lang
		);

		// update or insert title
		if($this->title_id == '') {
			// insert
			if($this->db->insert('content_titles', $data)){
				$this->title_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$this->db->where('title_id', $this->title_id);
			if($this->db->update('content_titles', $data)){
				return TRUE;
			}

		}
		return FALSE;

    }

    // return list of possible parents
    function get_parents()
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

    function get_parents_dropdown()
    {

		$parents = $this->get_parents();

		$list[0] = "site root";
		
		if(is_array($parents) && count($parents) > 0) {
			foreach($parents as $row) {
				if(array_key_exists('title',$row) && $row['title'] != '') {
					$pname = $row['title'];
				} else {
					$pname = $row['content_id'];
				}

				$list[$row['content_id']] = $pname;
			}
		}

		return $list;
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