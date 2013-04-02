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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums) / Logs";
require FORUM_ROOT.'header.php';
@set_time_limit(0);

function gzip_PrintFourChars($Val)
{
	for ($i = 0; $i < 4; $i ++)
	{
		$return = chr($Val % 256);
		$Val = floor($Val / 256);
	}
	return $return;
} 

function field_name($offset, $query_id = 0)
{
	global $db_type;
	if(!$query_id)
	{
		$query_id = $this->query_result;
	}
	if($query_id)
	{
		switch($db_type)
		{
			case 'mysql':
				$result = @mysql_field_name($query_id, $offset);
			break;
			case 'mysqli':
				$finfo = @mysqli_fetch_field_direct($query_id, $offset);
				$result = $finfo->name;
		}		
		return $result;
	}
	else
		return false;
}

function num_fields($query_id = 0)
{
	global $db_type;
	if (!$query_id) $query_id = $this->query_result;
	switch($db_type)
	{
		case 'mysql':
			return ($query_id) ? @mysql_num_fields($query_id) : false;
		break;
		case 'mysqli':
			return ($query_id) ? @mysqli_num_fields($query_id) : false;
	}		
}

function get_table_def_mysql($table, $crlf)
{
	global $drop, $db;
	$schema_create = "";
	$field_query = "SHOW FIELDS FROM $table";
	$key_query = "SHOW KEYS FROM $table";
	$schema_create = "DROP TABLE IF EXISTS $table;$crlf";
	$schema_create .= "CREATE TABLE $table($crlf";
	$result = $db->query($field_query);
	if(!$result)
	{
		message('Failed to get field list');
	}
	while ($row = $db->fetch_assoc($result))
	{
		$schema_create .= '	' . $row['Field'] . ' ' . $row['Type'];
		if(!empty($row['Default']))
		{
			$schema_create .= ' DEFAULT \'' . $row['Default'] . '\'';
		}
		if($row['Null'] != "YES")
		{
			$schema_create .= ' NOT NULL';
		}
		if($row['Extra'] != "")
		{
			$schema_create .= ' ' . $row['Extra'];
		}
		$schema_create .= ",$crlf";
	}
	$schema_create = ereg_replace(',' . $crlf . '$', "", $schema_create);
	$result = $db->query($key_query);
	if(!$result)
	{
		message('Failed to get Indexed Fields');
	}
	while($row = $db->fetch_assoc($result))
	{
		$kname = $row['Key_name'];
		if(($kname != 'PRIMARY') && ($row['Non_unique'] == 0))
		{
			$kname = "UNIQUE|$kname";
		}
		if (!isset($index[$kname])) $index[$kname] = array();
		$index[$kname][] = $row['Column_name'];
	}
	while(list($x, $columns) = @each($index))
	{
		$schema_create .= ", $crlf";

		if($x == 'PRIMARY')
		{
			$schema_create .= '	PRIMARY KEY (' . implode($columns, ', ') . ')';
		}
		elseif (substr($x,0,6) == 'UNIQUE')
		{
			$schema_create .= '	UNIQUE ' . substr($x,7) . ' (' . implode($columns, ', ') . ')';
		}
		else
		{
			$schema_create .= "	KEY $x (" . implode($columns, ', ') . ')';
		}
	}
	$schema_create .= "$crlf);";
	if(get_magic_quotes_runtime())
	{
		return(stripslashes($schema_create));
	}
	else
	{
		return($schema_create);
	}
}

