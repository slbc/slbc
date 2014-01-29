<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2013-10-03 11:35:55 -0700 (Thu, 03 Oct 2013) $
$Rev: 10779 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
#	CORE: This file is loaded at CORE
#	SP Error Handling and reporting
#
#	If the constant SPSHOWERRORS is set to true, non E_STRICT
#	errors will also echo to the screen. E_NOTICE 'undefined'
#	errors are curtrently ignored.
#
# ==================================================================

# ------------------------------------------------------------------
# sp_construct_database_error()
#
# Version: 5.0
# DATABASE ERROR MESSAGE CONSTRUCTOR
#
# Creates database error message and sends to error log function
#
#	$sql:		the original sql statement
#	$sqlerror:	the reported mysql error text
# ------------------------------------------------------------------
function sp_construct_database_error($sql, $sqlerror) {
	global $spStatus, $wpdb;
	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;

	# check error log exists as it won't until installed.
	$success = $wpdb->get_var("SHOW TABLES LIKE '".SFERRORLOG."'");
	if ($success == false) return;

	$mess = '';
	$trace = debug_backtrace();
	$traceitem = $trace[2];
	$mess.= 'file: '.$traceitem['file'].'<br />';
	$mess.= 'line: '.$traceitem['line'].'<br />';
	$mess.= 'function: '.$traceitem['function'].'<br />';
	$mess.= "error: $sqlerror<br /><br />";
	$mess.= $sql;

	$keyCheck = substr(E_ERROR . $traceitem['line'] . substr($traceitem['file'], -30, 30), 0, 45);

	# write out error to our toolbox log if it doesn't exist already
	$e = spdb_table(SFERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="database"', 'error_count');
	if(empty($e) || $e == 0) {
		@sp_write_error('database', $mess, E_ERROR, $keyCheck);
	} else {
		@sp_update_error($keyCheck, $e);
	}

	# create display message
	include_once(SPAPI.'sp-api-cache.php');
	sp_notify(1, sp_text('Invalid database query'));
}

# ------------------------------------------------------------------
# sp_construct_php_error()
#
# Version: 5.0
# PHP ERROR MESSAGE CONSTRUCTOR (at least those catchable ones)
#
# Creates php error message and sends to error log function
#
#	$errno:		Error Type
#	$errstr:	Error message text
#	$errfile:	Error File
#	$errline:	Error Line Number in file
# ------------------------------------------------------------------
function sp_construct_php_error($errno, $errstr, $errfile, $errline) {
	global $spPaths, $spStatus, $wpdb;
	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;

	# check error log exists as it won't until installed.
	$success = $wpdb->get_var("SHOW TABLES LIKE '".SFERRORLOG."'");
	if ($success == false) return;

	# only interested in SP errors
	$errfile = str_replace('\\','/',$errfile); # sanitize for Win32 installs
	$pos = strpos($errfile, '/plugins/simple-press/');
	$pos1 = strpos($errfile, $spPaths['plugins']);
	if ($pos === false && $pos1 === false) return false;

	$errortype = array (
		E_ERROR				 => 'Error',
		E_WARNING			 => 'Warning',
		E_PARSE				 => 'Parsing Error',
		E_NOTICE			 => 'Notice',
		E_CORE_ERROR		 => 'Core Error',
		E_CORE_WARNING		 => 'Core Warning',
		E_COMPILE_ERROR		 => 'Compile Error',
		E_COMPILE_WARNING	 => 'Compile Warning',
		E_USER_ERROR		 => 'User Error',
		E_USER_WARNING		 => 'User Warning',
		E_USER_NOTICE		 => 'User Notice',
		E_STRICT			 => 'Runtime Notice',
		E_RECOVERABLE_ERROR	 => 'Catchable Fatal Error'
	);

	if ($errno==E_NOTICE || $errno==E_RECOVERABLE_ERROR || $errno==E_WARNING || $errno==E_USER_WARNING) {
		$mess = '';
		$trace = debug_backtrace();
		$traceitem = $trace[1];
		unset($trace);
		if ($traceitem['function'] == 'spHandleShutdown') $traceitem['function'] ='Unavailable';

		$mess.= 'file: '.substr($errfile, $pos+7, strlen($errfile)).'<br />';
		$mess.= "line: $errline<br />";
		$mess.= 'function: '.$traceitem['function'].'<br />';
		$mess.= $errortype[$errno].' | '.$errstr;

		$keyCheck = substr($errortype[$errno] . $errline . substr($errfile, -30, 30), 0, 45);

		# write out error to our toolbox log if it doesn't exist already
		$e = spdb_table(SFERRORLOG, 'keycheck="'.$keyCheck.'" AND error_type="php"', 'error_count');
		if(empty($e) || $e == 0) {
			@sp_write_error('php', $mess, $errno, $keyCheck);
		} else {
			@sp_update_error($keyCheck, $e);
		}
		# wrtie error out to php error log (its still supressed from the screen)
		error_log('PHP '.$errortype[$errno].':  '.$errstr, 0);

		# if we arent showing SP errors, dont let php error handler run
		if (!defined('SPSHOWERRORS') || SPSHOWERRORS == false) return true;
	}
	return false;
}

# ------------------------------------------------------------------
# spHandleShutdown()
#
# Version: 5.0
# FATAL (CRASH) ERROR RECORDING HANDLER
#
# Creates fatal error warning and passes to main error handler
# ------------------------------------------------------------------
register_shutdown_function('spHandleShutdown');
function spHandleShutdown() {
	global $spStatus;
	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;
	$error = error_get_last();
	if ($error !== NULL) sp_construct_php_error($error['type'], $error['message'], $error['file'], $error['line']);
}

# ------------------------------------------------------------------
# sp_write_error()
#
# Version: 5.0
# ERROR RECORDING HANDLER
#
# Creates entry in table sferrorlog
#
#	$errortyoe:	'database'
#	$errortext:	pre-formatted error details
# ------------------------------------------------------------------
function sp_write_error($errortype, $errortext, $errno=E_ERROR, $keyCheck='unset_keycheck') {
	global $spStatus, $spVars, $wpdb;

	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;
	if (mysql_ping() == false) return;

	# check error log exists as it won't until installed.
	$success = $wpdb->get_var("SHOW TABLES LIKE '".SFERRORLOG."'");
	if ($success == false) return;

	$cat = $errno;
	if($errno == E_ERROR) {
		$cat = 'spaErrError';
	} elseif($errno == E_WARNING) {
		$cat = 'spaErrWarning';
	} elseif($errno == E_NOTICE) {
		$cat = 'spaErrNotice';
	} elseif($errno == E_STRICT) {
		$cat = 'spaErrStrict';
	}

	$now = "'".current_time('mysql')."'";
	$sql = 'INSERT INTO '.SFERRORLOG;
	$sql.= ' (error_date, error_type, error_cat, keycheck, error_count, error_text) ';
	$sql.= 'VALUES (';
	$sql.= $now.", ";
	$sql.= "'".$errortype."', ";
	$sql.= "'".$cat."', ";
	$sql.= "'".$keyCheck."', ";
	$sql.= '1, ';
	$sql.= "'".esc_sql($errortext)."')";
	$wpdb->query($sql);

	# leave just last 50 entries
	if ($wpdb->insert_id > 51) {
		$sql = 'DELETE FROM '.SFERRORLOG.' WHERE id < '.($wpdb->insert_id - 50);
		$wpdb->query($sql);
	}
}

# ------------------------------------------------------------------
# sp_update_error()
#
# Version: 5.3.2
# ERROR RECORDING HANDLER
#
# Updates entry in table sferrorlog with new date and count
#
#	$keyCheck:	Unique shirtened key for ID purposes
#	$e:			number of prior occurencies of error
# ------------------------------------------------------------------
function sp_update_error($keyCheck, $e) {
	global $spStatus;
	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;

	$now = "'".current_time('mysql')."'";
	$e++;
	$sql = 'UPDATE '.SFERRORLOG.' SET
			error_date = '.$now.',
			error_count = '.$e.' WHERE
			keycheck = "'.$keyCheck.'"';
	spdb_query($sql);
}

# ------------------------------------------------------------------
# sp_gis_error()
#
# Version: 5.0
# Handles GetImageSize calls and produces error in failure
# ------------------------------------------------------------------
function sp_gis_error($errno, $errstr, $errfile, $errline, $errcontext) {
	global $gis_error;
	if ($errno == E_WARNING || $errno == E_NOTICE) $gis_error = sp_text('Unable to validate image details');
}


?>