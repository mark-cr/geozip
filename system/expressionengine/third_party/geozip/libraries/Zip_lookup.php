<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zip_lookup {
	function __construct() {
	}

	function lookup($zip) {
		header('Content-Type: application/json');
		echo $this->ziplocate_us($zip);
	}

	function ziplocate_us($zip) {
		$stream_options = array(
			'http' => array(
				'method' => 'GET',
				'timeout' => 3,
				'header' => "Content-Type: text/html",
				'request_fulluri' => TRUE
				)
			);
		$stream_context = stream_context_create($stream_options);

		try {
			$fp = @fopen('http://ziplocate.us/api/v1/'.$zip, 'r', FALSE, $stream_context);
			if ( $fp ) {
				$result = fread($fp, 2000);
				fclose($fp);

				if ($result == '')
				{
					throw new Exception('Unexpected result (no data).');
				} else {
					$decoded_result = json_decode($result);

					if ( ! is_object($decoded_result) )
					{
						throw new Exception('Unexpected result (not a JSON object).');
					} else {
						return json_encode(array(
							'status' => 'success',
							'zip'    => $decoded_result->zip,
							'lat'    => $decoded_result->lat,
							'lng'    => $decoded_result->lng
							));
					}
				}
			} else {
				throw new Exception('Unable to connect / Invalid request.');
			}
		} catch(Exception $e) {
			return json_encode(array('status'=>'error','message'=>$e->getMessage()));
		}
	}
}
