<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

if ( ! function_exists('is_lang'))
{	
	function is_lang($value)
	{
		
		// read tab-delimited file of ISO 639 lang codes
		$iso_all = read_file('system/application/language/iso-639-3.tab');
		//Id	Part2B	Part2T	Part1	Scope	Language_Type	Ref_Name	Comment
		$iso_639 = preg_replace('/\r\n|\r/', "\n", $iso_all);
		$langs = explode("\n",$iso_639);
		
		foreach($langs as $line) {
			$temp = explode("\t",$line);

			if(in_array($value, $temp)) {
				return true;
			}
			
		}
		return false;
		
	}

}

if ( ! function_exists('convert_date'))
{	
	function convert_date($str)
	{
		$delim = array('\\', '/');
		$newstr = str_replace($delim, '-', $str);

		if ($timestamp = strtotime($newstr)) {
			$date = date('Y-m-d H:i:s', $timestamp);
			return $date;
		} else {
			return FALSE;
		}		
	}

}

/* End of file test_helper.php */
/* Location: ./system/application/helpers/test_helper.php */