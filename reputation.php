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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/reputation.php';
if ($forum_user['is_guest']) message($lang_common['No permission']);
if (empty($_GET['id'])) message($lang_common['Bad request']);
$id = intval($_GET['id']);
if($configuration['o_reputation_enabled'] != '1') message($lang_reputation['Disabled']);
$query = $db->query("select id from ".$db->prefix."users where id='".$id."';");
$target_user = $db->fetch_assoc($query);
if(empty($target_user["id"])) message($lang_reputation['No user']);
if($target_user["id"] == $forum_user['id']) message($lang_reputation['Silly user']);
if($configuration['o_reputation_timeout'] > (time()-$forum_user['last_reputation_voice'])) message($lang_reputation['Timeout 1'].$configuration['o_reputation_timeout'].$lang_reputation['Timeout 2']);
$plus = $minus = false;
if(isset($_GET["plus"]) && isset($_GET["minus"]))
{
	message($lang_reputation['Invalid voice value']);
}
else
{
	if(isset($_GET["plus"]))
	{
      	$plus = true;
	}
	if(isset($_GET["minus"]))
	{
      	$minus = true;
	}
}
if($plus) $db->query("UPDATE ".$db->prefix."users SET reputation_plus=reputation_plus+1 where id='".$id."';");
if($minus) $db->query("UPDATE ".$db->prefix."users SET reputation_minus=reputation_minus+1 where id='".$id."';");
$db->query("UPDATE ".$db->prefix."users SET last_reputation_voice='".mktime()."' where id='".$forum_user["id"]."';");
header("Location: ".$_SERVER["HTTP_REFERER"]);
?>