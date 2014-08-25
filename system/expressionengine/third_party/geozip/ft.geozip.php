<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Geozip_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'GeoZip',
		'shortname' => 'geozip',
		'version'	=> '1.0'
	);

	function __construct()
	{
		parent::__construct();

		if (! isset($this->EE->session->cache[$this->info['shortname']]))
		{
			$this->EE->session->cache[$this->info['shortname']] = array();
		}
		$this->cache =& $this->EE->session->cache[$this->info['shortname']];

		if (!isset($this->cache['includes'])) {
			$this->cache['includes'] = array();
		}
	}

	function _extract_data($data) {
		// Matrix gives us back $data as an array.
		if ( is_array($data) ) {
			$data_out = new stdClass();
			$data_out->code = isset($data['code']) ? $data['code'] : '';
			$data_out->lat = isset($data['lat']) ? $data['lat'] : '';
			$data_out->lng = isset($data['lng']) ? $data['lng'] : '';
			return $data_out;
		}

		$datas = explode('|', $data);

		$data_out = new stdClass();
		$data_out->code = $datas[0];
		$data_out->lat = isset($datas[1]) ? $datas[1] : '';
		$data_out->lng = isset($datas[2]) ? $datas[2] : '';
		return $data_out;
	}

	/**
	 * Allow the Field Type to show up in a Grid.
	 */
	public function accepts_content_type($name)
	{
		return ($name == 'channel' || $name == 'grid');
	}

	protected function _include_theme_js($file) {
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'?version='.$this->info['version'].'"></script>');
		}
	}

	protected function _include_theme_css($file) {
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->_theme_url().$file.'?version='.$this->info['version'].'">');
		}
	}

	/**
	 * Theme URL
	 */
	protected function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url . $this->info['shortname'] . '/';
		}

		return $this->cache['theme_url'];
	}


	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data)
	{
		return $this->_display($data, $this->field_name);
	}

	function display_cell($data)
	{
		return $this->_display($data, $this->cell_name);
	}

	function _display($data, $name) {
		$obj = $this->_extract_data($data);


		if ( ! defined('GEOZIP_INIT') )
		{
			define('GEOZIP_INIT',TRUE);
			$this->_include_theme_js('js/' . $this->info['shortname'] . '.js');
			$this->_include_theme_css('css/' . $this->info['shortname'] . '.css');
			$init_js = '<script>var geozip_helper = \'' . $this->_theme_url() . 'helpers/ziplocate.us.proxy.php\';</script>';
		} else { $init_js = ''; }

		return <<<EOF
{$init_js}<div class="{$this->info['shortname']}">
	<input data-code type="text" name="{$name}[code]" value="{$obj->code}">
	<input data-lat type="hidden" name="{$name}[lat]" value="{$obj->lat}">
	<input data-lng type="hidden" name="{$name}[lng]" value="{$obj->lng}">
</div>
EOF;
	}

	/**
	 * Prep data for saving
	 *
	 * @access	public
	 * @param	submitted field data
	 * @return	string to save
	 */
	function save($data)
	{
		$code = $data['code'];
		$lat = $data['lat'];
		$lng = $data['lng'];

		return $code . '|' . $lat . '|' . $lng;
	}

	function save_cell($data) {
		return $this->save($data);
	}

	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field data
	 * @param	field parameters
	 * @param	data between tag pairs
	 * @return	replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$data_out = $this->_extract_data($data);
		return $data_out->code;
	}
	function replace_code($p1, $p2 = array(), $p3 = FALSE) { return $this->replace_tag($p1,$p2,$p3); }
	function replace_zip($p1, $p2 = array(), $p3 = FALSE) { return $this->replace_tag($p1,$p2,$p3); }

	function replace_lat($data, $params = array(), $tagdata = FALSE)
	{
		$data_out = $this->_extract_data($data);
		return $data_out->lat;
	}

	function replace_lng($data, $params = array(), $tagdata = FALSE)
	{
		$data_out = $this->_extract_data($data);
		return $data_out->lng;
	}

	function replace_latlng($data, $params = array(), $tagdata = FALSE)
	{
		$data_out = $this->_extract_data($data);
		return $data_out->lat . '|' . $data_out->lng;
	}
}
