<?php
define ('IN_SPELLING', true);
define ('LINE_FEED', "\n");
$phpEx = 'php';
$Spell_Config['DB_Type'] = 'PowerBB';
$Spell_Config["DB_Username"] = "";
$Spell_Config["DB_Password"] = "";
$Spell_Config["DB_Database"] = "";
$Spell_Config["DB_Host"] = "localhost";
$Spell_Config['IE_UTF_Encode'] = false;
$Spell_Config['Default_Language'] = 'English';
$Spell_Config['Languages_Supported'] = array('English', 'German');
$Spell_Config['Enable_Drop_S_Support'] = true;
$Spell_Config['USE_BBCODE'] = true;
$Spell_Config['USE_HTML'] = false;
$Spell_Config['Levenshtein_Distance'] = 3;
$Spell_Config['Max_User_Levenshtein_Distance'] = 0;
$Spell_Config['Off_By_One_Search'] = 0;
$Spell_Config['HTML_Tags'] = array('<a','</a','<img','</img','<b','</b','<center','</center','<i','</i','<u','</u','<list','</list','<font','</font');
$Spell_Config['BBCODE_Tags'] = array('[b','[/b','[url','[/url',array('[img','[/img]'),'[i','[/i','[color','[/color','[size','[/size','[u','[/u','[list','[/list',array('[quote','[/quote]'),array('[code','[/code]'));
$Spell_Config['Skip_Word_Tags'] = array('http://','www.');
$Spell_Config['Symbol_Tags'] = array(';-D',':-d',':-P',':-p',':p',':P');
$Spell_Config['Insert_Word_Wrap'] = 46;
$Spell_Config['CSS'] = '../css/spelladmin.css';
require '../../config.php';
$Spell_Config["DB_Username"] = $db_username;
$Spell_Config["DB_Password"] = $db_password;
$Spell_Config["DB_Database"] = $db_name;
$Spell_Config["DB_Host"] = $db_host;
$table_prefix = $db_prefix;

if (!defined('IN_SPELLING')) exit;
define('IN_SPELL_ADMIN', false);
$dbms = 'mysql';
$table_prefix = '';
$Spell_Config['DB_MODULE'] = 'Native MySQL';
if ($Spell_Config['DB_Username'] == '' || $Spell_Config['DB_Database'] == '')
{
	die('You need to fill in the Database Information in the Spelling Configuration File.');
}
$db = mysql_pconnect($Spell_Config['DB_Host'], $Spell_Config['DB_Username'], $Spell_Config['DB_Password']);
mysql_select_db($Spell_Config['DB_Database'], $db);

function DB_Check_Word($Word_To_Check)
{
	global $db, $dbms, $DB_TableName;
	$word_to_seek = addslashes($Word_To_Check);
	$Query = 'select * from '.$DB_TableName.' where word=\''.$word_to_seek.'\'';
	if( !($Query_Result = mysql_query($Query, $db)) )
	{
		message_die(CRITICAL_ERROR,'Unable to perform spell check at this time.<br />');
	}
	if (mysql_num_rows($Query_Result) > 0) return (true);
	return (false);
}

function DB_Get_Suggestions($Word_Sound, $Word_To_Check)
{
	global $db, $DB_TableName, $dbms;
	$Suggestions = array();
	$Query = 'select word from '. $DB_TableName.' where sound=\''.$Word_Sound.'\'';
	if( !($Query_Result = mysql_query($Query, $db)) )
	{
		message_die(CRITICAL_ERROR,'Unable to perform spell check at this time.<br />');
	}
	$Count = mysql_num_rows($Query_Result);
	for ($i=0;$i<$Count;$i++)
	{
		$Fetched_Array = mysql_fetch_array($Query_Result);
		$Suggestions[$i] = stripslashes(trim(strtolower($Fetched_Array['word'])));
	}
	return ($Suggestions);
}

function DB_Get_OBO_Suggestions($Word_To_Check)
{
	global $db, $dbms, $DB_TableName;
	$Suggestions = array();
	$Query_Words_Length = strlen($Word_To_Check);
	$Query_Words = '"_'.substr($Word_To_Check, 1).'"';
	for ($i=1;$i<$Query_Words_Length;$i++)
	{
		$Query_Words .= ' or word like "'.substr($Word_To_Check, 0, $i).'_'.substr($Word_To_Check, $i+1).'"';
	}
	$Query_Words = str_replace('\'', "\\'", $Query_Words);
	$Query = 'select word from '. $DB_TableName.' where word like '.$Query_Words;
	if( !($Query_Result = mysql_query($Query, $db)) )
	{
		message_die(CRITICAL_ERROR,'Unable to perform spell check at this time.<br />');
	}
	$Count = mysql_num_rows($Query_Result);
	for ($i=0;$i<$Count;$i++)
	{
		$Fetched_Array = mysql_fetch_array($Query_Result);
		$Suggestions[$i] = stripslashes(trim(strtolower($Fetched_Array['word'])));
	}
	return ($Suggestions);
}

function DB_Get_Word_Count()
{
	global $DB_TableName, $db;
	$Query = 'select count(word) from '.$DB_TableName;
	if( !($Query_Result = mysql_query($Query, $db)) )
	{
		return(DB_Error_Message($Query_Result));
	}
	if (mysql_num_rows($Query_Result) == 0) return ('0');
	$code = mysql_fetch_array($Query_Result);
	return ($code[0]);
}

function DB_Error_Message($Query_Result, $Ignore_Table_Create_Error=false)
{
	global $db, $dbms, $Spell_Config;
	$result = mysql_error($db);
	if (mysql_errno($db) == 1050 && $Ignore_Table_Create_Error) return (-1);
	$Info = 'Code: '.mysql_errno($db).'<br />Message: '.mysql_error($db);
	$Info .= '<br />Result: '.$Query_Result.'  Link: '.$db.'<br />';
	$Info .= 'DB: '.$Spell_Config['DB_Database'].' User: '.$Spell_Config['DB_Username'].' Server: '.$Spell_Config['DB_Host'];
	return ($Info);
}

function DB_Add_Word($Word_To_Add, $Word_Sound)
{
	global $db, $DB_TableName;
	$Word_To_Add = addslashes($Word_To_Add);
	$Query = 'insert into '.$DB_TableName.'(word, sound) values (\''.$Word_To_Add.'\', \''.$Word_Sound.'\')';
	mysql_query($Query, $db);
}

function DB_Create_Table()
{
	global $db, $DB_TableName;
	$Query = 'CREATE TABLE '.$DB_TableName.' (id MEDIUMINT AUTO_INCREMENT NOT NULL, word VARCHAR (30) BINARY NOT NULL, sound VARCHAR(10) not NULL, PRIMARY KEY(id), INDEX(sound), UNIQUE(word))';
	$result = mysql_query($Query, $db);
	if (!$result)
	{
		$Info = DB_Error_Message($result);
		if ($Info != '1050') echo 'Unable to create dictionary database table: '.$Info;
	}
}

function DB_Drop_Table()
{
	global $db, $DB_TableName;
	$Query = 'drop table '.$DB_TableName;
	mysql_query($Query, $db);
}

define('CRITICAL_ERROR', 1);
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);

function message_die($Error, $Message)
{
	die($Message);
}
?>