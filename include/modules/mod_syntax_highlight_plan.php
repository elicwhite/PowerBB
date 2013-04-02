<?
	define('S_NONE',0);
	define('S_COMMENT1',1);
	define('S_COMMENT2',2);
	define('S_COMMENT3',3);
	define('S_STRING',4);
	define('S_STRING2',5);
	define('S_KEYWORD',6);
	define('S_SYMBOL',7);
	define('S_NUMBER',8);
	define('S_DIRECTIVE',9);
	define('S_OBJECT',10);
	define('S_IDENTIFIER',11);
	define('S_VARIABLE',12);
	define('S_VALUE',13);

	class plan_code_syn
	{
		var $styles;
		var $state=S_NONE;
		var $open_state=S_NONE;
		var $close_state=S_NONE;
		var $spaces = '  ';

		function plan_code_syn()
		{
      		$this->styles = array(
			S_KEYWORD => array('<span class="syn_keyword">','</span>'),
			S_IDENTIFIER => array('<span class="syn_identifier">','</span>'),
			S_NUMBER => array('<span class="syn_number">','</span>'),
			S_STRING => array('<span class="syn_string">','</span>'),
			S_STRING2 => array('<span class="syn_string2">','</span>'),
			S_SYMBOL => array('<span class="syn_symbol">','</span>'),
			S_NUMBER => array('<span class="syn_number">','</span>'),
			S_OBJECT => array('<span class="syn_object">','</span>'),
			S_COMMENT1 => array('<span class="syn_comment1">','</span>'),
			S_COMMENT2 => array('<span class="syn_comment2">','</span>'),
			S_COMMENT3 => array('<span class="syn_comment3">','</span>'),
			S_DIRECTIVE => array('<span class="syn_directive">','</span>'),
			S_VARIABLE => array('<span class="syn_variable">','</span>'),
			S_VALUE => array('<span class="syn_value">','</span>'));
		}

		function format_out($out)
		{
			$out = htmlspecialchars($out);
			return str_replace("\t", $this->spaces, $out);
		}

		function text_out(&$out)
		{
			$out=$this->format_out($out);
			if ($this->open_state!=S_NONE)
			{
				$out=$this->styles[$this->open_state][0].$out;
				$this->open_state=S_NONE;
			}
			if ($this->close_state!=S_NONE)
			{
				$out=$out.$this->styles[$this->close_state][1];
				$this->open_state=S_NONE;
				$this->state=S_NONE;
			}
			echo $out;
			$out='';
		}

		function highlight($code)
		{
			echo $this->format_out($code);
		}

		function is_identifier_open($ch)
		{
			if (($ch>='a' and $ch<='z') or ($ch>='A' and $ch<='Z') or ($ch=='_')) return true;
			else return false;
		}

		function is_identifier($ch)
		{
			if (($ch>='a' and $ch<='z') or ($ch>='A' and $ch<='Z') or ($ch>='0' and $ch<='9') or ($ch=='_')) return true;
			else return false;
		}

		function process_std_identifier(&$i, &$l, &$code, &$keywords, &$out)
		{
			$j = $i;
			while ($j < $l)
			{
				if (!$this->is_identifier($code{$j})) break;
				$j++;
			}
			$this->close_state = $this->state;
			$out.=substr($code, $i, $j - $i);
			$i = $j - 1;
			if (!in_array($out, $keywords))
			{
				$this->state=S_NONE;
				$this->open_state=S_NONE;
				$this->close_state=S_NONE;
			}
		}

		function highlight_code($code)
		{
			ob_start();
			$this->highlight($code);
			$code=ob_get_contents();
			ob_end_clean();
			return $code;
		}

		function highlight_file($file)
		{
			set_time_limit(0);
			$f=fopen($file,'r');
			while (!feof($f))
			{
				$line=fgets($f);
				$this->highlight($line);
				flush();
			}
			fclose($f);
		}
	}
?>