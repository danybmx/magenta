<?php

class ImageComponent
{
	public static function crop($path, $filename, $width, $height, $x, $y, $prefix = null)
	{
		if ($path{0} == '/')
				$path = ROOT.DS.'public'.$path;

		if ( ! preg_match('/\/$/', $path))
			$path .= '/';
		
		$explode = explode('.', $filename);
		$ext = strtolower($explode[count($explode)-1]);

		if($ext == 'gif') {
			$src = imageCreateFromGif($path.$filename);
		} elseif ($ext == 'png') {
			$src = imageCreateFromPng($path.$filename);
			imagealphablending($src, true);
			imagesavealpha($src, true);
		} elseif ($ext == 'jpg' or $ext == 'jpeg') {
			$src = imageCreateFromJpeg($path.$filename);
		} else {
			echo 'Error: '.$path.$filename;
		}
		
		$oWidth = imagesx($src);
		$oHeight = imagesy($src);

		$img = ImageCreateTrueColor($width, $height);
		imagealphablending($img,false);

		if ($ext == 'png') {
			$fondo = imagecolorallocatealpha($img, 0, 0, 0, 0);
		} else {
			$fondo = imagecolorallocate($img, 255, 255, 255);
		}
		imagefilledrectangle($img, 0, 0, $width, $height, $fondo);

		imagecopyresampled($img, $src, 0, 0, $x, $y, $oWidth, $oHeight, $oWidth, $oHeight);

		//imagecopyresampled($img, $src, -$posx, -$posy, 0, 0, $oWidth, $oHeight, $oWidth, $oHeight);

		$completeName = $path.$prefix.$filename;
		if($ext == 'gif') {
			imagegif($img, $completeName, $quality);
		} elseif ($ext == 'png') {
			imagesavealpha($img, true);
			imagepng($img, $completeName, 9);
		} elseif ($ext == 'jpg' or $ext == 'jpeg') {
			imagejpeg($img, $completeName, 100);
		}
		chmod($completeName, 0777);
	}

	public static function resize($path, $filename, $max_w, $max_h, $crop = false, $prefix = null, $quality = 95, $imagename = null)
	{
		if ($path{0} == '/')
				$path = ROOT.DS.'public'.$path;
		
		if ( ! preg_match('/\/$/', $path))
			$path .= '/';
		
		$explode = explode('.', $filename);
		$ext = strtolower($explode[count($explode)-1]);

		if($ext == 'gif') {
			$src = imageCreateFromGif($path.$filename);
		} elseif ($ext == 'png') {
			$src = imageCreateFromPng($path.$filename);
			imagealphablending($src, true);
			imagesavealpha($src, true);
		} elseif ($ext == 'jpg' or $ext == 'jpeg') {
			$src = imageCreateFromJpeg($path.$filename);
		}
		$oWidth = imagesx($src);
		$oHeight = imagesy($src);

		if ($crop) {
			$rate = $oWidth/$max_w;
			$width = $max_w;
			$height = $oHeight/$rate;
			
			if($height < $max_h) {
				$rate = $oHeight/$max_h;
				$height = $max_h;
				$width = $oWidth/$rate;
			}

			$Nwidth = $max_w;
			$Nheight = $max_h;

			$posx = ($width-$Nwidth)/2;
			$posy = ($height-$Nheight)/2;
		} else {
			if ($oWidth > $max_w) {
				$width = $max_w;
				$rate = $oWidth/$max_w;
			} else {
				$width = $oWidth;
				$rate = 1;
			}
			$height = $oHeight/$rate;
			if ($height > $max_h) {
				$rate = $oHeight/$max_h;
				$width = $oWidth/$rate;
				$height = $max_h;
			}
			$Nwidth = $width;
			$Nheight = $height;
			$posx = 0;
			$posy = 0;
		}
		
		$img = ImageCreateTrueColor($Nwidth, $Nheight);
		imagealphablending($img,false);
		
		if ($ext == 'png') {			
			$fondo = imagecolorallocatealpha($img, 0, 0, 0, 0);
		} else {
			$fondo = imagecolorallocate($img, 255, 255, 255);
		}
		imagefilledrectangle($img,0,0,$width, $height, $fondo);
		
		imagecopyresampled($img, $src, -$posx, -$posy, 0, 0, $width, $height, $oWidth, $oHeight);

		//imagecopyresampled($img, $src, -$posx, -$posy, 0, 0, $oWidth, $oHeight, $oWidth, $oHeight);

		$completeName = $imagename ? $path.$imagename : $path.$prefix.$filename;
		if($ext == 'gif') {
			imagegif($img, $completeName, $quality);
		} elseif ($ext == 'png') {
			imagesavealpha($img, true);
			imagepng($img, $completeName, 9);
		} elseif ($ext == 'jpg' or $ext == 'jpeg') {
			imagejpeg($img, $completeName, $quality);
		}
		chmod($completeName, 0777);

		return array('full_name' => $completeName, 'filename' => $prefix.$filename, 'path' => $path);
	}
}

?>