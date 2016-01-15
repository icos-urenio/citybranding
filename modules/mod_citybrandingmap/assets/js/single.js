console.log((relativeBrands));
function citybranding_mod_map_initialize() {

    var poiCenter = new google.maps.LatLng(poiLat, poiLng);

    var mapOptions = {
        scrollwheel: true,
        center: poiCenter,
        zoom: parseInt(zoom)
    }

    var citybranding_mod_map = new google.maps.Map(document.getElementById('citybranding-mod-map-canvas'),
        mapOptions);

    var infowindow = new google.maps.InfoWindow({
        content: poiAddress
    });

    var marker = new google.maps.Marker({
        position: poiCenter,
        map: citybranding_mod_map,
        title: poiTitle,
        animation: google.maps.Animation.DROP
    });
    marker.addListener('click', function() {
        infowindow.open(citybranding_mod_map, marker);
    });

    if (poiIcon != '') {
        marker.setIcon(poiIcon);
    }

    //infowindow.open(citybranding_mod_map, marker);
}