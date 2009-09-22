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
<div id="page">
	<div class="page-wrap block" id="breadcrumb">
		<?php
		if (isset($breadcrumb)){
			echo $breadcrumb;
		}
	 ?>
	</div>
	<div class="page-wrap">
		<div class="block five">
			<div class="column w3">
			<?php
			if(isset($title))
			{
				echo "<h1>{$title}</h1>\r";
			}
	
			if(isset($content_date_created))
			{
				echo '<p>'.$content_date_created."</p>\r";
			}
			if(isset($content_body))
			{
				echo $content_body;
			}
			if(isset($content_keyword_links))
			{
				echo "<p>Keywords: {$content_keyword_links}</p>\r";
			}
			?>
			</div>
			<div class="float w2">
				<?php
				if(isset($menu) && $menu != '')
				{
					echo $menu;
				}
				?>
			</div>
		</div>
	</div>
	<div class="page-wrap block" id="footer">
		<?php
	if (isset($site_meta_copyright) ) {
		echo "<p>".$site_meta_copyright."</p>\r";
	}
	echo POWERED_BY;
	?>
	</div>
</div>
</body>
</html>
