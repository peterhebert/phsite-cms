<?php

class Image extends MY_Controller {

	var $types = array();

	function Image()
	{
		parent::MY_Controller();	
		$this->load->model('File_model');
		$this->load->helper('image');
		$this->load->config('mime_info');

		$this->types = $this->config->item('playable_types');
		$this->data['playable_types'] = $this->config->item('playable_types');
	}
	
	function index()
	{
		$this->load->model('Site_model');
		$this->load->helper('timezone');

		$langs = $this->Site_model->langs_dropdown();
		$segments = $this->uri->segment_array();
		array_shift($segments);
		
		if(count($segments) > 0 && preg_match('#^([a-z]{3})$#',$segments[0]) && array_key_exists($segments[0],$langs))
		{
			$this->File_model->temp_lang = array_shift($segments);
		}
		else
		{
			$this->File_model->temp_lang = $this->Site_model->site_default_lang;
		}
		
		$this->Site_model->temp_lang = $this->File_model->temp_lang;
		// get site metadata
		$this->_load_site_metadata();
		$this->_load_cms_labels();
		
		$this->File_model->file_uri = '/'.implode('/',$segments);
		$exists = $this->File_model->find_media_by_uri(TRUE);

		if($exists)
		{
			$mime_cat = preg_replace('#^([a-z]+)/(.*)$#','$1',$this->File_model->mime_type);
			$media_info = array(
			'file_uri' => $this->File_model->file_uri,
			'media_id' => $this->File_model->media_id,
			'external' => $this->File_model->external,
			'mime_type' => $this->File_model->mime_type,
			'mime_cat' => $mime_cat,
			'publish' => $this->File_model->publish,
			'date_created' => $this->File_model->date_created,
			'title_id' => $this->File_model->title_id,
			'lang' => $this->File_model->temp_lang,
			'title' => $this->File_model->title,
			'uri_title' => $this->File_model->uri_title,
			'subtitle' => $this->File_model->subtitle,
			'keywords' => $this->File_model->keywords,
			);
			if($this->File_model->external == 0)
			{
				$media_info['rel_path'] = BASE_PATH.MEDIA_PATH_ORIG.$this->File_model->file_uri;
				$media_info['server_path'] = ROOT_REL.MEDIA_PATH_ORIG.$this->File_model->file_uri;
			}

			if($this->File_model->keywords != '')
			{
				$kwds = explode(',',$this->File_model->keywords);
				$links = array();
				foreach ($kwds as $tag)
				{
					$keyword = trim($tag);
					$url = 'search/k/'.$keyword;
					$links[] = anchor($url,$keyword);
					
				}
				$media_info['keyword_links'] = implode(', ',$links);
			}

			$updated = $this->File_model->title_updated;

			$this->File_model->temp_id = $this->File_model->media_id;
			$this->File_model->scope = 'media';
			$this->File_model->load_metadata();
			$meta = $this->File_model->metadata;
			
			if(count($meta) > 0)
			{
				foreach($meta as $info)
				{
					if($info['value'] != '')
					{
						$media_info[$info['field_ref_name'] ] = $info['value'] ;
					}
					if($info['date_updated'] > $updated) {
						$updated = $info['date_updated'];
					}
				}
			}
			$media_info['date_updated'] = $updated;
			$media_info['date_updated'] = convert_time($updated, $this->Site_model->site_timezone);
			$media_info['date_updated_W3C'] = convert_time($updated, $this->Site_model->site_timezone, "Y-m-d");
			$this->data['title'] = $this->File_model->title;

	

			$this->data['content'] = $media_info;
			$this->view = 'themes/'.$this->Site_model->theme.'/media';
		}
		else
		{
			$this->output->set_status_header('404');
			$this->data['header'] = $this->cms_labels['server_error_404_header'];
			$this->data['message'] = sprintf($this->cms_labels['server_error_404_media'],$this->File_model->file_uri);
			$this->view = 'themes/'.$this->Site_model->theme.'/error_404';
		}

		$header_view = 'themes/'.$this->Site_model->theme.'/inc_header';
		$header_view_full = APPPATH.'views/'.$header_view.'.php';
		if(file_exists($header_view_full))
		{
			$this->data['html_header'] = $this->load->view($header_view,$this->data,TRUE);
		}

		$this->load->view($this->view,$this->data);
	}

	function root()
	{
		echo BASE_PATH.MEDIA_PATH_ORIG;
	}
	function browser()
	{
		$this->_load_cms_labels();
		
		$this->data['filetree_root'] = BASE_PATH.MEDIA_PATH_ORIG;

		$this->load->view('filetree_browser',$this->data);
	}

	function embed()
	{
		//$file = urldecode($this->input->post('file'));
		$file = '/media/originals/RV8_28.ram';
		
		$fullpath = ROOT_REL.$file;
		$code = '';
		
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->read_file_info();
			
			$this->File_model->file_uri = preg_replace("#^".MEDIA_PATH_ORIG."(.*)$#","$1",$file);
			
			$this->File_model->find_media_by_uri();
			
			if($this->File_model->title != '')
			{
				$title = $this->File_model->title;
			}
			else
			{
				$title = basename($fullpath);
			}
			$filesize = resize_bytes(filesize($fullpath));
	
			$params = array(
			'id' => 'media-'.$this->File_model->media_id, 
			'rel' => $file, 
			);

			if($this->File_model->title != '')
			{
				$params['alt'] = $this->File_model->title;
			}

			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->input->post('size');
				$code = view_image($fullpath,$size,$params);
			}
			else if(in_array($this->File_model->mime_type,$this->types))
			{
				$params['src'] = $file;
				$params[] = 'autostart';
				$code = media_player_object($params);
			}

			else
			{
				$code = "<a href=\"{$file}\" title=\"{$title}\">{$title}</a> ($filesize)";
			}			


		}

		echo $code;

	}

	function ajax_preview ()
	{
		$this->config->load('mime_info');
		$types = $this->config->item('playable_types');

		$file = urldecode($this->input->post('file'));

		$fullpath = ROOT_REL.$file;
		
		$code = '';
		if(file_exists($fullpath))
		{
			$this->File_model->file_current = $fullpath;
			$this->File_model->file_uri = preg_replace("#^".MEDIA_PATH_ORIG."(.*)$#","$1",$file);
			$this->File_model->external = 0;
			$this->File_model->publish = 1;

			$this->File_model->read_file_info();
			$this->File_model->update_file();
			
			if($this->File_model->mime_cat == 'image')
			{
				$size = $this->Site_model->img_size_th;
				
				$code .= '<div class="image-left">';
				$code .= view_image($fullpath,$size,array('id' => 'media-'.$this->File_model->media_id ));
				$code .= '</div>';
			}
			if($this->File_model->title != '')
			{
				$code .= '<p><strong>'.$this->File_model->title."</strong></p>\r";
			}


		} else {
				$code .= '<p><strong>could not find file : '.$file."</strong></p>\r";
		}

		echo $code;

	}

}
?>
