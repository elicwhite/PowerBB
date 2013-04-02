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

define('ADMIN_CONSOLE', 1);
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
if (isset($_POST['add_word']))
{
	confirm_referrer('admin_censoring.php');
	$search_for = trim($_POST['new_search_for']);
	$replace_with = trim($_POST['new_replace_with']);
	if ($search_for == '' || $replace_with == '') message('You must enter both a word to censor and text to replace it with.');
	$db->query('INSERT INTO '.$db->prefix.'censoring (search_for, replace_with) VALUES (\''.$db->escape($search_for).'\', \''.$db->escape($replace_with).'\')') or error('Unable to add censor word', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_censoring.php', 'Censor word added. Redirecting &hellip;');
}
else if (isset($_POST['update']))
{
	confirm_referrer('admin_censoring.php');
	$id = intval(key($_POST['update']));
	$search_for = trim($_POST['search_for'][$id]);
	$replace_with = trim($_POST['replace_with'][$id]);
	if ($search_for == '' || $replace_with == '') message('You must enter both text to search for and text to replace with.');
	$db->query('UPDATE '.$db->prefix.'censoring SET search_for=\''.$db->escape($search_for).'\', replace_with=\''.$db->escape($replace_with).'\' WHERE id='.$id) or error('Unable to update censor word', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_censoring.php', 'Censor word updated. Redirecting &hellip;');
}
else if (isset($_POST['remove']))
{
	confirm_referrer('admin_censoring.php');
	$id = intval(key($_POST['remove']));
	$db->query('DELETE FROM '.$db->prefix.'censoring WHERE id='.$id) or error('Unable to delete censor word', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_censoring.php', 'Censor word removed. Redirecting &hellip;');
}
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Censoring'];
$focus_element = array('censoring', 'new_search_for');
require FORUM_ROOT.'header.php';
generate_admin_menu('censoring');
?>
<div class="blockform">
	<div class="tab-page" id="censorPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "censorPane" ), 1 )</script>
	<div class="tab-page" id="help-censor-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-censor-page" ) );</script>
		<div class="box">
			<form>
				<div class="inbox">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width="100px">
									<img src="<?php echo FORUM_ROOT?>img/admin/censoring.png" />
								</td>
								<td>
									<span><?php echo $lang_admin['help_censor']; ?></span>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="add-censor-page"><h2 class="tab"><?php echo $lang_admin['Add']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "add-censor-page" ) );</script>
		<div class="box">
			<form id="censoring" method="post" action="admin_censoring.php">
				<div class="inbox">
					<fieldset>
						<legend><?php echo $lang_admin['Add word']; ?></legend>
						<div class="infldset">
							<p><?php echo $lang_admin['help_censor_add']; ?></p>
							<table  cellspacing="0">
								<thead>
									<tr>
										<th class="tcl" scope="col"><?php echo $lang_admin['Censored word']; ?></th>
										<th class="tc2" scope="col"><?php echo $lang_admin['Replacement text']; ?></th>
										<th class="hidehead" scope="col"><?php echo $lang_admin['Action']; ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<input type="text" class="textbox" name="new_search_for" size="24" maxlength="60" tabindex="1" />
										</td>
										<td>
											<input type="text" class="textbox" name="new_replace_with" size="24" maxlength="60" tabindex="2" />
										</td>
										<td>
											<input type="submit" class="b1" name="add_word" value="<?php echo $lang_admin['Add'] ?>" tabindex="3" />
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="edit-censor-page"><h2 class="tab"><?php echo $lang_admin['Edit']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "edit-censor-page" ) );</script>
		<div class="box">
			<form id="censoring" method="post" action="admin_censoring.php">
				<div class="inbox">
					<fieldset>
						<legend><?php echo $lang_admin['Edit/remove words']; ?></legend>
						<div class="infldset">
							<?php
							$result = $db->query('SELECT id, search_for, replace_with FROM '.$db->prefix.'censoring ORDER BY id') or error('Unable to fetch censor word list', __FILE__, __LINE__, $db->error());
							if ($db->num_rows($result))
							{
							?>
							<table cellspacing="0" >
								<thead>
									<tr>
										<th class="tcl" scope="col"><?php echo $lang_admin['Censored word']; ?></th>
										<th class="tc2" scope="col"><?php echo $lang_admin['Replacement text']; ?></th>
										<th class="hidehead" scope="col"><?php echo $lang_admin['Action']; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
										while ($cur_word = $db->fetch_assoc($result)) echo "\t\t\t\t\t\t\t\t".'<tr><td><input type="text" class="textbox" name="search_for['.$cur_word['id'].']" value="'.convert_htmlspecialchars($cur_word['search_for']).'" size="24" maxlength="60" /></td><td><input type="text" class="textbox" name="replace_with['.$cur_word['id'].']" value="'.convert_htmlspecialchars($cur_word['replace_with']).'" size="24" maxlength="60" /></td><td><input type="submit" class="b1" name="update['.$cur_word['id'].']" value="'. $lang_admin['Update'] .'" />&nbsp;<input type="submit" class="b1" name="remove['.$cur_word['id'].']" value="'. $lang_admin['Remove'] .'" /></td></tr>'."\n";
									?>
								</tbody>
							</table>
							<?php
							}
							else echo "\t\t\t\t\t\t\t".'<p>'.$lang_admin['no_cens_words'].'</p>'."\n";
							?>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="clearer">
	</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>