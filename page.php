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
$result = $db->query("SELECT id, title, content FROM ".$db_prefix."pages WHERE id='".intval($_GET['id'])."'") or error('Unable to fetch page information', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$data = $db->fetch_assoc($result);
$page_title = convert_htmlspecialchars($configuration['o_board_name']) . ' / '. $data['title'];
define('ALLOW_INDEX', 1);
require FORUM_ROOT.'header.php';
$pattern = array('#\n\[page_title=([^\[]*?)\]#s', '#\n\[page_break\]#s');
$replace = array("\n\t\t".'</div>'."\n\t".'</div>'."\n".'</div>'."\n".'<div class="block">'."\n\t".'<h2><span>$1</span></h2>'."\n\t".'<div class="box">'."\n\t\t".'<div class="inbox">', "\n\t\t".'</div>'."\n\t".'</div>'."\n".'</div>'."\n".'<div class="block">'."\n\t".'<div class="box">'."\n\t\t".'<div class="inbox">');
$content = preg_replace($pattern, $replace, $data['content']);
?>
<div class="block">
	<h2><span><?php echo $data['title'] ?></span></h2>
	<div class="box">
		<div class="inbox">
<!--==================-->
<!--Start Page Content-->
<?php echo $content."\n" ?>
<!-- End Page Content -->
<!--==================-->
		</div>
	</div>
</div>
<?php require FORUM_ROOT.'footer.php';?>