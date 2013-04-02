<?php
define('FORUM_ROOT', '../');
$WorkingDirectory = getcwd()."/";
require FORUM_ROOT.'include/common.php';
$latest_version = str_replace('.', '', $configuration['o_cur_version']);
if (file_exists($latest_version))
{
echo "Click below to start the upgrade process. <br /> <a href=\"".$latest_version."\">Start!</a>";
}
else
{
echo "You are currently up to date. You are currently running: ".$configuration['o_cur_version'];
}
?>