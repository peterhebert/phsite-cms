<?php
class Site_model extends Model {

	// ID of the content for the home page (0 means auto-generate it)
	var $home_page_id;

	// folder for current theme in '/system/application/views/'
	var $theme;

	// default site language
	var $default_lang;

	// user ID of the site webmaster (used for contact info and RSS feeds)
	var $webmaster_id;

	// path to phsite folder
	var $install_path;

	// limit for queries on blog entries per page
	var $limit_posts;
	
	// limit for queries on thumbnails per page in galleries
	var $limit_thumbs;

	// site metadata (language-specific)
	var $site_meta_id;
	var $name;
	var $description;
	var $keywords;
	var $copyright;
	var $lang;
	var $updated;

    function Site_model()
    {
        // Call the Model constructor
        parent::Model();

		// update site prefs
		$docroot = $_SERVER['DOCUMENT_ROOT'];
		$script = $_SERVER['SCRIPT_NAME'];
		$basedir = preg_replace('/(.*)index\.php(.*)/','\1',$script);
		$this->install_path = $docroot.$basedir;
    }

    // load the site prefs from config file
	function load_prefs()
    {
		$this->config->load('phsite_prefs');

		$this->home_page_id = $this->config->item('home_page_id');
		$this->theme = $this->config->item('theme');
		$this->default_lang = $this->config->item('default_lang');
		$this->webmaster_id = $this->config->item('webmaster_id');
		$this->limit_posts = $this->config->item('limit_posts');
		$this->limit_thumbs = $this->config->item('limit_thumbs');
    }

    function update_prefs()
    {

		// get document directory of index.php
		$dir = $this->install_path.'system/application/config';
		$filename = $dir.'/phsite_prefs.php';
	
		$prefs  = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n\n";
		$prefs .= "// phSite Global Preferences\n\n";
		
		$prefs .= "// ID of the content for the home page (0 means auto-generate it)\n";
		$prefs .= "\$config['home_page_id'] = ";
		$prefs .= $this->home_page_id;
		$prefs .= ";\n\n";
		
		$prefs .= "// folder for current theme in '/system/application/views/'\n";
		$prefs .= "\$config['theme'] = '";
		$prefs .= $this->theme;
		$prefs .= "';\n\n";

		$prefs .= "// default site language (ISO 639-3 code)\n";
		$prefs .= "\$config['default_lang'] = '";
		$prefs .= $this->default_lang;
		$prefs .= "';\n\n";

		$prefs .= "// user ID of the site webmaster (used for contact info and RSS feeds)\n";
		$prefs .= "\$config['webmaster_id'] = ";
		$prefs .= $this->webmaster_id;
		$prefs .= ";\n\n";
		
		$prefs .= "// limit for queries on blog entries per page\n";
		$prefs .= "\$config['limit_posts'] = ";
		$prefs .= $this->limit_posts;
		$prefs .= ";\n\n";
		
		$prefs .= "// limit for queries on thumbnails per page in galleries\n";
		$prefs .= "\$config['limit_thumbs'] = ";
		$prefs .= $this->limit_thumbs;
		$prefs .= ";\n\n";
		
		$prefs .= "/* End of file phsite_prefs.php */\n";
		$prefs .= "/* Location: ./system/application/config/phsite_prefs.php */\n";
	
		// now write preferences to config file
		if (is_dir($dir)) {
			chdir($dir);
			
			$file = fopen($filename,'w');
			if(fwrite($file, $prefs)) {
				$msg = '';
			}
			fclose($file);
	
		}
	}


}

?>
