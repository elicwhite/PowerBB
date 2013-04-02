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

if(isset($_GET["referrer"]))
{
	setcookie("pw_bbreferrer", $_GET["referrer"], time()+3600);
}
define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/index.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name']);
define('ALLOW_INDEX', 1);
require FORUM_ROOT.'header.php';
$sfdb = array(array());
$subforum = $db->query('SELECT MAX(id) FROM '.$db->prefix.'forums') or error('Unable to fetch sub forum info',__FILE__,__LINE__,$db->error());
$_count = $db->result($subforum)+1;
for ($i = 0; $i < $_count; $i++)
{
	$forums_info = $db->query('SELECT num_topics, num_posts, parent_forum_id, last_post_id, last_poster, last_post, forum_name FROM '.$db->prefix.'forums WHERE id='.$i) or error('Unable to fetch sub forum info',__FILE__,__LINE__,$db->error());
	$current = $db->fetch_assoc($forums_info);
	$sfdb[$i][0] = $current['parent_forum_id'];
	$sfdb[$i][1] = $current['num_topics'];
	$sfdb[$i][2] = $current['num_posts'];
	$sfdb[$i][3] = $current['last_post_id'];
	$sfdb[$i][4] = $current['last_poster'];
	$display_name = $db->query('SELECT displayname FROM '.$db->prefix.'users WHERE username='.$current['last_poster']);
	$sfdb[$i][5] = $current['last_post'];
	$subject = $db->query('SELECT subject FROM '.$db->prefix.'topics WHERE last_post_id='.$current['last_post_id']);
	$sfdb[$i][6] = $db->result($subject);
	$sfdb[$i][7] = $current['forum_name'];
}
$new_topics = get_all_new_topics();
$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.forum_desc, f.redirect_url, f.moderators, f.num_topics, f.num_posts, f.last_post, f.last_post_id, f.last_poster, t.question, f.parent_forum_id, u.displayname, u.username, u.id FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id INNER JOIN '.$db->prefix.'users as u ON (u.username=f.last_poster) LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') LEFT JOIN '.$db->prefix.'topics AS t ON f.last_post=t.last_post AND f.last_post_id=t.last_post_id WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND (f.parent_forum_id IS NULL OR f.parent_forum_id=0) ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
$cur_category = 0;
$cat_count = 0;
$cat_ids = (isset($_COOKIE['collapseprefs']))? $_COOKIE['collapseprefs'].',': FALSE;
while ($cur_forum = $db->fetch_assoc($result))
{
	$moderators = '';
	if ($cur_forum['cid'] != $cur_category)
	{
		if ($cur_category != 0) echo "\t\t\t".'</tbody>'."\n\t\t\t".'</table>'."\n\t\t".'</div>'."\n\t".'</div>'."\n".'</div>'."\n\n";
		++$cat_count;
		if ($configuration['o_coll_cat'] == '1')
		{
			if (strstr($cat_ids, $cat_count.','))
			{
				$div_ido = "Block";
				$div_idx = "none";
			}
			else
			{
				$div_ido = "none";
				$div_idx = "block";
			}
			$exp_up = (is_file(FORUM_ROOT.'img/general/exp_up.png'))? 'general/exp_up.png': 'exp_up.png';
			$exp_down = (is_file(FORUM_ROOT.'img/general/exp_down.png'))? 'general/exp_down.png': 'exp_down.png';
	?>
	<div id="ido<?php echo $cat_count ?>" class="blocktable" style="display:<?php echo $div_ido?>">
		<h2>
			<span style="float:right"><a href="javascript:togglecategory(<?php echo $cat_count?>, 0);"><img src="img/<?php echo $exp_down ?>" alt="Expand" /></a></span>
			<span><?php echo convert_htmlspecialchars($cur_forum['cat_name']) ?></span>
		</h2>
	</div>
	<div id="idx<?php echo $cat_count ?>" class="blocktable" style="display:<?php echo $div_idx?>">
		<h2>
			<span style="float:right">
<?php
if ($configuration['o_rss_type'] != "0")
{
	$rss_button = (is_file(FORUM_ROOT.'img/'.$forum_user['style'].'/button-feed.png'))?  'img/'.$forum_user['style'].'/button-feed.png': 'img/general/button-feed.png';
?>
	<a href="rss.php?cid=<?php echo $cur_forum['cid'] ?>"><img src="<?php echo $rss_button; ?>" alt="RSS feeds" /></a>&nbsp;
<?php
}
?>

<a href="javascript:togglecategory(<?php echo $cat_count?>, 1);"><img src="img/<?php echo $exp_up?>" alt="Collapse" /></a></span>
			<span><?php echo convert_htmlspecialchars($cur_forum['cat_name']) ?></span>
		</h2>
	<?php
}
else
{
	?>
	<div id="idx<?php echo $cat_count ?>" class="blocktable">
		<h2><span><?php echo convert_htmlspecialchars($cur_forum['cat_name']) ?></span></h2>
	<?php
}
	?>
	<div class="box" id="forum">
		<div class="inbox">
			<table cellspacing="0">
				<thead>
					<tr>
						<th class="tcl" scope="col"><?php echo $lang_common['Forum'] ?></th>
						<th class="tc2" scope="col"><?php echo $lang_index['Topics'] ?></th>
						<th class="tc3" scope="col"><?php echo $lang_common['Posts'] ?></th>
						<th class="tcr" scope="col"><?php echo $lang_common['Last post'] ?></th>
					</tr>
				</thead>
				<tbody>
<?php
		$cur_category = $cur_forum['cid'];
	}
	$item_status = '';
	$icon_text = $lang_common['Normal icon'];
	$icon_type = 'icon';
	if (!$forum_user['is_guest'] && forum_is_new($cur_forum['fid'], $cur_forum['last_post']))
	{
		$item_status = 'inew';
		$icon_text = $lang_common['New icon'];
		$icon_type = 'icon inew';
	}
	if ($cur_forum['redirect_url'] != '')
	{
		$forum_field = '<h3><a href="'.convert_htmlspecialchars($cur_forum['redirect_url']).'" title="'.$lang_index['Link to'].' '.convert_htmlspecialchars($cur_forum['redirect_url']).'">'.convert_htmlspecialchars($cur_forum['forum_name']).'</a></h3>';
		$num_topics = $num_posts = '&nbsp;';
		$item_status = 'iredirect';
		$icon_text = $lang_common['Redirect icon'];
		$icon_type = 'icon';
	}
	else
	{
		if ($configuration['o_rewrite_urls'] == '1') $forum_field = '<h3><a href="'.makeurl("f", $cur_forum['fid'], $cur_forum['forum_name']).'">'.convert_htmlspecialchars($cur_forum['forum_name']).'</a></h3>';
		else $forum_field = '<h3><a href="view_forum.php?id='.$cur_forum['fid'].'">'.convert_htmlspecialchars($cur_forum['forum_name']).'</a></h3>';
        $n_t = 0;
        $n_p = 0;
        $l_pid = $cur_forum['last_post_id'];
		if ($cur_forum['displayname'] != "")
		{
			  $l_pr = $cur_forum['displayname'];
		}
		else
		{
			 $l_pr = $cur_forum['username'];
		}
        $l_post = $cur_forum['last_post'];
        for ($i = 0; $i < $_count; ++$i)
        {
         if (($cur_forum['forum_name'] == $sfdb[$i][7]))
         {
          $_subject_ = $sfdb[$i][6];
         }
        }
        for ($i = 0; $i < $_count; $i++)
        {
         if ($sfdb[$i][0] == $cur_forum['fid'])
          {
           $n_t = $n_t + $sfdb[$i][1];
           $n_p = $n_p + $sfdb[$i][2];
           if ($l_pid < $sfdb[$i][3])
            {
             $l_pid = $sfdb[$i][3];
             $l_pr = $sfdb[$i][4];
             $l_post = $sfdb[$i][5];
             $_subject_ = $sfdb[$i][6];
            }
          }
        }
        $num_topics = $n_t + $cur_forum['num_topics'];
        $num_posts = $n_p + $cur_forum['num_posts'];
	}
	if ($cur_forum['forum_desc'] != '') $forum_field .= "\n\t\t\t\t\t\t\t\t".$cur_forum['forum_desc'];
	$queryid = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$cur_forum['last_post_id']);
	$idm = $db->result($queryid);
	$queryid = $db->query('SELECT subject FROM '.$db->prefix.'topics WHERE id='.$idm);
	$idm = $db->result($queryid);
	if(strlen($idm) > 30)
	{
		$idmComp = str_replace('"', "''", $idm);
		$idm = substr($idm, 0, 30).'...';
	}
	else $idmComp = '';
	if ($cur_forum['last_post'] != '')
	{
		$idmT = (isset($idmComp)) ? ' title="'.$idmComp.'"' : '';
		if ($configuration['o_rewrite_urls'] == '1')
		{
			if ($cur_forum['question'] != '')'<a href="'.makeurl("p", $cur_forum['last_post_id'], $cur_forum['subject']).'#p'.$cur_forum['last_post_id'].'">'.$_subject_.'</a>&nbsp; ' .$lang_common['by'].' '.convert_htmlspecialchars($l_pr);
			else $last_post = '<a href="'.makeurl("p", $cur_forum['last_post_id'], $cur_forum['subject']).'#p'.$cur_forum['last_post_id'].'">'.$cur_forum['subject'].'</a>&nbsp;'.$lang_common['by'].' '.convert_htmlspecialchars($l_pr);
		}
		else
		{
			if ($cur_forum['question'] != '') $last_post = '<a href="view_poll.php?pid='.$l_pid.'#p'.$l_pid.'">'.$_subject_.'</a> '.$lang_common['by'].' '.convert_htmlspecialchars($l_pr);
			else $last_post = '<a href="view_topic.php?pid='.$l_pid.'#p'.$l_pid.'">'.$_subject_.'</a> '.$lang_common['by'].' '.convert_htmlspecialchars($l_pr);
		}
	}
	else $last_post = '&nbsp;';
	$moderators = array();
	if ($cur_forum['moderators'] != '')
	{
		$mods_array = unserialize($cur_forum['moderators']);
		while (list($mod_username, $mod_id) = @each($mods_array)) $moderators[] = '[<a href="profile.php?id='.$mod_id.'">'.convert_htmlspecialchars($mod_username).'</a>]';
		$moderators = "\t\t\t\t\t\t\t\t".'<p><em>'.$lang_common['Moderated by'].'</em>: '.implode(', ', $moderators).'</p>'."\n";
	}
	$results = $db->query('SELECT forum_name, id FROM '.$db->prefix.'forums WHERE parent_forum_id ='.$cur_forum['fid']) or error('Unable to fetch subforums count', __FILE__, __LINE__, $db->error());
	$subs_name = NULL;$subs_id = NULL;
	while($results2 = mysql_fetch_array($results))
	{
		$subs_name[] = $results2['forum_name'];
		$subs_id[] = $results2['id'];
	}
	if (is_array($subs_name))
	{
		$subforums = array();
		for ($i=0; $i < count($subs_name);$i++)
		{
			$subforums[] = '[<a href="view_forum.php?id='.$subs_id[$i].'">'.convert_htmlspecialchars($subs_name[$i]).'</a>]';
		}
		$subforumsp = "\t\t\t\t\t\t\t\t".'<p><em>'.$lang_common['Subforums'].'</em>: '.implode(', ', $subforums).'</p>'."\n";
	}
	else $subforumsp = "";
	if ($configuration['o_click_row'] == '1')
	{
?>
 		<tr<?php if ($item_status != '') echo ' class="'.$item_status.'"'; ?> onclick="window.location.href='view_forum.php?id=<?php echo $cur_forum['fid']; ?>'">
<?php
	}
	else
	{
?>
		<tr<?php if ($item_status != '') echo ' class="'.$item_status.'"'; ?>> 
<?php
	}
