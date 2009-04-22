<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php //echo $site_name ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $theme_path; ?>base.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $theme_path; ?>simple_em.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php require_once('masthead.php'); ?>
<div id="page">
	<div class="page-wrap block">
		Home
		<div class="golden">
			<div class="column g1">
				<h1>Featured Media</h1>
				<div class="placeholder" style="height:315px;">Placeholder image</div>
				<p>Nisl diam commodo ad eu dolor commodo esse in ut nulla aliquip te eu nulla facilisi dignissim velit. Nostrud quis, facilisis veniam vel nibh, sed vero eros consequat facilisi magna eros ad luptatum vel. Duis odio volutpat. Enim enim laoreet euismod et et facilisi autem ut iusto quis lobortis eu adipiscing. Dolore ad lobortis, quis dolore consequat qui, quis facilisis autem nulla ad. </p>
				<p>Nisl consequat suscipit. Luptatum veniam velit. </p>
				<p>Duis, eu vel. Dolore quis autem, dolor eros quis in augue aliquam hendrerit lobortis et vel hendrerit. Vero eu tation, dignissim iriure consectetuer iusto ullamcorper molestie te wisi esse ex qui, ut esse ut magna. Enim praesent in diam quis praesent lobortis lorem eros nibh consequat tation commodo suscipit, ad, ut augue duis sed. Enim tation duis ex. </p>
				<p>Iusto duis consequat, ut. Vel aliquip, qui, feugiat odio odio exerci vero, illum duis dolor consectetuer eum, luptatum amet iusto ullamcorper odio iriure blandit. </p>
			</div>
			<div class="float g2">
				<h1>About this site</h1>
				<p>This site is the online home of Peter Hebert, web developer, graphic designer and independent filmmaker. Peter lives in Vancouver, British Columbia.</p>
				<p><strong>Social Networks</strong></p>
				<ul class="social-icons block">
					<li><a href="http://www.facebook.com/profile.php?id=527077494" title="View my profile on Facebook"><img src="/assets/icons/favicons/facebook.png" alt="Facebook" /></a></li>
					<li><a href="http://www.linkedin.com/in/peterhebert" title="View my profile on LinkedIn"><img src="/assets/icons/favicons/linkedin.png" alt="LinkedIn" /></a> </li>
					<li><a href="http://www.youtube.com/user/peterhebert" title="Visit my YouTube channel"><img src="/assets/icons/peter-hebert/youtube.png" alt="YouTube" /></a> </li>
					<li><a href="http://vimeo.com/user1065113" title="Visit my Vimeo channel"><img src="/assets/icons/favicons/vimeo.png" alt="Vimeo" /></a></li>
				</ul>
				<p><strong>Contact</strong></p>
				<ul class="social-icons block">
					<li><a href="mailto:mail@peterhebert.com" title="Send me an email"><img src="/assets/icons/fugue/mail.png" alt="e-mail" /></a></li>
					<li><a href="/Peter_Hebert.vcf" title="Download my vCard for your address book"><img src="/assets/icons/fugue/card.png" alt="vCard" /></a></li>
					<li><a href="xmpp:mail@peterhebert.com" title="Send me an instant message via XMPP/Jabber/Google Talk"><img src="/assets/icons/favicons/xmpp-jabber.png" alt="XMPP/Jabber/Google Talk" /></a></li>
					<li><a href="msnim:add?contact=peterhebert@hotmail.com" title="Send me an instant message via MSN/Windows Live Messenger"><img src="/assets/icons/other/messenger.png" alt="MSN/Windows Live Messenger" /></a></li>
					<li><a href="callto://peterhebert" title="Call me using Skype"><img src="/assets/icons/favicons/skype.png" alt="Skype" /></a></li>
				</ul>
				
					
				<div class="block bdr-top">
					<div class="column w1">
						<h2>Blog</h2>
						<p>Nisl diam commodo ad eu dolor commodo esse in ut nulla aliquip te eu nulla facilisi dignissim velit. Nostrud quis, facilisis veniam vel nibh, sed vero eros consequat facilisi magna eros ad luptatum vel. Duis odio volutpat. Enim enim laoreet euismod et et facilisi autem ut iusto quis lobortis eu adipiscing. Dolore ad lobortis, quis dolore consequat qui, quis facilisis autem nulla ad. </p>
					</div>
					<div class="float w1">
						<h2>Site Menu</h2>
						<?php echo ul($menu); ?>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	<div class="page-wrap block" id="footer"> &copy; 1996-2009 Peter Hebert. All rights reserved unless stated otherwise.</div>
</div>
</body>
</html>
