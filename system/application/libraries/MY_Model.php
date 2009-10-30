<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* EXTENDED Model Class
* @author	Peter Hebert
* @link		http://www.peterhebert.com/

*/
class MY_Model extends Model {

	// general variables
	var $install_path, $scope, $temp_lang, $temp_id, $temp_value;
	var $messages = array();
	var $scope_list = array('content','media','types','users','site');
	var $label_scope_list = array('field','types');
	var $limit; // limit number for query
	var $page; // page number number for query limit
	
	var $date_filter;
	// add < or > for before or after at beginning of string
	// date range YYYY, YYYY-MM or YYYY-MM-DD format
	// one value means on that date, month or year
	// two values means range

	// field variables
	var $field_list = array();
	var $input_types = array();
	var $data_types = array();
	var $validation_rules = array();
	var $field_id, $field_ref_name, $field_input_type, $field_validation, $field_needs_lang;
	
	// type variables
	var $type_id, $type_uri_name, $type_list = array();

	// labels for fields, types 
	var $label_id, $label_scope, $label_rel_id, $label_rel_name, $label;

	// field validation rules
	var $validation_string, $validation_exact, $validation_min, $validation_max;

	// language variables
	var $lang_ISO_639_3, $lang_ISO_639_2B, $lang_ISO_639_2T, $lang_ISO_639_1, $lang_Scope, $lang_Type, $lang_Ref_Name, $lang_Inverted_Name, $lang_Native_Name, $lang_CI_Name, $lang_activated;
	var $lang_count, $lang_list = array(), $matches = array();

	// content_types

	// metadata variables
	var $metadata = array();
	var $meta_id, $rel_id, $value;

	// titles & keywords
	var $title_id, $title_rel_id, $title, $uri_title, $subtitle, $keywords, $title_updated;
	
	// Constructor
	function MY_Model()
	{	
		parent::Model();

		$docroot = realpath($_SERVER['DOCUMENT_ROOT']);
		$script = $_SERVER['SCRIPT_NAME'];
		$basedir = preg_replace('/(.*)index\.php(.*)/','\1',$script);

		$this->install_path = $docroot.$basedir;

	}

	function _output_messages()
	{
		if(count($this->messages) > 0)
		{
			$tmp_msg = "<p>";
			$tmp_msg .= implode("</p>\r<p>",$this->messages);
			$tmp_msg .= "</p>";
			return $tmp_msg;
		}
		return FALSE;
	}

	// language functions
	function list_langs($activeOnly = TRUE,$limit=0)
    {
		if($activeOnly == TRUE) {
			$this->db->where('activated', 1);
		}
		$this->db->order_by("Inverted_Name", "asc");
		if($limit > 0) {
			$query = $this->db->get('ISO_639_LANG', $limit);
		} else {
			$query = $this->db->get('ISO_639_LANG');
		}

		$this->lang_list = $query->result_array();
		$this->lang_count = $query->num_rows();

	}

    // get list of active languages for form drop-down
	function langs_dropdown()
    {
		$this->list_langs();
		$list = array();

		foreach($this->lang_list as $row)
		{
			if($row['Native_Name'] != '')
			{
				$list[$row['ISO_639_3']] = $row['Native_Name'];
			} else {
				$list[$row['ISO_639_3']] = $row['Ref_Name'];
			}
		}

		return $list;
	}

    // find matching languages
	function lang_matches()
    {
		$match = $this->input->xss_clean($this->temp_value);
		
		$sql = "SELECT * FROM `ISO_639_LANG` WHERE (
		`Ref_Name` LIKE '%{$match}%' OR
		`Inverted_Name` LIKE '%{$match}%' OR
		`Native_Name` LIKE '%{$match}%' 
		) AND `activated` = 0";

		$query = $this->db->query($sql);
		
