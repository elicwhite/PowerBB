<?php
	$DB_TableName=$table_prefix."spelling_words";
	$Language_Text = array('Suche nach %d Wörtern.    %d zu korrigierende Wörter gefunden.');
	$Language_Javascript = array('Bitte Warten','Keine Störungen gefunden...', 'OK','Abbrechen','Rechtschreibprüfung abgeschlossen','Korrigieren', 'Alle','Ignorieren','Hinzufügen','Vorschlagen', 'Definition','Thesaurus','Ändern in:','Keine Vorschläge');
	$Spell_Config["PSPELL_LANGUAGE"] = "de";
	$Translation_Table = array();
	$Replacement_Table = array();
	$Language_Character_List = "abcdefghijklmnopqrstuvwxyzäöüßÄÖÜ'";
	$Language_Common_Words = "der,die,das,ist,war,sein,sind,waren,bin,von,vom,und,ein,eine,einer,innen,zu,zum,haben,hat,habe,hatten,er,sie,es,seiner,seine,seines,ich,mein,mir,mich,wir,unser,unsere,euer,eures,ihnen,nicht,nein,für,du,deins,ihrs,mit,auf,dieses,dies,jeses,tun,tat,getan,bei,beim,aber,leider,jedoch,von,als,oder,wird,sagen,sagte,sage,würde,würdest,was,dort,hier,wenn,kann,wer,wessen,so,gehen,geht,gegangen,mehr,anders,andere,eins,sehen,sah,gesehen,wissen,weiß,wußte";

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