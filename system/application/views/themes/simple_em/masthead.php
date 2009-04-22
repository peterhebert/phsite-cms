<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2>Site name</h2>
		</div>
		<div class="column lh">Site subtitle</div>
		<div class="float-right lh">
			<form action="<?php echo site_url("search") ?>" method="post" id="site-search">
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
