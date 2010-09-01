<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**

// ------------------------------------------------------------------------

/**
 * 'Langues' Class - alternative to CodeIgniter's language class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Language
 * @author		Peter Hebert
 * @link		http://www.peterhebert.com/
 */
class Langues {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Langues()
	{
		log_message('debug', "Langues Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Load a language file
	 *
	 * @access	public
	 * @param	mixed	the name of the language file to be loaded. Can be an array
	 * @param	string	the language (english, etc.)
	 * @return	mixed
	 */
	function load($langfile, $line, $idiom)
	{
		
		$langfile = $langfile.'_lang'.EXT;

		if ($idiom == '')
		{
			show_error('no language specified');
		}
		
		$langpath = 'language/'.$idiom.'/'.$langfile;

		unset($lang);

		// Determine where the language file is and load it
		if (file_exists(APPPATH.$langpath))
		{
			include(APPPATH.$langpath);
		}
		else
		{
			if (file_exists(BASEPATH.$langpath))
			{
				include(BASEPATH.$langpath);
			}
			else
			{
				show_error('Unable to load the requested language file: '.$langpath);
			}
		}

		if(is_array($lang) && array_key_exists($line,$lang))
		{
			return $lang[$line];	
		}
		else
		{
			show_error("cannot find line '{$line}'");
		}


	}


}
// END Language Class

/* End of file Language.php */
/* Location: ./system/libraries/Language.php */