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
print('<div class="block"><h2><span>Games</span></h2><div class="box"><div class="inbox">');
if (!$PowerBB_user['is_guest'])
{
	print("<br />");
?>
	<a href="play.php?act=asteroids"><img src="include/modules/mod_games/img/asteroids.gif" width="50" height="50" alt="Asteroids" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=asteroids">Asteroids</a> : space game<br /><br />
	<a href="play.php?act=breakout"><img src="include/modules/mod_games/img/breakout.gif" width="50" height="50" alt="Breakout" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=breakout">Breakout</a> : no description<br /><br />
	<a href="play.php?act=hexxagon"><img src="include/modules/mod_games/img/hexxagon.gif" width="50" height="50" alt="Hexxagon" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=hexxagon">Hexxagon</a> : no description<br /><br />
	<a href="play.php?act=invaders"><img src="include/modules/mod_games/img/invaders.gif" width="50" height="50" alt="Invaders" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=invaders">Invaders</a> : no description<br /><br />
	<a href="play.php?act=moonlander"><img src="include/modules/mod_games/img/moonlander.gif" width="50" height="50" alt="Moonlander" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=moonlander">Moonlander</a> : no description<br /><br />
	<a href="play.php?act=pacman"><img src="include/modules/mod_games/img/pacman.gif" width="50" height="50" alt="Pacman" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=pacman">Pacman</a> : one of the first video games<br /><br />
	<a href="play.php?act=simon"><img src="include/modules/mod_games/img/simon.gif" width="50" height="50" alt="Simon" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=simon">Simon</a> : memory game<br /><br />
	<a href="play.php?act=snake"><img src="include/modules/mod_games/img/snake.gif" width="50" height="50" alt="Snake" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=snake">Snake</a> : move your snake (Nokia-style)<br /><br />
	<a href="play.php?act=tetris"><img src="include/modules/mod_games/img/tetris.gif" width="50" height="50" alt="Tetris" style="vertical-align: middle; border: 0px;" /></a> <a href="play.php?act=tetris">Tetris</a> : everybody knows Tetris<br /><br />
<?php
}
else print("<br />You must be logged in to access this section.<br /><br />");
print('</div></div></div><br />');
require (FORUM_ROOT.'footer.php');
?>