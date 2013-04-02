<?php
if (!defined('FORUM_ROOT')) exit();
require FORUM_ROOT.'include/modules/phpThumb/phpthumb.class.php';

function check_mod_config()
{
	global $configuration;
	if (!isset($configuration['o_upload_path'])) return false;
	if (!is_dir($configuration['o_upload_path'])) return false;
	if (!is_writable($configuration['o_upload_path'])) return false;
	if (!isset($configuration['o_table_cols'])) return false;
	if ($configuration['o_table_cols'] <= 0) return false;
	return true;
}

function get_file_extension($filename)
{
	$filename = basename($filename);
	$ext = substr($filename, strrpos($filename, '.') + 1);
	return $ext;
}

function get_mime_type($filename)
{
	if (!is_file($filename)) return '';
	list($width, $height, $type, $attr) = getimagesize($filename);
	return image_type_to_mime_type($type);
}
function get_dir_contents($dir, $remove_thumbnails = false)
{
	$contents = array();
	if (!is_dir($dir)) return $contents;
	$dh = opendir($dir);
	while (false !== ($file = readdir($dh)))
	{
		if ($file == '.' || $file == '..' || ($remove_thumbnails && $file == 'thumbnails')) continue;
		$contents[] = $file;
	}
	closedir($dh);
	sort($contents);
	return $contents;
}

function get_dir_file_count($dir)
{
	return count(get_dir_contents($dir));
}

function rmdirr($dir)
{
	$dir = realpath($dir).'/';
	if (!is_dir($dir)) return false;
	$files = get_dir_contents($dir);
	foreach ($files as $file)
	{
		$file = $dir.$file;
		if (is_file($file))
		{
			if (!@unlink($file)) return false;
		}
		else
		{
			if (!rmdirr($file)) return false;
		}
	}
	if (!@rmdir($dir)) return false;
	return true;
}

function show_problems()
{
	global $configuration;
	$log = array();
	if (!check_mod_config())
	{
		$log[] = "Image uploading is not configured correctly!";
		return $log;
	}
	$root = $configuration['o_upload_path'];
	$pids = get_dir_contents($root);
	foreach ($pids as $pid)
	{
		$cur_dir = $root.$pid.'/';
		$images = get_dir_contents($cur_dir, true);
		$icount = count($images);
		if (!is_dir($cur_dir.'thumbnails'))
		{
			$log[] = "Post $pid: Error in directory structure";
			continue;
		}
		if (!is_writable($cur_dir))
		{
			$log[] = "Post $pid: Directory isn't writable";
			continue;
		}
		if (!is_writable($cur_dir.'thumbnails'))
		{
			$log[] = "Post $pid: Thumbnails directory isn't writable";
			continue;
		}
		$thumbnails = get_dir_contents($cur_dir.'thumbnails');
		$tcount = count($thumbnails);
		if ($icount == 0)
		{
			$log[] = "Post $pid: No attached images";
			continue;
		}
		if ($tcount == 0)
		{
			$log[] = "Post $pid: Missing thumbnails";
			continue;
		}
		if ($icount != $tcount)
		{
			$log[] = "Post $pid: Image count doesn't match thumbnail count";
			continue;
		}
		$idx = 0;
		$error = false;
		for ($idx = 0; $idx < $icount; $idx++)
		{
			if ($images[$idx] != $thumbnails[$idx])
			{
				$error = true;
				break;
			}
		}
		if ($error)
		{
			$log[] = "Post $pid: Error in filename synchronization";
			continue;
		}
	}
	if (count($log) == 0) $log[] = "No problems Found!";
	return $log;
}

function delete_orphans()
{
	global $configuration, $db;
	$log = array();
	if (!check_mod_config())
	{
		$log[] = "Image uploading is not configured correctly!";
		return $log;
	}
	$root = $configuration['o_upload_path'];
	$pids = get_dir_contents($root);
	$sql = 'SELECT p.id FROM '.$db->prefix.'posts AS p WHERE (';
	$first = true;
	foreach ($pids as $pid)
	{
		if ($first) $first = false;
		else $sql .= ' or ';
		$sql .= 'p.id='.$pid;
	}
	if ($first)
	{
		$log[] = "No problems Found!";
		return $log;
	}
	$sql .= ')';
	$result = $db->query($sql) or error('Unable to execute query', __FILE__, __LINE__, $db->error());
	while ($pid = $db->fetch_assoc($result))
	{
		$idx = array_search($pid, $pids);
		array_splice($pids, $idx, 1);
	}
	foreach ($pids as $pid)
	{
		if (rmdirr($root.$pid.'/')) $log[] = "Post $pid: No post with this id - Deleted";
		else $log[] = "Post $pid: No post with this id - Unable to Delete";
	}
	if (count($log) == 0) $log[] = "No problems Found!";
	return $log;
}

