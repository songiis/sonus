<?php namespace Rafasamp\Sonus;

use Config;

/**
 * Laravel Audio Conversion Package
 *
 * This package is created to handle server-side conversion tasks using FFMPEG (http://www.fmpeg.org)
 *
 * @package    Laravel
 * @category   Bundle
 * @version    0.1
 * @author     Rafael Sampaio <rafaelsampaio@live.com>
 */

class Sonus
{

	protected $FFMPEG;

	public function __construct()
	{
		try 
		{
			$this->FFMPEG = Config::get('sonus::ffmpeg');
			if(file_exists($this->FFMPEG) === false)
			{
				throw new FileNotFoundException("Unable to access FFMPEG executable!");
			}

		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * Returns full path of FFMPEG
	 * @return string
	 */
	public function getConverterPath()
	{
		return $this->FFMPEG;
	}

	/**
	 * Returns installed FFMPEG version
	 * @return array
	 */
	public function getConverterVersion()
	{
		// Run terminal command to retrieve version
		$command = $this->FFMPEG.' -version';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match("/ffmpeg version (?P<major>[0-9]{0,3}).(?P<minor>[0-9]{0,3}).(?P<revision>[0-9]{0,3})/", $output, $parsed);

		// Assign array with variables
		$version = array(
			'major' => $parsed['major'],
			'minor' => $parsed['minor'],
			'rev'   => $parsed['revision']
			);

		return $version;
	}

	/**
	 * Returns all formats FFMPEG supports
	 * @return array
	 */
	public function getSupportedFormats()
	{
		// Run terminal command
		$command = $this->FFMPEG.' -formats';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match_all("/(?P<mux>(D\s|\sE|DE))\s(?P<format>\S{3,11})\s/", $output, $parsed);

		// Combine the format and mux information into an array
		$formats = array_combine($parsed['format'], $parsed['mux']);

		return $formats;
	}

	/**
	 * Returns all audio formats FFMPEG can encode
	 * @return array
	 */
	public function getSupportedAudioEncoders()
	{
		// Run terminal command
		$command = $this->FFMPEG.' -encoders';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match_all("/[A]([.]|\w)([.]|\w)([.]|\w)([.]|\w)([.]|\w)\s(?P<format>\S{3,20})\s/", $output, $parsed);

		return $parsed['format'];
	}

	/**
	 * Returns all video formats FFMPEG can encode
	 * @return array
	 */
	public function getSupportedVideoEncoders()
	{
		// Run terminal command
		$command = $this->FFMPEG.' -encoders';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match_all("/[V]([.]|\w)([.]|\w)([.]|\w)([.]|\w)([.]|\w)\s(?P<format>\S{3,20})\s/", $output, $parsed);

		return $parsed['format'];
	}

	/**
	 * Returns all audio formats FFMPEG can decode
	 * @return array
	 */
	public function getSupportedAudioDecoders()
	{
		// Run terminal command
		$command = $this->FFMPEG.' -decoders';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match_all("/[A]([.]|\w)([.]|\w)([.]|\w)([.]|\w)([.]|\w)\s(?P<format>\w{3,20})\s/", $output, $parsed);

		return $parsed['format'];
	}

	/**
	 * Returns all video formats FFMPEG can decode
	 * @return array
	 */
	public function getSupportedVideoDecoders()
	{
		// Run terminal command
		$command = $this->FFMPEG.' -decoders';
		$output  = shell_exec($command);

		// PREG pattern to retrive version information
		preg_match_all("/[V]([.]|\w)([.]|\w)([.]|\w)([.]|\w)([.]|\w)\s(?P<format>\w{3,20})\s/", $output, $parsed);

		return $parsed['format'];
	}

	/**
	 * Returns boolean if FFMPEG is able to encode to this format
	 * @param  string $format FFMPEG format name
	 * @return boolean
	 */
	public static function canEncode($format)
	{
		// Get an array with all supported encoding formats
		$app     = new Sonus;
		$formats = array_merge($app->getSupportedAudioEncoders(), $app->getSupportedVideoEncoders());

		// Return boolean if they can be encoded or not
		if(!in_array($format, $formats)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Returns boolean if FFMPEG is able to decode to this format
	 * @param  string $format FFMPEG format name
	 * @return boolean
	 */
	public static function canDecode($format)
	{
		// Get an array with all supported encoding formats
		$app     = new Sonus;
		$formats = array_merge($app->getSupportedAudioDecoders(), $app->getSupportedVideoDecoders());

		// Return boolean if they can be encoded or not
		if(!in_array($format, $formats)) {
			return false;
		} else	{
			return true;
		}
	}


	/**
	 * Extracts information from a string when given a beggining and end needle
	 * @param  string  $string    Haystack
	 * @param  string  $start     Needle for starting extraction
	 * @param  string  $end       Needle to stop extraction
	 * @param  boolean $array     Item should be returned as an array
	 * @param  string  $delimiter Delimiter
	 * @return string             Retrieved information from string
	 * @return array 			  Array with exploded elements from string
	 */
	private static function _extractFromString($string, $start, $end, $array = false, $delimiter = ',')
	{
		// Get lenght of start string
		$startLen  	= strlen($start);

		// Return piece of string requested
		$output 	= strstr(strstr($string, $start), $end, true);

		// Trim whitespace and remove start parameter
		$output 	= trim(substr($output, $startLen));

		// If requested, process output to array
		if($array === true) {
			// Explode string using given delimiter
			$explode = explode($delimiter, $output);

			// Set output as array
			$output  = array();

			// Loop through each item and trim whitespaces
			foreach($explode as $item) {
				$output[] = trim($item);
			}
		}

		return $output;

	}

}