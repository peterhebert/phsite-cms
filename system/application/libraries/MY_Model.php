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
	var $lang_count, $lang_list = array();

	// content_types

	// metadata variables
	var $metadata = array();
	var $meta_id, $meta_rel_id, $value;

	// titles
	var $title_id, $title_rel_id, $title, $uri_title, $subtitle, $title_updated;
	
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

	// content / media type functions
	function load_type()
    {

		$this->db->where('type_id', $this->type_id);
		$this->db->where('scope', $this->scope);
		$query = $this->db->get('s_types');
 
 		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->type_uri_name = $row['type_uri_name'];
			
			return TRUE;
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
			'label_id' => '',
			'label' => '',
			'lang_ISO_639_3' => '',
			);

			$this->db->where('scope', 'type');
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
	function lang_matches($str)
    {
		$match = $this->input->xss_clean($str);
		
		$sql = "SELECT * FROM `ISO_639_LANG` WHERE (
		`Ref_Name` LIKE '%{$match}%' OR
		`Inverted_Name` LIKE '%{$match}%' OR
		`Native_Name` LIKE '%{$match}%' 
		) AND `activated` = 0";

		$query = $this->db->query($sql);
		return $query->result_array();
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
		
		$this->db->where('scope',$this->scope);
		$this->db->where('rf_rel_id',$this->temp_id);
		$this->db->join('s_fields', 'r_fields.field_id = s_fields.field_id', 'left');
		$query = $this->db->get('r_fields');
		
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
				$key = strval($row->field_id);
				$list[$key] = array(
				'rf_id' => $row->rf_id,
				'scope' => $row->scope,
				'rf_rel_id' => $row->rf_rel_id,
				'rf_rel_ref_name' => $row->rf_rel_ref_name,
				'field_id' => $row->field_id,
				'field_ref_name' => $row->field_ref_name,
				'field_input_type' => $row->field_input_type,
				'field_validation' => $row->field_validation,
				'field_needs_lang' => $row->field_needs_lang,
				'required' => $row->required,
				'order' => $row->order,
				'meta_id' => '',
				'meta_rel_id' => '',
				'value' => '',
				'lang_ISO_639_3' => '',
				'date_updated' => '',
			  );
		   }
		} 


		$this->field_list = $list;

	}

