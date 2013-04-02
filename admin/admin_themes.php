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
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".$lang_admin['Admin'].$lang_admin['Themes'];
require FORUM_ROOT.'header.php';
if (isset($_POST['style']))
{
	$db->query('UPDATE '.$db->prefix.'users SET style=\''.$_POST['form']['style'].'\' WHERE id>1') or error('Unable to set style settings', __FILE__, __LINE__, $db->error());
	redirect(FORUM_ROOT.'admin/admin_themes.php', 'Themes reset. Redirecting &hellip;');
}
if(isset($_POST['install']))
{
	$file['size'] = $_FILES['style_package']['size'];
	$file['error'] = $_FILES['style_package']['error'];
	$file['name'] = $_FILES['style_package']['name'];
	$file['tmp_name'] = $_FILES['style_package']['tmp_name'];
		if(substr($file['name'], -4) == '.php' && $cur_project['PowerBBmod'] == 0) $file['name'] .= 's';
		switch ($file['error'])
		{
			case 1:
			case 2:
				$errors[] = $file['name'].': Too large ini';
				break;
			case 3:
				$errors[] = $file['name'].': Partial upload';
				break;
		}
		if($file['size'] > 0 && empty($errors))
		{
			$filename = $file['tmp_name'];
			$fp = fopen($filename,"rb");
			$tar_file = fread($fp,filesize($filename));
			fclose($fp);
			$tar_length = strlen($tar_file);
			$main_offset = 0;
			$outbuffer = '';
			$error = 0;
			$lastdir = '';
			while($main_offset < $tar_length)
			{
				if(substr($tar_file,$main_offset,512) == str_repeat(chr(0),512)) break;
				$position		= strpos(substr($tar_file,$main_offset,100),chr(0));
				$filename		= substr(substr($tar_file,$main_offset,100),0,$position);
				$filesize		= octdec(substr($tar_file,$main_offset + 124,12));
				$filecontents	= substr($tar_file,$main_offset + 512,$filesize);
				if($filesize > 0)
				{
					if(is_file($filename))
					{
						$outbuffer .= "\t\t\t\t\t".'<li><strong>File already existed:</strong> '.$filename.' - Not unpacked</li>'."\n";
						$error = 1;
					}
					else
					{
						$f = fopen($filename, 'w');
						fwrite($f, $filecontents);
						fclose($f);
						$outbuffer .= "\t\t\t\t\t".'<li>Unpacked file: '.$filename.'</li>'."\n";
					}
				}
				else
				{
					$dirs = explode('/', $filename);
					foreach($dirs as $dir)
					{
						$cdir = $lastdir.$dir;
						if(@mkdir($cdir))
						{
							$outbuffer .= "\t\t\t\t\t".'<li>Created directory: '.$filename.'</li>'."\n";
							$f = fopen($filename.'/index.html', 'w');
							fwrite($f, "<html>\n<head>\n<title>.</title>\n</head>\n<body>\n.\n</body>\n</html>");
							fclose($f);
						}
						$lastdir = $cdir.'/';
					}
					$lastdir = '';
				}
				$main_offset += 512 + (ceil($filesize / 512) * 512);
			}

?>
	<div id="posterror" class="block">
		<h2><span>Installation log</span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<?php echo $outbuffer ?>
				</ul>
				<p><?php echo ($error == 0?'The style was installed without errors':'<strong>There were errors during the installation</strong>') ?></p>
			</div>
		</div>
	</div>
	<br />
<?php
		}
}
if (!empty($errors))
{
?>
	<div id="posterror" class="block">
		<h2><span>Errors</span></h2>
		<div class="box">
			<div class="inbox">
				<p>The following errors were encountered:</p>
				<ul>
	<?php
		while (list(, $cur_error) = each($errors)) echo "\t\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
	?>
				</ul>
			</div>
		</div>
	</div>
	<br />
<?php
}
generate_admin_menu('themes');
?>
<div id="modtopicpreview"></div>
<script type="text/javascript" src="include/js/topic_preview.js"></script>
	<div class="blockform">
	<div class="tab-page" id="themesPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "themesPane" ), 1 )</script>
	<div class="tab-page" id="help-page"><h2 class="tab"><?php echo $lang_admin['Help'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/themes.png></td>
							<td>
								<span><?php echo $lang_admin['help_themes']; ?></span>
							</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="Install-Themes-page"><h2 class="tab"><?php echo $lang_admin['Install'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "install-themes-page" ) );</script>
		<div class="box">
			<form method="post" action="admin_themes.php" enctype="multipart/form-data">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin['help_install_themes'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<td width=150px><?php echo $lang_admin['Theme package'] ?></td>
								<td>
									<input type="file" name="style_package" size="25" tabindex="1" />
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="install" value="<?php echo $lang_admin['Install'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="Configure-Themes-page"><h2 class="tab"><?php echo $lang_admin['Configure'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "configure-themes-page" ) );</script>
		<div class="box">
			<form>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin['cur_installed_themes'] ?></legend>
						<div class="infldset">
						<table border=0 width=100%>
						<tr>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;" width="140px"><b>Theme</b></td>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;" width="150px"><b>Author</b></td>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;"><b>Description</b></td>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;" width="120px"><b>Actions</b></td>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;" width="10px"><b>Default</b></td>
							<td style="background-image:url(../img/Default/tile_sub.gif);height:20px;" width="5px"><b>Information</b></td>
						</tr>
<?php
		$styles = array();
		$d = dir(FORUM_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css') $styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();
		while (list(, $temp) = @each($styles))
		{
			if (!$fp = @fopen(FORUM_ROOT.'style/'.$temp.'.css', 'r')) 
			{
				error('File not found.');
			}
			$description = rtrim(@fgets($fp),"\x00..\x1F");
			$author = rtrim(fgets($fp),"\x00..\x1F");
			@fclose($fp);
			echo "<tr>
				<td>".$temp."</td>
				<td>" . trim($author, '/*') . "</td>
				<td>" . trim($description, '/*') . "</td>
				<td style='text-align:center;'>";
			if (($temp == 'Default') || ($temp == $configuration['o_default_style'])) echo "<img border='0' src='" . FORUM_ROOT . "img/admin/notrash.png' alt='cannot delete default theme' />";
			else echo "<a href='admin_themes_process.php?action=delete&theme_file=". $temp . "'><img border='0' src='" . FORUM_ROOT . "img/admin/trash.png' alt='delete theme' /></a>";
			if ($temp == 'Default') echo "&nbsp;&nbsp;<img border='0' src='" . FORUM_ROOT . "img/admin/noedit.png' alt='cannot edit default theme' />";
			else echo "&nbsp;&nbsp;<a href='admin_loader.php?plugin=AP_File_Editor.php&chdir=./style/&edit=". $temp . ".css'><img border='0' src='" . FORUM_ROOT . "img/admin/edit.png' alt='edit theme' /></a>";
			if (file_exists(FORUM_ROOT. "img/" . $temp . "/preview.png")) echo "&nbsp;&nbsp;<img border='0' onmouseover=\"return overlib('<img src=" . FORUM_ROOT. "img/" . $temp . "/preview.png />',CAPTION,'Preview',LEFT);\" onmouseout='return nd();' src='" . FORUM_ROOT . "img/admin/preview.png' alt='preview theme' /></td>";
			else echo "&nbsp;&nbsp;<img border='0' src='" . FORUM_ROOT . "img/admin/nopreview.png' alt='no preview' /></td>";
			if ($configuration['o_default_style'] == $temp) echo "<td style='text-align:center;'><img border='0' src='" . FORUM_ROOT . "img/admin/theme_default.png' alt='this is the default theme' /></td></tr>";
			else echo "<td style='text-align:center;'><img border='0' onclick='window.location = \"admin_themes_process.php?action=set_default&theme_file=".$temp. "\";' src='" . FORUM_ROOT . "img/admin/nodefault.png' alt='no preview' /></td>
<td>
<img src=\"".FORUM_ROOT."img/admin/tooltip.png\" onmouseover=\"return overlib('The far left icon deletes the theme from your server. The CSS button uses the theme editor to edit the css for that theme. The folder button shows you a preview of the theme.');\" onmouseout=\"return nd();\" alt=\"\" />
</td>
</tr>";
		}
?>

							</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="tab-page" id="Reset-Themes-page"><h2 class="tab"><?php echo $lang_admin['Reset'] ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "reset-themes-page" ) );</script>
		<div class="box">
			<div class="inbox">
				<form id="style" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Reset user styles to a new style (after installing a new one)</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<th scope="row">Theme Usage</th>
							<td>
