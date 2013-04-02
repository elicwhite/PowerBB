<?php
session_start();

class ran
{
	var $text;

	/**
	 * Enter description here...
	 *
	 * @return ran
	
	 */
	function ran()
	{
		srand((double)microtime()*1000000^getmypid());
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @return unknown
	
	 */
	function createImage($width=135,$height=45)
	{
		header("Content-type:image/jpeg");
		$im=imagecreate($width,$height);
		$black=imagecolorallocate($im,241,241,241);
		$white=imagecolorallocate($im,0,0,0);
            $other=imagecolorallocate($im,0,0,255);
		$string=substr(strtolower(md5(uniqid(rand(),1))),0,7);
		$string=str_replace('2','a',$string);
		$string=str_replace('l','p',$string);
		$string=str_replace('1','h',$string);
		$string=str_replace('0','y',$string);
		$string=str_replace('o','y',$string);
		$font = imageloadfont('../../img/general/anticlimax.gdf');
		imagestring($im,$font,10,10,$string, $white);
		imagejpeg($im);
		imagedestroy($im);
		return $this->text=$string;
	}
}

$im=&new ran;
$_SESSION['text']=$im->createImage();
?>