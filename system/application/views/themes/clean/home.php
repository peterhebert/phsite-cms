<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php
if (isset($site['title'])) {
	echo $site['title'];
}
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
if (isset($site['lang'])) {
	echo "<meta name=\"dc.language\" content=\"{$site['lang']}\" />\n";
}
if (isset($site['title'])) {
	echo "<meta name=\"dc.title\" content=\"{$site['title']}\" />\n";
}

if (isset($site['created_W3C'])) {
	echo "<meta name=\"dcterms.issued\" scheme=\"W3CDTF\" content=\"{$site['created_W3C']}\" />\n";
}
if (isset($site['updated_W3C'])) {
	echo "<meta name=\"dcterms.modified\" scheme=\"W3CDTF\" content=\"{$site['updated_W3C']}\" />\n";
}
if (isset($site['description']) && $site['description'] != '') {
	echo "<meta name=\"dc.description\" content=\"{$site['description']}\" />\r";
	echo "<meta name=\"Description\" content=\"{$site['description']}\" />\r";
}
if (isset($site['keywords']) && $site['keywords'] != '') {
	echo "<meta name=\"Keywords\" content=\"{$site['keywords']}\" />\n";
} 
if (isset($site['creator']) && $site['creator'] != '') {
	echo "<meta name=\"dc.creator\" content=\"{$site['creator']}\" />\n";
} 
if (isset($theme_path)) {
	echo "<link href=\"{$theme_path}clean";
	if (!DEV_MODE) {
		echo '-min';
	}
	echo ".css\" rel=\"stylesheet\" type=\"text/css\" />\r";
} 
?>
<meta name="generator" content="phSite CMS" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<div id="content" class="column">
			<?php
			
			if(isset($recent) && is_array($recent) && count($recent) > 0)
			{
				echo "<h2>".$cms_labels['recent_content']."</h2>\r";

				foreach($recent as $index => $entry)
				{
					$cls = '';
					if($index == 0) 
					{
						$cls = ' first';
					}
					echo "<article><div class=\"block article{$cls}\">";
					// generate permalink
					if(isset($entry['uri']) && $entry['uri'] != '') 
					{
						$pl_title = sprintf($cms_labels['permalink_title'],$entry['title']);
					}

					// entry title
					if(isset($entry['title']) && $entry['title'] != '') 
					{
						echo "<h3>";
						if(isset($pl_title))
						{
							echo anchor($entry['uri'],$entry['title']);
						}
						else
						{
							echo $entry['title'];
						}
						echo "</h3>\r";
					}

					// entry subtitle
					if(isset($entry['subtitle']) && $entry['subtitle'] != '') 
					{
						echo "<h4>{$entry['subtitle']}</h4>\r";
					}
					// byline
					if(isset($entry['author'])) 
					{
						$user_name_formatted = $entry['author']['name_first'].' '.$entry['author']['name_last'];
						$byline = sprintf($cms_labels['posted_by'],$entry['date_created'],$user_name_formatted);

						// parent title
						if(isset($entry['parent_title']) && $entry['parent_title'] != '') 
						{
							$to = anchor($entry['parent_uri'],$entry['parent_title']);
							$byline = sprintf($cms_labels['posted_to_by'],$to,$user_name_formatted,$entry['date_created_short']);
						}
						echo "<p>{$byline}</p>\r";
					}
					// image_poster
					if(array_key_exists('attachments',$entry) && array_key_exists('image_poster',$entry)) 
					{

						echo "<div class=\"image-left\">";
				
						foreach($entry['attachments'] as $file)
						{
							if($file['media_id'] == $entry['image_poster'])
							{
								if($file['mime_cat'] == 'image')
								{
									$attributes = array(
									'alt' => $file['title'],
									);
									echo view_image($file['server_path'],$this->Site_model->img_size_sm,$attributes);
								}
							}
						}
						echo "</div>\r";
					}

					// excerpt
					if(isset($entry['excerpt']) && $entry['excerpt'] != '') 
					{
						echo "<p>{$entry['excerpt']}</p>\r";
						$permalink = anchor($entry['uri'],$cms_labels['read_more'],array('title' => $entry['title']));
						echo "<p>{$permalink}</p>\r";
					}
					else
					{
						// if no excerpt, try to print body
						if(isset($entry['body']) && $entry['body'] != '') 
						{
							echo $entry['body'];
						}
					}

					// keywords
					if(isset($entry['keyword_links'])) 
					{
						echo "<p>{$cms_labels['keywords']} : {$entry['keyword_links']}</p>\r";
					}
					echo "</div></article>\r";
				}
			}
			// pagination
			if (isset($recent_total)) {

				$pconfig = array(
				'base_url' => site_url(''),
				'total_rows' => $recent_total,
				'per_page' => $limit,
				'cur_page' => $page,
				'first_text' => $cms_labels['first'],
				'last_text' => $cms_labels['last'],
				'prev_text' => $cms_labels['entries_prev'],
				'next_text' => $cms_labels['entries_next'],
				'page_prefix' => 'p',
				'prev_link' => '',
				'next_link' => '',
				'show_numbers' => false,
				'show_prev_next' => true,
				'show_first_last' => false,
				);
				$this->pagination->initialize($pconfig); 
				$page_links = $this->pagination->create_links_proper();
		
				echo "<p>{$page_links}</p>\r";
			}

			?>
		</div>
		<div id="menu" class="float">
		<?php
		
		if (isset($site['description']) && $site['description'] != '') {
			echo "<h2 class=\"first\">";
			echo $cms_labels['about_this_site'];
			echo "</h2>\r";
			echo "<p>{$site['description']}</p>\r";
		}

		if(isset($menu))
		{
			echo "<h3>".$cms_labels['menu']."</h3>\r";
			echo build_menu($menu,2);
		}
		?>
		</div>
	</div>
</div>
<!-- end page wrapper -->
<?php require_once('inc_footer.php'); ?>
</body>
</html>
