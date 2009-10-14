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


		$links[] = anchor("cms/media/manage",$this->cms_labels['media_manage']);
		$links[] = anchor("cms/media/upload",$this->cms_labels['media_upload']);

		$this->data['sidebar'] = ul($links);

		$config['base_url'] = 'http://example.com/index.php/test/page/';
		$config['per_page'] = $this->Site_model->limit_thumbs;
		$this->Media_model->count_media();
		$config['total_rows'] = $this->Media_model->file_count;

		$this->Media_model->temp_lang = $this->Site_model->site_default_lang;
		$this->Media_model->list_media();


		$html .= "<table class=\"borders\">\r";
		$html .= "\t<thead>\r";
		$html .= "\t\t<tr>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_file\">File</th>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_mime\">MIME type</th>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_publish\">publish</th>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_title\">title</th>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_attach\">attached to</th>\r";
		$html .= "\t\t<th scope=\"col\" id=\"m_action\">action</th>\r";
		$html .= "\t\t</tr>\r";
		$html .= "\t</thead>\r";
		
		$html .= "\t<tbody>\r";
		
		foreach($this->Media_model->media_list as $md)
		{
			$html .= "\t\t<tr>\r";
			$html .= "\t\t<td headers=\"m_file\">";
			
			$path = $this->Media_model->dir_base.$md['file_uri'];
			if(preg_match('#^image/(.*)$#',$md['mime_type']))
			{
				$thumb = view_image($path,$this->Site_model->img_size_th);
				$file = $thumb;
			}
			else
			{
				$path_parts = pathinfo($path);
				$thumb_path = $path_parts['dirname'].'/'.$path_parts['filename'].'.jpg';
				if(file_exists($thumb_path))
				{
					$thumb = view_image($path,$this->Site_model->img_size_th);
					$file = $thumb;
				}
				else
				{
					$file = $path_parts['filename'];
				}
			}
			$uri_edit = 'cms/media/edit/id/'.$md['media_id'];
			if($md['lang_ISO_639_3'] != '')
			{
				$uri_edit .= '/lang/'.$md['lang_ISO_639_3'];
			}
			
			$html .= anchor($uri_edit,$file)."</td>\r";
			

			$mime_array = explode('/',$md['mime_type']);
			$type = $mime_array[0];

			if($type == 'application')
			{
				$this->Media_model->lang_load(); 
				$this->lang->load('mime', $this->Media_model->lang_CI_Name);
				$mime_names = $this->lang->line('mime');
				$type = $mime_names[$path_parts['extension']];
			}
		
			$html .= "\t\t<td headers=\"m_mime\">{$type}</td>\r";
			$html .= "\t\t<td headers=\"m_publish\">{$md['publish']}</td>\r";
			$html .= "\t\t<td headers=\"m_title\">{$md['title']}</td>\r";
			
			$html .= "\t\t<td headers=\"m_attach\">";
			if(count($md['attached_to']) <= 0)
			{
				$html .= "none";
			}
			else
			{
				$html .= "<pre>".$md['attached_to']."</pre>\r";
			}
			$html .= "</td>\r";
			$html .= "\t\t<td headers=\"m_action\">action</td>\r";
			$html .= "\t\t</tr>\r";
		} 
		
		
		$html .= "\t</tbody>\r";
		$html .= "</table>\r";

/*

[media_id] => 1
[file_uri] => /peterhebert.com-redirects.ods
[external] => 0
[mime_type] => application/vnd.oasis.opendocument.spreadsheet
[date_created] => 2009-10-07 11:05:09
[publish] => 1
[title_id] => 
[title] => 
[uri_title] => 
[subtitle] => 
[keywords] => 
[attached_to] => Array
(
)


*/


		$this->data['form'] = $html;

		
		$breadcrumb[] = anchor("cms",$this->cms_labels['phsite_cms']);
		$breadcrumb[] = $this->cms_labels['media'];
		
		$this->data['breadcrumb'] = implode(' : ',$breadcrumb);

		$title_tag[] = $this->cms_labels['media'];
		$title_tag[] = $this->cms_labels['phsite_cms'];
		$this->data['title_tag'] = implode(' | ',$title_tag);
		$this->data['title_h1'] = $this->cms_labels['media'];

		$this->load->view('cms/cms_form', $this->data);
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
		$this->data['title_h1'] = $this->cms_labels['media'];

		$this->data['custom_js'] = "<script type=\"text/javascript\" src=\"/assets/js/jquery_media_filetree.js\"></script>\r";

		$this->load->view('cms/cms_form', $this->data);
	}
	function popup_media()
	{
		$this->load->view('cms/media');
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
		}

		$form = form_open($action, $attributes);
		$form .= form_hidden('media_id',$this->Media_model->media_id);
		$form .= form_hidden('title_id',$this->Media_model->title_id);
		

		$form .= form_fieldset($this->cms_labels['general_info']);

		$form_options = array(
		'id' => 'file_uri',
		'name' => 'file_uri',
		'class' => 'text',
		'value' => $this->Media_model->file_uri
		);
		$form .= "<p>";
		$form .= form_label($this->cms_labels['file'],'file_uri');
		$form .= form_input($form_options);
		$form .= "</p>";

		$files = $this->Media_model->list_recursive();
		$form .= "<div class=\"filetree block\">";
		$form .= $this->build_filetree($files);
		$form .= "</div>";


//		$form .= "<p>";
//		$form .=  form_label($this->cms_labels['mime']);
//		$form .=  $this->Media_model->mime_type;
//		$form .= "</p>\n";

		$form_options = array(1 => $this->cms_labels['yes'], 0 => $this->cms_labels['no']);
/*
			$this->media_id = $row->media_id;
			$this->file_uri = $row->file_uri;
			$this->external = $row->external;
			$this->mime_type = $row->mime_type;
			$this->date_created = $row->date_created;
			$this->publish = $row->publish;
		$form .= "<p>";
		$form .=  form_label($this->cms_labels['file_external'], 'external');
		$form .=  form_dropdown('external', $form_options, $this->Media_model->external);
		$form .= "</p>\n";

*/

		$form .= "<p>";
		$form .=  form_label($this->cms_labels['publish'], 'publish');
		$form .=  form_dropdown('publish', $form_options, $this->Media_model->publish);
		$form .= "</p>\n";

/*		// convert dates to user timezone
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

*/		$form .= form_fieldset_close();
		
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
		foreach($files as $path => $file)
		{
			$path_rel = str_replace($this->Media_model->dir_base,'',$path);
			if(is_array($file))
			{
				$paths = explode('/',$path);
				$filename = array_pop($paths); 
				$filetree .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . $path_rel . "/\">" . htmlentities($filename) . "</a>";
				$filetree .= $this->build_filetree($file);
				$filetree .= "</li>";
			}
			else
			{
				$ext = strtolower(preg_replace('/^.*\./', '', $file));
				$class = '';
				
				if(array_key_exists($ext,$this->Media_model->mime_types))
				{
					$type = explode('/',$this->Media_model->mime_types[$ext]);
					if($type[0] != 'application')
					{
						$class = 'icon_'.$type[0];
					}
				}
				if($class == '')
				{
					$class = 'ext_'.$ext;
				}
				
				$filetree .= "<li class=\"file {$class}\"><a href=\"#\" rel=\"" . $path_rel . "\">" . htmlentities($file) . "</a></li>";

			}
			
		}
		$filetree .= "</ul>\r";	
		return $filetree;

	}

}
?>
