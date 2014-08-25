<?php

// ZipLocate.us Lookup Proxy
// -------------------------
// Defeats browser same-origin policies.

header('Content-Type: application/json');

require_once 'ziplocate.us.lib.php';

echo geoLookup($_REQUEST['zip']);

// echo json_encode(array(
// 	'lat' => 90,
// 	'lng' => 123,
// 	'zip' => 51002
// ));

exit;