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

if (!defined('IN_FORUM')) exit;
$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
ob_end_clean();
ob_start();
$db->end_transaction();
$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<forum_footer>', $tpl_temp, $tpl_main);
ob_end_clean();
while (preg_match('#<forum_include "([^/\\\\]*?)">#', $tpl_main, $cur_include))
{
	if (!file_exists(FORUM_ROOT.'include/user/'.$cur_include[1])) error('Unable to process user include &lt;forum_include "'.htmlspecialchars($cur_include[1]).'"&gt; from template main.tpl. There is no such file in folder /include/user/');
	ob_start();
	include FORUM_ROOT.'include/user/'.$cur_include[1];
	$tpl_temp = ob_get_contents();
	$tpl_main = str_replace($cur_include[0], $tpl_temp, $tpl_main);
	ob_end_clean();
}
$db->close();
exit($tpl_main);
?>