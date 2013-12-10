<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title><?php echo $feed['title']; ?></title>
		<link><?php echo $feed['uri']; ?></link>
		<?php
		// copyright
		if(isset($feed['description']) && $feed['description'] != '')
		{
			echo "<description><![CDATA[".$feed['description']."]]></description>\r";
		}
		// language
		if(isset($feed['language']) && $feed['language'] != '')
		{
			echo "\t\t<language>".$feed['language']."</language>\r";
		}
		// copyright
		if(isset($feed['copyright']) && $feed['copyright'] != '')
		{
			echo "\t\t<copyright><![CDATA[".$feed['copyright']."]]></copyright>\r";
		}
		// publish date
		if(isset($feed['date_created_rss']) && $feed['date_created_rss'] != '')
		{
			echo "\t\t<pubDate>".$feed['date_created_rss']."</pubDate>\r";
		}
		// last updated
		if(isset($feed['date_updated']) && $feed['date_updated'] != '')
		{
			echo "\t\t<lastBuildDate>".$feed['date_updated']."</lastBuildDate>\r";
		}
		// editor
		if(isset($feed['editor']) && is_array($feed['editor']))
		{
			echo "\t\t<managingEditor>{$feed['editor']['email']} ({$feed['editor']['name_first']} {$feed['editor']['name_last']})</managingEditor>\r";
		}
		// webmaster
		if(isset($feed['webmaster']) && is_array($feed['webmaster']))
		{
			echo "\t\t<webMaster>{$feed['webmaster']['email']} ({$feed['webmaster']['name_first']} {$feed['webmaster']['name_last']})</webMaster>\r";
		}
		// generator
		if(isset($cms_labels['phsite_cms']))
		{
			echo "\t\t<generator>".$cms_labels['phsite_cms']."</generator>\r";
		}
		?>
		<docs>http://cyber.law.harvard.edu/rss/rss.html</docs>
		
		<?php
		if(isset($items))
		{
			foreach ($items as $item)
			{
				echo "\t\t<item>";
				echo "\t\t\t<title>".$item['title']."</title>\r";

				// description
				if(isset($item['description']) && $item['description'] != '')
				{
					echo "\t\t\t<description><![CDATA[".$item['link']."]]></description>\r";
				}
				// item URI
				if(isset($item['uri']) && $item['uri'] != '')
				{
					echo "\t\t\t<guid>".$item['uri']."</guid>\r";
				}
				// author
				if(isset($item['author']) && is_array($item['author']))
				{
					echo "\t\t<author>{$item['author']['email']} ({$item['author']['name_first']} {$item['author']['name_last']})</author>\r";
				}

				// publish date
				if(isset($item['date_created']) && $item['date_created'] != '')
				{
					echo "\t\t\t<pubDate>".$item['date_created']."</pubDate>\r";
				}
				if(isset($item['enclosure']) && is_array($item['enclosure']))
				{
					echo "\t\t\t<enclosure url=\"{$item['enclosure']['uri']}\" length=\"{$item['enclosure']['bytes']}\" type=\"{$item['enclosure']['mime_type']}\" />\r";
				}

				echo"</item>\r";
			}
		}
		?>
	</channel>
</rss>