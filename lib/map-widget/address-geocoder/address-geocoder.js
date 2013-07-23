var mapcanvas = "geocodepreview";

jQuery(document).ready(function($) {

	jQuery.fn.codeAddress = function () {

		var geocoder;
		var map;


	  geocoder = new google.maps.Geocoder();
	  var latlng = new google.maps.LatLng(-34.397, 150.644);
	  var myOptions = {
		backgroundColor: '#EAEAEA',
		mapTypeControl: false,
	    zoom: 11,
	    center: latlng,
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	  }
	  map = new google.maps.Map(document.getElementById(mapcanvas), myOptions);


	  var address = $('input[name="martygeocoderaddress"]').attr('value');;

	  geocoder.geocode( { 'address': address}, function(results, status) {
	    if (status == google.maps.GeocoderStatus.OK) {
	      map.setCenter(results[0].geometry.location);
	      var marker = new google.maps.Marker({
	          map: map, 
	          position: results[0].geometry.location
	      });
			$('input[name="martygeocoderlatlng"]').attr('value',results[0].geometry.location);
	    } else {
	      alert("Geocode was not successful for the following reason: " + status);
	    }
	  });
	}

	$('#geocode').bind('click', function() {
		$(document).codeAddress();
	});

	

});