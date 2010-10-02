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

				foreach($recent as $entry)
				{
				
					if(array_key_exists('title',$entry) && $entry['title'] != '') 
					{
						$title = $entry['title'];
						if(array_key_exists('uri',$entry)) 
						{
							$pl_title = sprintf($cms_labels['permalink_title'],$entry['title']);
							$title = anchor($entry['uri'], $entry['title'], array('title' => $pl_title));
					
						}
						echo "<h3>".$title."</h3>\r";
					}
				
					if(array_key_exists('subtitle',$entry) && $entry['subtitle'] != '') 
					{
						echo "<h4>{$entry['subtitle']}</h4>\r";
					}
					
					if(array_key_exists('author',$entry)) 
					{
						$user_name_formatted = $entry['author']['name_first'].' '.$entry['author']['name_last'];
						$byline = sprintf($cms_labels['posted_by'],$entry['date_created'],$user_name_formatted);
						echo "<p>{$byline}</p>\r";
					}
					
					if(array_key_exists('excerpt',$entry) && $entry['excerpt'] != '') 
					{
						echo $entry['excerpt'];
					} 
					else if (array_key_exists('body',$entry) && $entry['body'] != '') 
					{
						echo $entry['body'];
					}
					
					if(array_key_exists('keyword_links',$entry) && $entry['keyword_links'] != '') 
					{
						echo "<p>".$cms_labels['keywords']." : ".$entry['keyword_links']."</p>\r";
					}
				
					if(array_key_exists('uri',$entry)) 
					{
						$pl_title = sprintf($cms_labels['permalink_title'],$entry['title']);
						
				
						if(array_key_exists('excerpt',$entry) && $entry['excerpt'] != '') 
						{
							$pl_label = $cms_labels['read_more'];
						} 
						else
						{
							$pl_label = $cms_labels['permalink'];
						}
				
						$permalink = anchor($entry['uri'], $pl_label, array('title' => $pl_title));
						echo "<p>{$permalink}</p>\r";
					}
					
					
				}

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
