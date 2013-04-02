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
	</head>
	<body>
	<div class="Banner">
		<img src="../img/logo.png" border="0" alt="PowerBB Forum" />
	</div>
      <div class="Body">
		<div class="Contents">
			<h1>PowerBB Installation Wizard (Step 1 of 5)</h1><p>Before we can do much of anything, we need to make sure that you've got your directory &amp; file permissions set up properly. Below are the folders that need to be chmoded correctly. If the row containing the folder is green, then it has the correct permissions. If the row is red, then please fix the permissions.</p>
<table width="100%\"  border=\"1\" cellspacing=\"0\" cellpadding=\"3\" style=\"text-align:center;\">
         <tr>
        <th style="border:0px;"><b>File Name</b></th>
        <th style="border:0px;"><b>Needed Chmod</b></th>
        <th style="border:0px;"><b>Current Chmod</b></th>
    </tr>
    <?php 
		check_perms("../backup","0777");
        check_perms("../cache","0777");
		check_perms("../img","0777");
        check_perms("../img/avatars","0777");
		check_perms("../include/template","0777");
        check_perms("../include/user","0777");
		check_perms("../style","0777");
        check_perms("../uploads","0777");
		check_perms("../config.php","0666");
    ?>
</table>  
			<div class="Button"><a href="install_step_2.php">Once all permissions are set, click here to proceed to the next step</a></div>
		</div>
		</div>
		<div class="Foot">
			<a href="http://www.powerwd.com/index.php"><b>Eli White</b></a> <a href="http://www.powerwd.com/forum/index.php">PowerBB Forum</a> Copyright &copy; 2005 - 2006
		</div>
	</div>
	</body>
	</html>