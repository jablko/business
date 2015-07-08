function load_handler() {
  var map_div = document.getElementById('map-canvas');

  var opts = {
    scrollwheel: false
  };

  var map = new google.maps.Map(map_div, opts);

  var info_window = new google.maps.InfoWindow();

  function click_handler() {
    info_window.setContent(map_template(this));
    info_window.open(map, this);
  }

  var all_data = <?php echo wp_json_encode($all_data) ?>;
  var bounds = new google.maps.LatLngBounds();
  for (var i = 0; i < all_data.length; i++) {
    var latlng = new google.maps.LatLng(all_data[i].lat, all_data[i].lng);

    var opts = all_data[i];
    opts.position = latlng;
    opts.map = map;

    var marker = new google.maps.Marker(opts);
    if (marker.getClickable()) {
      google.maps.event.addListener(marker, 'click', click_handler);
    }

    bounds.extend(latlng);
  }

  function bounds_changed_handler() {
    if (map.getZoom() > 14) {
      map.setZoom(14);
    }
  }
  google.maps.event.addListenerOnce(map, 'bounds_changed', bounds_changed_handler);
  map.fitBounds(bounds);
}
google.maps.event.addDomListener(window, 'load', load_handler);
