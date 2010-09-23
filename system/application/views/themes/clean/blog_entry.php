<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($html_header))
{
echo $html_header;
}
?>
</head>
<body>
<?php require_once('masthead.php'); ?>
<!-- start page wrapper -->
<div class="wrap pad-h">
	<div id="container" class="wide margins">
		<div id="content" class="column"><?php
		
		// determine previous and next links
		$count = count($menu);
		
		if($count > 0)
		{
			foreach($menu as $index => $entry)
			{
				if(trim($entry['uri'],'/') == $resource_uri)
				{
					$myindex = $index;
				}
			}
			
			$navlinks = array();

			if(isset($myindex))
			{

				$prev_index = $myindex - 1;
				if(array_key_exists($prev_index,$menu))
				{
					$navlinks[] = anchor($menu[$prev_index]['uri'],$cms_labels['result_prev'],array('title' => $menu[$prev_index]['title']));
				}

				if(isset($parents))
				{
					$navlinks[] = anchor($parents[0]['uri'],$cms_labels['result_index'],array('title' => $parents[0]['title']));
				}

				$next_index = $myindex + 1;
				if(array_key_exists($next_index,$menu))
				{
					$navlinks[] = anchor($menu[$next_index]['uri'],$cms_labels['result_next'],array('title' => $menu[$next_index]['title']));
				}

				
			}
			$nav = implode(' | ',$navlinks);
			echo "<p class=\"align-center\">{$nav}</p>\r";
			

		}

		if(isset($content['title'])) 
		{
			echo "<h1>{$content['title']}</h1>\r";
		}
		if(isset($content['subtitle']) && $content['subtitle'] != '') 
		{
			echo "<h2>{$content['subtitle']}</h2>\r";
		}
		if(isset($content['author'])) 
		{
			$user_name_formatted = $content['author']['name_first'].' '.$content['author']['name_last'];
			$byline = sprintf($cms_labels['posted_by'],$content['date_created'],$user_name_formatted);
			echo "<p>{$byline}</p>\r";
		}
		if(isset($content['body'])) 
		{
			echo $content['body'];
		}
		
		// keywords
		
		if(isset($content['keyword_links'])) 
		{
			echo "<p>{$cms_labels['keywords']} : {$content['keyword_links']}</p>\r";
		}
		?>
		</div>
		<div id="menu" class="float">
		<?php
		if(isset($recent) && count($recent) > 0)
		{			
			echo "<h4>{$cms_labels['recent_content']}</h4>\r";
			echo build_menu($recent);
		}
		if(isset($menu) && count($menu) > 0)
		{
			$skip = NULL;
			if($content['parent_id'] > 0)
			{
				$skip = $content['content_id'];
			}
			$menutxt = trim(build_menu($menu,2,$skip));
			
			if($menutxt != '')
			{
				echo "<h4>{$menu_title}</h4>\r";
				echo $menutxt;
			}
		}
		?>
		</div>
	</div>
</div>
<!-- end page wrapper -->
<!-- start footer -->
<div class="wrap" id="footer"><?php
if (isset($site['copyright']) ) {
	echo "<p>".$site['copyright']."</p>\r";
}
echo $cms_labels['powered_by'];
?></div>
</body>
</html>
