<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2><?php echo anchor("cms",$cms_labels['phsite_cms']) ?></h2>
		</div>
		<div class="column lh"><?php echo $cms_labels['slogan']; ?></div>
		<div class="float-right lh">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>

	<div class="page-wrap block">
		<div class="column">
		<?php
		if(isset($site_meta_title)) {
			echo "<p>{$cms_labels['site']} : ";
			echo anchor('', $site_meta_title, array('title' => $cms_labels['site_external_link'], 'rel' => 'external'));;
			echo "</p>\r";
		}
		?>
		</div>
		<?php
		if(isset($userdata)) {
			echo "<div class=\"float-right\">\r";
			echo "<p>{$cms_labels['logged_in_as']} : ";
			echo anchor("cms/user/edit/id/".$userdata['user_id'],$userdata['username']);
			echo " | ";
			echo anchor("cms/logout",$cms_labels['logout']);
			echo "</div>\r";
		}
		?>
	</div>
	
</div>
