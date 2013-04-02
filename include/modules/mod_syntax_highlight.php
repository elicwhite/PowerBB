<?
$syn_class = $codename.'_syn';
if (!class_exists($syn_class))
{
	include_once(FORUM_ROOT.'include/modules/mod_syntax_highlight_plan.php');
	$syn_file = FORUM_ROOT.'include/modules/highlighters/'.$codename.'.php';
	if (file_exists($syn_file)) include_once($syn_file);
	else $syn_class='plan_code_syn';
}
$syn = new $syn_class;
$code = $syn->highlight_code($code);
unset($syn);
?>