function process_uploaded_images($pid)
{
	global $configuration, $lang_common;
	if (!isset($_FILES['image_upload']['error'])) return '';
	if (!check_mod_config()) return '';
	$result = $lang_common['Upload Results'].":<br />\n";
	$total_uploaded = 0;
	$dest = $configuration['o_upload_path'].$pid.'/';
	$allowed_ext = $configuration['o_allowed_ext'];
	$allowed_ext = explode(',', $allowed_ext);
	foreach ($_FILES['image_upload']['error'] as $key => $error)
	{
		if ($error == UPLOAD_ERR_OK)
		{
			$tmp_name = $_FILES["image_upload"]["tmp_name"][$key];
			$name = $_FILES["image_upload"]["name"][$key];
			$file_ext    = get_file_extension($name);
			if (!in_array(strtolower($file_ext), $allowed_ext) || $file_ext == '')
			{
				$result .= "$name ".$lang_common['Extension Banned'].".<br />\n";
				continue;
			}
			if (filesize($tmp_name) > $configuration['o_max_size'])
			{
				$result .= "$name ".$lang_common['Size Too Big'].".<br />\n";
				continue;
			}
			list($width, $height, $type, $attr) = getimagesize($tmp_name);
			if ($width > $configuration['o_max_width'] || $height > $configuration['o_max_height'])
			{
				$result .= "$name ".$lang_common['Dim Too Big'].".<br />\n";
				continue;
			}
			if (!is_dir($dest))
			{
				mkdir($dest);
				chmod($dest, 0777);
				mkdir($dest.'thumbnails');
				chmod($dest.'thumbnails', 0777);
			}
			move_uploaded_file($tmp_name, $dest.$name);
			chmod($dest.$name, 0666);
			$phpThumb = new phpThumb();
			$phpThumb->setSourceFilename($dest.$name);
			$phpThumb->w = $configuration['o_thumb_width'];
			$phpThumb->h = $configuration['o_thumb_height'];
			$phpThumb->config_output_format = 'jpeg';
			$phpThumb->config_error_die_on_error = false;
			if ($phpThumb->GenerateThumbnail()) $phpThumb->RenderToFile($dest.'thumbnails/'.$name);
			$total_uploaded++;
		}
	}
	$result .= $lang_common['Uploaded'].' '.$total_uploaded.' '. $lang_common['Images']."<br /><br />\n";
	return $result;
}

function process_deleted_images($pid)
{
	global $configuration;
	if (!isset($_POST['delete_image'])) return;
	if (!check_mod_config()) return;
	$target_dir = $configuration['o_upload_path'].$pid.'/';
	foreach ($_POST['delete_image'] as $image)
	{
		$image = basename($image);
		if (!is_file($target_dir.$image) || !is_file($target_dir.'thumbnails/'. $image)) continue;
		unlink($target_dir.$image);
		unlink($target_dir.'thumbnails/'.$image);
	}
	if (count(get_dir_contents($target_dir.'thumbnails/')) == 0)
	{
		rmdir($target_dir.'thumbnails/');
		rmdir($target_dir);
	}
}

function delete_images($pid)
{
	global $configuration;
	if (!check_mod_config()) return;
	$target_dir = $configuration['o_upload_path'].$pid.'/';
	if (!is_dir($target_dir)) return;
	$images = get_dir_contents($target_dir.'thumbnails/');
	foreach ($images as $image)
	{
		unlink($target_dir.'thumbnails/'.$image);
	}
	rmdir($target_dir.'thumbnails/');
	$images = get_dir_contents($target_dir);
	foreach ($images as $image)
	{
		unlink($target_dir.$image);
	}
	rmdir($target_dir);
}

