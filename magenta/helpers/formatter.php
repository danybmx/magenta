<?php
/**
 * Created by dpStudios
 * Date: 21/04/11
 */
 
class Formatter
{
public static function date($date, $format = 'd/m/Y')
	{
		$time = strtotime($date);
		return date($format, $time);
	}

	public static function datetime($date)
	{
		return self::date($date, 'd/m/Y H:i:s');
	}

	public static function dateTransform($date, $from = 'd/m/y', $to = 'Y-m-d H:i:s', $delimiter = '/')
	{
		$day = 0;
		$month = 0;
		$year = 0;

		$from = str_split($from);
		$date = explode($delimiter, $date);
		$x = 0;
		foreach ($from as $i) {
			switch ($i) {
				case 'd':
					$day = $date[$x++];
					break;
				case 'm':
					$month = $date[$x++];
					break;
				case 'y':
					$year = $date[$x++];
					break;
				default:
					break;
			}
		}
		$date_string = $day.'.'.$month.'.'.$year;

		return date($to, strtotime($date_string));
	}

	public static function cut($string, $length)
	{
		return substr($string, 0, $length);
	}

	public static function money($number, $currency = '&euro;', $decimals = 2) {
		return number_format($number, $decimals, ',', '.').$currency;
	}

	public static function checkbox($bool)
	{
		if ($bool) {
			return '<div style="background-color: #6F6; width: 16px; height: 16px; margin-top: 2px; margin-bottom: -2px;"></div>';
		} else {
			return '<div style="background-color: #F22; width: 16px; height: 16px; margin-top: 2px; margin-bottom: -2px;"></div>';
		}
	}

	public static function math($number, $operation)
	{
		return eval('return '.$number.' '.$operation.';');
	}

	public static function count($obj)
	{
		return count($obj);
	}
}
