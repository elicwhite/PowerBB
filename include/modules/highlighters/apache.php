<?
  class apache_syn extends plan_code_syn
  {
    var $keywords = array(
      'ServerRoot',
      'ScoreBoardFile',
      'PidFile',
      'Timeout',
      'KeepAlive',
      'MaxKeepAliveRequests',
      'KeepAliveTimeout',
      'LoadModule',
      'ExtendedStatus',
      'ServerAdmin',
      'ServerName',
      'UseCanonicalName',
      'DocumentRoot',
      'Options',
      'AllowOverride',
      'FollowSymLinks',
      'All',
      'MultiViews',
      'Indexes',
      'None',
      'Allow',
      'Order',
      'Deny',
      'From',
      'Listen',
      'UserDir',
      'ReadmeName',
      'HeaderName',
      'IndexIgnore',
      'Limit',
      'Action',
      'AuthConfig',
      'FileInfo ',
      'DirectoryIndex',
      'AccessFileName',
      'TypesConfig',
      'DefaultType',
      'ErrorLog',
      'LogLevel',
      'LogFormat',
      'CustomLog',
      'ServerTokens',
      'ServerSignature',
      'Alias',
      'AliasMatch',
      'SetEnvIf',
      'ScriptAlias',
      'IndexOptions',
      'AddIconByType',
      'AddIcon',
      'AddIconByEncoding',
      'DefaultIcon',
      'AddDescription',
      'AddLanguage',
      'LanguagePriority',
      'ForceLanguagePriority',
      'AddDefaultCharset',
      'AddCharset',
      'AddType',
      'AddHandler',
      'HostnameLookups',
      'AddOutputFilter',
      'BrowserMatch',
      'SetHandler',
      'NameVirtualHost',
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
          else if ($this->is_identifier_open($ch))
          {
            $this->state=S_KEYWORD;
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
         else if ($ch=='<')
        {
            $this->state=S_OBJECT;
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
            case S_KEYWORD:
            {
              $j=$i;
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
            case S_OBJECT:
            {
              $j=$i;
              while ($j < $l)
              {
                if ($code{$j}=='>')
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
            case S_STRING:
            {
              $j=$i;
              while ($j < $l)
              {
                if ($code{$j}=='\'' or $code{$j}=="\n")
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
                if ($code{$j}=='"' or $code{$j}=="\n")
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
