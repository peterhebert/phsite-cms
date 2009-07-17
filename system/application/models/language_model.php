<?php
class Language_model extends MY_Model {

	var $ISO_639_3;
	var $ISO_639_2B;
	var $ISO_639_2T;
	var $ISO_639_1;
	var $Scope;
	var $Type;
	var $Ref_Name;
	var $Inverted_Name;
	var $Native_Name;
	var $activated;

	var $lang_count;

    function Language_model()
    {
        // Call the Model constructor
        parent::MY_Model();
    }

    // get list of languages
	function get_list($activeOnly = TRUE,$limit=0)
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

		return $query->result_array();
	}

    // count active languages
	function count_active()
    {
		$this->db->select('count(ISO_639_3) AS count_langs');
		$this->db->where('activated', 1);
		$this->db->order_by("Inverted_Name", "asc");
		$query = $this->db->get('ISO_639_LANG');
		
		$this->lang_count = 0;
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
		
			$this->lang_count = $row->count_langs;
		}

	}

    // get list of active languages for form drop-down
	function get_list_dropdown()
    {
		$active = $this->get_list();
		$list = array();
		
		foreach($active as $row)
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
	function find_matches($str)
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

    // load the language based upon 639-3 key
	function load()
    {
		$this->db->where('ISO_639_3', $this->ISO_639_3);
		$query = $this->db->get('ISO_639_LANG');
 
 		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$this->ISO_639_3 = $row['ISO_639_3'];
			$this->ISO_639_2B = $row['ISO_639_2B'];
			$this->ISO_639_2T = $row['ISO_639_2T'];
			$this->ISO_639_1 = $row['ISO_639_1'];
			$this->Scope = $row['Scope'];
			$this->Type = $row['Type'];
			$this->Ref_Name = $row['Ref_Name'];
			$this->Inverted_Name = $row['Inverted_Name'];
			$this->Native_Name = $row['Native_Name'];
			$this->activated = $row['activated'];
			return TRUE;
		}
		return FALSE;

    }

	function set_active($val)
    {

		$data = array(
		'activated' => $val,
		);
		
		$this->db->where('ISO_639_3', $this->ISO_639_3);
		if($this->db->update('ISO_639_LANG', $data)) {
			$this->activated = $val;
		} 
    }

	function site_meta_menu()
    {
		// list active languages
		$lang_links = array();
		$langs = $this->get_list();
		foreach($langs as $llang)
		{
			$temp_link = 'cms/setup/site_meta/lang/'.$llang['ISO_639_3'];
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

		$menu =  "<ul>\r";
		$menu .=  "<li>".anchor("cms/setup/site_meta_fields","Edit site metadata fields")."</li>\r";
		$menu .=  "<li>Edit site metadata\r";
		$menu .= ul($lang_links);
		$menu .=  "</li>\r";
		$menu .=  "</ul>\r";

		return $menu;
	}

}

?>
