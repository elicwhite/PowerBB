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
if ($forum_user['g_id'] > USER_ADMIN) message($lang_common['No permission']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Note Pad'];
require FORUM_ROOT.'header.php';
if (isset($_POST['new_note']))
{
	if ($_POST['new_note']) $_POST['new_note'] .= '---- '. date('l dS \of F Y h:i:s A'. '----');
	$db->query('UPDATE '.$db->prefix.'config SET conf_value='.'\''.$db->escape($_POST['new_note']).'\''.' WHERE conf_name=\'o_notes\'') or error('Unable to save note', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache(); 
	redirect(FORUM_ROOT.'admin/admin_notes.php', $lang_admin['help_notes_note_saved']);
}
if (isset($_POST['new_note_todo']))
{
	if ($_POST['new_note_todo']) $_POST['new_note_todo'] .= '---- '. date('l dS \of F Y h:i:s A'. '----');
	$db->query('UPDATE '.$db->prefix.'config SET conf_value='.'\''.$db->escape($_POST['new_note_todo']).'\''.' WHERE conf_name=\'o_notes_todo\'') or error('Unable to save ToDo note', __FILE__, __LINE__, $db->error());
	require_once FORUM_ROOT.'include/cache.php';
	generate_config_cache(); 
	redirect(FORUM_ROOT.'admin/admin_notes.php', $lang_admin['help_notes_todo_saved']);
}
generate_admin_menu('notes');
?>
<div class="blockform">
	<div class="tab-page" id="notesPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "notesPane" ), 1 )</script>
	<div class="tab-page" id="admin-note-page"><h2 class="tab"><?php echo $lang_admin['Admin_note'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "admin-note-page" ) );</script>
		<div id="adintro" class="box">
			<div class="inbox">
				<p>
					<form id="notes" name="notes" method="post" action="admin_notes.php">
						<textarea name="new_note" class="note" cols="80" rows="20"><?php echo $configuration['o_notes'] ?></textarea><br /><br />
						<input type="submit" class="b1" value="<?php echo $lang_admin['SaveNote'] ?>" />&nbsp;
						<input type="button" onclick="document.forms['notes'].elements[0].value =''; return false" class="b1" value="<?php echo $lang_admin['ClearNote'] ?>" /><br />
					</form>
				</p>
			</div>
		</div>
	</div>
	<div class="tab-page" id="todo-note-page"><h2 class="tab"><?php echo $lang_admin['Todo_note'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "todo-note-page" ) );</script>
		<div id="adintro" class="box">
			<div class="inbox">
				<p>
					<form id="todo_notes" method="post" action="admin_notes.php">
						<textarea name="new_note_todo" class="note" cols="80" rows="20"><?php echo $configuration['o_notes_todo'] ?></textarea><br /><br />
						<input type="submit" class="b1" value="<?php echo $lang_admin['SaveTodo'] ?>" />&nbsp;
						<input type="button" onclick="document.forms['todo_notes'].elements[0].value =''; return false" class="b1" value="<?php echo $lang_admin['ClearTodo'] ?>"><br />
					</form>
				</p>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>