function get_table_content_mysql($table, $handler)
{
	global $db;
	if (!($result = $db->query("SELECT * FROM $table")))
	{
		message('Failed to get table content');
	}
	if ($row = $db->fetch_assoc($result))
	{
		$handler("\n#\n# Table Data for $table\n#\n");
		$field_names = array();
		$num_fields = num_fields($result);
		$table_list = '(';
		for ($j = 0; $j < $num_fields; $j++)
		{
			$field_names[$j] = field_name($j, $result);
			$table_list .= (($j > 0) ? ', ' : '') . $field_names[$j];
			
		}
		$table_list .= ')';
		do
		{
			$schema_insert = "INSERT INTO $table $table_list VALUES(";
			for ($j = 0; $j < $num_fields; $j++)
			{
				$schema_insert .= ($j > 0) ? ', ' : '';
				if(!isset($row[$field_names[$j]]))
				{
					$schema_insert .= 'NULL';
				}
				elseif ($row[$field_names[$j]] != '')
				{
					$schema_insert .= '\'' . addslashes($row[$field_names[$j]]) . '\'';
				}
				else
				{
					$schema_insert .= '\'\'';
				}
			}
			$schema_insert .= ');';
			$handler(trim($schema_insert));
		}
		while ($row = $db->fetch_assoc($result));
	}
	return(true);
}

function output_table_content($content)
{
	global $tempfile;
	echo $content ."\n";
	return;
}

