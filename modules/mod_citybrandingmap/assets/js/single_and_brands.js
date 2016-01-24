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
        content: poiTitle //+ ' (' + poiAddress + ')'
    });

    //create POI marker
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


    //create relative brand markers
    for (var i = 0, length = relativeBrands.length; i < length; i++) {
        var data = relativeBrands[i],
            latLng = new google.maps.LatLng(data.latitude, data.longitude);
        // Create marker and putting it on the map
        var brand = new google.maps.Marker({
            position: latLng,
            //icon: data.category_image,
            map: citybranding_mod_map,
            title: data.title,
            id: data.id
        });

        //citybranding_markers.push(marker);
        ////bounds.extends(marker.position);
        //
        //infoBox(map, marker, data);
        //
        //if(data.moderation == 1){
        //    marker.setIcon('http://maps.google.com/mapfiles/ms/icons/blue-dot.png');
        //}
    }



    //infowindow.open(citybranding_mod_map, marker);
}