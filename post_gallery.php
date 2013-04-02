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
define('QUIET_VISIT', 1);
require FORUM_ROOT.'include/common.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)";
define('ALLOW_INDEX', 1);
require FORUM_ROOT.'header.php';
require FORUM_ROOT.'include/parser.php';
require FORUM_ROOT.'include/modules/mod_image_upload.php';

function check_parameters()
{
	global $configuration;
	if (!isset($_GET['pid']) || !isset($_GET['filename'])) error('Invalid image parameters', __FILE__, __LINE__);
	$pid = $_GET['pid'];
	$filename = $_GET['filename'];
	$source = $configuration['o_upload_path'].$pid.'/';
	if (!is_dir($source)) error('No images for this post id', __FILE__, __LINE__);
	if (!is_file($source.$filename)) error('This image does not exist', __FILE__, __LINE__);
}

function show_gallery()
{
	global $configuration, $lang_common;
	if (!check_mod_config()) error('Image Upload is not configured correctly', __FILE__, __LINE__);
	check_parameters();
	$pid = $_GET['pid'];
	$filename = $_GET['filename'];
	$source = $configuration['o_upload_path'].$pid.'/';
	$contents = get_dir_contents($source, true);
	$nav = array();
	$ulimit = count($contents) - 1;
	$idx = array_search($filename, $contents);
	if ($idx > 0)
	{
		$before = urlencode($contents[$idx - 1]);
		$nav[] = "\t\t\t\t\t\t<a href='post_gallery.php?pid=$pid&amp;filename=$before'>";
	}
	$nav[] = "\t\t\t\t\t\t\t&lt; ".$lang_common['Previous Image'];
	if (isset($before))
	{
		$nav[] = "\t\t\t\t\t\t</a>";
	}
	$nav[] = "\t\t\t\t\t\t | <a href='view_topic.php?pid=$pid#p$pid'>".$lang_common['Back to Topic']."</a> | ";
	if ($idx < $ulimit)
	{
		$after = urlencode($contents[$idx + 1]);
		$nav[] = "\t\t\t\t\t\t<a href='post_gallery.php?pid=$pid&amp;filename=$after'>";
	}
	$nav[] = "\t\t\t\t\t\t\t".$lang_common['Next Image']." &gt;";
	if (isset($after))
	{
		$nav[] = "\t\t\t\t\t\t</a>";
	}
	$nav = implode("\n", $nav);
	$filename = urlencode($filename);
	return "<br />\n".$nav."\n\t\t\t\t\t\t<br />\n\t\t\t\t\t\t<br />\n\t\t\t\t\t\t<img src='show_image.php?pid=$pid&amp;filename=$filename' alt='$filename' />\n\t\t\t\t\t\t<br />\n\t\t\t\t\t\t<br />\n".$nav."\n\t\t\t\t\t\t<br />\n\t\t\t\t\t\t<br />\n";
}
?>
<div class="block">
	<h2><span>Post Gallery</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing='0' cellpadding='0' border='0'>
				<tr>
					<td align='center' valign='top' style='border: none; padding: 0px'>
						<?php echo show_gallery(); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php'; ?>