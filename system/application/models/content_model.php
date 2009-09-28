<?php
class Content_model extends MY_Model {

	// 'c_content' variables
	var $content_id, $user_id, $parent_id, $date_created, $date_end_expiry, $date_updated, $publish;

	// misc utility variables
	var $content_list = array(), $content_metadata = array(), $content_meta_fields = array();

	var $link_mode; // display links for site (0) or cms (1)
	var $lang_mode; // display language at beginning of url: No (0) or Yes ( > 0)
	var $limit; // limit number for query
	var $page; // page number number for query limit
	var $date_filter; // date range (YYYY, YYYY-MM or YYYY-MM-DD format)

	// variables for site map
	var $current_level = 1;
	var $levels = 0;

    function Content_model()
    {
        // Call the Model constructor
        parent::MY_Model();
		
		$this->link_mode = 0;
		$this->lang_mode = 0;
		$this->scope = 'content';
		$this->page = 1;
		$this->limit = 10;
    }

    // list content
	function list_content($parent = NULL, $recent = FALSE, $include_meta = FALSE)
    {

		$list = array();

		if(isset($parent))
		{
			$this->db->where('parent_id', $parent);
		}

		if($this->type_id != '')
		{
			$this->db->where('type_id', $this->type_id);
		}

		if($recent)
		{
			$this->db->order_by("date_created", "desc");
			$this->db->limit($this->limit);

			if($this->page > 1)
			{
				// page 1: 0-9
				// page 2: 10-19
				// page 3: 20-29
				
				$offset = ($this->page - 1) * $this->limit;
				
				$this->db->limit($this->limit, $offset);
			}


			if($this->link_mode == 0)
			{
				$this->db->where('publish', 1);
			}

		}
		
		$query = $this->db->get('c_content');
	
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$key = strval($row->content_id);
				$tmp = array(
				'content_id' => $row->content_id,
				'user_id' => $row->user_id,
				'parent_id' => $row->parent_id,
				'type_id' => $row->type_id,
				'type_uri_name' => $row->type_uri_name,
				'date_created' => $row->date_created,
				'date_end_expiry' => $row->date_end_expiry,
				'date_updated' => $row->date_updated,
				'publish' => $row->publish,
				'title_id' => '',
				'scope' => '',
				'title_rel_id' => '',
				'title' => '',
				'uri_title' => '',
				'subtitle' => '',
				'keywords' => '',
				'title_updated' => '',
				'lang_ISO_639_3' => '',
				);

				$this->db->where('scope', 'content');
				$this->db->where('rel_id', $row->content_id);
				$this->db->where('lang_ISO_639_3', $this->temp_lang);
				$this->db->limit(1);
				$q2 = $this->db->get('c_titles');
		
				if ($query->num_rows() > 0)
				{
				   $r2 = $q2->row();
				   
				   $tmp['title_id'] = $r2->title_id;
				   $tmp['scope'] = $r2->scope;
				   $tmp['title_rel_id'] = $r2->rel_id;
				   $tmp['title'] = $r2->title;
				   $tmp['uri_title'] = $r2->uri_title;
				   $tmp['subtitle'] = $r2->subtitle;
				   $tmp['keywords'] = $r2->keywords;
				   $tmp['title_updated'] = $r2->title_updated;
				   $tmp['lang_ISO_639_3'] = $r2->lang_ISO_639_3;
				}
				
				// if metadata included, get it
				if($include_meta)
				{
					$this->temp_id = $row->type_id;
					$this->scope = 'types';
					$this->list_fields_related();
			
					$this->scope = 'content';
					$this->temp_id = $row->content_id;
					$this->load_metadata();
					
					$tmp['metadata'] = $this->metadata;
				}

				$list[$key] = $tmp;
		   }
		}

		$this->content_list = $list;

	}

    function load_content()
    {
		$this->load_content_object();
		$this->temp_id = $this->content_id;
		$this->load_title();
		
		if($this->temp_value != '')
		{
			$this->type_id = $this->temp_value;
		}
		
		$this->temp_id = $this->type_id;
		$this->scope = 'types';
		
		$this->list_fields_related();
		$this->scope = 'content';
		$this->temp_id = $this->content_id;
		$this->load_metadata();

	}

    function load_content_form()
    {
		if($this->content_id != '')
		{
			$this->load_content_object();
			$this->temp_id = $this->content_id;
			$this->load_title();
		}

		if($this->temp_value != '')
		{
			$this->type_id = $this->temp_value;
		}
		
		if($this->type_id != '')
		{
			$this->temp_id = $this->type_id;
		}
		else
		{
			$this->temp_id = 1;
		}
		$this->scope = 'types';
		$this->list_fields_related();

		if($this->content_id != '')
		{
			$this->scope = 'content';
			$this->temp_id = $this->content_id;
			$this->load_metadata();
		}
		else
		{
			$this->metadata = $this->field_list;
		}

	}

    function load_content_object()
    {

		if ($this->content_id != '')
		{
			// first get data from 'c_content' table
			$this->db->where('content_id', $this->content_id);
			$query = $this->db->get('c_content');
	
			if ($query->num_rows() > 0)
			{
				$row = $query->row();

				$this->user_id = $row->user_id;
				$this->parent_id = $row->parent_id;
				$this->temp_id = $row->type_id;
				$this->date_created = $row->date_created;
				$this->date_end_expiry = $row->date_end_expiry;
				$this->date_updated = $row->date_updated;
				$this->publish = $row->publish;
			}

			// now get type name
			$this->scope = 'content';
			$this->load_type();
		}

	}

    // return other languages than current language
    function get_other_langs()
    {
		
		$otherlangs = array();

		if($this->temp_lang == '') {
			$sql = "select count(data_id) as num, lang_ISO_639_3 from c_metadata where lang_ISO_639_3 != '' group by lang_ISO_639_3";
		} else {
			$sql = "select count(data_id) as num, lang_ISO_639_3 from c_metadata where lang_ISO_639_3 != '{$this->temp_lang}' and lang_ISO_639_3 != '' group by lang_ISO_639_3";
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

		// first get type name
		$this->scope = 'content';
		$this->load_type();
		
		if($this->date_created == '' || $this->date_created == '0000-00-00 00:00:00')
		{
			$this->date_created = date('Y-m-d H:i:s');
		}

		$data = array(
		'user_id' => $this->user_id,
		'parent_id' => $this->parent_id,
		'type_id' => $this->type_id,
		'type_uri_name' => $this->type_uri_name,
		'date_created' => $this->date_created,
		'date_end_expiry' => $this->date_end_expiry,
		'publish' => $this->publish,
		);

		if($this->content_id == '') {
			// insert
			if($this->db->insert('c_content', $data)){
				$this->content_id = $this->db->insert_id();
				return TRUE;
			}
		} else {
			// update
			$this->db->where('content_id', $this->content_id);
			if($this->db->update('c_content', $data)){
				return TRUE;
			}
		}

		return FALSE;

    }

    function get_parents_dropdown()
    {

		$parents['0'] = "site root";

		if($this->type_id == 2)
		{
			if($this->content_id != '')
			{
				$this->db->where('c_content.content_id !=',$this->content_id);
			}
			$this->db->where('type_id',3);
	
			if($this->temp_lang != '')
			{
				$this->db->where('lang_ISO_639_3',$this->temp_lang);
			}
	
			$this->db->where('scope','content');
			$this->db->join('c_titles', 'c_content.content_id = c_titles.rel_id', 'left');
	
			$query = $this->db->get('c_content');
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
	
					$key = strval($row->content_id);
					$parents[$key] = $row->title;
			   }
			   
			}
		}
		else
		{
			if($this->content_id != '')
			{
	
				// eliminate this content and all descendants
				$children  = $this->get_descendants($this->content_id);
				foreach($children as $id)
				{
					$this->db->where('c_content.content_id !=',$id);
				}
				
				$this->db->where('parent_id !=',$this->content_id);
				$this->db->where('c_content.content_id !=',$this->content_id);
			}
			$this->db->where('type_id !=',2);
	
			if($this->temp_lang != '')
			{
				$this->db->where('lang_ISO_639_3',$this->temp_lang);
			}
	
			$this->db->where('scope','content');
			$this->db->join('c_titles', 'c_content.content_id = c_titles.rel_id', 'left');
	
			$query = $this->db->get('c_content');
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
	
					$key = strval($row->content_id);
					$parents[$key] = $row->title;
			   }
			   
			}
		}

		return $parents;
	}

    function get_parent_title()
    {

		if($this->content_id != '')
		{
			$this->db->where('c_content.content_id',$this->parent_id);
		}
		if($this->temp_lang != '')
		{
			$this->db->where('lang_ISO_639_3',$this->temp_lang);
		}

		$this->db->where('scope','content');
		$this->db->join('c_titles', 'c_content.content_id = c_titles.rel_id', 'left');

		$query = $this->db->get('c_content');
		
		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		
		   return $row->title;
		}
		return FALSE; 
	}

    // search for content by title, parent
	function find_by_uri()
    {

		$this->db->where('uri_title',$this->temp_value);
		$this->db->where('publish',1);

		if($this->temp_id != '')
		{
			$this->db->where('parent_id',$this->temp_id);
		}		

		if($this->temp_lang != '')
		{
			$this->db->where('lang_ISO_639_3',$this->temp_lang);
		}		

		$this->db->where('scope','content');
		$this->db->join('c_titles', 'c_content.content_id = c_titles.rel_id', 'left');

		$query = $this->db->get('c_content');

		if ($query->num_rows() > 0) {
			return $query->result_array();
		}

		return FALSE;
	}

	function get_descendants($parent = 0)
	{
		$descendants = array();

		$this->db->where('parent_id', $parent);
		$this->db->where('content_id !=', $parent);
		
		$query = $this->db->get('c_content');
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$descendants[] = $row->content_id;
				$children = $this->get_descendants($row->content_id);
				$descendants = array_merge($descendants,$children);
		   }
		}
		
		return $descendants;

	}

	function site_map()
	{
		$this->scope = 'content';

		$site_map = "";
		
		if($this->temp_id == '')
		{
			$this->temp_id = 0;
		}

		$this->db->where('type_id !=', 2);
		$this->db->where('parent_id', $this->temp_id);

		if($this->link_mode == 0)
		{
			$this->db->where('publish', 1);
		}
		$query = $this->db->get('c_content');

		if ($query->num_rows() > 0)
		{

			foreach ($query->result_array() as $row)
			{
				
				$this->db->where('scope', $this->scope);
				$this->db->where('rel_id', $row['content_id']);
				$this->db->where('lang_ISO_639_3', $this->temp_lang);
				$this->db->limit(1);
				$q2 = $this->db->get('c_titles');
				$r2 = $q2->row_array();

				if ($q2->num_rows() > 0)
				{
					if($this->link_mode == 1)
					{
						$link = 'cms/content/edit/id/'.$row['content_id'].'/lang/'.$r2['lang_ISO_639_3'];
					}
					else
					{
						$this->temp_id = $row['content_id'];
						$this->temp_lang = $r2['lang_ISO_639_3'];
						$link = $this->generate_uri();
					}
					
					$site_map .= '<li>'.anchor($link,$r2['title']);
					
					if( ($this->levels > 0 && $this->current_level <= $this->levels) || $this->levels == -1 )
					{
						$this->temp_id = $row['content_id'];
						$this->current_level = $this->current_level + 1;
						$site_map .= $this->site_map();
					}
					$site_map .= "</li>\r";
				}

			}

 		}
		
		if($site_map != '')
		{
			$site_map = "<ul>\r".$site_map."</ul>\r";
		}

		return $site_map;

	}

    function generate_uri()
    {
		$uriArray = array();
		// get content info
		$this->db->where('content_id', $this->temp_id);
		$query = $this->db->get('c_content');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

			$cid = $row->content_id;
			$parent = $row->parent_id;
		}
		// get uri title
		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->temp_id);
		$this->db->where('lang_ISO_639_3', $this->temp_lang);
		$this->db->limit(1);
		$query = $this->db->get('c_titles');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$uriArray[] = $row->uri_title;
		}

		//now loop through to top level		
		do
		{
			$this->db->where('content_id', $parent);
			$this->db->where('lang_ISO_639_3', $this->temp_lang);
			$this->db->where('scope','content');
			$this->db->join('c_titles', 'c_content.content_id = c_titles.rel_id', 'left');
			$query = $this->db->get('c_content');
			if ($query->num_rows() > 0)
			{
			   $row = $query->row();
				$uriArray[] = $row->uri_title;
				$parent = $row->parent_id;
			}

		} while ($parent > 0);

		$uriArray2 = array_reverse($uriArray);

		$uri = '';

		if($this->lang_mode > 1) {
			$uri .= $this->temp_lang.'/';
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

    // search for content
	function search($str,$keyword = FALSE)
    {
		$match = $this->input->xss_clean($str);
		$scores = array();

		if($keyword)
		{
			$sql_1 = "SELECT content_id, COUNT(content_id) AS score
			FROM c_metadata 
			WHERE value REGEXP '[[:<:]]{$match}[[:>:]]'
			AND field_name = 'keywords' GROUP BY content_id";
			
		}
		else
		{
			// first match values
			$sql_1 = "SELECT content_id, AVG(MATCH(`value`) AGAINST ('{$match}')) * 4 AS score
			FROM c_metadata
			WHERE MATCH(`value`) AGAINST('{$match}')";
	
			if($this->temp_lang != '')
			{
				$my_lang = $this->temp_lang;
				$sql_1 .= " AND lang_ISO_639_3 = '{$this->temp_lang}'";
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
			$sql_2 = "SELECT content_id, COUNT(content_id) AS score FROM c_titles WHERE title LIKE '%{$match}%'";
			if($this->temp_lang != '')
			{
				$sql_2 .= " AND lang_ISO_639_3 = '{$this->temp_lang}'";
			}
			$sql_2 .= " GROUP BY content_id";
			
			$sql_3 = "SELECT content_id, COUNT(content_id) AS score FROM c_titles WHERE subtitle LIKE '%{$match}%'";
			if($this->temp_lang != '')
			{
				$sql_3 .= " AND lang_ISO_639_3 = '{$this->temp_lang}'";
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
		
				if($this->temp_lang != '') {
					$this->db->where('lang_ISO_639_3', $this->temp_lang);
				}
				$this->db->limit(1);
				
				$query = $this->db->get('c_titles');
		
				if ($query->num_rows() > 0)
				{
					$row = $query->row();
					$this->temp_lang = $row->lang_ISO_639_3;
				   
				   
					if($this->link_mode == 1)
					{
					$link = 'cms/content/edit/id/'.$id.'/lang/'.$row->lang_ISO_639_3;
					}
					else
					{
						$this->content_id = $id;
						$this->content_title_id = $row->content_title_id;
						$link = $this->generate_uri();
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

    function list_type_meta_fields()
    {
		$this->list_fields();
		$field_list_full = $this->field_list;
		
		$this->scope = 'types';
		$this->temp_id = $this->type_id;
		$this->list_fields_related();
		$field_list_related = $this->field_list;

		$field_list_final = array();
		foreach ($field_list_full as $index => $fd)
		{
			
			$key = $fd['field_id'];
			$temp_field = array(
			'field_id' => $fd['field_id'],
			'field_ref_name' => $fd['field_ref_name'],
			'field_input_type' => $fd['field_input_type'],
			'field_validation' => $fd['field_validation'],
			'field_needs_lang' => $fd['field_needs_lang'],
			'rf_id' => '',
			'required' => '',
			'order' => ''
			);
			
			$field_list_final["$key"] = $temp_field;

		}

		foreach ($field_list_related as $index => $fd)
		{
			$field_list_final[$index]['rf_id'] = $fd['rf_id'];
			$field_list_final[$index]['required'] = $fd['required'];
			$field_list_final[$index]['order'] = $fd['order'];
		}
		$this->field_list = $field_list_final;
		
	}

	function new_content_dropdown()
    {
		$this->scope = 'content';
		$this->list_types();
		$list = array();
		foreach($this->type_list as $row)
		{
			$type_url = site_url('cms/content/edit/type/'.$row['type_id']);
			if($row['label'] != '')
			{
				$list[$type_url] = $row['label'];
			} else {
				$list[$type_url] = $row['type_uri_name'];
			}
		}

		return $list;
	}

}

?>