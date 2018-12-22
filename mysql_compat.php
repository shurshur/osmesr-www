<?php

// source: https://raw.githubusercontent.com/bAndie91/mysql-compat-php/master/mysql_compat.php

/*
	mysql compatibility constants
 */


foreach(array(
 'MYSQL_CLIENT_LONG_PASSWORD'=>1,
 'MYSQL_CLIENT_FOUND_ROWS'=>2,
 'MYSQL_CLIENT_LONG_FLAG'=>4,
 'MYSQL_CLIENT_CONNECT_WITH_DB'=>8,
 'MYSQL_CLIENT_NO_SCHEMA'=>16,
 'MYSQL_CLIENT_COMPRESS'=>32,
 'MYSQL_CLIENT_ODBC'=>64,
 'MYSQL_CLIENT_LOCAL_FILES'=>128,
 'MYSQL_CLIENT_IGNORE_SPACE'=>256,
 'MYSQL_CLIENT_PROTOCOL'=>512,
 'MYSQL_CLIENT_INTERACTIVE'=>1024,
 'MYSQL_CLIENT_SSL'=>2048,
 'MYSQL_CLIENT_IGNORE_SIGPIPE'=>4096,
 'MYSQL_CLIENT_TRANSACTIONS'=>8192,
 'MYSQL_CLIENT_RESERVED'=>16384,
 'MYSQL_CLIENT_SECURE_CONNECTION'=>32768,
 'MYSQL_CLIENT_MULTI_STATEMENTS'=>65535,
 'MYSQL_CLIENT_MULTI_RESULTS'=>131072,
 'MYSQL_CLIENT_REMEMBER_OPTIONS'=>1 << 31,
 'MYSQL_ASSOC'=>1,
 'MYSQL_NUM'=>2,
 'MYSQL_BOTH'=>3,
) as $name=>$value)
{
	if(!defined($name)) define($name, $value);
}

/*
	mysql compatibility functions
 */
 
function mysql_affected_rows($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_affected_rows($link);
}

function mysql_client_encoding($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_character_set_name($link);
}

function mysql_close($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_close($link);
}

function mysql_connect($server = NULL, $username = NULL, $password = NULL, $new_link = false, $client_flags = 0)
{
	if(!isset($server)) $server = ini_get("mysql.default_host");
	if(!isset($username)) $username = ini_get("mysql.default_user");
	if(!isset($password)) $password = ini_get("mysql.default_password");
	$dbname = '';
	$socket = NULL;
	if(strpos($server, ':') !== false)
	{
		list ($host, $port) = explode(':', $server);
		if($host == '')
		{
			$host = '';
			$socket = $port;
			$port = NULL;
		}
	}
	else
	{
		$host = $server;
		$port = ini_get("mysqli.default_port");
	}
	
	$link = mysqli_connect($host, $username, $password, $dbname, $port, $socket);
	$GLOBALS['mysql_compat_last_link'] = $link;
	return $link;
}

function mysql_escape_name($string)
{
	return preg_replace('/`/', '\\`', $dbname);
}

function mysql_create_db($dbname, $link = NULL)
{
	return mysql_query("CREATE DATABASE `" . mysql_escape_name($dbname) . "`", $link);
}

function mysql_data_seek($result, $row_num)
{
	return mysqli_data_seek($result, $row_num);
}

function mysql_db_name($dblist_result, $row_num, $field = NULL)
{
	$seek = mysql_data_seek($dblist_result, $row_num);
	if($seek)
	{
		$array = mysql_fetch_assoc($dblist_result);
		if($array)
		{
			if(isset($field))
			{
				return $array[$field];
			}
			else
			{
				return $array["Database"];
			}
		}
		else
		{
			return $array;
		}
	}
	else
	{
		return $seek;
	}
}

function mysql_db_query($dbname, $query, $link = NULL)
{
	$select = mysql_select_db($dbname, $link);
	if($select)
	{
		return mysql_query($query, $link);
	}
	else
	{
		return $select;
	}
}

function mysql_drop_db($dbname, $link)
{
	return mysql_query("DROP DATABASE `" . mysql_escape_name($dbname) . "`", $link);
}

function mysql_errno($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_errno($link);
}

function mysql_error($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_error($link);
}

function mysql_escape_string($string)
{
	return mysqli_escape_string($string);
}

function mysql_fetch_array($result, $type)
{
	$type_i = $type;
	return mysqli_fetch_array($result, $type_i);
}

function mysql_fetch_assoc($result)
{
	return mysqli_fetch_assoc($result);
}

function mysql_fetch_field($result, $field_offset = NULL)
{
	if(isset($field_offset))
	{
		$seek = mysql_field_seek($result, $field_offset);
		if(!$seek) return $seek;
	}
	return mysqli_fetch_field($result);
}

function mysql_fetch_lengths($result)
{
	return mysqli_fetch_lengths($result);
}

function mysql_fetch_object($result, $class_name = "stdClass", $params = array())
{
	return mysqli_fetch_object($result, $class_name, $params);
}

function mysql_fetch_row($result)
{
	return mysqli_fetch_row($result);
}

