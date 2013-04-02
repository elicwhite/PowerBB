<?php
if (!defined('IN_FORUM')) exit;

if (!function_exists('var_export'))
{
	function var_export()
	{
		$args = func_get_args();
		$indent = (isset($args[2])) ? $args[2] : '';
		if (is_array($args[0]))
		{
			$output = 'array ('."\n";
			foreach ($args[0] as $k => $v)
			{
				if (is_numeric($k)) $output .= $indent.'  '.$k.' => ';
				else $output .= $indent.'  \''.str_replace('\'', '\\\'', str_replace('\\', '\\\\', $k)).'\' => ';
				if (is_array($v)) $output .= var_export($v, true, $indent.'  ');
				else
				{
					if (gettype($v) != 'string' && !empty($v)) $output .= $v.','."\n";
					else $output .= '\''.str_replace('\'', '\\\'', str_replace('\\', '\\\\', $v)).'\','."\n";
				}
			}
			$output .= ($indent != '') ? $indent.'),'."\n" : ')';
		}
		else $output = $args[0];
		if ($args[1] == true) return $output;
		else echo $output;
	}
}

/**
 * Generates the main config cache file
 */

function generate_config_cache()
{
	global $db;
	$result = $db->query('SELECT * FROM '.$db->prefix.'config', true) or error('Unable to fetch forum config', __FILE__, __LINE__, $db->error());
	while ($cur_config_item = $db->fetch_row($result)) $output[$cur_config_item[0]] = $cur_config_item[1];
	$fh = @fopen(FORUM_ROOT.'cache/cache_config.php', 'wb');
	if (!$fh)  error('Unable to write configuration cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'', __FILE__, __LINE__);
	fwrite($fh, '<?php'."\n\n".'define(\'CONFIG_LOADED\', 1);'."\n\n".'$configuration = '.var_export($output, true).';'."\n\n".'?>');
	fclose($fh);
}

/**
 * Generates the bans cache file
 */

function generate_bans_cache()
{
	global $db;
	$result = $db->query('SELECT * FROM '.$db->prefix.'bans', true) or error('Unable to fetch ban list', __FILE__, __LINE__, $db->error());
	$output = array();
	while ($cur_ban = $db->fetch_assoc($result)) $output[] = $cur_ban;
	$fh = @fopen(FORUM_ROOT.'cache/cache_bans.php', 'wb');
	if (!$fh) error('Unable to write bans cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'', __FILE__, __LINE__);
	fwrite($fh, '<?php'."\n\n".'define(\'BANS_LOADED\', 1);'."\n\n".'$forum_bans = '.var_export($output, true).';'."\n\n".'?>');
	fclose($fh);
}

/**
 * Generates the ranks cache file
 */

function generate_ranks_cache()
{
	global $db;
	$result = $db->query('SELECT * FROM '.$db->prefix.'ranks ORDER BY min_posts', true) or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());
	$output = array();
	while ($cur_rank = $db->fetch_assoc($result)) $output[] = $cur_rank;
	$fh = @fopen(FORUM_ROOT.'cache/cache_ranks.php', 'wb');
	if (!$fh) error('Unable to write ranks cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'', __FILE__, __LINE__);
	fwrite($fh, '<?php'."\n\n".'define(\'RANKS_LOADED\', 1);'."\n\n".'$forum_ranks = '.var_export($output, true).';'."\n\n".'?>');
	fclose($fh);
}

/**
 * Generates the quickjump cache file
 */

function generate_quickjump_cache($group_id = false)
{
	global $db, $lang_common, $forum_user;
	if ($group_id !== false) $groups[0] = $group_id;
	else
	{
		$result = $db->query('SELECT g_id FROM '.$db->prefix.'groups') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
		$num_groups = $db->num_rows($result);
		for ($i = 0; $i < $num_groups; ++$i) $groups[] = $db->result($result, $i);
	}
	while (list(, $group_id) = @each($groups))
	{
		$fh = @fopen(FORUM_ROOT.'cache/cache_quickjump_'.$group_id.'.php', 'wb');
		if (!$fh) error('Unable to write quickjump cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'', __FILE__, __LINE__);
		$output = '<?php'."\n\n".'if (!defined(\'IN_FORUM\')) exit;'."\n".'define(\'QJ_LOADED\', 1);'."\n\n".'?>';
		$output .= "\t\t\t\t".'<form id="qjump" method="get" action="view_forum.php">'."\n\t\t\t\t\t".'<div><label><?php echo $lang_common[\'Jump to\'] ?>'."\n\n\t\t\t\t\t".'<br /><select style="width: 180px" name="id" onchange="window.location=(\'view_forum.php?id=\'+this.options[this.selectedIndex].value)">'."\n";
		$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url, f.parent_forum_id FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$group_id.') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.parent_forum_id = 0 ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
		$cur_category = 0;
		while ($cur_forum = $db->fetch_assoc($result))
		{
			if ($cur_forum['cid'] != $cur_category)
			{
				if ($cur_category) $output .= "\t\t\t\t\t\t".'</optgroup>'."\n";
				$output .= "\t\t\t\t\t\t".'<optgroup label="'.convert_htmlspecialchars($cur_forum['cat_name']).'">'."\n";
				$cur_category = $cur_forum['cid'];
			}
			$redirect_tag = ($cur_forum['redirect_url'] != '') ? ' &gt;&gt;&gt;' : '';
			$output .= "\t\t\t\t\t\t\t".'<option value="'.$cur_forum['fid'].'"<?php echo ($forum_id == '.$cur_forum['fid'].') ? \' selected="selected"\' : \'\' ?>>'.convert_htmlspecialchars($cur_forum['forum_name']).$redirect_tag.'</option>'."\n";
		}
		$output .= "\t\t\t\t\t".'</optgroup>'."\n\t\t\t\t\t".'</select>'."\n\t\t\t\t\t".''."\n\t\t\t\t\t".'</label></div>'."\n\t\t\t\t".'</form>'."\n";
		fwrite($fh, $output);
		fclose($fh);
	}
}

/**
 * Generates the advertising cache file
 */
function generate_advertising_config_cache()
{
	global $db;
	$result = $db->query('SELECT * FROM '.$db->prefix.'advertising_config', true) or error('Unable to fetch advertising config', __FILE__, __LINE__, $db->error());
	while ($cur_config_item = $db->fetch_row($result)) $output[$cur_config_item[0]] = $cur_config_item[1];
	$fh = @fopen(FORUM_ROOT.'cache/cache_ads_config.php', 'wb');
	if (!$fh) error('Unable to write advertising configuration cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'', __FILE__, __LINE__);
	fwrite($fh, '<?php'."\n\n".'define(\'ADS_CONFIG_LOADED\', 1);'."\n\n".'$ads_config = '.var_export($output, true).';'."\n\n".'?>');
	fclose($fh);
}
