// Throttle / Debounce
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);

function fetchGeo(e) {
	var $codeField = ( typeof(e.eventPhase) !== 'undefined' )? $(this): $(e),
		fieldId    = $codeField.attr('name').split('[').shift().replace('field_id_',''),
		$latField  = $('input[name="field_id_' + fieldId + '[lat]"]'),
		$lngField  = $('input[name="field_id_' + fieldId + '[lng]"]'),
		code       = $codeField.val(),
		lat        = $latField.val(),
		lng        = $lngField.val();

	// We accept 5- or 10-character strings.
	if ( code.length !== 5 && code.length !== 10 ) {
		return false;
	}

	// If a +4 zip is supplied...
	if ( code.length === 10 ) {

		// Truncate it...
		code = code.split('-').shift();

		// And make sure we're left with 5 characters.
		if ( code.length !== 5 ) {
			return false;
		}
	}

	// Make sure it's a 5-number string.
	if ( ! code.match(/[0-9]{5}/) ) {
		return false;
	}

	// Remove status classes, mark as in-progress.
	$codeField
		.removeClass('geozip-lookup-complete geozip-lookup-success geozip-lookup-error')
		.addClass('geozip-lookup-in-progress');

	// Do zip lookup.
	$.getJSON(geozip_helper,{zip:code})
		.fail(function(){
			// On service failure, set error status and clear lat/lng.
			$codeField.addClass('geozip-lookup-error');
			$latField.val('');
			$lngField.val('');
		})
		.done(function(d){
			// If the service responded, check to see if it replied with an object...
			if ( typeof(d) === 'object' ) {

				// And that it contains the data we're looking for...
				if ( typeof(d.lat) === 'number' && typeof(d.lng) === 'number' ) {

					// On success, set status and lat/lng values.
					$codeField.addClass('geozip-lookup-success');
					$latField.val(d.lat);
					$lngField.val(d.lng);
				} else {

					// On failure, set status and clear lat/lng.
					$codeField.addClass('geozip-lookup-error');
					$latField.val('');
					$lngField.val('');
				}
			} else {

				// If the response was a non-object, set status and clear lat/lng.
				$codeField.addClass('geozip-lookup-error');
				$latField.val('');
				$lngField.val('');

			}
		})
		.always(function(){

			// Set complete status when we're done waiting for the lookup.
			$codeField.removeClass('geozip-lookup-in-progress').addClass('geozip-lookup-complete');

		});

	return true;
}

jQuery(function(){

	$('input[data-code]').each(function(i){
		var fieldId = $(this).attr('name').split('[').shift().replace('field_id_',''),
			code    = $(this).val(),
			lat     = $('input[name="field_id_' + fieldId + '[lat]"]').val(),
			lng     = $('input[name="field_id_' + fieldId + '[lng]"]').val();

		// Do initial lookup--for fields converted from text to geozip
		if ( lat === '' || lng === '' ) { fetchGeo(this); }

		$(this).on('keyup',$.debounce(250,fetchGeo));
	});

});