<?php
		$result = $db->query('SELECT style, count(*) as number FROM '.$db->prefix.'users WHERE id > 1 GROUP BY style ORDER BY number') or error('Unable to fetch style settings', __FILE__, __LINE__, $db->error());
		$number = $db->num_rows($db->query('SELECT username from '.$db->prefix.'users WHERE id > 1'));
		while ($cur_lang = $db->fetch_assoc($result))
		{
			echo RoundSigDigs($cur_lang['number'] / $number * 100,3).'% '.str_replace('_',' ',$cur_lang['style']).'&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib(\'The percentage of use for each theme.\');" onmouseout="return nd();" alt="" /><br />';
		}
?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo $lang_admin['Theme'] ?></th>
							<td>
<?php
		$styles = array();
		$d = dir(FORUM_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css') $styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();
?>
								<select name="form[style]">
<?php
		while (list(, $temp) = @each($styles))
		{
			echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>'."\n";
		}
?>
								</select>
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The theme which will become default for all users, even if they have chosen another theme.');" onmouseout="return nd();" alt="" />
							</td>
						</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend" style="text-align:left;"><input class="b1" type="submit" name="style" value="<?php echo $lang_admin['Reset'] ?>" tabindex="2" /></p>
				</form>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php
require FORUM_ROOT.'admin/admin_footer.php'; ?>