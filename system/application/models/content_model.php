<?php
class Content_model extends MY_Model {

	// 'content' variables
	var $content_id, $user_id, $parent_id, $type_id, $type_name, $publish_status, $splash, $index, $date_1, $date_2, $date_updated;
	
	// 'content_titles' title variables
	var $content_title_id, $content_lang, $title, $uri_title, $subtitle, $title_updated;

	// misc utility variables
	var $content_list = array(), $content_metadata = array(), $content_meta_fields = array();

	var $link_mode; // display links for site (0) or cms (1)
	var $lang_mode; // display language at beginning of url: No (0) or Yes ( > 0)
	var $limit; // limit number for query
	var $page; // page number number for query limit

    function Content_model()
    {
        // Call the Model constructor
        parent::MY_Model();
		
		$this->link_mode = 0;
		$this->lang_mode = 0;
    }

    // list content
	function list_content($parent = NULL,$recent = FALSE)
    {

		$list = array();

		if(isset($parent) && $parent != '')
		{
			$this->db->where('parent_id', $parent);
		}

		if($recent)
		{
			$this->db->order_by("date_1", "desc");
			$this->db->limit(10);

			if($this->link_mode == 0)
			{
				$this->db->where('publish_status', 1);
			}

		}
		
		$query = $this->db->get('content');
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$tmp = array(
				'content_id' => $row->content_id,
				'user_id' => $row->user_id,
				'parent_id' => $row->parent_id,
				'type_id' => $row->type_id,
				'type_name' => $row->type_name,
				'publish_status' => $row->publish_status,
				'splash' => $row->splash,
				'index' => $row->index,
				'date_1' => $row->date_1,
				'date_2' => $row->date_2,
				'date_updated' => $row->date_updated,
				'content_title_id' => '',
				'content_lang' => '',
				'title' => '',
				'uri_title' => '',
				'subtitle' => '',
				);

				$this->db->where('content_id', $row->content_id);
				if($this->content_lang != '')
				{
					$this->db->where('lang_ISO_639_3', $this->content_lang);
				}
				$this->db->limit(1);
				
				$q2 = $this->db->get('content_titles');

				if ($q2->num_rows() > 0)
				{
					$r2 = $q2->row();
					$tmp['content_title_id'] = $r2->content_title_id;
					$tmp['content_lang'] = $r2->lang_ISO_639_3;
					$tmp['title'] = $r2->title;
					$tmp['uri_title'] = $r2->uri_title;
					$tmp['subtitle'] = $r2->subtitle;
				} 

				$list["{$row->content_id}"] = $tmp;
		   }
		}

		$this->content_list = $list;

	}


    // search for content
	function search($str,$keyword = FALSE)
    {
		$match = $this->input->xss_clean($str);
		$scores = array();

		if($keyword)
		{
			$sql_1 = "SELECT content_id, COUNT(content_id) AS score
			FROM content_meta 
			WHERE value REGEXP '[[:<:]]{$match}[[:>:]]'
			AND field_name = 'keywords' GROUP BY content_id";
			
		}
		else
		{
			// first match values
			$sql_1 = "SELECT content_id, AVG(MATCH(`value`) AGAINST ('{$match}')) * 4 AS score
			FROM content_meta
			WHERE MATCH(`value`) AGAINST('{$match}')";
	
			if($this->content_lang != '')
			{
				$my_lang = $this->content_lang;
				$sql_1 .= " AND lang_ISO_639_3 = '{$this->content_lang}'";
			}
			$sql_1 .= " GROUP BY content_id";
		}

		$query = $this->db->query($sql_1);
		
		if ($query->num_rows() > 0)
		{

			foreach ($query->result() as $row)
			{
				$key = strval($row->content_id);
				$scores[$key] = $row->score;
			}
		} 		


		if(!$keyword)
		{
			$sql_2 = "SELECT content_id, COUNT(content_id) AS score FROM content_titles WHERE title LIKE '%{$match}%'";
			if($this->content_lang != '')
			{
				$sql_2 .= " AND lang_ISO_639_3 = '{$this->content_lang}'";
			}
			$sql_2 .= " GROUP BY content_id";
			
			$sql_3 = "SELECT content_id, COUNT(content_id) AS score FROM content_titles WHERE subtitle LIKE '%{$match}%'";
			if($this->content_lang != '')
			{
				$sql_3 .= " AND lang_ISO_639_3 = '{$this->content_lang}'";
			}
			$sql_3 .= " GROUP BY content_id";
	
			$query = $this->db->query($sql_2);
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$key = strval($row->content_id);
					$tmp_score = $row->score;
					
					if(array_key_exists($key,$scores))
					{
						$tmp_score += $scores[$key];
					}
					$scores[$key] = $tmp_score;
				}
			} 		
	
			$query = $this->db->query($sql_3);
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$key = strval($row->content_id);
					$tmp_score = $row->score;
					
					if(array_key_exists($key,$scores))
					{
						$tmp_score += $scores[$key];
					}
					$scores[$key] = $tmp_score;
				}
			}

		}

		arsort($scores);
		
		$result = '';
		if (count($scores) > 0)
		{
			foreach($scores as $id => $score)
			{

				$this->db->where('content_id', $id);
		
				if($this->content_lang != '') {
					$this->db->where('lang_ISO_639_3', $this->content_lang);
				}
				$this->db->limit(1);
				
				$query = $this->db->get('content_titles');
		
				if ($query->num_rows() > 0)
				{
					$row = $query->row();
					$this->content_lang = $row->lang_ISO_639_3;
				   
				   
					if($this->link_mode == 1)
					{
					$link = 'cms/content/edit/id/'.$id.'/lang/'.$row->lang_ISO_639_3;
					}
					else
					{
						$this->content_id = $id;
						$this->content_title_id = $row->content_title_id;
						$link = $this->_generate_uri();
					}
					
					$tmp_item = anchor($link,$row->title);
					$results[] = $tmp_item;
				}


			}
			$result = ul($results);
		}
		else
		{
			$result = 'no results found';
		}

		return $result;		
	}

    function get_content()
    {
		$this->get_content_object();
		$this->get_title();
		$this->get_content_metadata();
	}

    function get_content_object()
    {

		if ($this->content_id != '')
		{
			// first get data from 'content' table
			$query = $this->db->where('content_id', $this->content_id);
			$query = $this->db->get('content');
	
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

	}

    function get_title()
    {

		if ($this->content_id != '')
		{
			$this->db->where('content_id', $this->content_id);
	
			if($this->content_title_id != '') {
				$this->db->where('content_title_id', $this->content_title_id);
			}
			
			if($this->content_lang != '') {
				$this->db->where('lang_ISO_639_3', $this->content_lang);
			}
			$this->db->limit(1);
			
			$query = $this->db->get('content_titles');
	
			if ($query->num_rows() > 0)
			{
			   $row = $query->row();
			   $this->content_title_id = $row->content_title_id;
			   $this->title = $row->title;
			   $this->uri_title = $row->uri_title;
			   $this->subtitle = $row->subtitle;
			   $this->content_lang = $row->lang_ISO_639_3;
			   $this->title_updated = $row->date_updated;
			}
		}

	}

    function get_content_meta_fields()
    {
		$this->db->where('type_id', $this->type_id);
		$this->db->order_by("order", "asc"); 
		$this->db->join('fields', 'r_types_fields.field_id = fields.field_id', 'left');
		$query = $this->db->get('r_types_fields');

		$fields = array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{

				$temp_field = array(
				'rel_id' => $row->rel_id,
				'field_id' => $row->field_id,
				'required' => $row->required,
				'order' => $row->order,
				'field_ref_name' => $row->field_ref_name,
				'field_input_type' => $row->field_input_type,
				'field_validation' => $row->field_validation,
				'field_needs_lang' => $row->field_needs_lang,
				'field_name' => '',
				'content_meta_id' => '',
				'content_lang' => '',
				'value' => '',
				'date_updated' => ''
				);
			
				// GET FIELD NAME
				$this->db->where('field_id', $row->field_id);
				if($this->content_lang != '')
				{
					$this->db->where('lang_ISO_639_3', $this->content_lang);
				}
				$this->db->limit(1); 
				$q2 = $this->db->get('fields_meta');
				
				if ($q2->num_rows() > 0)
				{
					$r2 = $q2->row();
					$temp_field['field_name'] = $r2->field_name;
				} 
				
				$key = $row->field_id;
				$fields["$key"] = $temp_field;
			}
		}
		$this->content_meta_fields = $fields;
	}

    function get_content_metadata()
	{
		$this->get_content_meta_fields();

		$fields = $this->content_meta_fields;
		
		if ($this->content_id != '')
		{
			$sql = "SELECT * FROM content_meta WHERE content_id = ".$this->content_id;
			if ($this->content_lang != '')
			{
				$sql .= " AND (lang_ISO_639_3 = '{$this->content_lang}' OR lang_ISO_639_3 = '')";
			}
			else
			{
				$sql .= " AND lang_ISO_639_3 = ''";
			}
	
			$query = $this->db->query($sql);
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$key = $row->field_id;
					$fields["$key"]['value'] = $row->value;
					$fields["$key"]['content_meta_id'] = $row->content_meta_id;
					$fields["$key"]['content_lang'] = $row->lang_ISO_639_3;
					$fields["$key"]['date_updated'] = $row->date_updated;
				}
			}
		}

		$this->content_metadata = $fields;

    }

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		if($this->content_lang == '') {
			$sql = "select count(data_id) as num, lang_ISO_639_3 from content_meta where lang_ISO_639_3 != '' group by lang_ISO_639_3";
		} else {
			$sql = "select count(data_id) as num, lang_ISO_639_3 from content_meta where lang_ISO_639_3 != '{$this->content_lang}' and lang_ISO_639_3 != '' group by lang_ISO_639_3";
		}
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
			   $otherlangs[] = $row['lang_ISO_639_3'];
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

    // update metadata
    function update_metadata()
    {

		foreach($this->content_metadata as $meta)
		{

			$db_fields = array(
			'content_id' => $this->content_id,
			'field_id' => $meta['field_id'],
			'field_name' => $meta['field_ref_name'],
			'value' => $meta['value'],
			'lang_ISO_639_3' => '',
			);

			if($meta['field_needs_lang'] == 1)
			{
				$db_fields['lang_ISO_639_3'] = $this->content_lang;
			}
	
			if($meta['content_meta_id'] == '')
			{
				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// create new metadata
					if(!$this->db->insert('content_meta', $db_fields) )
					{
						$this->messages[] = "could not insert metadata '{$meta['field_name']}'";
					}
				}
				
				
	
			}
			else
			{

				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// update metadata
					if(!$this->db->update('content_meta', $db_fields, array('content_meta_id' => $meta['content_meta_id'])) ) {
						$this->messages[] = "could not update metadata '{$meta['field_name']}'";
					}
				}
				else
				{
					// delete metadata
					if(!$this->db->delete('content_meta', array('content_meta_id' => $meta['content_meta_id'])) ) {
						$this->messages[] = "could not delete metadata '{$meta['field_name']}'";
					}
				}

			}
		
		}

    }

    // update title
    function update_title()
    {

		// try to find existing title id
		if($this->content_title_id == '') {

			$title_query = $this->db->get_where('content_titles', array('content_id' => $this->content_id, 'lang_ISO_639_3' => $this->content_lang));
			if ($title_query->num_rows() > 0)
			{
			   $trow = $title_query->row();
			   $this->content_title_id = $trow->content_title_id;
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
		if($this->content_title_id == '') {
			// insert
			if($this->db->insert('content_titles', $data)){
				$this->content_title_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$this->db->where('content_title_id', $this->content_title_id);
			if($this->db->update('content_titles', $data)){
				return TRUE;
			}

		}
		return FALSE;

    }

    function get_parents_dropdown()
    {

		$parents['0'] = "site root";

		if($this->content_id != '')
		{
			$this->db->where('parent_id !=',$this->content_id);
			$this->db->where('content.content_id !=',$this->content_id);

			// eliminate this content and all descendants
			$children  = $this->get_descendants($this->content_id);
			foreach($children as $id)
			{
				$this->db->where('content.content_id !=',$id);
			}
		}

		if($this->content_lang != '')
		{
			$this->db->where('lang_ISO_639_3',$this->content_lang);
		}

		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$query = $this->db->get('content');
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{

				$key = strval($row->content_id);
				$parents[$key] = $row->title;
		   }
		   
		}

		return $parents;
	}

    // search for content by title, parent
	function find_by_uri()
    {

		$this->db->where('uri_title',$this->uri_title);
		$this->db->where('publish_status',1);

		if($this->parent_id != '')
		{
			$this->db->where('parent_id',$this->parent_id);
		}		

		if($this->content_lang != '')
		{
			$this->db->where('lang_ISO_639_3',$this->content_lang);
		}		

		$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');
		$this->db->limit(1);
		$title_query = $this->db->get('content');

		if ($title_query->num_rows() > 0)
		{
		   $row = $title_query->row();
		   return $row;
		} 
		
		return FALSE;
	}

	function get_descendants($parent = '0')
	{

		$this->list_content($parent);

		$descendants = array();
		
		foreach($this->content_list as $content)
		{
			$descendants[] = $content['content_id'];

			$children = $this->get_descendants($content['content_id']);
			$descendants = array_merge($descendants,$children);
			
		}
		
		return $descendants;

	}

	function site_map($parent = 0,$current_level = 1,$levels = 0)
	{
		$site_map = "";

		$this->db->where('parent_id', $parent);
		if($this->link_mode == 0)
		{
			$this->db->where('publish_status', 1);
		}
		$query = $this->db->get('content');
		if ($query->num_rows() > 0)
		{

			foreach ($query->result_array() as $row)
			{
				
				$this->db->where('content_id', $row['content_id']);
				if($this->content_lang != '')
				{
					$this->db->where('lang_ISO_639_3', $this->content_lang);
				}
				$this->db->limit(1);
				$q2 = $this->db->get('content_titles');
				$r2 = $q2->row_array();

				if($this->link_mode == 1)
				{
					$link = 'cms/content/edit/id/'.$row['content_id'].'/lang/'.$r2['lang_ISO_639_3'];
				}
				else
				{
					$this->content_id = $row['content_id'];
					$this->content_title_id = $r2['content_title_id'];
					$this->content_lang = $r2['lang_ISO_639_3'];
					$link = $this->_generate_uri();
				}
				
				$site_map .= '<li>'.anchor($link,$r2['title']);
				
				if($levels > 0 && $current_level <= $levels)
				{
					$site_map .= $this->site_map($row['content_id'],$current_level + 1,$levels);
				}
				$site_map .= "</li>\r";

					
				
			}

 		}
		
		if($site_map != '')
		{
			$site_map = "<ul>\r".$site_map."</ul>\r";
		}
		
		return $site_map;

	}

	function list_recent_content()
	{

		$this->list_content('',TRUE);

		$recent = array();
		
		foreach($this->content_list as $content)
		{
			$this->content_lang = $content['content_lang'];
			
			if($this->link_mode == 1)
			{
				$link = 'cms/content/edit/id/'.$content['content_id'].'/lang/'.$content['content_lang'];
			}
			else
			{
				$link = $this->_generate_uri($content['content_id']);
			}
			
			$tmp_item = anchor($link,$content['title']);

			if($this->link_mode === 1 || ($content['index'] == 1 && $content['publish_status'] == 1) )
			{
				$recent[] = $tmp_item;
			}

		}
		
		$site_map = '';
		if(count($recent) > 0 )
		{
			$site_map = ul($recent);
		}
		
		return $site_map;

	}

    function _generate_uri()
    {

		$this->get_content_object();
		$this->get_title();
		
		$uriArray[] = $this->uri_title;

		$content_id = $this->parent_id;
		
		while ($content_id > 0)
		{

			$this->db->where('content.content_id', $content_id);
			$this->db->where('lang_ISO_639_3', $this->content_lang);
			$this->db->join('content_titles', 'content.content_id = content_titles.content_id', 'left');			
			$query = $this->db->get('content');

			if ($query->num_rows() > 0)
			{
			   $row = $query->row();
			
			   $uriArray[] = $row->uri_title;
			   $content_id = $row->parent_id;
			} 

		} 
		
		$uriArray2 = array_reverse($uriArray);

		$uri = '';

		if($this->lang_mode > 1) {
			$uri .= $this->content_lang.'/';
		}
		$uri .= implode('/',$uriArray2);

		return $uri;

    }

	// generate keyword links
	function keyword_links($str)
	{
		if($this->link_mode == 1)
		{
			$base_url = "cms/search/";
		}
		else
		{
			$base_url = "search/k/";
		}

		$kwds = explode(',',$str);
		$links = array();
		foreach ($kwds as $tag)
		{
			$keyword = trim($tag);
			$url = $base_url.$keyword;
			$links[] = anchor($url,$keyword);
			
		}
		$keyword_links = implode(', ',$links);
		
		return $keyword_links;

	}

	function get_parent_title()
	{
		$parent_title = '';
		
		$this->db->where('content_id', $this->parent_id);

		if($this->content_lang != '') {
			$this->db->where('lang_ISO_639_3', $this->content_lang);
		}
		$this->db->limit(1);
		
		$query = $this->db->get('content_titles');

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		   $parent_title = $row->title;
		}
	   return $parent_title;

	}

}

?>