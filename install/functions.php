<?php
function check_perms($path,$perm)
	{
		clearstatcache();
		$configmod = substr(sprintf('%o', fileperms($path)), -4); 
		//$corr = preg_replace('..', '', $path);
		$trcss = (($configmod != $perm) ? "background-color:#fd7a7a;" : "background-color:#91f587;");
		echo "<tr style=".$trcss.">"; 
		echo "<td style=\"border:1px solid black; text-align: left;\">". $path ."</td>"; 
		echo "<td style=\"border:1px solid black; text-align: center;\">$perm</td>"; 
		echo "<td style=\"border:1px solid black; text-align: center;\">$configmod</td>"; 
		echo "</tr>";  
	}
function unescape($str)
	{
		return (get_magic_quotes_gpc() == 1) ? stripslashes($str) : $str;
	}
function forum_hash($str)
{
	if (function_exists('sha1'))
	{
		return sha1($str);
	}
	else if (function_exists('mhash'))
	{
		return bin2hex(mhash(MHASH_SHA1, $str));
	}
	else return md5($str);
}
function error($message, $file = false, $line = false, $db_error = false)
	{
?>
		<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html>
		<head>
			<title>PowerBB Installer</title>
			<link rel="stylesheet" type="text/css" href="../include/css/install.css" />
		</head>
		<body>
		<div class="Banner">
			<img src="../img/logo.png" border="0" alt="PowerBB Forum" />
		</div>
	      <div class="Body">
			<div class="Contents">
				<h1>PowerBB Installation Wizard (Step 2 of 3)</h1><p>Below you can provide the connection parameters for the database server where you want to install PowerBB. If you haven't done it yet, now would be a good time to create the database where you want PowerBB installed.</p>
				<div class="Warnings">
					<strong>Oops. We came across some problems.</strong>
<?php
					if ($file !== false && $line !== false) echo '<strong style="color: A00000">An error occured on line '.$line.' in file '.$file.'.</strong><br /><br />';
					else echo '<strong style="color: A00000">An error occured.</strong><br />';
					echo '<strong>PowerBB Forum reported:</strong> '.htmlspecialchars($message).'<br /><br />';
					if ($db_error !== false) echo '<strong>Database reported:</strong> '.htmlspecialchars($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '');
?>
				</div>
				<p>Let's try this again...</p>
				<div class="Button"><a href="javascript:history.go(-1)">Click here to go back and correct the errors.</a></div>
			</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
		</body>
		</html>
<?php
		exit;
	}
?>