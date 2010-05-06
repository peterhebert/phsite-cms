<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * EXTENDED Application Controller Class
 *
 * This class object is the super class the every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Peter Hebert
 * @link		http://www.peterhebert.com/
 
 */
class MY_Controller extends Controller {

	var $data = array();
	var $cms_labels = array();
	var $custom_js = array();

	
	/**
	 * Constructor
	 *
	 * Calls the initialize() function
	 */
	function MY_Controller()
	{	
		parent::Controller();

		$this->data['img_size_th'] = $this->Site_model->img_size_th;
		$this->data['img_size_sm'] = $this->Site_model->img_size_sm;
		$this->data['img_size_md'] = $this->Site_model->img_size_md;
		$this->data['img_size_lg'] = $this->Site_model->img_size_lg;
		$this->data['img_path_orig'] = realpath(getcwd()).MEDIA_PATH_ORIG;

	}

	function _authenticate()
	{
		$this->load->library('session');
		// if not logged in, redirect to login form
		if(!$this->session->userdata('user_id'))
		{
			redirect('cms/login');
			exit();
		} else {
			$this->data['userdata'] = $this->session->userdata;

			// get site metadata
			$this->Site_model->temp_lang = $this->session->userdata('default_lang');
			$this->_load_site_metadata();
			$this->_load_cms_labels();
		}
	}

	function _load_site_metadata() {

		$base_raw = $_SERVER['PHP_SELF'];
		$base = preg_replace("#^(.*)/index.php(.*)#", "$1", $base_raw);
		$this->data['base_path'] = $base;

		$this->Site_model->load_site();
		if($this->Site_model->temp_lang == '')
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}
		$this->data['site_meta_lang'] = $this->Site_model->temp_lang;
		$this->data['theme_path'] = $base.'/system/application/views/themes/'.$this->Site_model->theme.'/';

		// get site title
		$this->Site_model->temp_id = $this->Site_model->site_id;
		$this->Site_model->scope = 'site';
		$this->Site_model->load_title();

		$this->data['site_meta_title'] = $this->Site_model->title;
		$this->data['site_meta_subtitle'] = $this->Site_model->subtitle;
		$this->data['site_meta_keywords'] = $this->Site_model->keywords;

		// format the date according to site timezone

		$this->data['site_meta_created'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone);
		$this->data['site_meta_created_W3C'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y-m-d");
		$this->data['site_meta_created_W3C'] = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y-m-d");

		$tmp_cr_yr = convert_time($this->Site_model->site_date_created, $this->Site_model->site_timezone, "Y");
		$tmp_mod_yr = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y");

		// if site created year before mod year, show range - if not just created year
		if($tmp_cr_yr < $tmp_mod_yr)
		{
			$tmp_site_dates = $tmp_cr_yr.'-'.$tmp_mod_yr;
		}
		else
		{
			$tmp_site_dates = $tmp_cr_yr;
		}
		

		$this->data['site_meta_updated'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone);
		$this->data['site_meta_updated_W3C'] = convert_time($this->Site_model->site_date_updated, $this->Site_model->site_timezone, "Y-m-d");

		// get site metadata
		$this->Site_model->load_site_metadata();
		
		foreach($this->Site_model->metadata as $fd_id => $meta)
		{
			$key = 'site_meta_'.$meta['field_ref_name'];
			
			if($meta['field_ref_name'] == 'copyright')
			{
				$val = '&#169; '.$tmp_site_dates.' '.$meta['value'];
				
				$val_xml = '&#x00A9;'.$tmp_site_dates.' '.$meta['value'];
				$this->data['site_meta_copyright_xml'] = $val_xml;
			}
			else
			{
				$val = $meta['value'];
			}
			
			$this->data[$key] = $val;
			
		}

	}


	function _load_cms_labels($user=FALSE)
	{	
		$this->load->library('session');

		if($user)
		{
			$user_lang = $this->session->userdata('default_lang');
			if($user_lang)
			{
				$this->Site_model->temp_lang = $user_lang;
			}
		}
		

		if($this->Site_model->temp_lang == '')
		{
			$this->Site_model->temp_lang = $this->Site_model->site_default_lang;
		}
		$this->Site_model->lang_load();

		$this->load->library('langues');

		$this->cms_labels = $this->langues->load('cms', 'cms_labels', $this->Site_model->lang_CI_Name);
		$this->data['cms_labels'] = $this->cms_labels;

    }

