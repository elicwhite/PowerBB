<?php
	$DB_TableName=$table_prefix.'spelling_words';
	$Language_Text = array('Scanned %d words.    Found %d words to be corrected.');
	$Language_Javascript = array('Checking Document...','No misspellings found','OK','Cancel','Spell Check Completed','Correct','All','Ignore','Learn','Suggest','Definition','Thesaurus','Word correction','No Suggestions');
	$Spell_Config['PSPELL_LANGUAGE'] = 'en';
	$Translation_Table = array();
	$Replacement_Table = array();
	$Language_Character_List = "abcdefghijklmnopqrstuvwxyz'";
	$Language_Common_Words = ',the,is,was,be,are,were,been,being,am,of,and,a,an,in,inside,to,have,has,had,having,he,him,his,it,its,i,me,my,to,they,their,not,no,for,you,your,she,her,with,on,that,these,this,those,do,did,does,done,doing,we,us,our,by,at,but,from,as,which,or,will,said,say,says,saying,would,what,there,if,can,who,whose,so,go,gone,went,goes,more,other,another,one,see,saw,seen,seeing,know,knew,known,knows,knowing,there,';

	function Translate_Word($Word)
	{
		return ($Word);
	}

	function Word_Sound_Function($Word)
	{
		return (metaphone($Word));
	}

	function Language_Decode(&$Data)
	{
		if (strpos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') > 0 || strpos(@$_SERVER['ALL_HTTP'], 'MSIE') > 0)
		{
			if (function_exists('utf8_decode')) $Data = utf8_decode($Data);
		}
		return ($Data);
	}

	function Language_Encode(&$Data)
	{
		global $Spell_Config;
		if (!$Spell_Config['IE_UTF_Encode']) return ($Data);
		if (strpos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') > 0 || strpos(@$_SERVER['ALL_HTTP'], 'MSIE') > 0)
		{
			if (function_exists('utf8_encode')) $Data = utf8_encode($Data);
		}
		return ($Data);
	}

	function Language_Lower(&$Data)
	{
		return(strtolower($Data));
	}

	function Language_Upper(&$Data)
	{
		return(strtoupper($Data));
	}
?>