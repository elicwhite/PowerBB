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



//--Definitions for page loading and including common page files.
define('ADMIN_CONSOLE', 1);
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';

//--Checking user level for ban permissions and adding bans.
if ($forum_user['g_id'] > USER_MOD || ($forum_user['g_id'] == USER_MOD && $configuration['p_mod_ban_users'] == '0')) message($lang_common['No permission']);
if (isset($_REQUEST['add_ban']) || isset($_GET['edit_ban']))
{
	if (isset($_GET['add_ban']) || isset($_POST['add_ban']))
	{
		if (isset($_GET['add_ban']))
		{
			//--To add a ban, checking specifics, checking database, return message
			$add_ban = intval($_GET['add_ban']);
			if ($add_ban < 2) message($lang_common['Bad request']);
			$user_id = $add_ban;
			$result = $db->query('SELECT group_id, username, displayname, email FROM '.$db->prefix.'users WHERE id='.$user_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result)) list($group_id, $ban_user, $ban_email) = $db->fetch_row($result);
			else message('No user by that ID registered.');
		}
		else
		{
			$ban_user = trim($_POST['new_ban_user']);
			if ($ban_user != '')
			{
				//--Adding bans to the database
				$result = $db->query('SELECT id, group_id, username, displayname, email FROM '.$db->prefix.'users WHERE username=\''.$db->escape($ban_user).'\' AND id>1') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
				if ($db->num_rows($result)) list($user_id, $group_id, $ban_user, $ban_email) = $db->fetch_row($result);
				else message('No user by that username registered. If you want to add a ban not tied to a specific username just leave the username blank.');
			}
		}
		if (isset($group_id) && $group_id == USER_ADMIN) message('The user '.convert_htmlspecialchars($ban_user).' is an administrator and can\'t be banned. If you want to ban an administrator, you must first demote him/her to moderator or user.');
		if (isset($user_id))
		{
			$result = $db->query('SELECT poster_ip FROM '.$db->prefix.'posts WHERE poster_id='.$user_id.' ORDER BY posted DESC LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
			$ban_ip = ($db->num_rows($result)) ? $db->result($result) : '';
		}
		$mode = 'add';
	}
	else
	{
		$ban_id = intval($_GET['edit_ban']);
		if ($ban_id < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT username, displayname, ip, email, message, expire FROM '.$db->prefix.'bans WHERE id='.$ban_id) or error('Unable to fetch ban info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result)) list($ban_user, $ban_ip, $ban_email, $ban_message, $ban_expire) = $db->fetch_row($result);
		else message($lang_common['Bad request']);
		$ban_expire = ($ban_expire != '') ? date('Y-m-d', $ban_expire) : '';
		$mode = 'edit';
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Bans'];
	$focus_element = array('bans2', 'ban_user');
	require FORUM_ROOT.'header.php';
	generate_admin_menu('bans');
?>
	<div class="blockform">
	<div class="tab-page" id="bans1Pane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "bans1Pane" ), 1 )</script>
	<div class="tab-page" id="adv-bans-page"><h2 class="tab">Edit</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "adv-bans-page" ) );</script>
		<div class="box">
			<form id="bans2" method="post" action="admin_bans.php">
				<div class="inform">
					<input type="hidden" name="mode" value="<?php echo $mode ?>" />
						<?php if ($mode == 'edit'): ?>
							<input type="hidden" name="ban_id" value="<?php echo $ban_id ?>" />
						<?php endif; ?>	
						<fieldset>
							<legend>
								<?php echo $lang_admin['Supplement ban'] ?>
							</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<th scope="row">
											<?php echo $lang_admin['Username'] ?>
										</th>
										<td>
											<input type="text" class="textbox" name="ban_user" size="30" maxlength="25" value="<?php if (isset($ban_user)) echo convert_htmlspecialchars($ban_user); ?>" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['help_bans_username'] ?>');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<?php echo $lang_admin['IP address'] ?>
										</th>
										<td>
											<input type="text" class="textbox" name="ban_ip" size="30" maxlength="255" value="<?php if (isset($ban_ip)) echo $ban_ip; ?>" tabindex="2" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['help_bans_ip'] ?>');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<?php echo $lang_admin['E-Mail / domain'] ?>
										</th>
										<td>
											<input type="text" class="textbox" name="ban_email" size="30" maxlength="50" value="<?php if (isset($ban_email)) echo strtolower($ban_email); ?>" tabindex="3" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['help_bans_email'] ?>');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
									<p class="topspace">
										<strong class="warntext">
											<?php echo $lang_admin['help_bans_warn'] ?>
										</strong>
									</p>
							</div>
						</fieldset>
					</div>
					<div class="inform">
						<fieldset>
							<legend>
								<?php echo $lang_admin['Ban message and expiry'] ?>
							</legend>
							<div class="infldset">
								<table class="aligntop" cellspacing="0">
									<tr>
										<th scope="row">
											<?php echo $lang_admin['Ban message'] ?>
										</th>
										<td>
											<input type="text" class="textbox" name="ban_message" size="50" maxlength="255" value="<?php if (isset($ban_message)) echo convert_htmlspecialchars($ban_message); ?>" tabindex="4" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('A message that will be displayed to the banned user when he/she visits the forums.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<?php echo $lang_admin['Ban expire date'] ?>
										</th>
										<td>
											<input type="text" class="textbox" name="ban_expire" size="30" maxlength="10" value="<?php if (isset($ban_expire)) echo $ban_expire; ?>" tabindex="5" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The date when this ban should be automatically removed (format: YYYY-MM-DD). Leave blank to remove manually.');" onmouseout="return nd();" alt="" />
										</td>
									</tr>
								</table>
							</div>
						</fieldset>
					</div>
					<p class="submitend" style="text-align:left;"><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="add_edit_ban" value="<?php echo $lang_admin['Save'] ?>" tabindex="6" /></p>
				</form>
			</div>
		</div>
		<div class="clearer">
		</div>
	</div>
	<?php
		require FORUM_ROOT.'admin/admin_footer.php';
	}
	else if (isset($_POST['add_edit_ban']))
	{
		confirm_referrer('admin_bans.php');
		$ban_user = trim($_POST['ban_user']);
		$ban_ip = trim($_POST['ban_ip']);
		$ban_email = strtolower(trim($_POST['ban_email']));
		$ban_message = trim($_POST['ban_message']);
		$ban_expire = trim($_POST['ban_expire']);
		if ($ban_user == '' && $ban_ip == '' && $ban_email == '') message($lang_admin['Ban_error_message']);
		if ($ban_ip != '')
		{
			$ban_ip = preg_replace('/[\s]{2,}/', ' ', $ban_ip);
			$addresses = explode(' ', $ban_ip);
			$addresses = array_map('trim', $addresses);
			for ($i = 0; $i < count($addresses); ++$i)
			{
				$octets = explode('.', $addresses[$i]);
				for ($c = 0; $c < count($octets); ++$c)
				{
					$octets[$c] = (strlen($octets[$c]) > 1) ? ltrim($octets[$c], "0") : $octets[$c];
					if ($c > 3 || preg_match('/[^0-9]/', $octets[$c]) || intval($octets[$c]) > 255) message($lang_admin['Ban_ip_error']);
				}
				$cur_address = implode('.', $octets);
				$addresses[$i] = $cur_address;
			}
			$ban_ip = implode(' ', $addresses);
		}
		require FORUM_ROOT.'include/email.php';
		if ($ban_email != '' && !is_valid_email($ban_email))
		{
			if (!preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/', $ban_email)) message($lang_admin['Ban_email_error']);
		}
		if ($ban_expire != '' && $ban_expire != 'Never')
		{
			$ban_expire = strtotime($ban_expire);
			if ($ban_expire == -1 || $ban_expire <= time()) message($lang_admin['Ban_date_error']);
		}
		else $ban_expire = 'NULL';
		$ban_user = ($ban_user != '') ? '\''.$db->escape($ban_user).'\'' : 'NULL';
		$ban_ip = ($ban_ip != '') ? '\''.$db->escape($ban_ip).'\'' : 'NULL';
		$ban_email = ($ban_email != '') ? '\''.$db->escape($ban_email).'\'' : 'NULL';
		$ban_message = ($ban_message != '') ? '\''.$db->escape($ban_message).'\'' : 'NULL';
		if ($_POST['mode'] == 'add') $db->query('INSERT INTO '.$db->prefix.'bans (username, ip, email, message, expire) VALUES('.$ban_user.', '.$ban_ip.', '.$ban_email.', '.$ban_message.', '.$ban_expire.')') or error('Unable to add ban', __FILE__, __LINE__, $db->error());
		else $db->query('UPDATE '.$db->prefix.'bans SET username='.$ban_user.', ip='.$ban_ip.', email='.$ban_email.', message='.$ban_message.', expire='.$ban_expire.' WHERE id='.intval($_POST['ban_id'])) or error('Unable to update ban', __FILE__, __LINE__, $db->error());
		require_once FORUM_ROOT.'include/cache.php';
		generate_bans_cache();
		redirect(FORUM_ROOT.'admin/admin_bans.php', 'Ban '.(($_POST['mode'] == 'edit') ? 'edited' : 'added').'. Redirecting &hellip;');
	}
	else if (isset($_GET['del_ban']))
	{
		confirm_referrer('admin_bans.php');
		$ban_id = intval($_GET['del_ban']);
		if ($ban_id < 1) message($lang_common['Bad request']);
		$db->query('DELETE FROM '.$db->prefix.'bans WHERE id='.$ban_id) or error('Unable to delete ban', __FILE__, __LINE__, $db->error());
		require_once FORUM_ROOT.'include/cache.php';
		generate_bans_cache();
		redirect(FORUM_ROOT.'admin/admin_bans.php', 'Ban removed. Redirecting &hellip;');
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / Admin / Bans';
	$focus_element = array('bans', 'new_ban_user');
	require FORUM_ROOT.'header.php';
	generate_admin_menu('bans');
?>
<div class="blockform">
	<div class="tab-page" id="bansPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "bansPane" ), 1 )</script>
	<div class="tab-page" id="help-bans-page"><h2 class="tab"><?php echo $lang_admin['Help'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-bans-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src=<?php echo FORUM_ROOT?>img/admin/bans.png>
								</td>
								<td>
									<span>
										<?php echo $lang_admin['help_bans']; ?>
									</span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-bans-page"><h2 class="tab"><?php echo $lang_admin['Add'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-bans-page" ) );</script>
		<div class="box">
			<form id="bans" method="post" action="admin_bans.php?action=more">
				<div class="inform">
					<fieldset>
						<legend>Add ban</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">
										<?php echo $lang_admin['Username'] ?>
									</th>
									<td>
										<input type="text" class="textbox" name="new_ban_user" size="25" maxlength="25" tabindex="1" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('<?php echo $lang_admin['help_bans_username_ext'] ?>');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="add_ban" value="<?php echo $lang_admin['Add'] ?>" tabindex="2" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="edit-bans-page"><h2 class="tab"><?php echo $lang_admin['Edit'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-bans-page" ) );</script>
			<div class="box">
				<div class="fakeform">
					<?php
					$result = $db->query('SELECT id, username, ip, email, message, expire FROM '.$db->prefix.'bans ORDER BY id') or error('Unable to fetch ban list', __FILE__, __LINE__, $db->error());
					if ($db->num_rows($result))
					{
						while ($cur_ban = $db->fetch_assoc($result))
						{
							$expire = format_time($cur_ban['expire'], true);
					?>
				<div class="inform">
					<fieldset>
						<legend>
							<?php echo $lang_admin['Ban expires'] ?><?php echo $expire ?>
						</legend>
						<div class="infldset">
							<table cellspacing="0">
								<?php if ($cur_ban['username'] != ''): ?>
								<tr>
									<th>
										<?php echo $lang_admin['Username'] ?>
									</th>
									<td>
										<?php echo convert_htmlspecialchars($cur_ban['username']) ?>
									</td>
								</tr>
								<?php endif; ?><?php if ($cur_ban['email'] != ''): ?>
								<tr>
									<th>
										<?php echo $lang_admin['E-Mail'] ?>
									</th>
									<td>
										<?php echo $cur_ban['email'] ?>
									</td>
								</tr>
										<?php endif; ?><?php if ($cur_ban['ip'] != ''): ?>
								<tr>
									<th>
										<?php echo $lang_admin['IP Range'] ?>
									</th>
									<td>
										<?php echo $cur_ban['ip'] ?>
									</td>
								</tr>
								<?php endif; ?><?php if ($cur_ban['message'] != ''): ?>
								<tr>
									<th>
										<?php echo $lang_admin['Reason'] ?>
									</th>
									<td>
										<?php echo convert_htmlspecialchars($cur_ban['message']) ?>
									</td>
								</tr>
								<?php endif; ?>
							</table>
							<p class="linkactions">
								<a href="admin_bans.php?edit_ban=<?php echo $cur_ban['id'] ?>">
									<?php echo $lang_admin['Edit'] ?>
								</a> - 
								<a href="admin_bans.php?del_ban=<?php echo $cur_ban['id'] ?>">
									<?php echo $lang_admin['Remove'] ?>
								</a>
							</p>
						</div>
					</fieldset>
				</div>
				<?php
					}
				}
				else echo "\t\t\t\t".'<p>'.$lang_admin['No bans'] .'</p>'."\n";
				?>
			</div>
		</div>
	</div>
	<div class="clearer">
	</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php';?>