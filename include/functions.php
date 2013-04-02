<?php
require FORUM_ROOT.'include/tar.php';

/**
 * Check cookie for login.
 *
 * @param unknown_type $forum_user
 */

function check_cookie(&$forum_user)
{
	global $db, $configuration, $cookie_name, $cookie_seed;
	$now = time();
	$expire = $now + 31536000;
	$cookie = array('user_id' => 1, 'password_hash' => 'Guest');
	if (isset($_COOKIE[$cookie_name])) list($cookie['user_id'], $cookie['password_hash']) = @unserialize($_COOKIE[$cookie_name]);
	if ($cookie['user_id'] > 1)
	{
		$result = $db->query('SELECT u.*, g.*, o.logged, o.idle FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE u.id='.intval($cookie['user_id'])) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
		$forum_user = $db->fetch_assoc($result);
		if (!isset($forum_user['id']) || md5($cookie_seed.$forum_user['password']) !== $cookie['password_hash'])
		{
			forum_setcookie(0, random_pass(8), $expire);
			set_default_user();
			return;
		}
		if (!@file_exists(FORUM_ROOT.'lang/'.$forum_user['language'])) $forum_user['language'] = $configuration['o_default_lang'];
		if (!@file_exists(FORUM_ROOT.'style/'.$forum_user['style'].'.css')) $forum_user['style'] = $configuration['o_default_style'];
		if (!$forum_user['disp_topics']) $forum_user['disp_topics'] = $configuration['o_disp_topics_default'];
		if (!$forum_user['disp_posts']) $forum_user['disp_posts'] = $configuration['o_disp_posts_default'];
		if ($forum_user['save_pass'] == '0') $expire = 0;
        	if ($forum_user['read_topics']) $forum_user['read_topics'] = unserialize($forum_user['read_topics']);
		else $forum_user['read_topics'] = array();
		if (!defined('QUIET_VISIT'))
		{
			if (!$forum_user['logged'])
			{
				$db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged, color) VALUES('.$forum_user['id'].', \''.$db->escape($forum_user['username']).'\', '.$now.', \''.$db->escape($forum_user['g_color']).'\')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
			}
			else
			{
				if ($forum_user['logged'] < ($now-$configuration['o_timeout_visit']))
				{
					$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$forum_user['logged'].', read_topics=NULL WHERE id='.$forum_user['id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
					$forum_user['last_visit'] = $forum_user['logged'];
				}
				$idle_sql = ($forum_user['idle'] == '1') ? ', idle=0' : '';
				$db->query('UPDATE '.$db->prefix.'online SET logged='.$now.$idle_sql.' WHERE user_id='.$forum_user['id']) or error('Unable to update online list', __FILE__, __LINE__, $db->error());
			}
		}
		$forum_user['is_guest'] = false;
	}
	else set_default_user();
}

/**
 * Default User
 */

function set_default_user()
{
	global $db, $forum_user, $configuration;
	$remote_addr = get_remote_address();
	$result = $db->query('SELECT u.*, g.*, o.logged FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.ident=\''.$remote_addr.'\' WHERE u.id=1') or error('Unable to fetch guest information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) exit('Unable to fetch guest information. The table \''.$db->prefix.'users\' must contain an entry with id = 1 that represents anonymous users.');
	$forum_user = $db->fetch_assoc($result);
	if (!$forum_user['logged']) $db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged, color) VALUES(1, \''.$db->escape($remote_addr).'\', '.time().', \''.$forum_user['g_color'].'\')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
	else $db->query('UPDATE '.$db->prefix.'online SET logged='.time().' WHERE ident=\''.$db->escape($remote_addr).'\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());
	$forum_user['disp_topics'] = $configuration['o_disp_topics_default'];
	$forum_user['disp_posts'] = $configuration['o_disp_posts_default'];
	$forum_user['timezone'] = $configuration['o_server_timezone'];
	$forum_user['language'] = $configuration['o_default_lang'];
	$forum_user['style'] = $configuration['o_default_style'];
	$forum_user['is_guest'] = true;
}

/**
 * Enter description here...
 *
 * @param unknown_type $user_id
 * @param unknown_type $password_hash
 * @param unknown_type $expire
 */

function forum_setcookie($user_id, $password_hash, $expire)
{
	global $cookie_name, $cookie_path, $cookie_domain, $cookie_secure, $cookie_seed;
	setcookie($cookie_name, serialize(array($user_id, md5($cookie_seed.$password_hash))), $expire, $cookie_path, $cookie_domain, $cookie_secure);
}

/**
 * Enter description here...
 */

function check_bans()
{
	global $db, $configuration, $lang_common, $forum_user, $forum_bans;
	if ($forum_user['g_id'] == USER_ADMIN || !$forum_bans) return;
	$user_ip = get_remote_address().'.';
	$bans_altered = false;
	foreach ($forum_bans as $cur_ban)
	{
		if ($cur_ban['expire'] != '' && $cur_ban['expire'] <= time())
		{
			$db->query('DELETE FROM '.$db->prefix.'bans WHERE id='.$cur_ban['id']) or error('Unable to delete expired ban', __FILE__, __LINE__, $db->error());
			$bans_altered = true;
			continue;
		}
		if ($cur_ban['username'] != '' && !strcasecmp($forum_user['username'], $cur_ban['username']))
		{
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($forum_user['username']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			message($lang_common['Ban message'].' '.(($cur_ban['expire'] != '') ? $lang_common['Ban message 2'].' '.strtolower(format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message'] != '') ? $lang_common['Ban message 3'].'<br /><br /><strong>'.convert_htmlspecialchars($cur_ban['message']).'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.', true);
		}
		if ($cur_ban['ip'] != '')
		{
			$cur_ban_ips = explode(' ', $cur_ban['ip']);
			for ($i = 0; $i < count($cur_ban_ips); ++$i)
			{
				$cur_ban_ips[$i] = $cur_ban_ips[$i].'.';
				if (substr($user_ip, 0, strlen($cur_ban_ips[$i])) == $cur_ban_ips[$i])
				{
					$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($forum_user['username']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
					message($lang_common['Ban message'].' '.(($cur_ban['expire'] != '') ? $lang_common['Ban message 2'].' '.strtolower(format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message'] != '') ? $lang_common['Ban message 3'].'<br /><br /><strong>'.convert_htmlspecialchars($cur_ban['message']).'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.', true);
				}
			}
		}
	}
	if ($bans_altered)
	{
		require_once FORUM_ROOT.'include/cache.php';
		generate_bans_cache();
	}
}

/**
 * Enter description here...
 */

function update_users_online()
{
	global $db, $configuration, $forum_user;
	$now = time();
	$pathinfo = pathinfo($_SERVER['PHP_SELF']);
	$current_page_full = $_SERVER['PHP_SELF'];
	$current_page = $pathinfo['basename'];
	$current_ip = get_remote_address();
	if ($current_page == "view_forum.php" || $current_page == "view_topic.php" || $current_page == "profile.php" || $current_page == "post.php" || $current_page == "edit.php")
	{
		if (isset($_GET['id']))
		{
			$current_page_id = intval($_GET['id']);
		}
		else if (isset($_GET['pid']))
		{
			$current_topic_id = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id=\''.intval($_GET["pid"]).'\'') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
			$tmp = $db->result($current_topic_id, 0);
			$current_page_id = ($tmp != '') ? $tmp : '0' ;
		}
		else if (isset($_GET['tid']))
		{
			$current_page_id = intval($_GET['tid']);
		}
		else if (isset($_GET['fid']))
		{
			$current_page_id = intval($_GET['fid']);
		}
	}
	else  $current_page_id = 0;
	if($forum_user['id'] > 1) $db->query('UPDATE '.$db->prefix.'online SET current_page=\''.$current_page_full.'\', current_ip=\''.$current_ip.'\', current_page_id=\''.$current_page_id.'\' WHERE user_id=\''.$forum_user['id'].'\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());
	else $db->query('UPDATE '.$db->prefix.'online SET current_page=\''.$current_page_full.'\', current_ip=\''.$current_ip.'\', current_page_id=\''.$current_page_id.'\' WHERE ident=\''.$current_ip.'\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());
	$result = $db->query('SELECT * FROM '.$db->prefix.'online WHERE logged<'.($now-$configuration['o_timeout_online'])) or error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
	while ($cur_user = $db->fetch_assoc($result))
	{
		if ($cur_user['user_id'] == '1') $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($cur_user['ident']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
		else
		{
			if ($cur_user['logged'] < ($now-$configuration['o_timeout_visit']))
			{
				$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$cur_user['logged'].', read_topics=NULL WHERE id='.$cur_user['user_id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
				$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$cur_user['user_id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			}
			else if ($cur_user['idle'] == '0') $db->query('UPDATE '.$db->prefix.'online SET idle=1 WHERE user_id='.$cur_user['user_id']) or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
		}
	}
}

/**
 * Returns a list of the navigation links availible for the user (used in the header of the page) in the form of an unordered list.
 */

function generate_navlinks()
{
	global $configuration, $lang_common, $forum_user;
	$links[] = '<li id="navindex" class="brdmenulinks"><a href="'.FORUM_ROOT.'index.php">'.$lang_common['Index'].'</a>';
    if ($forum_user['g_view_users'] == '1')
	{
		$links[] = '<li id="navuserlist" class="brdmenulinks"><a class="navbar" href="'.FORUM_ROOT.'userlist.php">'.$lang_common['User list'].'</a>';
	}
	if ($configuration['o_onlist_enable'] == '1') $links[] = '<li id="navonline" class="brdmenulinks"><a href="'.FORUM_ROOT.'online.php">'.$lang_common['Online List'].'</a>';
	if ($configuration['g_gallery_enable'] == '1') $links[] = '<li id="navgallery" class="brdmenulinks"><a href="'.FORUM_ROOT.'gallery.php">Gallery</a>';
	if ($configuration['o_um_enable'] == '1') $links[] = '<li id="navmap" class="brdmenulinks"><a href="'.FORUM_ROOT.'map.php">'.$lang_common['User map'].'</a></li>';
	$links[] = '<li id="navcalendar" class="brdmenulinks"><a href="'.FORUM_ROOT.'calendar.php">Calendar</a>';
	if ($configuration['cb_enable'] == '1') $links[] = '<li id="navchat" class="brdmenulinks"><a href="'.FORUM_ROOT.'chatbox.php">Chatbox</a>';
	if ($forum_user['g_search'] == '1') $links[] = '<li id="navsearch" class="brdmenulinks"><a href="'.FORUM_ROOT.'search.php">'.$lang_common['Search'].'</a>';
	if ($configuration['o_rules'] == '1') $links[] = '<li id="navrules" class="brdmenulinks"><a href="'.FORUM_ROOT.'misc.php?action=rules">'.$lang_common['Rules'].'</a>';
	if ($configuration['o_additional_navlinks'] != '')
	{
		if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $configuration['o_additional_navlinks']."\n", $extra_links))
		{
			for ($i = 0; $i < count($extra_links[1]); ++$i) array_splice($links, $extra_links[1][$i], 0, array('<li id="navextra'.($i + 1).'" class="brdmenulinks">'.$extra_links[2][$i]));
		}
	}
	return '<ul class="brdmenu">'."\n\t\t\t\t".implode($lang_common['Link separator'].'</li>'."\n\t\t\t\t", $links).'</li>'."\n\t\t\t".'</ul>';
}

function generate_navlinks2()
{
	global $configuration, $lang_common, $forum_user;
	if ($forum_user['is_guest'])
	{
		$books[] = '<li id="navregister" class="brdmenu2links"><a href="'.FORUM_ROOT.'register.php">'.$lang_common['Register'].'</a>';
		$books[] = '<li id="navlogin" class="brdmenu2links"><a href="'.FORUM_ROOT.'login.php">'.$lang_common['Login'].'</a>';
		$info = $lang_common['Not logged in'];
	}
	else
	{
		if ($forum_user['g_id'] > USER_MOD)
		{
			$books[] = '<li id="navprofile" class="brdmenu2links"><a href="'.FORUM_ROOT.'profile.php?id='.$forum_user['id'].'">'.$lang_common['MyProfile'].'</a>';
			require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
			if($configuration['o_pms_enabled'] && $forum_user['g_pm'] == 1) $books[] = '<li id="navmessage_list" class="brdmenu2links"><a href="'.FORUM_ROOT.'message_list.php">'.$lang_pms['Messages'].'</a>';
			$books[] = '<li id="navlogout" class="brdmenu2links"><a href="'.FORUM_ROOT.'login.php?action=out&amp;id='.$forum_user['id'].'">'.$lang_common['Logout'].'</a>';
		}
		else
		{
			require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
			if($configuration['o_pms_enabled'] && $forum_user['g_pm'] == 1) $books[] = '<li id="navmessage_list" class="brdmenu2links"><a href="'.FORUM_ROOT.'message_list.php">'.$lang_pms['Messages'].'</a>';
			$books[] = '<li id="navprofile" class="brdmenu2links"><a href="'.FORUM_ROOT.'profile.php?id='.$forum_user['id'].'">'.$lang_common['MyProfile'].'</a>';
			$books[] = '<li id="navadmin" class="brdmenu2links"><a href="'.FORUM_ROOT.'admin/admin_index.php">'.$lang_common['Admin'].'</a>';
			$books[] = '<li id="navlogout" class="brdmenu2links"><a href="'.FORUM_ROOT.'login.php?action=out&amp;id='.$forum_user['id'].'">'.$lang_common['Logout'].'</a>';
		}
		if ($forum_user['bookmarks'] != '')
		{
			if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $forum_user['bookmarks']."\n", $extra_bookmarks))
			{
				for ($i = 0; $i < count($extra_bookmarks[1]); ++$i) array_splice($books, $extra_bookmarks[1][$i], 0, array('<li id='.($i + 1).'" class="brdmenu2links">'.$extra_bookmarks[2][$i]));
			}
			if (preg_match_all('#(?<![0-9]=)<a href="[^"]+">[^<]+</a>#i', $forum_user['bookmarks']."\n", $bookies))
			{
				//array_push($books, $bookies[]);
				$books = $books + $bookies;
			}
		}
	}
	return '<ul class="brdmenu2">'."\n\t\t\t\t".implode($lang_common['Link separator']."\n\t\t\t\t", $books).'</li>'."\n\t\t\t".'</ul>';
}

/**
 * Displays the profile page menu.
 *
 * @param string $page
 */

function generate_profile_menu($page = '')
{
	global $lang_profile, $lang_invitation, $configuration, $forum_user, $id;
?>
<div id="profile" class="block2col">
	<div class="blockmenu">
		<h2><span><?php echo $lang_profile['Profile menu'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'essentials') echo ' class="isactive"'; ?>><a href="profile.php?section=essentials&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section essentials'] ?></a></li>
					<li<?php if ($page == 'personal') echo ' class="isactive"'; ?>><a href="profile.php?section=personal&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personal'] ?></a></li>
					<li<?php if ($page == 'bookmarks') echo ' class="isactive"'; ?>><a href="profile.php?section=bookmarks&amp;id=<?php echo $id ?>">Bookmarks</a></li>
					<li<?php if ($page == 'messaging') echo ' class="isactive"'; ?>><a href="profile.php?section=messaging&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section messaging'] ?></a></li>
					<li<?php if ($page == 'personality') echo ' class="isactive"'; ?>><a href="profile.php?section=personality&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personality'] ?></a></li>
					<li<?php if ($page == 'display') echo ' class="isactive"'; ?>><a href="profile.php?section=display&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section display'] ?></a></li>
					<li<?php if ($page == 'privacy') echo ' class="isactive"'; ?>><a href="profile.php?section=privacy&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section privacy'] ?></a></li>
					<li<?php if ($page == 'expertise') echo ' class="isactive"'; ?>><a href="expertise.php?id=<?php echo $id ?>"><?php echo $lang_profile['Section expertise'] ?></a></li>
<?php
if ($configuration['o_invitations_enable'])
{
?>
					<li<?php if ($page == 'invitation') echo ' class="isactive"'; ?>><a href="profile.php?section=invitation&amp;id=<?php echo $id ?>"><?php echo $lang_invitation['Invitations'] ?></a></li>
<?php
}
if ($configuration['o_digests_enable'])
{
?>
					<li<?php if ($page == 'digests') echo ' class="isactive"'; ?>><a href="digests.php?id=<?php echo $id ?>"><?php echo $lang_profile['Section digests'] ?></a></li>
<?php
}
?>
<?php if ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_ban_users'] == '1')): ?>					<li<?php if ($page == 'admin') echo ' class="isactive"'; ?>><a href="profile.php?section=admin&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section admin'] ?></a></li>
<?php endif; ?>
	  		 		<li<?php if ($page == 'view') echo ' class="isactive"'; ?>><a href="profile.php?action=view&amp;id=<?php echo $id ?>"><?php echo $lang_profile['View profile'] ?></a></li>
				</ul>
			</div>
		</div>
	</div>
<?php

}

/**
 * Updates the columns num_topics, num_posts, last_post, last_post_id, last_poster for the given forum id
 *
 * @param int $forum_id
 */

function update_forum($forum_id)
{
	global $db;
	$result = $db->query('SELECT COUNT(id), SUM(num_replies) FROM '.$db->prefix.'topics WHERE moved_to IS NULL AND forum_id='.$forum_id) or error('Unable to fetch forum topic count', __FILE__, __LINE__, $db->error());
	list($num_topics, $num_posts) = $db->fetch_row($result);
	$num_posts = $num_posts + $num_topics;
	$result = $db->query('SELECT last_post, last_post_id, last_poster FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id.' AND moved_to IS NULL ORDER BY last_post DESC LIMIT 1') or error('Unable to fetch last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		list($last_post, $last_post_id, $last_poster) = $db->fetch_row($result);
		$db->query('UPDATE '.$db->prefix.'forums SET num_topics='.$num_topics.', num_posts='.$num_posts.', last_post='.$last_post.', last_post_id='.$last_post_id.', last_poster=\''.$db->escape($last_poster).'\' WHERE id='.$forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
	}
	else $db->query('UPDATE '.$db->prefix.'forums SET num_topics=0, num_posts=0, last_post=NULL, last_post_id=NULL, last_poster=NULL WHERE id='.$forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
}

/**
 * Deletes a topic and removes all posts in it. Also removes any subscriptions and redirect topics for the given topic id
 *
 * @param int $topic_id
 */

function delete_topic($topic_id)
{
	global $db;
	$db->query('DELETE FROM '.$db->prefix.'topics WHERE id='.$topic_id.' OR moved_to='.$topic_id) or error('Unable to delete topic', __FILE__, __LINE__, $db->error());
	$post_ids = '';
	$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_row($result))
	{
		$post_ids .= ($post_ids != '') ? ','.$row[0] : $row[0];
		delete_images($row[0]);
	}
	if ($post_ids != '')
	{
		strip_search_index($post_ids);
		$db->query('DELETE FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to delete posts', __FILE__, __LINE__, $db->error());
	}
	$db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE topic_id='.$topic_id) or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());
	$db->query('DELETE FROM '.$db->prefix.'polls WHERE pollid='.$topic_id) or error('Unable to delete polls', __FILE__, __LINE__, $db->error());
}

/**
 * Deletes a single post and updates the forum and topic.
 *
 * @param int $post_id
 * @param int $topic_id

 */

function delete_post($post_id, $topic_id)
{
	global $db;
	$result = $db->query('SELECT id, poster, posted FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id.' ORDER BY id DESC LIMIT 2') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	list($last_id, ,) = $db->fetch_row($result);
	list($second_last_id, $second_poster, $second_posted) = $db->fetch_row($result);
	$db->query('DELETE FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to delete post', __FILE__, __LINE__, $db->error());
	delete_images($post_id);
	strip_search_index($post_id);
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
	$num_replies = $db->result($result, 0) - 1;
	if ($last_id == $post_id)
	{
		if (!empty($second_last_id)) $db->query('UPDATE '.$db->prefix.'topics SET last_post='.$second_posted.', last_post_id='.$second_last_id.', last_poster=\''.$db->escape($second_poster).'\', num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
		else $db->query('UPDATE '.$db->prefix.'topics SET last_post=posted, last_post_id=id, last_poster=poster, num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
	}
	else $db->query('UPDATE '.$db->prefix.'topics SET num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
}

/**
 * Returns text but with censored words.
 *
 * @param string $text
 * @return string

 */

function censor_words($text)
{
	global $db;
	static $search_for, $replace_with;
	if (!isset($search_for))
	{
		$result = $db->query('SELECT search_for, replace_with FROM '.$db->prefix.'censoring') or error('Unable to fetch censor word list', __FILE__, __LINE__, $db->error());
		$num_words = $db->num_rows($result);
		$search_for = array();
		for ($i = 0; $i < $num_words; ++$i)
		{
			list($search_for[$i], $replace_with[$i]) = $db->fetch_row($result);
			$search_for[$i] = '/\b('.str_replace('\*', '\w*?', preg_quote($search_for[$i], '/')).')\b/i';
		}
	}
	if (!empty($search_for)) $text = substr(preg_replace($search_for, $replace_with, ' '.$text.' '), 1, -1);
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $user
 * @return unknown

 */

function get_title($user)
{
	global $db, $configuration, $forum_bans, $lang_common;
	static $ban_list, $forum_ranks;
	if (empty($ban_list))
	{
		$ban_list = array();
		foreach ($forum_bans as $cur_ban) $ban_list[] = strtolower($cur_ban['username']);
	}
	if ($configuration['o_ranks'] == '1' && empty($forum_ranks))
	{
		@include FORUM_ROOT.'cache/cache_ranks.php';
		if (!defined('RANKS_LOADED'))
		{
			require_once FORUM_ROOT.'include/cache.php';
			generate_ranks_cache();
			require FORUM_ROOT.'cache/cache_ranks.php';
		}
	}
	if ($user['title'] != '') $user_title = convert_htmlspecialchars($user['title']);
	else if (in_array(strtolower($user['username']), $ban_list)) $user_title = $lang_common['Banned'];
	else if ($user['g_user_title'] != '') $user_title = convert_htmlspecialchars($user['g_user_title']);
	else if ($user['g_id'] == USER_GUEST) $user_title = $lang_common['Guest'];
	else
	{
		if ($configuration['o_ranks'] == '1' && !empty($forum_ranks))
		{
			@reset($forum_ranks);
			while (list(, $cur_rank) = @each($forum_ranks))
			{
				if (intval($user['num_posts']) >= $cur_rank['min_posts']) $user_title = convert_htmlspecialchars($cur_rank['rank']);
			}
		}
		if (!isset($user_title)) $user_title = $lang_common['Member'];
	}
	return $user_title;
}

/**
 * Enter description here...
 *
 * @param unknown_type $num_pages
 * @param unknown_type $cur_page
 * @param unknown_type $link_to
 * @return unknown

 */

function paginate($num_pages, $cur_page, $link_to)
{
	$pages = array();
	$link_to_all = false;
	$nav_links = true;
	if ($cur_page == -1)
	{
		$cur_page = 1;
		$link_to_all = true;
		$nav_links = false;
	}
	if ($num_pages <= 1) $pages = array('<strong>1</strong>');
	else
	{
		if ($cur_page > 3)
		{
			$pages[] = '<a href="'.$link_to.'&amp;p=1">1</a>';
			if ($cur_page != 4) $pages[] = '&hellip;';
		}
		for ($current = $cur_page - 2, $stop = $cur_page + 3; $current < $stop; ++$current)
		{
			if ($current < 1 || $current > $num_pages) continue;
			else if ($current != $cur_page || $link_to_all) $pages[] = '<a href="'.$link_to.'&amp;p='.$current.'">'.$current.'</a>';
			else $pages[] = '<strong>'.$current.'</strong>';
		}

		if ($cur_page <= ($num_pages-3))
		{
			if ($cur_page != ($num_pages-3)) $pages[] = '&hellip;';
			$pages[] = '<a href="'.$link_to.'&amp;p='.$num_pages.'">'.$num_pages.'</a>';
		}
	}
	if($nav_links)
	{
		if($cur_page > 1)
		{
			$back_page_number = $cur_page-1;
			$back_page = '<a href="'.$link_to.'&amp;p='.$back_page_number.'">&laquo;--</a>';
			array_splice($pages, 0, 0, $back_page);
		}
		if($cur_page < $num_pages)
		{
			$next_page_number =  $cur_page+1;
			$next_page = '<a href="'.$link_to.'&amp;p='.$next_page_number.'">--&raquo;</a>';
			array_push($pages, $next_page);
		}
	}
	return implode('&nbsp;', $pages);
}

/**
 * Enter description here...
 *
 * @param unknown_type $keywords

 */

function suggest($keywords)
{
	global $db, $lang_search;
	if( isset($_GET['suggest']) || empty( $keywords ) )
	{
		message($lang_search['No hits']);
	}
	$suggest = array();
	$bold = array();
	foreach ($keywords as $cur_word)
	{
		$len = strlen( $cur_word );
		if( $len < 3 || $cur_word == 'and' || $cur_word == 'or' || $cur_word == 'not')
		{
			continue;
		}
		$size = round( $len / 3 );
		$cur_word1 = substr( $cur_word, 0, $size );
		$cur_word2 = substr( $cur_word, $size, $size );
		$cur_word3 = substr( $cur_word, 2*$size );
		$sql = 'SELECT w.word, COUNT(m.post_id) AS c FROM '.$db->prefix.'search_words AS w INNER JOIN '.$db->prefix.'search_matches AS m ON m.word_id = w.id WHERE CHAR_LENGTH(w.word) >= '.($len-2).' and CHAR_LENGTH(w.word) <= '.($len+2).' and (w.word like \''.$cur_word1.'%\' or w.word like \'%'.$cur_word2.'%\' or w.word like \'%'.$cur_word3.'\') GROUP BY w.word ORDER BY c DESC LIMIT 50';
		$result = $db->query($sql, true) or error('Unable to search for posts', __FILE__, __LINE__, $db->error());
		while ($temp = $db->fetch_row($result))
		{
			if (levenshtein($temp[0], $cur_word) <= 2)
			{
				$suggest[$cur_word] = $temp[0];
				$bold[$cur_word] = '<b><i>'.$temp[0].'</i></b>';
				break;
			}
		}
	}
	if( empty( $suggest ) ) message($lang_search['No hits']);
	$_GET['keywords'] = strtolower(trim($_GET['keywords']));
	$suggestion = str_replace( array_keys($bold), array_values($bold), $_GET['keywords'] );
	$_GET['keywords'] = str_replace( array_keys($suggest), array_values($suggest), $_GET['keywords'] );
	$q = '';
	foreach( $_GET as $k => $v )
		$q .= $k.'='.$v.'&';
	message( 'Did you mean: <a href="search.php?'.$q.'suggest=1">'.$suggestion.'</a> ?</p><p>'.$lang_search['No hits'] );
}

/**
 * Enter description here...
 *
 * @param unknown_type $message
 * @param unknown_type $no_back_link

 */

function message($message, $no_back_link = false)
{
	global $db, $lang_common, $configuration, $forum_start, $tpl_main;
	if (!defined('FORUM_HEADER'))
	{
		global $forum_user;
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Info'];
		require FORUM_ROOT.'header.php';
	}
?>
<div id="msg" class="block">
	<h2><span><?php echo $lang_common['Info'] ?></span></h2>
	<div class="box">
		<div class="inbox">
		<p><br /><?php echo $message ?><br /><br /></p>
<?php
	if (!$no_back_link):
?>		<p><input type="button" class="b1" onClick="javascript:history.go(-1)" value="<?php echo$lang_common['Go back']?>" /></p>
<?php
	endif;
?>		</div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}

/**
 * Enter description here...
 *
 * @param unknown_type $timestamp
 * @param unknown_type $date_only
 * @return unknown

 */

function format_time($timestamp, $date_only = false)
{
	global $configuration, $lang_common, $forum_user;
	if ($timestamp == '') return $lang_common['Never'];
	$diff = ($forum_user['timezone'] - $configuration['o_server_timezone']) * 3600;
	$timestamp += $diff;
	$now = time();
	$date = date($configuration['o_date_format'], $timestamp);
	$today = date($configuration['o_date_format'], $now+$diff);
	$yesterday = date($configuration['o_date_format'], $now+$diff-86400);
	if ($date == $today) $date = $lang_common['Today'];
	else if ($date == $yesterday) $date = $lang_common['Yesterday'];
	if (!$date_only) return $date.' '.date($configuration['o_time_format'], $timestamp);
	else return $date;
}

if (!function_exists('file_get_contents'))
{
	function file_get_contents($filename, $use_include_path = 0)
	{
		$data = '';
		if ($fh = fopen($filename, 'rb', $use_include_path))
		{
			$data = fread($fh, filesize($filename));
			fclose($fh);
		}
		return $data;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $script

 */

function confirm_referrer($script)
{
	global $configuration, $lang_common;
	static $rewrites = array('view_poll.php'=>'l', 'view_topic.php'=>'t', 'view_forum.php'=>'f', 'post.php'=>'p');
	$referer = str_replace('www.', '', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));
	$allowed_referer_base = preg_quote(str_replace('www.', '', $configuration['o_base_url']).'/', '#');
	$allowed_referer = preg_quote($script, '#');
	if (!preg_match('#^'.$allowed_referer_base.$allowed_referer.'#i', $referer))
	{
		if (array_key_exists($script, $rewrites))
		{
			$allowed_rewrites = $rewrites[$script].'[0-9]+-[0-9|a-b|\-|\.]*';
//			if (!preg_match('#^'.$allowed_referer_base.$allowed_rewrites.'#i', $referer)) message($lang_common['Bad referrer']);
		}
//		else message($lang_common['Bad referrer']);
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $len
 * @return unknown

 */

function random_pass($len)
{
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$password = '';
	for ($i = 0; $i < $len; ++$i) $password .= substr($chars, (mt_rand() % strlen($chars)), 1);
	return $password;
}

/**
 * Enter description here...
 *
 * @param unknown_type $str
 * @return unknown

 */

function forum_hash($str)
{
	if (function_exists('sha1')) return sha1($str);
	else if (function_exists('mhash')) return bin2hex(mhash(MHASH_SHA1, $str));
	else return md5($str);
}

/**
 * Enter description here...
 *
 * @return unknown

 */

function get_remote_address()
{
	$remote_address = isset($_SERVER['SHELL']) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
	return $remote_address;
}

/**
 * Enter description here...
 *
 * @param unknown_type $str
 * @return unknown

 */

function convert_htmlspecialchars($str)
{
	$str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
	$str = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $str);
	return $str;
}

/**
 * Enter description here...
 *
 * @param unknown_type $str
 * @return unknown

 */

function forum_strlen($str)
{
	return strlen(preg_replace('/&#([0-9]+);/', '!', $str));
}

/**
 * Enter description here...
 *
 * @param unknown_type $str
 * @return unknown

 */

function forum_linebreaks($str)
{
	return str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
}

/**
 * Enter description here...
 *
 * @param unknown_type $str
 * @return unknown

 */

function forum_trim($str)
{
	global $lang_common;
	if (strpos($lang_common['lang_encoding'], '8859') !== false)
	{
		$fishy_chars = array(chr(0x81), chr(0x8D), chr(0x8F), chr(0x90), chr(0x9D), chr(0xA0));
		return trim(str_replace($fishy_chars, ' ', $str));
	}
	else return trim($str);
}

/**
 * Enter description here...

 */

function maintenance_message()
{
	global $db, $configuration, $lang_common, $forum_user;
	$pattern = array("\t", '  ', '  ');
	$replace = array('&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;');
	$message = str_replace($pattern, $replace, $configuration['o_maintenance_message']);
	global $style;
	if(is_file(FORUM_ROOT.'include/template/'.$style.'/maintenance.tpl')) $tpl_maint = trim(file_get_contents(FORUM_ROOT.'include/template/'.$style.'/maintenance.tpl'));
	else $tpl_maint = trim(file_get_contents(FORUM_ROOT.'include/template/maintenance.tpl'));
	$tpl_maint = str_replace('<forum_content_direction>', $lang_common['lang_direction'], $tpl_maint);
	$tpl_maint = str_replace('<forum_char_encoding>', $lang_common['lang_encoding'], $tpl_maint);
	ob_start();
?>
<title><?php echo convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_common['Maintenance'] ?></title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $forum_user['style'].'.css' ?>" />
<?php
	$tpl_temp = trim(ob_get_contents());
	$tpl_maint = str_replace('<forum_head>', $tpl_temp, $tpl_maint);
	ob_end_clean();
	$tpl_maint = str_replace('<forum_maint_heading>', $lang_common['Maintenance'], $tpl_maint);
	$tpl_maint = str_replace('<forum_maint_message>', $message, $tpl_maint);
	$db->end_transaction();
	while (preg_match('#<forum_include "([^/\\\\]*?)">#', $tpl_maint, $cur_include))
	{
		if (!file_exists(FORUM_ROOT.'include/user/'.$cur_include[1])) error('Unable to process user include &lt;forum_include "'.htmlspecialchars($cur_include[1]).'"&gt; from template maintenance.tpl. There is no such file in folder /include/user/');
		ob_start();
		include FORUM_ROOT.'include/user/'.$cur_include[1];
		$tpl_temp = ob_get_contents();
		$tpl_maint = str_replace($cur_include[0], $tpl_temp, $tpl_maint);
		ob_end_clean();
	}
	$db->close();
	exit($tpl_maint);
}

/**
 * Enter description here...
 *
 * @param unknown_type $destination_url
 * @param unknown_type $message

 */

function redirect($destination_url, $message)
{
	global $db, $configuration, $lang_common, $forum_user;
	if ($destination_url == '') $destination_url = 'index.php';
	if ($configuration['o_redirect_delay'] == '0') header('Location: '.str_replace('&amp;', '&', $destination_url));
	global $style;
	if(is_file(FORUM_ROOT.'include/template/'.$style.'/redirect.tpl')) $tpl_redir = trim(file_get_contents(FORUM_ROOT.'include/template/'.$style.'/redirect.tpl'));
	else $tpl_redir = trim(file_get_contents(FORUM_ROOT.'include/template/redirect.tpl'));
	$tpl_redir = str_replace('<forum_content_direction>', $lang_common['lang_direction'], $tpl_redir);
	$tpl_redir = str_replace('<forum_char_encoding>', $lang_common['lang_encoding'], $tpl_redir);
	ob_start();
?>
<meta http-equiv="refresh" content="<?php echo $configuration['o_redirect_delay'] ?>;URL=<?php echo str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $destination_url) ?>" />
<title><?php echo convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_common['Redirecting'] ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo FORUM_ROOT?>style/<?php echo $forum_user['style'].'.css' ?>" />
<?php
	$tpl_temp = trim(ob_get_contents());
	$tpl_redir = str_replace('<forum_head>', $tpl_temp, $tpl_redir);
	ob_end_clean();
	$tpl_redir = str_replace('<forum_redir_heading>', $lang_common['Redirecting'], $tpl_redir);
	$tpl_temp = $message.'<br /><br />'.'<a href="'.$destination_url.'">'.$lang_common['Click redirect'].'</a>';
	$tpl_redir = str_replace('<forum_redir_text>', $tpl_temp, $tpl_redir);
	ob_start();
	$db->end_transaction();
	if (defined('SHOW_QUERIES')) display_saved_queries();
	$tpl_temp = trim(ob_get_contents());
	$tpl_redir = str_replace('<forum_footer>', $tpl_temp, $tpl_redir);
	ob_end_clean();
	while (preg_match('#<forum_include "([^/\\\\]*?)">#', $tpl_redir, $cur_include))
	{
		if (!file_exists(FORUM_ROOT.'include/user/'.$cur_include[1])) error('Unable to process user include &lt;forum_include "'.htmlspecialchars($cur_include[1]).'"&gt; from template redirect.tpl. There is no such file in folder /include/user/');
		ob_start();
		include FORUM_ROOT.'include/user/'.$cur_include[1];
		$tpl_temp = ob_get_contents();
		$tpl_redir = str_replace($cur_include[0], $tpl_temp, $tpl_redir);
		ob_end_clean();
	}
	$db->close();
	exit($tpl_redir);
}

/**
 * Enter description here...
 *
 * @param unknown_type $message
 * @param unknown_type $file
 * @param unknown_type $line
 * @param unknown_type $db_error

 * @author Eli White <thesavior@cox.net>
 */

function error($message, $file, $line, $db_error = false)
{
	global $configuration;
	if (empty($configuration)) $configuration['o_board_name'] = 'PowerBB Forum';
	@ob_end_clean();
	if (!empty($configuration['o_gzip']) && extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false)) ob_start('ob_gzhandler');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo convert_htmlspecialchars($configuration['o_board_name']) ?> / Error</title>
<style type="text/css">
<!--
BODY {MARGIN: 10% 20% auto 20%; font: 10px Verdana, Arial, Helvetica, sans-serif}
#errorbox {BORDER: 1px solid #B84623}
H2 {MARGIN: 0; COLOR: #FFFFFF; BACKGROUND-COLOR: #B84623; FONT-SIZE: 1.1em; PADDING: 5px 4px}
#errorbox DIV {PADDING: 6px 5px; BACKGROUND-COLOR: #F1F1F1}
-->
</style>
</head>
<body>
<div id="errorbox">
	<h2>An error was encountered</h2>
	<div>
<?php
	if (defined('DEBUG'))
	{
		echo "\t\t".'<strong>File:</strong> '.$file.'<br />'."\n\t\t".'<strong>Line:</strong> '.$line.'<br /><br />'."\n\t\t".'<strong>PowerBB Forum reported</strong>: '.$message."\n";
		if ($db_error)
		{
			echo "\t\t".'<br /><br /><strong>Database reported:</strong> '.convert_htmlspecialchars($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '')."\n";
			if ($db_error['error_sql'] != '') echo "\t\t".'<br /><br /><strong>Failed query:</strong> '.convert_htmlspecialchars($db_error['error_sql'])."\n";
		}
	}
	else echo "\t\t".'Error: <strong>'.$message.'.</strong>'."\n";
?>
	</div>
</div>
</body>
</html>
<?php
	if ($db_error) $GLOBALS['db']->close();
	exit;
}

/**
 * Enter description here...

 */

function display_saved_queries()
{
	global $db, $lang_common;
	$saved_queries = $db->get_saved_queries();
?>
<div id="debug" class="blocktable">
	<h2><span><?php echo $lang_common['Debug table'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Time (s)</th>
					<th class="tcr" scope="col">Query</th>
				</tr>
			</thead>
			<tbody>
<?php
	$query_time_total = 0.0;
	while (list(, $cur_query) = @each($saved_queries))
	{
		$query_time_total += $cur_query[1];
?>
				<tr>
					<td class="tcl"><?php echo ($cur_query[1] != 0) ? $cur_query[1] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo convert_htmlspecialchars($cur_query[0]) ?></td>
				</tr>
<?php
	}
?>
				<tr>
					<td class="tcl" colspan="2">Total query time: <?php echo $query_time_total ?> s</td>
				</tr>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php
}

/**
 * Enter description here...

 */

function unregister_globals()
{
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) exit('I\'ll have a steak sandwich and... a steak sandwich.');
	$no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ($input as $k => $v)
	{
		if (!in_array($k, $no_unset) && isset($GLOBALS[$k])) unset($GLOBALS[$k]);
	}
}

/**
 * Enter description here...

 */

function dump()
{
	echo '<pre>';
	$num_args = func_num_args();
	for ($i = 0; $i < $num_args; ++$i)
	{
		print_r(func_get_arg($i));
		echo "\n\n";
	}
	echo '</pre>';
	exit;
}

/**
 * Enter description here...
 *
 * @return unknown

 */

function get_all_new_topics()
{
	global $db, $forum_user;
	$result = $db->query('SELECT forum_id, id, last_post FROM '.$db->prefix.'topics WHERE last_post>'. $forum_user['last_visit'] .' AND moved_to IS NULL ORDER BY last_post DESC') or error('Unable to fetch new topics from forum', __FILE__, __LINE__, $db->error());
	$new_topics = array();
	while($new_topics_row = $db->fetch_assoc($result)) $new_topics[$new_topics_row['forum_id']][$new_topics_row['id']] = $new_topics_row['last_post'];
	return $new_topics;
}

/**
 * Enter description here...
 *
 * @param unknown_type $forum_id
 * @param unknown_type $last_post_time
 * @return unknown

 */

function forum_is_new($forum_id, $last_post_time)
{
	global $forum_user, $new_topics;
	if ($forum_user['last_visit'] >= $last_post_time)
	{
		return false;
	}
	else if (!empty($forum_user['read_topics']['f'][$forum_id]) && $forum_user['read_topics']['f'][$forum_id] >= $last_post_time)
	{
		return false;
	}
	else if (empty($forum_user['read_topics']['t']) && empty($forum_user['read_topics']['f']))
	{
		return true;
	}
	else
	{
		foreach($new_topics[$forum_id] as $topic_id => $last_post)
		{
			if ((empty($forum_user['read_topics']['f'][$forum_id]) || $forum_user['read_topics']['f'][$forum_id] < $last_post) && (empty($forum_user['read_topics']['t'][$topic_id]) || $forum_user['read_topics']['t'][$topic_id] < $last_post)) return true;
		}
		return false;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $topic_id
 * @param unknown_type $forum_id
 * @param unknown_type $last_post_time
 * @return unknown

 */

function topic_is_new($topic_id, $forum_id, $last_post_time)
{
	global $forum_user;
	if ($forum_user['last_visit'] >= $last_post_time)
	{
		return false;
	}
	else if (!empty($forum_user['read_topics']['f'][$forum_id]) && $forum_user['read_topics']['f'][$forum_id] >= $last_post_time)
	{
		return false;
	}
	else if (!empty($forum_user['read_topics']['t'][$topic_id]) && $forum_user['read_topics']['t'][$topic_id] >= $last_post_time)
	{
		return false;
	}
	return true;
}

/**
 * Enter description here...
 *
 * @param unknown_type $topic_id
 * @param unknown_type $forum_id
 * @param unknown_type $last_post

 */

function mark_topic_read($topic_id, $forum_id, $last_post)
{
	global $db, $forum_user;
	if (topic_is_new($topic_id, $forum_id, $last_post))
	{
		$forum_user['read_topics']['t'][$topic_id] = time();
		$db->query('UPDATE '.$db->prefix.'users SET read_topics=\''.$db->escape(serialize($forum_user['read_topics'])).'\' WHERE id='.$forum_user['id']) or error('Unable to update read-topic data', __FILE__, __LINE__, $db->error());
	}
}

/**
 * Enter description here...
 *
 * @return unknown

 */

function isBotOnline()
{
	global $db;
	$botResult = '';
	$result = $db->query('SELECT * FROM '.$db->prefix.'botsconfig');
	$botConfig = $db->fetch_row($result);
	$botLifeSpan = $botConfig[1];
	$botUAStringSensitive = $botConfig[2];
	$botEnabled = $botConfig[3];
	if($botEnabled == 0) return $botResult;
	$result = $db->query('SELECT * FROM '.$db->prefix.'bots') or error('Unable to retrieve bot data.', __FILE__, __LINE__, 								$db->error());
	while ($row = $db->fetch_row($result))
	{
		if($botUAStringSensitive == 0) $userAgent = strtolower(getenv('HTTP_USER_AGENT')); else $userAgent = getenv('HTTP_USER_AGENT');
		if(strstr($userAgent, $row[2]))
		{
			$result = $db->query('UPDATE '.$db->prefix.'bots SET time_stamp='.time().' WHERE id='.$row[0]);
			break;
		}
		elseif(date("Y-m-d", $row[3]) == date("Y-m-d") & date("G", $row[3]) == date("G") & date("i") - date("i", $row[3]) <= $botLifeSpan)
		{
			$botResult .= ', ' . $row[1];
		}
	}
	return $botResult;
}

$key = 'j4dlrpqw02jk%&(jk7"{)HJD"$}?>VM"%}PF:*(%Lpd0=+-3h25mdkwy3idf83jdsu3';

/**
 * Enter description here...
 *
 * @param unknown_type $a
 * @param unknown_type $b
 * @param unknown_type $l
 * @return unknown

 */
function bytexor($a,$b,$l)
{
	$c="";
	for($i=0;$i<$l;$i++)
	{
		$c.=$a{$i}^$b{$i};
	}
	return($c);
}

/**
 * Enter description here...
 *
 * @param unknown_type $val
 * @return unknown

 */
function binmd5($val)
{
	return(pack("H*",md5($val)));
}

/**
 * Enter description here...
 *
 * @param unknown_type $msg
 * @param unknown_type $heslo
 * @return unknown

 */

function decrypt_md5($msg,$heslo)
{
	$key=$heslo;$sifra="";
	$key1=binmd5($key);
	while($msg)
	{
		$m=substr($msg,0,16);
		$msg=substr($msg,16);
		$sifra.=$m=bytexor($m,$key1,16);
		$key1=binmd5($key.$key1.$m);
	}
	echo "\n";
	return($sifra);
}

/**
 * Enter description here...
 *
 * @param unknown_type $msg
 * @param unknown_type $heslo
 * @return unknown

 */

function crypt_md5($msg,$heslo)
{
	$key=$heslo;$sifra="";
	$key1=binmd5($key);
	while($msg)
	{
		$m=substr($msg,0,16);
		$msg=substr($msg,16);
		$sifra.=bytexor($m,$key1,16);
		$key1=binmd5($key.$key1.$m);
	}
	echo "\n";
	return($sifra);
}

/**
 * Enter description here...
 *
 * @param unknown_type $number
 * @param unknown_type $sigdigs
 * @return unknown

 */
function RoundSigDigs($number, $sigdigs)
{
   $multiplier = 1;
   while ($number < 0.1)
   {
       $number *= 10;
       $multiplier /= 10;
   }
   while ($number >= 1)
   {
       $number /= 10;
       $multiplier *= 10;
   }
   return round($number, $sigdigs) * $multiplier;
}

/**
 * Enter description here...
 *
 * @param unknown_type $directory
 * @return unknown

 */
function RemoveDirectory($directory)
{
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}
	if(!file_exists($directory) || !is_dir($directory))
	{
		return FALSE;
	}
	elseif(!is_readable($directory))
	{
		return FALSE;
	}
	else
	{
		$handle = opendir($directory);
		while (FALSE !== ($item = readdir($handle)))
		{
			if($item != '.' && $item != '..')
			{
				$path = $directory.'/'.$item;
				if(is_dir($path))
				{
					RemoveDirectory($path);
				}
				else
				{
					unlink($path);
				}
			}
		}
		closedir($handle);
		if(!rmdir($directory))
		{
			return FALSE;
		}
		return TRUE;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @param unknown_type $skip

 */
function RemoveDirectorySkip($path, $skip = array())
{

	if($dir = opendir($path))
	{
		$orgpath = $path;
		if($path == FORUM_ROOT && FORUM_ROOT == './')
			$path = '';
		else
			$path .= '/';
		while (($file = readdir($dir)) !== false)
		{
			if($file != '.' && $file != '..')
			{
				if(in_array($path.$file, $skip)) continue;
				if(is_dir($path.$file))
				{
					remove_directory($path.$file, $skip);
				}
				else
				{
					unlink($path.$file);
				}
			}
		}
		closedir($dir);
		if($path != '')
		{
			rmdir($path);
		}
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function RemoveBlankLines($text)
{
	while(trim($text[0]) == '') $text = array_slice($text, 1);
	$ptr = count($text);
	while(trim($text[$ptr]) == '') $ptr--;
	$text = array_slice($text, 0, $ptr+1);
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $what
 * @param unknown_type $where
 * @param unknown_type $start
 * @param unknown_type $end
 * @return unknown

 */
function LocateText(&$what, &$where, &$start, &$end)
 {
	global $files;
	$found = 0;
	$where_pos = 0;
	while ($where_pos < count($where))
	{
		$what_pos = 0;
		while (($where_pos+$what_pos < count($where)) && (strtolower(trim($what[$what_pos])) == strtolower(trim($where[$where_pos+$what_pos]))))
		{
			$what_pos++;
			if ($what_pos == count($what))
			{
				$start = $where_pos;
				$end = $where_pos + $what_pos - 1;
				$found++;
			}
		}
		$where_pos++;
	}
	return($found);
}

/**
 * Enter description here...
 *
 * @param unknown_type $path

 */
function RecursiveMkdir($path)
{
	if (!file_exists($path))
	{
		RecursiveMkdir(dirname($path));
		mkdir($path, 0777);
	}
}

$failed = array();

/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @param unknown_type $failed

 */
function IsDirectoryWritable($path = '', &$failed)
{
	if($dir = opendir($path))
	{
		$orgpath = $path;
		if($path == FORUM_ROOT && FORUM_ROOT == './') $path = '';
		else $path .= '/';
		while (($file = readdir($dir)) !== false)
		{
			if($file != '.' && $file != '..')
			{
				if(is_dir($path.$file))
				{
					IsDirectoryWritable($path.$file, $failed);
				}
				else
				{
					if(!is_writable($path.$file)) $failed[] = $path.$file;
				}
			}
		}
		closedir($dir);
	}
}

if (!function_exists('file_put_contents'))
{
	function file_put_contents($filename, $data)
	{
		if ($handle = fopen($filename, 'w'))
		{
			if (!fwrite($handle, $data)) return(false);
			fclose($handle);
		}
		else
		{
			return(false);
		}
		return(true);
	}
}
/**
 * Enter description here...
 *
 * @param unknown_type $date
 * @return unknown

 */
function start_date($date)
{
	global $configuration;
	if($configuration['cal_start_day'] == "M")
	{
		switch($date)
		{
			case 0:
				$start = 6;
				break;
			default:
				$start = $date - 1;
				break;
		}
	}
	elseif($configuration['cal_start_day'] == "S") $start = $date;
	return $start;
}

/**
 * Enter description here...
 *
 * @param unknown_type $type

 */
function navigation($type)
{
	global $lang_calendar,$lang_common,$day,$month,$year,$pages;
	
	$month_last_day = date("t", mktime(0, 0, 0, $month-1, 1, $year));
	$month_next_day = date("t", mktime(0, 0, 0, $month+1, 1, $year));
	$day_in_mth = date("t", mktime(0, 0, 0, $month, 1, $year));
	?>
	<br />
	<h2><span><? echo$lang_common['Pages'].':'.$pages?></span></h2>
	<div class="box">
	
	<table cellspacing="0">
	<thead>
		<tr>
			<th colspan="3"><? echo$lang_calendar['Navigation']?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align="right">
				<a href=?t=<? echo $type?>&amp;year=<? if($month == 1){echo $year-1;} else{echo $year;}?>&amp;month=<? if($day == 1){if($month==1){echo'12';}else{echo $month-1;}}else{echo $month;}?>&amp;day=<? if($day == 1){echo $month_last_day;}else{echo $day-1;}?>><? echo$lang_calendar['Last Day']?></a><br>
				<a href=?t=<? echo$type?>&amp;year=<? if($month == 1){echo $year-1;}else{echo $year;}?>&amp;month=<? if($month == 1){echo '12';}else{echo $month-1;}?>&amp;day=<? echo$day?>><? echo$lang_calendar['Last Month']?></a><br>
				<a href=?t=<? echo$type?>&amp;year=<? echo $year-1?>&amp;month=<? echo $month?>&amp;day=<? echo $day?>><? echo $lang_calendar['Last Year']?></a>
			</td>
			<td width="100" align="center">
				<a href="calendar.php?year=<? echo $year?>&amp;month=<? echo $month?>"><? echo $lang_calendar['Back']?></a>
			</td>
			<td align="left">
				<a href=?t=<? echo $type?>&amp;year=<? if($month == 12){echo $year+1;}else{echo $year;}?>&amp;month=<? if($day == $day_in_mth){if($month==12){echo'1';}else{echo $month+1;}}else{echo $month;}?>&amp;day=<? if($day == $day_in_mth){echo '1';}else{echo $day+1;}?>><? echo $lang_calendar['Next Day']?></a><br>
				<a href=?t=<? echo $type?>&amp;year=<? if($month == 12){echo $year+1;}else{echo $year;}?>&amp;month=<? if($month == 12){echo '1';}else{echo $month+1;}?>&amp;day=<? echo $day?>><? echo $lang_calendar['Next Month']?></a><br>
				<a href=?t=<? echo $type?>&amp;year=<? echo $year+1?>&amp;month=<? echo $month?>&amp;day=<? echo $day?>><? echo $lang_calendar['Next Year']?></a>
			</td>
		</tr>
	</tbody>
	</table>
	</div>
<?
}

/**
 * Enter description here...
 *
 * @param unknown_type $month
 * @param unknown_type $year
 * @param unknown_type $place

 */
function mini_cal($month, $year, $place)
{
	global $db, $forum_user, $configuration, $lang_calendar, $type;
		for($X=($month-1); $X<=($month+1); $X++)
		{
			$day_in_mth = date("t", mktime(0, 0, 0, $X, 1, $year)) ;
			$monthtext = date("F", mktime(0, 0, 0, $X, 1, $year));
			$day_text = date("D", mktime(0, 0, 0, $X, 1, $year));
			$day_of_wk = start_date(date("w", mktime(0, 0, 0, $X, 1, $year)));
			$month_start = mktime(0,0,0,$X,1,$year);
			$month_end = mktime(23,59,59,$X,$day_in_mth,$year);
			if($X != $month)
			{
?>
				<h2><span><a href="calendar.php?date=<?php echo $year?>.<?php echo $X?>"><?php echo $lang_calendar[$monthtext]?></a></span></h2>
				<div class="box">
				<table cellspacing="0">
				<thead>
					<tr>
<?
				if($place == 'main')
				{
					if($configuration['cal_start_day'] == "S")echo "\t\t\t\t\t\t<th>".$lang_calendar['Sun']."</th>\n";
					echo "\t\t\t\t\t\t<th>".$lang_calendar['Mon']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['Tue']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['Wed']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['Thu']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['Fri']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['Sat']."</th>\n";
					if($configuration['cal_start_day'] == "M")echo "\t\t\t\t\t\t<th>".$lang_calendar['Sun']."</th>\n";
				}
				elseif($place == 'week')
				{
					if($configuration['cal_start_day'] == "S")echo "\t\t\t\t\t\t<th>".$lang_calendar['S']."</th>\n";
					echo "\t\t\t\t\t\t<th>".$lang_calendar['M']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['T']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['W']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['T']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['F']."</th>\n\t\t\t\t\t\t<th>".$lang_calendar['S']."</th>\n";
					if($configuration['cal_start_day'] == "M")echo "\t\t\t\t\t\t<th>".$lang_calendar['S']."</th>\n";
				}
?>
					</tr>
				</thead>
				<tbody>
					<tr>
<?
				if ($day_of_wk <> 0){for ($i=0; $i<$day_of_wk; $i++){ echo "\t\t\t\t\t\t<td class='calendar_no'>&nbsp;</td>\n"; }}
				for ($date_of_mth = 1; $date_of_mth <= $day_in_mth; $date_of_mth++)
				{
					$date_no = date("j", mktime(0, 0, 0, $X, $date_of_mth, $year));
					$day_of_wk = start_date(date("w", mktime(0, 0, 0, $X, $date_of_mth, $year)));
					$class = ($date_no ==  date("j") &&  $X == date("n") && $year == date("Y"))? " class='calendar_day'" :'';
					echo "\t\t\t\t\t\t<td".$class." class=\"calendar_norm\">".$date_no."</td>\n";
					if ( $day_of_wk == 6 ) echo "\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n";
					if ( $day_of_wk < 6 && $date_of_mth == $day_in_mth )
					{
						for ( $i = $day_of_wk ; $i < 6; $i++ ) echo "\t\t\t\t\t\t<td class='calendar_no'>&nbsp;</td>\n";
					}
				}
?>
					</tr>
				</tbody>
				</table>
			</div>
<?
			}
			else
			{
				if($place == 'main') echo "\t\t</td><td valign='top' style='border:none;padding:0; margin:0'>\n";
				elseif($place == 'week') echo "\t\t<br />\n";
			}
		}
}