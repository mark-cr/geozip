<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'geozip/config.php';

class Geozip_ext
{
	var $name           = GEOZIP_NAME;
	var $version        = GEOZIP_VERSION;
	var $description    = GEOZIP_DESCRIPTION;
	var $settings_exist = 'n';
	var $docs_url       = '';

	function __construct($settings = '') {
		$this->settings = $settings;
		ee()->load->add_package_path(PATH_THIRD.'geozip');
	}

	/*
	Parses segment_1, see if it matches the "API" segment setting, then routes to a method in
	the API library matching segment_2
	*/
	function route_url($session) {
		if (ee()->uri->segment(1) == 'geozip-api') {
			// set the session
			ee()->session = $session;

			ee()->load->library('zip_lookup');
			ee()->zip_lookup->lookup(ee()->uri->segment(2));

			die();
		}
	}

	function update_extension($current='') {
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		ee()->db->where('class', __CLASS__);
		ee()->db->update(
			'extensions',
			array('version' => $this->version)
		);
	}

	function activate_extension() {
		// add sessions start hook
		$data = array(
			'class'     => __CLASS__,
			'method'    => 'route_url',
			'hook'      => 'sessions_start',
			'settings'  => '',
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
		ee()->db->insert('extensions', $data);
	}

	function disable_extension() {
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}
}
