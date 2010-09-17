<!-- begin masthead-->
<div id="masthead" class="float block">
	<div class="wrap padded">
		<div class="column" id="site-id">
			<h5><?php echo anchor('',$site['title']); ?></h5>
			<?php
			if ($site['subtitle'] != '') {
			echo "<p>{$site['subtitle']}</p>\r";
			} ?>
		</div>
		<div class="column" id="site-admin">
			<form action="<?php echo site_url('search'); ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="<?php echo $cms_labels['search']; ?>" />
			</form>
			<?php
			if(isset($userdata)) {
				echo "<p>".$cms_labels['logged_in_as']." : ".anchor("cms/user/edit/id/".$userdata['user_id'],$userdata['username'])." | ".anchor("cms/logout",$cms_labels['logout'])."</p>\r";
			}
			?>
		</div>
	</div>
</div>
<!-- end masthead-->
<!-- begin breadcrumb -->
<?php
if(isset($breadcrumb_links))
{
	$breadcrumb = implode(' : ', $breadcrumb_links);
	echo "\t<div class=\"wrap\" id=\"breadcrumb\">{$breadcrumb}</div>\r";
}
if(isset($other_langs) && count($other_langs) > 0)
{
	foreach ($other_langs as $lang)
	{
		if($lang['lang_Native_Name'] != '')
		{
			$langlinks[] = anchor($lang['uri'], $lang['lang_Native_Name']);
		}
		else
		{
			$langlinks[] = anchor($lang['uri'], $lang['lang_Ref_Name']);
		}
	}
	$languages = implode(' | ', $langlinks);
	echo "\t<div class=\"wrap\" id=\"languages\">{$cms_labels['languages_other']} : {$languages}</div>\r";
}
?>
<!-- end breadcrumb-->
