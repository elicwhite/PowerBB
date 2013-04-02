<?php
//----------------------------------------------
// PowerBB
//----------------------------------------------
// All code is copyright to Power Software
// unless mentioned otherwise. This code
// may NOT be reproduced, or distributed
// by any means, unless you have explicit
// written permission from Power Software.
// Some code is derived from early versions
// of PunBB.
//-----------------------------------------------
// Copyright as of 2006
// All rights reserved
//-----------------------------------------------

define('FORUM_ROOT', './');
@include FORUM_ROOT.'config.php';
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);
require FORUM_ROOT.'include/functions.php';
require FORUM_ROOT.'include/parser.php';
require FORUM_ROOT.'include/dblayer/common_db.php';
$result = $db->query('SELECT * FROM '.$db->prefix.'config') or error('Unable to fetch forum config', __FILE__, __LINE__, $db->error());
while ($cur_config_item = $db->fetch_row($result)) $configuration[$cur_config_item[0]] = $cur_config_item[1];
$result = $db->query('SELECT g_read_board FROM '.$db->prefix.'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if ($db->result($result) == '0') exit('No permission');
@include FORUM_ROOT.'lang/'.$configuration['o_default_lang'].'/common.php';
if (!isset($lang_common)) exit('There is no valid language pack \''.$configuration['o_default_lang'].'\' installed. Please reinstall a language of that name.');
if ($configuration['o_rss_type'] == "0") message("RSS not available");
ob_start();
if (!empty($_GET["cid"]))
{
	$where = "	AND c.id = '".intval($_GET['cid'])."'";
	$title = 'cid';
}
else if (!empty($_GET["fid"]))
{
	$where = "	AND f.id = '".intval($_GET['fid'])."'";
	$title = 'fid';
}
else if (!empty($_GET["tid"]))
{
	$where = "	AND t.id = '".intval($_GET['tid'])."'";
	$title = 'tid';
}
else
{
	$where = '';
	$title = '';
}
$result = $db->query("SELECT p.id AS id, p.message AS message, p.posted AS postposted, t.subject AS subject, f.forum_name, c.cat_name FROM ".$db->prefix."posts p LEFT JOIN ".$db->prefix."topics t ON p.topic_id=t.id INNER JOIN ".$db->prefix."forums AS f ON f.id=t.forum_id LEFT JOIN ".$db->prefix."categories AS c ON f.cat_id = c.id LEFT JOIN ".$db->prefix."forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) $where ORDER BY postposted DESC LIMIT 0,15") or error('Unable to fetch forum posts', __FILE__, __LINE__, $db->error());;
$i = 0;
while ($cur = $db->fetch_assoc($result))
{
	if ($i == 0)
	{
		putHeader($cur, $title);
		$i++;
	}
	putPost($cur);
}
putEnd();
$feed = ob_get_contents();
ob_end_clean();
$eTag = '"'.md5($feed).'"';
header('Etag: '.$eTag);
if ($eTag == $_SERVER['HTTP_IF_NONE_MATCH'])
{	
	header("HTTP/1.0 304 Not Modified");
	header('Content-Length: 0');
}
else
{
	header ("Content-type: text/xml");
	echo $feed;
}

function entity_to_decimal_value($string)
{
	static $entities_dec = false;
	if (!is_array($entities_dec))
	{
		$entities_named = array("&nbsp;","&iexcl;","&cent;","&pound;","&curren;","&yen;","&brvbar;","&sect;","&uml;","&copy;","&ordf;","&laquo;","&not;","&shy;","&reg;","&macr;","&deg;","&plusmn;","&sup2;","&sup3;","&acute;","&micro;","&para;","&middot;","&cedil;","&sup1;","&ordm;","&raquo;","&frac14;","&frac12;","&frac34;","&iquest;","&Agrave;","&Aacute;","&Acirc;","&Atilde;","&Auml;","&Aring;","&AElig;","&Ccedil;","&Egrave;","&Eacute;","&Ecirc;","&Euml;","&Igrave;","&Iacute;","&Icirc;","&Iuml;","&ETH;","&Ntilde;","&Ograve;","&Oacute;","&Ocirc;","&Otilde;","&Ouml;","&times;","&Oslash;","&Ugrave;","&Uacute;","&Ucirc;","&Uuml;","&Yacute;","&THORN;","&szlig;","&agrave;","&aacute;","&acirc;","&atilde;","&auml;","&aring;","&aelig;","&ccedil;","&egrave;","&eacute;","&ecirc;","&euml;","&igrave;","&iacute;","&icirc;","&iuml;","&eth;","&ntilde;","&ograve;","&oacute;","&ocirc;","&otilde;","&ouml;","&divide;","&oslash;","&ugrave;","&uacute;","&ucirc;","&uuml;","&yacute;","&thorn;","&yuml;","&fnof;","&Alpha;","&Beta;","&Gamma;","&Delta;","&Epsilon;","&Zeta;","&Eta;","&Theta;","&Iota;","&Kappa;","&Lambda;","&Mu;","&Nu;","&Xi;","&Omicron;","&Pi;","&Rho;","&Sigma;","&Tau;","&Upsilon;","&Phi;","&Chi;","&Psi;","&Omega;","&alpha;","&beta;","&gamma;","&delta;","&epsilon;","&zeta;","&eta;","&theta;","&iota;","&kappa;","&lambda;","&mu;","&nu;","&xi;","&omicron;","&pi;","&rho;","&sigmaf;","&sigma;","&tau;","&upsilon;","&phi;","&chi;","&psi;","&omega;","&thetasym;","&upsih;","&piv;","&bull;","&hellip;","&prime;","&Prime;","&oline;","&frasl;","&weierp;","&image;","&real;","&trade;","&alefsym;","&larr;","&uarr;","&rarr;","&darr;","&harr;","&crarr;","&lArr;","&uArr;","&rArr;","&dArr;","&hArr;","&forall;","&part;","&exist;","&empty;","&nabla;","&isin;","&notin;","&ni;","&prod;","&sum;","&minus;","&lowast;","&radic;","&prop;","&infin;","&ang;","&and;","&or;","&cap;","&cup;","&int;","&there4;","&sim;","&cong;","&asymp;","&ne;","&equiv;","&le;","&ge;","&sub;","&sup;","&nsub;","&sube;","&supe;","&oplus;","&otimes;","&perp;","&sdot;","&lceil;","&rceil;","&lfloor;","&rfloor;","&lang;","&rang;","&loz;","&spades;","&clubs;","&hearts;","&diams;","&quot;","&amp;","&lt;","&gt;","&OElig;","&oelig;","&Scaron;","&scaron;","&Yuml;","&circ;","&tilde;","&ensp;","&emsp;","&thinsp;","&zwnj;","&zwj;","&lrm;","&rlm;","&ndash;","&mdash;","&lsquo;","&rsquo;","&sbquo;","&ldquo;","&rdquo;","&bdquo;","&dagger;","&Dagger;","&permil;","&lsaquo;","&rsaquo;","&euro;","&apos;");
		$entities_decimal	= array("&#160;","&#161;","&#162;","&#163;","&#164;","&#165;","&#166;","&#167;","&#168;","&#169;","&#170;","&#171;","&#172;","&#173;","&#174;","&#175;","&#176;","&#177;","&#178;","&#179;","&#180;","&#181;","&#182;","&#183;","&#184;","&#185;","&#186;","&#187;","&#188;","&#189;","&#190;","&#191;","&#192;","&#193;","&#194;","&#195;","&#196;","&#197;","&#198;","&#199;","&#200;","&#201;","&#202;","&#203;","&#204;","&#205;","&#206;","&#207;","&#208;","&#209;","&#210;","&#211;","&#212;","&#213;","&#214;","&#215;","&#216;","&#217;","&#218;","&#219;","&#220;","&#221;","&#222;","&#223;","&#224;","&#225;","&#226;","&#227;","&#228;","&#229;","&#230;","&#231;","&#232;","&#233;","&#234;","&#235;","&#236;","&#237;","&#238;","&#239;","&#240;","&#241;","&#242;","&#243;","&#244;","&#245;","&#246;","&#247;","&#248;","&#249;","&#250;","&#251;","&#252;","&#253;","&#254;","&#255;","&#402;","&#913;","&#914;","&#915;","&#916;","&#917;","&#918;","&#919;","&#920;","&#921;","&#922;","&#923;","&#924;","&#925;","&#926;","&#927;","&#928;","&#929;","&#931;","&#932;","&#933;","&#934;","&#935;","&#936;","&#937;","&#945;","&#946;","&#947;","&#948;","&#949;","&#950;","&#951;","&#952;","&#953;","&#954;","&#955;","&#956;","&#957;","&#958;","&#959;","&#960;","&#961;","&#962;","&#963;","&#964;","&#965;","&#966;","&#967;","&#968;","&#969;","&#977;","&#978;","&#982;","&#8226;","&#8230;","&#8242;","&#8243;","&#8254;","&#8260;","&#8472;","&#8465;","&#8476;","&#8482;","&#8501;","&#8592;","&#8593;","&#8594;","&#8595;","&#8596;","&#8629;","&#8656;","&#8657;","&#8658;","&#8659;","&#8660;","&#8704;","&#8706;","&#8707;","&#8709;","&#8711;","&#8712;","&#8713;","&#8715;","&#8719;","&#8721;","&#8722;","&#8727;","&#8730;","&#8733;","&#8734;","&#8736;","&#8743;","&#8744;","&#8745;","&#8746;","&#8747;","&#8756;","&#8764;","&#8773;","&#8776;","&#8800;","&#8801;","&#8804;","&#8805;","&#8834;","&#8835;","&#8836;","&#8838;","&#8839;","&#8853;","&#8855;","&#8869;","&#8901;","&#8968;","&#8969;","&#8970;","&#8971;","&#9001;","&#9002;","&#9674;","&#9824;","&#9827;","&#9829;","&#9830;","&#34;","&#38;","&#60;","&#62;","&#338;","&#339;","&#352;","&#353;","&#376;","&#710;","&#732;","&#8194;","&#8195;","&#8201;","&#8204;","&#8205;","&#8206;","&#8207;","&#8211;","&#8212;","&#8216;","&#8217;","&#8218;","&#8220;","&#8221;","&#8222;","&#8224;","&#8225;","&#8240;","&#8249;","&#8250;","&#8364;","&#39;");
		if (function_exists('array_combine')) $entities_dec=array_combine($entities_named,$entities_decimal);
		else
		{
			$i=0;
			foreach ($entities_named as $_entities_named) $entities_dec[$_entities_named]=$entities_decimal[$i++];
		}
	}
	return preg_replace( "/&[A-Za-z]+;/", " ", strtr($string,$entities_dec) );
}

function encode_xml($data)
{
	$data=str_replace('<br(.*?)>',"\n",$data);
	$data=preg_replace("/<\/(pre|ul|li|p|table|tr)>/","\n",$data);
	$data=preg_replace("/<(.*?)>/","",$data);
	$data=preg_replace("/\n\n+/","\n\n",$data);
	return entity_to_decimal_value($data);
}

function putHeader($cur, $title)
{ 
	switch ($title)
	{
		case "cid":
			$title = ' : '.$cur['cat_name'];
			break;
		case "fid":
			$title = ' : '.$cur['cat_name'].' : '.$cur['forum_name'];
			break;
		case "tid":
			$title = ' : '.$cur['cat_name'].' : '.$cur['forum_name'].' : '.$cur['subject'];
			break;
		default:
			$title = '';
			break;
	}
	global $lang_common,$configuration;
	if ($configuration['o_rss_type'] == "1")
	{
		echo '<'.'?xml version="1.0" encoding="'.$lang_common['lang_encoding'].'"?'.'>'."\n";
		echo '<rdf:RDF'."\n";
		echo "\t".'xmlns="http://purl.org/rss/1.0/"'."\n";
		echo "\t".'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
		echo "\t".'xmlns:slash="http://purl.org/rss/1.0/modules/slash/"'."\n";
		echo "\t".'xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		echo '<channel rdf:about="'.$configuration['o_base_url'].'">'."\n";
		echo "\t".'<title>'.entity_to_decimal_value(htmlspecialchars($configuration['o_board_name'].$title)).'</title>'."\n";
		echo "\t".'<description>'.entity_to_decimal_value(htmlspecialchars($rss_description.' '.$configuration['o_board_name'])).'</description>'."\n";
		echo "\t".'<link>'.$configuration['o_base_url'].'</link>'."\n";
		echo "\t".'<image rdf:resource="'.$configuration['o_base_url'].'/img/general/button-rss.png" />'."\n";
		echo "\t".'<dc:date>'.strval(date('l dS \of F Y h:i:s A')).'</dc:date>'."\n";
		echo '</channel>'."\n";
	}
	else if ($configuration['o_rss_type'] == "2")
	{
		echo '<'.'?xml version="1.0" encoding="'.$lang_common['lang_encoding'].'"?'.'>'."\n";
		echo "<rss version=\"2.0\">\n";
		echo "<channel>\n";
		echo "<title>".entity_to_decimal_value(htmlspecialchars($configuration['o_board_name'].$title))."</title>\n";
		echo "<link>".$configuration['o_base_url']."</link>\n";
		echo "<description>".entity_to_decimal_value(htmlspecialchars($rss_description.' '.$configuration['o_board_name']))."</description>\n";
		echo "<language>en</language>\n";
		echo "<docs>http://backend.userland.com/rss</docs>\n";
	}
}

function putPost($cur)
{
	global $configuration;
	if ($configuration['o_rss_type'] == "1")
	{
		$link = $configuration['o_base_url'].'/view_topic.php?pid='.strval($cur['id']).'#'.strval($cur['id']);
		echo "<item rdf:about=\"".entity_to_decimal_value(htmlspecialchars($link))."\">"."\n";
		echo "\t"."<dc:format>text/html</dc:format>"."\n";
		echo "\t"."<dc:date>".strval(date("r",$cur['postposted']))."</dc:date>"."\n";
		echo "\t"."<dc:source>".$configuration['o_base_url']."</dc:source>"."\n";
		echo "\t"."<title>".entity_to_decimal_value(htmlspecialchars($cur['subject'].' in '.$cur['cat_name'].' : '.$cur['forum_name']))."</title>"."\n";
		echo "\t"."<link>".entity_to_decimal_value(htmlspecialchars($link))."</link>"."\n";
		$data = "Topic: ".parse_message($cur['subject'],0)."\n\nMessage: ".parse_message($cur['message'],0);
		echo "\t"."<description>".encode_xml($data)."</description>"."\n";
		echo "</item>\n";
	}
	else if ($configuration['o_rss_type'] == "2")
	{
		echo "<item>\n";
		echo "<title>".entity_to_decimal_value(htmlspecialchars($cur['subject'].' in '.$cur['cat_name'].' : '.$cur['forum_name']))."</title>\n";
		$link = $configuration['o_base_url'].'/view_topic.php?pid='.strval($cur['id']).'#'.strval($cur['id']);
		echo "<link>".entity_to_decimal_value(htmlspecialchars($link))."</link>\n";
		echo '<guid isPermaLink="false">'.strval($cur['id']).'@'.$configuration['o_base_url'].'</guid>'."\n";
		$data = "Topic: ".parse_message($cur['subject'],0)."\n\nMessage: ".parse_message($cur['message'],0);
		echo "<description>".encode_xml($data)."</description>\n";
//		echo "<content:encoded><![CDATA[".$data."]]></content:encoded>\n";
		echo "<pubDate>".strval(date("r",$cur['postposted']))."</pubDate>\n";
		echo "</item>\n";
	}
}

function putEnd()
{
	global $configuration;
	if ($configuration['o_rss_type'] == "1")
	{
		echo "</rdf:RDF>\n";
	}
	else if ($configuration['o_rss_type'] == "2")
	{
		echo "</channel>\n";
		echo "</rss>\n";
	}
}
?>