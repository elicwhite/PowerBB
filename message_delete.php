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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/pms.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/delete.php';
if ($forum_user['is_guest'] || $forum_user['g_pm'] == 0) message($lang_common['No permission']);
if (empty($_GET['id'])) message($lang_common['Bad request']);
$id = intval($_GET['id']);
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$cur_post = $db->fetch_assoc($result);
if ($cur_post['owner'] != $forum_user['id']) message($lang_common['No permission']);
if (isset($_POST['delete']))
{
	if (empty($_GET['id'])) message($lang_common['Bad request']);
	$id = intval($_GET['id']);
	confirm_referrer('message_delete.php');
	$db->query('DELETE FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'message_list.php?box='.$_POST['box'].'&p='.$_POST['p'], $lang_pms['Del redirect']);
}
else
{
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_pms['Delete message'];
	require FORUM_ROOT.'header.php';
	require FORUM_ROOT.'include/parser.php';
	$cur_post['message'] = parse_message($cur_post['message'], (int)(!$cur_post['smileys']));
?>
<div class="blockform">
	<h2><span><?php echo $lang_pms['Delete message'] ?></span></h2>
	<div class="box">
		<form method="post" action="message_delete.php?id=<?php echo $id ?>">
		<input type="hidden" name="box" value="<?php echo (int)$_GET['box'] ?>">
		<input type="hidden" name="p" value="<?php echo (int)$_GET['p'] ?>">
			<div class="inform">
				<fieldset>
					<div class="infldset">
						<div class="postmsg">
							<p><?php echo $lang_pms['Sender'] ?>: <strong><?php echo convert_htmlspecialchars($cur_post['sender']) ?></strong></p>
							<?php echo $cur_post['message'] ?>
						</div>
					</div>
				</fieldset>
			</div>
			<p><input type="button" class="b1" name="submit" OnClick="javascript:history.go(-1);" value="<?php echo $lang_common['Go back'] ?>" /><input type="submit" class="b1" name="delete" value="<?php echo $lang_delete['Delete'] ?>" /></p>
		</form>
	</div>
</div>
<?php
	require FORUM_ROOT.'footer.php';
	}
?>