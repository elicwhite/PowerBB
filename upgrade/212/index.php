<?php
define('FORUM_ROOT', '../../');
$WorkingDirectory = getcwd()."/";
require FORUM_ROOT.'include/common.php';
?>
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html>
	<head>
		<title>PowerBB Upgrade Wizard</title>
		<link rel="stylesheet" type="text/css" href="../../include/css/install.css" />
	</head>
	<body>
	<div class="Banner">
		<img src="../../img/logo.png" border="0" alt="PowerBB Forum" />
	</div>
      <div class="Body">
		<div class="Contents">
			<h1>PowerBB Upgrade Wizard</h1><p>This upgrade wizard will upgrade your forum version from <?php echo $configuration['o_cur_version'] ?>. </p>
			<div class="Button"><a href="upgrade212.php">Click here to upgrade and continue.</a></div>
		</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
	</div>
	</body>
	</html>