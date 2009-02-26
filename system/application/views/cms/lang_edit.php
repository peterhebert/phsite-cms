<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Content editor | phSite</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/css/base.css" rel="stylesheet" type="text/css" />
<link href="/css/simple.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="masthead">
	<div class="page-wrap block">
		<div class="column">
			<h2><a href="/">phSite</a></h2>
		</div>
		<div class="column lh">a content manager for the rest of us</div>
		<div class="float-right lh">
			<form action="<?php echo site_url("cms/content/search") ?>" method="post" id="site-search">
				<input name="search-text" id="search-text" type="text" />
				<input name="search-submit" type="submit" id="search-submit" value="Search" />
			</form>
		</div>
	</div>
</div>
<div id="page">
	<div class="page-wrap block" id="breadcrumb"><?php echo anchor("cms","phSite CMS"); ?> : Language list</div>
	<div class="page-wrap block">
		<h1>Languages</h1>
		<?php
			
			// search for matches
			$action = site_url('cms/language/edit');
			$attributes = array('name' => 'search_langs');
			echo form_open($action, $attributes);

			echo "<p>";
			echo form_label('Type to find languages', 'search');
			$params = array(
			'name'        => 'search_for',
			'id'          => 'search_for',
			);
			echo form_input($params);
			echo "</p>\n";

			echo "<p>";
			echo form_submit('search_submit', 'Search');
			echo "</p>\n";
			echo form_close();

			
			// enable or disable - list of enabled languages
			$attributes = array('name' => 'langform');
			foreach($enabled as $lang) {
				$active[] = $lang['ISO_639_3'];
			}
			$hidden_list = implode(',',$active);
			$hidden = array(
				'enabled_langs' => $hidden_list,
			);
			
			echo form_open($action, $attributes,$hidden);

			echo "<p>";
			echo form_label('Activated Languages');
			echo "<span class=\"radiogroup w4\">";

			foreach($enabled as $lang) {
				$params = array(
				'name'        => "lang[]",
				'id'        => "lang[]",
				'value'       => $lang['ISO_639_3'],
				'checked'     => TRUE,
				);
				echo "<label>";
				echo form_checkbox($params);
				echo " {$lang['Ref_Name']}</label> ";
			}
			echo "</span></p>\n";

			echo "<p>";
			echo form_submit('submit', 'Save');
			echo "</p>\n";
			echo form_close();
		?>

	</div>
	<div class="page-wrap block" id="footer"> phSite &copy; 2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
