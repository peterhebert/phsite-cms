<?php

class Media extends MY_Controller {
		
	function Media()
	{
		parent::MY_Controller();
		$this->load->helper(array('form','url','file','image'));
		$this->load->model('Media_model');
		$this->_load_cms_labels();
		$this->_load_site_metadata();
	}
	
	function index()
	{	

		$this->Media_model->temp_lang = $this->Site_model->site_default_lang;
		$this->Media_model->lang_load();

		//$files = $this->Media_model->list_media_recursive();
		$this->Media_model->list_media();
		$action = 'cms/media';
		$attributes = array(
		'id' => 'filter_media'
		);
		$this->lang->load('mime', $this->Media_model->lang_CI_Name);
		$mime_labels = $this->lang->line('mime_cat');
		$form = form_open($action, $attributes);
		
		$form_options[''] = 'any';
		foreach($this->Media_model->mime_categories as $cat)
		{
			$form_options[$cat] = $mime_labels[$cat];
		}	

		$form .= "<p>";
		$form .=  form_label($this->cms_labels['media_type'], 'media_type');
		$form .=  form_dropdown('media_type', $form_options, '');
		$form .= "</p>\n";

		$form_options = array(10,20,30,40,50);
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['results_per_page'], 'limit');
		$form .=  form_dropdown('limit', $form_options, 10);
		$form .= "</p>\n";

		$form .= "<p>";
		$form .= form_submit('submit', $this->cms_labels['results_filter']);
		$form .= "</p>\n";
		$form .= form_close();

/*
<form id="filter_media" method="post" action="">
	<p>
		<label for="date_modifier">date</label>
		<select name="date_modifier" id="date_modifier">
			<option value="" selected="selected">on / between</option>
			<option value="<">before</option>
			<option value=">">after</option>
		</select>
		<input name="date_1" id="date_1" type="text" />
		<label for="date_2">and</label>
		<input name="date_2" id="date_2" type="text" />
		<input type="submit" name="submit" id="submit" value="Filter Results" />
	</p>
</form>
*/


		$links[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
		$links[] = anchor("cms/media/upload",$this->cms_labels['media_upload']);

		$form .= ul($links);
		$this->data['sidebar'] = $form;



/*
$media_html = "<pre>";
$media_html .= print_r($this->Media_model->media_list,true);
$media_html .= "</pre>\r";

thumbnail / icon
[file_uri] => /peterhebert.com-redirects.ods
[mime_type] => application/vnd.oasis.opendocument.spreadsheet
[date_created] => 2009-10-16 14:05:27
[publish] => 1
[title] => title
[attached_to] => Array
*/
	$media_html = '';

		foreach($this->Media_model->media_list as $media)
		{
			$media_html .= '<p>';
			// determine edit uri
			$edit_uri = 'cms/media/edit/id/'.$media['media_id'];
			if($this->Media_model->temp_lang != '')
			{
				$edit_uri .= '/lang/'.$this->Media_model->temp_lang;
			}
			// get mime category
			// get filename and extension
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$media['mime_type']);
			
			if($mime_cat == 'image' && $media['external'] == 0)
			{
				// get thumbnail
				$img_path = $this->Media_model->dir_base.$media['file_uri'];
				$media_html .= view_image($img_path,$this->Site_model->img_size_th,array('alt' => $media['title']));
			}
			
			$filename = basename($media['file_uri']);
			$ext = strtolower(preg_replace('#^(.*)\.([a-zA-Z0-9]+)$#','$2',$media['file_uri']));

			$mime_icon_exts = array('ai', 'doc', 'docx', 'eps', 'gz', 'gzip', 'odb', 'odf', 'odg', 'odm', 'odp', 'ods', 'odt', 'pdf', 'pps', 'ppsx', 'ppt', 'pptx', 'ps', 'ram', 'rm', 'sit', 'sldx', 'swf', 'tar', 'tgz', 'wp', 'wp5', 'wp5', 'wp6', 'wpd', 'wpd', 'wpl', 'xls', 'xlsx', 'zip');
			if(in_array($ext,$mime_icon_exts))
			{
				$media_html .= "<span class=\"icon ext_{$ext}\">";
			}
			else
			{
				$media_html .= "<span class=\"icon icon_{$mime_cat}\">";
			}

			$media_html .= anchor($edit_uri, $filename);
			$media_html .= "</span>";
			$media_html .= "</p>\r";



/*
			
		
		
			$thumb = '';
				if()
				{
				}
				else
				{
					if(url_exists($media['file_uri']))
					{
						$image_properties = array(
						'src' => $media['file_uri'],
						'alt' => $media['title'],
						'style' => 'width: '.$this->Media_model->img_size_th.'px',
						);
						$thumb = img($image_properties);
					}
				}
			}
		
			if($thumb === false || $thumb == '')
			{
			}
			else
			{
				$media_html .= anchor($edit_uri, $thumb);
			}
		
			$media_html .= anchor($edit_uri, $fname);
		
			if($thumb === false || $thumb == '')
			{
				$media_html .= "</span>";
			}
*/		}
/*
[media_id] => 10
[file_uri] => /peterhebert.com-redirects.ods
[external] => 0
[mime_type] => application/vnd.oasis.opendocument.spreadsheet
[date_created] => 2009-10-16 14:05:27
[publish] => 1
[title_id] => 48
[title] => title
[uri_title] => title
[subtitle] => 
[keywords] => baseball, Canada Day, holiday, Ottawa, sports
[lang_ISO_639_3] => eng
[attached_to] => Array
(
)


*/
		$this->data['form'] = $media_html;
		
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['media'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media'];

		$this->data['custom_js'] = "<script type=\"text/javascript\" src=\"/assets/js/jquery_media_filetree.js\"></script>\r";

		$this->load->view('cms/cms_form', $this->data);
	}

