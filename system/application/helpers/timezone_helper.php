<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

// convert time from one time zone to another
if ( ! function_exists('convert_time'))
{
	function convert_time($time, $zone_to, $format = "Y-m-d H:i:s", $zone_from = '')
	{
		if($zone_from == '')
		{
			$zone_from = date_default_timezone_get();
		}
		
		$TZ_to = new DateTimeZone($zone_to);
		$TZ_from = new DateTimeZone($zone_from);

		$time_object = new DateTime($time, $TZ_from);
		date_timezone_set ( $time_object, $TZ_to );

		$time_converted = date_format ( $time_object, $format );

/*

DateTime::ATOM
DATE_ATOM
    Atom (example: 2005-08-15T15:52:01+00:00) 
DateTime::COOKIE
DATE_COOKIE
    HTTP Cookies (example: Monday, 15-Aug-05 15:52:01 UTC) 
DateTime::ISO8601
DATE_ISO8601
    ISO-8601 (example: 2005-08-15T15:52:01+0000) 
DateTime::RFC822
DATE_RFC822
    RFC 822 (example: Mon, 15 Aug 05 15:52:01 +0000) 
DateTime::RFC850
DATE_RFC850
    RFC 850 (example: Monday, 15-Aug-05 15:52:01 UTC) 
DateTime::RFC1036
DATE_RFC1036
    RFC 1036 (example: Mon, 15 Aug 05 15:52:01 +0000) 
DateTime::RFC1123
DATE_RFC1123
    RFC 1123 (example: Mon, 15 Aug 2005 15:52:01 +0000) 
DateTime::RFC2822
DATE_RFC2822
    RFC 2822 (Mon, 15 Aug 2005 15:52:01 +0000) 
DateTime::RFC3339
DATE_RFC3339
    Same as DATE_ATOM (since PHP 5.1.3) 
DateTime::RSS
DATE_RSS
    RSS (Mon, 15 Aug 2005 15:52:01 +0000) 
DateTime::W3C
DATE_W3C
    World Wide Web Consortium (example: 2005-08-15T15:52:01+00:00) 
	*/

		return $time_converted;
	}
}
	
// generate array of timezones for dropdown list
if ( ! function_exists('timezone_dropdown'))
{
	function timezone_dropdown()
	{
		$timezones = timezone_identifiers_list();
		$continents = array('Africa','America','Antarctica','Arctic','Asia','Atlantic','Australia','Europe','Indian','Pacific');
		$locations = array();
		
		foreach ($timezones as $zone)
		{
			$parts = explode('/', $zone);
		   
			// Only use "friendly" continent names
			if (in_array($parts[0],$continents,true) )
			{       
				$locations[$zone] = str_replace('_',' ',$zone);
			}
		}

		return $locations;
	}

}
/* End of file timezone_helper.php */
/* Location: ./system/application/helpers/timezone_helper.php */