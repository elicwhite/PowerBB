<?php
include_once ('functions.php');
$action = isset($_GET['action']) ? $_GET['action'] : NULL;
$forum_version = '2.2.1';
define('FORUM_ROOT', '../');
error_reporting(E_ALL);
@set_time_limit(0);
$WorkingDirectory = getcwd()."/";
$currentdir = $_SERVER['SERVER_NAME'].str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($currentdir, -7) == 'install')
	{
		$currentdir = substr("$currentdir", 0, -8);
	}
?>
		<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html>
		<head>
		<script type="text/javascript" src="../include/js/overlib_mini.js"></script>
		<script type="text/javascript" src="../include/js/uncrypt_email.js"></script>
		<script type="text/javascript" src="../include/js/global.js"></script>
			<title>PowerBB Installer</title>
			<link rel="stylesheet" type="text/css" href="../include/css/install.css" />
		</head>
		<body>
		<div class="Banner">
			<img src="../img/logo.png" border="0" alt="PowerBB Forum" />
		</div>
	      <div class="Body">
			<div class="Contents">
				<h1>PowerBB Installation Wizard (Step 2 of 5)</h1><p>Below you can provide the connection parameters for the database server where you want to install PowerBB. If you haven't done it yet, now would be a good time to create the database where you want PowerBB installed.</p>
		<form id="install" name="frmDatabase" method="post" action="install_step_3.php">
			<input type="hidden" id="form_sent" name="form_sent" value="1" />
			<input type="hidden" id="req_db_type" name="req_db_type" value="mysql" />
			<div class="Form">
				<dl>
					<dt>Database Server</dt>
					<dd><input type="text" name="req_db_host" value="localhost" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The server that your mysql is on. Usually localhost.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Database Name</dt>
					<dd><input type="text" name="req_db_name" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The name of the Database you wish to install PowerBB to.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Database User</dt>
					<dd><input type="text" name="db_username" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('Your username used to access your Mysql server.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Database Password</dt>
					<dd><input type="password" name="db_password" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('Your Mysql password that corresponds to the username entered above.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Database Prefix</dt>
					<dd><input type="text" name="db_prefix" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('If you wish to use a prefix on all of the PowerBB Tables. This allows you to run more than one forum on a database. Ex: foo_');" onMouseOut="return nd();" alt="" /></dd>
					<br /><br />
					<dt>Administrator Username</dt>
					<dd><input type="text" name="req_username" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The username you wish to have on your Forum. The username of the initial administrator.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Administrator Password</dt>
					<dd><input type="password" name="req_password1" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The password that you will use to login with the username entered above. This can be changed later.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Administrator Confirm Password</dt>
					<dd><input type="password" name="req_password2" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('Repeat the above password.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Administrator E-Mail</dt>
					<dd><input type="text" name="req_email" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('Your email if an error on the forums occur. Also the Email that corresponds to your account.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Registered to (username)</dt>
					<dd><input type="text" name="req_your_name" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The name you want to register your forum to.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Registered to (company)</dt>
					<dd><input type="text" name="req_company" value="" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('This is what will show up in \'Licensed to: ******\' in the footer.');" onMouseOut="return nd();" alt="" /></dd>
					<dt>Base Forum URL</dt>
					<dd><input type="text" name="req_base_url" value="http://<?php echo $currentdir ?>" /> <img src="../img/admin/tooltip.png" onMouseOver="return overlib('The url used to complete links and refferrer addresses. This is important. It can be changed later, but should be used now. The Url of the folder containing the forum index page without the final /. Powerbb does its best to guess what this is, but make sure it is correct.');" onMouseOut="return nd();" alt="" /></dd>
				</dl>
				<input type="submit" value="Click here to continue" />
			</div>
			</form>
			</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
		</body>
		</html>