/*
	function update_fields_related()
    {


		foreach($this->field_list as $field)
		{

			$this->temp_id = $field['field_id'];
			$this->label_scope = 'field';
			$this->load_label();
			if($this->label != '')
			{
				$field_label = $this->label;
			}
			else
			{
				$field_label = $field['field_ref_name'];
			}

			$db_fields = array(
			'scope' => $this->scope,
			'rf_rel_id' => $field['rf_rel_id'],
			'rf_rel_ref_name' => $field['rf_rel_ref_name'],
			'field_id' => $field['field_id'],
			'field_ref_name' => $field['field_ref_name'],
			'field_input_type' => $field['field_input_type'],
			'field_validation' => $field['field_validation'],
			'field_needs_lang' => $field['field_needs_lang'],
			'required' => $field['required'],
			'order' => $field['order'],
			);
			
			if($field['exists'] === TRUE)
			{
				// update field

				if($field['rf_id'] == '')
				{
					// create new metadata
					if(!$this->db->insert('r_fields', $db_fields) )
					{
						$this->messages[] = "could not create relationship for field {$field_label}";
					}
		
				}
				else
				{
					// update metadata field
					if(!$this->db->update('r_fields', $db_fields, array('rf_id' => $field['rf_id'] )) ) {
						$this->messages[] = "could not update relationship for field {$field_label}";
					}
				}

			}
			else
			{
				if($field['rf_id'] != '')
				{
					// remove relationship
					$this->db->where('rel_id', $field['rf_id']);
					if(!$this->db->delete('r_fields')) {
						$this->messages[] = "could not remove relationship for field {$field_label}";
					}
				} 

			}

		} // end foreach()

		if(count($this->messages) == 0)
		{
			$this->messages[] = "field relationships successfully updated";
		}
	}
*/
	function load_field()
    {
		$query = $this->db->get_where('s_fields', array('field_id' => $this->temp_id));
		$row = $query->row_array();
		$this->field_id = $row['field_id'];
		$this->field_ref_name = $row['field_ref_name'];
		$this->field_input_type = $row['field_input_type'];
		$this->field_validation = $row['field_validation'];
		$this->field_needs_lang = $row['field_needs_lang'];

		$this->field_parse_validation();
    }

	function field_parse_validation()
	{

		$valid = explode('|',$this->field_validation);

		if ( is_array($valid) && count($valid > 0) ) {
		
			foreach($valid as $rule) {
			
				if(in_array($rule,$this->data_types)){
					$this->validation_string = $rule;
				}
				if(ereg ( 'exact_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)exact_length\[([0-9]+)\](.*)$';
					$this->validation_exact = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'min_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)min_length\[([0-9]+)\](.*)$';
					$this->validation_min = ereg_replace($pattern,'\\2',$rule);
				}
		
				if(ereg ( 'max_length\[([0-9]+)\]', $rule)) {
					$pattern = '^(.*)max_length\[([0-9]+)\](.*)$';
					$this->validation_max = ereg_replace($pattern,'\\2',$rule);
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
		$this->db->where('label_id',$this->temp_id);
		$this->db->where('lang_ISO_639_3',$this->temp_lang);
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

	// metadata functions
	function load_metadata()
    {
		$fields = $this->field_list;

		if ($this->temp_id != '')
		{

			$this->db->where('scope',$this->scope);
			$this->db->where('meta_rel_id',$this->temp_id);

			if ($this->temp_lang != '')
			{
				$where = "(lang_ISO_639_3 = '{$this->temp_lang}' OR lang_ISO_639_3 = '')";
				$this->db->where($where);
			}
			else
			{
				$this->db->where('lang_ISO_639_3',$this->temp_lang);
			}
	
			$query = $this->db->get('c_metadata');
	
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$key = $row->field_id;
					$fields["$key"]['meta_id'] = $row->meta_id;
					$fields["$key"]['meta_rel_id'] = $row->meta_rel_id;
					$fields["$key"]['value'] = $row->value;
					$fields["$key"]['lang_ISO_639_3'] = $row->lang_ISO_639_3;
					$fields["$key"]['date_updated'] = $row->date_updated;
				}
			}
		}

		$this->metadata = $fields;

	}

	function update_metadata()
    {

		foreach($this->metadata as $meta)
		{
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
			'meta_rel_id' => $meta['meta_rel_id'],
			'field_id' => $meta['field_id'],
			'field_ref_name' => $meta['field_ref_name'],
			'value' => $meta['value'],
			'lang_ISO_639_3' => '',
			);

			if($meta['field_needs_lang'] == 1)
			{
				$db_fields['lang_ISO_639_3'] = $this->temp_lang;
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
		
		}

	}

	function load_title()
    {

		$this->db->where('scope', $this->scope);
		$this->db->where('rel_id', $this->temp_id);
		$this->db->where('lang_ISO_639_3', $this->temp_lang);
		if($this->title_id != '') {
			$this->db->where('title_id', $this->title_id);
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
		   $this->temp_lang = $row->lang_ISO_639_3;
		   $this->title_updated = $row->title_updated;
		}

    }

    // update title
    function update_title()
    {
		// try to find existing title id
		if($this->title_id == '') {
		
			$data = array(
			'rel_id' => $this->title_rel_id,
			'lang_ISO_639_3' => $this->temp_lang
			);
			
			$title_query = $this->db->get_where('c_titles', $data);
			if ($title_query->num_rows() > 0)
			{
			   $trow = $title_query->row();
			   $this->title_id = $trow->title_id;
			} 

		}
		
		$data = array(
		'scope' => $this->scope,
		'rel_id' => $this->title_rel_id,
		'title' => $this->title,
		'uri_title' => convert_uri($this->title),
		'subtitle' => $this->subtitle,
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

}
// END MY_Model class

/* End of file MY_Model.php */
/* Location: ./system/application/libraries/MY_Model.php */