	function _media_form_table($params=array(),$base_url = '',$show_all = true,$action_label = '')
	{
		// get filters from post data
		$limit = $this->input->post('limit');
		$media_type = $this->input->post('media_type');
		$this->Media_model->page = 1;
		$html = '';

		// limit
		if(array_key_exists('limit',$params))
		{
			$limit = $params['limit'];
		}
		if(!$limit || $limit == '')
		{
			$limit = 10;
		}
		$this->Media_model->limit = $limit;

		// media type
		if(array_key_exists('type',$params))
		{
			$media_type = $params['type'];
		}
		if ($media_type !== FALSE && $media_type != '')
		{
			$this->Media_model->temp_value = $media_type;
		}

		if(array_key_exists('page',$params))
		{
			$this->Media_model->page = $params['page'];
		}
		
		$total = $this->Media_model->count_media_all();
		
		$this->Media_model->list_media();
		
		$this->lang->load('mime', $this->Media_model->lang_CI_Name);
		$mime_cat_labels = $this->lang->line('mime_cat');
		$mime_labels = $this->lang->line('mime');

		$end = $this->Media_model->page * $limit;
		$start = $end - $limit + 1;
		
		if($end > $total || $end == 0)
		{
			$end = $total;
		}
		if($total > 0)
		{
			$results = sprintf($this->cms_labels['showing_results'],$start,$end,$total);
		}
		else
		{
			$results = $this->cms_labels['no_results'];
		}

		$form_options[''] = $this->cms_labels['all'];
		foreach($this->Media_model->mime_categories as $cat)
		{
			$form_options[$cat] = $mime_cat_labels[$cat];
		}	

		$html .= "<div class=\"form-filter\">\r";
		$html .=  form_label($this->cms_labels['media_type'], 'media_type');
		$html .=  form_dropdown('media_type', $form_options, $media_type);

		$form_options = array(10 => 10,20 => 20,30=>30,40=>40,50=>50);
		$html .=  form_label($this->cms_labels['results_per_page'], 'limit');
		$html .=  form_dropdown('limit', $form_options, $limit);
		$html .= form_submit('filter', $this->cms_labels['results_filter']);
		$html .= "</div>\r";

		$html .= "<p class=\"align-center\">\r";
		$html .= "{$results}<br />\r";

		$this->load->library('pagination');
		$base_url .= '/limit/'.$limit;
		if ($media_type !== FALSE && $media_type != '')
		{
			$base_url .= '/type/'.$media_type;
		}
		$base_url .= '/page/';

		$pconfig['base_url'] = $base_url;
		$pconfig['total_rows'] = $total;
		$pconfig['per_page'] = $limit;
		$pconfig['cur_page'] = $this->Media_model->page;
		$this->pagination->initialize($pconfig);

		$html .= $this->pagination->create_links_proper();
		$html .= "</p>\r";

		$html .= "<table class=\"borders full\">\r";
		$html .= "\t<thead>\r";
		$html .= "\t\t<tr>\r";
		$html .= "\t\t\t<th scope=\"col\" id=\"files\">{$this->cms_labels['file']}</th>\r";
		if($show_all)
		{
			$html .= "\t\t\t<th scope=\"col\" id=\"attached\">{$this->cms_labels['media_attached_to']}</th>\r";
			$html .= "\t\t\t<th scope=\"col\" id=\"published\">{$this->cms_labels['published']}</th>\r";
		}

		$html .= "\t\t\t<th scope=\"col\" id=\"actions\">{$action_label}\r";
		if($action_label == $this->cms_labels['select'])
		{
			$html .= "<ul class=\"option-list\">\r";
			$html .= "<li class=\"hide\"><a href=\"#\" id=\"select-all\">{$this->cms_labels['all']}</a></li>\r";
			$html .= "<li class=\"hide\"><a href=\"#\" id=\"select-none\">{$this->cms_labels['none']}</a></li>\r";
			$html .= "</ul>\r";
		}
		$html .= "</th>\r";
		$html .= "\t\t</tr>\r";
		$html .= "\t</thead>\r";
		$html .= "\t<tbody>\r";

		foreach($this->Media_model->media_list as $media)
		{
			$html .= "\t\t<tr>\r";

			// get mime category
			// get filename and extension
			$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
			$filename = basename($media['file_uri']);
			$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$media['file_uri']));
			$mid = $media['media_id'];

