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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/polls.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$ptype = isset($_POST['ptype']) ? intval($_POST['ptype']) : 0;
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
if ($tid < 1 && $fid < 1) message($lang_common['Bad request']);
if ($tid) $result = $db->query('SELECT f.id, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.subject, t.closed, t.question FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$tid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
else $result = $db->query('SELECT f.id, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics FROM ' . $db->prefix . 'forums AS f LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $forum_user['g_id'] . ') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id=' . $fid) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_posting = $db->fetch_assoc($result);
if ($cur_posting['redirect_url'] != '') message($lang_common['Bad request']);
$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
if ((($tid && (($cur_posting['post_replies'] == '' && $forum_user['g_post_replies'] == '0') || $cur_posting['post_replies'] == '0')) || ($fid && (($cur_posting['post_topics'] == '' && $forum_user['g_post_topics'] == '0') || $cur_posting['post_topics'] == '0')) || (isset($cur_posting['closed']) && $cur_posting['closed'] == '1')) && !$is_admmod) message($lang_common['No permission']);
$errors = array();
if (isset($_POST['form_sent']))
{
    if (($forum_user['is_guest'] && $_POST['form_user'] != 'Guest') || (!$forum_user['is_guest'] && $_POST['form_user'] != $forum_user['username'])) message($lang_common['Bad request']);
	if (!$forum_user['is_guest'] && !isset($_POST['preview']) && $forum_user['last_post'] != '' && (time() - $forum_user['last_post']) < $forum_user['g_post_flood']) $errors[] = $lang_post['Flood start'] . ' ' . $forum_user['g_post_flood'] . ' ' . $lang_post['flood end']; 
	if ($fid)
	{
		if (!empty($_POST['create_poll']))
		{
        	$subject = forum_trim($_POST['req_subject']);
        	if ($subject == '') $errors[] = $lang_post['No subject'];
        	else if (forum_strlen($subject) > 70) $errors[] = $lang_post['Too long subject'];
        	else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $subject = ucwords(strtolower($subject)); 
        	$question = forum_trim($_POST['req_question']);
        	if ($question == '') $errors[] = $lang_polls['No question'];
        	else if (forum_strlen($question) > 70) $errors[] = $lang_polls['Too long question'];
		else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($question) == $question && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $question = ucwords(strtolower($question)); 
        	if ($ptype == 3)
        	{
	            $yesval = forum_trim($_POST['poll_yes']);
            	if ($yesval == '') $errors[] = $lang_polls['No yes'];
            	else if (forum_strlen($yesval) > 35) $errors[] = $lang_polls['Too long yes'];
            	else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($yesval) == $yesval && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $yesval = ucwords(strtolower($yesval));
            	$noval = forum_trim($_POST['poll_no']);
            	if ($noval == '') $errors[] = $lang_polls['No no'];
            	else if (forum_strlen($noval) > 35) $errors[] = $lang_polls['Too long no'];
            	else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($noval) == $noval && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $noval = ucwords(strtolower($noval));
        	} 
        	$option = array();
        	$lastoption = "null";
        	while (list($key, $value) = each($_POST['poll_option']))
        	{
	    		$value = forum_trim($value);
            	if ($value != "")
            	{
	                if ($lastoption == '') $errors[] = $lang_polls['Empty option'];
                	else
                	{
	                    $option[$key] = forum_trim($value);
                    	if ($key > $configuration['o_poll_max_fields']) message($lang_common['Bad request']);
                    	else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($option[$key]) == $option[$key] && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $option[$key] = ucwords(strtolower($option[$key]));
                	} 
            	} 
            	$lastoption = forum_trim($value);
        	} 
	  		if (empty($option)) $errors[] = $lang_polls['No options'];
	  		if (!array_key_exists(2,$option)) $errors[] = $lang_polls['Low options'];
	 	}
	}
    if (!$forum_user['is_guest'])
	{
		$username = $forum_user['username'];
		$displayname = $forum_user['displayname'];
		$email = $forum_user['email'];
	} 
    else
    {
            $username = trim($_POST['req_username']);
            $email = strtolower(trim(($configuration['p_force_guest_email'] == '1') ? $_POST['req_email'] : $_POST['email'])); 
            if (strlen($username) < 2) $errors[] = $lang_prof_reg['Username too short'];
            else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) $errors[] = $lang_prof_reg['Username guest'];
            else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) $errors[] = $lang_prof_reg['Username IP'];
            if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) $errors[] = $lang_prof_reg['Username reserved chars'];
            if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) $errors[] = $lang_prof_reg['Username BBCode']; 
            $temp = censor_words($username);
            if ($temp != $username) $errors[] = $lang_register['Username censor']; 
            $result = $db->query('SELECT username FROM ' . $db->prefix . 'users WHERE username=\'' . $db->escape($username) . '\' OR username=\'' . $db->escape(preg_replace('/[^\w]/', '', $username)) . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
            if ($db->num_rows($result))
            {
                $busy = $db->result($result);
                $errors[] = $lang_register['Username dupe 1'] . ' ' . convert_htmlspecialchars($busy) . '. ' . $lang_register['Username dupe 2'];
            } 
            if ($configuration['p_force_guest_email'] == '1' || $email != '')
            {
                require FORUM_ROOT . 'include/email.php';
                if (!is_valid_email($email)) $errors[] = $lang_common['Invalid e-mail'];
            } 
        } 
        $message = forum_linebreaks(forum_trim($_POST['req_message'])); 
        if ($message == '') $errors[] = $lang_post['No message'];
        else if (strlen($message) > 65535) $errors[] = $lang_post['Too long message'];
        else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && ($forum_user['g_id'] > USER_MOD && !$forum_user['g_global_moderation'])) $message = ucwords(strtolower($message)); 
        if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
        {
            require FORUM_ROOT . 'include/parser.php';
            $message = preparse_bbcode($message, $errors);
        } 
        require FORUM_ROOT . 'include/search_idx.php';
        $hide_smilies = isset($_POST['hide_smilies']) ? 1 : 0;
        $subscribe = isset($_POST['subscribe']) ? 1 : 0;
        $now = time(); 
        if (empty($errors) && !isset($_POST['preview']))
        {
		if ($tid)
		{
			if (!$forum_user['is_guest'])
			{
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_id, poster_ip, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();

			}
			else
			{
				$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\''.$email.'\'' : 'NULL';
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_ip, poster_email, message, hide_smilies, posted, topic_id) VALUES(\''.$db->escape($username).'\', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', \''.$hide_smilies.'\', '.$now.', '.$tid.')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();
			}
			$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'posts WHERE topic_id='.$tid) or error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
			$num_replies = $db->result($result, 0) - 1;
			$db->query('UPDATE '.$db->prefix.'topics SET num_replies='.$num_replies.', last_post='.$now.', last_post_id='.$new_pid.', last_poster=\''.$db->escape($username).'\' WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
			update_search_index('post', $new_pid, $message);
			update_forum($cur_posting['id']);
			if ($configuration['o_subscriptions'] == '1')
			{
			$result = $db->query('SELECT posted FROM '.$db->prefix.'posts WHERE topic_id='.$tid.' ORDER BY id DESC LIMIT 1, 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
			$previous_post_time = $db->result($result);
			}
		}
		else if ($fid)
		{
			if ($cur_posting['valide'] == '1') $closed = '2';
			else $closed = '0';
			$icon_topic = $_POST['icon_topic'];
			if ($ptype == 3)
            	{
                		$db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, last_poster, closed, forum_id, icon_topic, question, yes, no) VALUES(\''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$closed.', '.$fid.', '.$icon_topic.', \''.$db->escape($question).'\', \''.$db->escape($yesval).'\', \''.$db->escape($noval).'\')') or error('Unable to create topic', __FILE__, __LINE__, $db->error());
			}
            	else
           		{
                		$db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, last_poster, closed, forum_id, icon_topic, question) VALUES(\''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$closed.', '.$fid.', '.$icon_topic.', \''.$db->escape($question).'\')') or error('Unable to create topic', __FILE__, __LINE__, $db->error());
            	} 
            	$new_tid = $db->insert_id();
            	$db->query('INSERT INTO ' . $db->prefix . 'polls (pollid, options, ptype) VALUES(' . $new_tid . ', \'' . $db->escape(serialize($option)) . '\', ' . $ptype . ')') or error('Unable to create poll', __FILE__, __LINE__, $db->error());
	    		if (!$forum_user['is_guest'])
	    		{
				$db->query('INSERT INTO ' . $db->prefix . 'posts (poster, poster_id, poster_ip, message, hide_smilies, posted, topic_id) VALUES(\'' . $db->escape($username) . '\', ' . $forum_user['id'] . ', \'' . get_remote_address() . '\', \'' . $db->escape($message) . '\', \'' . $hide_smilies . '\', ' . $now . ', ' . $new_tid . ')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
			}
			else
			{
				$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\'' . $email . '\'' : 'NULL';
				$db->query('INSERT INTO ' . $db->prefix . 'posts (poster, poster_ip, poster_email, message, hide_smilies, posted, topic_id) VALUES(\'' . $db->escape($username) . '\', \'' . get_remote_address() . '\', ' . $email_sql . ', \'' . $db->escape($message) . '\', \'' . $hide_smilies . '\', ' . $now . ', ' . $new_tid . ')') or error('Unable to create post', __FILE__, __LINE__, $db->error());
			} 
			$new_pid = $db->insert_id(); 
			$db->query('UPDATE ' . $db->prefix . 'topics SET last_post_id=' . $new_pid . ' WHERE id=' . $new_tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
			update_search_index('post', $new_pid, $message, $subject);
			update_forum($fid); 
		} 
		if (!$forum_user['is_guest'])
		{
			$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
			$db->query('UPDATE ' . $low_prio . $db->prefix . 'users SET num_posts=num_posts+1, last_post=' . $now . ' WHERE id=' . $forum_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
		} 
		redirect(FORUM_ROOT.'view_poll.php?pid=' . $new_pid . '#p' . $new_pid, $lang_post['Post redirect']);
	} 
} 
if ($tid)
{
	$action = $lang_post['Post a reply'];
	$form = '<form name="spelling_mod" id="post" method="post" action="poll.php?action=post&amp;tid='.$tid.'" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}"  enctype="multipart/form-data">';
	if (isset($_GET['qid']))
	{
		$qid = intval($_GET['qid']);
		if ($qid < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT p.poster, p.message, u.username, u.displayname FROM '.$db->prefix.'posts as p WHERE p.id='.$qid.' INNER JOIN '.$db->prefix.'users as u on u.username=p.poster') or error('Unable to fetch quote info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		list($q_poster, $q_message, $q_username, $q_displayname) = $db->fetch_row($result);
		$q_message = str_replace('[img]', '[url]', $q_message);
		$q_message = str_replace('[/img]', '[/url]', $q_message);
		$q_message = convert_htmlspecialchars($q_message);
		if ($q_displayname != "") $q_poster = $q_displayname;
		if ($configuration['p_message_bbcode'] == '1')
		{
			if (strpos($q_poster, '[') !== false || strpos($q_poster, ']') !== false)
			{
				if (strpos($q_poster, '\'') !== false) $q_poster = '"'.$q_poster.'"';
				else $q_poster = '\''.$q_poster.'\'';
			}
			else
			{
				$ends = substr($q_poster, 0, 1).substr($q_poster, -1, 1);
				if ($ends == '\'\'') $q_poster = '"'.$q_poster.'"';
				else if ($ends == '""') $q_poster = '\''.$q_poster.'\'';
			}
			$quote = '[quote='.$q_poster.']'.$q_message.'[/quote]'."\n";
		}
		else $quote = '> '.$q_poster.' '.$lang_common['wrote'].':'."\n\n".'> '.$q_message."\n";
	}
	$forum_name = '<a href="view_forum.php?id='.$cur_posting['id'].'">'.convert_htmlspecialchars($cur_posting['forum_name']).'</a>';
}
else if ($fid)
{
	$form = '<form name="spelling_mod" id="post" method="post" action="poll.php?action=post&amp;fid=' . $fid . '" onsubmit="return process_form(this)" enctype="multipart/form-data">';
	$action = $lang_polls['Create new poll'];
	$forum_name = convert_htmlspecialchars($cur_posting['forum_name']);
}
else message($lang_common['Bad request']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)" . ' / ' . $action;
$cur_index = 1; 
if ($fid)
{
if ($ptype == 0)
{
    $form = '<form id="post" method="post" action="poll.php?&amp;fid=' . $fid . '">';
    require FORUM_ROOT . 'header.php';
    ?>
<div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form . "\n" ?>
		<div class="inform">
				<fieldset>
					<legend><?php echo $lang_polls['Poll select'] ?></legend>
					<div class="infldset">
					<center><select tabindex="<?php echo $cur_index++ ?>" name="ptype">
					<option value="1"><?php echo $lang_polls['Regular'] ?>
					<option value="2"><?php echo $lang_polls['Multiselect'] ?>
					<option value="3"><?php echo $lang_polls['Yesno'] ?>
					</select></center>
					</div>
				</fieldset>
			</div>
			<p><center><input type="button" class="b1" onclick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>">&nbsp;<input type="submit" class="b1" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /></center></p>
		</form>
	</div>
</div>
<?php
}
elseif ($ptype == 1 || $ptype == 2 || $ptype == 3)
{
    $required_fields = array('req_email' => $lang_common['E-mail'], 'req_question' => $lang_polls['Question'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
    $focus_element = array('post');
    if (!$forum_user['is_guest']) $focus_element[] = 'req_question';
    else
    {
        $required_fields['req_username'] = $lang_post['Guest name'];
        $focus_element[] = 'req_question';
    } 
    require FORUM_ROOT . 'header.php';
    ?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php">
		<?php echo $lang_common['Index'] ?>
		</a></li><li>&nbsp;&raquo;&nbsp;
		<?php echo $forum_name ?>
		</li></ul>
	</div>
</div>
<?php 
    if (!empty($errors))
    {
        ?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php
        while (list(, $cur_error) = each($errors))
        echo "\t\t\t\t" . '<li><strong>' . $cur_error . '</strong></li>' . "\n";
        ?>
			</ul>
		</div>
	</div>
</div>
<?php
    }
    else if (isset($_POST['preview']))
    {
        require_once FORUM_ROOT . 'include/parser.php';
        $message = parse_message(trim($_POST['req_message']), $hide_smilies);
        ?>
		<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_polls['Poll preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postright">
				<div class="postmsg">
				<?php
        if ($ptype == 1)
        {
            ?><strong>
					<?php echo convert_htmlspecialchars($question);
            ?>
				</strong>	<br /><br />
					<form action="" method="POST">
					<?php
            while (list($key, $value) = each($option))
            {
                if (!empty($value))
                {
                    ?>
					<input type="radio"> <?php echo convert_htmlspecialchars($value);
                    ?> <br />
					<?php
                } 
            } 
            ?>
			</form>
			<?php
        }
        elseif ($ptype == 2)
        {
            ?><strong>
					<?php echo convert_htmlspecialchars($question);
            ?>
					</strong><br /><br />
					<form action="" method="POST">
					<?php
            while (list($key, $value) = each($option))
            {
                if (!empty($value))
                {
                    ?>
					<input type="checkbox"> <?php echo convert_htmlspecialchars($value);
                    ?> <br />
					<?php
                } 
            } 
            ?>
			</form>
			<?php
        }
        elseif ($ptype == 3)
        {
            ?><strong>
					<?php echo convert_htmlspecialchars($question);
            ?></strong>
					<br /><br />
					<form action="" method="POST">
					<?php
            while (list($key, $value) = each($option))
            {
                if (!empty($value))
                {
                    ?>
<strong>
					<?php echo convert_htmlspecialchars($value);
                    ?></strong><br /><input type="radio"> <?php echo convert_htmlspecialchars($yesval);
                    ?><input type="radio"> <?php echo convert_htmlspecialchars($noval);
                    ?><br />
					<?php
                } 
            } 
            ?>
			</form>
			<?php
        } 
        ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postright">
				<div class="postmsg">
					<?php echo $message . "\n" ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
    } 
    if ($ptype == 1)
    {
        ?>
	<div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form . "\n" ?>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_polls['New poll legend'] ?></legend>
				<div class="infldset">
				<input type="hidden" name="ptype" value="1" />
					<label><strong><?php echo $lang_polls['Question'] ?></strong><br /><input type="text" class="textbox" name="req_question" value="<?php if (isset($_POST['req_question'])) echo convert_htmlspecialchars($question);
        ?>" size="70" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        for ($x = 1; $x <= $configuration['o_poll_max_fields'] ;$x++) {
            ?>
						<label><strong><?php echo $lang_polls['Option'] ?></strong><br /> <input class="textbox" type="text" name="poll_option[<?php echo $x;
            ?>]" value="<?php if (isset($_POST['poll_option'][$x])) echo convert_htmlspecialchars($option[$x]);
            ?>" size="40" maxlength="55" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        } 
        ?> </div> </fieldset> </div> <?php 
    }
    elseif ($ptype == 2)
    {
        ?>
		<div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form . "\n" ?>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_polls['New poll legend multiselect'] ?></legend>
				<div class="infldset">
				<input type="hidden" name="ptype" value="2" />
					<label><strong><?php echo $lang_polls['Question'] ?></strong><br /><input type="text" class="textbox" name="req_question" value="<?php if (isset($_POST['req_question'])) echo convert_htmlspecialchars($question);
        ?>" size="70" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        for ($x = 1;
            $x <= $configuration['o_poll_max_fields']; $x++)
            {
            ?>
						<label><strong><?php echo $lang_polls['Option'] ?></strong><br /> <input class="textbox" type="text" name="poll_option[<?php echo $x;
            ?>]" value="<?php if (isset($_POST['poll_option'][$x])) echo convert_htmlspecialchars($option[$x]);
            ?>" size="40" maxlength="55" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        } 
        ?> </div> </fieldset> </div> <?php 
    }
    elseif ($ptype == 3)
    {
        ?>
		<div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form . "\n" ?>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_polls['New poll legend yesno'] ?></legend>
				<div class="infldset">
				<input type="hidden" name="ptype" value="3" />
					<label><strong><?php echo $lang_polls['Question'] ?></strong><br /><input type="text" class="textbox" name="req_question" value="<?php if (isset($_POST['req_question'])) echo convert_htmlspecialchars($question);
        ?>" size="70" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label><strong><?php echo $lang_polls['Yes'] ?></strong><br /> <input type="text" class="textbox" name="poll_yes" value="<?php if (isset($_POST['poll_yes'])) echo convert_htmlspecialchars($yesval);
        ?>" size="40" maxlength="35" tabindex="<?php echo $cur_index++ ?>" /></label>
						<label><strong><?php echo $lang_polls['No'] ?></strong><br /> <input type="text" class="textbox" name="poll_no" value="<?php if (isset($_POST['poll_no'])) echo convert_htmlspecialchars($noval);
        ?>" size="40" maxlength="35" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        for ($x = 1; $x <= $configuration['o_poll_max_fields']; $x++)
        {
            ?>
						<label><strong><?php echo $lang_polls['Option'] ?></strong><br /> <input class="textbox" type="text" name="poll_option[<?php echo $x;
            ?>]" value="<?php if (isset($_POST['poll_option'][$x])) echo convert_htmlspecialchars($option[$x]);
            ?>" size="40" maxlength="55" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<?php
        } 
        ?> </div> </fieldset> </div> <?php
    }
    else message($lang_common['Bad request']);
	}
	else message($lang_common['Bad request']);
}
else
{
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$action;
$required_fields = array('req_email' => $lang_common['E-mail'], 'req_question' => $lang_polls['Question'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('post');
if (!$forum_user['is_guest']) $focus_element[] = ($fid) ? 'req_subject' : 'req_message';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_username';
}
require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo $forum_name ?><?php if (isset($cur_posting['subject'])) echo '</li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_posting['subject']) ?></li></ul>
	</div>
</div>
<?php
if (!empty($errors))
{
?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php
	while (list(, $cur_error) = each($errors)) echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>
<?php
}
else if (isset($_POST['preview']))
{
	require_once FORUM_ROOT.'include/parser.php';
	$message = parse_message($message, $hide_smilies);
?>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postright">
				<div class="postmsg">
					<?php echo $message."\n" ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } 
}
?>
<?php
if (($tid) || ($ptype != 0))
{
    ?>
<?php if ($tid): 
$cur_index = 1; ?> <div class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form . "\n" ?> <?php endif; ?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Write message legend'] ?></legend>
					<div class="infldset txtarea">
					<?php if ($fid): ?>	<input type="hidden" name="create_poll" value="1" /> <?php endif; ?>
					<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['username']) : 'Guest';
    ?>" />
<?php
    if ($forum_user['is_guest'])
    {
        $email_label = ($configuration['p_force_guest_email'] == '1') ? '<strong>' . $lang_common['E-mail'] . '</strong>' : $lang_common['E-mail'];
        $email_form_name = ($configuration['p_force_guest_email'] == '1') ? 'req_email' : 'email';
        ?>						<label class="conl"><strong><?php echo $lang_post['Guest name'] ?></strong><br /><input type="text" name="req_username" class="textbox" value="<?php if (isset($_POST['req_username'])) echo convert_htmlspecialchars($username);
        ?>" size="25" maxlength="25" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label class="conl"><?php echo $email_label ?><br /><input type="text" class="textbox" name="<?php echo $email_form_name ?>" value="<?php if (isset($_POST[$email_form_name])) echo convert_htmlspecialchars($email);
        ?>" size="40" maxlength="50" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<div class="clearer"></div>
<?php
    } 
if ($fid):
	$icons_topic = array();
	$d = dir(FORUM_ROOT.'img/general/icons');
	while (($entry = $d->read()) !== false)
	{
		if (substr($entry, strlen($entry)-4) == '.gif')
            {    
			$icons_topic[] = substr($entry, 0, strlen($entry)-4);
            }    
	}
	$d->close();
	if (count($icons_topic) > 1)
	{
		echo 'Please select an icon for the topic.<br />';
		while (list(, $temp) = @each($icons_topic))
		{
			echo '<input type="radio" name="icon_topic" value="'.$temp.'" />&nbsp;<img src="./img/general/icons/'.$temp.'.gif" alt="'.$temp.'" />&nbsp;';
		}
		echo '<input type="radio" name="icon_topic" value="0" checked="checked" />&nbsp;None';
	}
?>
						<label><strong><?php echo $lang_common['Subject'] ?></strong><br /><input type="text" class="textbox" name="req_subject"  value="<?php if (isset($_POST['req_subject'])) echo convert_htmlspecialchars($subject); ?>" size="90" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
<?php endif; require FORUM_ROOT.'include/modules/mod_bbcode.php'; ?>
					  <label><strong><?php echo $lang_common['Message'] ?></strong><br />
						<textarea name="req_message" rows="20" style="width:500px" tabindex="<?php echo $cur_index++ ?>"><?php echo isset($_POST['req_message']) ? convert_htmlspecialchars(trim($_POST['req_message'])) : (isset($quote) ? $quote : ''); ?></textarea><br /></label>
					</div>
				</fieldset>
<?php
    $checkboxes = array();
    if (!$forum_user['is_guest'])
    {
        if ($configuration['o_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="' . ($cur_index++) . '"' . (isset($_POST['hide_smilies']) ? ' checked="checked"' : '') . ' />' . $lang_post['Hide smilies'];
        if ($configuration['o_subscriptions'] == '1') $checkboxes[] = '<label><input type="checkbox" name="subscribe" value="1" tabindex="' . ($cur_index++) . '"' . (isset($_POST['subscribe']) ? ' checked="checked"' : '') . ' />' . $lang_post['Subscribe'];
    }
    else if ($configuration['o_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="' . ($cur_index++) . '"' . (isset($_POST['hide_smilies']) ? ' checked="checked"' : '') . ' />' . $lang_post['Hide smilies'];
    if (!empty($checkboxes))
    {
        ?>
			</div>
			<div class="inform">
<?php
				show_image_upload($cur_posting);
?>
				<fieldset>
					<legend><?php echo $lang_common['Options'] ?></legend>
					<div class="infldset">
						<div class="rbox">
							<?php echo implode('<br /></label>' . "\n\t\t\t\t", $checkboxes) . '<br /></label>' . "\n" ?>
						</div>
					</div>
				</fieldset>
<?php
    } 
    ?>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>">&nbsp;<input type="submit" class="b1" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" />&nbsp;<input type="submit" class="b1" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /><input class="b1" type="button" value="<?php echo $lang_post['SpellCheck'] ?>" onclick="openspell();" /></p>
		</form>
	</div>
</div>
<?php
}
	require FORUM_ROOT . 'footer.php';
