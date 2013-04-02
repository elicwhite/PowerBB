<?
  class cpp_syn extends plan_code_syn
  {
    var $directives = array(
      '#define',
      '#elif',
      '#else',
      '#endif',
      '#error',
      '#if',
      '#ifdef',
      '#ifndef',
      '#include',
      '#line',
      '#pragma',
      '#printf',
      '#undef'
    );

    var $keywords = array(
      'define',
      'elif',
      'endif',
      'error',
      'ifdef',
      'ifndef',
      'include',
      'line',
      'pragma',
      'undef',
      'and',
      'and_eq',
      'asm',
      'auto',
      'bitand',
      'bitor',
      'bool',
      'break',
      'case',
      'catch',
      'char',
      'class',
      'compl',
      'const',
      'const_cast',
      'continue',
      'default',
      'delete',
      'do',
      'double',
      'dynamic_cast',
      'else',
      'enum',
      'explicit',
      'export',
      'extern',
      'false',
      'float',
      'for',
      'friend',
      'goto',
      'if',
      'inline',
      'int',
      'long',
      'mutable',
      'namespace',
      'new',
      'not',
      'not_eq',
      'operator',
      'or',
      'or_eq',
      'private',
      'protected',
      'public',
      'register',
      'reinterpret_cast',
      'return',
      'short',
      'signed',
      'sizeof',
      'static',
      'static_cast',
      'struct',
      'switch',
      'template',
      'this',
      'throw',
      'true',
      'try',
      'typedef',
      'typeid',
      'typename',
      'union',
      'unsigned',
      'using',
      'virtual',
      'void',
      'volatile',
      'wchar_t',
      'while',
      'xor',
      'xor_eq'
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

          if ($ch=='/' and $next_ch=='/')
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
            $this->state = S_KEYWORD;
            $out = $ch;
            $i++;
          }
          else if ($ch=='#')
          {
            $this->state = S_DIRECTIVE;
            $out = $ch;
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
          $this->close_state = S_NONE;
        }

        if ($this->state != S_NONE)
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
              $this->process_std_identifier($i, $l, $code, $this->keywords, $out);
              break;
            }

            case S_DIRECTIVE:
            {
              $this->process_std_identifier($i, $l, $code, $this->directives, $out);
              break;
            }

            case S_STRING:
            {
              $j=$i;
              while ($j < $l)
              {
                if ($code{$j}=="\\")
                  $j++;
                else if ($code{$j} == "\n")
                  break;
                else if ($code{$j} == '\'')
                  break;
                $j++;
              }
              $this->close_state = $this->state;
              $out.=substr($code, $i, $j - $i + 1);
              $i=$j;
              break;
            }
            case S_STRING2:
            {
              $j = $i;
              while ($j < $l)
              {
                if ($code{$j}=="\\" and !(($j + 1 < $l) and $code{$j} == "\n"))
                  $j++;
                else if ($code{$j} == "\n")
                  break;
                else if ($code{$j}=='"')
                  break;
                $j++;
              }
              $this->close_state=$this->state;
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