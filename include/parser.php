<?php
if (!defined('IN_FORUM')) exit;
$smiley_text = array(':)', '=)', ':(', '=(', ':D', '=D', ';)', ':/', ':P', ':lol:', ':angel:', ':angry:', ':argue:', ':biggrin:', ':blink:', ':blush:', ':bored:', ':clapping:', ':cool:', ':dry:', ':eek:', ':first:', ':glare:', ':happy:', ':hug:', ':huh:', ':laugh:', ':mad2:', ':mad:', ':mellow:', ':ohmy:', ':ninja:', ':rolleyes:', ':sad:', ':cold:', ':sleep:', ':smile:', ':tongue:', ':unsure:', ':wacko:', ':walkman:', ':wink:', ':wub:');
$smiley_img = array('smiley-smile.gif', 'smiley-smile.gif', 'smiley-sad.gif', 'smiley-sad.gif', 'smiley-laugh.gif', 'smiley-laugh.gif', 'smiley-wink.gif', 'smiley-unsure.gif', 'smiley-tongue.gif', 'smiley-smile.gif', 'smiley-angel.gif', 'smiley-angry.gif', 'smiley-argue.gif', 'smiley-biggrin.gif', 'smiley-blink.gif', 'smiley-blush.gif', 'smiley-boredlook.gif', 'smiley-clapping.gif', 'smiley-cool.gif', 'smiley-dry.gif', 'smiley-eek.gif', 'smiley-first.gif', 'smiley-glare.gif', 'smiley-happy.gif', 'smiley-hug.gif', 'smiley-huh.gif', 'smiley-laugh.gif', 'smiley-mad2.gif', 'smiley-mad.gif', 'smiley-mellow.gif', 'smiley-ohmy.gif', 'smiley-ph34r.gif', 'smiley-rolleyes.gif', 'smiley-sad.gif', 'smiley-samui.gif', 'smiley-sleep.gif', 'smiley-smile.gif', 'smiley-tongue.gif', 'smiley-unsure.gif', 'smiley-wacko.gif', 'smiley-walkman.gif', 'smiley-wink.gif', 'smiley-wub.gif');
// Uncomment the next row if you add smilies that contain any of the characters &"'<>
//$smiley_text = array_map('convert_htmlspecialchars', $smiley_text);

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $errors
 * @param unknown_type $is_signature
 * @return unknown

 */
