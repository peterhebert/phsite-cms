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
		<div id="content" class="column">
			<?php
			if (isset($content['title'])) {
				echo "<h1>{$content['title']}</h1>\r";
			}
			if (isset($subtitle) && $subtitle != '') {
				echo "<h2>{$subtitle}</h2>\r";
			}
			if (isset($content['description'])) {
				echo "<p>".$content['description']."</p>\r";
			}
			if (isset($children))
			{

				foreach($children as $child)
				{
					// generate permalink
					if(isset($child['uri']) && $child['uri'] != '') 
					{
						$pl_title = sprintf($cms_labels['permalink_title'],$child['title']);
					}

					// entry title
					if(isset($child['title']) && $child['title'] != '') 
					{
						echo "<h3>";
						if(isset($pl_title))
						{
							echo anchor($child['uri'],$child['title']);
						}
						else
						{
							echo $child['title'];
						}
						echo "</h3>\r";
					}
					// entry subtitle
					if(isset($child['subtitle']) && $child['subtitle'] != '') 
					{
						echo "<h4>{$child['subtitle']}</h4>\r";
					}
					// byline
					if(isset($child['author'])) 
					{
						$user_name_formatted = $child['author']['name_first'].' '.$child['author']['name_last'];
						$byline = sprintf($cms_labels['posted_by'],$child['date_created'],$user_name_formatted);
						echo "<p>{$byline}</p>\r";
					}
					// excerpt
					if(isset($child['excerpt']) && $child['excerpt'] != '') 
					{
						echo "<p>{$child['excerpt']}</p>\r";
						// generate permalink
						if(isset($pl_title)) 
						{
							$permalink = anchor($child['uri'],$cms_labels['read_more'],array('title' => $child['title']));
							echo "<p>{$permalink}</p>\r";
						}
					}
					else
					{
						// if no excerpt, try to print body
						if(isset($child['body']) && $child['body'] != '') 
						{
							echo $child['body'];
						}
					}
					// keywords
					if(isset($child['keyword_links'])) 
					{
						echo "<p>{$cms_labels['keywords']} : {$child['keyword_links']}</p>\r";
					}
				}
			}

			if (isset($total_children)) {

				$pconfig = array(
				'base_url' => site_url($resource_uri),
				'total_rows' => $total_children,
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
<?php require_once('inc_footer.php'); ?>
</body>
</html>
