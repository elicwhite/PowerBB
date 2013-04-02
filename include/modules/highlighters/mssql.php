<?
  class mssql_syn extends plan_code_syn
  {
    var $keywords = array(
      'add',
      'all',
      'alter',
      'and',
      'any',
      'as',
      'asc',
      'authorization',
      'backup',
      'begin',
      'between',
      'break',
      'browse',
      'bulk',
      'by',
      'cascade',
      'case',
      'check',
      'checkpoint',
      'close',
      'clustered',
      'collate',
      'column',
      'commit',
      'compute',
      'constraint',
      'contains',
      'containstable',
      'continue',
      'create',
      'cross',
      'current',
      'cursor',
      'database',
      'dbcc',
      'deallocate',
      'declare',
      'default',
      'delete',
      'deny',
      'desc',
      'disk',
      'distinct',
      'distributed',
      'double',
      'drop',
      'dummy',
      'dump',
      'else',
      'end',
      'errlvl',
      'escape',
      'except',
      'exec',
      'execute',
      'exists',
      'exit',
      'fetch',
      'file',
      'fillfactor',
      'for',
      'foreign',
      'formsof',
      'freetext',
      'freetexttable',
      'from',
      'full',
      'function',
      'go',
      'goto',
      'grant',
      'group',
      'having',
      'holdlock',
      'identity',
      'identitycol',
      'identity_insert',
      'if',
      'in',
      'inflectional',
      'index',
      'inner',
      'insert',
      'intersect',
      'into',
      'is',
      'isabout',
      'join',
      'key',
      'kill',
      'left',
      'like',
      'lineno',
      'load',
      'national',
      'nocheck',
      'nonclustered',
      'not',
      'null',
      'nullif',
      'of',
      'off',
      'offsets',
      'on',
      'open',
      'opendatasource',
      'openquery',
      'openrowset',
      'openxml',
      'option',
      'or',
      'order',
      'outer',
      'over',
      'percent',
      'plan',
      'precision',
      'primary',
      'print',
      'proc',
      'procedure',
      'public',
      'raiserror',
      'read',
      'readtext',
      'reconfigure',
      'references',
      'replication',
      'restore',
      'restrict',
      'return',
      'revoke',
      'right',
      'rollback',
      'rowcount',
      'rowguidcol',
      'rule',
      'save',
      'schema',
      'select',
      'session_user',
      'set',
      'setuser',
      'shutdown',
      'some',
      'statistics',
      'table',
      'textsize',
      'then',
      'to',
      'top',
      'tran',
      'transaction',
      'trigger',
      'truncate',
      'tsequal',
      'union',
      'unique',
      'update',
      'updatetext',
      'use',
      'user',
      'values',
      'varying',
      'view',
      'waitfor',
      'weight',
      'when',
      'where',
      'while',
      'with',
      'writetext'
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

          if ($ch=='-' and $next_ch=='-')
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
          else if ($ch=='[')
          {
            $this->state=S_OBJECT;
            $out=$ch;
            $i++;
          }
          else if ($ch=='\'')
          {
            $this->state=S_STRING;
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
                if ((!$this->is_identifier($code{$j})))
                  break;
                $j++;
              }
              $this->close_state=$this->state;//close if string breaked
              $out.=substr($code, $i, $j - $i);
              $i=$j - 1;
              if (!in_array(strtolower($out), $this->keywords))
              {
                $this->state=S_NONE;
                $this->open_state=S_NONE;
                $this->close_state=S_NONE;
              }
              break;
            }
            case S_OBJECT:
            {
              $j=$i;
              while ($j < $l)
              {
                if ($code{$j}==']')
                  break;
                $j++;
              }
              $this->close_state=$this->state;
              $out.=substr($code, $i, $j - $i + 1);
              $i=$j;
              break;
            }
            case S_STRING:
            {
              $j=$i;
              while ($j < $l)
              {
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
          }
        }

        $this->text_out($out);
        $i++;
      }
    }
  }
?>