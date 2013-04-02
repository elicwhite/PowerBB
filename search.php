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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/search.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
else if ($forum_user['g_search'] == '0') message($lang_search['No search permission']);
$multibyte = (isset($lang_common['lang_multibyte']) && $lang_common['lang_multibyte']) ? true : false;
if (isset($_GET['action']) || isset($_GET['search_id']))
{
	$action = (isset($_GET['action'])) ? $_GET['action'] : null;
	$forum = (isset($_GET['forum'])) ? intval($_GET['forum']) : -1;
	$sort_dir = (isset($_GET['sort_dir'])) ? (($_GET['sort_dir'] == 'DESC') ? 'DESC' : 'ASC') : 'DESC';
	if (isset($search_id)) unset($search_id);
	if (isset($_GET['search_id']))
	{
		$search_id = intval($_GET['search_id']);
		if ($search_id < 1) message($lang_common['Bad request']);
	}
	else if ($action == 'search')
	{
		$keywords = (isset($_GET['keywords'])) ? strtolower(trim($_GET['keywords'])) : null;
		$author = (isset($_GET['author'])) ? strtolower(trim($_GET['author'])) : null;
		if (preg_match('#^[\*%]+$#', $keywords) || strlen(str_replace(array('*', '%'), '', $keywords)) < 3) $keywords = '';
		if (preg_match('#^[\*%]+$#', $author) || strlen(str_replace(array('*', '%'), '', $author)) < 3) $author = '';
		if (!$keywords && !$author) message($lang_search['No terms']);
		if ($author) $author = str_replace('*', '%', $author);
		$show_as = (isset($_GET['show_as'])) ? $_GET['show_as'] : 'posts';
		$sort_by = (isset($_GET['sort_by'])) ? intval($_GET['sort_by']) : null;
		$search_in = (!isset($_GET['search_in']) || $_GET['search_in'] == 'all') ? 0 : (($_GET['search_in'] == 'message') ? 1 : -1);
	}
	else if ($action == 'show_user')
	{
		$user_id = intval($_GET['user_id']);
		if ($user_id < 2) message($lang_common['Bad request']);
	}
	else
	{
		if ($action != 'show_new' && $action != 'show_24h' && $action != 'show_unanswered' && $action != 'show_subscriptions') message($lang_common['Bad request']);
	}
	if (isset($search_id))
	{
		$ident = ($forum_user['is_guest']) ? get_remote_address() : $forum_user['username'];
		$result = $db->query('SELECT search_data FROM '.$db->prefix.'search_cache WHERE id='.$search_id.' AND ident=\''.$db->escape($ident).'\'') or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());
		if ($row = $db->fetch_assoc($result))
		{
			$temp = unserialize($row['search_data']);
			$search_results = $temp['search_results'];
			$num_hits = $temp['num_hits'];
			$sort_by = $temp['sort_by'];
			$sort_dir = $temp['sort_dir'];
			$show_as = $temp['show_as'];

			unset($temp);
		}
		else message($lang_search['No hits']);
	}
	else
	{
		$keyword_results = $author_results = array();
		$forum_sql = ($forum != -1) ? ' AND t.forum_id = '.$forum : '';
		if (!empty($author) || !empty($keywords))
		{
			if ($keywords)
			{
				$stopwords = (array)@file(FORUM_ROOT.'lang/'.$forum_user['language'].'/stopwords.txt');
				$stopwords = array_map('trim', $stopwords);
				if ($multibyte)
				{
					$keywords = trim(preg_replace('#\s+#', ' ', $keywords));
					$keywords_array = explode(' ', $keywords);
				}
				else
				{
					$noise_match = array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!', '¤');
					$noise_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ', ' ',  ' ', ' ', ' ');
					$keywords = str_replace($noise_match, $noise_replace, $keywords);
					$keywords = trim(preg_replace('#\s+#', ' ', $keywords));
					$keywords_array = explode(' ', $keywords);
					if (empty($keywords_array)) message($lang_search['No hits']);
					while (list($i, $word) = @each($keywords_array))
					{
						$num_chars = forum_strlen($word);
						if ($num_chars < 3 || $num_chars > 20 || in_array($word, $stopwords)) unset($keywords_array[$i]);
					}
					$search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';
				}
				$word_count = 0;
				$match_type = 'and';
				@reset($keywords_array);
				while (list($i, $cur_word) = @each($keywords_array))
				{
					switch ($cur_word)
					{
						case 'and':
						case 'or':
						case 'not':
							$match_type = $cur_word;
							break;
						default:
						{
							if ($multibyte)
							{
								$cur_word = $db->escape('%'.str_replace('*', '', $cur_word).'%');
								$cur_word_like = ($db_type == 'pgsql') ? 'ILIKE \''.$cur_word.'\'' : 'LIKE \''.$cur_word.'\'';
								if ($search_in > 0) $sql = 'SELECT id FROM '.$db->prefix.'posts WHERE message '.$cur_word_like;
								else if ($search_in < 0) $sql = 'SELECT p.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE t.subject '.$cur_word_like.' GROUP BY p.id, t.id';
								else $sql = 'SELECT p.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE p.message '.$cur_word_like.' OR t.subject '.$cur_word_like.' GROUP BY p.id, t.id';
							}
							else
							{
								$cur_word = str_replace('*', '%', $cur_word);
								$sql = 'SELECT m.post_id FROM '.$db->prefix.'search_words AS w INNER JOIN '.$db->prefix.'search_matches AS m ON m.word_id = w.id WHERE w.word LIKE \''.$cur_word.'\''.$search_in_cond;
							}
							$result = $db->query($sql, true) or error('Unable to search for posts', __FILE__, __LINE__, $db->error());
							$row = array();
							while ($temp = $db->fetch_row($result))
							{
								$row[$temp[0]] = 1;
								if (!$word_count) $result_list[$temp[0]] = 1;
								else if ($match_type == 'or') $result_list[$temp[0]] = 1;
								else if ($match_type == 'not') $result_list[$temp[0]] = 0;
							}
							if ($db->num_rows($result) > 0) unset($keywords_array[$i]);
							if ($match_type == 'and' && $word_count)
							{
								@reset($result_list);
								while (list($post_id,) = @each($result_list))
								{
									if (!isset($row[$post_id])) $result_list[$post_id] = 0;
								}
							}
							++$word_count;
							$db->free_result($result);
							break;
						}
					}
				}
				@reset($result_list);
				while (list($post_id, $matches) = @each($result_list))
				{
					if ($matches) $keyword_results[] = $post_id;
				}
				unset($result_list);
			}
			if ($author && strcasecmp($author, 'Guest') && strcasecmp($author, $lang_common['Guest']))
			{
				switch ($db_type)
				{
					case 'pgsql':
						$result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE username ILIKE \''.$db->escape($author).'\'') or error('Unable to fetch users', __FILE__, __LINE__, $db->error());
						break;
					default:
						$result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE username LIKE \''.$db->escape($author).'\'') or error('Unable to fetch users', __FILE__, __LINE__, $db->error());
						break;
				}
				if ($db->num_rows($result))
				{
					$user_ids = '';
					while ($row = $db->fetch_row($result)) $user_ids .= (($user_ids != '') ? ',' : '').$row[0];
					$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE poster_id IN('.$user_ids.')') or error('Unable to fetch matched posts list', __FILE__, __LINE__, $db->error());
					$search_ids = array();
					while ($row = $db->fetch_row($result)) $author_results[] = $row[0];
					$db->free_result($result);
				}
			}
			if ($author && $keywords)
			{
				$search_ids = array_intersect($keyword_results, $author_results);
				unset($keyword_results, $author_results);
			}
			else if ($keywords) $search_ids = $keyword_results;
			else $search_ids = $author_results;
			$num_hits = count($search_ids);
			if (!$num_hits) suggest( $keywords_array );
			if ($show_as == 'topics')
			{
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id IN('.implode(',', $search_ids).')'.$forum_sql.' GROUP BY t.id', true) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$search_ids = array();
				while ($row = $db->fetch_row($result)) $search_ids[] = $row[0];
				$db->free_result($result);
				$num_hits = count($search_ids);
			}
			else
			{
				$result = $db->query('SELECT p.id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id IN('.implode(',', $search_ids).')'.$forum_sql, true) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$search_ids = array();
				while ($row = $db->fetch_row($result)) $search_ids[] = $row[0];
				$db->free_result($result);
				$num_hits = count($search_ids);
			}
		}
		else if ($action == 'show_new' || $action == 'show_24h' || $action == 'show_user' || $action == 'show_subscriptions' || $action == 'show_unanswered')
		{
			if ($action == 'show_new')
			{
				if ($forum_user['is_guest']) message($lang_common['No permission']);
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.last_post>'.$forum_user['last_visit']) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$num_hits = $db->num_rows($result);
				if (!$num_hits) message($lang_search['No new posts']);
			}
			else if ($action == 'show_24h')
			{
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.last_post>'.(time() - 86400)) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$num_hits = $db->num_rows($result);
				if (!$num_hits) message($lang_search['No recent posts']);
			}
			else if ($action == 'show_user')
			{
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.poster_id='.$user_id.' GROUP BY t.id') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$num_hits = $db->num_rows($result);
				if (!$num_hits) message($lang_search['No user posts']);
			}
			else if ($action == 'show_subscriptions')
			{
				if ($forum_user['is_guest']) message($lang_common['Bad request']);
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$forum_user['id'].') INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1)') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$num_hits = $db->num_rows($result);
				if (!$num_hits) message($lang_search['No subscriptions']);
			}
			else
			{
				$result = $db->query('SELECT t.id FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.num_replies=0 AND t.moved_to IS NULL') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
				$num_hits = $db->num_rows($result);
				if (!$num_hits) message($lang_search['No unanswered']);
			}
			$sort_by = 4;
			$search_ids = array();
			while ($row = $db->fetch_row($result)) $search_ids[] = $row[0];
			$db->free_result($result);
			$show_as = 'topics';
		}
		else message($lang_common['Bad request']);
		$old_searches = array();
		$result = $db->query('SELECT ident FROM '.$db->prefix.'online') or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			while ($row = $db->fetch_row($result)) $old_searches[] = '\''.$db->escape($row[0]).'\'';
			$db->query('DELETE FROM '.$db->prefix.'search_cache WHERE ident NOT IN('.implode(',', $old_searches).')') or error('Unable to delete search results', __FILE__, __LINE__, $db->error());
		}
		$search_results = implode(',', $search_ids);
		$temp['search_results'] = $search_results;
		$temp['num_hits'] = $num_hits;
		$temp['sort_by'] = $sort_by;
		$temp['sort_dir'] = $sort_dir;
		$temp['show_as'] = $show_as;
		$temp = serialize($temp);
		$search_id = mt_rand(1, 2147483647);
		$ident = ($forum_user['is_guest']) ? get_remote_address() : $forum_user['username'];
		$db->query('INSERT INTO '.$db->prefix.'search_cache (id, ident, search_data) VALUES('.$search_id.', \''.$db->escape($ident).'\', \''.$db->escape($temp).'\')') or error('Unable to insert search results', __FILE__, __LINE__, $db->error());
		if ($action != 'show_new' && $action != 'show_24h')
		{
			$db->end_transaction();
			$db->close();
			header('Location: search.php?search_id='.$search_id);
			exit;
		}
	}
	if ($search_results != '')
	{
		$group_by_sql = '';
		switch ($sort_by)
		{
			case 1:
				$sort_by_sql = ($show_as == 'topics') ? 't.poster' : 'p.poster';
				break;
			case 2:
				$sort_by_sql = 't.subject';
				break;
			case 3:
				$sort_by_sql = 't.forum_id';
				break;
			case 4:
				$sort_by_sql = 't.last_post';
				break;
			default:
			{
				$sort_by_sql = ($show_as == 'topics') ? 't.posted' : 'p.posted';
				if ($show_as == 'topics') $group_by_sql = ', t.posted';
				break;
			}
		}
		if ($show_as == 'posts')
		{
			$substr_sql = ($db_type != 'sqlite') ? 'SUBSTRING' : 'SUBSTR';
			$sql = 'SELECT p.id AS pid, p.poster AS pposter, p.posted AS pposted, p.poster_id, '.$substr_sql.'(p.message, 1, 1000) AS message, t.id AS tid, t.poster, t.subject, t.question t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE p.id IN('.$search_results.') ORDER BY '.$sort_by_sql;
		}
		else $sql = 'SELECT t.id AS tid, t.poster, t.subject, t.question, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE t.id IN('.$search_results.') GROUP BY t.id, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.forum_id'.$group_by_sql.' ORDER BY '.$sort_by_sql;
		$per_page = ($show_as == 'posts') ? $forum_user['disp_posts'] : $forum_user['disp_topics'];
		$num_pages = ceil($num_hits / $per_page);
		$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
		$start_from = $per_page * ($p - 1);
		$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'search.php?search_id='.$search_id);
		$sql .= ' '.$sort_dir.' LIMIT '.$start_from.', '.$per_page;
		$result = $db->query($sql) or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());
		$search_set = array();
		while ($row = $db->fetch_assoc($result)) $search_set[] = $row;
		$db->free_result($result);
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_search['Search results'];
		require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink"><?php echo $paging_links ?></p>
	</div>
