<?php

class Tests extends Controller {

	function Tests()
	{
		parent::Controller();	
		$this->load->helper('file');
		$this->load->helper('url');
	}
	
	function index()
	{
			$data['result'] = "add parameters to URI to test";

		$this->load->view('test',$data);

	}
	function lang()
	{
		$lang = $this->uri->segment(3);
		
		if($lang != '') {
			$data['result'] = "'$lang' is ";
			if($this->is_lang($lang) == false) {
				$data['result'] .= "NOT ";
			} 
			$data['result'] .= "a language";
		} else {
			$data['result'] = "add parameters to URI to test";
		}

		$this->load->view('test',$data);

	}


	function is_lang($value)
	{
		
		
		// read tab-delimited file of ISO 639 lang codes
		$iso_all = read_file('system/application/language/iso-639-3.tab');
		//Id	Part2B	Part2T	Part1	Scope	Language_Type	Ref_Name	Comment
		$iso_639 = preg_replace('/\r\n|\r/', "\n", $iso_all);
		$langs = explode("\n",$iso_639);
		
		foreach($langs as $line) {
			$temp = explode("\t",$line);
			/*$langArray[] = array(
			'iso_639_3'  => $temp[0],
			'iso_639_2b' => $temp[1],
			'iso_639_2t' => $temp[2],
			'iso_639_1'  => $temp[3],
			'Scope'  => $temp[4],
			'Language_Type'  => $temp[5],
			'Ref_Name'  => $temp[6],
			'Comment'  => $temp[7],
			);*/
			if(in_array($value, $temp)) {
				return true;
			}
		}
		return false;
		
	}




}
?>