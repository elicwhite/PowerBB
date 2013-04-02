<?php
//----------------------------------------------
// PowerBB
//----------------------------------------------
// All code is copyright to Power Software
// unless mentioned otherwise. This code
// may NOT be reproduced, or distributed
// by any means, unless you have explicit
// written permission from Power Software.
// Some code is derived from early versions
// of PunBB.
//-----------------------------------------------
// Copyright as of 2006
// All rights reserved
//-----------------------------------------------

define('QUIET_VISIT', 1);
define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
$dynamic = true;
$filename = 'sitemap.xml';
$result = $db->query('SELECT t.id as topic_id, subject, last_post, sticky FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL ORDER BY last_post DESC') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
$result2 = $db->query('SELECT f.id as forum_id, forum_name, last_post FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY f.id DESC') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
$output = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";
$output .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">' . "\n";
$output .= "<url>\n";
$output .= "\t<loc>".$configuration['o_base_url']."/"  . "</loc>\n";
$output .= "\t<lastmod>".gmdate('Y-m-d\TH:i:s+00:00', time())."</lastmod>\n";
$output .= "\t<priority>1.0</priority>\n";
$output .= "</url>\n\n";
while ($cur_forum = $db->fetch_assoc($result2))
{
	$lastmodified = gmdate('Y-m-d\TH:i:s+00:00', $cur_forum['last_post']);
	if ($configuration['o_rewrite_urls'] == '1') $viewforum = makeurl("f", $cur_forum['forum_id'], $cur_forum['forum_name']);
	else $viewforum = 'view_forum.php?id='.$cur_forum['forum_id'].'';
	$priority = '1.0';
	$output .= "<url>\n";
	$output .= "\t<loc>".$configuration['o_base_url']."/$viewforum</loc>\n";
	$output .= "\t<lastmod>$lastmodified</lastmod>\n";
	$output .= "\t<priority>$priority</priority>\n";
	$output .= "</url>\n\n";
}
while ($cur_topic = $db->fetch_assoc($result))
{
	$lastmodified = gmdate('Y-m-d\TH:i:s+00:00', $cur_topic['last_post']);
	if ($configuration['o_rewrite_urls'] == '1') $viewtopic = makeurl("t", $cur_topic['topic_id'], $cur_topic['subject']);
	else $viewtopic = 'view_topic.php?id='.$cur_topic['topic_id'].'';
	$priority = ($cur_topic['sticky'] == '1') ? '1.0' : '0.5';
	$output .= "<url>\n";
	$output .= "\t<loc>".$configuration['o_base_url']."/$viewtopic"  . "</loc>\n";
	$output .= "\t<lastmod>$lastmodified</lastmod>\n";
	$output .= "\t<priority>$priority</priority>\n";
	$output .= "</url>\n\n";
}
$output .= "</urlset>\n";
if ($dynamic)
{
	header('Content-type: application/xml');
	echo $output;
}
else
{
	$file = fopen($filename, "w");
	fwrite($file, $output);
	fclose($file);
	echo "Done";
}
?>