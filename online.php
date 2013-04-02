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

define ('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/userlist.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/online.php';
if ($forum_user['is_guest'] && $forum_user['g_read_board'] == '0') message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_online['Online List'];
require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"></p>
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a> </li><li>&raquo; Online list</li></ul>
		<div class="clearer"></div>
	</div>
</div>
<div class="blocktable">
	<h2><span><?php echo $lang_online['Online List']?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
			<tr>
				<th class="tcr" scope="col" style="font-weight:bold;width:100px;" align="left"><?php echo $lang_common['Username'] ?></th>
				<th class="tcr" scope="col" style="font-weight:bold;" align="left">Display Name</th>
				<th class="tcr" scope="col" style="font-weight:bold;" align="left"><?php echo $lang_online['Last action'] ?></th>
				<th class="tcr" scope="col" style="font-weight:bold;" align="left"><?php echo $lang_online['Time'] ?></th>
				<? if($forum_user['g_id'] <= USER_MOD):?>	<th class="tcr" style="font-weight:bold;" "align=left" scope="col"><?php echo $lang_online['IP'] ?></th><?php echo"\n";endif?>
			</tr>
			</thead>
			<tbody>
	<?php
	$result = $db->query('SELECT o.*, u.username, u.displayname FROM '.$db->prefix.'online as o INNER JOIN '.$db->prefix.'users as u on u.username=o.ident WHERE o.user_id > 0 AND o.idle=0 ORDER BY o.idle') or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
	$num_users_page = $db->num_rows($result);
	if ($num_users_page)
	{
		while ($num_users_page--)
		{
			$user_data = $db->fetch_assoc($result);
			if ($user_data['current_page'])
			{
				echo"\t\t\t\t".'<tr>'."\n";
				if ($user_data['user_id'] > 1) echo "\t\t\t\t\t".'<td class="online"><a href="'.FORUM_ROOT.'profile.php?id='.$user_data['user_id'].'"><span style="color:'.$user_data['color'].'">'.$user_data['ident'].'</span></a></td>'."\n";
				else echo "\t\t\t\t\t".'<td class="online">'.$lang_online['Guest'].'</td>'."\n";
				$pathinfo = pathinfo($user_data['current_page']);
				$current_page = $pathinfo['basename'];
				echo '<td class="tcr">'.$user_data['displayname'].'</td>';
				if ($user_data['current_page_id'] > 0)
				{
					if ($current_page == "view_topic.php" || $current_page == "post.php") { $current_page_name = $db->query('SELECT subject FROM '.$db->prefix.'topics WHERE id=\''.$user_data['current_page_id'].'\'') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error()); }
					if ($current_page == "view_forum.php") { $current_page_name = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id=\''.$user_data['current_page_id'].'\'') or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error()); }
					if ($current_page == "profile.php") { $current_page_name = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id=\''.$user_data['current_page_id'].'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error()); }
					if ($current_page == "post.php" || $current_page == "edit.php") { echo"\t\t\t\t\t".'<td class="online">'.$lang_online[$user_data['current_page']].': <b><a href="'.FORUM_ROOT.'view_topic.php?id='.$user_data['current_page_id'].'">'.$db->result($current_page_name, 0).'</a></b></td>'."\n";}
					else { echo"\t\t\t\t\t".'<td class="online">'.$lang_online[$user_data['current_page']].': <b><a href="'.$user_data['current_page'].'?id='.$user_data['current_page_id'].'">'.$db->result($current_page_name, 0).'</a></b></td>'."\n";}
				}
				else if ((@$lang_online[$user_data['current_page']]) == '') echo"\t\t\t\t\t".'<td class="online">'.$lang_online['Hiding Somewhere'].'</td>'."\n";
				else echo"\t\t\t\t\t".'<td class="online"><a href="'.$user_data['current_page'].'">'.$lang_online[$user_data['current_page']].'</a></td>'."\n";	
				echo"\t\t\t\t\t".'<td class="online">'.format_time($user_data['logged']).'</td>'."\n";
				if ($forum_user['g_id'] <= USER_MOD) echo"\t\t\t\t\t".'<td class="online"><a href="'.FORUM_ROOT.'admin_users.php?show_users='.$user_data['current_ip'].'">'.$user_data['current_ip'].'</a></td>'."\n";
				echo"\t\t\t\t".'</tr>'."\n";
			}
		}
	}
	else
	{
		echo "\t\t\t\t".'<tr><td bgcolor="#ffffff" colspan="4">'.$lang_online['No users'].'</td>'."\n\t\t\t\t".'</tr>'."\n";
	}
	?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>