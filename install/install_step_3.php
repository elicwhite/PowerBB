<?php
include_once ('functions.php');
$action = isset($_GET['action']) ? $_GET['action'] : NULL;
$WorkingDirectory = getcwd()."/";
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html>
	<head>
		<title>PowerBB Installer</title>
		<link rel="stylesheet" type="text/css" href="../include/css/install.css" />
		<style type="text/css">
			input {background-color:#CCCCCC; border: none; padding: 3px;}
			.submit {font-size: medium; background-color:#66CC33;}
		</style>
	</head>
	<body>
	<div class="Banner">
		<img src="../img/logo.png" border="0" alt="PowerBB Forum" />
	</div>
      <div class="Body">
		<div class="Contents">
			<h1>PowerBB Installation Wizard (Step 3 of 5)</h1><p>Please review the below information to make sure it is correct. If a field is incorrect, please go back and correct them. When you click the final button, PowerBB will create all of the tables, and fields required to run PowerBB. This may take a few moments, so please be patient. You will have a conformation message when it is complete.</p>
<?php
	$db_type = $_POST['req_db_type'];
	$db_host = trim($_POST['req_db_host']);
	$db_name = trim($_POST['req_db_name']);
	$db_username = unescape(trim($_POST['db_username']));
	$db_password = unescape(trim($_POST['db_password']));
	$db_prefix = trim($_POST['db_prefix']);
	$username = unescape(trim($_POST['req_username']));
	$your_name = unescape(trim($_POST['req_your_name']));
	$your_company = unescape(trim($_POST['req_company']));
	$email = strtolower(trim($_POST['req_email']));
	$password1 = unescape(trim($_POST['req_password1']));
	$password2 = unescape(trim($_POST['req_password2']));
	if (substr($_POST['req_base_url'], -1) == '/') $base_url = substr($_POST['req_base_url'], 0, -1);
	else $base_url = $_POST['req_base_url'];
?>
<form id="install" name="frmDatabase" method="post" action="install_step_4.php">
			<input type="hidden" id="form_sent" name="form_sent" value="1" />
			<input type="hidden" id="req_db_type" name="req_db_type" value="mysql" />
			<div class="Form">
				<dl>
					<dt>Database type</dt>
					<dd><input type="text" name="req_db_type" value="<?php echo $db_type?>" readonly="readonly" /></dd>
					<dt>Database Server</dt>
					<dd><input type="text" name="req_db_host" value="<?php echo $db_host?>" readonly="readonly" /></dd>
					<dt>Database Name</dt>
					<dd><input type="text" name="req_db_name" value="<?php echo $db_name?>" readonly="readonly" /></dd>
					<dt>Database User</dt>
					<dd><input type="text" name="db_username" value="<?php echo $db_username?>" readonly="readonly" /></dd>
					<dt>Database Password</dt>
					<dd><input type="text" name="db_password" value="<?php echo $db_password?>" readonly="readonly" /></dd>
					<dt>Database Prefix</dt>
					<dd><input type="text" name="db_prefix" value="<?php echo $db_prefix?>" readonly="readonly" /></dd>
					<br /><br />
					<dt>Administrator Username</dt>
					<dd><input type="text" name="req_username" value="<?php echo $username?>" readonly="readonly" /></dd>
					<dt>Administrator Password</dt>
					<dd><input type="text" name="req_password1" value="<?php echo $password1?>" readonly="readonly" /></dd>
					<dt>Administrator Confirm Password</dt>
					<dd><input type="text" name="req_password2" value="<?php echo $password2?>" readonly="readonly" /></dd>
					<dt>Administrator E-Mail</dt>
					<dd><input type="text" name="req_email" value="<?php echo $email?>" readonly="readonly" /></dd>
					<dt>Registered to (username)</dt>
					<dd><input type="text" name="req_your_name" value="<?php echo $your_name?>" readonly="readonly" /></dd>
					<dt>Registered to (company)</dt>
					<dd><input type="text" name="req_company" value="<?php echo $your_company?>" readonly="readonly" /></dd>
					<dt>Base Forum URL</dt>
					<dd><input type="text" name="req_base_url" value="<?php echo $base_url?>" readonly="readonly" /></dd>
				</dl>
				<input type="submit" value="Click here to continue" />
			</div>
			</form>
		</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
	</div>
	</body>
	</html>