	function info()
	{	

		$this->Media_model->temp_id = $this->uri->segment(4);
		$this->Media_model->load_media();
		$this->data['file_uri'] = $this->Media_model->file_uri;
/*		)
		{
			//$this->Media_model->load_type_by_mime();
			$this->Media_model->scope = 'media';
			$this->Media_model->temp_id = $this->Media_model->media_id;
			$this->Media_model->temp_lang = $this->Site_model->site_default_lang);
			
			//$this->Media_model->load_title();
		}
		
		if($this->Media_model->title != '')
		{
			$this->data['title'] = $this->Media_model->title;
		}
		
*/

		$this->load->view('cms/media_info', $this->data);
		
	}

	function manage()
	{	
		$this->Media_model->temp_lang = $this->Site_model->site_default_lang;

		// get media path from uri
		$media_root = $this->Media_model->dir_base;

		$raw_segs = $this->uri->segment_array();
		if(count($raw_segs) > 3)
		{
			$uri_array = array_slice($raw_segs, 3);
			$dir_uri = implode('/',$uri_array);
			$dir_display = $dir_uri;
			$my_uri = $this->Media_model->dir_base.'/'.$dir_uri;
			
		}
		else
		{
			$my_uri = $this->Media_model->dir_base;
			$dir_uri = '';
			$dir_display = 'media root';
		}

		$media_root = $this->Media_model->dir_base;

		//get list of directories in media folder
		$this->Media_model->dir_current = $my_uri;
		$this->Media_model->list_dirs();
		$subdirs = array();
		foreach($this->Media_model->dir_list_rel as $full_dir => $dir)
		{
			$dir_rel = trim(str_replace(MEDIA_PATH_ORIG,'',$dir),'/');
			$link = 'cms/media/manage/'.$dir_rel;
			$title = trim($dir_rel,'/');
			
			$subdirs[] = anchor($link,$title);
		}


		$form = "<p>Current directory: ".$dir_display."</p>";
		if(count($subdirs) > 0)
		{
			$form .= "<h2>".$this->cms_labels['media_subdirs']."</h2>";
			$form .= ul($subdirs);
		}
		
		// scan files in current directory

		$this->Media_model->dir_current = $my_uri;
		$this->Media_model->list_files_unique();

		$this->Media_model->update_media_batch();

		if(count($this->Media_model->file_list) > 0)
		{
			$form .= "<h2>Files</h2>";
			$form .= "<pre>";
			$form .= print_r($this->Media_model->file_list,true);
			$form .= "</pre>\r";

		}
		else
		{
			$form .= "<p>no files in this directory</p>";
		}



		$this->data['form'] = $form;

		$msg = $this->Media_model->_output_messages();
		if($msg)
		{
			$this->data['msg'] = $msg;
		}



		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$dirz = trim($dir_uri,'/');
		if($dirz != '')
		{
			$breadcrumb[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
			// build breadcrumb from dir path
			$dir_array = explode('/',$dirz);

			$dir_count = count($dir_array);
			$cur = 1;
			$base_dir = 'cms/media/manage';

			$mydir = '';
			
			foreach ($dir_array as $tmp_dir)
			{
				$mydir .= '/'.$tmp_dir;
				$link = $base_dir.$mydir;
				if($cur < $dir_count)
				{
					$breadcrumb[] = anchor($link,$tmp_dir);
				}
				else
				{
					$breadcrumb[] = $tmp_dir;
				}
				$cur = $cur + 1;
			}
			
			$title_tag[] = str_replace('/',' | ',$dirz);
		}
		else
		{
			$breadcrumb[] = $this->cms_labels['media_manage'];
		}

		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_manage'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_manage'];

		$this->load->view('cms/cms_form', $this->data);

	}