function remove_remarks($sql)
{
	$lines = explode("\n", $sql);
	$sql = "";
	$linecount = count($lines);
	$output = "";
	for ($i = 0; $i < $linecount; $i++)
	{
		if ((($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) and $lines[$i])
		{
			if ($lines[$i][0] != "#")
			{
				$output .= $lines[$i] . "\n";
				$lines[$i] = "";
			}
		}
	}
	return $output;
}

function split_sql_file($sql, $delimiter)
{
	$tokens = explode($delimiter, $sql);
	$sql = "";
	$output = array();
	$matches = array();
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++)
	{
		if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
		{
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
			$unescaped_quotes = $total_quotes - $escaped_quotes;
			if (($unescaped_quotes % 2) == 0)
			{
				$output[] = $tokens[$i];
				$tokens[$i] = "";
			}
			else
			{
				$temp = $tokens[$i] . $delimiter;
				$tokens[$i] = "";
				$complete_stmt = false;
				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
				{
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
					$unescaped_quotes = $total_quotes - $escaped_quotes;
					if (($unescaped_quotes % 2) == 1)
					{
						$output[] = $temp . $tokens[$j];
						$tokens[$j] = "";
						$temp = "";
						$complete_stmt = true;
						$i = $j;
					}
					else
					{
						$temp .= $tokens[$j] . $delimiter;
						$tokens[$j] = "";
					}
				}
			}
		}
	}
	return $output;
}
switch($db_type)
{
	case 'mysql':
	case 'mysqli':
		break;
	default:
		message('Sorry your database type is not yet supported');
}
if (isset($_POST['backupstart']))
{
	$tables = array('bans', 'bots', 'botsconfig', 'calendar', 'categories', 'censoring', 'config', 'digest_subscribed_forums', 'digest_subscriptions', 'expertise_links', 'expertise_tags', 'forum_perms', 'forums', 'groups', 'invitations', 'messages', 'online', 'polls', 'posts', 'ranks', 'reports', 'search_cache', 'search_matches', 'search_words', 'subscriptions', 'topics', 'users');
	$additional_tables = (isset($_POST['additional_tables'])) ? $_POST['additional_tables'] : ( (isset($HTTP_GET_VARS['additional_tables'])) ? $HTTP_GET_VARS['additional_tables'] : "" );
	$backup_type = (isset($_POST['backup_type'])) ? $_POST['backup_type'] : ( (isset($HTTP_GET_VARS['backup_type'])) ? $HTTP_GET_VARS['backup_type'] : "" );
	$gzipcompress = (!empty($_POST['gzipcompress'])) ? $_POST['gzipcompress'] : ( (!empty($HTTP_GET_VARS['gzipcompress'])) ? $HTTP_GET_VARS['gzipcompress'] : 0 );
	$drop = (!empty($_POST['drop'])) ? intval($_POST['drop']) : ( (!empty($HTTP_GET_VARS['drop'])) ? intval($HTTP_GET_VARS['drop']) : 0 );
	if(!empty($additional_tables))
	{
		if(ereg(",", $additional_tables))
		{
			$additional_tables = split(",", $additional_tables);
			for($i = 0; $i < count($additional_tables); $i++)
			{
				$tables[] = trim($additional_tables[$i]);
			}
		}
		else
		{
			$tables[] = trim($additional_tables);
		}
	}
	if(!empty($_POST["additional_tables_check"]))
	{
		$additional_tables_check = $_POST["additional_tables_check"];
		for($i = 0; $i < count($additional_tables_check); $i++)
		{
			$tables[] = trim($additional_tables_check[$i]);
		}
	}
	header("Pragma: no-cache");
	$do_gzip_compress = FALSE;
	if( $gzipcompress )
	{
		$phpver = phpversion();
		if($phpver >= "4.0")
		{
			if(extension_loaded("zlib"))
			{
				$do_gzip_compress = TRUE;
			}
		}
	}
	if($do_gzip_compress)
	{
		@ob_start();
		@ob_implicit_flush(0);
		header("Content-Type: application/x-gzip; name=\"forum_db_backup." . gmdate("Y-m-d") . ".sql.gz\"");
		header("Content-disposition: attachment; filename=forum_db_backup." . gmdate("Y-m-d") . ".sql.gz");
	}
	else
	{
		header("Content-Type: text/x-delimtext; name=\"forum_db_backup." . gmdate("Y-m-d") . ".sql\"");
		header("Content-disposition: attachment; filename=forum_db_backup." . gmdate("Y-m-d") . ".sql");
	}
	echo "#\n";
	echo "# PowerBB Forum Backup Script\n";
	echo "# Dump of tables for $db_name\n";
	echo "#\n# DATE : " .  gmdate("d-m-Y H:i:s", time()) . " GMT\n";
	echo "#\n";
	for($i = 0; $i < count($tables); $i++)
	{
		$table_name = $tables[$i];
		$table_def_function = "get_table_def_mysql";
		$table_content_function = "get_table_content_mysql";
		if($backup_type != 'data')
		{
			echo "\n#\n# TABLE: " . $db->prefix . $table_name . "\n#\n\n";
			echo $table_def_function($db->prefix . $table_name, "\n") . "\n";
		}
		if($backup_type != 'structure')
		{
			$table_content_function($db->prefix . $table_name, "output_table_content");
		}
	}
	if($do_gzip_compress)
	{
		$Size = ob_get_length();
		$Crc = crc32(ob_get_contents());
		$contents = gzcompress(ob_get_contents());
		ob_end_clean();
		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00".substr($contents, 0, strlen($contents) - 4).gzip_PrintFourChars($Crc).gzip_PrintFourChars($Size);
	}
	exit;
}
elseif (isset($_POST['restore_start']))
{
	$backup_file_name = (!empty($HTTP_POST_FILES['backup_file']['name'])) ? $HTTP_POST_FILES['backup_file']['name'] : "";
	$backup_file_tmpname = ($HTTP_POST_FILES['backup_file']['tmp_name'] != "none") ? $HTTP_POST_FILES['backup_file']['tmp_name'] : "";
	$backup_file_type = (!empty($HTTP_POST_FILES['backup_file']['type'])) ? $HTTP_POST_FILES['backup_file']['type'] : "";
	if($backup_file_tmpname == "" || $backup_file_name == "")
	{
		message('No file was uploaed or the upload failed, the database was not restored');
	}
	if( preg_match("/^(text\/[a-zA-Z]+)|(application\/(x\-)?gzip(\-compressed)?)|(application\/octet-stream)$/is", $backup_file_type) )
	{
		if( preg_match("/\.gz$/is",$backup_file_name) )
		{
			$do_gzip_compress = FALSE;
			$phpver = phpversion();
			if($phpver >= "4.0")
			{
				if(extension_loaded("zlib"))
				{
					$do_gzip_compress = TRUE;
				}
			}
			if($do_gzip_compress)
			{
				$gz_ptr = gzopen($backup_file_tmpname, 'rb');
				$sql_query = "";
				while( !gzeof($gz_ptr) )
				{
					$sql_query .= gzgets($gz_ptr, 100000);
				}
			}
			else
			{
				message('Sorry the database could not be restored');
			}
		}
		else
		{
			$sql_query = fread(fopen($backup_file_tmpname, 'r'), filesize($backup_file_tmpname));
		}
	}
	else
	{
		message('Error the file name or file format caused an error, the database was not restored');
	}
	if($sql_query != "")
	{
		$sql_query = remove_remarks($sql_query);
		$pieces = split_sql_file($sql_query, ";");
		if(defined('DEBUG'))
		{
			generate_admin_menu('db');
?>
	<div class="block">
		<h2><span>Debug info</span></h2>
		<div class="box">
			<div class="inbox">
				<p>
<?php
		}
		$sql_count = count($pieces);
		for($i = 0; $i < $sql_count; $i++)
		{
			$sql = trim($pieces[$i]);
			if(!empty($sql))
			{
				if(defined('DEBUG'))
				{
					echo "Executing: $sql\n<br />";
					flush();
				}
				$result = $db->query($sql);
				if(!$result)
				{
					message('Error imported backup file, the database probably has not been restored');
				}
			}
		}
		if(defined('DEBUG'))
		{
?>
				</p>
			</div>
		</div>
	</div>
<?php
		}
	}
	if(defined('DEBUG'))
	{
?>
	<div class="block">
	<h2 class="block2"><span>Restore complete</span></h2>
		<div class="box">
			<div class="inbox">
				<p>
					<a href="admin_db.php">Back</a>
				</p>
			</div>
		</div>
	</div>
<?php
	}
	else
	{
		message('Restore Complete');
	}
}
elseif (isset($_POST['repairall']))
{
	$sql = 'SHOW TABLE STATUS';
	if (!$result = $db->query($sql))
	{
		message('Tables error, repair failed');
	}
	$tables = array();
	$counter = 0;
	while ($row = $db->fetch_assoc($result))
	{
		$counter++;
		$tables[$counter] = $row['Name'];
	}
	$tablecount = $counter;
	for ($i = 1; $i <= $tablecount; $i++)
	{
		$sql = 'REPAIR TABLE ' . $tables[$i];
		if (!$result = $db->query($sql))
		{
			message('SQL error, repair failed');
		}
	}
	message('All tables repaired');
}
elseif (isset($_POST['optimizeall']))
{
	$sql = 'SHOW TABLE STATUS';
	if (!$result = $db->query($sql))
	{
		message('Tables error, optimise failed');
	}
	$tables = array();
	$counter = 0;
	while ($row = $db->fetch_assoc($result))
	{
		$counter++;
		$tables[$counter] = $row['Name'];
	}
	$tablecount = $counter;
	for ($i = 1; $i <= $tablecount; $i++)
	{
		$sql = 'OPTIMIZE TABLE ' . $tables[$i];
		if (!$result = $db->query($sql))
		{
			message('SQL error, optimise failed');
		}
	}
	message('All tables optimised');
}
elseif (isset($_POST['submit']))
{
	echo "<div>";
	$this_query = $_POST['this_query'];
	if (empty($this_query))
	{
		message('No Query Entered');
	}
	if ((!strrpos($this_query, ";")) || (substr($this_query, -1) != ';'))
	{
		$this_query .= ";";
	}
	$this_query = str_replace(' #__',' '.$db->prefix,$this_query);
	$this_query = remove_remarks($this_query);
	$queries = split_sql_file($this_query, ";");
	$queries = array_map('trim', $queries);
	$queriesdone = "";
	foreach($queries as $query)
	{
		if (!$query) continue;
		if (!strrpos($query, ";"))
		{
			$query .= ";";
		}
		$result = $db->query($query);
		if (!$result)
		{
			message("SQL Error: ".mysql_error());
		}
		$queriesdone .= $query."\n";
		$query_words = explode(" ", $query);
		if ($db->num_rows($result))
		{
			if ($db->num_rows($result) > 500)
			{
				message('Query result too long to be displayed');
			}
			$field_count = num_fields($result);
			$row_count = $db->num_rows($result);
			echo '<div><div class="linkst"><div class="inbox"><div><a href="javascript:history.go(-1)" />Go back</a></div></div></div>';
			echo '<div class="blocktable"><h2 class="block2"><span>';
			echo convert_htmlspecialchars($query);
			echo '</span></h2><div class="box"><div class="inbox"><div class="scrollbox" style="max-height: 500px"><table cellspacing="0"><thead><tr>';
			for ($i = 0; $i < $field_count; $i++)
			{
				$field[$i] = field_name($i, $result);
				echo '<th>';
				echo convert_htmlspecialchars($field[$i]);
				echo '</th>';
			}
			echo '</tr></thead><tbody>';
			while ($row = $db->fetch_assoc($result))
			{
				echo '<tr>';
				for ($i = 0; $i < $field_count; $i++)
				{
					echo '<td>';
					$temp = isset($row[$field[$i]]) ? convert_htmlspecialchars($row[$field[$i]]) : '&nbsp;';
					echo $temp;
					echo '</td>';
				}
				echo "</tr>";
			}
			echo '</tbody></table></div></div></div></div>';
		}
		elseif (substr(trim($query), 0, 6) == 'SELECT')
		{
			echo '<div><div class="linkst"><div class="inbox"><div><a href="javascript:history.go(-1)" />Go back</a></div></div></div>';
			echo '<div class="block"><h2 class="block2"><span>'.convert_htmlspecialchars($query).'</span></h2><div class="box"><div class="inbox"><p>';
			echo "No data found";
			echo '</p></div></div></div>';
		}
	}
	echo '<div class="block"><h2 class="block2"><span>Queries Done</span></h2><div class="box"><div class="inbox"><p>';
	echo nl2br(convert_htmlspecialchars($queriesdone));
	echo '</p></div></div></div>';
	echo '<div><div class="linkst"><div class="inbox"><div><a href="javascript:history.go(-1)" />Go back</a></div></div></div>';
}
else
{
generate_admin_menu('db');
?>
	<div class="blockform">
	<div class="tab-page" id="dbPane"><script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "dbPane" ), 1 )</script>
	<div class="tab-page" id="help-db-page"><h2 class="tab"><?php echo $lang_admin['Help']; ?></h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "help-db-page" ) );</script>
		<div class="box">
		<form>
			<div class="inform">
				<div class="infldset">
					<table class="aligntop" cellspacing="0">
					<tr>
						<td width="100px"><img src=<?php echo FORUM_ROOT?>img/admin/backup.png></td>
						<td>
							<span>This plugin allows the administrator to perform database operations on the master forum database.</span>
						</td>
					</tr>
					</table>
				</div>
			</div>
		</form>
		</div>
	</div>
	<div class="tab-page" id="bkp-db-page"><h2 class="tab">Backup</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "bkp-db-page" ) );</script>
		<div class="box">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Backup options</legend>
						<div class="infldset">
							<p>Here you can back up all your PowerBB Forum-related data. If your server supports it you may also gzip-compress the file to reduce its size before download.</p>
							<table cellspacing="0">
								<tr>
									<th scope="row">Backup type</th>
									<td>
										<input type="radio" name="backup_type" value="full" checked="checked" />&nbsp;Full&nbsp;&nbsp;&nbsp;<input type="radio" name="backup_type" value="structure" />&nbsp;Structure Only&nbsp;&nbsp;&nbsp;<input type="radio" name="backup_type" value="data" />&nbsp;Data Only
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('The type of backup, full will backup all of the data and table structure, whereas structure only and data only will only backup structure or data, a full backup is recommended.');" onmouseout="return nd();" alt="" />
									</td>
								</tr>
								<tr>
									<th scope="row">Additional tables</th>
									<td>
										<span>