		$this->matches = $query->result_array();
	}

	function lang_load()
    {

		$this->db->where('ISO_639_3', $this->temp_lang);
		$query = $this->db->get('ISO_639_LANG');
 
 		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->lang_ISO_639_3 = $row['ISO_639_3'];
			$this->lang_ISO_639_2B = $row['ISO_639_2B'];
			$this->lang_ISO_639_2T = $row['ISO_639_2T'];
			$this->lang_ISO_639_1 = $row['ISO_639_1'];
			$this->lang_Scope = $row['Scope'];
			$this->lang_Type = $row['Type'];
			$this->lang_Ref_Name = $row['Ref_Name'];
			$this->lang_Inverted_Name = $row['Inverted_Name'];
			$this->lang_Native_Name = $row['Native_Name'];
			$this->lang_CI_Name = $row['CI_Name'];
			$this->lang_activated = $row['activated'];
			
			return TRUE;
		}
		return FALSE;

    }
	
	function lang_set_active()
    {

		$data = array(
		'activated' => $this->temp_value,
		);
		
		$this->db->where('ISO_639_3', $this->temp_lang);
		if($this->db->update('ISO_639_LANG', $data)) {
			return TRUE;
		}
		return FALSE; 
    }

	function lang_meta_menu()
    {
		$base_uri = $this->temp_value;
		
		// list active languages
		$lang_links = array();
		$this->list_langs();
		$langs = $this->lang_list;
		$menu = '';
		if(count($langs) > 0)
		{
			foreach($langs as $llang)
			{
				$temp_link = $base_uri.'/lang/'.$llang['ISO_639_3'];
				if($llang['Native_Name'] != '')
				{
					$temp_txt = $llang['Native_Name'];
				}
				else
				{
					$temp_txt = $llang['Ref_Name'];
				}
				$lang_links[] = anchor($temp_link,$temp_txt);
			}
	
			$menu = ul($lang_links);
		}

		return $menu;
	}
	
	// content / media type functions
	function load_type()
    {

		$this->db->where('type_id', $this->temp_id);
		$this->db->where('scope', $this->scope);
		$query = $this->db->get('s_types');
 
 		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->type_id = $row['type_id'];
			$this->type_uri_name = $row['type_uri_name'];
			
			return TRUE;
		}
		return FALSE;

	}

    function update_type()
    {
		
		$type_data = array(
		'scope' => $this->scope,
		'type_uri_name' => $this->type_uri_name
		);

		if( $this->type_id != '' )
		{
			// update
			$this->db->where('type_id', $this->type_id);
			if($this->db->update('s_types', $type_data))
			{
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('s_types', $type_data))
			{
				$this->type_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

    }

	function list_types()
    {

		$list = array();
		$this->db->where('scope',$this->scope);
		$query = $this->db->get('s_types');

		foreach ($query->result() as $row)
		{

			$temp =  array(
			'type_id' => $row->type_id,
			'type_uri_name' => $row->type_uri_name,
			);

			if($this->scope != 'media')
			{
				$this->db->where('scope', 'types');
				$this->db->where('rel_id', $row->type_id);
				$this->db->where('lang_ISO_639_3', $this->temp_lang);
				$this->db->limit(1);
				$query2 = $this->db->get('s_labels');
	
				if ($query2->num_rows() > 0)
				{
					$row2 = $query2->row();
					$temp['label_id'] = $row2->label_id;
					$temp['label'] = $row2->label;
					$temp['lang_ISO_639_3'] = $row2->lang_ISO_639_3;
				}
			}

		   $list[] = $temp;

		}

		$this->type_list = $list;

	}

	function types_dropdown()
    {
		$this->list_types();
		$list = array();
		foreach($this->type_list as $row)
		{
			if($row['label'] != '')
			{
				$list[$row['type_id']] = $row['label'];
			} else {
				$list[$row['type_id']] = $row['type_uri_name'];
			}
		}

		return $list;
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



	// field functions
	function list_fields()
    {

		$list = array();
		$query = $this->db->get('s_fields');

		if ($query->num_rows() > 0)
		{
			$list = $query->result_array();
		} 

		$this->field_list = $list;

	}

	function list_fields_related()
    {

		$list = array();

// select * from r_fields left join s_fields on r_fields.field_id = s_fields.field_id where scope = 'types' and rf_rel_id = 3
		
		$this->db->where('scope',$this->scope);
		if ($this->temp_id != '' && $this->scope == 'types')
		{
			$this->db->where('rf_rel_id',$this->temp_id);
		}
		$this->db->join('s_fields', 'r_fields.field_id = s_fields.field_id', 'left');

		$this->db->order_by("order");
		$query = $this->db->get('r_fields');

		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
				$key = strval($row->field_id);
				
				if($row->rf_rel_id == 0)
				{
					$tmp_rf_rel_id = '';
				}
				else
				{
					$tmp_rf_rel_id = $row->rf_rel_id;
				}
				
				$list[$key] = array(
				'rf_id' => $row->rf_id,
				'scope' => $row->scope,
				'rf_rel_id' => $tmp_rf_rel_id,
				'rf_rel_ref_name' => $row->rf_rel_ref_name,
				'field_id' => $row->field_id,
				'field_ref_name' => $row->field_ref_name,
				'field_input_type' => $row->field_input_type,
				'field_validation' => $row->field_validation,
				'field_needs_lang' => $row->field_needs_lang,
				'required' => $row->required,
				'order' => $row->order,
				'meta_id' => '',
				'rel_id' => '',
				'value' => '',
				'lang_ISO_639_3' => '',
				'date_updated' => '',
			  );
		   }
		} 

		$this->field_list = $list;

	}

    function update_field()
    {
		$this->field_merge_validation();
		
		$field_data = array(
		'field_ref_name' => $this->field_ref_name,
		'field_input_type' => $this->field_input_type,
		'field_validation' => $this->field_validation,
		'field_needs_lang' => $this->field_needs_lang
		);
		
		if( $this->field_id != '' )
		{
			// update
			$this->db->where('field_id', $this->field_id);
			if($this->db->update('s_fields', $field_data))
			{
				return TRUE;
			}

		} else {
			// insert
			if($this->db->insert('s_fields', $field_data))
			{
				$this->field_id = $this->db->insert_id();
				return TRUE;
			}
		}

		return FALSE;

    }

	function update_fields_related()
    {

		foreach($this->field_list as $field)
		{

			$this->temp_id = $field['field_id'];
			$this->load_field(FALSE);

			$this->temp_id = $field['field_id'];
			$this->label_scope = 'field';
			$this->load_label();
			if($this->label != '')
			{
				$field_label = $this->label;
			}
			else
			{
				$field_label = $this->field_ref_name;
			}


			$db_fields = array(
			'scope' => $this->scope,
			'rf_rel_id' => '',
			'rf_rel_ref_name' => '',
			'field_id' => $field['field_id'],
			'field_ref_name' => '',
			'required' => $field['required'],
			'order' => $field['order'],
			);
			
			if($this->field_id != '')
			{
				$db_fields['field_ref_name'] = $this->field_ref_name;
			}

			if(array_key_exists('rf_rel_id',$field))
			{
				$db_fields['rf_rel_id'] = $field['rf_rel_id'];
			}
			if(array_key_exists('rf_rel_ref_name',$field))
			{
				$db_fields['rf_rel_ref_name'] = $field['rf_rel_ref_name'];
			}
			
			$cms_labels = $this->lang->line('cms_labels');

			if($field['exists'] === TRUE)
			{
				// update field

				if($field['rf_id'] == '')
				{
					// create new metadata
					if(!$this->db->insert('r_fields', $db_fields) )
					{

						$this->messages[] = sprintf($cms_labels['rel_insert_failure'], $field_label);
					}
		
				}
				else
				{
					// update metadata field
					if(!$this->db->update('r_fields', $db_fields, array('rf_id' => $field['rf_id'] )) ) {
						$this->messages[] = sprintf($cms_labels['rel_update_failure'], $field_label);
					}
				}

			}
			else
			{
				if($field['rf_id'] != '')
				{
					// remove relationship
					$this->db->where('rf_id', $field['rf_id']);
					if(!$this->db->delete('r_fields')) {
						$this->messages[] = sprintf($cms_labels['rel_delete_failure'], $field_label);
					}
				} 

			}

			$this->field_id == '';
		} // end foreach()

	}

	function load_field($parse = TRUE)
    {
		$query = $this->db->get_where('s_fields', array('field_id' => $this->temp_id));

		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->field_id = $row['field_id'];
			$this->field_ref_name = $row['field_ref_name'];
			$this->field_input_type = $row['field_input_type'];
			$this->field_validation = $row['field_validation'];
			$this->field_needs_lang = $row['field_needs_lang'];
	
			if($parse)
			{
				$this->field_parse_validation();
			}
			return TRUE;
		}
		else
		{
			return FALSE;
		} 
    }

	function field_parse_validation()
	{

		$valid = explode('|',$this->field_validation);

		if ( is_array($valid) && count($valid > 0) ) {
		
			foreach($valid as $rule) {
			
				if(in_array($rule,$this->data_types)){
					$this->validation_string = $rule;
				}
				
				$pattern_exact = '/exact_length\[([0-9]+)\]/';
				$pattern_min = '/min_length\[([0-9]+)\]/';
				$pattern_max = '/max_length\[([0-9]+)\]/';
				
				if( preg_match($pattern_exact,$rule) ) {
					$this->validation_exact = preg_replace($pattern_exact, '\\1',$rule);
				}
				if( preg_match($pattern_min,$rule) ) {
					$this->validation_min = preg_replace($pattern_min, '\\1',$rule);
				}
				if( preg_match($pattern_max,$rule) ) {
					$this->validation_max = preg_replace($pattern_max, '\\1',$rule);
				}
				
			}
		}

	}

	function field_merge_validation()
	{
		$validArray = array();

		if($this->validation_string != ''){
			$validArray[] = $this->validation_string;
		}
		if($this->validation_exact != ''){
			$validArray[] = 'exact_length['.$this->validation_string.']';
		}

		if($this->validation_min != ''){
			$validArray[] = 'min_length['.$this->validation_min.']';
		}
		if($this->validation_max != ''){
			$validArray[] = 'max_length['.$this->validation_max.']';
		}

		$this->field_validation = implode('|',$validArray);

	}

	// labels for fields or types
	function load_label()
    {
		$this->db->where('scope',$this->label_scope);
		if($this->temp_id != '')
		{
			$this->db->where('rel_id',$this->temp_id);
		}
		if($this->label_id != '')
		{
			$this->db->where('label_id',$this->label_id);
		}

		if($this->session->userdata('default_lang'))
		{
			$this->db->where('lang_ISO_639_3',$this->session->userdata('default_lang'));
		}
		else if ($this->temp_lang != '')
		{
			$this->db->where('lang_ISO_639_3',$this->temp_lang);
		}
		
		$this->db->limit(1);
		$query = $this->db->get('s_labels');
			
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$this->label_id = $row->label_id;
			$this->label_scope = $row->scope;
			$this->label_rel_id = $row->rel_id;
			$this->label_rel_name = $row->rel_name;
			$this->label = $row->label;
			$this->temp_lang = $row->lang_ISO_639_3;
			return TRUE;
		}
		else
		{
			$this->label_id = '';
			$this->label_scope = '';
			$this->label_rel_id = '';
			$this->label_rel_name = '';
			$this->label = '';
			return FALSE;
		}
	}

	function reset_label()
    {
		$this->temp_id = '';
		$this->label_id = '';
		$this->label_scope = '';
		$this->label_rel_id = '';
		$this->label_rel_name = '';
		$this->label = '';
	}


    function update_label()
    {
		$data = array(
		'scope' => $this->label_scope,
		'rel_id' => $this->label_rel_id,
		'rel_name' => $this->label_rel_name,
		'label' => $this->label,
		'lang_ISO_639_3' => $this->temp_lang
		);

		// update or insert title
		if($this->label_id == '') {
			// insert
			if($this->db->insert('s_labels', $data)){
				$this->label_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$this->db->where('label_id', $this->label_id);
			if($this->db->update('s_labels', $data)){
				return TRUE;
			}

		}
		return FALSE;
    }



	// metadata functions
	function load_metadata()
    {
		$fields = $this->field_list;

		$this->db->where('scope',$this->scope);
		$this->db->where('rel_id',$this->temp_id);

		if ($this->temp_lang != '')
		{
			$where = "(lang_ISO_639_3 = '{$this->temp_lang}' OR lang_ISO_639_3 = '')";
			$this->db->where($where);
		}

		$this->db->join('s_fields', 'c_metadata.field_id = s_fields.field_id', 'left');

		$query = $this->db->get('c_metadata');
		
		$meta_all = array();
		if ($query->num_rows() > 0)
		{
			$meta_all = $query->result_array();
		}

		foreach($meta_all as $meta)
		{

			$key = strval($meta['field_id']);
			
			if(array_key_exists($key,$fields))
			{
				$fields[$key] = array_merge($fields[$key],$meta);
			}
			else
			{

				$temp_meta = $meta;
/*				$temp_meta['rf_id'] = '';
				$temp_meta['scope'] = $this->scope;
				$temp_meta['rf_rel_id'] = '';
				$temp_meta['rf_rel_ref_name'] = '';
*/				
				$temp_meta['required'] = 0;

				$fields[$key] = $temp_meta;
				unset($temp_meta);

			}
			
		}

		$this->metadata = $fields;
	}

	function update_metadata()
    {

		foreach($this->metadata as $meta)
		{
			$this->temp_id = $meta['field_id'];
			$this->load_field();

			$this->temp_id = $meta['field_id'];
			$this->label_scope = 'field';
			$this->load_label();
			if($this->label != '')
			{
				$field_label = $this->label;
			}
			else
			{
				$field_label = $meta['field_ref_name'];
			}
			
			$db_fields = array(
			'scope' => $this->scope,
			'rel_id' => $meta['rel_id'],
			'field_id' => $meta['field_id'],
			'field_ref_name' => $meta['field_ref_name'],
			'value' => $meta['value'],
			'lang_ISO_639_3' => '',
			);

			if($this->field_needs_lang == 1)
			{
				$db_fields['lang_ISO_639_3'] = $this->temp_lang;
			}
			if($this->field_id != '')
			{
				$db_fields['field_ref_name'] = $this->field_ref_name;
			}
	
			$cms_labels = $this->lang->line('cms_labels');

			if($meta['meta_id'] == '')
			{
				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// create new metadata
					if(!$this->db->insert('c_metadata', $db_fields) )
					{
						$this->messages[] = sprintf($cms_labels['metadata_insert_failure'], $field_label);
					}
				}
	
			}
			else
			{

				if($meta['required'] == 1 || $meta['value'] != '')
				{
					// update metadata
					if(!$this->db->update('c_metadata', $db_fields, array('meta_id' => $meta['meta_id'])) ) {
						$this->messages[] = sprintf($cms_labels['metadata_update_failure'], $field_label);
					}
				}
				else
				{
					// delete metadata
					if(!$this->db->delete('c_metadata', array('meta_id' => $meta['meta_id'])) ) {
						$this->messages[] = sprintf($cms_labels['metadata_delete_failure'], $field_label);
					}
				}

			}
			$this->field_id = '';
			$this->field_needs_lang = '';
		
		}

	}

	function load_title()
    {

		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->temp_id);

		if($this->title_id != '') {
			$this->db->where('title_id', $this->title_id);
		}
		if($this->temp_lang != '' && preg_match('#^([a-z]{3})$#',$this->temp_lang)) {
			$this->db->where('lang_ISO_639_3', $this->temp_lang);
		}

		$this->db->limit(1);
		$query = $this->db->get('c_titles');

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		   
		   $this->title_id = $row->title_id;
		   $this->scope = $row->scope;
		   $this->title_rel_id = $row->rel_id;
		   $this->title = $row->title;
		   $this->uri_title = $row->uri_title;
		   $this->subtitle = $row->subtitle;
		   $this->keywords = $row->keywords;
		   $this->temp_lang = $row->lang_ISO_639_3;
		   $this->title_updated = $row->title_updated;
		}

    }

	function title_exists()
    {

		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->temp_id);
		$this->db->where('lang_ISO_639_3', $this->temp_lang);
		$query = $this->db->get('c_titles');

		if ($query->num_rows() > 0)
		{
		   $row = $query->row();
		   $this->title_id = $row->title_id;
		   return true;
		}
	   return false;
    }

    // update title
    function update_title()
    {
		
		$data = array(
		'scope' => $this->scope,
		'rel_id' => $this->title_rel_id,
		'title' => $this->title,
		'uri_title' => convert_uri($this->title,URI_SEPARATOR),
		'subtitle' => $this->subtitle,
		'keywords' => csv_normalize($this->keywords),
		'lang_ISO_639_3' => $this->temp_lang
		);

		// update or insert title
		if($this->title_id == '') {
			// insert
			if($this->db->insert('c_titles', $data)){
				$this->title_id = $this->db->insert_id();
				return TRUE;
			}
			
		} else {
			// update
			$this->db->where('title_id', $this->title_id);
			if($this->db->update('c_titles', $data)){
				return TRUE;
			}

		}
		return FALSE;

    }
	
    // search for content/media matching a string
	function search()
    {
		$match = $this->input->xss_clean($this->temp_value);
		$scores = array();

		$lang_sql = "";

		if($this->temp_lang != '' && preg_match('#^([a-z]{3})$#',$this->temp_lang))
		{
			$lang_sql = " AND lang_ISO_639_3 = '{$this->temp_lang}'";
		}

		// first match values
		$sql = "SELECT rel_id AS rel_id, AVG(MATCH(`value`) AGAINST ('{$match}')) * 4 AS score
		FROM c_metadata
		WHERE MATCH(`value`) AGAINST('{$match}'){$lang_sql} 
		AND scope = '{$this->scope}'
		GROUP BY rel_id";

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$key = strval($row->rel_id);
				$scores[$key] = array(
				'rel_id' => $row->rel_id,
				'score' => $row->score,
				);
				
			}
		}	

		$sql = "SELECT *, COUNT(rel_id) AS score FROM c_titles WHERE 
		(title LIKE '%{$match}%' OR subtitle LIKE '%{$match}%' OR keywords LIKE '%{$match}%'{$lang_sql}) 
		AND scope = '{$this->scope}'
		GROUP BY rel_id";
		
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$key = strval($row->rel_id);
				$tmp_score = $row->score;
				
				if(array_key_exists($key,$scores))
				{
					$tmp_score += $scores[$key]['score'];
				}

				$scores[$key] = array(
				'rel_id' => $row->rel_id,
				'score' => $tmp_score,
				'title_id' => $row->title_id,
				'title' => $row->title,
				'lang' => $row->lang_ISO_639_3,
				);


			}
		}

		uasort($scores, 'sort_scores');
		
		return $scores;		
	}

    // search using a keyword
	function keyword_search()
    {
		$match = $this->input->xss_clean($this->temp_value);
		$results = array();

		$sql = "SELECT * FROM c_titles WHERE keywords REGEXP '[[:<:]]{$match}[[:>:]]' AND scope = '{$this->scope}'";

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			$results = $query->result_array();
		} 		

		return $results;		
	}

}
// END MY_Model class

/* End of file MY_Model.php */
/* Location: ./system/application/libraries/MY_Model.php */