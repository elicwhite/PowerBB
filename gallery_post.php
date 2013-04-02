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
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
if ($cid < 1) message($lang_common['Bad request']);
$result = $db->query('SELECT c.id, c.cat_name, c.moderators, gp.post_cat FROM '.$db->prefix.'gallery_cat AS c LEFT JOIN '.$db->prefix.'gallery_perms AS gp ON (gp.cat_id=c.id AND gp.group_id='.$forum_user['g_id'].') WHERE (gp.read_cat IS NULL OR gp.read_cat=1) AND c.id='.$cid) or error('Unable to fetch gallery info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_posting = $db->fetch_assoc($result);
$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($forum_user['g_id'] == USER_ADMIN || ($forum_user['g_id'] == USER_MOD && array_key_exists($forum_user['username'], $mods_array))) ? true : false;
if ((($cid && (($cur_posting['post_cat'] == '' && $forum_user['g_post_topics'] == '0') || $cur_posting['post_cat'] == '0'))) && !$is_admmod) message($lang_common['No permission']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/gallery.php';
$errors = array();

if (isset($_POST['form_sent']))
{
	if (($forum_user['is_guest'] && $_POST['form_user'] != 'Guest') || (!$forum_user['is_guest'] && $_POST['form_user'] != $forum_user['username'])) message($lang_common['Bad request']);
	if (!$forum_user['is_guest'] && $forum_user['last_post'] != '' && (time() - $forum_user['last_post']) < $forum_user['g_post_flood'])
		$errors[] = $lang_post['Flood start'].' '.$forum_user['g_post_flood'].' '.$lang_post['flood end'];
		$subject = forum_trim($_POST['req_subject']);
		if ($subject == '') $errors[] = $lang_gallery['Error No subject'];
		else if (forum_strlen($subject) > 70) $errors[] = $lang_gallery['Error Too long subject'];
		else if ($configuration['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && $forum_user['g_id'] > USER_MOD) $subject = ucwords(strtolower($subject));
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
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/prof_reg.php';
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/register.php';
		if (strlen($username) < 2) $errors[] = $lang_prof_reg['Username too short'];
		else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest'])) $errors[] = $lang_prof_reg['Username guest'];
		else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)) $errors[] = $lang_prof_reg['Username IP'];
		if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) $errors[] = $lang_prof_reg['Username reserved chars'];
		if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username)) $errors[] = $lang_prof_reg['Username BBCode'];
		$temp = censor_words($username);
		if ($temp != $username) $errors[] = $lang_register['Username censor'];
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\' OR username=\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
		{
			$busy = $db->result($result);
			$errors[] = $lang_register['Username dupe 1'].' '.convert_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2'];
		}

		if ($configuration['p_force_guest_email'] == '1' || $email != '')
		{
			require FORUM_ROOT.'include/email.php';
			if (!is_valid_email($email)) $errors[] = $lang_common['Invalid e-mail'];
		}
	}
	$message = forum_linebreaks(forum_trim($_POST['req_message']));
	if ($message == '') $errors[] = $lang_gallery['Error No message'];
	else if (strlen($message) > 500) $errors[] = $lang_gallery['Error Too long message'];
	else if ($configuration['p_message_all_caps'] == '0' && strtoupper($message) == $message && $forum_user['g_id'] > USER_MOD) $message = ucwords(strtolower($message));
	if ($configuration['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require FORUM_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}
	$uploaded_file = $_FILES['req_file'];
	if (isset($uploaded_file['error']))
	{
		switch ($uploaded_file['error'])
		{
			case 1:
				message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Too large ini']);
				break;
			case 2:
				message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Too large']);
				break;
			case 3:
				message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Partial upload']);
				break;
			case 4:
				message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No file']);
				break;
			case 6:
				message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No tmp directory']);
				break;
			default:
				if ($uploaded_file['size'] == 0) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No file']);
				break;
		}
	}
	if (is_uploaded_file($uploaded_file['tmp_name']))
	{
		$now = time();
		$allowed_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
		if (!in_array($uploaded_file['type'], $allowed_types)) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No file']);
		if ($uploaded_file['size'] > $configuration['g_max_size']) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Too large']);
		$extensions = null;
		if ($uploaded_file['type'] == 'image/gif') $extensions = array('.gif', '.jpg', '.png');
		else if ($uploaded_file['type'] == 'image/jpeg' || $uploaded_file['type'] == 'image/pjpeg') $extensions = array('.jpg', '.gif', '.png');
		else $extensions = array('.png', '.gif', '.jpg');
		if (!@@move_uploaded_file($uploaded_file['tmp_name'], $configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp')) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Move failed'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
		list($width, $height, ,) = getimagesize($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
		if($extensions[0] == '.gif') $img_src = imagecreatefromgif($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
		elseif($extensions[0] == '.jpg') $img_src = imagecreatefromjpeg($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
		else $img_src = imagecreatefrompng($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
     if ($width>$configuration['g_max_width'] OR $height>$configuration['g_max_height'])
     {
       if ($height<=$width) $ratio = $configuration['g_max_width']/$width;
       else $ratio = $configuration['g_max_height']/$height;
     }
     else
     {
        $ratio = 1;
     } 
     $img_dest = imagecreatetruecolor(round($width*$ratio), round($height*$ratio));
     imagecopyresized($img_dest,$img_src,0,0,0,0,round($width*$ratio),round($height*$ratio),$width,$height);
     imagejpeg($img_dest, $configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]);
	@@chmod($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0], 0644);
     if ($width>$configuration['g_max_width_thumbs'] OR $height>$configuration['g_max_height_thumbs'])
     {
       if ($height<=$width) $ratio = $configuration['g_max_width_thumbs']/$width;
       else $ratio = $configuration['g_max_height_thumbs']/$height;
     }
     else
     {
        $ratio = 1;
     }
     $img_dest = imagecreatetruecolor(round($width*$ratio), round($height*$ratio));
     imagecopyresized($img_dest,$img_src,0,0,0,0,round($width*$ratio),round($height*$ratio),$width,$height);
     imagejpeg($img_dest, $configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0]);
	@@chmod($configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0], 0644);
     if (!file_exists($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]) || !file_exists($configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0]))
     {
    	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]);
    	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0]);
    	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
    	message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error Move failed'].' <a href="mailto:'.$configuration['o_admin_email'].'">'.$configuration['o_admin_email'].'</a>.');
     }
     else @@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'.tmp');
  if($configuration['g_ftp_upload'] == 1)
  {
    $conn_id = ftp_connect($configuration['g_ftp_host']); 
    $login_result = ftp_login($conn_id, $configuration['g_ftp_login'], $configuration['g_ftp_pass']);  
    if ((!$conn_id) || (!$login_result)) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error FTP connect']);
    $upload_picture = ftp_put($conn_id, $configuration['g_ftp_rep'].'/'.$forum_user['id'].'_'.$now.$extensions[0], $configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0], FTP_BINARY);
    $upload_thumbs = ftp_put($conn_id, $configuration['g_ftp_rep'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0], $configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0], FTP_BINARY);
    if (!$upload_picture)
    {
    	 @@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]);
  		 message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error FTP upload_picture']);
    }
    if (!$upload_thumbs)
    {
    	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]);
    	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0]);
	    message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error FTP upload_thumbs']);
    }
    ftp_close($conn_id);
  	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_'.$now.$extensions[0]);
  	@@unlink($configuration['g_rep_upload'].'/'.$forum_user['id'].'_thumbs_'.$now.$extensions[0]);
  }
	}
	else message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_profile['Error Unknown failure']);
	if (empty($errors))
	{
			if (!$forum_user['is_guest'])
			{
  				$db->query('INSERT INTO '.$db->prefix.'gallery_img (poster, poster_id, poster_ip, subject, message, posted, cat_id) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', \''.$db->escape($subject).'\', \''.$db->escape($message).'\', '.$now.', '.$cid.')') or error('Unable to create picture', __FILE__, __LINE__, $db->error());
			}
			else
			{
				$email_sql = ($configuration['p_force_guest_email'] == '1' || $email != '') ? '\''.$email.'\'' : 'NULL';
  				$db->query('INSERT INTO '.$db->prefix.'gallery_img (poster, poster_id, poster_ip, poster_email, subject, message, posted, cat_id) VALUES(\''.$db->escape($username).'\', '.$forum_user['id'].', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($subject).'\', \''.$db->escape($message).'\', '.$now.', '.$cid.')') or error('Unable to create picture', __FILE__, __LINE__, $db->error());
			}
			$new_pid = $db->insert_id();
			$db->query('UPDATE '.$db->prefix.'gallery_cat SET num_img=num_img+1, last_post='.$now.', last_poster=\''.$db->escape($username).'\', last_poster_id='.$forum_user['id'].' WHERE id='.$cid) or error('Unable to update gallery', __FILE__, __LINE__, $db->error());
		if (!$forum_user['is_guest'])
		{
			$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
			$db->query('UPDATE '.$low_prio.$db->prefix.'users SET num_posts=num_posts+1, last_post='.$now.' WHERE id='.$forum_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());
		}
		redirect(FORUM_ROOT.'gallery.php?cid='.$cid, $lang_gallery['Post img redirect']);
	}
}
if ($cid)
{
	$action = $lang_post['Post new topic'];
	$form = '<form id="post" method="post" enctype="multipart/form-data" action="gallery_post.php?action=post&amp;cid='.$cid.'" onsubmit="return process_form(this)">';
	$cat_name = convert_htmlspecialchars($cur_posting['cat_name']);
}
else message($lang_common['Bad request']);
$page_title = convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_gallery['Post img'];
$required_fields = array('req_email' => $lang_gallery['E-mail'], 'req_subject' => $lang_gallery['Title'], 'req_message' => $lang_gallery['Message'], 'req_file' => $lang_gallery['Picture']);
$focus_element = array('post');
if (!$forum_user['is_guest']) $focus_element[] = 'req_subject';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_username';
}
require FORUM_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="gallery.php"><?php echo $lang_gallery['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo $cat_name ?><?php if (isset($cur_posting['subject'])) echo '</li><li>&nbsp;&raquo;&nbsp;'.convert_htmlspecialchars($cur_posting['subject']) ?></li></ul>
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
		echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>
<?php
}
$cur_index = 1;
$lang_gallery['Announcement_text'] = str_replace('<MAX_SIZE>', ceil($configuration['g_max_size'] / 1024).' KB', $lang_gallery['Announcement_text']);
?>
<div class="blockform">
	<h2><span><?php echo $lang_gallery['Post img'] ?></span></h2>
	<div class="box">
		<?php echo $form."\n" ?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_gallery['Announcement'] ?></legend>
					<div class="infldset txtarea">
            <?php echo $lang_gallery['Announcement_text'] ?>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_gallery['Write message legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$forum_user['is_guest']) ? convert_htmlspecialchars($forum_user['displayname']) : 'Guest'; ?>" />
<?php
if ($forum_user['is_guest'])
{
	$email_label = ($configuration['p_force_guest_email'] == '1') ? '<strong>'.$lang_common['E-mail'].'</strong>' : $lang_common['E-mail'];
	$email_form_name = ($configuration['p_force_guest_email'] == '1') ? 'req_email' : 'email';
?>						<label class="conl"><strong><?php echo $lang_post['Guest name'] ?></strong><br /><input type="text" name="req_username" value="<?php if (isset($_POST['req_username'])) echo convert_htmlspecialchars($username); ?>" size="25" maxlength="25" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label class="conl"><?php echo $email_label ?><br /><input type="text" name="<?php echo $email_form_name ?>" value="<?php if (isset($_POST[$email_form_name])) echo convert_htmlspecialchars($email); ?>" size="50" maxlength="50" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<div class="clearer"></div>
<?php
}
?>
						<label><strong><?php echo $lang_gallery['Title'] ?></strong><br /><input class="textbox" type="text" name="req_subject" value="<?php if (isset($_POST['req_subject'])) echo convert_htmlspecialchars($subject); ?>" size="50" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label><strong><?php echo $lang_gallery['Message'] ?></strong><br />
						<textarea name="req_message" rows="5" cols="50" tabindex="<?php echo $cur_index++ ?>"><?php echo isset($_POST['req_message']) ? convert_htmlspecialchars(trim($_POST['req_message'])) : (isset($quote) ? $quote : ''); ?></textarea><br /></label>
						<label><strong><?php echo $lang_gallery['Picture'] ?></strong><br />
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $configuration['g_max_size'] ?>" />
            <input name="req_file" type="file" size="40"/ tabindex="<?php echo $cur_index++ ?>"><br /></label>
						<ul class="bblinks">
							<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($configuration['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($configuration['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($configuration['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
						</ul>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" name="submit" class="b1" value="<?php echo $lang_gallery['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
?>