<?php
if (!function_exists('pg_connect')) exit('This PHP environment doesn\'t have PostgreSQL support built in. PostgreSQL support is required if you want to use a PostgreSQL database to run this forum. Consult the PHP documentation for further assistance.');

class DBLayer
{
	var $prefix;
	var $link_id;
	var $query_result;
	var $last_query_text = array();
	var $in_transaction = 0;
	var $saved_queries = array();
	var $num_queries = 0;
	var $error_no = false;
	var $error_msg = 'Unknown';

	function DBLayer($db_host, $db_username, $db_password, $db_name, $db_prefix, $p_connect)
	{
		$this->prefix = $db_prefix;
		if ($db_host != '')
		{
			if (strpos($db_host, ':') !== false)
			{
				list($db_host, $dbport) = explode(':', $db_host);
				$connect_str[] = 'host='.$db_host.' port='.$dbport;
			}
			else
			{
				if ($db_host != 'localhost') $connect_str[] = 'host='.$db_host;
			}
		}
		if ($db_name) $connect_str[] = 'dbname='.$db_name;
		if ($db_username != '') $connect_str[] = 'user='.$db_username;
		if ($db_password != '') $connect_str[] = 'password='.$db_password;
		if ($p_connect) $this->link_id = @pg_pconnect(implode(' ', $connect_str));
		else $this->link_id = @pg_connect(implode(' ', $connect_str));
		if (!$this->link_id) error('Unable to connect to PostgreSQL server', __FILE__, __LINE__);
		else return $this->link_id;
	}

	function start_transaction()
	{
		++$this->in_transaction;
		return (@pg_query($this->link_id, 'BEGIN')) ? true : false;
	}

	function end_transaction()
	{
		--$this->in_transaction;
		if (@pg_query($this->link_id, 'COMMIT')) return true;
		else
		{
			@pg_query($this->link_id, 'ROLLBACK');
			return false;
		}
	}

	function query($sql, $unbuffered = false)
	{
		if (strrpos($sql, 'LIMIT') !== false) $sql = preg_replace('#LIMIT ([0-9]+),([ 0-9]+)#', 'LIMIT \\2 OFFSET \\1', $sql);
		if (defined('SHOW_QUERIES')) $q_start = get_microtime();
		@pg_send_query($this->link_id, $sql);
		$this->query_result = @pg_get_result($this->link_id);
		if (pg_result_status($this->query_result) != PGSQL_FATAL_ERROR)
		{
			if (defined('SHOW_QUERIES')) $this->saved_queries[] = array($sql, sprintf('%.5f', get_microtime() - $q_start));
			++$this->num_queries;
			$this->last_query_text[$this->query_result] = $sql;
			return $this->query_result;
		}
		else
		{
			if (defined('SHOW_QUERIES')) $this->saved_queries[] = array($sql, 0);
			$this->error_msg = @pg_result_error($this->query_result);
			if ($this->in_transaction) @pg_query($this->link_id, 'ROLLBACK');
			--$this->in_transaction;
			return false;
		}
	}

	function result($query_id = 0, $row = 0)
	{
		return ($query_id) ? @pg_fetch_result($query_id, $row, 0) : false;
	}

	function fetch_assoc($query_id = 0)
	{
		return ($query_id) ? @pg_fetch_assoc($query_id) : false;
	}

	function fetch_row($query_id = 0)
	{
		return ($query_id) ? @pg_fetch_row($query_id) : false;
	}

	function num_rows($query_id = 0)
	{
		return ($query_id) ? @pg_num_rows($query_id) : false;
	}

	function affected_rows()
	{
		return ($this->query_result) ? @pg_affected_rows($this->query_result) : false;
	}

	function insert_id()
	{
		$query_id = $this->query_result;
		if ($query_id && $this->last_query_text[$query_id] != '')
		{
			if (preg_match('/^INSERT INTO ([a-z0-9\_\-]+)/is', $this->last_query_text[$query_id], $table_name))
			{
				if (substr($table_name[1], -6) == 'groups') $table_name[1] .= '_g';
				$temp_q_id = @pg_query($this->link_id, 'SELECT currval(\''.$table_name[1].'_id_seq\')');
				return ($temp_q_id) ? intval(@pg_fetch_result($temp_q_id, 0)) : false;
			}
		}
		return false;
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
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @pg_free_result($query_id) : false;
	}

	function escape($str)
	{
		return pg_escape_string($str);
	}

	function error()
	{
		$result['error_sql'] = @current(@end($this->saved_queries));
		$result['error_no'] = false;
		$result['error_msg'] = $this->error_msg;
		return $result;
	}

	function close()
	{
		if ($this->link_id)
		{
			if ($this->in_transaction)
			{
				if (defined('SHOW_QUERIES')) $this->saved_queries[] = array('COMMIT', 0);
				@pg_query($this->link_id, 'COMMIT');
			}
			if ($this->query_result) @pg_free_result($this->query_result);
			return @pg_close($this->link_id);
		}
		else return false;
	}
}