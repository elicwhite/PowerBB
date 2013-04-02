<?php
class tar
 {
	var $filename;
	var $isGzipped;
	var $tar_file;
	var $files;
	var $directories;
	var $numFiles;
	var $numDirectories;

	/**
	 * Enter description here...
	 *
	 * @return tar
	
	 */
	function tar()
	{
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $bytestring
	 * @return unknown
	
	 */
	function __computeUnsignedChecksum($bytestring)
	{
		if(!isset($unsigned_chksum)) $unsigned_chksum = '';
		for($i=0; $i<512; $i++) $unsigned_chksum += ord($bytestring[$i]);
		for($i=0; $i<8; $i++) $unsigned_chksum -= ord($bytestring[148 + $i]);
		$unsigned_chksum += ord(" ") * 8;
		return $unsigned_chksum;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $string
	 * @return unknown
	
	 */
	function __parseNullPaddedString($string)
	{
		$position = strpos($string,chr(0));
		return substr($string,0,$position);
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	
	 */
	function __parseTar()
	{
		$tar_length = strlen($this->tar_file);
		$main_offset = 0;
		while($main_offset < $tar_length)
		{
			if(substr($this->tar_file,$main_offset,512) == str_repeat(chr(0),512)) break;
			$file_name = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset,100));
			$file_mode = substr($this->tar_file,$main_offset + 100,8);
			$file_uid = octdec(substr($this->tar_file,$main_offset + 108,8));
			$file_gid = octdec(substr($this->tar_file,$main_offset + 116,8));
			$file_size = octdec(substr($this->tar_file,$main_offset + 124,12));
			$file_time = octdec(substr($this->tar_file,$main_offset + 136,12));
			$file_chksum = octdec(substr($this->tar_file,$main_offset + 148,6));
			$file_uname	= $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 265,32));
			$file_gname	= $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 297,32));
			if($this->__computeUnsignedChecksum(substr($this->tar_file,$main_offset,512)) != $file_chksum) return false;
			$file_contents	= substr($this->tar_file,$main_offset + 512,$file_size);
			if($file_size > 0)
			{
				$this->numFiles++;
				$activeFile = &$this->files[];
				$activeFile["name"] = $file_name;
				$activeFile["mode"] = $file_mode;
				$activeFile["size"] = $file_size;
				$activeFile["time"] = $file_time;
				$activeFile["user_id"] = $file_uid;
				$activeFile["group_id"] = $file_gid;
				$activeFile["user_name"] = $file_uname;
				$activeFile["group_name"] = $file_gname;
				$activeFile["checksum"]	= $file_chksum;
				$activeFile["file"] = $file_contents;
			}
			else
			{
				$this->numDirectories++;
				$activeDir = &$this->directories[];
				$activeDir["name"] = $file_name;
				$activeDir["mode"] = $file_mode;
				$activeDir["time"] = $file_time;
				$activeDir["user_id"] = $file_uid;
				$activeDir["group_id"] = $file_gid;
				$activeDir["user_name"] = $file_uname;
				$activeDir["group_name"] = $file_gname;
				$activeDir["checksum"] = $file_chksum;
			}
		$main_offset += 512 + (ceil($file_size / 512) * 512);
		}
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function __readTar($filename='')
	{
		if(!$filename) $filename = $this->filename;
		$fp = fopen($filename,"rb");
		$this->tar_file = fread($fp,filesize($filename));
		fclose($fp);
		if($this->tar_file[0] == chr(31) && $this->tar_file[1] == chr(139) && $this->tar_file[2] == chr(8))
		{
			if(!function_exists("gzinflate")) return false;
			$this->isGzipped = TRUE;
			$this->tar_file = gzinflate(substr($this->tar_file,10,-4));
		}
		$this->__parseTar();
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	
	 */
	function __generateTAR()
	{
		unset($this->tar_file);
		if($this->numDirectories > 0)
		{
			foreach($this->directories as $key => $information)
			{
				unset($header);
				$header = str_pad($information["name"],100,chr(0));
				$header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct(0),11,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_repeat(" ",8);
				$header .= "5";
				$header .= str_repeat(chr(0),100);
				$header .= str_pad("ustar",6,chr(32));
				$header .= chr(32) . chr(0);
				$header .= str_pad("",32,chr(0));
				$header .= str_pad("",32,chr(0));
				$header .= str_repeat(chr(0),8);
				$header .= str_repeat(chr(0),8);
				$header .= str_repeat(chr(0),155);
				$header .= str_repeat(chr(0),12);
				$checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
				for($i=0; $i<6; $i++)
				{
					$header[(148 + $i)] = substr($checksum,$i,1);
				}
				$header[154] = chr(0);
				$header[155] = chr(32);
				$this->tar_file .= $header;
			}
		}
		if($this->numFiles > 0)
		{
			foreach($this->files as $key => $information)
			{
				unset($header);
				$header = str_pad($information["name"],100,chr(0));
				$header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["size"]),11,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
				$header .= str_repeat(" ",8);
				$header .= "0";
				$header .= str_repeat(chr(0),100);
				$header .= str_pad("ustar",6,chr(32));
				$header .= chr(32) . chr(0);
				$header .= str_pad($information["user_name"],32,chr(0));
				$header .= str_pad($information["group_name"],32,chr(0));
				$header .= str_repeat(chr(0),8);
				$header .= str_repeat(chr(0),8);
				$header .= str_repeat(chr(0),155);
				$header .= str_repeat(chr(0),12);
				$checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
				for($i=0; $i<6; $i++)
				{
					$header[(148 + $i)] = substr($checksum,$i,1);
				}
				$header[154] = chr(0);
				$header[155] = chr(32);
				$file_contents = str_pad($information["file"],(ceil($information["size"] / 512) * 512),chr(0));
				$this->tar_file .= $header . $file_contents;
			}
		}
		$this->tar_file .= str_repeat(chr(0),512);
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function openTAR($filename)
	{
		unset($this->filename);
		unset($this->isGzipped);
		unset($this->tar_file);
		unset($this->files);
		unset($this->directories);
		unset($this->numFiles);
		unset($this->numDirectories);
		if(!file_exists($filename)) return false;
		$this->filename = $filename;
		$this->__readTar();
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function appendTar($filename)
	{
		if(!file_exists($filename)) return false;
		$this->__readTar($filename);
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function getFile($filename)
	{
		if($this->numFiles > 0)
		{
			foreach($this->files as $key => $information)
			{
				if($information["name"] == $filename) return $information;
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $dirname
	 * @return unknown
	
	 */
	function getDirectory($dirname)
	{
		if($this->numDirectories > 0)
		{
			foreach($this->directories as $key => $information)
			{
				if($information["name"] == $dirname) return $information;
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function containsFile($filename)
	{
		if($this->numFiles > 0)
		{
			foreach($this->files as $key => $information)
			{
				if($information["name"] == $filename) return true;
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $dirname
	 * @return unknown
	
	 */
	function containsDirectory($dirname)
	{
		if($this->numDirectories > 0)
		{
			foreach($this->directories as $key => $information)
			{
				if($information["name"] == $dirname) return true;
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $dirname
	 * @return unknown
	
	 */
	function addDirectory($dirname)
	{
		if(!file_exists($dirname)) return false;
		$file_information = stat($dirname);
		$this->numDirectories++;
		$activeDir = &$this->directories[];
		$activeDir["name"] = $dirname;
		$activeDir["mode"] = $file_information["mode"];
		$activeDir["time"] = $file_information["mtime"];
		$activeDir["user_id"] = $file_information["uid"];
		$activeDir["group_id"] = $file_information["gid"];
		if(!isset($checksum)) $checksum = '';
		$activeDir["checksum"] = $checksum;
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function addFile($filename)
	{
		if(!file_exists($filename)) return false;
		if($this->containsFile($filename)) return false;
		$file_information = stat($filename);
		$fp = fopen($filename,"rb");
		@$file_contents = fread($fp,filesize($filename));
		fclose($fp);
		$this->numFiles++;
		$activeFile	= &$this->files[];
		$activeFile["name"] = $filename;
		$activeFile["mode"] = $file_information["mode"];
		$activeFile["user_id"] = $file_information["uid"];
		$activeFile["group_id"]	= $file_information["gid"];
		$activeFile["size"] = $file_information["size"];
		$activeFile["time"] = $file_information["mtime"];
		if(!isset($checksum)) $checksum = '';
		$activeFile["checksum"]	= $checksum;
		$activeFile["user_name"] = "";
		$activeFile["group_name"] = "";
		$activeFile["file"] = $file_contents;
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	
	 */
	function removeFile($filename)
	{
		if($this->numFiles > 0)
		{
			foreach($this->files as $key => $information)
			{
				if($information["name"] == $filename)
				{
					$this->numFiles--;
					unset($this->files[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $dirname
	 * @return unknown
	
	 */
	function removeDirectory($dirname)
	{
		if($this->numDirectories > 0)
		{
			foreach($this->directories as $key => $information)
			{
				if($information["name"] == $dirname)
				{
					$this->numDirectories--;
					unset($this->directories[$key]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	
	 */
	function saveTar()
	{
		if(!$this->filename)
			return false;
		$this->toTar($this->filename,$this->isGzipped);
		return true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @param unknown_type $useGzip
	 * @return unknown
	
	 */
	function toTar($filename,$useGzip)
	{
		if(!$filename) return false;
		$this->__generateTar();
		if($useGzip)
		{
			if(!function_exists("gzencode")) return false;
			$file = gzencode($this->tar_file);
		}
		else
		{
			$file = $this->tar_file;
		}
		$fp = fopen($filename,"wb");
		fwrite($fp,$file);
		fclose($fp);
		return true;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @param unknown_type $tar_file
 * @param unknown_type $filelist

 */
function backup($path, $tar_file, &$filelist)
{
	global $skip;
	$d = opendir($path);
	while (false !== ($entry = readdir($d)))
	{
		if($entry != '.' && $entry != '..' && !in_array($entry, $skip))
		{
			if(is_dir($path.'/'.$entry))
			{
				$tar_file->addDirectory(($path==FORUM_ROOT?'':$path.'/').$entry);
				$filelist .= ($path==FORUM_ROOT?'':$path.'/').$entry.';';
				backup(($path==FORUM_ROOT?'':$path.'/').$entry, $tar_file, $filelist);
			}else
			{
				$tar_file->addFile(($path==FORUM_ROOT?'':$path.'/').$entry);
				$filelist .= ($path==FORUM_ROOT?'':$path.'/').$entry.';';
			}
		}
	}
	closedir($d);
}
?>