function mysql_field_flags($result, $field_offset)
{
	$fld = mysqli_fetch_field_direct($result, $field_offset);
	if(!$fld) return $fld;
	return $fld->flags;
}

function mysql_field_len($result, $field_offset)
{
	$fld = mysqli_fetch_field_direct($result, $field_offset);
	if(!$fld) return $fld;
	return $fld->length;
}

function mysql_field_name($result, $field_offset)
{
	$fld = mysqli_fetch_field_direct($result, $field_offset);
	if(!$fld) return $fld;
	return $fld->name;
}

function mysql_field_seek($result, $field_offset)
{
	return mysqli_field_seek($result, $field_offset);
}

function mysql_field_table($result, $field_offset)
{
	$fld = mysqli_fetch_field_direct($result, $field_offset);
	if(!$fld) return $fld;
	return $fld->table;
}

function mysql_field_type($result, $field_offset)
{
	$fld = mysqli_fetch_field_direct($result, $field_offset);
	if(!$fld) return $fld;
	return $fld->type;
}

function mysql_free_result($result)
{
	return mysqli_free_result($result);
}

function mysql_get_client_info($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_get_client_info($link);
}

function mysql_get_host_info($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_get_host_info($link);
}

function mysql_get_proto_info($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_get_proto_info($link);
}

function mysql_get_server_info($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_get_server_info($link);
}

function mysql_info($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_info($link);
}

function mysql_insert_id($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_insert_id($link);
}

function mysql_list_dbs($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysql_query("SHOW DATABASES", $link);
}

function mysql_list_fields($dbname, $tname, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysql_query("SHOW COLUMNS FROM `".mysql_escape_name($dbname)."`.`".mysql_escape_name($tname)."`", $link);
}

function mysql_list_processes($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysql_query("SHOW PROCESSLIST", $link);
}

function mysql_list_tables($dbname, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysql_query("SHOW TABLES FROM `".mysql_escape_name($dbname)."`", $link);
}

function mysql_num_fields($result)
{
	return mysqli_num_fields($result);
}

function mysql_num_rows($result)
{
	return mysqli_num_rows($result);
}

function mysql_pconnect($server = NULL, $username = NULL, $password = NULL, $client_flags = 0)
{
	if(!isset($server)) $server = ini_get("mysql.default_host");
	if(!isset($username)) $username = ini_get("mysql.default_user");
	if(!isset($password)) $password = ini_get("mysql.default_password");
	$dbname = '';
	$socket = NULL;
	if(strpos($server, ':')>=0)
	{
		list ($host, $port) = explode(':', $server);
		if($host == '')
		{
			$host = '';
			$socket = $port;
			$port = NULL;
		}
	}
	else
	{
		$host = $server;
		$port = ini_get("mysqli.default_port");
	}
	
	if(isset($GLOBALS['mysql_persistent_link'][$server][$username][$password]))
	{
		return $GLOBALS['mysql_persistent_link'][$server][$username][$password];
	}

	$link = mysqli_connect("p:".$host, $username, $password, $dbname, $port, $socket);
	$GLOBALS['mysql_persistent_link'][$server][$username][$password] = $link;
	return $link;
}

function mysql_ping($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_ping($link);
}

function mysql_query($query, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_query($link, $query);
}

function mysql_real_escape_string($string, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_real_escape_string($link, $string);
}

function mysql_result($result, $row_num, $field_def = NULL)
{
	$dseek = mysql_data_seek($result, $row_num);
	if(!$dseek) return $dseek;
	if(!isset($field_def)) $field_def = 0;
	unset($field_num);
	if(preg_match('/^[0-9]+$/', $field_def))
	{
		$field_num = $field_def;
	}
	else
	{
		$fields = mysqli_fetch_fields($result);
		if(!$fields) return $fields;
		foreach($fields as $num => $field)
		{
			if($field->name == $field_def or $field->table.'.'.$field->name == $field_def)
			{
				$field_num = $num;
				break;
			}
		}
		if(!isset($field_num))
		{
			$trace = debug_backtrace();
			trigger_error("mysql_result(): $field_def not found in MySQL result in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_WARNING);
			return false;
		}
	}
	$record = mysql_fetch_row($result);
	if(!$record) return $record;
	return $record[$field_num];
}

function mysql_select_db($dbname, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_select_db($link, $dbname);
}

function mysql_set_charset($name, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_ser_charset($link, $name);
}

function mysql_stat($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_stat($link);
}

function mysql_tablename($tblist_result, $row_num)
{
	$seek = mysql_data_seek($tblist_result, $row_num);
	if($seek)
	{
		$array = mysql_fetch_array($tblist_result);
		if($array)
		{
			return $array[0];
		}
		else
		{
			return $array;
		}
	}
	else
	{
		return $seek;
	}
}

function mysql_thread_id($link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_thread_id($link);
}

function mysql_unbuffered_query($query, $link = NULL)
{
	if(!isset($link)) $link = $GLOBALS['mysql_compat_last_link'];
	return mysqli_query($link, $query, MYSQLI_USE_RESULT);
}
