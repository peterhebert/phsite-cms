<?php
class Content_model extends MY_Model {

	// 'c_content' variables
	var $content_id, $user_id, $parent_id, $date_created, $date_end_expiry, $date_updated, $publish;

	// list variables
	var $content_list = array(), $content_metadata = array(), $content_meta_fields = array(), $attachments = array();

	// misc utility variables
	var $link_mode; // display links for site (0) or cms (1)
	var $lang_mode; // display language at beginning of url: No (0) or Yes ( > 0)

	// variables for site map
	var $current_level = 1;
	var $levels = 0;
	var $content_count = 0;
	var $omit_id = NULL;

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
	function list_content($include_meta = FALSE)
    {
		// first get total matching results
		if($this->parent_id != '')
		{
			$this->db->where('parent_id', $this->parent_id);
		}
		if($this->type_id != '')
		{
			$this->db->where('type_id', $this->type_id);
		}
		if($this->link_mode == 0)
		{
			$this->db->where('publish', 1);
		}
		if($this->date_filter != '')
		{
			$this->db->like('date_created', $this->date_filter, 'after'); 
		}
		
		$query = $this->db->get('c_content');
		$this->content_count = $query->num_rows();	
		
		// then get content with limits
		$query->free_result();

		$list = array();

		if($this->parent_id != '')
		{
			$this->db->where('parent_id', $this->parent_id);
		}

		if($this->type_id != '')
		{
			$this->db->where('type_id', $this->type_id);
		}

		if($this->limit != '')
		{
			$this->db->order_by("date_created", "desc");
			$this->db->limit($this->limit);

			if($this->page > 1)
			{
				$offset = ($this->page - 1) * $this->limit;
				
				$this->db->limit($this->limit, $offset);
			}

		}

		if($this->link_mode == 0)
		{
			$this->db->where('publish', 1);
		}
		if($this->date_filter != '')
		{
			$this->db->like('date_created', $this->date_filter, 'after'); 
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
				'type_label' => '',
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
		
				if ($q2->num_rows() > 0)
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
				
				$q2->free_result();

				// get content type label
				$this->db->where('s_types.scope', 'content');
				$this->db->where('s_types.type_id', $row->type_id);
				$this->db->where('s_labels.scope', 'types');
				$this->db->where('s_labels.lang_ISO_639_3', $this->temp_lang);
				$this->db->limit(1);
				$this->db->join('s_labels', 's_types.type_id = s_labels.rel_id', 'left');

				$q2 = $this->db->get('s_types');
		
				if ($q2->num_rows() > 0)
				{
				   $r2 = $q2->row();
				   $tmp['type_label'] = $r2->label;
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

    function content_exists()
    {

		if ($this->content_id != '')
		{
			// first get data from 'c_content' table
			$this->db->where('content_id', $this->content_id);
			$query = $this->db->get('c_content');
	
			if ($query->num_rows() > 0)
			{
				return TRUE;
			}
		}
		return FALSE;
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

    function get_other_lang_ids()
    {
		
		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->content_id);
		$this->db->where('lang_ISO_639_3 !=', $this->temp_lang);
		$query = $this->db->get('c_titles');
		
		if ($query->num_rows() > 0)
		{
			$otherlangs = array();

			foreach ($query->result_array() as $row)
			{
				$this->temp_id = $row['rel_id'];
				$this->temp_lang = $row['lang_ISO_639_3'];

				$this->lang_load();

				$tmp_uri = $this->generate_uri();

				$tmp_langinfo = array(
				'title_id' => $row['title_id'],
				'scope' => $row['scope'],
				'rel_id' => $row['rel_id'],
				'title' => $row['title'],
				'uri_title' => $row['uri_title'],
				'subtitle' => $row['subtitle'],
				'keywords' => $row['keywords'],
				'lang_ISO_639_3' => $row['lang_ISO_639_3'],
				'lang_Ref_Name' => $this->lang_Ref_Name,
				'lang_Inverted_Name' => $this->lang_Inverted_Name,
				'lang_Native_Name' => $this->lang_Native_Name,
				'title_updated' => $row['title_updated'],
				'uri' => $tmp_uri
				);

				$otherlangs[] = $tmp_langinfo;
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

	function get_descendant_content($parent = 0)
	{
		$descendants = array();

		$this->db->where('parent_id', $parent);
		$this->db->where('content_id !=', $parent);

		$query = $this->db->get('c_content');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$this->reset_vars();
				$info = array(
				'content_id' => $row->content_id,
				'user_id' => $row->user_id,
				'parent_id' => $row->parent_id,
				'type_id' => $row->type_id,
				'type_uri_name' => $row->type_uri_name,
				'date_created' => $row->date_created,
				'date_end_expiry' => $row->date_end_expiry,
				'date_updated' => $row->date_updated,
				'publish' => $row->publish
                );


				if($this->link_mode == 1)
				{
					$link = 'cms/content/edit/id/'.$row->content_id.'/lang/'.$this->lang_ISO_639_3;
				}
				else
				{
					$this->temp_id = $row->content_id;
					$link = $this->generate_uri();
				}

				$info['uri'] = $link;

				$this->temp_id = $row->content_id;
				$this->load_title();
				$info['title_id'] = $this->title_id;
				$info['title_rel_id'] = $this->title_rel_id;
				$info['title'] = $this->title;
				$info['uri_title'] = $this->uri_title;
				$info['subtitle'] = $this->subtitle;
				$info['keywords'] = $this->keywords;
				$info['title_updated'] = $this->title_updated;
				
				$this->temp_id = $row->type_id;
				$this->scope = 'types';
				
				$this->list_fields_related();
				$this->scope = 'content';
				$this->temp_id = $this->content_id;
				$this->load_metadata();
				
				foreach($this->metadata as $field)
				{
					$info[$field['field_ref_name']] = encode_entities($field['value']);
				}

				$info['children'] = $this->get_descendant_content($row->content_id);
				$descendants[] = $info;
			}
		}
		
		return $descendants;

	}

    function generate_uri()
    {
		if($this->link_mode == 1)
		{
			// edit uri
			$uri = 'cms/content/edit/id/'.$this->temp_id;
			if($this->temp_lang != '')
			{
				$uri .= '/lang/'.$this->temp_lang;
			}
		
		}
		else
		{
			$uriArray = array();
			// get content info
			$this->count_lang_versions();
			$this->db->where('content_id', $this->temp_id);
			$query = $this->db->get('c_content');

			$parent = 0;
	
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
	
				$cid = $row->content_id;
				$parent = $row->parent_id;
				$type_id = $row->type_id;
				$date_segment = substr($row->date_created, 0, 10);
			}
			// get uri title
			$this->db->where('scope', $this->scope);
			$this->db->where('rel_id', $this->temp_id);
			$this->db->where('lang_ISO_639_3', $this->temp_lang);
			$this->db->limit(1);
			$query = $this->db->get('c_titles');
	
			if ($query->num_rows() > 0)
			{
				$row = $query->row_array();
				$uriArray[] = $row['uri_title'];
				
				if($type_id == 2)
				{
					$uriArray[] = $date_segment;
				}
	
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
					$row = $query->row_array();
					$uriArray[] = $row['uri_title'];	
					$parent = $row['parent_id'];
				}
	
			} while ($parent > 0);
	

			$uriArray2 = array_reverse($uriArray);
	
			$uri = '';
	
			if($this->lang_mode > 1) {
				$uri .= $this->temp_lang.'/';
			}
			$uri .= implode('/',$uriArray2);

		}
		
		return $uri;

    }

	// generate keyword links
	function keyword_links()
	{
		if($this->keywords != '')
		{
			if($this->link_mode == 1)
			{
				$base_url = "cms/search/";
			}
			else
			{
				$base_url = "search/k/";
			}
	
			$kwds = explode(',',$this->keywords);
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
		return FALSE;

	}

    // search for content
	function search_content()
    {
		$scores = $this->search();
		$content = array();

		if (count($scores) > 0)
		{
			foreach($scores as $id => $result)
			{
				$this->temp_lang = '';
				$this->title = '';
				$this->title_id = '';
				
				$this->temp_id = $result['rel_id'];
				if(array_key_exists('title_id',$result))
				{
					$this->temp_lang = $result['lang'];
					$this->title = $result['title'];
					$this->title_id = $result['title_id'];
				}
				else
				{
					$this->load_title();
				}
				
				if($this->link_mode == 1)
				{
					$link = 'cms/content/edit/id/'.$result['rel_id'].'/lang/'.$this->temp_lang;
				}
				else
				{
					$link = $this->generate_uri();
				}
				$content[] = anchor($link,$this->title);

			}
		}
		
		return $content;		
	}

    // search for content
	function search_content_keywords()
    {
		$results = $this->keyword_search();

		$content = array();
		
		if (count($results) > 0)
		{
			foreach ($results as $row)
			{
				if($this->link_mode == 1)
				{
					$uri = 'cms/content/edit/id/'.$id.'/lang/'.$row['lang_ISO_639_3'];
				}
				else
				{
					$this->temp_lang = $row['lang_ISO_639_3'];
					$this->temp_id = $row['rel_id'];
					$this->title_id = $row['title_id'];
					$uri = $this->generate_uri();
				}
				$content[] = anchor($uri,$row['title']);
			}
		} 		

		return $content;		
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

	function count_lang_versions()
    {
		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->temp_id);
		$this->db->group_by("lang_ISO_639_3");
		$query = $this->db->get('c_titles');

		$this->lang_mode = $query->num_rows();
	}

	// attachments
	function list_attachments()
    {
		$this->db->where('content_id', $this->content_id);
		$query = $this->db->get('r_attachments');

		$this->attachments = array();
		if ($query->num_rows() > 0)
		{
			$this->attachments = $query->result_array();
		}
	}

	function remove_attachments()
    {
		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{
			foreach($this->temp_value as $a_id)
			{
				$this->db->or_where('a_id',$a_id);
			}
			$this->temp_value = '';
			if($this->db->delete('r_attachments'))
			{
				return true;
			}
		}
		return false;
	}

	function add_attachments()
    {
		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{
			$sql = "INSERT INTO `r_attachments` (`content_id`,`media_id`) VALUES ";
			foreach($this->temp_value as $media_id)
			{
				$rows[] = '('.$this->content_id.','.$media_id.')';
			}
			$sql .= implode(',',$rows);
			$this->temp_value = '';
			
			if($this->db->query($sql))
			{
				return true;
			}
		}
		return false;
	}

	function update_publish()
	{

		$data = array(
		'publish' => $this->publish,
		);

		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{

			foreach($this->temp_value as $id)
			{
				$this->db->or_where('content_id', $id);
			}
			// update
			if($this->db->update('c_content', $data))
			{
				return TRUE;
			}

		}

		return FALSE;

	}
	
	function delete_content()
	{
		// first load to get file URI
		$this->load_content_object();
		$this->temp_id = $this->content_id;
		$this->load_title();
		$name = $this->cms_labels['content'].' '.$this->content_id;
		if($this->title != '')
		{
			$name = $this->title;
		}

		// delete all titles, metadata
		$this->db->where('scope', 'content');
		$this->db->where('rel_id', $this->content_id);
		if(!$this->db->delete('c_titles'))
		{
			$this->reset_vars();
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		$this->db->where('scope', 'content');
		$this->db->where('rel_id', $this->content_id);
		if(!$this->db->delete('c_metadata'))
		{
			$this->reset_vars();
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}
		
		// delete all attachments for this entry
		$this->db->where('content_id', $this->content_id);
		if(!$this->db->delete('r_attachments'))
		{
			$this->reset_vars();
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		// delete content object
		$this->db->where('content_id', $this->content_id);
		if(!$this->db->delete('c_content'))
		{
			$this->reset_vars();
			$this->messages[] = sprintf($this->cms_labels['delete_failure'],$name);
			return FALSE;
		}

		$this->messages[] = sprintf($this->cms_labels['delete_success'],$name);
		$this->reset_vars();
		return TRUE;
	}

	function delete_batch()
	{
		if(is_array($this->temp_value) && count($this->temp_value) > 0)
		{

			foreach($this->temp_value as $mid)
			{
				$this->content_id = $mid;
				$this->delete_content();
			}

		}
	}

	function reset_vars()
	{
		$this->temp_value = '';
		$this->temp_id = '';
		$this->type_id = '';

		$this->title_id = '';
		$this->title_rel_id = '';
		$this->title = '';
		$this->uri_title = '';
		$this->subtitle = '';
		$this->keywords = '';
		$this->title_updated = '';

		$this->content_id = '';
		$this->user_id = '';
		$this->parent_id = '';
		$this->date_created = '';
		$this->date_end_expiry = '';
		$this->date_updated = '';
		$this->publish = '';
	}

}

?>