function show_post_images($pid, $edit = false)
{
	global $configuration, $lang_common;
	if (!check_mod_config()) return;
	if (!is_dir($configuration['o_upload_path'].$pid)) return;
	$images = get_dir_contents($configuration['o_upload_path'].$pid.'/thumbnails/');
	$image_count = count($images);
	$col = 0;
	$idx = 0;
	$column_count = $configuration['o_table_cols'];
	$column_width = floor(100/$column_count);
	$image_height = $configuration['o_thumb_height'];
	$row_height   = $image_height + 6;
	if ($edit) $row_height += 20;
	$output[] = '';
	if (!$edit) $output[] = "\t\t\t\t\t<br />";
	$output[] = "\t\t\t\t\t<div class='image_thumbnails'>";
	$output[] = "\t\t\t\t\t\t<fieldset>";
	$output[] = "\t\t\t\t\t\t\t<legend>$lang_common[Uploaded_Images]</legend>";
	while ($idx < $image_count)
	{
		$output[] = "\t\t\t\t\t\t\t<div class='image_row' style='height: ". $row_height."px;'>";
		for ($col = 0; $col < $column_count; $col++, $idx++)
		{
			if ($col == $column_count - 1) $column_width--;
			if ($idx < $image_count)
			{
				$url_name = urlencode($images[$idx]);
				$output[] = "\t\t\t\t\t\t\t\t<span class='image_item' style='width: ". $column_width."%; height: $image_height"."px;'>";
				if (!$edit) $output[] = "\t\t\t\t\t\t\t\t\t<a href='post_gallery.php?". "pid=$pid&amp;filename=$url_name'>";
				$output[] = "\t\t\t\t\t\t\t\t\t\t<span class='image_thumbnail' ". "style=\"background-image: url('show_image.php?". "pid=$pid&amp;filename=$url_name&amp;preview=true'); ". ($edit ? '' : 'cursor: pointer;') . "\"></span>";
				if (!$edit) $output[] = "\t\t\t\t\t\t\t\t\t</a>";
				if ($edit) $output[] = "\t\t\t\t\t\t\t\t\t<label for='delete_image_$idx'>". "<input type='checkbox' name='delete_image[]' ". "id='delete_image_$idx' value='".urlencode($images[$idx]). "' /> Delete</label>";
				$output[] = "\t\t\t\t\t\t\t\t</span>";
			}
			if ($col == $column_count - 1) $column_width++;
		}
		$output[] = "\t\t\t\t\t\t\t</div>";
	}
	$output[] = "\t\t\t\t\t\t</fieldset>";
	$output[] = "\t\t\t\t\t</div>";
	$output[] = "\t\t\t\t\t<br />\n";
	echo implode("\n", $output);
}

function show_image_upload($cur_post, $edit = false)
{
	global $lang_common, $forum_user, $configuration;
	if (!check_mod_config()) return;
	$upload_slots = 0;
	if (isset($_POST['upload_slots'])) $upload_slots = $_POST['upload_slots'];
	if ($cur_post['image_upload'] || $forum_user['g_id'] == USER_ADMIN)
	{
		if ($edit) show_post_images($_GET['id'], true);
?>
		<fieldset>
			<legend><?php echo $lang_common['Image_Upload'] ?></legend>
			<div class="infldset">
				<div class="rbox">
					<p><?php echo $lang_common['image_upload_show']; ?>
					<select onchange="UpdateUploadSlots()" size="1" name="upload_slots">
<?php
		$upload_limit = $configuration['o_max_post_images'];
		if ($edit) $upload_limit -= get_dir_file_count( $configuration['o_upload_path'].$_GET['id']. '/thumbnails');
		for ($i = 0; $i <= $upload_limit; $i++)
		{
			echo "\t\t\t\t\t\t\t\t<option value='$i' ";
			if ($upload_slots == $i) echo 'selected="selected"';
			echo ">$i</option>\n";
		}
?>
					</select>
					<?php echo $lang_common['image_upload_slots']; ?></p>
					<div id="image_upload">
<?php
		for ($i = 0; $i < $upload_slots; $i++) echo "\t\t\t\t\t\t\t\t<p><input type='file' ". "name='image_upload[]' size='70' /></p>\n";
?>
					</div>
					<p>
						(<?php echo $lang_common['image_upload_limits'].': '.
						$configuration['o_max_post_images'].' '.
						$lang_common['image_upload_limits2'].' '.
						$configuration['o_max_width'].
						'x'.$configuration['o_max_height'].' '.
						$lang_common['image_upload_pixels'].
						' '.round($configuration['o_max_size']/1024); ?> KB)
					</p>
				</div>
			</div>
		</fieldset>
		<br />
<?php
	}
}
?>