function preparse_bbcode($text, &$errors, $is_signature = false)
{
	$a = array('[B]', '[I]', '[U]', '[PHP]','[/B]', '[/I]', '[/U]', '[/PHP]');
	$b = array('[b]', '[i]', '[u]', '[php]','[/b]', '[/i]', '[/u]', '[/php]');
	$text = str_replace($a, $b, $text);
	$a = array( '#\[url=("|\'|)(.*?)\\1\]\s*#i',
				'#\[url\]\s*#i',
				'#\s*\[/url\]#i',
				'#\[email=("|\'|)(.*?)\\1\]\s*#i',
				'#\[email\]\s*#i',
				'#\s*\[/email\]#i',
				'#\[img\]\s*(.*?)\s*\[/img\]#is',
				'#\[colou?r=("|\'|)(.*?)\\1\](.*?)\[/colou?r\]#is');
	$b = array(	'[url=$2]',
				'[url]',
				'[/url]',
				'[email=$2]',
				'[email]',
				'[/email]',
				'[img]$1[/img]',
				'[color=$2]$3[/color]');
	if (!$is_signature)
	{
		$a[] = '#\[quote=(&quot;|"|\'|)(.*?)\\1\]\s*#i';
		$a[] = '#\[quote\]\s*#i';
		$a[] = '#\s*\[/quote\]\s*#i';
		$a[] = '#\[code\][\r\n]*(.*?)\s*\[/code\]\s*#is';
		$b[] = '[quote=$1$2$1]';
		$b[] = '[quote]';
		$b[] = '[/quote]'."\n";
		$b[] = '[code]$1[/code]'."\n";
	}
	$text = preg_replace($a, $b, $text);
	if (!$is_signature)
	{
		$overflow = check_tag_order($text, $error);
		if ($error) $errors[] = $error;
		else if ($overflow) $text = substr($text, 0, $overflow[0]).substr($text, $overflow[1], (strlen($text) - $overflow[0]));
	}
	else
	{
		global $lang_prof_reg;
		if (preg_match('#\[quote=(&quot;|"|\'|)(.*)\\1\]|\[quote\]|\[/quote\]|\[code\]|\[/code\]#i', $text)) message($lang_prof_reg['Signature quote/code']);
	}
	return trim($text);
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $error
 * @return unknown

 */
function check_tag_order($text, &$error)
{
	global $lang_common;
	$max_depth = 3;
	$cur_index = 0;
	$q_depth = 0;
	while (true)
	{
            if (preg_match('#\[code=?(.*?)\]#s', $text, $matches)) $c_start = strpos($text, $matches[0]);
            else $c_start = 65536;
		$c_end = strpos($text, '[/code]');
		$q_start = strpos($text, '[quote]');
		$q_end = strpos($text, '[/quote]');
		if (preg_match('#\[quote=(&quot;|"|\'|)(.*)\\1\]#sU', $text, $matches)) $q2_start = strpos($text, $matches[0]);
		else $q2_start = 65536;
		if ($c_start === false) $c_start = 65536;
		if ($c_end === false) $c_end = 65536;
		if ($q_start === false) $q_start = 65536;
		if ($q_end === false) $q_end = 65536;
		if (min($c_start, $c_end, $q_start, $q_end, $q2_start) == 65536) break;
		$q3_start = ($q_start < $q2_start) ? $q_start : $q2_start;
		if ($q3_start < min($q_end, $c_start, $c_end))
		{
			$step = ($q_start < $q2_start) ? 7 : strlen($matches[0]);
			$cur_index += $q3_start + $step;
			if ($q_depth == $max_depth) $overflow_begin = $cur_index - $step;
			++$q_depth;
			$text = substr($text, $q3_start + $step);
		}
		else if ($q_end < min($q_start, $c_start, $c_end))
		{
			if ($q_depth == 0)
			{
				$error = $lang_common['BBCode error'].' '.$lang_common['BBCode error 1'];
				return;
			}
			$q_depth--;
			$cur_index += $q_end+8;
			if ($q_depth == $max_depth) $overflow_end = $cur_index;
			$text = substr($text, $q_end+8);
		}
		else if ($c_start < min($c_end, $q_start, $q_end))
		{
			$tmp = strpos($text, '[/code]');
			$tmp2 = strpos(substr($text, $c_start+6), '[code]');
			if ($tmp2 !== false) $tmp2 += $c_start+6;
			if ($tmp === false || ($tmp2 !== false && $tmp2 < $tmp))
			{
				$error = $lang_common['BBCode error'].' '.$lang_common['BBCode error 2'];
				return;
			}
			else $text = substr($text, $tmp+7);
			$cur_index += $tmp+7;
		}
		else if ($c_end < min($c_start, $q_start, $q_end))
		{
			$error = $lang_common['BBCode error'].' '.$lang_common['BBCode error 3'];
			return;
		}
	}
	if ($q_depth)
	{
		$error = $lang_common['BBCode error'].' '.$lang_common['BBCode error 4'];
		return;
	}
	else if ($q_depth < 0)
	{
		$error = $lang_common['BBCode error'].' '.$lang_common['BBCode error 5'];
		return;
	}
	if (isset($overflow_begin)) return array($overflow_begin, $overflow_end);
	else return null;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $start
 * @param unknown_type $end
 * @return unknown

 */
function split_text($text, $start, $end)
{
	global $configuration;
	$tokens = explode($start, $text);
	$outside[] = $tokens[0];
	$num_tokens = count($tokens);
	for ($i = 1; $i < $num_tokens; ++$i)
	{
		$temp = explode($end, $tokens[$i]);
		$inside[] = $temp[0];
		$outside[] = $temp[1];
	}
	if ($configuration['o_indent_num_spaces'] != 8 && $start == '[code]')
	{
		$spaces = str_repeat(' ', $configuration['o_indent_num_spaces']);
		$inside = str_replace("\t", $spaces, $inside);
	}
	return array($inside, $outside);
}

/**
 * Enter description here...
 *
 * @param unknown_type $url
 * @param unknown_type $link
 * @return unknown

 */
function handle_url_tag($url, $link = '')
{
	global $forum_user;
	$full_url = str_replace(array(' ', '\'', '`'), array('%20', '', ''), $url);
	if (strpos($url, 'www.') === 0) $full_url = 'http://'.$full_url;
	else if (strpos($url, 'ftp.') === 0) $full_url = 'ftp://'.$full_url;
	else if (!preg_match('#^([a-z0-9]{3,6})://#', $url, $bah)) $full_url = 'http://'.$full_url;
	$link = ($link == '' || $link == $url) ? ((strlen($url) > 55) ? substr($url, 0 , 39).' &hellip; '.substr($url, -10) : $url) : stripslashes($link);
	return '<a href="'.$full_url.'">'.$link.'</a>';
}

/**
 * Enter description here...
 *
 * @param unknown_type $url
 * @param unknown_type $is_signature
 * @return unknown

 */
function handle_img_tag($url, $is_signature = false)
{
	global $lang_common, $configuration, $forum_user;
	$img_tag = '<a rel="lightbox" href="'.$url.'">&lt;'.$lang_common['Image link'].'&gt;</a>';
	if ($is_signature && $forum_user['show_img_sig'] != '0') $img_tag = '<img class="sigimage" src="'.$url.'" alt="'.htmlspecialchars($url).'" />';
	else if (!$is_signature && $forum_user['show_img'] != '0') $img_tag = '<img class="postimg" src="'.$url.'" alt="'.htmlspecialchars($url).'" />';
	return $img_tag;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function do_bbcode_preview($text)
{
	global $lang_common, $forum_user;
	if (strpos($text, 'quote') !== false)
	{
		$text = str_replace('[quote]', '', $text);
		$text = preg_replace('#\[quote=(&quot;|"|\'|)(.*)\\1\]#seU', '"".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].""', $text);
		$text = preg_replace('#\[\/quote\]\s*#', '', $text);
	}
	$pattern = array('#\[b\](.*?)\[/b\]#s',
					 '#\[i\](.*?)\[/i\]#s',
					 '#\[u\](.*?)\[/u\]#s',
					 '#\[url\]([^\[]*?)\[/url\]#',
					 '#\[url=([^\[]*?)\](.*?)\[/url\]#',
					 '#\[email\]([^\[]*?)\[/email\]#',
					 '#\[email=([^\[]*?)\](.*?)\[/email\]#',
					 '#\[ul\](.*?)\[/ul\]#s',
					 '#\[ol\](.*?)\[/ol\]#',
					 '#\[uli\](.*?)\[/uli\]#',
					 '#\[oli\](.*?)\[/oli\]#',
					 '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.*?)\[/color\]#s',
					 '#\[font=(.*?)](.*?)\[/font\]#',
					 '#\[align=(.*?)\](.*?)\[/align\]#',
					 '#\[s\](.*?)\[/s\]#',
					 '#\[pre\](.*?)\[/pre\]#',
					 '#\[sup\](.*?)\[/sup\]#',
					 '#\[sub\](.*?)\[/sub\]#');
	$replace = array('$1',
					 '$1',
					 '$1',
					 '$1',
					 '  ',
					 '$1',
					 '  ',
					 '$1',
					 '$1',
					 '&#149;&nbsp;&nbsp;$1',
					 '$1',
					 '$2',
					 '$2',
					 '$2',
					 '$1',
					 '$1',
					 '$1',
					 '$1');
	$text = preg_replace($pattern, $replace, $text);
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function do_bbcode($text)
{
	global $lang_common, $forum_user, $cur_post;
	if ($cur_post['g_id'] == USER_ADMIN)
	{
		preg_match_all('#\[php\](.*?)\[/php\]#s', $text, $match);
		foreach ($match[1] as $key => $include) 
		{
			if (substr($include, -4) == '.php')
			{
				$return = '';
				@include($_SERVER['DOCUMENT_ROOT'].$include);
				$text = str_replace($match[0][$key], $return, $text);
				unset($return);
			}
			else
			{
				$text = str_replace($match[0][$key], '<!-- FAILED BBINC -->', $text);
			}
		}
	}
	if (strpos($text, 'quote') !== false)
	{
		$text = str_replace('[quote]', '</p><blockquote><div class="incqbox"><p>', $text);
		$text = preg_replace('#\[quote=(&quot;|"|\'|)(.*)\\1\]#seU', '"</p><blockquote><div class=\"incqbox\"><h4>".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].":</h4><p>"', $text);
		$text = preg_replace('#\[\/quote\]\s*#', '</p></div></blockquote><p>', $text);
	}
	$pattern = array('#\[b\](.*?)\[/b\]#s',
					 '#\[i\](.*?)\[/i\]#s',
					 '#\[u\](.*?)\[/u\]#s',
					 '#\[url\]([^\[]*?)\[/url\]#e',
					 '#\[url=([^\[]*?)\](.*?)\[/url\]#e',
					 '#\[email\]([^\[]*?)\[/email\]#e',
					 '#\[email=([^\[]*?)\](.*?)\[/email\]#e',
					 '#\[ul\](.*?)\[/ul\]#s',
					 '#\[ol\](.*?)\[/ol\]#',
					 '#\[uli\](.*?)\[/uli\]#',
					 '#\[oli\](.*?)\[/oli\]#',
					 '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.*?)\[/color\]#s',
					 '#\[font=(.*?)](.*?)\[/font\]#',
					 '#\[align=(.*?)\](.*?)\[/align\]#',
					 '#\[hr /\]#',
					 '#\[hr\]#',
					 '#\[s\](.*?)\[/s\]#',
					 '#\[pre\](.*?)\[/pre\]#',
					 '#\[sup\](.*?)\[/sup\]#',
					 '#\[sub\](.*?)\[/sub\]#',
					 '#\[h\](.*?)\[/h\]#');
	$replace = array('<strong>$1</strong>',
					 '<em>$1</em>',
					 '<span class="bbu">$1</span>',
					 'handle_url_tag(\'$1\')',
					 'handle_url_tag(\'$1\', \'$2\')',
					 'handle_email_tag(\'$1\')',
					 'handle_email_tag(\'$1\',\'$2\')',
					 '<ul>$1</ul>',
					 '<ol>$1</ol>',
					 '<li>&#149;&nbsp;&nbsp;$1</li>',
					 '<li>$1</li>',
					 '<span style="color: $1">$2</span>',
					 '<span style="font-family: $1">$2</span>',
					 '<div align="$1">$2</div>',
					 '<hr />',
					 '<hr />',
					 '<del>$1</del>',
					 '<pre>$1</pre>',
					 '<sup>$1</sup>',
					 '<sub>$1</sub>',
					 '<span style="background-color: #FFFF00; color: #000000">$1</span>');
	$text = preg_replace($pattern, $replace, $text);
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function do_clickable($text)
{
	global $forum_user;
	$text = ' '.$text;
	$text = preg_replace('#([\s\(\)])(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2://$3\')', $text);
	$text = preg_replace('#([\s\(\)])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2.$3\', \'$2.$3\')', $text);
	$text = preg_replace('#([\s\(\)])((mailto:)?([\w\d][\w\d$.-]*[\w\d]@[\w\d][\w\d.-]*[\w\d]\.[a-z0-9]{2,5}))((/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_email_tag(\'$2\')', $text);
	return substr($text, 1);
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function do_smilies($text)
{
	global $smiley_text, $smiley_img;
	$text = ' '.$text.' ';
	$num_smilies = count($smiley_text);
	for ($i = 0; $i < $num_smilies; ++$i) $text = preg_replace("#(?<=.\W|\W.|^\W)".preg_quote($smiley_text[$i], '#')."(?=.\W|\W.|\W$)#m", '$1<img src="img/smilies/'.$smiley_img[$i].'" alt="'.substr($smiley_img[$i], 0, strrpos($smiley_img[$i], '.')).'" />$2', $text);
	return substr($text, 1, -1);
}

/**
 * Enter description here...
 *
 * @param unknown_type $codename
 * @param unknown_type $code
 * @return unknown

 */
function parse_code($codename, $code)
{
	global $configuration, $lang_common, $forum_user;
	if (!empty($codename))
	{
		include(FORUM_ROOT.'include/modules/mod_syntax_highlight.php');
	}
	else
	{
		if ($configuration['o_indent_num_spaces'] != 8)
		{
			$spaces = str_repeat(' ', $configuration['o_indent_num_spaces']);
			$code = str_replace("\t", $spaces, trim($code));
		}
	}
	if (!empty($codename)) $codename= $lang_common['Code'].': '.$codename;
	else $codename= $lang_common['Code'].':';
	$num_lines = ((substr_count($code, "\n")) + 3) * 1.5;
	$height_str = ($num_lines > 35) ? '35em' : $num_lines.'em';
	return '</p><div class="codebox"><div class="incqbox"><h4>'.$codename.':</h4><div class="scrollbox" style="height: '.$height_str.'"><pre dir="ltr">'.$code.'</pre></div></div></div><p>';
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $hide_smilies
 * @return unknown

 */
function parse_part($text, $hide_smilies)
{
        global $configuration, $lang_common, $forum_user;
        if ($configuration['o_censoring'] == '1') $text = censor_words($text);
        $text = convert_htmlspecialchars($text);
        if ($configuration['o_make_links'] == '1') $text = do_clickable($text);
        if ($configuration['o_smilies'] == '1' && $forum_user['show_smilies'] == '1' && $hide_smilies == '0') $text = do_smilies($text);
        if ($configuration['p_message_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
        {
                $text = do_bbcode($text);
                if ($configuration['p_message_img_tag'] == '1')
                {
                        $text = preg_replace('#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e', 'handle_img_tag(\'$1$3\')', $text);
                }
        }
        $pattern = array("\n", "\t", '  ', '  ');
        $replace = array('<br />', '&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;');
        $text = str_replace($pattern, $replace, $text);
        return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $hide_smilies
 * @return unknown

 */
function parse_part_preview($text, $hide_smilies)
{
        global $configuration, $lang_common, $forum_user;
        if ($configuration['o_censoring'] == '1') $text = censor_words($text);
        $text = convert_htmlspecialchars($text);
        if ($configuration['p_message_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
        {
                $text = do_bbcode_preview($text);
        }
        $pattern = array("\n", "\t", '  ', '  ');
        $replace = array('<br />', '&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;');
        $text = str_replace($pattern, $replace, $text);
        return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $hide_smilies
 * @return unknown

 */
function parse_message_preview($text)
{
	global $configuration, $lang_common, $forum_user;
	$parts = preg_split ('#\n?\[\/?code=?(.*?)\]\n?#is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$idx = 0;
	$codename = '';
	$text = '';
	foreach ($parts as $value)
	{
          $idx++;
          switch ($idx)
          {
            case 1:
              $text .= parse_part_preview($value, $hide_smilies);
              break;
            case 2:
              $codename = $value;
              break;
            case 4:
              $codename = $value;
              $idx = 0;
              break;
          }
	}
	$text = str_replace('<p></p>', '', '<p>'.$text.'</p>');
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @param unknown_type $hide_smilies
 * @return unknown

 */
function parse_message($text, $hide_smilies)
{
	global $configuration, $lang_common, $forum_user;
	if (strstr($text, "[code]"))
	{
		$text = str_replace('<', '&lt;', $text);
		$text = str_replace('>', '&gt;', $text);
	}
	$parts = preg_split ('#\n?\[\/?code=?(.*?)\]\n?#is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$idx = 0;
	$codename = '';
	$text = '';
	foreach ($parts as $value)
	{
          $idx++;
          switch ($idx)
          {
            case 1:
              $text .= parse_part($value, $hide_smilies);
              break;
            case 2:
              $codename = $value;
              break;
            case 3:
              $text .= parse_code(strtolower($codename), $value);
              break;
            case 4:
              $codename = $value;
              $idx = 0;
              break;
          }
	}
	$text = str_replace('<p></p>', '', '<p>'.$text.'</p>');
	return $text;
}

/**
 * Enter description here...
 *
 * @param unknown_type $text
 * @return unknown

 */
function parse_signature($text)
{
	global $configuration, $lang_common, $forum_user;
	if ($configuration['o_censoring'] == '1') $text = censor_words($text);
	$text = convert_htmlspecialchars($text);
	if ($configuration['o_make_links'] == '1') $text = do_clickable($text);
	if ($configuration['o_smilies_sig'] == '1' && $forum_user['show_smilies'] != '0') $text = do_smilies($text);
	if ($configuration['p_sig_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
	{
		$text = do_bbcode($text);
		if ($configuration['p_sig_img_tag'] == '1')
		{
			$text = preg_replace('#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e', 'handle_img_tag(\'$1$3\', true)', $text);
		}
	}
	$pattern = array("\n", "\t", '  ', '  ');
	$replace = array('<br />', '&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;');
	$text = str_replace($pattern, $replace, $text);
	return $text;
}

function handle_email_tag($email,$text = '') 
{
	$enc_email='';
	for ($a=0;$a<strlen($email);$a++)
	{
		$charValue = ord(substr($email,$a,1));
		$charValue+=intval(2);
		$enc_email.=chr($charValue);
	}
	$enc_email = str_replace('\\','\\\\',$enc_email);
	$enc_email = str_replace('\'','\\\'',$enc_email);
	if ($text == '')
	{
		$text = str_replace('@','@<span style="display:none">remove-this.</span>',$email);
	}
	return '<a href="javascript:mail_to(\''.$enc_email.'\')">'.$text.'</a>';
}

