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
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, fp.image_upload, t.id AS tid, t.subject, t.posted, t.closed, t.icon_topic, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_post = $db->fetch_assoc($result);
$mods_array = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['tid'].' ORDER BY posted LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$topic_post_id = $db->result($result);
$can_edit_subject = ($id == $topic_post_id && (($forum_user['g_edit_subjects_interval'] == '0' || (time() - $cur_post['posted']) < $forum_user['g_edit_subjects_interval']) || $is_admmod)) ? true : false;
if (($forum_user['g_edit_posts'] == '0' || $cur_post['poster_id'] != $forum_user['id'] || $cur_post['closed'] == '1') && !$is_admmod) message($lang_common['No permission']);
$errors = array();
if (isset($_POST['form_sent']))
{
	if ($is_admmod) confirm_referrer('edit.php');
	if ($can_edit_subject)
	{
		$subject = forum_trim($_POST['req_subject']);
		if ($subject == '') $errors[] = $lang_post['No subject'];
		else if (forum_strlen($subject) > 70) $errors[] = $lang_post['Too long subject'];
		else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && $forum_user['g_id'] > USER_MOD) $subject = ucwords(strtolower($subject));
	}
	$message = forum_linebreaks(forum_trim($_POST['req_message']));
	if ($message == '') $errors[] = $lang_post['No message'];
	else if (strlen($message) > 65535) $errors[] = $lang_post['Too long message'];
	else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && $forum_user['g_id'] > USER_MOD) $message = ucwords(strtolower($message));
	if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require FORUM_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}
	$hide_smilies = isset($_POST['hide_smilies']) ? intval($_POST['hide_smilies']) : 0;
	if ($hide_smilies != '1') $hide_smilies = '0';
	if (empty($errors) && !isset($_POST['preview']))
	{
		$edited_sql = (!isset($_POST['silent']) || !$is_admmod) ? $edited_sql = ', edited='.time().', edited_by=\''.$db->escape($forum_user['username']).'\'' : '';
		require FORUM_ROOT.'include/search_idx.php';
		if ($can_edit_subject)
		{
			$icon_topic = $_POST['icon_topic'];
			$db->query('UPDATE '.$db->prefix.'topics SET subject=\''.$db->escape($subject).'\', icon_topic='.$icon_topic.' WHERE id='.$cur_post['tid'].' OR moved_to='.$cur_post['tid']) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
			update_search_index('edit', $id, $message, $subject);
		}
		else update_search_index('edit', $id, $message);
		$db->query('UPDATE '.$db->prefix.'posts SET message=\''.$db->escape($message).'\', hide_smilies=\''.$hide_smilies.'\''.$edited_sql.' WHERE id='.$id) or error('Unable to update post', __FILE__, __LINE__, $db->error());
		$upload_result = process_uploaded_images($id);
		process_deleted_images($id);
		$resultpost = $db->query('SELECT p.topic_id, t.question FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE p.id='.$id) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
		$cur_question = $db->fetch_assoc($resultpost);
		if ($cur_question['question'] != '') redirect(FORUM_ROOT.'view_poll.php?pid='.$id.'#p'.$id, $upload_result.$lang_post['Edit redirect']);
		else redirect(FORUM_ROOT.'view_topic.php?pid='.$id.'#p'.$id, $upload_result.$lang_post['Edit redirect']);
	}
}
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_post['Edit post'];
$required_fields = array('req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('edit', 'req_message');
require FORUM_ROOT.'header.php';
$cur_index = 1;
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<a href="view_forum.php?id=<?php echo $cur_post['fid'] ?>"><?php echo convert_htmlspecialchars($cur_post['forum_name']) ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo convert_htmlspecialchars($cur_post['subject']) ?></li></ul>
	</div>
</div>
<?php
if (!empty($errors))
{
?>
	<div id="posterror" class="block">
		<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
		<div class="box">
			<div class="inbox"
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
	$preview_message = parse_message($message, $hide_smilies);
?>
	<div id="postpreview" class="blockpost">
		<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<div class="postright">
					<div class="postmsg">
						<?php echo $preview_message."\n" ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
<div class="blockform">
	<h2><?php echo $lang_post['Edit post'] ?></h2>
	<div class="box">
		<form name="spelling_mod" id="edit" method="post" action="edit.php?id=<?php echo $id ?>&amp;action=edit" onsubmit="return submitForm(this)" enctype="multipart/form-data">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_post['Edit post legend'] ?></legend>
					<input type="hidden" name="form_sent" value="1" />
					<div class="infldset txtarea">
<?php 
	if ($can_edit_subject): 
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
            	$checked = ' ';
            	$aucune = ' ';
            	if($cur_post['icon_topic'] == $temp) $checked = ' checked="checked"';
            	if ($cur_post['icon_topic'] == '0') $aucune = ' checked="checked"';
			echo '<input type="radio" name="icon_topic" value="'.$temp.'"'.$checked.' />&nbsp;<img src="./img/general/icons/'.$temp.'.gif" alt="'.$temp.'" />&nbsp;';
            }
            echo '<input type="radio" name="icon_topic" value="0"'.$aucune.' />&nbsp;None';
        }
?>
						<label><?php echo $lang_common['Subject'] ?><br />
						<input class="textbox" type="text" name="req_subject" size="70" maxlength="70" tabindex="<?php echo $cur_index++ ?>" value="<?php echo convert_htmlspecialchars(isset($_POST['req_subject']) ? $_POST['req_subject'] : $cur_post['subject']) ?>" /><br /></label>
<?php endif; $bbcode_form = 'edit'; $bbcode_field = 'req_message'; require FORUM_ROOT.'include/modules/mod_bbcode.php'; ?>						<label><?php echo $lang_common['Message'] ?><br />
						<textarea name="req_message" class="post" rows="20" style="width:500px" tabindex="<?php echo $cur_index++ ?>"><?php echo convert_htmlspecialchars(isset($_POST['req_message']) ? $message : $cur_post['message']) ?></textarea><br /></label>
					</div>
				</fieldset>
<?php
$checkboxes = array();
if ($configuration['o_smilies'] == '1')
{
	if (isset($_POST['hide_smilies']) || $cur_post['hide_smilies'] == '1') $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" checked="checked" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Hide smilies'];
	else $checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Hide smilies'];
}
if ($is_admmod)
{
	if ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent'])) $checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" checked="checked" />&nbsp;'.$lang_post['Silent edit'];
	else $checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Silent edit'];
}
if (!empty($checkboxes))
{
?>
			</div>
			<div class="inform">
<?php
				show_image_upload($cur_post, true);
?>
				<fieldset>
					<legend><?php echo $lang_common['Options'] ?></legend>
					<div class="infldset">
						<div class="rbox">
							<?php echo implode('</label>'."\n\t\t\t\t\t\t\t", $checkboxes).'</label>'."\n" ?>
						</div>
					</div>
				</fieldset>
<?php
	}
?>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>" /><input type="submit" class="b1" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><input class="b1" type="submit" name="preview" onclick="ClearUploadSlots();" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /><input class="b1" type="button" value="<?php echo $lang_post['SpellCheck'] ?>" onclick="openspell();" /></p>
		</form>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>