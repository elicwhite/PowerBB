<?php
ob_start();
if (!file_exists(dirname(__FILE__).'/phpthumb.functions.php') || !include_once(dirname(__FILE__).'/phpthumb.functions.php'))
{
	ob_end_flush();
	die('failed to include_once(phpthumb.functions.php) - realpath="'.realpath(dirname(__FILE__).'/phpthumb.functions.php').'"');
}
ob_end_clean();

$PHPTHUMB_CONFIG['document_root'] = realpath((getenv('DOCUMENT_ROOT') && ereg('^'.preg_quote(realpath(getenv('DOCUMENT_ROOT'))), realpath(__FILE__))) ? getenv('DOCUMENT_ROOT') : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace($phpThumb->osslash, '/', dirname(__FILE__))));
$PHPTHUMB_CONFIG['cache_directory'] = dirname(__FILE__).'/cache/';
$PHPTHUMB_CONFIG['cache_disable_warning'] = false;
$PHPTHUMB_CONFIG['cache_maxage'] = null;
$PHPTHUMB_CONFIG['cache_maxsize'] = 10485760;
$PHPTHUMB_CONFIG['cache_maxfiles'] = null;
$PHPTHUMB_CONFIG['cache_source_enabled']   = false;
$PHPTHUMB_CONFIG['cache_source_directory'] = dirname(__FILE__).'/cache/source/';
$PHPTHUMB_CONFIG['cache_source_filemtime_ignore_local']  = false;
$PHPTHUMB_CONFIG['cache_source_filemtime_ignore_remote'] = false;
$PHPTHUMB_CONFIG['cache_differentiate_offsite'] = true;
$PHPTHUMB_CONFIG['cache_default_only_suffix'] = '';
$PHPTHUMB_CONFIG['temp_directory'] = null;

if (phpthumb_functions::version_compare_replacement(phpversion(), '4.3.2', '>=') && !defined('memory_get_usage') && !@ini_get('memory_limit'))
{
	$PHPTHUMB_CONFIG['max_source_pixels'] = 0;
}
else
{
	$PHPTHUMB_CONFIG['max_source_pixels'] = round(max(intval(ini_get('memory_limit')), intval(get_cfg_var('memory_limit'))) * 1048576 * 0.20);
}

if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
{
	$PHPTHUMB_CONFIG['imagemagick_path'] = 'C:/Program Files/ImageMagick-6.2.3-Q16/convert.exe';
}
else
{
	$PHPTHUMB_CONFIG['imagemagick_path'] = null;
}

$PHPTHUMB_CONFIG['prefer_imagemagick'] = true;
$PHPTHUMB_CONFIG['output_format']    = 'jpeg';
$PHPTHUMB_CONFIG['output_maxwidth']  = 0;
$PHPTHUMB_CONFIG['output_maxheight'] = 0;
$PHPTHUMB_CONFIG['output_interlace'] = true;
$PHPTHUMB_CONFIG['error_image_width']           = 400;
$PHPTHUMB_CONFIG['error_image_height']          = 100;
$PHPTHUMB_CONFIG['error_message_image_default'] = '';
$PHPTHUMB_CONFIG['error_bgcolor']               = 'CCCCFF';
$PHPTHUMB_CONFIG['error_textcolor']             = 'FF0000';
$PHPTHUMB_CONFIG['error_fontsize']              = 1;
$PHPTHUMB_CONFIG['error_die_on_error']          = true;
$PHPTHUMB_CONFIG['error_silent_die_on_error']   = false;
$PHPTHUMB_CONFIG['error_die_on_source_failure'] = true;
$PHPTHUMB_CONFIG['nohotlink_enabled']           = true;
$PHPTHUMB_CONFIG['nohotlink_valid_domains']     = array(@$_SERVER['HTTP_HOST']);
$PHPTHUMB_CONFIG['nohotlink_erase_image']       = true;
$PHPTHUMB_CONFIG['nohotlink_text_message']      = 'Off-server thumbnailing is not allowed';
$PHPTHUMB_CONFIG['nooffsitelink_enabled']       = true;
$PHPTHUMB_CONFIG['nooffsitelink_valid_domains'] = array(@$_SERVER['HTTP_HOST']);
$PHPTHUMB_CONFIG['nooffsitelink_require_refer'] = false;
$PHPTHUMB_CONFIG['nooffsitelink_erase_image']   = true;
$PHPTHUMB_CONFIG['nooffsitelink_text_message']  = 'Image taken from '.@$_SERVER['HTTP_HOST'];
$PHPTHUMB_CONFIG['border_hexcolor']     = '000000';
$PHPTHUMB_CONFIG['background_hexcolor'] = 'FFFFFF';
$PHPTHUMB_CONFIG['ttf_directory'] = dirname(__FILE__).'/fonts';
$PHPTHUMB_CONFIG['mysql_query'] = '';
$PHPTHUMB_CONFIG['mysql_hostname'] = 'localhost';
$PHPTHUMB_CONFIG['mysql_username'] = '';
$PHPTHUMB_CONFIG['mysql_password'] = '';
$PHPTHUMB_CONFIG['mysql_database'] = '';
$PHPTHUMB_CONFIG['high_security_enabled']    = false;
$PHPTHUMB_CONFIG['high_security_password']   = '';
$PHPTHUMB_CONFIG['disable_debug']            = false;
$PHPTHUMB_CONFIG['allow_src_above_docroot']  = false;
$PHPTHUMB_CONFIG['allow_src_above_phpthumb'] = true;
$PHPTHUMB_CONFIG['allow_parameter_file']     = false;
$PHPTHUMB_CONFIG['allow_parameter_goto']     = false;
$PHPTHUMB_CONFIG['config_prefer_imagemagick']    = true;
$PHPTHUMB_CONFIG['use_exif_thumbnail_for_speed'] = false;
$PHPTHUMB_DEFAULTS_GETSTRINGOVERRIDE = true;
$PHPTHUMB_DEFAULTS_DISABLEGETPARAMS  = false;

function phpThumbURL($ParameterString)
{
	global $PHPTHUMB_CONFIG;
	return 'phpThumb.php?'.$ParameterString.'&hash='.md5($ParameterString.$PHPTHUMB_CONFIG['high_security_password']);
}
?>