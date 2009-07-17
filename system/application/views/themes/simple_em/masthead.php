<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2><?php echo $site_meta_title; ?></h2>
		</div>
		<?php
		if ($site_meta_subtitle != '') {
		echo "<div class=\"column lh\">{$site_meta_subtitle}</div>\n";
		} ?>
		<div class="float-right lh">
			<form action="/search/index" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
	
	<?php
	if(isset($userdata)) {
		echo "<div class=\"page-wrap block\">\n";
		echo "<p>Logged in as: ";
		echo anchor("cms/user/edit/id/".$userdata['user_id'],$userdata['username']);
		echo " | ";
		echo anchor("cms/logout",'Logout');
		echo "</p>\n";
		echo "</div>\n";
	}
	?>
</div>
