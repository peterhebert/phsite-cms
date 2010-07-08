<div id="masthead" class="float block">
	<div class="wrap padded">
		<div class="column" id="site-id">
			<h6><?php echo anchor("cms",$cms_labels['phsite_cms']) ?></h6>
			<p><?php echo $cms_labels['slogan']; ?></p>
		</div>
		<div class="column" id="site-admin">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
			<?php
			if(isset($site['title'])) {
				$adminlinks[] = "{$cms_labels['site']} : " . anchor('', $site['title'], array('title' => $cms_labels['site_external_link'], 'rel' => 'external'));;
			}

			if(isset($userdata)) {
				$adminlinks[] = "{$cms_labels['logged_in_as']} : ".anchor("cms/user/edit/id/".$userdata['user_id'],$userdata['username'])." | ".anchor("cms/logout",$cms_labels['logout']);
			}
			
			if(isset($adminlinks)) {
				echo "<p>".implode(" &nbsp; ",$adminlinks)."</p>\r";
			}
			?>
		</div>
	</div>
</div>
<?php
if (isset($breadcrumb)){
	echo "<div class=\"wrap\" id=\"breadcrumb\">{$breadcrumb}</div>\n";
}
?>
