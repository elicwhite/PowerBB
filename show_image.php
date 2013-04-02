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

define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/modules/mod_image_upload.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
if (!isset($_GET['pid']) || !isset($_GET['filename'])) error('Invalid image parameters', __FILE__, __LINE__);
$pid = $_GET['pid'];
$pid = $db->escape($pid);
$filename = $_GET['filename'];

$findme  = '../';
$pos = strpos($filename, $findme);

// Note our use of ===.  Simply == would not work as expected
// because the position of 'a' was the 0th (first) character.
if ($pos === true) {
die ("Bad Request");
}
$result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$id = $db->result($result);
if (!$forum_user['is_guest']) $result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$forum_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else $result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$source = $configuration['o_upload_path'].$pid.'/';
if (!is_dir($source)) error('No images for this post id', __FILE__, __LINE__);
if (isset($_GET['preview']) && $_GET['preview'] == true)
{
	$file = $source.'thumbnails/'.$filename;
}
else
{
	$file = $source.$filename;
}
if (!is_file($file)) error('This image does not exist', __FILE__, __LINE__);
header('Content-type: '.get_mime_type($file));
echo file_get_contents($file)
?>