?>
					<td class="tcl">
						<div class="intd">
							<div class="<?php echo $icon_type ?>">
								<div class="nosize">
									<?php echo $icon_text ?>
								</div>
							</div>
							<div class="tclcon">
								<?php echo $forum_field."\n";
								if (count($moderators)>0) echo $moderators; ?>
								<?php echo "\n".$subforumsp ?>
							</div>
						</div>
					</td>
					<td class="tc2"><?php echo $num_topics ?></td>
					<td class="tc3"><?php echo $num_posts ?></td>
					<td class="tcr"><?php echo $last_post ?></td>
				</tr>
<?php
}
if ($cur_category > 0) echo "\t\t\t".'</tbody>'."\n\t\t\t".'</table>'."\n\t\t".'</div>
'."\n\t".'</div>'."\n".'</div>'."\n\n";
else echo '<div id="idx0" class="block"><div class="box"><div class="inbox"><p>'.$lang_index['Empty board'].'</p></div></div></div>';
$ak_limit = $configuration['o_active_topics_nr'];
if ($ak_limit != '0') include('include/modules/mod_active_topics.php');
if ($configuration['o_boardstats_enable'] == '1')
{
	$result = $db->query('SELECT COUNT(id)-1 FROM '.$db->prefix.'users') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
	$stats['total_users'] = $db->result($result);
	$result = $db->query('SELECT id, username, displayname FROM '.$db->prefix.'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
	$stats['last_user'] = $db->fetch_assoc($result);
	$result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
	list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);
	?>
		<div id="brdstats" class="block">
			<h2><span><?php echo $lang_index['Board info'] ?></span></h2>
			<div class="box">
				<div class="inbox">
					<dl class="conr">
						<dd><?php echo $lang_index['No of users'].': <strong>'. $stats['total_users'] ?></strong></dd>
						<dd><?php echo $lang_index['No of topics'].': <strong>'.$stats['total_topics'] ?></strong></dd>
						<dd><?php echo $lang_index['No of posts'].': <strong>'.$stats['total_posts'] ?></strong></dd>
						<?php
							$result = $db->query("SELECT COUNT(*) FROM ".$db->prefix."posts WHERE posted > ".(time()-7*24*3600)) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
							$posts_week = $db->result($result);
							$result = $db->query("SELECT COUNT(*) FROM ".$db->prefix."posts WHERE posted > ".(time()-24*3600)) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
							$posts_day = $db->result($result);
							$posts_h = $posts_day/24;
						?>
						<dd>
						<?php
							if ($posts_h<1)
						   		if ($posts_day<1)
									if ($posts_week<1) echo $lang_index['dormant'];
									else echo $lang_index['number week'].': <strong>'.$posts_week . '</strong>';
						   		else echo $lang_index['number day'].': <strong>'.$posts_day . '</strong>';
							else echo $lang_index['average h'].': <strong>'.number_format($posts_h,1) . '</strong>';
				?>
				</dd>
					</dl>
					<dl class="conl">
						<dt><strong><?php echo $lang_index['User info'] ?></strong></dt>
						<dd><?php echo $lang_index['Newest user'] ?>: <a href="profile.php?id=<?php echo $stats['last_user']['id'] ?>"><?php
						if ($stats['last_user']['displayname'] !="")
							{
								echo convert_htmlspecialchars($stats['last_user']['displayname']);
							}
						else
							{
								echo convert_htmlspecialchars($stats['last_user']['username']);
							} ?></a></dd>
	<?php
	if ($configuration['o_users_online'] == '1')
	{
		$num_guests = 0;
		$users = array();
		$result = $db->query('SELECT o.user_id, o.ident, o.idle, o.color, u.username, u.displayname FROM '.$db->prefix.'online AS o INNER JOIN '.$db->prefix.'users AS u ON u.username=o.ident WHERE o.idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error()); 
		
		while ($forum_user_online = $db->fetch_assoc($result))
		{
			if ($forum_user_online['displayname'] != "")
				{
					$forum_user_online['ident'] = $forum_user_online['displayname'];
				}		
			if ($forum_user_online['color'] == '' ) $forum_user_online['color'] = '#000000';
			if ($forum_user_online['user_id'] > 1) $users[] = "\n\t\t\t\t".'<dd><a href="profile.php?id='.$forum_user_online['user_id'].'"><span style="color: '.$forum_user_online['color'].'">'.convert_htmlspecialchars($forum_user_online['ident']).'</span></a>';
			else ++$num_guests;
		}
		$num_users = count($users);

		$date = getdate(time());
		$todaystamp = mktime(0,0,0, $date['mon'], $date['mday'], $date['year']);
		$result = $db->query('SELECT username, displayname, id, last_visit from '.$db->prefix.'users WHERE last_visit >= \''.$todaystamp.'\' ORDER by last_visit DESC') or error('Cannot retrieve the list of today visitors', __FILE__, __LINE__, $db->error());
		$users_today = array();
		while ($user_online_today = $db->fetch_assoc($result))
			{
				if ($user_online_today['displayname'] !="")
					{
						$user_online_today['username'] = $user_online_today['displayname'];
					}
				$users_today[] .=  "\n\t\t\t\t".'<a href="profile.php?id='.$user_online_today['id'].'" title="Last visit of '.$user_online_today['username'].' : '.format_time($user_online_today['last_visit']).'">'.$user_online_today['username'].'</a>';
			}
		$num_users_today = count($users_today);
		echo "\t\t\t\t".'<dd>'. $lang_index['Users online'].': <strong>'.$num_users.'</strong></dd>'."\n\t\t\t\t".'<dd>'.$lang_index['Users today'].': <strong>'.$num_users_today.'</strong></dd>'."\n\t\t\t\t".'<dd>'.$lang_index['Guests online'].': <strong>'.$num_guests.'</strong></dd>'."\n\t\t\t".'</dl>'."\n";

		$botStatus = isBotOnline();
    		if ($num_users > 0 || $botStatus != '')
		{
       		echo "\t\t\t".'<dl id="onlinelist" class= "clearb">'."\n\t\t\t\t".'<dt><strong>'.$lang_index['Online'].':&nbsp;</strong></dt>'."\t\t\t\t".implode(',</dd> ', $users);
			if($botStatus != '' & $num_users == 0) echo substr($botStatus, 1);
			else echo $botStatus;	
			echo '</dd>'."\n\t\t\t".'</dl>'."\n";
    		}
		echo "\t\t\t".'<div class="clearer"></div>'."\n";

		echo "\t\t\t".'<dl id="onlinetodaylist">'."\n\t\t\t\t".'<dt><strong>'.$lang_index['Online today'].': </strong>';			
		if ($num_users_today > 0) echo implode(', ', $users_today).''."\n\t\t\t".'</dt></dl>'."\n";
		else echo '<em>none</em>'."\n\t\t\t".'</dt></dl>'."\n";

}
else echo "\t\t".'</dl>'."\n\t\t\t".'<div class="clearer"></div>'."\n";
?>
				</div>
			</div>
		</div>
<?php } ?>
<?php
if ($configuration['o_most_active'] != '0')
{
?>
<div id="top10" class="block">
    <h2><span><?php echo $lang_index['Top10MostActive'] ?></span></h2>
    <div class="box">
        <div class="inbox">
            <div>
			<?php
				$result = $db->query('SELECT id, group_id, username, displayname, num_posts FROM '.$db->prefix.'users WHERE (num_posts != 0) && (group_id != 3) ORDER BY num_posts DESC LIMIT '.$configuration['o_most_active']) or error('Unable to fetch user data', __FILE__, __LINE__, $db->error());
				while ($data = $db->fetch_assoc($result))
				{
					if ($data['displayname'] != "")
					{	
						$data['username'] = $data['displayname'];
					}
					echo "\t\t\t\t\t\t".'<a href="profile.php?id='.$data['id'].'">'.convert_htmlspecialchars($data['username']).'</a> ('.$data['num_posts'].') '."\n";
				}

			?>
		</div>
	   </div>
     </div>
</div>
<?php
}
$footer_style = 'index';
require FORUM_ROOT.'footer.php';
?>