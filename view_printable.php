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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/topic.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT t.subject, t.num_replies, f.id AS forum_id, f.forum_name, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE t.id='.$id) or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_topic = $db->fetch_assoc($result);
$page_title = convert_htmlspecialchars($configuration['o_board_name'].' / '.$cur_topic['subject']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="<?php echo $lang_common['lang_direction']?>">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang_common['lang_encoding']?>" />
	<link rel="stylesheet" href="include/css/printable.css" type="text/css" />
	<title><?php echo $page_title ?></title>
</head>
<body>
	<table width="100%" class="links">
	<tr>
		<td>
		<b>&raquo; <?php echo $configuration['o_board_name'] ?></b><br />&nbsp;&nbsp;&nbsp;<?php echo $configuration['o_base_url']?>/index.php<br />
		<b>&raquo; <?php echo $cur_topic['forum_name'] ?></b><br />&nbsp;&nbsp;&nbsp;<?php echo $configuration['o_base_url']?>/view_forum.php?id=<?php echo $cur_topic['forum_id'] ?><br />
		<b>&raquo; <?php echo $cur_topic['subject'] ?></b><br />&nbsp;&nbsp;&nbsp;<?php echo $configuration['o_base_url']?>/view_topic.php?id=<?php echo $id ?>
		<br /><br />
		</td>
	</tr>
	</table>
	<div style="text-align:center;">
		<table style="text-align:left;" width="100%" cellspacing="0" cellpadding="3">
		<tbody>
<?php
require FORUM_ROOT.'include/parser.php';
$result = $db->query('SELECT p.poster AS username, p.message, p.posted FROM '.$db->prefix.'posts AS p WHERE p.topic_id='.$id.' ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
while ($cur_post = $db->fetch_assoc($result))
{
	$username = convert_htmlspecialchars($cur_post['username']);
	$cur_post['message'] = parse_message($cur_post['message'], true);
?>
		<tr><td style="border-bottom: 0px"><b><?php echo $username ?>&nbsp;-&nbsp;<?php echo format_time($cur_post['posted']) ?></b></td></tr>
		<tr><td style="border-bottom: 1px solid #333333"><?php echo $cur_post['message']."\n" ?></td></tr>
<?php
}
?>
		</tbody>
		</table>
	</div>
	<table width="100%" class="links">
	<tr>
		<td><br /><b>&raquo; <a href="javascript:window.print();"><?php echo $lang_topic['Print this topic'] ?></a></b></td>
	</tr>
	</table>
</body>
</html>