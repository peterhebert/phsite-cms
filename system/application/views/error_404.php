<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo BASE_PATH; ?>/assets/css/reset.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/fonts.css" rel="stylesheet" type="text/css" media="all" />
<link href="<?php echo BASE_PATH; ?>/assets/css/grid.css" rel="stylesheet" type="text/css" media="screen" />
<link href="<?php echo BASE_PATH; ?>/assets/css/admin.css" rel="stylesheet" type="text/css" media="screen" />
<title><?php echo $header; ?></title>
</head>

<body>
<div class="wrap pad-h">
	<div id="container" class="margins">
		<div id="content">
			<h1><?php echo $header; ?></h1>
			<?php 
			if (isset($message)){
				echo "<div class=\"msg\">{$message}</div>\n";
			}?>
		</div>
	</div>
</div>
</body>
</html>
