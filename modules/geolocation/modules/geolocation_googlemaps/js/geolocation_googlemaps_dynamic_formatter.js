/**
 * @file
 * Javascript for Goole Map Dynamic API Formatter javascript code.
 * 
 * Many thanks to Lukasz Klimek http://www.klimek.ws for the help
 */

(function($) {

  Backdrop.geolocationGooglemaps = Backdrop.geolocationGooglemaps || {};
  Backdrop.geolocationGooglemaps.maps = Backdrop.geolocationGooglemaps.maps || {};
  Backdrop.geolocationGooglemaps.markers = Backdrop.geolocationGooglemaps.markers || {};

  Backdrop.behaviors.geolocationGooglemapsDynamicFormatter = {

    attach : function(context, settings) {

      var fields = Backdrop.settings.geolocationGooglemaps.formatters;

      // Work on each map
      $.each(fields, function(instance, data) {
        var instanceSettings = data.settings;
        $.each(data.deltas, function(d, delta) {

          id = instance + "-" + d;

          // Only make this once ;)
          $("#geolocation-googlemaps-dynamic-" + id).once('geolocation-googlemaps-dynamic-formatter', function() {

            var map_type;
            var mapOptions;

            var lat = delta.lat;
            var lng = delta.lng;
            var latLng = new google.maps.LatLng(lat, lng);

            switch (instanceSettings.map_maptype) {
              case 'satellite':
                map_type = google.maps.MapTypeId.SATELLITE;
                break;

              case 'terrain':
                map_type = google.maps.MapTypeId.TERRAIN;
                break;

              case 'hybrid':
                map_type = google.maps.MapTypeId.HYBRID;
                break;

              default:
                map_type = google.maps.MapTypeId.ROADMAP;
                break;
            }

            mapOptions = {
              zoom : parseInt(instanceSettings.map_zoomlevel),
              center : latLng,
              mapTypeId : map_type,
              scrollwheel : instanceSettings.map_scrollwheel
            };

            // Create map
            Backdrop.geolocationGooglemaps.maps[id] = new google.maps.Map(this, mapOptions);

            // Create and place marker
            Backdrop.geolocationGooglemaps.markers[id] = new google.maps.Marker({
              map : Backdrop.geolocationGooglemaps.maps[id],
              draggable : false,
              icon : instanceSettings.marker_icon,
              position : latLng
            });
          });
        });
      });
    }
  };
}(jQuery));
