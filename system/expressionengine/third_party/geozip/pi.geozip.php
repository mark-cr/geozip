<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'        => 'GeoZip Helper Plugin',
	'pi_version'     => '1.0',
	'pi_author'      => 'Click Rain',
	'pi_author_url'  => 'http://clickrain.com/',
	'pi_description' => 'GeoZip - Locate things',
	'pi_usage'       => Geozip::usage()
	);


class Geozip
{
	var $return_data = '';
	function Fmt()
	{
		Geozip::__construct();
	}

	function __construct()
	{
		$this->EE =& get_instance();
	}

	/**
	 * Theme Path
	 */
	private function _theme_path()
	{
		if (! isset($this->cache['theme_path']))
		{
			$theme_folder_url = defined('PATH_THIRD_THEMES') ? PATH_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_path').'third_party/';
			$this->cache['theme_path'] = $theme_folder_url . 'geozip/';
		}

		return $this->cache['theme_path'];
	}

	private function _calcDistance($latFrom,$lngFrom,$latTo,$lngTo,$unit)
	{
		switch($unit)
		{
			case 'mi':
				$earthRadius = 3959;
				break;
			case 'ft':
				$earthRadius = 20903520;
				break;
			case 'km':
				$earthRadius = 6371;
				break;
			case 'm':
				$earthRadius = 6371000;
				break;
		}

		// convert from degrees to radians
		$latFrom = deg2rad($latFrom);
		$lngFrom = deg2rad($lngFrom);
		$latTo = deg2rad($latTo);
		$lngTo = deg2rad($lngTo);

		$lngDelta = $lngTo - $lngFrom;
		$a = pow(cos($latTo) * sin($lngDelta), 2) +
		pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lngDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lngDelta);

		$angle = atan2(sqrt($a), $b);
		return $angle * $earthRadius;
	}

	private function _roundToAny($n,$x=5)
	{
	    return (round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
	}

	private function _validateZip($zip)
	{
		// HA!!
		return TRUE;
	}

	public function latlng($incLat = TRUE, $incLng = TRUE)
	{
		$zip = $this->EE->TMPL->fetch_param('zip');

		if ( ! $zip ) {
			return 'Error: No zip provided.';
		}

		if ( ! $this->_validateZip($zip) ) {
			return 'Error: Invalid zip.';
		}

		require_once $this->_theme_path() . 'helpers/ziplocate.us.lib.php';

		$geoData = json_decode(geoLookup($zip));

		if ( $geoData->status == 'success' )
		{
			$output = '';

			if ( $incLat ) $output .= $geoData->lat;
			if ( $incLat && $incLng ) $output .= '|';
			if ( $incLng ) $output .= $geoData->lng;

			return $output;
		} else {
			return 'Error: Lookup failure.';
		}
	}
	public function lat() { return $this->latlng(TRUE,FALSE); }
	public function lng() { return $this->latlng(FALSE,TRUE); }

	public function distance()
	{
		$unit = $this->EE->TMPL->fetch_param('unit','mi');
		$allowed_units = array('mi','km','m','ft');

		// Check for a valid unit definition.
		if ( ! in_array(strtolower($unit),$allowed_units) )
		{
			return 'Error: Bad unit.';
		}

		// Retrieve granularity & convert.
		$granularity = $this->EE->TMPL->fetch_param('granularity',5);
		if ( $granularity != 'exact' ) $granularity = (int) $granularity;

		// Attempt to retrieve coordinates.
		$coord1 = $this->EE->TMPL->fetch_param('from');
		$coord2 = $this->EE->TMPL->fetch_param('to');

		// Check for coordinates.
		if ( ! $coord1 || ! $coord2 ) {
			return 'Error: To and From coordinates must be provided.';
		}

		// Start parsing coordinates.
		$coord1 = explode('|',$coord1);
		$coord2 = explode('|',$coord2);

		// Check for properly-formed coordinates.
		if ( ! is_array($coord1) || ! is_array($coord2) || count($coord1) != 2 || count($coord2) != 2 )
		{
			return 'Error: Malformed coordinates.';
		}

		// Validate (well, force) coordinate data.
		array_walk($coord1,function(&$value,$key){ $value = (float) $value; });
		array_walk($coord2,function(&$value,$key){ $value = (float) $value; });

		return $this->_roundToAny(
			$this->_calcDistance(
				$coord1[0],
				$coord1[1],
				$coord2[0],
				$coord2[1],
				$unit),
			$granularity);
	}

	/**
	 * Usage
	 *
	 * This function describes how the plugin is used.
	 *
	 * @access	public
	 * @return	string
	 */

	//  Make sure and use output buffering

	function usage()
	{
		ob_start();
?>

{exp:geozip:distance from='lat|lng' to='lat|lng' unit='mi'}

Coordinates must be provided in the "lat|lng" format. (E.g. 43.534906906977|-96.691036267034)

Accepted units are:

	mi: Miles (default)
	km: Kilometers
	ft: Feet
	m:  Meters

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}