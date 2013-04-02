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
?>
<div id="brdfooter" class="block">
	<h2><span><?php echo $lang_common['Board footer'] ?></span></h2>
	<div class="box">
		<div class="inbox">
<?php
$footer_style = isset($footer_style) ? $footer_style : NULL;
if ($footer_style == 'message_list')
{
	if (!isset($_GET['box'])) $_GET['box'] = 0;
	if (!isset($_GET['p'])) $_GET['p'] = 0;
?>
			<dl id="searchlinks" class="conl">
				<dt><strong>PM links</strong></dt>
<?php
if ($new_messages) echo "\t\t\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'message_list.php?action=markall&amp;box='.$_GET['box'].'&amp;p='.$_GET['p'].'">'.$lang_pms['Mark all'].'</a></dd>'."\n";
if ($messages_exist) echo "\t\t\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'message_list.php?action=multidelete&amp;box='.$_GET['box'].'&amp;p='.$_GET['p'].'">'.$lang_pms['Multidelete'].'</a></dd>'."\n";
?>
			</dl>
<?php
}
if ($footer_style == 'index' || $footer_style == 'search')
{
	if (!$forum_user['is_guest'])
	{
		echo "\n\t\t\t".'<dl id="searchlinks" class="conl">'."\n\t\t\t\t".'<dt><strong>'.$lang_common['Search links'].'</strong></dt>'."\n\t\t\t\t".'<dd><a href="search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a></dd>'."\n";
		echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a></dd>'."\n";
		if ($configuration['o_subscriptions'] == '1') 
		{
		echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'search.php?action=show_subscriptions">'.$lang_common['Show subscriptions'].'</a></dd>'."\n";
		}
		echo "</dl><dl class=\"conr\">";
		echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'search.php?action=show_new">'.$lang_common['Show new posts'].'</a></dd>'."\n";
		echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'misc.php?action=markread">'.$lang_common['Mark all as read'].'</a></dd>'."\n";
		echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'search.php?action=show_user&amp;user_id='.$forum_user['id'].'">'.$lang_common['Show your posts'].'</a>
		</dd>'."\n\t\t\t";
		if (basename($_SERVER['PHP_SELF']) == 'view_forum.php') 
		echo '<dd><a href="'.FORUM_ROOT.'misc.php?action=markforumread&id='.$id.'">'.$lang_common['Mark forum as read'].'</a></dd>'."\n\t\t\t";
		elseif (basename($_SERVER['PHP_SELF']) == 'view_topic.php')
		echo '<dd><a href="'.FORUM_ROOT.'view_printable.php?id='.$id.'">'.$lang_common['Print version'].'</a></dd>';
		echo '</dl>'."\n";
	}
	else
	{
		if ($forum_user['g_search'] == '1')
		{
			echo "\n\t\t\t".'<dl id="searchlinks" class="conl">'."\n\t\t\t\t".'<dt><strong>'.$lang_common['Search links'].'</strong></dt><dd><a href="'.FORUM_ROOT.'search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a></dd>'."\n";
			echo "\t\t\t\t".'<dd><a href="'.FORUM_ROOT.'search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a></dd>'."\n\t\t\t".'</dl>'."\n";
		}
	}
}
else if ($footer_style == 'view_poll')
{
	echo "\n\t\t\t".'<div class="conl">'."\n";
	if ($configuration['o_quickjump'] == '1')
	{
		@include FORUM_ROOT.'cache/cache_quickjump_'.$forum_user['g_id'].'.php';
		if (!defined('QJ_LOADED'))
		{
			require_once FORUM_ROOT.'include/cache.php';
			generate_quickjump_cache($forum_user['g_id']);
			require FORUM_ROOT.'cache/cache_quickjump_'.$forum_user['g_id'].'.php';
		}
	}
	if ($is_admmod)
	{
		echo "\t\t\t".'<dl id="modcontrols"><dt><strong>'.$lang_topic['Mod controls'].'</strong></dt><dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;tid='.$id.'&amp;p='.$p.'">'.$lang_common['Delete posts'].'</a></dd>'."\n";
		echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;move_topics='.$id.'">'.$lang_common['Move topic'].'</a></dd>'."\n";
		if ($cur_topic['closed'] == '1') echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;open='.$id.'">'.$lang_common['Open topic'].'</a></dd>'."\n";
		else echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;close='.$id.'">'.$lang_common['Close topic'].'</a></dd>'."\n";
		if ($cur_topic['sticky'] == '1') echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;unstick='.$id.'">'.$lang_common['Unstick topic'].'</a></dd></dl>'."\n";
		else echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate_poll.php?fid='.$forum_id.'&amp;stick='.$id.'">'.$lang_common['Stick topic'].'</a></dd></dl></div>'."\n";
	}
	echo "\t\t\t\n";
}
else if ($footer_style == 'view_forum' || $footer_style == 'view_topic')
{
	echo "\n\t\t\t".'<div class="conl">'."\n";
	if ($configuration['o_quickjump'] == '1')
	{
		@include FORUM_ROOT.'cache/cache_quickjump_'.$forum_user['g_id'].'.php';
		if (!defined('QJ_LOADED'))
		{
			require_once FORUM_ROOT.'include/cache.php';
			generate_quickjump_cache($forum_user['g_id']);
			require FORUM_ROOT.'cache/cache_quickjump_'.$forum_user['g_id'].'.php';
		}
	}
	echo "</div><div class=\"conr\">";
	if ($footer_style == 'view_forum' && $is_admmod) echo "\t\t\t".'<p id="modcontrols"><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;p='.$p.'">'.$lang_common['Moderate forum'].'</a></p>'."\n";
	else if ($footer_style == 'view_topic' && $is_admmod)
	{
		if ($cur_topic['closed'] == '2')
		{
			echo "\t\t\t".'<dl><dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;topic_yes='.$id.'">'.$lang_common['Valide topic'].'</a></dd></dl>'."\n";
		}
		else
	  	{
			echo "\t\t\t".'<dl id="modcontrols"><dt><strong>'.$lang_topic['Mod controls'].'</strong></dt><dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;tid='.$id.'&amp;p='.$p.'">'.$lang_common['Delete posts'].'</a></dd>'."\n";
			echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;move_topics='.$id.'">'.$lang_common['Move topic'].'</a></dd>'."\n";
			if ($cur_topic['closed'] == '1') echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;open='.$id.'">'.$lang_common['Open topic'].'</a></dd>'."\n";
			else echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;close='.$id.'">'.$lang_common['Close topic'].'</a></dd>'."\n";
			if ($cur_topic['sticky'] == '1') echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;unstick='.$id.'">'.$lang_common['Unstick topic'].'</a></dd></dl>'."\n";
			else echo "\t\t\t".'<dd><a href="'.FORUM_ROOT.'moderate.php?fid='.$forum_id.'&amp;stick='.$id.'">'.$lang_common['Stick topic'].'</a></dd></dl>'."\n";
		}
	}
			echo "</div>";
}
?>
<br /><br /><br />
<div class="clearer"></div>
		</div>
	</div>
</div>

<div class="block" id="footer">
	<div class="box">
		<div class="inbox">
			<span class="copyright"><a href="http://www.powerwd.net">PowerBB</a> <?php if ($configuration['o_show_version']) echo " v".$configuration['o_cur_version'];?> &copy; <? echo date('Y');?> <a href="http://www.powerwd.net"><b>Eli White</b></a><br />Licensed to: <?php echo $configuration['o_lic_company'] ?></span>
			<div class="clearer"></div>
		</div>
	</div>
</div>
<?php
if (defined('DEBUG'))
{
	list($usec, $sec) = explode(' ', microtime());
	$time_diff = sprintf('%.3f', ((float)$usec + (float)$sec) - $forum_start);
	echo "\t\t\t".'<p class="conr">[ Generated in '.$time_diff.' seconds, '.$db->get_num_queries().' queries executed ]</p>'."\n";
}
?>
			<div class="clearer"></div>
		</div>
	</div>
</div>
<?php
$db->end_transaction();
if (defined('SHOW_QUERIES')) display_saved_queries();
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