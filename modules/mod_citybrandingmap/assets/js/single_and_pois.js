var citybranding_markers = [];
var infoWindow = new google.maps.InfoWindow({
    maxWidth: 350
});
function citybranding_mod_map_initialize() {

    var brandCenter = new google.maps.LatLng(poiLat, poiLng);

    var mapOptions = {
        scrollwheel: true,
        center: brandCenter,
        zoom: parseInt(zoom)
    }

    var citybranding_mod_map = new google.maps.Map(document.getElementById('citybranding-mod-map-canvas'),
        mapOptions);

    var infowindow = new google.maps.InfoWindow({
        content: poiTitle //+ ' (' + poiAddress + ')'
    });

    //create BRAND marker
    var marker = new google.maps.Marker({
        position: brandCenter,
        map: citybranding_mod_map,
        title: poiTitle,
        animation: google.maps.Animation.DROP
    });
    marker.addListener('click', function() {
        infowindow.open(citybranding_mod_map, marker);
    });

    //if (poiIcon != '') {
    //    marker.setIcon(poiIcon);
    //}
    citybranding_markers.push(marker);

    //create relative poi markers
    for (var i = 0, length = relativePois.length; i < length; i++) {
        var data = relativePois[i],
            latLng = new google.maps.LatLng(data.latitude, data.longitude);
        // Create marker and putting it on the map
        var poi = new google.maps.Marker({
            position: latLng,
            icon: data.category_image,
            map: citybranding_mod_map,
            title: data.title,
            id: data.id
        });
        citybranding_markers.push(poi);

        //brand.addListener('click', function() {
        //    brand_infowindow.open(citybranding_mod_map, brand);
        //});

        infoBox(citybranding_mod_map, poi, data);

        if(data.moderation == 1){
            poi.setIcon('https://maps.google.com/mapfiles/ms/icons/blue-dot.png');
        }
    }

    resetBounds(citybranding_mod_map, citybranding_markers);

}

function resetBounds(map, gmarkers) {
    var a = 0;
    bounds = null;
    bounds = new google.maps.LatLngBounds();
    for (var i=0; i<gmarkers.length; i++) {
        if(gmarkers[i].getVisible()){
            a++;
            bounds.extend(gmarkers[i].position);
        }
    }
    if(a > 0){
        map.fitBounds(bounds);
        var listener = google.maps.event.addListener(map, 'idle', function() {
            if (map.getZoom() > 18) map.setZoom(18);
            google.maps.event.removeListener(listener);
        });
    }
}


function infoBox(map, marker, data) {

    var link = linkToPoi;

    // Attaching a click event to the current marker
    google.maps.event.addListener(marker, "click", function(e) {
        infoWindow.setContent('<div class="infowindowcontent"><a href="'+link+'/'+data.id+'">'+data.title+'</a></div>');
        infoWindow.open(map, marker);

        if(data.poitype == 'poi'){
            panelFocus(data.id);
        }
    });
    google.maps.event.addListener(infoWindow,'closeclick',function(){
        panelFocusReset();
    });

    // Creating a closure to retain the correct data
    // Pass the current data in the loop into the closure (marker, data)
    (function(marker, data) {
        // Attaching a click event to the current marker
        google.maps.event.addListener(marker, "click", function(e) {
            if(data.state == 0){
                infoWindow.setContent('<div class="infowindowcontent citybranding-warning"><i class="icon-info-sign"></i> '+data.title+'</div>');
            } else {
                infoWindow.setContent('<div class="infowindowcontent"><a href="'+link+'/'+data.id+'">'+data.title+'</a></div>');
            }

            infoWindow.open(map, marker);
        });
    })(marker, data);
}