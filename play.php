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
$page_title = convert_htmlspecialchars($configuration['o_board_name']).' / Games';
require (FORUM_ROOT.'header.php');
if(isset($_GET['act']))
{
	$game=$_GET['act'];
	print('<div class="block"><h2><span>Games : '.ucfirst($game).'</span></h2><div class="box"><div class="inbox" style="text-align: center;">');
	if (!$PowerBB_user['is_guest'])
	{
		print("<br />");
		?>
		<object type="application/x-shockwave-flash" data="include/modules/mod_games/<?php print($game); ?>.swf" width="500" height="500">
    		<param name="movie" value="include/modules/mod_games/<?php print($game); ?>.swf" />
    		You don't have the Macromedia Flash plugin, get it for free from <a href="http://www.macromedia.com">here</a>.
		</object>
		<br /><br />
		<a href="arcade.php">Return to games list</a><br /><br />
		<?php
	}
	else print("<br />You must be logged in to access this section.<br /><br />");
	print('</div></div></div><br />');
}
else print("An error has ocurred, please go back to <a href='arcade.php'>games list</a>.");
require (FORUM_ROOT.'footer.php');
?>