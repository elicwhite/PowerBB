<?php
if (!defined('IN_FORUM')) exit;

/**
 * Returns TRUE if the email is valid. Returns FALSE if invalid or the email is longer than 50 chars, or invalid.
 *
 * @param string $email
 * @return bool
 */

function is_valid_email($email)
{
	if (strlen($email) > 50) return false;
	return preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email);
}

/**
 * Returns TRUE if the email is banned. Returns FALSE if not.
 *
 * @param string $email
 * @return bool
 */

function is_banned_email($email)
{
	global $db, $forum_bans;
	foreach ($forum_bans as $cur_ban)
	{
		if ($cur_ban['email'] != '' && ($email == $cur_ban['email'] || (strpos($cur_ban['email'], '@') === false && stristr($email, '@'.$cur_ban['email'])))) return true;
	}
	return false;
}

/**
 * Sends email using PowerBB’s settings (either via mail() or SMTP)
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $from
 */

function forum_mail($to, $subject, $message, $from = '')
{
	global $configuration, $lang_common;
	if (!$from) $from = '"'.str_replace('"', '', $configuration['o_board_name'].' '.$lang_common['Mailer']).'" <'.$configuration['o_webmaster_email'].'>';
	$accents = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ";
	$ssaccents = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	$to = trim(preg_replace('#[\n\r]+#s', '', $to));
	$subject = strtr(trim(preg_replace('#[\n\r]+#s', '', $subject)),$accents,$ssaccents);
	$from = trim(preg_replace('#[\n\r:]+#s', '', $from));
	$headers = 'From: '.$from."\r\n".'Date: '.date('r')."\r\n".'MIME-Version: 1.0'."\r\n".'Content-transfer-encoding: 8bit'."\r\n".'Content-type: text/plain; charset='.$lang_common['lang_encoding']."\r\n".'X-Mailer: BinaryForum Mailer';
	$message = str_replace("\n", "\r\n", forum_linebreaks($message));
	if ($configuration['o_smtp_host'] != '') smtp_mail($to, $subject, $message, $headers);
	else
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'MAC') $headers = str_replace("\r\n", "\r", $headers);
		else if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') $headers = str_replace("\r\n", "\n", $headers);
		mail($to, $subject, $message, $headers);
	}
}

/*
 * Parses the server if it's correctly spcified
 *
 * @param string $socket
 * @param string $expected_response
 */

function server_parse($socket, $expected_response)
{
	$server_response = '';
	while (substr($server_response, 3, 1) != ' ')
	{
		if (!($server_response = fgets($socket, 256))) error('Couldn\'t get mail server response codes. Please contact the forum administrator.', __FILE__, __LINE__);
	}
	if (!(substr($server_response, 0, 3) == $expected_response)) error('Unable to send e-mail. Please contact the forum administrator with the following error message reported by the SMTP server: "'.$server_response.'"', __FILE__, __LINE__);
}

/**
 * Sends an email via SMTP (use only though forum_mail). Returns TRUE if the email is send, else returns FALSE.
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $headers
 * @return bool
 */

function smtp_mail($to, $subject, $message, $headers = '')
{
	global $configuration;
	$recipients = explode(',', $to);
	if (strpos($configuration['o_smtp_host'], ':') !== false) list($smtp_host, $smtp_port) = explode(':', $configuration['o_smtp_host']);
	else
	{
		$smtp_host = $configuration['o_smtp_host'];
		$smtp_port = 25;
	}
	if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15))) error('Could not connect to smtp host "'.$configuration['o_smtp_host'].'" ('.$errno.') ('.$errstr.')', __FILE__, __LINE__);
	server_parse($socket, '220');
	if ($configuration['o_smtp_user'] != '' && $configuration['o_smtp_pass'] != '')
	{
		fwrite($socket, 'EHLO '.$smtp_host."\r\n");
		server_parse($socket, '250');
		fwrite($socket, 'AUTH LOGIN'."\r\n");
		server_parse($socket, '334');
		fwrite($socket, base64_encode($configuration['o_smtp_user'])."\r\n");
		server_parse($socket, '334');
		fwrite($socket, base64_encode($configuration['o_smtp_pass'])."\r\n");
		server_parse($socket, '235');
	}
	else
	{
		fwrite($socket, 'HELO '.$smtp_host."\r\n");
		server_parse($socket, '250');
	}

	fwrite($socket, 'MAIL FROM: <'.$configuration['o_webmaster_email'].'>'."\r\n");
	server_parse($socket, '250');
	$to_header = 'To: ';
	@reset($recipients);
	while (list(, $email) = @each($recipients))
	{
		fwrite($socket, 'RCPT TO: <'.$email.'>'."\r\n");
		server_parse($socket, '250');
		$to_header .= '<'.$email.'>, ';
	}
	fwrite($socket, 'DATA'."\r\n");
	server_parse($socket, '354');
	fwrite($socket, 'Subject: '.$subject."\r\n".$to_header."\r\n".$headers."\r\n\r\n".$message."\r\n");
	fwrite($socket, '.'."\r\n");
	server_parse($socket, '250');
	fwrite($socket, 'QUIT'."\r\n");
	fclose($socket);
	return true;
}