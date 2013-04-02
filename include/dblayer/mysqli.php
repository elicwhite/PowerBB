<?php
if (!function_exists('mysqli_connect')) exit('This PHP environment doesn\'t have Improved MySQL (mysqli) support built in. Improved MySQL support is required if you want to use a MySQL 4.1 (or later) database to run this forum. Consult the PHP documentation for further assistance.');
require_once(FORUM_ROOT . "include/modules/mod_adminlogs.php");

class DBLayer
{
	var $prefix;
	var $link_id;
	var $query_result;
	var $saved_queries = array();
	var $num_queries = 0;

	function DBLayer($db_host, $db_username, $db_password, $db_name, $db_prefix, $foo)
	{
		$this->prefix = $db_prefix;
		if (strpos($db_host, ':') !== false) list($db_host, $db_port) = explode(':', $db_host);
		if (isset($db_port)) $this->link_id = @mysqli_connect($db_host, $db_username, $db_password, $db_name, $db_port);
		else $this->link_id = @mysqli_connect($db_host, $db_username, $db_password, $db_name);
		if (!$this->link_id) error('Unable to connect to MySQL and select database. MySQL reported: '.mysqli_connect_error(), __FILE__, __LINE__);
	}

	function start_transaction()
	{
		return;
	}

	function end_transaction()
	{
		return;
	}

	function query($sql, $unbuffered = false)
	{
		LogQuery($sql);
		if (defined('SHOW_QUERIES')) $q_start = get_microtime();
		$this->query_result = @mysqli_query($this->link_id, $sql);
		if ($this->query_result)
		{
			if (defined('SHOW_QUERIES')) $this->saved_queries[] = array($sql, sprintf('%.5f', get_microtime() - $q_start));
			++$this->num_queries;
			return $this->query_result;
		}
		else
		{
			if (defined('SHOW_QUERIES')) $this->saved_queries[] = array($sql, 0);
			return false;
		}
	}

	function result($query_id = 0, $row = 0)
	{
		if ($query_id)
		{
			if ($row) @mysqli_data_seek($query_id, $row);
			$cur_row = @mysqli_fetch_row($query_id);
			return $cur_row[0];
		}
		else return false;
	}

	function fetch_assoc($query_id = 0)
	{
		return ($query_id) ? @mysqli_fetch_assoc($query_id) : false;
	}

	function fetch_row($query_id = 0)
	{
		return ($query_id) ? @mysqli_fetch_row($query_id) : false;
	}

	function num_rows($query_id = 0)
	{
		return ($query_id) ? @mysqli_num_rows($query_id) : false;
	}

	function affected_rows()
	{
		return ($this->link_id) ? @mysqli_affected_rows($this->link_id) : false;
	}

	function insert_id()
	{
		return ($this->link_id) ? @mysqli_insert_id($this->link_id) : false;
	}

	function get_num_queries()
	{
		return $this->num_queries;
	}

	function get_saved_queries()
	{
		return $this->saved_queries;
	}

	function free_result($query_id = false)
	{
		return ($query_id) ? @mysqli_free_result($query_id) : false;
	}

	function escape($str)
	{
		return mysqli_real_escape_string($this->link_id, $str);
	}

	function error()
	{
		$result['error_sql'] = @current(@end($this->saved_queries));
		$result['error_no'] = @mysqli_errno($this->link_id);
		$result['error_msg'] = @mysqli_error($this->link_id);
		return $result;
	}

	function close()
	{
		if ($this->link_id)
		{
			if ($this->query_result) @mysqli_free_result($this->query_result);
			return @mysqli_close($this->link_id);
		}
		else return false;
	}
}