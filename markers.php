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
header('Content-type: text/xml');
$result = $db->query('SELECT id, username, displayname title, realname, url, location, latitude, longitude, use_avatar FROM '.$db->prefix.'users WHERE latitude IS NOT NULL AND longitude IS NOT NULL;;') or error('Unable to select users', __FILE__, __LINE__, $db->error());
echo '<?xml version="1.0" encoding="ISO-8859-15"?>'."\n";
echo '<markers>'."\n";
while($user = $db->fetch_assoc($result))
{
	if ($user['use_avatar'] == '1')
	{
		if (@getimagesize($configuration['o_avatars_dir'].'/'.$user['id'].'.gif')) $avatar_field = $configuration['o_avatars_dir'].'/'.$user['id'].'.gif';
		else if (@getimagesize($configuration['o_avatars_dir'].'/'.$user['id'].'.jpg')) $avatar_field = $configuration['o_avatars_dir'].'/'.$user['id'].'.jpg';
		else if (@getimagesize($configuration['o_avatars_dir'].'/'.$user['id'].'.png')) $avatar_field = $configuration['o_avatars_dir'].'/'.$user['id'].'.png';
		else $avatar_field = '';
	}	
	else $avatar_field = '';
	echo '  <marker id="'.convert_htmlspecialchars($user['id']).'" username="'.convert_htmlspecialchars($user['username']).'" title="'.convert_htmlspecialchars($user['title']).'" realname="'.convert_htmlspecialchars($user['realname']).'" url="'.convert_htmlspecialchars($user['url']).'" location="'.convert_htmlspecialchars($user['location']).'" lat="'.convert_htmlspecialchars($user['latitude']).'" lng="'.convert_htmlspecialchars($user['longitude']).'" useavatar="'.convert_htmlspecialchars($user['use_avatar']).'" avatar="'.convert_htmlspecialchars($avatar_field).'"/>'."\n";
}
echo '</markers>';
?>