			// determine edit uri
			$edit_uri = 'cms/media/edit/id/'.$media['media_id'];
			if($this->Media_model->temp_lang != '')
			{
				$edit_uri .= '/lang/'.$this->Media_model->temp_lang;
			}

			$html .= "\t\t\t<th id=\"file-{$mid}\" headers=\"files\">";
			if($mime_cat == 'image' && $media['external'] == 0)
			{
				$html .= "<div class=\"image-left\">";
				// get thumbnail
				$img_path = $this->Media_model->dir_base.$media['file_uri'];
				$thumb = view_image($img_path,$this->Site_model->img_size_th,array('alt' => sprintf($this->cms_labels['edit_this'],$filename)));
				$html .= anchor($edit_uri, $thumb, array('title' => sprintf($this->cms_labels['edit_this'],$filename)))."<br />\r";
				$html .= "</div>";
			}

			if(in_array($ext,$mime_icon_exts))
			{
				$html .= "<span class=\"icon ext_{$ext}\">";
			}
			else
			{
				$html .= "<span class=\"icon icon_{$mime_cat}\">";
			}
			if($media['title'] != '' && $media['title'] != $filename)
			{
				$html .= $media['title']."<br />\r";
			}
			$html .= anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));
			$html .= "</span>";
			$html .= "<br />\r".$mime_labels[$ext];

			$html .= "</th>\r";

			if($show_all)
			{
				$html .= "\t\t\t<td headers=\"file-{$mid} attached\">";
	
				if(count($media['attached_to']) > 0)
				{
					$this->Content_model->temp_lang = $this->Media_model->temp_lang;
					$this->Content_model->link_mode = 1;
					$attachment_links = array();
					foreach($media['attached_to'] as $page)
					{
						$this->Content_model->temp_id = $page['content_id'];
						$this->Content_model->load_title();
						$uri = $this->Content_model->generate_uri();
						$attachment_links[] = anchor($uri,$this->Content_model->title, array('title' => sprintf($this->cms_labels['edit_this'],$this->Content_model->title)));
					}
					$html .= ul($attachment_links);
				}
				else
				{
					$html .= $this->cms_labels['none'];
				}
	
				$html .= "</td>\r";

				$options = array($this->cms_labels['no'],$this->cms_labels['yes']);
				$html .= "\t\t\t<td headers=\"file-{$mid} published\">";
				$html .= $options[$media['publish']];
				$html .= "</td>\r";
			}

			$html .= "\t\t\t<td headers=\"file-{$mid} actions\">";

			$options = array(
			'name'        => "select[]",
			'value'       => $media['media_id'],
			);
			
			$html .= form_checkbox($options);
			$html .= "</td>\r";
			$html .= "\t\t</tr>\r";
		}
		$html .= "\t</tbody>\r";
		$html .= "</table>\r";

		return $html;

	}

	function _media_form_data($params=array(),$base_url = '',$show_all = true,$action_label = '')
	{
		$form_data['cms_labels'] = $this->cms_labels;
		
		// get filters from post data
		$limit = $this->input->post('limit');
		$media_type = $this->input->post('media_type');
		$this->Media_model->page = 1;
		$html = '';

		// limit
		if(array_key_exists('limit',$params))
		{
			$limit = $params['limit'];
		}
		if(!$limit || $limit == '')
		{
			$limit = 10;
		}
		$this->Media_model->limit = $limit;

		// media type
		if(array_key_exists('type',$params))
		{
			$media_type = $params['type'];
		}
		if ($media_type !== FALSE && $media_type != '')
		{
			$this->Media_model->temp_value = $media_type;
		}

		if(array_key_exists('page',$params))
		{
			$this->Media_model->page = $params['page'];
		}
		
		$total = $this->Media_model->count_media_all();
		
		$this->Media_model->list_media();
		
		$this->lang->load('mime', $this->Media_model->lang_CI_Name);
		$mime_cat_labels = $this->lang->line('mime_cat');
		$mime_labels = $this->lang->line('mime');

		$end = $this->Media_model->page * $limit;
		$start = $end - $limit + 1;
		
		if($end > $total || $end == 0)
		{
			$end = $total;
		}
		if($total > 0)
		{
			$results[] = sprintf($this->cms_labels['showing_results'],$start,$end,$total);
		}
		else
		{
			$results[] = $this->cms_labels['no_results'];
		}

		$form_options[''] = $this->cms_labels['all'];
		foreach($this->Media_model->mime_categories as $cat)
		{
			$form_options[$cat] = $mime_cat_labels[$cat];
		}
		$form_data['media_types'] = $form_options;	
		$form_data['media_type'] = $media_type;	

		$form_data['per_page'] = array(10 => 10,20 => 20,30=>30,40=>40,50=>50);
		$form_data['limit'] = $limit;	


		$this->load->library('pagination');
		$base_url .= '/limit/'.$limit;
		if ($media_type !== FALSE && $media_type != '')
		{
			$base_url .= '/type/'.$media_type;
		}
		$base_url .= '/page/';

		$pconfig['base_url'] = $base_url;
		$pconfig['total_rows'] = $total;
		$pconfig['per_page'] = $limit;
		$pconfig['cur_page'] = $this->Media_model->page;
		$this->pagination->initialize($pconfig);

		$pagination = $this->pagination->create_links_proper();
		if($pagination != '')
		{
			$results[] = $pagination;
		}
		$form_data['results_pagination'] = implode("<br />\r",$results);	

		$form_data['show_all'] = $show_all;	
		$form_data['action_label'] = $action_label;	

		$media_array = array();
		foreach($this->Media_model->media_list as $media)
		{
			$tmp_array = $media;

			// get mime category
			// get filename and extension
			$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
			$filename = basename($media['file_uri']);
			$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$media['file_uri']));
			$mid = $media['media_id'];

			// determine edit uri
			$edit_uri = 'cms/media/edit/id/'.$media['media_id'];
			if($this->Media_model->temp_lang != '')
			{
				$edit_uri .= '/lang/'.$this->Media_model->temp_lang;
			}

			if($mime_cat == 'image' && $media['external'] == 0)
			{
				// get thumbnail
				$img_path = $this->Media_model->dir_base.$media['file_uri'];
				$thumb = view_image($img_path,$this->Site_model->img_size_th,array('alt' => sprintf($this->cms_labels['edit_this'],$filename)));
				$tmp_array['thumbnail'] = anchor($edit_uri, $thumb, array('title' => sprintf($this->cms_labels['edit_this'],$filename)))."<br />\r";

			}

			if(in_array($ext,$mime_icon_exts))
			{
				$tmp_array['icon_class'] = "ext_{$ext}";
			}
			else
			{
				$tmp_array['icon_class'] = "icon_{$mime_cat}";
			}
			
			$tmp_array['edit_link'] = anchor($edit_uri, $filename, array('title' => sprintf($this->cms_labels['edit_this'],$filename)));

			$tmp_array['media_type'] = $mime_labels[$ext];

			if($show_all)
			{
	
				if(count($media['attached_to']) > 0)
				{
					$this->Content_model->temp_lang = $this->Media_model->temp_lang;
					$this->Content_model->link_mode = 1;
					$attachment_links = array();
					foreach($media['attached_to'] as $page)
					{
						$this->Content_model->temp_id = $page['content_id'];
						$this->Content_model->load_title();
						$uri = $this->Content_model->generate_uri();
						$attachment_links[] = anchor($uri,$this->Content_model->title, array('title' => sprintf($this->cms_labels['edit_this'],$this->Content_model->title)));
					}
					$tmp_array['attachment_links'] = ul($attachment_links);
				}
				else
				{
					$tmp_array['attachment_links'] = $this->cms_labels['none'];
				}
	
				$options = array($this->cms_labels['no'],$this->cms_labels['yes']);
				$tmp_array['published'] = $options[$media['publish']];
			}
		}
		$html = $this->view('cms/embed/media_manage_table',$form_data,true);
		return $html;

	}
}
// END _Controller class

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */