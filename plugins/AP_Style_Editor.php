<?php
if (!defined('IN_FORUM')) exit;
define('PLUGIN_LOADED', 1);
$allowed_files = array('');
$always_deny_files = array($_GET['plugin'],'config.php');
$always_allow_files = array('');
$curdir = './';
if( isset($_GET['chdir']))
{
	$tmp = str_replace('\\', '/', $_GET['chdir']);
	if($_GET['chdir'] != $curdir)
	{
		$tmp = explode('/', $tmp);
		$tmp = preg_replace('/\\W/', '', $tmp);
		$i=0;
		foreach($tmp as $value)
		{
			if($value != false)
			{
				$tmpdir[$i] = $value;
				$i++;
			}
		}
		if(isset($tmpdir))
		{
			$curdir = './';
			$curdir .= implode('/', $tmpdir);
			$curdir .= '/';
		}
	}
}
$backup_date = date("m_d_Y");
$backup_root = FORUM_ROOT.'backup/';
if(!is_writable($backup_root)) message('The backup root folder: <strong>'.$backup_root.'</strong> is not writable, or does not exist. Please correct this before running this plugin again.');
$backup_index = 'backup_index.txt';
$backup_data_file = preg_replace('/\\D/', '', microtime());
if(!is_file($backup_root.$backup_index))
{
	$fh = fopen($backup_root.$backup_index, 'wb');
	fwrite($fh, '');
	fclose($fh);
}
if( isset($_GET['edit']) || isset($_GET['chdir']))
{
	$fte = $curdir.basename($_GET['edit']);
	if(!is_dir($fte) && !in_array(preg_replace('/.*(\\.\\w*)$/i', '$1', basename($_GET['edit'])), $allowed_files) || in_array(basename($_GET['edit']), $always_deny_files) )
	{
		if( !in_array(basename($_GET['edit']), $always_allow_files) ) message('Error: You are not allowed to edit this file &hellip;');
	}
	if( isset($_POST['save_as']) && preg_match('/^\\./', $_POST['save_as']) )
	{
		if( !in_array(basename($_POST['save_as']), $always_allow_files) ) message('Error: Filenames may not start with a period. Please go back and correct this error before trying to save the document.');
	}
	if( isset($_POST['save_as']) && !in_array(preg_replace('/.*(\\.\\w*)$/i', '$1', basename($_POST['save_as'])), $allowed_files) )
	{
  		if( !in_array(basename($_POST['save_as']), $always_allow_files) ) message('Error: You are not allowed to save files with this extension. Please go back and specify a valid filename.</p><p>Allowed extensions are: '.implode(', ', $allowed_files).'');
	}
	if(isset($_GET['chdir']) && trim($_GET['chdir']) == '') message('Incorrect usage of script. Parameters cannot be left empty &hellip;');
	if(!file_exists($fte)) message('Error: File could not be found.');
}
if(isset($_POST['savefile']) || isset($_POST['savefile2']))
{
	if( isset($_POST['fredit']) || isset($_GET['saveeditmode']))
	{
		$towrite = $_POST['code'];
	}
	else
	{
		file_editor_del_line();
		$i=0;
		foreach($_POST as $key => $value)
		{
			if(strpos($key, 'code') !== false)
			{
				$towrite[$i] = $value;
				$i++;
			}
		}
		$towrite = @implode("\n", $towrite);
	}
	if(defined('RESTORE_FILE') && isset($_POST)){}
	if(!is_dir($backup_root.'data/')) mkdir($backup_root.'data/', 0777);
	if(!is_dir($backup_root.'data/'.$backup_date.'/')) mkdir($backup_root.'data/'.$backup_date.'/', 0777);
	$backup_data = ''."\n".'<START ID="'.$backup_data_file.'">'.basename($_POST['save_as']).'|'.$curdir.'|'.time().'|'.$backup_root.'data/'.$backup_date.'/'.$backup_data_file.'</END>';
	$fh = fopen($backup_root.$backup_index, 'a+');
	$fh2 = fopen($backup_root.'data/'.$backup_date.'/'.$backup_data_file, 'wb');
	$fileineditor = file_get_contents($fte);
	@fwrite($fh, $backup_data);
	@fwrite($fh2, $fileineditor);
	@fclose($fh);
	@fclose($fh2);
	$fh = @fopen($curdir.'/'.basename($_POST['save_as']), 'wb');
	@fwrite($fh, $towrite);
	@fclose($fh);
	if(isset($_POST['savefile']) && !isset($_POST['saveeditmode']))
	{
		if(!isset($_SERVER['HTTP_REFERER'])) $dest = FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir.'&amp;edit='.basename($_GET['edit']);
		else $dest = $_SERVER['HTTP_REFERER'];
		redirect($dest, 'The file was successfully saved. Redirecting you back to the editor &hellip;');
	}
	if(isset($_POST['savefile2'])) redirect(FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir, 'Successfully saved the file. You are now being redirected back to the directory you came from &hellip;');
	$savestatus = '<strong>'.basename($_POST['save_as']).'</strong> successfully saved ('.date("M. d. Y, H:i:s").')';
}

function file_editor_restore()
{
	global $backup_date, $backup_data_file, $backup_root, $backup_index, $curdir;
	if(!isset($_POST['submitbackupform']) && !isset($_POST['restorefilenow'])) return;
	if(!isset($_SERVER['HTTP_REFERER'])) $dest = FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir.'&amp;edit='.$_GET['edit'];
	else $dest = $_SERVER['HTTP_REFERER'];
	foreach($_POST as $key => $value)
	{
		if( strpos($key, 'backuprestore') !== false || strpos($key, 'backupview') !== false || strpos($key, 'backuppurge') !== false )
		{
			$backup_action = $key;
			$filetoprocess = preg_replace('/\\D*(\\d*).*/', '$1', $backup_action);
			$fileaction = preg_replace('/^(\\D*).*/', '$1', $backup_action);
		}
	}
	$get_backup_index_tmp = file_get_contents($backup_root.$backup_index);
	$get_backup_index = explode('END>', $get_backup_index_tmp);
	if($fileaction == 'backupview')
	{
		define('RESTORE_FILE', $filetoprocess);
	}
	if(isset($_POST['purgeall']) && isset($_POST['purgedays']))
	{
		if(!is_numeric($_POST['purgedays'])) message('Error: Invalid number of days supplied');
		$delfrom = time()-((intval($_POST['purgedays'])*24)*3600);
		$i=0;
		foreach($get_backup_index as $key => $value)
		{
			if (preg_match('/<START.*?ID="(\\d*)">(.*)\\|(.*)\\|(\\d*)\\|(.*)<\//', $value, $regs))
			{
				$fid = $regs[1];
				$fname = $regs[2];
				$fpath = $regs[3];
				$ftime = $regs[4];
				$fdata = $regs[5];
			}
			if( $fname == basename($_GET['edit']) && $fpath == $curdir && $ftime < $delfrom && trim($value) != '' )
			{
				@unlink($fdata) or message('Error deleting file: <strong>'.$fdata.'</strong>');
				$write = 1;
			}
			else
			{
				if(trim($value) != '')
				{
					$towrite[$i] = trim($value).'END>';
					$i++;
				}
			}
		}
		if(isset($write))
		{
			$towrite = @implode("\n", $towrite);
			$rmfh = @fopen($backup_root.$backup_index, 'wb');
			@fwrite($rmfh, $towrite);
			@fclose($rmfh);
			redirect($dest, 'Finished purging all backup copies older then <strong>'.$_POST['purgedays'].'</strong> days. You will now be redirected back to the editor &hellip;');
		}
		message('No backup copies of this file was older then <strong>'.$_POST['purgedays'].'</strong> days. Please go back and try entering a lower number in the "purge all" dialogue.');
	}
	if( isset($fileaction) || isset($_POST['restorethisfile']) && isset($_POST['restorefilenow']) )
	{
		foreach($get_backup_index as $key => $value)
		{
			if (preg_match('/<START.*?ID="(\\d*)">(.*)\\|(.*)\\|(\\d*)\\|(.*)<\//', $value, $regs))
			{
				$fid = $regs[1];
				$fname = $regs[2];
				$fpath = $regs[3];
				$ftime = $regs[4];
				$fdata = $regs[5];
			}
			if($fid == $filetoprocess || $fid == $_POST['restorethisfile'])
			{
				if($fileaction == 'backuprestore' || isset($_POST['restorefilenow']) )
				{
					$fh = fopen($fpath.$fname, 'wb');
					$content = file_get_contents($fdata);
					fwrite($fh, $content) or message('Error restoring file');
					redirect($dest, 'Successfully restored the file. Redirecting you back to the editor where the newly restored copy will be available for editing &hellip;');
				}
				elseif($fileaction == 'backuppurge')
				{
					if(is_file($fdata))
					{
						@unlink($fdata) or message('Error deleting file: <strong>'.$fdata.'</strong>');
						$i=0;
						foreach($get_backup_index as $value)
						{
							if(!preg_match('/<START.*?ID="'.$filetoprocess.'">.*\\|.*\\|\\d*\\|.*<\//', $value) && trim($value) != '')
							{
								$tokeep[$i] = trim($value).'END>';
								$i++;
							}
						}
						$tmp = implode("\n", $tokeep);
						$rmfh = fopen($backup_root.$backup_index, 'wb');
						fwrite($rmfh, $tmp);
						fclose($rmfh);
						redirect($dest, 'Successfully purged the backup copy. You will now be redirected back to the editor &hellip;');
					}
				}
			}
		}
	}
}

function file_editor_restore_filelist ()
{
	global $backup_date, $backup_data_file, $backup_root, $backup_index, $curdir;
	$get_backup_index = file_get_contents($backup_root.$backup_index);
	$get_backup_index = explode('END>', $get_backup_index);
	$get_backup_index = array_reverse($get_backup_index);
	$table = '<table id="fetable2" class="restoretable" cellspacing="0" cellpadding="0">'."\n".'';
	$table .= '<tr><th>Filename</th><th>Path</th><th>Time</th><th>View</th><th>Restore</th><th>Purge</th></tr>'."\n".'';
	foreach($get_backup_index as $value)
	{
		if (preg_match('/<START.*?ID="(\\d*)">(.*)\\|(.*)\\|(\\d*)\\|(.*)<\//', $value, $regs))
		{
					$fid = $regs[1];
					$fname = $regs[2];
					$fpath = $regs[3];
					$ftime = $regs[4];
					$fdata = $regs[5];
		}
		if( $fname == basename($_GET['edit']) && $fpath == $curdir )
		{
			$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
	    		$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
			$md5class = '';
	    		if(@md5_file($fdata) == @md5_file($curdir.$_GET['edit'])) $md5class = ' feidenticalcopy';
			$table .= '<tr class="'.$vtbg.$md5class.'"><td>'.$fname.'</td>';
			$table .= '<td>'.$fpath.'</td>';
			$table .= '<td>'.format_time($ftime).'</td>';
			$table .= '<td><input type="submit" class="b1" name="backupview'.$fid.'" value="View content" /></td>';
			$table .= '<td><input type="submit" class="b1" name="backuprestore'.$fid.'" value="Restore now" /></td>';
			$table .= '<td><input type="submit" class="b1" name="backuppurge'.$fid.'" value="Purge file" /></td></tr>'."\n".'';
		}
	}
	$table .= '</table>';
	echo '<p>Purge all backups of this file older than<input type="text" name="purgedays" size="3" value="7" />days<input type="submit" class="b1" name="purgeall" value="Purge" /></p>';
	echo $table;
}

function file_editor_chkfiletype($curfile = '')
{
	global $allowed_files, $always_deny_files, $always_allow_files;
	if ( in_array(preg_replace('/.*(\\.\\w*)$/i', '$1', basename($curfile)), $allowed_files) && !in_array(basename($curfile), $always_deny_files) || in_array(basename($curfile), $always_allow_files) ) return true;
	else return false;
}

function file_editor_grab_contents()
{
	global $curdir, $fte, $filetoprocess, $backup_index, $backup_root;
	if(isset($_POST['save_as']))
	{
		$fte = $curdir.basename($_POST['save_as']);
	}
	if(defined('RESTORE_FILE'))
	{
		$get_backup_index_tmp = file_get_contents($backup_root.$backup_index);
		$get_backup_index = explode('END>', $get_backup_index_tmp);
		foreach($get_backup_index as $key => $value)
		{
			if (preg_match('/<START.*?ID="(\\d*)">(.*)\\|(.*)\\|(\\d*)\\|(.*)<\//', $value, $regs))
			{
				$fid = $regs[1];
				$fname = $regs[2];
				$fpath = $regs[3];
				$ftime = $regs[4];
				$fdata = $regs[5];
			}
			if($fid == RESTORE_FILE)
			{
				unset($get_backup_index);
				$output = explode("\n", file_get_contents($fdata));
				for($i=0; $i < count($output); $i++)
				{
					$findlength[$i] =  intval(strlen($output[$i]));
				}
				sort($findlength, SORT_NUMERIC);
				$printout = '<div id="fecodearea">';
				$printout .= '<table id="fetable" cellpadding="0" cellspacing="0">';
				$printout .= '<tr><th class="fekey">Line</th><th>Data</th></tr>';
				foreach($output as $key => $value)
				{
					$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
					$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
					$printout .= '<tr class="'.$vtbg.'"><td class="feline"><label>'.($key+1).':</label></td><td><input class="felineedit" name="preview" type="text" size="'.($findlength[count($findlength)-1]+1024).'" value="'.convert_htmlspecialchars($value).'" disabled="disabled" /></td></tr>';
				}
				$printout .= '</table></div>';
				$printout .= '<ul><li><input type="submit" name="returntoeditor" value="Return to editor" /><input type="hidden" name="restorethisfile" value="'.$fid.'" /><input type="submit" name="restorefilenow" value="Restore this backup copy now" /></li></ul>';
				echo $printout;
				return;
			}
		}
	}
	if(isset($_POST['fredit']) || isset($_POST['saveeditmode']) && !isset($_POST['lnedit']))
	{
		unset($printout);
		$printout = '<div>';
		$printout .= '<textarea id="fetextedit" name="code" rows="40" cols="150" tabindex="1" wrap="off">'.convert_htmlspecialchars(file_get_contents($fte)).'</textarea>';
		$printout .= '</div>';
		echo $printout;
		return;
	}
	$output = explode("\n", file_get_contents($fte));
	for($i=0; $i < count($output); $i++)
	{
		$findlength[$i] =  intval(strlen($output[$i]));
	}
	sort($findlength, SORT_NUMERIC);
	$canwrite = '';
	if(!is_writable($fte)) $canwrite = 'disabled="disabled"';
	$printout = '<div id="fecodearea">';
	$printout .= '<table id="fetable" cellpadding="0" cellspacing="0">';
	$printout .= '<tr><th>Del</th><th class="fekey">Line</th><th>Data</th></tr>';
	foreach($output as $key => $value)
	{
		$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
		$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
		$printout .= '<tr class="'.$vtbg.'"><td class="fedelete"><input type="checkbox" '.$canwrite.' name="linechecked'.($key+1).'" value="code'.($key+1).'" /></td><td class="feline"><label for="code'.($key+1).'">'.($key+1).':</label></td><td><input id="code'.($key+1).'" class="felineedit" name="code'.($key+1).'" type="text" '.$canwrite.' size="'.($findlength[count($findlength)-1]+1024).'" value="'.convert_htmlspecialchars($value).'" /></td></tr>';
	}
	$printout .= '</table></div>';
	echo $printout;
}

function file_editor_del_line()
{
	global $fte;
	if(isset($_POST['save_as']))
	{
		$output = explode("\n", file_get_contents($fte));
		$akeys = array_keys($_POST);
		foreach($output as $key => $value)
		{
			if($_POST['linechecked'.($key+1).''] != $akeys[$value])
			{
				unset($_POST['code'.($key+1).'']);
			}
		}
	}
}

file_editor_restore();
if( isset($_GET['edit']) && !isset($_POST['restore']) && !isset($_GET['restorefile']) && !isset($_GET['restoredate']) ):
  generate_admin_menu($plugin);
?>
	<div id="exampleplugin" class="blockform">
		<h2><span>File Editor</span></h2>
		<div class="box">
			<div class="inbox">
				<p>Warning: Please be careful when editing files, as it could break your PowerBB installation.</p>
				<p><a href="#feformbackup">Controls for restoring a backup copy</a> of the file currently loaded, are available below the text editor.</p>
				<p>Note: Backup copies styled <span class="rowodd feidenticalcopy">this</span> and <span class="roweven feidenticalcopy">this</span> way is identical to the file you're currently editing (MD5 checksum matches).</p>
			</div>
		</div>
		<h2 class="block2"><span><?php if(isset($savestatus)) echo $savestatus; elseif(defined('RESTORE_FILE')) echo 'Viewing backup copy of '.$curdir.basename($_GET['edit']).''; elseif(isset($_GET['write'])) echo 'Viewing file: <strong>'.$curdir.basename($_GET['edit']).'</strong> (Read Only)'; else echo 'Editing file: <strong>'.$curdir.basename($_GET['edit']).'</strong>'; ?></span></h2>
		<div class="box">
			<form id="feformedit" method="post" action="<?php echo convert_htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
				<div class="inform">
					<fieldset>
						<div class="infldset">
<?php if(!defined('RESTORE_FILE')): ?>
				   <div><label for="save_as"><?php if(!isset($_GET['write'])) echo 'Save file as (in the current directory) &hellip;'; else echo '<strong>File is not writable</strong>'; ?></label><input type="text" id="save_as" name="save_as" value="<?php if(!isset($_POST['save_as'])) { echo basename($_GET['edit']); } else {echo basename($_POST['save_as']); } ?>" size="50" tabindex="2" <?php if(isset($_GET['write'])) echo 'disabled="disabled"' ?> style="margin-bottom:1em" /></div>
<?php endif; ?>
              <?php file_editor_grab_contents(); ?>
<?php if(!isset($_GET['write']) && !defined('RESTORE_FILE')): ?>
              <br /><div>
              <ul id="febuttons">
                <li>
                <input type="submit" class="b1" name="savefile" value="Save file" tabindex="3" />
                <input type="submit" class="b1" name="savefile2" value="Save &amp; return to file browser" tabindex="4" />
                <input type="reset" class="b1" value="Reset" tabindex="5" />
              <?php
              if(!isset($_POST['fredit'])) {
                echo '| <input type="submit" class="b1" name="fredit" value="Switch to free edit mode" tabindex="6" />';
              } else {
                echo '| <input type="submit" class="b1" name="lnedit" value="Switch to line edit mode" tabindex="6" />';
              }
              ?>
                </li>
<?php if(isset($_POST['fredit']) || isset($_POST['saveeditmode']) && !isset($_POST['lnedit'])): ?>
                <li>
                  <input type="checkbox" id="saveeditmode" name="saveeditmode" tabindex="7" <?php if(isset($_POST['saveeditmode'])) echo 'checked="checked"'; ?> /><label id="saveeditmodelabel" for="saveeditmode">Continue editing file in free edit mode (note: no redirect on save)</label>
                </li>
<?php endif; ?>
              </ul>
              </div>
<?php endif; ?>
            </div>
					</fieldset><br />
<?php echo '<p><a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$_GET['chdir'].'">Return to file browser</a></p>'; ?>
				</div>
			</form>
		</div>
	</div>
<?php
endif;
if( !isset($_GET['edit']) ):
generate_admin_menu($plugin);
?>
	<div id="exampleplugin" class="blockform">
		<h2><span>File Editor</span></h2>
		<div class="box">
			<div class="inbox">
				<p>Warning: Please be careful when editing files, as it could break your PowerBB installation.</p>
			</div>
		</div>
		<h2 class="block2"><span>Select the file you want to edit</span></h2>
		<div class="box">
			<form id="fefiledel" method="post" action="<?php echo convert_htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
				<div class="inform">
					<fieldset>
						<div class="infldset">
<?php
$i=0;
$dh = @opendir($curdir);
while (false !== ($file = readdir($dh))):
	if(!is_dir($curdir.$file) && !preg_match('/^\\./m', $file) || in_array($file, $always_allow_files))
	{
		$filelist[$i] = basename($file);
	}
	if(is_dir($curdir.$file) && !preg_match('/^\\./m', $file))
	{
		$dirlist[$i] = basename($file);
	}
	$i++;
	endwhile;
	echo ''."\t".'<ul id="femodlist">'."\n\t\t\t\t\t\t\t".'';
	if(isset($tmpdir))
	{
		$i=0;
		foreach($tmpdir as $value)
		{
			if(!isset($navlnk[0]))
			{
				$navuri[$i] = $value;
				$navlnk[$i] = '<a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$homelink.$value.'/">'.$value;
			}
			else
			{
				$navuri[$i] = $value;
				$navlnk[$i] = '<a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$homelink.implode('/',$navuri).'/">'.$value;
			}
			$i++;
		}
	}
	echo '<li id="felistlocation">Navigation: <strong><a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir=./">root</a>/'.@implode('</a>/', $navlnk).'</a></strong></li>';
	if(isset($dirlist))
	{
		sort($dirlist);
		foreach($dirlist as $key => $file)
		{
			$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
			$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
			echo ''."\t".'<li class="fedirlist '.$vtbg.'"><a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir.$file.'">'.$file.'</a></li>'."\n\t\t\t\t\t\t\t".'';
		}
	}
	if(isset($filelist))
	{
		sort($filelist);
		foreach($filelist as $key => $file)
		{
			if(file_editor_chkfiletype($file) !== false)
			{
				$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
				$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';
				if(is_writable($curdir.'/'.$file) && is_file($curdir.'/'.$file))
				{
					echo ''."\t".'<li class="fewritable '.$vtbg.'"><a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir.'&amp;edit='.$file.'">'.$file.'</a></li>'."\n\t\t\t\t\t\t\t".'';
				}
				else
				{
					echo ''."\t".'<li class="fenotwritable '.$vtbg.'"><a href="'.FORUM_ROOT.'admin/admin_loader.php?plugin='.$_GET['plugin'].'&amp;chdir='.$curdir.'&amp;edit='.$file.'&amp;write=no">'.$file.' (Not writable)</a></li>'."\n\t\t\t\t\t\t\t".'';
				}
			}
		}
	}
	echo '</ul>'."\n".'';
?>
						</div>
					</fieldset>
<?php echo '<p><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>'; ?>
				</div>
			</form>
		</div>
	</div>
<?php
endif;
if( isset($_GET['edit']) && isset($_GET['chdir']))
{
?>
	<div class="blockform">
		<h2 class="block2"><span>The following backup copies were found:</span></h2>
		<div class="box">
			<form id="feformbackup" method="post" action="<?php echo convert_htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
				<div class="inform">
					<fieldset>
						<div class="infldset">
							<div>
								<?php file_editor_restore_filelist() ?>
								<input type="hidden" name="submitbackupform" value="1" />
							</div>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
<?php
}
?>