	function edit()
	{
		$params = $this->uri->uri_to_assoc(4);
		if( array_key_exists('id',$params) && $this->Media_model->media_id == '')
		{
			$this->Media_model->media_id = $params['id'];
		}
		if( array_key_exists('lang',$params) )
		{
			$this->Media_model->temp_lang = $params['lang'];
		}
		
		$this->_form_edit();

		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = anchor("cms/media",$this->cms_labels['media']);
		$breadcrumb[] = $this->cms_labels['media_edit'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media_edit'];
		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media_edit'];


		$this->data['sidebar'] = $this->_media_meta_menu();

		$this->load->view('cms/cms_form', $this->data);
	}

	function _form_edit()
	{
		$this->load->helper('form');

		$action = 'cms/media/edit';
		$attributes = array(
		'name' => 'media_editor',
		'id' => 'media_editor'
		);

		if( $this->Media_model->media_id != '')
		{
			$this->Media_model->temp_id = $this->Media_model->media_id;
			$this->Media_model->load_media();
			$this->Media_model->load_type_by_mime();
			$this->Media_model->load_media();

			$this->Media_model->load_title();
			$this->Media_model->scope = 'types';
			$this->Media_model->temp_id = $this->Media_model->type_id;
			$this->Media_model->list_fields_related();

			$this->Media_model->scope = 'media';
			$this->Media_model->temp_id = $this->Media_model->media_id;
			$this->Media_model->load_metadata();

		}

		$form = form_open($action, $attributes);
		$form .= form_hidden('media_id',$this->Media_model->media_id);
		$form .= form_hidden('title_id',$this->Media_model->title_id);
		
		$form .= form_fieldset($this->cms_labels['general_info']);

		if($this->Media_model->type_uri_name == 'image')
		{
			if($this->Media_model->external == 0)
			{
				$file_path_full = $this->Media_model->dir_base.$this->Media_model->file_uri;
				$form .= "<p>";
				$form .= view_image($file_path_full,$this->Site_model->img_size_md,$this->Media_model->title);
				$form .= "</p>";
			}
			else
			{
				$em_size = $this->Site_model->img_size_md / 15;
				$form_options = array(
				'src' => $this->Media_model->file_uri,
				'alt' => $this->Media_model->title,
				'style' => "width: {$em_size}em",
				'title' => $this->Media_model->title,
				);

				$form .= "<p>";
				$form .= img($form_options);
				$form .= "</p>";
			}
		}

		$form .= "<div class=\"row\">";
		$form .= form_label($this->cms_labels['file']);
		$form .= "<div class=\"filename\">";
		$filename = str_replace('/','/ ',$this->Media_model->file_uri);
		$filename = str_replace('/ / ','//',$filename);
		$form .= $filename;
		$form .= "</div>";
		$form .= "</div>";

		$pinfo = pathinfo($this->Media_model->file_uri);
		$ext = $pinfo['extension'];
		
		$this->Media_model->lang_load();
		$this->lang->load('mime', $this->Media_model->lang_CI_Name);
		$mime_names = $this->lang->line('mime');

		$form .= "<p>";
		$form .= form_label($this->cms_labels['mime']);
		if(array_key_exists($ext,$mime_names))
		{
			$form .= $mime_names[$ext];
		}
		else
		{
			$form .= $this->Media_model->mime_type;
		}
		$form .= "</p>";

		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);

		$form .= "<p>";
		$form .=  form_label($this->cms_labels['publish'], 'publish');
		$form .=  form_dropdown('publish', $form_options, $this->Media_model->publish);
		$form .= "</p>\n";

		// convert dates to user timezone
		$sys_zone = date_default_timezone_get();
		$usr_zone = $this->session->userdata('timezone');
		
		if($usr_zone !== FALSE && $usr_zone != $sys_zone)
		{
			if($this->Media_model->date_created != '')
			{
				$this->Media_model->date_created = convert_time($this->Media_model->date_created, $usr_zone);
			}
	
		}

		if($this->Media_model->date_created == '0000-00-00 00:00:00')
		{
			$this->Media_model->date_created = '';
		}

		$form_options = array(
		'name'        => 'date_created',
		'id'          => 'date_created',
		'class'	=> 'small_input',
		'value' => $this->Media_model->date_created,
		);
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['date_created'], 'date_created');
		$form .=  form_input($form_options);
		$form .= "</p>\n";

