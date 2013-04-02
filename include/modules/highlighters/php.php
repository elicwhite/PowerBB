<?
  class php_syn extends plan_code_syn
  {
    var $keywords = array(
      'and',
      'or',
      'xor',
      '__FILE__',
      'exception',
      'php_user_filter',
      '__LINE__',
      'array',
      'as',
      'break',
      'case',
      'cfunction',
      'class',
      'const',
      'continue',
      'declare',
      'default',
      'die()',
      'do',
      'echo',
      'else',
      'elseif',
      'empty',
      'enddeclare',
      'endfor',
      'endforeach',
      'endif',
      'endswitch',
      'endwhile',
      'eval',
      'exit',
      'extends',
      'for',
      'foreach',
      'function',
      'global',
      'if',
      'include',
      'include_once',
      'isset',
      'list',
      'new',
      'old_function',
      'print',
      'require',
      'require_once',
      'return',
      'static',
      'switch',
      'unset',
      'use',
      'var',
      'while',
      '__FUNCTION__',
      '__CLASS__',
      '__METHOD__'
    );

    function highlight($code)
    {
      $ch = "";
      $next_ch = "";
      $l=strlen($code);
      $out='';
      $i=0;
      while ($i < $l)
      {
        if ($this->state==S_NONE)
        {
          $ch = $code{$i};
          if ($i+1 < $l)
            $next_ch = $code{$i+1};
          else
            $next_ch = '';

          if ($ch=='#')
          {
            $this->state=S_COMMENT1;
            $out=$ch;
            $i++;
          }
          else if ($ch=='/' and $next_ch=='/')
          {
            $this->state=S_COMMENT1;
            $out=$ch.$next_ch;
            $i++;
            $i++;
          }
          else if ($ch=='/' and $next_ch=='*')
          {
            $this->state=S_COMMENT2;
            $out=$ch.$next_ch;
            $i++;
            $i++;
          }
          else if ($this->is_identifier_open($ch))
          {
            $this->state=S_KEYWORD;
            $out=$ch;
            $i++;
         }
         else if ($ch=='$')
        {
            $this->state=S_VARIABLE;
            $out=$ch;
            $i++;
          }
          else if ($ch=='\'')
          {
            $this->state=S_STRING;
            $out=$ch;
            $i++;
          }
          else if ($ch=='"')
          {
            $this->state=S_STRING2;
            $out=$ch;
            $i++;
          }
          else
          {
            $out=$ch;
          }
          $this->open_state=$this->state;
          $this->close_state=S_NONE;
        }

        if ($this->state!=S_NONE)
        {
          switch ($this->state)
          {
            case S_COMMENT1:
              $j=strpos($code,"\n",$i);
              if ($j===false)
                $j=$l-1;
              else
                $this->close_state=$this->state;
              $out.=substr($code, $i, $j - $i + 1);
              $i=$j;
              break;
            case S_COMMENT2:
              $j=strpos($code,'*/',$i);
              if ($j===false)
                $j = $l - 1;
              else
                $this->close_state=$this->state;
              $out.=substr($code, $i, $j + 1 - $i + 1);
              $i=$j + 1;
              break;
            case S_KEYWORD:
            {
              $j= $i;
              while ($j < $l)
              {
                if (!$this->is_identifier($code{$j}))
                  break;
                $j++;
              }
              $this->close_state=$this->state;//close if string breaked
              $out.=substr($code, $i, $j - $i);
              $i=$j - 1;
              if (!in_array($out, $this->keywords))
              {
                      $this->state=S_NONE;
                $this->open_state=S_NONE;
                $this->close_state=S_NONE;
              }
              break;
            }
            case S_VARIABLE:
            {
              $j= $i;
              while ($j < $l)
              {
                if ((!$this->is_identifier($code{$j})))
                  break;
                $j++;
              }
              $this->close_state=$this->state;
              $out.=substr($code, $i, $j - $i);
              $i=$j - 1;
              break;
            }
            case S_STRING:
            {
              $j=$i;
              while ($j < $l)
              {
                if (($code{$j}=="\\") and ($j+1)<$l and (($code{$j+1}=="\"") or ($code{$j+1}=="\'")))
                  $j++;
                else
                if ($code{$j}=='\'')
                {
                  $this->close_state=$this->state;
                  break;
                }
                $j++;
              }
              $out.=substr($code, $i, $j - $i + 1);
              $i=$j;
              break;
            }
            case S_STRING2:
            {
              $j=$i;
              while ($j < $l)
              {
                if ($code{$j}=="\\")//escape \\
                  $j++;
                else if ($code{$j}=='"')
                {
                  $this->close_state=$this->state;
                  break;
                 }
                $j++;
              }
              $out.=substr($code, $i, $j - $i + 1);
              $i=$j;
              break;
            }
          }
        }
        $this->text_out($out);
        $i++;
      }
    }
  }
?>