</div>
<?php
		$bg_switch = true;
		if ($show_as == 'topics')
		{
?>
<div id="vf" class="blocktable">
	<h2><span><?php echo $lang_search['Search results']; ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_common['Topic']; ?></th>
					<th class="tc2" scope="col"><?php echo $lang_common['Forum'] ?></th>
					<th class="tc3" scope="col"><?php echo $lang_common['Replies'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_common['Last post'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php
		}
		$result = $db->query('SELECT id, forum_name FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
		$forum_list = array();
		while ($forum_list[] = $db->fetch_row($result))
			;
		for ($i = 0; $i < count($search_set); ++$i)
		{
			@reset($forum_list);
			while (list(, $temp) = @each($forum_list))
			{
				if ($configuration['o_rewrite_urls'] == '1')
				{
					if ($temp[0] == $search_set[$i]['forum_id']) $forum = '<a href="'.makeurl("f", $temp[0], $temp[1]).'">'.convert_htmlspecialchars($temp[1]).'</a>';
				}
				else
				{
					if ($temp[0] == $search_set[$i]['forum_id']) $forum = '<a href="view_forum.php?id='.$temp[0].'">'.convert_htmlspecialchars($temp[1]).'</a>';
				}
			}
			if ($configuration['o_censoring'] == '1') $search_set[$i]['subject'] = censor_words($search_set[$i]['subject']);
			if ($show_as == 'posts')
			{
				$icon = '<div class="icon"><div class="nosize">'.$lang_common['Normal icon'].'</div></div>'."\n";
				if ($configuration['o_rewrite_urls'] == '1')
				{
					if ($search_set[$i]['question'] == "") $subject = '<a href="'.makeurl("f", $search_set[$i]['tid'], $search_set[$i]['subject']).'">'.convert_htmlspecialchars($search_set[$i]['subject']).'</a>';
					else $subject = '<a href="view_poll.php?id='.$search_set[$i]['tid'].'"><b>'.convert_htmlspecialchars($search_set[$i]['question']).'</b><br />'.convert_htmlspecialchars($search_set[$i]['subject']).'</a>';
				}
				else
				{
					if ($search_set[$i]['question'] == "") $subject = '<a href="view_topic.php?id='.$search_set[$i]['tid'].'">'.convert_htmlspecialchars($search_set[$i]['subject']).'</a>';
					else $subject = '<a href="view_poll.php?id='.$search_set[$i]['tid'].'"><b>'.convert_htmlspecialchars($search_set[$i]['question']).'</b><br />'.convert_htmlspecialchars($search_set[$i]['subject']).'</a>';
				}
				if (!$forum_user['is_guest'] && topic_is_new($search_set[$i]['tid'], $search_set[$i]['forum_id'],  $search_set[$i]['last_post'])) $icon = '<div class="icon inew"><div class="nosize">'.$lang_common['New icon'].'</div></div>'."\n";
				if ($configuration['o_censoring'] == '1') $search_set[$i]['message'] = censor_words($search_set[$i]['message']);
				$message = str_replace("\n", '<br />', convert_htmlspecialchars($search_set[$i]['message']));
				$pposter = convert_htmlspecialchars($search_set[$i]['pposter']);
				if ($search_set[$i]['poster_id'] > 1) $pposter = '<strong><a href="profile.php?id='.$search_set[$i]['poster_id'].'">'.$pposter.'</a></strong>';
				if (forum_strlen($message) >= 1000) $message .= ' &hellip;';
				$vtpost1 = ($i == 0) ? ' vtp1' : '';
				$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
				$vtbg = ($bg_switch) ? ' rowodd' : ' roweven';
?>
<div class="blockpost searchposts<?php echo $vtbg ?>">
<?php
if ($configuration['o_rewrite_urls'] == '1')
{
	?><h2><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $subject ?>&nbsp;&raquo;&nbsp;<a href="<?php echo makeurl("p", $search_set[$i]['pid'], format_time($search_set[$i]['pposted'])).'#p'.$search_set[$i]['pid'] ?>"><?php echo format_time($search_set[$i]['pposted']) ?></a></h2><?php
}
else
{
	?><h2><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $subject ?>&nbsp;&raquo;&nbsp;<a href="view_topic.php?pid=<?php echo $search_set[$i]['pid'].'#p'.$search_set[$i]['pid'] ?>"><?php echo format_time($search_set[$i]['pposted']) ?></a></h2><?php
}
?>
	<div class="box">
		<div class="inbox">
			<div class="postleft">
				<dl>
					<dt><?php echo $pposter ?></dt>
					<dd>Replies: <?php echo $search_set[$i]['num_replies'] ?></dd>
					<dd><?php echo $icon; ?></dd>
					<dd><p class="clearb"><a href="view_topic.php?pid=<?php echo $search_set[$i]['pid'].'#p'.$search_set[$i]['pid'] ?>"><?php echo $lang_search['Go to post'] ?></a></p></dd>
				</dl>
			</div>
			<div class="postright">
				<div class="postmsg">
					<p><?php echo $message ?></p>
				</div>
			</div>
			<div class="clearer"></div>
		</div>
	</div>
</div>
<?php
			}
			else
			{
				$icon = '<div class="icon"><div class="nosize">'.$lang_common['Normal icon'].'</div></div>'."\n";
				$icon_text = $lang_common['Normal icon'];
				$item_status = '';
				$icon_type = 'icon';
				if ($configuration['o_rewrite_urls'] == '1')
				{
					if ($search_set[$i]['question'] == "") $subject = '<a href="'.makeurl("t", $search_set[$i]['tid'], $search_set[$i]['subject']).'">'.convert_htmlspecialchars($search_set[$i]['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['poster']).'</span>';
					else $subject = '<a href="view_poll.php?id='.$search_set[$i]['tid'].'">'.convert_htmlspecialchars($search_set[$i]['question']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['poster']).'</span>';
				}
				else
				{
					if ($search_set[$i]['question'] == "") $subject = '<a href="view_topic.php?id='.$search_set[$i]['tid'].'">'.convert_htmlspecialchars($search_set[$i]['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['poster']).'</span>';
					else $subject = '<a href="view_poll.php?id='.$search_set[$i]['tid'].'">'.convert_htmlspecialchars($search_set[$i]['question']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['poster']).'</span>';
				}
				if ($search_set[$i]['closed'] != '0')
				{
					$icon_text = $lang_common['Closed icon'];
					$item_status = 'iclosed';
				}
				if (!$forum_user['is_guest'] && $search_set[$i]['last_post'] > $forum_user['last_visit'])
				{
					$icon_text .= ' '.$lang_common['New icon'];
					$item_status .= ' inew';
					$icon_type = 'icon inew';
					$subject = '<strong>'.$subject.'</strong>';
					if ($search_set[$i]['question'] == "") $subject_new_posts = '<span class="newtext">[&nbsp;<a href="view_topic.php?id='.$search_set[$i]['tid'].'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a>&nbsp;]</span>';
					else $subject_new_posts = '<span class="newtext">[&nbsp;<a href="view_poll.php?id='.$search_set[$i]['tid'].'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a>&nbsp;]</span>';
				}
				else $subject_new_posts = null;
				$num_pages_topic = ceil(($search_set[$i]['num_replies'] + 1) / $forum_user['disp_posts']);
				if ($num_pages_topic > 1)
				{
					if ($search_set[$i]['question'] == "") $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_topic.php?id='.$search_set[$i]['tid']).' ]';
					else $subject_multipage = '[ '.paginate($num_pages_topic, -1, 'view_poll.php?id='.$search_set[$i]['tid']).' ]';
				}
				else $subject_multipage = null;
				if (!empty($subject_new_posts) || !empty($subject_multipage))
				{
					$subject .= '&nbsp; '.(!empty($subject_new_posts) ? $subject_new_posts : '');
					$subject .= !empty($subject_multipage) ? ' '.$subject_multipage : '';
				}
?>
				<tr<?php if ($item_status != '') echo ' class="'.trim($item_status).'"'; ?>>
					<td class="tcl">
						<div class="intd">
							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo trim($icon_text) ?></div></div>
							<div class="tclcon">
								<?php echo $subject."\n" ?>
							</div>
						</div>
					</td>
					<td class="tc2"><?php echo $forum ?></td>
					<td class="tc3"><?php echo $search_set[$i]['num_replies'] ?></td>
					<?php
					if ($configuration['o_rewrite_urls'] == '1')
					{
						if ($search_set[$i]['question'] == "")
						{
							?><td class="tcr"><?php echo '<a href="'.makeurl("p", $search_set[$i]['last_post_id'], format_time($search_set[$i]['last_post'])).'#p'.$search_set[$i]['last_post_id'].'">'.format_time($search_set[$i]['last_post']).'</a> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['last_poster']) ?></td><?php
						}
						else
						{
							?><td class="tcr"><?php echo '<a href="view_poll.php?pid='.$search_set[$i]['last_post_id'].'#p'.$search_set[$i]['last_post_id'].'">'.format_time($search_set[$i]['last_post']).'</a> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['last_poster']) ?></td><?php
						}
					}
					else
					{
						if ($search_set[$i]['question'] == "")
						{
							?><td class="tcr"><?php echo '<a href="view_topic.php?pid='.$search_set[$i]['last_post_id'].'#p'.$search_set[$i]['last_post_id'].'">'.format_time($search_set[$i]['last_post']).'</a> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['last_poster']) ?></td><?php
						}
						else
						{
							?><td class="tcr"><?php echo '<a href="view_poll.php?pid='.$search_set[$i]['last_post_id'].'#p'.$search_set[$i]['last_post_id'].'">'.format_time($search_set[$i]['last_post']).'</a> '.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($search_set[$i]['last_poster']) ?></td><?php
						}
					}
					?>
				</tr>
<?php
			}
		}
		if ($show_as == 'topics') echo "\t\t\t".'</tbody>'."\n\t\t\t".'</table>'."\n\t\t".'</div>'."\n\t".'</div>'."\n".'</div>'."\n\n";
?>
<div class="<?php echo ($show_as == 'topics') ? 'linksb' : 'postlinksb'; ?>">
	<div class="inbox">
		<p class="pagelink"><?php echo $paging_links ?></p>
	</div>
</div>
<?php
		$footer_style = 'search';
		require FORUM_ROOT.'footer.php';
	}
	else message($lang_search['No hits']);
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_search['Search'];
$focus_element = array('search', 'keywords');
require FORUM_ROOT.'header.php';
?>
<div id="searchform" class="blockform">
	<h2><span><?php echo $lang_search['Search'] ?></span></h2>
	<div class="box">
		<form id="search" method="get" action="search.php">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_search['Search criteria legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="action" value="search" />
						<label class="conl"><?php echo $lang_search['Keyword search'] ?><br /><input type="text" class="textbox" name="keywords" size="40" maxlength="100" /><br /></label>
						<label class="conl"><?php echo $lang_search['Author search'] ?><br /><input id="author" type="text" class="textbox" name="author" size="25" maxlength="25" /><br /></label>
						<p class="clearb"><?php echo $lang_search['Search info'] ?></p>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_search['Search in legend'] ?></legend>
					<div class="infldset">
						<label class="conl"><?php echo $lang_search['Forum search'] ?>
						<br /><select id="forum" name="forum">
<?php
if ($configuration['o_search_all_forums'] == '1' || $forum_user['g_id'] < USER_GUEST) echo "\t\t\t\t\t\t\t".'<option value="-1">'.$lang_search['All forums'].'</option>'."\n";
$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url, f.parent_forum_id FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
$cur_category = 0;
while ($cur_forum = $db->fetch_assoc($result))
{
	if ($cur_forum['cid'] != $cur_category)
	{
		if ($cur_category) echo "\t\t\t\t\t\t\t".'</optgroup>'."\n";
		echo "\t\t\t\t\t\t\t".'<optgroup label="'.convert_htmlspecialchars($cur_forum['cat_name']).'">'."\n";
		$cur_category = $cur_forum['cid'];
	}
	if ($cur_forum['parent_forum_id'] == 0)
	{
		echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_forum['fid'].'">'.convert_htmlspecialchars($cur_forum['forum_name']).'</option>'."\n";
	}
	else
	{
		echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_forum['fid'].'">&nbsp;&nbsp;&nbsp;'.convert_htmlspecialchars($cur_forum['forum_name']).'</option>'."\n";
	}
}
?>
							</optgroup>
						</select>
						<br /></label>
						<label class="conl"><?php echo $lang_search['Search in'] ?>
						<br /><select id="search_in" name="search_in">
							<option value="all"><?php echo $lang_search['Message and subject'] ?></option>
							<option value="message"><?php echo $lang_search['Message only'] ?></option>
							<option value="topic"><?php echo $lang_search['Topic only'] ?></option>
						</select>
						<br /></label>
						<p class="clearb"><?php echo $lang_search['Search in info'] ?></p>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_search['Search results legend'] ?></legend>
					<div class="infldset">
						<label class="conl"><?php echo $lang_search['Sort by'] ?>
						<br /><select name="sort_by">
							<option value="0"><?php echo $lang_search['Sort by post time'] ?></option>
							<option value="1"><?php echo $lang_search['Sort by author'] ?></option>
							<option value="2"><?php echo $lang_search['Sort by subject'] ?></option>
							<option value="3"><?php echo $lang_search['Sort by forum'] ?></option>
						</select>
						<br /></label>
						<label class="conl"><?php echo $lang_search['Sort order'] ?>
						<br /><select name="sort_dir">
							<option value="DESC"><?php echo $lang_search['Descending'] ?></option>
							<option value="ASC"><?php echo $lang_search['Ascending'] ?></option>
						</select>
						<br /></label>
						<label class="conl"><?php echo $lang_search['Show as'] ?>
						<br /><select name="show_as">
							<option value="topics"><?php echo $lang_search['Show as topics'] ?></option>
							<option value="posts"><?php echo $lang_search['Show as posts'] ?></option>
						</select>
						<br /></label>
						<p class="clearb"><?php echo $lang_search['Search results info'] ?></p>
					</div>
				</fieldset>
			</div>
			<p><input type="submit" class="b1" name="search" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php
require FORUM_ROOT.'footer.php';
?>