		$form .= form_fieldset_close();
		
		$form .= form_fieldset($this->cms_labels['metadata']);

		$form_options = $this->Media_model->langs_dropdown();
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['language'], 'media_lang');
		$form .=  form_dropdown('media_lang', $form_options, $this->Media_model->temp_lang);
		$form .= "</p>\n";

		$form_options = array(
		'id' => 'title',
		'name' => 'title',
		'class' => 'text',
		'value' => $this->Media_model->title
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['title'],'title');
		$form .= form_input($form_options);
		$form .= "</p>";

		$form_options = array(
		'id' => 'subtitle',
		'name' => 'subtitle',
		'class' => 'text',
		'value' => $this->Media_model->subtitle
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['subtitle'],'subtitle');
		$form .= form_input($form_options);
		$form .= "</p>";

/*$form .= "<pre>";
$form .= print_r($this->Media_model->metadata,true);
$form .= "</pre>\r";
*/
		// start meta fields
		foreach($this->Media_model->metadata as $index => $fd)
		{
			// get field data if it does not exist
			if(array_key_exists('field_id',$fd))
			{
				$tmp_ref_name = $fd['field_ref_name'];
				$tmp_label = $fd['field_ref_name'];
				$tmp_needs_lang = $fd['field_needs_lang'];
				$tmp_meta_id = $fd['meta_id'];
				$tmp_required = $fd['required'];
				$tmp_input_type = $fd['field_input_type'];
				$tmp_validation = $fd['field_validation'];
			}
			else
			{
				$this->Media_model->temp_id = $index;
				$this->Media_model->load_field();

				$tmp_ref_name = $this->Media_model->field_ref_name;
				$tmp_needs_lang = $this->Media_model->field_needs_lang;
				$tmp_input_type = $this->Media_model->field_input_type;
				$tmp_validation = $this->Media_model->field_validation;
				$tmp_label = $tmp_ref_name;
				$tmp_meta_id = '';
				$tmp_required = '';
			}

			// hidden fields
			$form_options = array(
			'field_id[]' =>  $index,
			'field_ref_name[]' =>  $tmp_ref_name,
			'meta_id[]' => $tmp_meta_id,
			'required[]' =>  $tmp_required,
			'field_validation[]' =>  $tmp_validation,
			'field_needs_lang[]' => $tmp_needs_lang
			);
			$form .= form_hidden($form_options);

			$this->Media_model->label_scope = 'field';
			$this->Media_model->temp_id = $index;
			$this->Media_model->temp_lang = $this->Site_model->temp_lang;

			if($this->Media_model->load_label())
			{
				$tmp_label = $this->Media_model->label;
			}

			$form .= "<p>\r";
			$form .= form_label($tmp_label, $tmp_ref_name);

			$form_options = array(
			'id' => $tmp_ref_name,
			'name' => 'value_'.$index,
			'value' => $fd['value'],
			'class' => $tmp_input_type
			);

			switch($tmp_input_type) {
			case 'textarea':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			case 'richtext':
				$form_options['cols'] = 38;
				$form .=  form_textarea($form_options);
				break;
			default:
				$form .=  form_input($form_options);
			}


			$form .= "</p>\r";
		}
		// end meta fields

		$form_options = array(
		'id' => 'keywords',
		'name' => 'keywords',
		'class' => 'textarea',
		'value' => $this->Media_model->keywords
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['keywords'],'keywords');
		$form .=  form_textarea($form_options);
		$form .= "</p>";

		$form .= form_fieldset_close();

		$form .= "<p>";
		$form .= form_submit('submit', $this->cms_labels['save']);
		$form .= "</p>\n";
		$form .= form_close();

		$this->data['form'] = $form;

	}

	function build_filetree($files=array())
	{
		$filetree = "<ul>\r";
		$base_uri = 'cms/media/edit/id/';
		foreach($files as $path_rel => $file)
		{

			$paths = explode('/',$path_rel);
			$filename = array_pop($paths); 

			if($file['type'] == 'file')
			{

				$ext = strtolower(preg_replace('/^.*\./', '', $filename));
				$classes = array();
				
				if(array_key_exists($ext,$this->Media_model->mime_types))
				{
					$type = explode('/',$this->Media_model->mime_types[$ext]);
					if($type[0] != 'application')
					{
						$classes[] = 'icon_'.$type[0];
					}
				}
				if(count($classes) <= 0)
				{
					$classes[] = 'file ext_'.$ext;
				}
/*
				$uri = $base_uri.$file['media_id'];
				if($this->Media_model->temp_lang != '')
				{
					$uri .= '/lang/'.$this->Media_model->temp_lang;
				}
*/


				$info_uri = 'cms/media/info/'.$file['media_id'];

				$class = implode(' ',$classes);			
				
/*				if($file['title'] != '')
				{
					//$li = anchor($uri, $file['title'], array('rel' => $path_rel));
					$li = "<a href=\"#\" rel=\"" . $info_uri . "/\">" . htmlentities($file['title']) . "</a>";
				}
				else
				{
*/					//$li = anchor($uri, $filename, array('rel' => $path_rel));
					$li = "<a href=\"#\" rel=\"" . $info_uri . "/\">" . htmlentities($filename) . "</a>";
				//}

			}
			else
			{
				$class = 'directory collapsed';			
				$li = "<a href=\"#\">" . htmlentities($filename) . "</a>";
				$li .= $this->build_filetree($file['files']);
			}

				$filetree .= "<li class=\"{$class}\">{$li}</li>\r";

		}
		$filetree .= "</ul>\r";

		return $filetree;
	}

	function _media_meta_menu()
    {
		$menu = '';
		
		if($this->Media_model->media_id != '')
		{
		
			// list active languages
			$lang_links = array();
			$this->Media_model->list_langs();
			foreach($this->Media_model->lang_list as $llang)
			{
				$temp_link = 'cms/media/edit/id/'.$this->Media_model->media_id.'/lang/'.$llang['ISO_639_3'];
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
			
			$menu .=  "<ul>\r";
			$menu .=  "<li>".$this->cms_labels['edit_by_lang']."\r";
			$menu .= ul($lang_links);
			$menu .=  "</li>\r";
	
			$menu .=  "</ul>\r";

		}

		return $menu;
	}

}
?>
