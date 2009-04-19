<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2>phSite</h2>
		</div>
		<div class="column lh">a content manager for the rest of us</div>
		<div class="float-right lh">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
	
	<?php
	if(isset($userdata)) {

/*
Array
(
    [session_id] => 35fb337e1647a6bff6bbddf57b13e329
    [ip_address] => 127.0.0.1
    [user_agent] => Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en
    [last_activity] => 1237584702
    [attempts] => 0
    [user_id] => 1
    [username] => peterhebert
    [name_first] => Peter
    [name_last] => Hebert
    [timezone] => America/Vancouver
    [admin] => 1
    [lang] => fra
    [email] => mail@peterhebert.com
)
*/



		echo "<div class=\"page-wrap block\">\n";
		echo "<p>Logged in as: ";
		echo anchor("cms/user/edit/id/".$userdata['user_id'],$userdata['username']);
		echo " | ";
		echo anchor("cms/logout",'Logout');
		echo " | ";
		echo anchor("",'View site');
		echo "</p>\n";
		echo "</div>\n";
	}
	?>
</div>
