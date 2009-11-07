<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Test</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/assets/css/base.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/simple.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/confirm.css" rel="stylesheet" type="text/css" />
</head>
<body>
<!--<div id="modal-screen" class="modal"></div>
<div id="dialog-confirm-delete" class="dialog">
	<div class="dialog-box">
		<button class="dialog-cancel-top">X</button>
		<h6>Delete Confirmation</h6>
		<div class="dialog-body">
			<p>These items will be permanently deleted and cannot be recovered. Are you sure you want to delete them?</p>
			<p class="align-right">
				<button id="dialog-cancel">Cancel</button>
				<button id="dialog-confirm">Delete selected items</button>
			</p>
		</div>
	</div>
</div>
--><div id="page">
	<div class="page-wrap block" id="breadcrumb"> <a href="http://phsite.local/cms">phSite CMS</a> : Media </div>
	<div class="page-wrap block">
		<h1>Test</h1>
<?php
if (isset($msg))
{
echo $msg;
}
?>
<div id="confirm-dialog"><h2>Confirm Override</h2>
	<p>A modal dialog override of the JavaScript confirm function. Demonstrates the use of <code>onShow</code> as well as how to display a modal dialog confirmation instead of the default JavaScript confirm dialog.</p>
	<form id="myform" method="post" action="/test">
		<input type="hidden" name="select[]" value="1" />
		<input type="hidden" name="select[]" value="9" />
		<input type="button" name="confirm" class="confirm" value="Demo" />
	</form>
</div>


<div id="confirm">
	<div class="header"><span>Delete  Media Confirmation</span></div>
	<p class="message"></p>
	<div class="buttons">
		<div class="no simplemodal-close">Cancel</div><div class="yes">Delete selected items</div>
	</div>
</div>




	</div>
	<div class="page-wrap block" id="footer"><a href="http://phsite.peterhebert.com" rel="external" title="Visit the phsite website in a new window">phSite CMS</a> &copy; 2009 <a href="http://www.peterhebert.com/">Peter Hebert</a>. Licensed under the <a href="http://www.opensource.org/licenses/mit-license.php" title="Read the text of the MIT License"><abbr title="Massachusetts institute of technology">MIT</abbr> License</a>.</div>
</div>
<script type="text/javascript" src="/assets/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/assets/js/admin_ui/jquery.simplemodal-1.3.3.min.js"></script>
<script type="text/javascript" src="/assets/js/admin_ui/confirm.js"></script>
</body>
</html>
