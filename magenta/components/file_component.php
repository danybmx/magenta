<?php

class FileComponent
{	
	public static function fastUpload($data, $path = null, $replace = false)
	{
		return self::upload($data['tmp_name'], $data['name'], $path, $replace);
	}

	public static function upload($tmp_file, $filename, $directory = null, $replace = false)
	{
		$_directory = $directory;
		if ($directory == null) {
			$directory = ROOT.DS.'public'.DS.'uploads';
			$_directory = 'uploads';
		} else {
			if ($directory{0} == '/')
				$directory = ROOT.DS.'public'.$directory;
			if ( ! is_dir($directory)) {
				if ( ! @mkdir($directory, 0777, true)) {
					echo 'No existe el directorio '.$directory.' y no se puede crear';
					return false;
				} else {
					chmod($directory, 0777);
				}
			}
		}
		$directory = $directory.DS;
		
		$explode = explode('.', $filename);
		$ext = strtolower($explode[count($explode)-1]);
		$file = preg_replace('/.'.$ext.'$/', '', $filename);
		
		if (file_exists($directory.$filename)) {
			if ($replace == true) {
				unlink($directory.$filename);
			} else {
				$filename = $file.'_'.rand(0000000,9999999).'.'.$ext;
			}
		} else {
			$filename = $file.'.'.$ext;
		}
		
		if (!move_uploaded_file($tmp_file, $directory.$filename)) {
			return false;
		} else {
			chmod($directory.$filename, 0777);
			$data['path'] = $_directory;
			$data['filename'] = $filename;
			return $data;
		}
	}
	
	public static function remove($filename)
	{
		if ($filename{0} == '/')
			$filename = ROOT.DS.'public'.$filename;
		
		if ( ! file_exists($filename))
			return true;

		if (unlink($filename)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function getExt($file)
	{
		$explode = explode('.', $file);
		$ext = strtolower($explode[count($explode)-1]);
		return $ext;
	}
}

?>