<?php
	$sql = 'SHOW TABLE STATUS';
	if (!$result = $db->query($sql))
	{
		message('Tables error');
	}
	$forum_tables = array('bans', 'bots', 'botsconfig', 'calendar', 'categories', 'censoring', 'config', 'digest_subscribed_forums', 'digest_subscriptions', 'expertise_links', 'expertise_tags', 'forum_perms', 'forums', 'groups', 'invitations', 'messages', 'online', 'polls', 'posts', 'ranks', 'reports', 'search_cache', 'search_matches', 'search_words', 'subscriptions', 'topics', 'users');
	$tables = array();
	$counter = 0;
	while ($row = $db->fetch_assoc($result))
	{
		$counter++;
		$tables[$counter] = $row['Name'];
	}
	$tablecount = $counter;
	$table_count = 0;
	if (($tablecount < 32) || ($db->prefix))
	{
		for ($i = 1; $i <= $tablecount; $i++)
		{
			if ((substr($tables[$i], 0, strlen($db->prefix)) == $db->prefix) && ($table_count < 32))
			{
				$table_count = $table_count + 1;
				$cur_table = substr($tables[$i], strlen($db->prefix), strlen($tables[$i]));
				if (!in_array($cur_table ,$forum_tables)) echo '<input name="additional_tables_check[]" type="checkbox" value="'.$cur_table.'"> '.$cur_table.'<br />';
			}
		}
		if ($table_count == 17)
		{
			echo 'None detected';
		}
	}
	else
	{
?>
										<input class="textbox" type="text" name="additional_tables" />
&nbsp;&nbsp;<img src="<?php echo FORUM_ROOT?>img/admin/tooltip.png" onmouseover="return overlib('Enter table names comma separated, excluding table prefix.');" onmouseout="return nd();" alt="" />
<?php
	}
