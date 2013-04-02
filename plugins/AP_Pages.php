<?php
if (!defined('IN_FORUM')) exit;
define('PLUGIN_LOADED', 1);
$action = isset($_GET['action']) ? $_GET['action'] : NULL;
$page_id = isset($_GET['id']) ? intval($_GET['id']) : NULL;
if($action == 'add')
{
	if(isset($_POST['form_sent']))
	{
		if(empty($_POST['title'])) message('You need to enter a title for your page');
		elseif(empty($_POST['content'])) message('You need to enter content for your page');
		$title = forum_trim($_POST['title']);
		$content = forum_linebreaks(addslashes($_POST['content']));
		$db->query('INSERT INTO '.$db->prefix.'pages (title, content) VALUES("'.$title.'", "'.$content.'")') or error('Unable to save page', __FILE__, __LINE__, $db->error());
		$page_id = $db->insert_id();
		redirect(FORUM_ROOT.'page.php?id='.$page_id, 'Page Sucessfully added, Redirecting &hellip;');
	}
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Add Page</span></h2>
		<div class="box">
			<form id="example" method="post" action="admin_loader.php?plugin=<?php echo $plugin ?>&amp;action=<?php echo $action ?>">
				<input type="hidden" name="form_sent" value="TRUE" />
				<div class="inform">
					<fieldset>
						<legend>Enter page settings and content</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<td><b>Page Title</b> <input type="text" class="textbox" name="title" size="50" tabindex="1" /></td>
								</tr>
								<tr>
									<td><textarea name="content" tabindex="2" rows="20" cols="50" style="width:99%"></textarea></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" name="submit" value="Submit" class="b1" tabindex="3" /></p>
			</form>
		</div>
	</div>
<?php
}
elseif($action == 'edit')
{
	if(isset($_POST['form_sent']))
	{
		if(empty($_POST['title'])) message('You need to enter a title for your page');
		elseif(empty($_POST['content'])) message('You need to enter content for your page');
		$title = forum_trim($_POST['title']);
		$content = forum_linebreaks(addslashes($_POST['content']));
		$db->query('UPDATE '.$db->prefix.'pages SET title="'.$title.'", content="'.$content.'" WHERE id='.$page_id) or error('Unable to update page', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'admin_loader.php?plugin='.$plugin, 'Page Sucessfully edited, Redirecting &hellip;');
	}
	$result = $db->query("SELECT id, title, content FROM ".$db_prefix."pages WHERE id='".intval($_GET['id'])."'") or error('Unable to fetch page information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result)) message($lang_common['Bad request']);
	$data = $db->fetch_assoc($result);
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Edit Page</span></h2>
		<div class="box">
			<form id="example" method="post" action="admin_loader.php?plugin=<?php echo $plugin ?>&amp;action=<?php echo $action ?>&amp;id=<?php echo $page_id ?>">
				<input type="hidden" name="form_sent" value="TRUE" />
				<div class="inform">
					<fieldset>
						<legend>Enter page settings and content</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<td><b>Page Title</b> <br/> <input type="text" class="textbox" name="title" value="<?php echo $data['title']?>" size="25" tabindex="1" /></td>
								</tr>
								<tr>
									<td><textarea name="content" tabindex="2" rows="20" cols="50" style="width:100%"><?php echo convert_htmlspecialchars($data['content'])?></textarea></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="submit" value="Submit" tabindex="3" /></p>
			</form>
		</div>
	</div>
<?php
}
elseif($action == 'delete')
{
	if(isset($_POST['delete_comply']))
	{
		$db->query('DELETE FROM '.$db->prefix.'pages WHERE id='.$page_id) or error('Unable to delete page', __FILE__, __LINE__, $db->error());
		redirect(FORUM_ROOT.'admin_loader.php?plugin='.$plugin, 'Page Sucessfully Deleted, Redirecting &hellip;');
	}
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Delete Page</span></h2>
		<div class="box">
			<form id="example" method="post" action="admin_loader.php?plugin=<?php echo $plugin ?>&amp;action=<?php echo $action?>&amp;id=<?php echo $page_id ?>">
				<div class="inform">
					<fieldset>
						<legend>Important: read before deleting</legend>
						<div class="infldset">
							<p>Please confirm that you want to delete this page.</p>
							<p class="warntext"><strong>Warning! Deleted Pages cannot be restored</strong></p>								
						</div>
					</fieldset>
				</div>
				<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" name="delete_comply" class="b1" value="Delete" /></p>
			</form>
		</div>
	</div>

<?php
}
else
{	
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Custom Pages</span></h2>
		<div class="box">
			<div class="fakeform">
				<div class="inform">
					<fieldset>
					<legend>Existing pages</legend>
						<div class="infldset">
							<table cellspacing="0">
<?php
	$result = $db->query('SELECT id, title, content FROM '.$db->prefix.'pages') or error('Unable to select pages from database', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while($data = $db->fetch_assoc($result))
			echo "\t\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t\t".'<th scope="row">'."\n\t\t\t\t\t\t\t\t\t".'<a href="admin_loader.php?plugin='.$plugin.'&amp;action=delete&amp;id='.$data['id'].'">Delete</a> | '."\n\t\t\t\t\t\t\t\t\t".'<a href="admin_loader.php?plugin='.$plugin.'&amp;action=edit&amp;id='.$data['id'].'">Edit</a>'."\n\t\t\t\t\t\t\t\t".'</th>'."\n\t\t\t\t\t\t\t\t".'<td><a href="page.php?id='.$data['id'].'">'.$data['title'].'</a></td>'."\n\t\t\t\t\t\t\t".'</tr>'."\n";
	}
	else echo "\t\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t\t".'<th scope="row">There are no pages in the database</td>'."\n\t\t\t\t\t\t\t".'</tr>'."\n";
?>
							</table>
						</div>
					</fieldset>
				</div>
				<p><a href="admin_loader.php?plugin=<?php echo $plugin ?>&amp;action=add">Add New Page</p>
			</div>
		</div>
	</div>

<?php	
}
