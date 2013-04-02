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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/invitation.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/digests.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/profile.php';
$id = isset($_POST['id']) ? intval($_POST['id']) : $forum_user['id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : $forum_user['id'];
if ($id < 2) message($lang_common['No permission']);
if ($forum_user['id'] != $id)
{
	if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
	else if ($forum_user['g_id'] == USER_MOD)
	{
		$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		if ($configuration['p_mod_edit_users'] == '0' || $db->result($result) < USER_GUEST) message($lang_common['No permission']);
	}
}
$result = $db->query('SELECT u.username, u.id, u.realname, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$user = $db->fetch_assoc($result);
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
		$sql = 'SELECT count(*) AS count FROM ' . $db->prefix.'digest_subscriptions' . ' WHERE user_id = ' . $user['id'];
		$result = $db->query($sql) or error('Could not get count from table '. $db->prefix.'digest_subscriptions' . '.', __FILE__, __LINE__, $db->error());
		$row['count'] = 0;
   		$row = $db->fetch_assoc($result);
   		$create_new = ($row['count'] == 0) ? true: false;
		if ($create_new)
		{
			$digest_type = 'NONE';
			$show_text = 'YES';
			$show_mine = 'YES';
			$new_only = 'TRUE';
			$send_on_no_messages = 'NO';
			$text_length = '300';
		}
		else 
		{
			$sql = 'SELECT digest_type, show_text, show_mine, new_only, send_on_no_messages, text_length FROM ' . $db->prefix.'digest_subscriptions' . ' WHERE user_id = ' . $user['id'];
			$result = $db->query($sql) or error('Could not get count from table '. $db->prefix.'digest_subscriptions' . '.', __FILE__, __LINE__, $db->error());
   			$row = $db->fetch_assoc($result);
			$digest_type           = $row['digest_type'];
			$show_text             = $row['show_text'];
			$show_mine             = $row['show_mine'];
			$new_only              = $row['new_only'];
			$send_on_no_messages   = $row['send_on_no_messages'];
			$text_length           = $row['text_length'];
		}  
		$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
		require FORUM_ROOT.'header.php';
		generate_profile_menu('digests');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']) ?> - <?php echo $lang_profile['Section digests'] ?></span></h2>
		<div class="box">
			<form id="digests" method="post" action="digests.php?id=<?php echo $id ?>">
				<div>
					<input type="hidden" name="form_sent" value="1" />
					<?php if ($create_new){ ?><input type="hidden" name="create_new" value="1" /><?php } ?>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_digests['Type of Digest Legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_digests['Type of Digest Info'] ?></p>
							<div class="rbox">
								<label><input type="radio" name="digest_type" value="NONE"<?php if ($digest_type == "NONE") echo ' checked="checked"' ?> /><?php echo $lang_digests['None'] ?><br /></label>
								<label><input type="radio" name="digest_type" value="DAY"<?php if ($digest_type == "DAY") echo ' checked="checked"' ?> /><?php echo $lang_digests['Daily'] ?><br /></label>
								<label><input type="radio" name="digest_type" value="WEEK"<?php if ($digest_type == "WEEK") echo ' checked="checked"' ?> /><?php echo $lang_digests['Weekly'] ?><br /></label>

							</div>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_digests['Options Legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_digests['Options Info'] ?></p>
							<div class="rbox">
								<label><input type="checkbox" name="show_text" value="YES"<?php if ($show_text == "YES") echo ' checked="checked"' ?> /><?php echo $lang_digests['Show Excerpt'] ?><br /></label>
								<label><input type="checkbox" name="show_mine" value="YES"<?php if ($show_mine == "YES") echo ' checked="checked"' ?> /><?php echo $lang_digests['Show Mine'] ?><br /></label>
								<label><input type="checkbox" name="new_only" value="TRUE"<?php if ($new_only == "TRUE") echo ' checked="checked"' ?> /><?php echo $lang_digests['New Only'] ?><br /></label>
								<label><input type="checkbox" name="send_on_no_messages" value="YES"<?php if ($send_on_no_messages == 'YES') echo ' checked="checked"' ?> /><?php echo $lang_digests['Send On No Messages'] ?><br /></label>
							</div>
							<br />
							<br />
							<p><?php echo $lang_digests['Text Length Info'] ?></p>
							<div class="rbox">
								<label><input type="radio" name="text_length" value="50"<?php if ($text_length == "50") echo ' checked="checked"' ?> /><?php echo $lang_digests['length_50'] ?><br /></label>
								<label><input type="radio" name="text_length" value="150"<?php if ($text_length == "150") echo ' checked="checked"' ?> /><?php echo $lang_digests['length_150'] ?><br /></label>
								<label><input type="radio" name="text_length" value="300"<?php if ($text_length == "300") echo ' checked="checked"' ?> /><?php echo $lang_digests['length_300'] ?><br /></label>
								<label><input type="radio" name="text_length" value="600"<?php if ($text_length == "600") echo ' checked="checked"' ?> /><?php echo $lang_digests['length_600'] ?><br /></label>
								<label><input type="radio" name="text_length" value="32000"<?php if ($text_length == "32000") echo ' checked="checked"' ?> /><?php echo $lang_digests['length_max'] ?><br /></label>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_digests['Forums Legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_digests['Forums Info'] ?></p>
							<div class="rbox">
<?php
		$sql = 'SELECT count(*) AS count FROM ' . $db->prefix.'digest_subscribed_forums' . ' WHERE user_id = ' . $user['id'];
		$result = $db->query($sql) or error('Could not get count from table '. $db->prefix.'digest_subscribed_forums' . '.', __FILE__, __LINE__, $db->error());
		$row = $db->fetch_assoc($result);
		$no_current_forums = ($row['count'] == 0) ? true : false;
		$sql = "SELECT c.id AS cid, c.cat_name, f.id AS forum_id, f.forum_name, c.disp_position as cat_order, f.disp_position as forum_order FROM ".$db->prefix."categories AS c INNER JOIN ".$db->prefix."forums AS f ON c.id=f.cat_id LEFT  JOIN ".$db->prefix."forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=".$user['g_id'].") WHERE	(fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position";
		$result = $db->query($sql) or error('Could not query forum information', __FILE__, __LINE__, $db->error());
		$i = 0;
		while ($row = $db->fetch_assoc($result)) 
		{ 
			$forum_ids [$i] = $row['forum_id'];
			$forum_names [$i] = $row['forum_name'];
			$cat_names [$i] = $row['cat_name'];
			$cat_orders [$i] = $row['cat_order'];
			$forum_orders [$i] = $row['forum_order'];
			$i++;
		}
		$i--;
		array_multisort($cat_orders, SORT_ASC, $cat_names, SORT_ASC, $forum_orders, SORT_ASC, $forum_ids, SORT_ASC, $forum_names, SORT_ASC);
		for ($j=0; $j<=$i; $j++) 
		{
			if (!(($j>0) && ($cat_orders[$j] == $cat_orders[$j-1]) && ($forum_orders[$j] == $forum_orders[$j-1]))) 
			{
				if (!($no_current_forums)) 
				{
					$sql = 'SELECT count(*) AS count FROM ' . $db->prefix.'digest_subscribed_forums' . ' WHERE forum_id = ' . $forum_ids[$j] . ' AND user_id = ' . $user['id'];
					$result = $db->query($sql) or error('Could not get count from table '. $db->prefix.'digest_subscribed_forums' . '', __FILE__, __LINE__, $db->error());
					$row = $db->fetch_assoc($result);
					if ($row['count'] == 0)
					{
						$forum_checked = false;
					}
					else
					{
						$forum_checked = true;
					}
				}
				else  
				{
					$forum_checked = false;
				}
			}
?>
								<label><input type="checkbox" name="forum_<?php echo $forum_ids[$j] ?>" value="1"<?php if ($forum_checked == true) echo ' checked="checked"' ?> /><?php echo convert_htmlspecialchars($cat_names[$j])." &nbsp;&raquo;&nbsp; ".convert_htmlspecialchars($forum_names[$j]) ?><br /></label>
<?php
		}
?>
							</div>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" />  <?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
}
else
{
	$live_subscriptions=0;
	foreach ($_POST as $key => $value) 
	{
		if (substr($key, 0, 6) == 'forum_') 
		{
			if ($value == 1) $live_subscriptions++;
		}
	}
	if ($_POST['digest_type'] == 'NONE' || $live_subscriptions < 1)
	{
		$sql = 'SELECT count(*) as count FROM ' . $db->prefix.'digest_subscriptions' . ' WHERE user_id = ' . $user['id'];
		$result = $db->query($sql) or error('Could not get count from table ' . $db->prefix.'digest_subscriptions' . '', __FILE__, __LINE__, $db->error());
		$row = $db->fetch_assoc($result);
		if($row['count'] != 0)
		{
			$sql = 'DELETE FROM ' . $db->prefix.'digest_subscribed_forums' . ' WHERE user_id = ' . $user['id'];
			$result = $db->query($sql) or error('Could not delete from table ' . $db->prefix.'digest_subscribed_forums' . '', __FILE__, __LINE__, $db->error());
			$sql = 'DELETE FROM ' . $db->prefix.'digest_subscriptions' . ' WHERE user_id = ' . $user['id'];
			$result = $db->query($sql) or error('Could not delete from table ' . $db->prefix.'digest_subscribed_forums' . '', __FILE__, __LINE__, $db->error());
		}
		$update_type = 'unsubscribe';
	}
	else 
	{
		$_POST['show_text']           = isset($_POST['show_text'])           ? $_POST['show_text']           : "NO";
		$_POST['show_mine']           = isset($_POST['show_mine'])           ? $_POST['show_mine']           : "NO";
		$_POST['new_only']            = isset($_POST['new_only'])            ? $_POST['new_only']            : "FALSE";
		$_POST['send_on_no_messages'] = isset($_POST['send_on_no_messages']) ? $_POST['send_on_no_messages'] : "NO";
		if (isset($_POST['create_new']))
		{
			$sql = 'INSERT INTO ' . $db->prefix.'digest_subscriptions' . ' (user_id, digest_type, show_text, show_mine, new_only, send_on_no_messages, text_length) VALUES (' .
				intval($user['id']) . ', ' .
				"'" . htmlspecialchars($_POST['digest_type']) . "', " .
				"'" . htmlspecialchars($_POST['show_text']) . "', " .
				"'" . htmlspecialchars($_POST['show_mine']) . "', " .
				"'" . htmlspecialchars($_POST['new_only']) . "', " .
				"'" . htmlspecialchars($_POST['send_on_no_messages']) . "', " .
				intval($_POST['text_length']). ')';
			$result = $db->query($sql) or error('Could not insert into table ' . $db->prefix.'digest_subscriptions' . '', __FILE__, __LINE__, $db->error());
			$update_type = 'create';
		}
		else
		{
			$sql = 'UPDATE ' . $db->prefix.'digest_subscriptions' . ' SET ' .
				"digest_type = '" . htmlspecialchars($_POST['digest_type']) . "', " .
				"show_text = '" . htmlspecialchars($_POST['show_text']) . "', " .
				"show_mine = '" . htmlspecialchars($_POST['show_mine']) . "', " .
				"new_only = '" . htmlspecialchars($_POST['new_only']) . "', " .
				"send_on_no_messages = '" . htmlspecialchars($_POST['send_on_no_messages']) . "', " .
				'text_length = ' . intval($_POST['text_length']) . ' ' . 
				' WHERE user_id = ' . intval($user['id']);
			$result = $db->query($sql) or error('Could not insert or update table ' . $db->prefix.'digest_subscriptions' . '', __FILE__, __LINE__, $db->error());
			$update_type = 'modify';
			$sql = 'DELETE FROM ' . $db->prefix.'digest_subscribed_forums' . ' WHERE user_id = ' . $user['id'];
			$db->query($sql) or error('Could not delete from table ' . $db->prefix.'digest_subscribed_forums' . '', __FILE__, __LINE__, $db->error());
		}
		foreach ($_POST as $key => $value) 
		{
			if (substr($key, 0, 6) == 'forum_') 
			{
				$sql = 'INSERT INTO ' . $db->prefix.'digest_subscribed_forums' . ' (user_id, forum_id) VALUES (' . $user['id'] . ', ' . htmlspecialchars(substr($key,6)) . ')';
				$result = $db->query($sql) or error('Could not insert into table ' . $db->prefix.'digest_subscribed_forums' . '', __FILE__, __LINE__, $db->error());
			}
		}
	}
	if ($update_type == 'unsubscribe')
	{
		redirect(FORUM_ROOT.'digests.php?id='.$id, $lang_digests['Unsubscribe Redirect']);		
	}
	else if ($update_type == 'create')
	{
		redirect(FORUM_ROOT.'digests.php?id='.$id, $lang_digests['Create Redirect']);		
	}
	else if ($update_type == 'modify')
	{
		redirect(FORUM_ROOT.'digests.php?id='.$id, $lang_digests['Update Redirect']);
	}
}
?>