?>
									</td>
								</tr>
								<tr>
									<th scope="row">Gzip compress file</th>
									<td><input type="radio" name="gzipcompress" value="1" />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="gzipcompress" value="0" checked="checked" />&nbsp;<strong>No</strong></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input type="submit" name="backupstart" class="b1" value="Start backup" class="mainoption" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="res-db-page"><h2 class="tab">Restore</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "res-db-page" ) );</script>
		<div class="box">
			<form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<div class="inform">
					<fieldset>
						<legend>Restore options</legend>
						<div class="infldset">
							<p>This will perform a full restore of all PowerBB Forum tables from a saved file. If your server supports it, you may upload a gzip-compressed text file and it will automatically be decompressed. <b>WARNING</b>: This will overwrite any existing data. The restore may take a long time to process, so please do not move from this page until it is complete.</p>
							<table cellspacing="1">
								<tr>
									<th scope="row">Restore from file</th>
									<td><input type="file" name="backup_file" /></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input class="b1" type="submit" name="restore_start" value="Start restore" class="mainoption" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="qry-db-page"><h2 class="tab">Query</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "qry-db-page" ) );</script>
		<div class="box">
			<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
				<div class="inform">
					<fieldset>
						<legend>Run SQL query</legend>
							<div class="infldset">
								<p>This allows you to run basically any command you want on the database (useful for fixing things you messed up), use #__ for your database prefix (e.g. "SELECT * FROM #__online") also use a ; at the end of each query when running multiple queries, linebreaks are irrelevant. WARNING: only use this if you know what you are doing, messing with it could trash your database!</p>
								<table cellspacing="1">
									<tr>
										<th scope="row">SQL query</th>
										<td><textarea name="this_query" rows="5" cols="50"></textarea></td>
									</tr>
								</table>	
							</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input class="b1" type="submit" name="submit" value="Run query" /></p>
			</form>
		</div>
	</div>
	<div class="tab-page" id="oth-db-page"><h2 class="tab">Others</h2><script type="text/javascript">tabPane1.addTabPage( document.getElementById( "oth-db-page" ) );</script>
		<div class="box">
			<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
				<div class="inform">
					<fieldset>
						<legend>Additional Functions</legend>
						<div class="infldset">
							<p>Additional features to help run a database, optimise and repair both do what they say</p>
						</div>
					</fieldset>
				</div>
			<p class="submitend" style="text-align:left;"><input type="submit" class="b1" name="repairall" value="Repair all tables" />&nbsp;<input class="b1" type="submit" name="optimizeall" value="Optimise all tables" /></p>
			</form>
		</div>
	</div>
	<div class="clearer">
	</div>
</div>
<?php
}
require FORUM_ROOT.'admin/admin_footer.php'; ?>